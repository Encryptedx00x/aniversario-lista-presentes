<?php
/**
 * _cards.php — Card grid partial.
 *
 * Used by:
 *   1. app/Views/public/gifts/index.php (PHP require, initial render)
 *   2. GiftController::filter() (require + exit, AJAX response)
 *
 * Expects: $gifts (array), $e (closure for htmlspecialchars)
 */
?>
<?php if (empty($gifts)): ?>
<div class="col-12 text-center py-5 text-muted">
    <i class="bi bi-gift fs-1 d-block mb-3"></i>
    <p class="fs-5 fw-semibold">Nenhum presente encontrado</p>
    <p class="small">Tente ajustar os filtros ou a busca.</p>
</div>
<?php else: ?>
<?php foreach ($gifts as $gift): ?>
<?php
    $isSoldOut  = (int)$gift['available_qty'] <= 0;
    $isFeatured = (bool)$gift['featured'];
    $cardClass  = $isSoldOut ? 'gift-card h-100 shadow-sm gift-card--esgotado' : 'gift-card h-100 shadow-sm';
?>
<div class="col">
    <div class="card <?= $cardClass ?>">
        <div class="position-relative">
            <?php if ($gift['image_path']): ?>
                <img src="<?= $e($gift['image_path']) ?>"
                     class="card-img-top"
                     alt="<?= $e($gift['name']) ?>">
            <?php else: ?>
                <div class="gift-card__placeholder">
                    <i class="bi bi-image" style="font-size: 2rem;"></i>
                </div>
            <?php endif; ?>
            <?php if ($isFeatured): ?>
                <span class="badge bg-warning text-dark gift-card__featured-badge">DESTAQUE</span>
            <?php endif; ?>
        </div>
        <div class="card-body d-flex flex-column">
            <div class="gift-card__meta mb-2">
                <?php if ($gift['category']): ?>
                    <span class="showcase-chip showcase-chip--mini"><span>Categoria</span><strong><?= $e($gift['category']) ?></strong></span>
                <?php endif; ?>
                <span class="showcase-chip showcase-chip--mini"><span>Status</span><strong><?= $isSoldOut ? 'Esgotado' : 'Disponível' ?></strong></span>
            </div>
            <h5 class="card-title fs-5 fw-semibold"><?= $e($gift['name']) ?></h5>
            <?php if ($gift['description']): ?>
                <p class="card-text small text-muted"
                   style="overflow:hidden;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical">
                    <?= $e($gift['description']) ?>
                </p>
            <?php endif; ?>
            <?php if ((float)$gift['price'] > 0): ?>
                <p class="fw-semibold text-primary mb-1">
                    R$ <?= $e(number_format((float)$gift['price'], 2, ',', '.')) ?>
                </p>
            <?php endif; ?>
            <?php if (!empty($gift['purchase_url'])): ?>
                <a href="<?= $e($gift['purchase_url']) ?>"
                   target="_blank"
                   rel="noopener noreferrer"
                   class="small text-decoration-underline mb-2 d-inline-block">
                    Ver link do produto
                </a>
            <?php endif; ?>
            <p class="small text-muted mt-auto mb-0">
                Disponível: <?= $e($gift['available_qty']) ?>
            </p>
        </div>
        <div class="card-footer bg-transparent border-0 pb-3">
            <?php if ($isSoldOut): ?>
                <button class="btn btn-secondary w-100" disabled>Indisponível</button>
            <?php else: ?>
                <button class="btn btn-primary w-100 btn-choose-gift"
                        data-gift-id="<?= $e($gift['id']) ?>"
                        data-logged-in="<?= isset($_SESSION['user_id']) ? '1' : '0' ?>">
                    Escolher este presente
                </button>
                <?php if ((float)$gift['price'] > 0): ?>
                    <a class="btn btn-outline-success w-100 mt-2"
                       href="/pix?gift_id=<?= $e((string) $gift['id']) ?>&amount=<?= $e(number_format((float)$gift['price'], 2, '.', '')) ?>">
                        Dar este presente via Pix
                    </a>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php endforeach; ?>
<?php endif; ?>
