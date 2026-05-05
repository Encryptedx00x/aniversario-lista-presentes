<?php
/**
 * _cards.php — Editorial gift card grid partial.
 *
 * Used by:
 *   1. app/Views/public/gifts/index.php  (initial render — full list)
 *   2. app/Views/public/home.php         (featured slice — 3 cards, $cardSize='big')
 *   3. GiftController::filter()          (AJAX response — full HTML fragment)
 *
 * Expects:
 *   $gifts    — array of gift rows
 *   $e        — closure for htmlspecialchars
 *   $cardSize — optional: 'big' to render larger placeholder; default null
 */

$cardSize = $cardSize ?? null;

/**
 * Pick a deterministic background color per card from a small palette.
 * Done in PHP so cards stay colorful even without an image.
 */
$paletteColors = ['#1B3A8B', '#2A2A2A', '#7A4B1F', '#A03426', '#3F3F3F', '#0F0F12', '#5C2A8F', '#1F5F4A'];
$paletteAccents = ['#6B3FCC', '#1B3A8B'];

if (empty($gifts)): ?>
    <div class="empty-state">
        <span class="empty-state__icon">✦</span>
        <p class="empty-state__title">Nenhum presente encontrado</p>
        <p class="empty-state__text">Tente ajustar os filtros ou a busca.</p>
    </div>
<?php else: ?>
    <?php foreach ($gifts as $i => $gift): ?>
        <?php
            $isSoldOut  = (int) $gift['available_qty'] <= 0;
            $isFeatured = (bool) ($gift['featured'] ?? false);
            $color      = $paletteColors[(int) ($gift['id'] ?? $i) % count($paletteColors)];
            $accent     = $paletteAccents[(int) ($gift['id'] ?? $i) % count($paletteAccents)];
            $words      = preg_split('/\s+/', trim((string) $gift['name'])) ?: [];
            $lastWord   = end($words) !== false ? (string) end($words) : '?';
            $initial    = mb_strtoupper(mb_substr($lastWord, 0, 1, 'UTF-8'), 'UTF-8');
            $cardClass  = 'gift-card';
            if ($cardSize === 'big') { $cardClass .= ' gift-card--big'; }
            if ($isSoldOut)          { $cardClass .= ' gift-card--esgotado'; }
            $delay      = ($i * 70) . 'ms';
            $price      = (float) ($gift['price'] ?? 0);
            $idStr      = str_pad((string) (int) ($gift['id'] ?? 0), 2, '0', STR_PAD_LEFT);
            $qty        = (int) $gift['available_qty'];
            $userAction = (string) ($gift['user_action_type'] ?? '');
            $isMine     = $userAction !== '';
        ?>
        <article class="<?= $cardClass ?>"
                 style="animation-delay: <?= $e($delay) ?>; --card-accent: <?= $e($accent) ?>;">
            <div class="gift-card__media" style="background: <?= $e($color) ?>;">
                <div class="gift-card__media-fill" aria-hidden="true"></div>
                <div class="gift-card__halo" aria-hidden="true"
                     style="background: radial-gradient(circle at 30% 30%, <?= $e($accent) ?>66, transparent 60%);"></div>

                <?php if (!empty($gift['image_path'])): ?>
                    <img src="<?= $e($gift['image_path']) ?>"
                         alt="<?= $e($gift['name']) ?>"
                         loading="lazy">
                <?php else: ?>
                    <div class="gift-card__placeholder" aria-hidden="true"><?= $e($initial) ?></div>
                <?php endif; ?>

                <?php if ($isFeatured && !$isSoldOut): ?>
                    <span class="gift-badge gift-badge--featured">★ favorito</span>
                <?php elseif ($isSoldOut): ?>
                    <span class="gift-badge gift-badge--out">já reservaram</span>
                <?php endif; ?>
            </div>

            <div class="gift-card__body">
                <div class="gift-card__meta">
                    <span>n° <?= $e($idStr) ?>
                        <?php if (!empty($gift['category'])): ?>
                            · <?= $e($gift['category']) ?>
                        <?php endif; ?>
                    </span>
                    <span class="<?= $isSoldOut ? 'gift-card__qty--out' : 'gift-card__qty' ?>"
                          style="<?= $isSoldOut ? '' : 'color: ' . $e($accent) . ';' ?>">
                        <?= $isSoldOut
                            ? 'indisponível'
                            : ($e($qty) . ' ' . ($qty === 1 ? 'vaga' : 'vagas')) ?>
                    </span>
                </div>

                <h3 class="gift-card__title"><?= $e($gift['name']) ?></h3>

                <?php if (!empty($gift['description'])): ?>
                    <p class="gift-card__desc"><?= $e($gift['description']) ?></p>
                <?php endif; ?>

                <div class="gift-card__foot">
                    <span class="gift-card__price">
                        <?= $price > 0
                            ? 'R$ ' . $e(number_format($price, ($price == (int) $price ? 0 : 2), ',', '.'))
                            : '—' ?>
                    </span>
                    <?php if ($isMine): ?>
                        <button class="gift-card__cta" disabled>
                            <?= $userAction === 'pix' ? 'Contribuído via PIX' : 'Selecionado' ?>
                        </button>
                    <?php elseif ($isSoldOut): ?>
                        <button class="gift-card__cta" disabled>Indisponível</button>
                    <?php else: ?>
                        <div class="d-flex gap-2">
                            <button type="button"
                                    class="gift-card__cta btn-choose-gift"
                                    data-gift-id="<?= $e($gift['id']) ?>"
                                    data-logged-in="<?= isset($_SESSION['user_id']) ? '1' : '0' ?>"
                                    data-redirect-url="<?= $e($gift['redirect_url'] ?? '') ?>"
                                    style="--cta-hover: <?= $e($accent) ?>;">
                                Quero esse <span aria-hidden="true">→</span>
                            </button>
                            <a href="/pix?gift_id=<?= $e($gift['id']) ?>"
                               class="gift-card__cta btn-pix-gift"
                               data-gift-id="<?= $e($gift['id']) ?>"
                               data-logged-in="<?= isset($_SESSION['user_id']) ? '1' : '0' ?>">
                                Mandar pelo Pix
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </article>
    <?php endforeach; ?>
<?php endif; ?>
