<?php
declare(strict_types=1);

namespace App\Models;

class ReservationModel extends BaseModel {

    /** Quantos minutos a reserva fica segurada antes de expirar sozinha. */
    public const RESERVATION_TIMEOUT_MIN = 30;

    private static bool $expireRan = false;

    /**
     * Lazy expiry — chamado uma vez por request a partir do front controller.
     * Identifica reservas ativas (action_type='reserved') com mais de
     * RESERVATION_TIMEOUT_MIN minutos sem purchase_confirmed=1, marca-as
     * como 'cancelled' e devolve o estoque ao gift. Falhas só logam — não
     * derrubam a request, já que isso é uma manutenção em background.
     */
    public static function expireStaleOnce(): void {
        if (self::$expireRan) return;
        self::$expireRan = true;

        $self = new self;
        $db   = $self->getDb();
        $minutes = self::RESERVATION_TIMEOUT_MIN;

        try {
            $db->beginTransaction();

            // Coluna purchase_confirmed pode não existir se o admin ainda não
            // rodou a migration phase 6. Detecta antes pra montar a WHERE certa.
            $hasCol = false;
            try {
                $row = $self->query(
                    "SHOW COLUMNS FROM reservations LIKE 'purchase_confirmed'"
                )->fetch();
                $hasCol = (bool) $row;
            } catch (\Throwable $_) {
                $hasCol = false;
            }

            $confirmedCheck = $hasCol
                ? "AND (purchase_confirmed IS NULL OR purchase_confirmed = 0)"
                : '';

            $stale = $self->query(
                "SELECT id, gift_id,
                        COALESCE(quantity, 1) AS quantity
                 FROM reservations
                 WHERE status = 'active'
                   AND action_type = 'reserved'
                   {$confirmedCheck}
                   AND created_at < DATE_SUB(NOW(), INTERVAL {$minutes} MINUTE)
                 FOR UPDATE"
            )->fetchAll();

            if (!$stale) {
                $db->commit();
                return;
            }

            foreach ($stale as $r) {
                $qty = max(1, (int) ($r['quantity'] ?? 1));
                $self->query(
                    "UPDATE reservations SET status = 'cancelled' WHERE id = :id",
                    [':id' => (int) $r['id']]
                );
                $self->query(
                    'UPDATE gifts
                     SET available_qty = LEAST(quantity, available_qty + :qty_inc),
                         reserved      = GREATEST(reserved - :qty_dec, 0),
                         updated_at    = NOW()
                     WHERE id = :gid',
                    [
                        ':qty_inc' => $qty,
                        ':qty_dec' => $qty,
                        ':gid'     => (int) $r['gift_id'],
                    ]
                );
            }
            error_log('[ReservationModel::expireStaleOnce] expirou ' . count($stale) . ' reserva(s)');

            $db->commit();
        } catch (\Throwable $e) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            error_log('[ReservationModel::expireStaleOnce] ' . $e->getMessage());
        }
    }

    /**
     * Atomic reservation with pessimistic lock. Aceita quantidade — usada
     * em presentes type='multi' para o convidado pegar mais de uma vaga
     * num único clique. Estoque é decrementado por $qty atomicamente.
     * Returns a normalized payload consumed by ReservationController::store().
     */
    public function reserve(int $giftId, int $userId, int $qty = 1): array {
        if ($qty < 1)  { $qty = 1; }
        if ($qty > 99) { $qty = 99; }

        try {
            $this->db->beginTransaction();

            // SELECT g.* em vez de listar colunas explícitas — evita
            // "Unknown column" se o schema do banco estiver atrás de
            // alguma migration (type, redirect_url etc).
            $gift = $this->query(
                'SELECT g.* FROM gifts g
                 WHERE g.id = :gift_id AND g.is_active = 1
                 FOR UPDATE',
                [':gift_id' => $giftId]
            )->fetch();

            if (!$gift) {
                $this->db->rollBack();
                error_log('[ReservationModel::reserve] gift not found or inactive: id=' . $giftId);
                return [
                    'success' => false,
                    'is_exhausted' => false,
                    'error' => 'Presente não encontrado.',
                    'debug_path' => 'gift_not_found',
                ];
            }

            $availableNow = (int) $gift['available_qty'];

            // Single-type só aceita qty=1, mesmo que o cliente tente abusar.
            if (($gift['type'] ?? 'single') !== 'multi') {
                $qty = 1;
            }

            if ($availableNow <= 0) {
                $this->db->rollBack();
                return [
                    'success' => false,
                    'is_exhausted' => true,
                    'error' => 'Esse presente acabou de ficar indisponível.',
                ];
            }

            if ($availableNow < $qty) {
                $this->db->rollBack();
                return [
                    'success' => false,
                    'is_exhausted' => false,
                    'available_qty' => $availableNow,
                    'error' => 'Só restam ' . $availableNow . ' ' . ($availableNow === 1 ? 'unidade' : 'unidades') . ' desse presente.',
                ];
            }

            // PDO sem emulação não aceita reusar o mesmo :param em
            // múltiplas posições (erro HY093 "Invalid parameter number"),
            // então usamos três placeholders distintos pro mesmo valor.
            $this->query(
                'UPDATE gifts
                 SET available_qty = available_qty - :qty_dec,
                     reserved      = reserved + :qty_inc,
                     updated_at    = NOW()
                 WHERE id = :gift_id AND available_qty >= :qty_check',
                [
                    ':gift_id'   => $giftId,
                    ':qty_dec'   => $qty,
                    ':qty_inc'   => $qty,
                    ':qty_check' => $qty,
                ]
            );

            // INSERT com `quantity`; se a coluna ainda não foi criada
            // (migration phase 6 não rodou no banco), cai pra um INSERT
            // legado sem ela. Quem ficar nessa rota perde só o tracking
            // de quantidade — restante do fluxo continua funcionando.
            try {
                $this->query(
                    'INSERT INTO reservations (gift_id, user_id, status, action_type, quantity)
                     VALUES (:gift_id, :user_id, :status, :action_type, :qty)',
                    [
                        ':gift_id'     => $giftId,
                        ':user_id'     => $userId,
                        ':status'      => 'active',
                        ':action_type' => 'reserved',
                        ':qty'         => $qty,
                    ]
                );
            } catch (\PDOException $e) {
                // 1054 = ER_BAD_FIELD_ERROR (Unknown column 'quantity').
                // SQLSTATE 42S22 cobre o mesmo cenário em outros drivers.
                $isMissingColumn = $e->getCode() === '42S22'
                    || str_contains($e->getMessage(), "Unknown column 'quantity'");
                if (!$isMissingColumn) {
                    throw $e;
                }
                error_log('[ReservationModel::reserve] coluna quantity ausente — rode sql/migrate_phase6.sql. Caindo para INSERT legado.');
                $this->query(
                    'INSERT INTO reservations (gift_id, user_id, status, action_type)
                     VALUES (:gift_id, :user_id, :status, :action_type)',
                    [
                        ':gift_id'     => $giftId,
                        ':user_id'     => $userId,
                        ':status'      => 'active',
                        ':action_type' => 'reserved',
                    ]
                );
            }

            $updatedGift = $this->query(
                'SELECT available_qty FROM gifts WHERE id = :gift_id',
                [':gift_id' => $giftId]
            )->fetch();

            $availableQty = (int) ($updatedGift['available_qty'] ?? 0);

            $this->db->commit();

            return [
                'success'       => true,
                'quantity'      => $qty,
                'available_qty' => $availableQty,
                'is_exhausted'  => ($availableQty === 0),
                'redirect_url'  => (string) ($gift['redirect_url'] ?? ''),
            ];
        } catch (\Throwable $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }

            $code = $e->getCode();
            error_log(sprintf(
                "[ReservationModel::reserve] FAILED gift=%d user=%d qty=%d code=%s msg=%s",
                $giftId, $userId, $qty, (string) $code, $e->getMessage()
            ));

            return [
                'success'    => false,
                'is_exhausted' => false,
                'error'      => 'Não foi possível concluir sua reserva agora. Tente novamente.',
                'debug_path' => 'exception',
                'debug_code' => (string) $code,
                'debug_msg'  => $e->getMessage(),
            ];
        }
    }

    /**
     * Admin-side hard delete. Libera estoque se a reserva estava ativa
     * e era do tipo 'reserved' (PIX não decrementa estoque). Pagamentos
     * vinculados ficam por audit — o FK em payments.reservation_id é
     * ON DELETE SET NULL, então o registro de PIX no banco continua.
     */
    public function adminDelete(int $reservationId): array {
        try {
            $this->db->beginTransaction();

            $res = $this->query(
                'SELECT id, gift_id, quantity, status, action_type
                 FROM reservations
                 WHERE id = :rid
                 FOR UPDATE',
                [':rid' => $reservationId]
            )->fetch();

            if (!$res) {
                $this->db->rollBack();
                return ['success' => false, 'error' => 'Reserva não encontrada.'];
            }

            $shouldRelease = $res['status'] === 'active'
                && ($res['action_type'] ?? '') === 'reserved';
            $qty = max(1, (int) ($res['quantity'] ?? 1));

            if ($shouldRelease) {
                $this->query(
                    'UPDATE gifts
                     SET available_qty = LEAST(quantity, available_qty + :qty_inc),
                         reserved      = GREATEST(reserved - :qty_dec, 0),
                         updated_at    = NOW()
                     WHERE id = :gid',
                    [
                        ':qty_inc' => $qty,
                        ':qty_dec' => $qty,
                        ':gid'     => (int) $res['gift_id'],
                    ]
                );
            }

            $this->query(
                'DELETE FROM reservations WHERE id = :rid',
                [':rid' => $reservationId]
            );

            $this->db->commit();
            return [
                'success'         => true,
                'stock_released'  => $shouldRelease,
                'released_qty'    => $shouldRelease ? $qty : 0,
            ];
        } catch (\Throwable $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            error_log('[ReservationModel::adminDelete] ' . $e->getMessage());
            return ['success' => false, 'error' => 'Não foi possível apagar a reserva agora.'];
        }
    }

    /**
     * Marks a reservation as purchase-confirmed (= 1) or not-purchased (= 0).
     * Quando $confirmed === 0, libera o estoque de volta (igual cancel).
     * Quando 1, só registra a confirmação — estoque continua reservado e
     * o admin sabe que aquela compra externa rolou.
     */
    public function setPurchaseConfirmed(int $reservationId, int $userId, int $confirmed): array {
        $confirmed = $confirmed === 1 ? 1 : 0;
        try {
            $this->db->beginTransaction();

            $reservation = $this->query(
                'SELECT id, gift_id, quantity, status, action_type
                 FROM reservations
                 WHERE id = :rid AND user_id = :uid
                 FOR UPDATE',
                [':rid' => $reservationId, ':uid' => $userId]
            )->fetch();

            if (!$reservation) {
                $this->db->rollBack();
                return ['success' => false, 'error' => 'Reserva não encontrada.'];
            }
            if ($reservation['status'] !== 'active') {
                $this->db->rollBack();
                return ['success' => false, 'error' => 'Essa reserva não está mais ativa.'];
            }
            if (($reservation['action_type'] ?? '') !== 'reserved') {
                $this->db->rollBack();
                return ['success' => false, 'error' => 'Esse fluxo só vale para presentes reservados.'];
            }

            // "Não comprei" libera estoque e cancela a reserva.
            if ($confirmed === 0) {
                $qty = max(1, (int) ($reservation['quantity'] ?? 1));
                $this->query(
                    'UPDATE reservations
                     SET status = "cancelled", purchase_confirmed = 0
                     WHERE id = :rid',
                    [':rid' => $reservationId]
                );
                $this->query(
                    'UPDATE gifts
                     SET available_qty = LEAST(quantity, available_qty + :qty_inc),
                         reserved      = GREATEST(reserved - :qty_dec, 0),
                         updated_at    = NOW()
                     WHERE id = :gid',
                    [
                        ':qty_inc' => $qty,
                        ':qty_dec' => $qty,
                        ':gid'     => (int) $reservation['gift_id'],
                    ]
                );
            } else {
                $this->query(
                    'UPDATE reservations
                     SET purchase_confirmed = 1
                     WHERE id = :rid',
                    [':rid' => $reservationId]
                );
            }

            $this->db->commit();
            return ['success' => true, 'confirmed' => $confirmed];
        } catch (\Throwable $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            error_log('[ReservationModel::setPurchaseConfirmed] ' . $e->getMessage());
            return ['success' => false, 'error' => 'Não foi possível registrar isso agora. Tente novamente.'];
        }
    }

    /**
     * Returns active reservations for the current guest (newest first).
     */
    public function getActiveByUser(int $userId): array {
        // Uso `r.*` em vez de listar `r.quantity, r.purchase_confirmed`
        // pra não quebrar caso a migration phase 6 ainda não tenha rodado
        // — colunas ausentes simplesmente não vêm no row, e o consumidor
        // (my-gifts view) já tem fallback `?? null` / `?? 1`.
        return $this->query(
            'SELECT
                r.*,
                g.name AS gift_name,
                g.category,
                g.description,
                g.price,
                g.image_path,
                g.available_qty,
                g.slug,
                g.redirect_url,
                p.amount AS paid_amount
             FROM reservations r
             INNER JOIN gifts    g ON g.id = r.gift_id
             LEFT  JOIN payments p ON p.reservation_id = r.id AND p.mp_status = "approved"
             WHERE r.user_id = :user_id
               AND r.status = :status
             ORDER BY r.created_at DESC',
            [
                ':user_id' => $userId,
                ':status'  => 'active',
            ]
        )->fetchAll();
    }

    /**
     * Admin listing — joins users + gifts and supports filtering by action_type
     * and status. Returns most recent first.
     */
    public function adminList(string $action = 'all', string $status = 'active'): array {
        $conditions = [];
        $params     = [];

        if ($action === 'reserved' || $action === 'pix') {
            $conditions[]            = 'r.action_type = :action_type';
            $params[':action_type']  = $action;
        }
        if ($status === 'active' || $status === 'cancelled') {
            $conditions[]      = 'r.status = :status';
            $params[':status'] = $status;
        }

        $where = $conditions ? ('WHERE ' . implode(' AND ', $conditions)) : '';
        $sql = "SELECT
                    r.id, r.gift_id, r.user_id, r.status, r.action_type, r.created_at,
                    u.name  AS user_name,
                    u.email AS user_email,
                    u.phone AS user_phone,
                    g.name  AS gift_name,
                    g.price AS gift_price,
                    g.category AS gift_category,
                    p.id     AS payment_id,
                    p.amount AS paid_amount
                FROM reservations r
                INNER JOIN users    u ON u.id = r.user_id
                INNER JOIN gifts    g ON g.id = r.gift_id
                LEFT  JOIN payments p ON p.reservation_id = r.id AND p.mp_status = 'approved'
                {$where}
                ORDER BY r.created_at DESC";

        return $this->query($sql, $params)->fetchAll();
    }

    /**
     * Most recent activity for the admin dashboard. Mixes reservas and PIX
     * (any active reservation row) and joins user/gift/payment so the view
     * can render rows without extra queries.
     */
    public function recentActivity(int $limit = 6): array {
        $limit = max(1, min(50, $limit));
        return $this->query(
            "SELECT r.id, r.action_type, r.created_at, r.status,
                    u.name  AS user_name,
                    g.name  AS gift_name,
                    g.price AS gift_price,
                    p.amount AS paid_amount
             FROM reservations r
             INNER JOIN users    u ON u.id = r.user_id
             INNER JOIN gifts    g ON g.id = r.gift_id
             LEFT  JOIN payments p ON p.reservation_id = r.id AND p.mp_status = 'approved'
             WHERE r.status = 'active'
             ORDER BY r.created_at DESC
             LIMIT {$limit}"
        )->fetchAll();
    }

    /**
     * Aggregated summary used by the admin dashboard / reservas page header.
     */
    public function adminSummary(): array {
        $row = $this->query(
            "SELECT
                SUM(CASE WHEN action_type = 'reserved' AND status = 'active' THEN 1 ELSE 0 END) AS active_reserved,
                SUM(CASE WHEN action_type = 'pix'      AND status = 'active' THEN 1 ELSE 0 END) AS active_pix,
                SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END)                          AS cancelled_total,
                COUNT(*)                                                                        AS total
             FROM reservations"
        )->fetch();

        return [
            'active_reserved' => (int) ($row['active_reserved'] ?? 0),
            'active_pix'      => (int) ($row['active_pix']      ?? 0),
            'cancelled_total' => (int) ($row['cancelled_total'] ?? 0),
            'total'           => (int) ($row['total']           ?? 0),
        ];
    }

    /**
     * Cancels one active reservation owned by the given guest and returns stock.
     */
    public function cancel(int $reservationId, int $userId): array {
        try {
            $this->db->beginTransaction();

            $reservation = $this->query(
                'SELECT id, gift_id, quantity, status, action_type
                 FROM reservations
                 WHERE id = :reservation_id AND user_id = :user_id
                 FOR UPDATE',
                [
                    ':reservation_id' => $reservationId,
                    ':user_id'        => $userId,
                ]
            )->fetch();

            if (!$reservation) {
                $this->db->rollBack();
                return [
                    'success' => false,
                    'error' => 'Reserva não encontrada.',
                ];
            }

            if ($reservation['status'] !== 'active') {
                $this->db->rollBack();
                return [
                    'success' => false,
                    'error' => 'Essa reserva já foi cancelada.',
                ];
            }
            if (($reservation['action_type'] ?? 'reserved') !== 'reserved') {
                $this->db->rollBack();
                return [
                    'success' => false,
                    'error' => 'Somente reservas podem ser canceladas.',
                ];
            }

            $qty = max(1, (int) ($reservation['quantity'] ?? 1));

            $this->query(
                'UPDATE reservations
                 SET status = :status
                 WHERE id = :reservation_id AND status = :active_status',
                [
                    ':status'         => 'cancelled',
                    ':reservation_id' => $reservationId,
                    ':active_status'  => 'active',
                ]
            );

            $this->query(
                'UPDATE gifts
                 SET available_qty = LEAST(quantity, available_qty + :qty_inc),
                     reserved      = GREATEST(reserved - :qty_dec, 0),
                     updated_at    = NOW()
                 WHERE id = :gift_id',
                [
                    ':qty_inc' => $qty,
                    ':qty_dec' => $qty,
                    ':gift_id' => (int) $reservation['gift_id'],
                ]
            );

            $updatedGift = $this->query(
                'SELECT available_qty FROM gifts WHERE id = :gift_id',
                [':gift_id' => (int) $reservation['gift_id']]
            )->fetch();

            $this->db->commit();

            return [
                'success' => true,
                'available_qty' => (int) ($updatedGift['available_qty'] ?? 0),
            ];
        } catch (\Throwable $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }

            error_log('[ReservationModel::cancel] ' . $e->getMessage());

            return [
                'success' => false,
                'error' => 'Não foi possível cancelar sua reserva agora. Tente novamente.',
            ];
        }
    }
}
