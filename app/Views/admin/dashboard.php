<h1 class="h3 mb-4">Dashboard</h1>
<?php
$stats = $stats ?? [];
$giftsTotal = (int) ($stats['gifts_total'] ?? 0);
$giftsAvailable = (int) ($stats['gifts_available'] ?? 0);
$giftsSoldOut = (int) ($stats['gifts_sold_out'] ?? 0);
$reservationsTotal = (int) ($stats['reservations_total'] ?? 0);
$pixTotal = (int) ($stats['pix_total'] ?? 0);
?>
<div class="row g-4 mb-4">
    <div class="col-sm-6 col-xl-2">
        <div class="card text-center h-100 dashboard-metric-card">
            <div class="card-body">
                <div class="display-6 fw-bold text-primary"><?= $e($giftsTotal) ?></div>
                <div class="text-muted small mt-1">Presentes</div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-2">
        <div class="card text-center h-100 dashboard-metric-card">
            <div class="card-body">
                <div class="display-6 fw-bold text-success"><?= $e($giftsAvailable) ?></div>
                <div class="text-muted small mt-1">Disponíveis</div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-2">
        <div class="card text-center h-100 dashboard-metric-card">
            <div class="card-body">
                <div class="display-6 fw-bold text-danger"><?= $e($giftsSoldOut) ?></div>
                <div class="text-muted small mt-1">Esgotados</div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card text-center h-100 dashboard-metric-card">
            <div class="card-body">
                <div class="display-6 fw-bold text-warning"><?= $e($reservationsTotal) ?></div>
                <div class="text-muted small mt-1">Reservas</div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card text-center h-100 dashboard-metric-card">
            <div class="card-body">
                <div class="display-6 fw-bold text-info"><?= $e($pixTotal) ?></div>
                <div class="text-muted small mt-1">Contribuições Pix</div>
            </div>
        </div>
    </div>
</div>
