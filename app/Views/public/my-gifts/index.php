<?php
/**
 * public/my-gifts/index.php
 * Variables expected: $reservations (array), $e (escape closure)
 */
?>
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="mb-0 fw-bold">Meus Presentes</h1>
        <a href="/gifts" class="btn btn-outline-primary btn-sm">Ver lista de presentes</a>
    </div>

    <?php if (empty($reservations)): ?>
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center py-5">
                <i class="bi bi-gift fs-1 d-block text-muted mb-3"></i>
                <h2 class="h5 mb-2">Você ainda não reservou presentes</h2>
                <p class="text-muted mb-4">Quando escolher um presente, ele aparecerá aqui para você acompanhar.</p>
                <a href="/gifts" class="btn btn-primary">Escolher presente</a>
            </div>
        </div>
    <?php else: ?>
        <div class="row g-4">
            <?php foreach ($reservations as $reservation): ?>
                <?php
                    $status = (string)($reservation['status'] ?? 'reserved');
                    $isReserved = $status === 'reserved';

                    $statusLabel = match ($status) {
                        'reserved'  => 'Reservado',
                        'confirmed' => 'Confirmado',
                        'cancelled' => 'Cancelado',
                        default     => ucfirst($status),
                    };

                    $statusClass = match ($status) {
                        'reserved'  => 'bg-warning text-dark',
                        'confirmed' => 'bg-success',
                        'cancelled' => 'bg-secondary',
                        default     => 'bg-light text-dark',
                    };

                    $createdRaw = (string)($reservation['created_at'] ?? '');
                    $createdAt = $createdRaw !== ''
                        ? date('d/m/Y H:i', strtotime($createdRaw))
                        : '-';
                ?>
                <div class="col-12 col-lg-6">
                    <div class="card h-100 shadow-sm">
                        <div class="row g-0 h-100">
                            <div class="col-4 col-md-3">
                                <?php if (!empty($reservation['image_path'])): ?>
                                    <img src="<?= $e($reservation['image_path']) ?>"
                                         class="img-fluid rounded-start h-100 w-100"
                                         style="object-fit: cover; min-height: 140px;"
                                         alt="<?= $e((string)($reservation['gift_name'] ?? 'Presente')) ?>">
                                <?php else: ?>
                                    <div class="d-flex align-items-center justify-content-center h-100 bg-light rounded-start text-muted"
                                         style="min-height: 140px;">
                                        <i class="bi bi-image fs-3"></i>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="col-8 col-md-9">
                                <div class="card-body h-100 d-flex flex-column">
                                    <div class="d-flex align-items-start justify-content-between gap-2 mb-2">
                                        <h2 class="h6 mb-0"><?= $e((string)($reservation['gift_name'] ?? 'Presente')) ?></h2>
                                        <span class="badge <?= $statusClass ?>"><?= $e($statusLabel) ?></span>
                                    </div>

                                    <p class="text-muted small mb-1">
                                        <strong>Categoria:</strong>
                                        <?= $e((string)($reservation['category'] ?? '-')) ?>
                                    </p>
                                    <p class="text-muted small mb-3">
                                        <strong>Data:</strong>
                                        <?= $e($createdAt) ?>
                                    </p>
                                    <?php if (!empty($reservation['purchase_url'])): ?>
                                        <p class="small mb-3">
                                            <a href="<?= $e((string) $reservation['purchase_url']) ?>"
                                               target="_blank"
                                               rel="noopener noreferrer">
                                                Abrir link de compra deste presente
                                            </a>
                                        </p>
                                    <?php endif; ?>

                                    <div class="mt-auto">
                                        <?php if ($isReserved): ?>
                                            <form method="POST" action="/my-gifts/cancel" class="d-inline">
                                                <input type="hidden"
                                                       name="csrf_token"
                                                       value="<?= htmlspecialchars(\App\Middleware\CsrfMiddleware::token(), ENT_QUOTES, 'UTF-8') ?>">
                                                <input type="hidden"
                                                       name="reservation_id"
                                                       value="<?= $e((string)($reservation['id'] ?? '')) ?>">
                                                <button type="submit" class="btn btn-outline-danger btn-sm">
                                                    Cancelar reserva
                                                </button>
                                            </form>
                                            <?php if (!empty($reservation['gift_id'])): ?>
                                                <a class="btn btn-outline-success btn-sm ms-2"
                                                   href="/pix?gift_id=<?= $e((string) $reservation['gift_id']) ?>&amount=<?= $e(number_format((float)($reservation['price'] ?? 0), 2, '.', '')) ?>">
                                                    Dar via Pix
                                                </a>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <p class="small text-muted mb-0">Esta reserva não pode mais ser cancelada.</p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
