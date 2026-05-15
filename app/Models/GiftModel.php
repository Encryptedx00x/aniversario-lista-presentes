<?php
declare(strict_types=1);

namespace App\Models;

class GiftModel extends BaseModel {

    /**
     * Returns all active gifts ordered by availability then sort_order.
     */
    public function getAllActive(int $userId = 0): array {
        // Trazemos também user_reserved_at (timestamp da reserva ativa
        // do usuário) e user_purchase_confirmed para o card mostrar o
        // link da loja / countdown de expiração depois de reservar.
        $sql = 'SELECT g.*,
                       us.action_type        AS user_action_type,
                       us.reserved_at        AS user_reserved_at,
                       us.purchase_confirmed AS user_purchase_confirmed
                FROM gifts g
                LEFT JOIN (
                    SELECT gift_id,
                           MAX(action_type) AS action_type,
                           MAX(created_at)  AS reserved_at,
                           MAX(' . self::purchaseConfirmedExpr() . ') AS purchase_confirmed
                    FROM reservations
                    WHERE user_id = :user_id AND status = :status
                    GROUP BY gift_id
                ) us ON us.gift_id = g.id
                WHERE g.is_active = 1
                ORDER BY g.available_qty DESC, g.sort_order ASC';
        return $this->query($sql, [
            ':user_id' => $userId,
            ':status' => 'active',
        ])->fetchAll();
    }

    /**
     * Retorna a expressão SQL correta pra purchase_confirmed: nome da
     * coluna se existir, NULL literal se não (banco ainda sem migration
     * phase 6). Detectado uma vez por request via static cache.
     */
    private static function purchaseConfirmedExpr(): string {
        static $cached = null;
        if ($cached !== null) return $cached;
        try {
            $row = (new self)->query(
                "SHOW COLUMNS FROM reservations LIKE 'purchase_confirmed'"
            )->fetch();
            $cached = $row ? 'purchase_confirmed' : 'NULL';
        } catch (\Throwable $_) {
            $cached = 'NULL';
        }
        return $cached;
    }

    /**
     * Returns active gifts filtered by optional search and category,
     * ordered by the given sort key.
     */
    public function filter(string $search, string $category, string $sort, int $userId = 0): array {
        $orderBy = match ($sort) {
            'price_asc'  => 'price ASC',
            'price_desc' => 'price DESC',
            'featured'   => 'featured DESC, sort_order ASC',
            default      => 'available_qty DESC, sort_order ASC', // 'available'
        };

        $conditions = ['g.is_active = 1'];
        $params     = [];

        if ($search !== '') {
            $conditions[] = 'g.name LIKE :search';
            $params[':search'] = '%' . $search . '%';
        }

        if ($category !== '') {
            $conditions[] = 'g.category = :category';
            $params[':category'] = $category;
        }

        $where = implode(' AND ', $conditions);
        $pcExpr = self::purchaseConfirmedExpr();
        $sql = "SELECT g.*,
                       us.action_type        AS user_action_type,
                       us.reserved_at        AS user_reserved_at,
                       us.purchase_confirmed AS user_purchase_confirmed
                FROM gifts g
                LEFT JOIN (
                    SELECT gift_id,
                           MAX(action_type) AS action_type,
                           MAX(created_at)  AS reserved_at,
                           MAX({$pcExpr})   AS purchase_confirmed
                    FROM reservations
                    WHERE user_id = :user_id AND status = :status
                    GROUP BY gift_id
                ) us ON us.gift_id = g.id
                WHERE {$where}
                ORDER BY {$orderBy}";
        $params[':user_id'] = $userId;
        $params[':status'] = 'active';
        return $this->query($sql, $params)->fetchAll();
    }

    /**
     * Returns gifts for admin filtered by optional search text and status.
     */
    public function adminFilter(string $search, string $status): array {
        // Sentinel "Contribuição livre" sempre escondido — é gerenciado pelo
        // próprio fluxo de PIX livre, não deve aparecer na listagem normal.
        $conditions = ["(g.slug <> 'contribuicao-livre' OR g.slug IS NULL)"];
        $params     = [];

        if ($search !== '') {
            $conditions[] = 'g.name LIKE :search';
            $params[':search'] = '%' . $search . '%';
        }

        if ($status === 'active') {
            $conditions[] = 'g.is_active = 1';
        } elseif ($status === 'inactive') {
            $conditions[] = 'g.is_active = 0';
        }
        // 'all' — no status condition

        // Detecta purchase_confirmed pra separar reservas pendentes das
        // já confirmadas como compradas. Sem a coluna (banco legado),
        // tudo continua caindo em "reserved" como antes.
        $hasPc = false;
        try {
            $check = $this->query("SHOW COLUMNS FROM reservations LIKE 'purchase_confirmed'")->fetch();
            $hasPc = $check !== false;
        } catch (\Throwable $e) { /* trata como ausente */ }

        $reservedPendingCond = $hasPc
            ? "r.action_type = 'reserved' AND r.status = 'active' AND (r.purchase_confirmed IS NULL OR r.purchase_confirmed = 0)"
            : "r.action_type = 'reserved' AND r.status = 'active'";
        $boughtCond = $hasPc
            ? "r.action_type = 'reserved' AND r.status = 'active' AND r.purchase_confirmed = 1"
            : "1 = 0";

        $where = $conditions ? ('WHERE ' . implode(' AND ', $conditions)) : '';
        $sql = "SELECT
                    g.*,
                    tr.reserved_by,
                    tr.bought_by,
                    tr.pix_by,
                    tr.reserved_count,
                    tr.bought_count,
                    tr.pix_count
                FROM gifts g
                LEFT JOIN (
                    SELECT
                        r.gift_id,
                        GROUP_CONCAT(DISTINCT CASE
                            WHEN {$reservedPendingCond}
                            THEN CONCAT(u.name, ' <', u.email, '>')
                            ELSE NULL END SEPARATOR ' | ') AS reserved_by,
                        GROUP_CONCAT(DISTINCT CASE
                            WHEN {$boughtCond}
                            THEN CONCAT(u.name, ' <', u.email, '>')
                            ELSE NULL END SEPARATOR ' | ') AS bought_by,
                        GROUP_CONCAT(DISTINCT CASE
                            WHEN r.action_type = 'pix' AND r.status = 'active'
                            THEN CONCAT(u.name, ' <', u.email, '>')
                            ELSE NULL END SEPARATOR ' | ') AS pix_by,
                        SUM(CASE WHEN {$reservedPendingCond} THEN 1 ELSE 0 END) AS reserved_count,
                        SUM(CASE WHEN {$boughtCond}           THEN 1 ELSE 0 END) AS bought_count,
                        SUM(CASE WHEN r.action_type = 'pix' AND r.status = 'active' THEN 1 ELSE 0 END) AS pix_count
                    FROM reservations r
                    INNER JOIN users u ON u.id = r.user_id
                    GROUP BY r.gift_id
                ) tr ON tr.gift_id = g.id
                {$where}
                ORDER BY g.id DESC";

        return $this->query($sql, $params)->fetchAll();
    }

    /**
     * Returns a single gift by ID or false if not found.
     */
    public function findById(int $id): array|false {
        return $this->query(
            'SELECT * FROM gifts WHERE id = :id LIMIT 1',
            [':id' => $id]
        )->fetch();
    }

    /**
     * Sentinel "Contribuição livre" gift used when the guest sends a PIX
     * without picking a specific item. Hidden from public lists
     * (is_active = 0) but referenced by the reservation/payment rows so
     * existing flows keep working without nullable FKs.
     *
     * Returns the existing row if already created, otherwise creates and
     * returns it. Idempotent.
     */
    public function findOrCreateFreeContribution(): array {
        $slug = 'contribuicao-livre';

        $existing = $this->query(
            'SELECT * FROM gifts WHERE slug = :slug LIMIT 1',
            [':slug' => $slug]
        )->fetch();
        if ($existing) {
            return $existing;
        }

        $newId = $this->insert([
            'name'          => 'Contribuição livre',
            'slug'          => $slug,
            'description'   => 'PIX livre — convidado mandou um valor sem reservar item específico.',
            'category'      => 'Contribuição',
            'type'          => 'multi',
            'price'         => 0,
            'image_path'    => null,
            'quantity'      => 9999,
            'available_qty' => 9999,
            'featured'      => 0,
            'is_active'     => 0,
            'sort_order'    => 9999,
            'redirect_url'  => null,
        ]);
        $row = $this->findById($newId);
        return $row ?: [
            'id'        => $newId,
            'name'      => 'Contribuição livre',
            'slug'      => $slug,
            'is_active' => 0,
        ];
    }

    /**
     * Returns a single active gift with the current user's reservation/pix
     * action (if any), used by the Pix page when arriving from a card link.
     */
    public function findActiveForUser(int $id, int $userId = 0): array|false {
        $sql = 'SELECT g.*, us.action_type AS user_action_type
                FROM gifts g
                LEFT JOIN (
                    SELECT gift_id, MAX(action_type) AS action_type
                    FROM reservations
                    WHERE user_id = :user_id AND status = :status
                    GROUP BY gift_id
                ) us ON us.gift_id = g.id
                WHERE g.id = :id AND g.is_active = 1
                LIMIT 1';
        return $this->query($sql, [
            ':id'      => $id,
            ':user_id' => $userId,
            ':status'  => 'active',
        ])->fetch();
    }

    /**
     * Inserts a new gift and returns the new row ID.
     *
     * Expected $data keys:
     *   name, slug, description, category, type, price,
     *   quantity (total), available_qty, featured, is_active, sort_order
     */
    public function insert(array $data): int {
        $this->query(
            'INSERT INTO gifts
                (name, slug, description, category, type, price, image_path,
                 quantity, available_qty, featured, is_active, sort_order, redirect_url)
             VALUES
                (:name, :slug, :description, :category, :type, :price, :image_path,
                 :quantity, :available_qty, :featured, :is_active, :sort_order, :redirect_url)',
            [
                ':name'          => $data['name']          ?? '',
                ':slug'          => $data['slug']          ?? '',
                ':description'   => $data['description']  ?? null,
                ':category'      => $data['category']      ?? null,
                ':type'          => $data['type']          ?? 'single',
                ':price'         => $data['price']         ?? 0,
                ':image_path'    => $data['image_path']    ?? null,
                ':quantity'      => $data['quantity']      ?? 0,
                ':available_qty' => $data['available_qty'] ?? 0,
                ':featured'      => $data['featured']      ?? 0,
                ':is_active'     => $data['is_active']     ?? 1,
                ':sort_order'    => $data['sort_order']    ?? 0,
                ':redirect_url'  => $data['redirect_url']  ?? null,
            ]
        );
        return (int) $this->db->lastInsertId();
    }

    /**
     * Updates an existing gift by ID.
     * Only keys present in $data are updated; updated_at is always refreshed.
     */
    public function update(int $id, array $data): void {
        if (empty($data)) {
            return;
        }

        $allowed = [
            'name', 'slug', 'description', 'category', 'type', 'price',
            'image_path', 'quantity', 'available_qty', 'featured',
            'is_active', 'sort_order', 'redirect_url',
        ];

        $setClauses = [];
        $params     = [':id' => $id];

        foreach ($data as $key => $value) {
            if (!in_array($key, $allowed, true)) {
                continue; // silently skip unknown columns
            }
            $setClauses[]      = "`{$key}` = :{$key}";
            $params[":{$key}"] = $value;
        }

        if (empty($setClauses)) {
            return;
        }

        $setClauses[] = 'updated_at = NOW()';
        $setStr       = implode(', ', $setClauses);

        $this->query("UPDATE gifts SET {$setStr} WHERE id = :id", $params);
    }

    /**
     * Deletes a gift by ID.
     * Caller must check reservationCount() before calling this.
     */
    public function delete(int $id): bool {
        $this->query('DELETE FROM gifts WHERE id = :id', [':id' => $id]);
        return true;
    }

    /**
     * Flips the is_active flag for a gift.
     */
    public function toggleActive(int $id): void {
        $gift = $this->findById($id);
        if (!$gift) {
            return;
        }
        $newValue = $gift['is_active'] ? 0 : 1;
        $this->query(
            'UPDATE gifts SET is_active = :is_active, updated_at = NOW() WHERE id = :id',
            [':is_active' => $newValue, ':id' => $id]
        );
    }

    /**
     * Returns the number of active reservations for a gift.
     * Used by AdminGiftController::delete() to block deletion when reservations exist.
     */
    public function reservationCount(int $id): int {
        return (int) $this->query(
            'SELECT COUNT(*) FROM reservations WHERE gift_id = :id AND status = :status',
            [':id' => $id, ':status' => 'active']
        )->fetchColumn();
    }

    /**
     * Aggregated counters for the admin dashboard. Excludes the
     * "contribuicao-livre" sentinel from the gift counts so the dashboard
     * reflects only real listed presentes.
     */
    public function adminStats(): array {
        $row = $this->query(
            "SELECT
                COUNT(*) AS total,
                SUM(CASE WHEN is_active = 1 THEN 1 ELSE 0 END)                                    AS active_count,
                SUM(CASE WHEN is_active = 1 AND available_qty > 0 THEN 1 ELSE 0 END)              AS available_count,
                SUM(CASE WHEN is_active = 1 AND available_qty = 0 THEN 1 ELSE 0 END)              AS sold_out_count,
                SUM(CASE WHEN is_active = 1 THEN available_qty ELSE 0 END)                       AS available_units
             FROM gifts
             WHERE slug <> 'contribuicao-livre' OR slug IS NULL"
        )->fetch();
        return [
            'total'           => (int) ($row['total']            ?? 0),
            'active'          => (int) ($row['active_count']     ?? 0),
            'available'       => (int) ($row['available_count']  ?? 0),
            'sold_out'        => (int) ($row['sold_out_count']   ?? 0),
            'available_units' => (int) ($row['available_units']  ?? 0),
        ];
    }
}
