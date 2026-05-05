<?php
/** @var array  $giftStats     keys: total, active, available, sold_out, available_units */
/** @var array  $reservSummary keys: active_reserved, active_pix, cancelled_total, total */
/** @var array  $paymentStats  keys: total, approved_count, approved_total, pending_count, failed_count */
/** @var array  $recent        list of recent active reservations w/ user/gift/paid_amount */
/** @var \Closure $e */

$giftStats     = $giftStats     ?? ['total' => 0, 'active' => 0, 'available' => 0, 'sold_out' => 0, 'available_units' => 0];
$reservSummary = $reservSummary ?? ['active_reserved' => 0, 'active_pix' => 0, 'cancelled_total' => 0, 'total' => 0];
$paymentStats  = $paymentStats  ?? ['approved_count' => 0, 'approved_total' => 0.0, 'pending_count' => 0, 'failed_count' => 0];
$recent        = $recent        ?? [];

$money = static fn (float $v): string => 'R$ ' . number_format($v, 2, ',', '.');
?>
<div class="admin-page-head mb-4 d-flex flex-wrap align-items-end justify-content-between gap-3">
    <div>
        <h1 class="fs-3 fw-semibold mb-1">Dashboard</h1>
        <p class="text-muted mb-0">Visão geral da lista, das reservas e das contribuições via PIX.</p>
    </div>
    <a href="/admin/gifts/create" class="btn btn-primary btn-sm">
        <i class="bi bi-plus-lg"></i> Novo presente
    </a>
</div>

<!-- ── Counters ──────────────────────────────────────────────────── -->
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-muted small text-uppercase">Presentes</span>
                    <i class="bi bi-bag-heart text-primary"></i>
                </div>
                <div class="fs-2 fw-semibold"><?= $e((string) $giftStats['active']) ?></div>
                <div class="small text-muted">
                    de <?= $e((string) $giftStats['total']) ?> total
                    <?php if ($giftStats['sold_out'] > 0): ?>
                        · <?= $e((string) $giftStats['sold_out']) ?> esgotados
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-muted small text-uppercase">Disponíveis</span>
                    <i class="bi bi-check-circle text-success"></i>
                </div>
                <div class="fs-2 fw-semibold"><?= $e((string) $giftStats['available']) ?></div>
                <div class="small text-muted">
                    com <?= $e((string) $giftStats['available_units']) ?> unidades livres
                </div>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-muted small text-uppercase">Reservas</span>
                    <i class="bi bi-bookmark-star text-warning"></i>
                </div>
                <div class="fs-2 fw-semibold"><?= $e((string) $reservSummary['active_reserved']) ?></div>
                <div class="small text-muted">
                    ativas
                    <?php if ($reservSummary['cancelled_total'] > 0): ?>
                        · <?= $e((string) $reservSummary['cancelled_total']) ?> canceladas
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-muted small text-uppercase">PIX confirmados</span>
                    <i class="bi bi-qr-code text-info"></i>
                </div>
                <div class="fs-2 fw-semibold"><?= $e((string) $paymentStats['approved_count']) ?></div>
                <div class="small text-muted">
                    <?= $e($money((float) $paymentStats['approved_total'])) ?> arrecadados
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ── Secondary metrics ─────────────────────────────────────────── -->
<?php
$pendingPayments = $pendingPayments ?? [];

$timeAgo = static function (?string $datetime): string {
    if (!$datetime) return '';
    $ts = strtotime($datetime);
    if (!$ts) return '';
    $diff = max(0, time() - $ts);
    if ($diff < 60)        return 'agora há pouco';
    if ($diff < 3600)      return floor($diff / 60) . ' min atrás';
    if ($diff < 86400)     return floor($diff / 3600) . 'h atrás';
    return floor($diff / 86400) . 'd atrás';
};
?>

<?php if ($paymentStats['pending_count'] > 0): ?>
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <div class="d-flex align-items-center gap-3 mb-3">
                <div class="rounded-circle bg-warning-subtle text-warning d-flex align-items-center justify-content-center flex-shrink-0"
                     style="width: 44px; height: 44px;">
                    <i class="bi bi-hourglass-split fs-5"></i>
                </div>
                <div class="flex-grow-1">
                    <div class="fw-semibold">
                        <?= $e((string) $paymentStats['pending_count']) ?>
                        PIX <?= $paymentStats['pending_count'] === 1 ? 'pendente' : 'pendentes' ?>
                    </div>
                    <div class="small text-muted">
                        Cobranças geradas que ainda não foram pagas. Se for ruído, dá pra descartar.
                    </div>
                </div>
            </div>

            <?php if (!empty($pendingPayments)): ?>
                <ul class="list-group list-group-flush">
                    <?php foreach ($pendingPayments as $pp):
                        $amt   = (float) ($pp['amount'] ?? 0);
                        $when  = (string) ($pp['created_at'] ?? '');
                    ?>
                        <li class="list-group-item px-0 d-flex flex-wrap align-items-center justify-content-between gap-2">
                            <div class="me-3">
                                <div class="fw-semibold">
                                    <?= $e((string) ($pp['user_name'] ?? 'Convidado')) ?>
                                    <span class="text-muted fw-normal">·</span>
                                    <?= $e((string) ($pp['gift_name'] ?? 'Contribuição livre')) ?>
                                </div>
                                <div class="small text-muted">
                                    <?= $e($money($amt)) ?>
                                    <?php if ($when !== ''): ?>
                                        · <?= $e($timeAgo($when)) ?>
                                    <?php endif; ?>
                                    <?php if (!empty($pp['user_email'])): ?>
                                        · <?= $e((string) $pp['user_email']) ?>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <form method="POST"
                                  action="/admin/payments/<?= $e((string) $pp['id']) ?>/dismiss"
                                  onsubmit="return confirm('Descartar esse PIX pendente? Ele some do dashboard. A cobrança no MercadoPago expira sozinha em 24h se ninguém pagar.');">
                                <input type="hidden" name="csrf_token"
                                       value="<?= htmlspecialchars(\App\Middleware\CsrfMiddleware::token(), ENT_QUOTES, 'UTF-8') ?>">
                                <button type="submit" class="btn btn-sm btn-outline-secondary">
                                    <i class="bi bi-x-lg"></i> Descartar
                                </button>
                            </form>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>
    </div>
<?php endif; ?>

<?php if ($paymentStats['failed_count'] > 0): ?>
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body d-flex align-items-center gap-3">
            <div class="rounded-circle bg-danger-subtle text-danger d-flex align-items-center justify-content-center flex-shrink-0"
                 style="width: 44px; height: 44px;">
                <i class="bi bi-x-circle fs-5"></i>
            </div>
            <div>
                <div class="fw-semibold"><?= $e((string) $paymentStats['failed_count']) ?> PIX que não foram pagos</div>
                <div class="small text-muted">
                    Cobranças canceladas, recusadas ou que expiraram sem pagamento.
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>

<!-- ── Recent activity ───────────────────────────────────────────── -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white border-0 pt-3 pb-0 d-flex align-items-center justify-content-between">
        <h2 class="h6 mb-0">Atividade recente</h2>
        <a href="/admin/reservations" class="small text-decoration-none">Ver tudo →</a>
    </div>
    <div class="table-responsive">
        <table class="table align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th class="ps-3">Quando</th>
                    <th>Pessoa</th>
                    <th>Presente</th>
                    <th>Tipo</th>
                    <th class="text-end pe-3">Valor</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($recent)): ?>
                    <tr>
                        <td colspan="5" class="text-center text-muted py-4">
                            Nada por aqui ainda. Quando alguém reservar um presente ou mandar PIX, vai aparecer aqui.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($recent as $r):
                        $when = $r['created_at']
                            ? date('d/m H:i', strtotime((string) $r['created_at']))
                            : '—';
                        $isPix = ((string) $r['action_type']) === 'pix';
                        $paid  = isset($r['paid_amount']) && $r['paid_amount'] !== null
                            ? (float) $r['paid_amount']
                            : null;
                        $price = (float) ($r['gift_price'] ?? 0);
                    ?>
                        <tr>
                            <td class="ps-3 text-nowrap small text-muted"><?= $e($when) ?></td>
                            <td><?= $e((string) ($r['user_name'] ?? '—')) ?></td>
                            <td><?= $e((string) ($r['gift_name'] ?? '—')) ?></td>
                            <td>
                                <?php if ($isPix): ?>
                                    <span class="badge text-bg-info">PIX</span>
                                <?php else: ?>
                                    <span class="badge text-bg-primary">Reservado</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-end pe-3 text-nowrap fw-semibold">
                                <?php if ($isPix && $paid !== null): ?>
                                    <?= $e($money($paid)) ?>
                                <?php elseif ($price > 0): ?>
                                    <span class="text-muted fw-normal"><?= $e($money($price)) ?></span>
                                <?php else: ?>
                                    <span class="text-muted">—</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- ── Quick actions ─────────────────────────────────────────────── -->
<div class="row g-3">
    <div class="col-sm-6 col-lg-3">
        <a href="/admin/gifts" class="card border-0 shadow-sm h-100 text-decoration-none text-reset">
            <div class="card-body d-flex align-items-center gap-3">
                <i class="bi bi-list-ul fs-4 text-primary"></i>
                <div>
                    <div class="fw-semibold">Gerenciar presentes</div>
                    <div class="small text-muted">Editar, ativar ou esgotar itens.</div>
                </div>
            </div>
        </a>
    </div>
    <div class="col-sm-6 col-lg-3">
        <a href="/admin/reservations" class="card border-0 shadow-sm h-100 text-decoration-none text-reset">
            <div class="card-body d-flex align-items-center gap-3">
                <i class="bi bi-people fs-4 text-warning"></i>
                <div>
                    <div class="fw-semibold">Ver reservas e PIX</div>
                    <div class="small text-muted">Quem reservou e quanto contribuiu.</div>
                </div>
            </div>
        </a>
    </div>
    <div class="col-sm-6 col-lg-3">
        <a href="/admin/pix" class="card border-0 shadow-sm h-100 text-decoration-none text-reset">
            <div class="card-body d-flex align-items-center gap-3">
                <i class="bi bi-qr-code fs-4 text-info"></i>
                <div>
                    <div class="fw-semibold">Configurar PIX</div>
                    <div class="small text-muted">Credenciais MercadoPago e dados públicos.</div>
                </div>
            </div>
        </a>
    </div>
    <div class="col-sm-6 col-lg-3">
        <a href="/admin/settings" class="card border-0 shadow-sm h-100 text-decoration-none text-reset">
            <div class="card-body d-flex align-items-center gap-3">
                <i class="bi bi-gear fs-4 text-secondary"></i>
                <div>
                    <div class="fw-semibold">Configurações</div>
                    <div class="small text-muted">Nome, gênero, idade, data e local.</div>
                </div>
            </div>
        </a>
    </div>
</div>
