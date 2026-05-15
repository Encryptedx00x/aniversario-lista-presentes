<?php
/** @var array $reservations */
/** @var array $summary */
/** @var array $filters */
/** @var \Closure $e */

$action = (string) ($filters['action_type'] ?? 'all');
$status = (string) ($filters['status']      ?? 'active');

// Aceita o row inteiro pra distinguir 'reservado pendente' de 'reservado
// e comprado' (purchase_confirmed=1) — esse último vira badge verde
// "Comprado" pra Dyogo saber que a compra externa já foi confirmada
// pelo convidado em /my-gifts.
$badgeForAction = function (array $r): string {
    $action    = (string) ($r['action_type'] ?? '');
    $confirmed = isset($r['purchase_confirmed']) && (int) $r['purchase_confirmed'] === 1;
    if ($action === 'pix') {
        return '<span class="badge text-bg-info">PIX</span>';
    }
    return $confirmed
        ? '<span class="badge text-bg-success" title="Convidado confirmou a compra em Minha Lista">Comprado</span>'
        : '<span class="badge text-bg-primary">Reservado</span>';
};
$badgeForStatus = function (string $s): string {
    return match ($s) {
        'active'    => '<span class="badge text-bg-success">Ativa</span>',
        'cancelled' => '<span class="badge text-bg-secondary">Cancelada</span>',
        default     => '<span class="badge text-bg-light">' . htmlspecialchars($s, ENT_QUOTES, 'UTF-8') . '</span>',
    };
};
?>
<div class="admin-page-head mb-4">
    <h1 class="fs-3 fw-semibold mb-1">Reservas e Contribuições</h1>
    <p class="text-muted mb-0">Quem reservou um presente e quem contribuiu via PIX. Atualizado em tempo real.</p>
</div>

<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="text-muted small text-uppercase">Reservas pendentes</div>
                <div class="fs-3 fw-semibold"><?= $e($summary['active_pending'] ?? 0) ?></div>
                <div class="small text-muted">aguardando confirmação</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="text-muted small text-uppercase">Comprados</div>
                <div class="fs-3 fw-semibold"><?= $e($summary['bought_count'] ?? 0) ?></div>
                <div class="small text-muted">confirmaram em Minha Lista</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="text-muted small text-uppercase">PIX confirmados</div>
                <div class="fs-3 fw-semibold"><?= $e($summary['active_pix'] ?? 0) ?></div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="text-muted small text-uppercase">Canceladas</div>
                <div class="fs-3 fw-semibold"><?= $e($summary['cancelled_total'] ?? 0) ?></div>
                <div class="small text-muted"><?= $e($summary['total'] ?? 0) ?> total</div>
            </div>
        </div>
    </div>
</div>

<form method="GET" action="/admin/reservations" class="d-flex flex-wrap gap-2 mb-3 align-items-end">
    <div>
        <label class="form-label small text-muted mb-1" for="action_type">Tipo</label>
        <select id="action_type" name="action_type" class="form-select form-select-sm" onchange="this.form.submit()">
            <option value="all"      <?= $action === 'all'      ? 'selected' : '' ?>>Todos</option>
            <option value="reserved" <?= $action === 'reserved' ? 'selected' : '' ?>>Reservas</option>
            <option value="pix"      <?= $action === 'pix'      ? 'selected' : '' ?>>PIX</option>
        </select>
    </div>
    <div>
        <label class="form-label small text-muted mb-1" for="status">Status</label>
        <select id="status" name="status" class="form-select form-select-sm" onchange="this.form.submit()">
            <option value="active"    <?= $status === 'active'    ? 'selected' : '' ?>>Ativas</option>
            <option value="cancelled" <?= $status === 'cancelled' ? 'selected' : '' ?>>Canceladas</option>
            <option value="all"       <?= $status === 'all'       ? 'selected' : '' ?>>Todas</option>
        </select>
    </div>
</form>

<div class="card border-0 shadow-sm">
    <div class="table-responsive">
        <table class="table align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th class="ps-3">Quando</th>
                    <th>Pessoa</th>
                    <th>Presente</th>
                    <th>Tipo</th>
                    <th>Status</th>
                    <th class="text-end">Valor</th>
                    <th class="text-end pe-3">Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($reservations)): ?>
                    <tr>
                        <td colspan="7" class="text-center text-muted py-4">
                            Nenhum registro com esses filtros.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($reservations as $r):
                        $created = $r['created_at']
                            ? date('d/m/Y H:i', strtotime((string) $r['created_at']))
                            : '—';
                        $price       = (float) ($r['gift_price'] ?? 0);
                        $isPix       = ((string) $r['action_type']) === 'pix';
                        $paidAmount  = isset($r['paid_amount']) && $r['paid_amount'] !== null
                            ? (float) $r['paid_amount']
                            : null;

                        $pixTier = null;
                        if ($isPix && $paidAmount !== null && $price > 0) {
                            if ($paidAmount + 0.005 < $price)      $pixTier = 'partial';
                            elseif ($paidAmount > $price + 0.005)  $pixTier = 'extra';
                            else                                   $pixTier = 'full';
                        }

                        $tierBadge = match ($pixTier) {
                            'partial' => '<span class="badge text-bg-warning ms-1" title="Valor abaixo do sugerido">parcial</span>',
                            'extra'   => '<span class="badge text-bg-success ms-1" title="Valor acima do sugerido">+ extra</span>',
                            default   => '',
                        };

                        $fmt = static fn (float $v): string =>
                            number_format($v, 2, ',', '.');
                    ?>
                        <tr>
                            <td class="ps-3 text-nowrap small text-muted"><?= $e($created) ?></td>
                            <td>
                                <div class="fw-semibold"><?= $e($r['user_name']) ?></div>
                                <div class="small text-muted"><?= $e($r['user_email']) ?></div>
                                <?php if (!empty($r['user_phone'])): ?>
                                    <div class="small text-muted"><?= $e($r['user_phone']) ?></div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="fw-semibold"><?= $e($r['gift_name']) ?></div>
                                <?php if (!empty($r['gift_category'])): ?>
                                    <div class="small text-muted"><?= $e($r['gift_category']) ?></div>
                                <?php endif; ?>
                            </td>
                            <td><?= $badgeForAction($r) ?></td>
                            <td><?= $badgeForStatus((string) $r['status']) ?></td>
                            <td class="text-end text-nowrap">
                                <?php if ($isPix && $paidAmount !== null): ?>
                                    <div class="fw-semibold">
                                        R$ <?= $e($fmt($paidAmount)) ?><?= $tierBadge ?>
                                    </div>
                                    <?php if ($price > 0 && $pixTier !== 'full'): ?>
                                        <div class="small text-muted">de R$ <?= $e($fmt($price)) ?> sugerido</div>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <?= $price > 0
                                        ? 'R$ ' . $e($fmt($price))
                                        : '—' ?>
                                <?php endif; ?>
                            </td>
                            <td class="text-end pe-3 text-nowrap">
                                <div class="d-flex gap-1 justify-content-end flex-wrap">
                                    <form method="POST"
                                          action="/admin/reservations/<?= $e((string) (int) $r['id']) ?>/delete"
                                          class="m-0"
                                          onsubmit="return confirm('Apagar essa reserva? <?= ($r['status'] === 'active' && $r['action_type'] === 'reserved') ? 'O estoque será devolvido.' : 'Essa ação não pode ser desfeita.' ?>');">
                                        <input type="hidden" name="csrf_token"
                                               value="<?= htmlspecialchars(\App\Middleware\CsrfMiddleware::token(), ENT_QUOTES, 'UTF-8') ?>">
                                        <input type="hidden" name="action_type" value="<?= $e($action) ?>">
                                        <input type="hidden" name="status"      value="<?= $e($status) ?>">
                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Apaga a reserva">
                                            Apagar reserva
                                        </button>
                                    </form>
                                    <?php if (!empty($r['payment_id'])): ?>
                                        <form method="POST"
                                              action="/admin/payments/<?= $e((string) (int) $r['payment_id']) ?>/delete"
                                              class="m-0"
                                              onsubmit="return confirm('Apagar permanentemente esse pagamento? O valor sai dos totais arrecadados.');">
                                            <input type="hidden" name="csrf_token"
                                                   value="<?= htmlspecialchars(\App\Middleware\CsrfMiddleware::token(), ENT_QUOTES, 'UTF-8') ?>">
                                            <input type="hidden" name="action_type" value="<?= $e($action) ?>">
                                            <input type="hidden" name="status"      value="<?= $e($status) ?>">
                                            <button type="submit" class="btn btn-sm btn-outline-danger" title="Apaga o registro de pagamento PIX">
                                                Apagar PIX
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
