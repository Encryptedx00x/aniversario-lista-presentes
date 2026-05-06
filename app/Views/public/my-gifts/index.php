<?php
/**
 * public/my-gifts/index.php — Editorial reservation list.
 * Variables expected: $reservations (array), $e (escape closure).
 */
$activeNav = 'my';

$paletteColors  = ['#1B3A8B', '#2A2A2A', '#7A4B1F', '#A03426', '#3F3F3F', '#0F0F12', '#5C2A8F', '#1F5F4A'];
$paletteAccents = ['#6B3FCC', '#1B3A8B'];
?>
<main class="section--tight">

    <div class="page-head">
        <div class="page-eyebrow">Minha lista</div>
        <h1 class="page-title">O que você <em>já garantiu.</em></h1>
    </div>

    <?php if (empty($reservations)): ?>
        <div class="reservation" style="grid-template-columns: 1fr; padding: 56px 32px; text-align: center;">
            <div>
                <div class="empty-state__icon">✦</div>
                <h2 class="reservation__title" style="margin-bottom: 8px;">Você ainda não selecionou nenhum presente.</h2>
                <p class="reservation__desc" style="margin: 0 auto 22px; max-width: 360px;">
                    Você ainda não reservou presentes. Quando você escolher um presente, ele aparece aqui pra você acompanhar com calma.
                </p>
                <a href="/gifts" class="btn-solid">Ver a lista <span aria-hidden="true">→</span></a>
            </div>
        </div>
    <?php else: ?>
        <div class="reservations">
            <?php foreach ($reservations as $i => $reservation): ?>
                <?php
                    $status      = (string) ($reservation['status'] ?? 'reserved');
                    $actionType  = (string) ($reservation['action_type'] ?? '');
                    $isReserved  = $status === 'reserved';
                    $isPix       = $actionType === 'pix';

                    $price       = (float) ($reservation['price'] ?? 0);
                    $resQty      = max(1, (int) ($reservation['quantity'] ?? 1));
                    $redirectUrl = (string) ($reservation['redirect_url'] ?? '');
                    // Só ativa o fluxo "Já comprei / Não comprei" se a coluna
                    // purchase_confirmed existir no row (= migration aplicada).
                    $hasConfirmCol     = array_key_exists('purchase_confirmed', $reservation);
                    $purchaseRaw       = $reservation['purchase_confirmed'] ?? null;
                    $purchaseConfirmed = $purchaseRaw !== null ? (int) $purchaseRaw : null;
                    $needsConfirm      = $hasConfirmCol && $isReserved && !$isPix
                                         && $redirectUrl !== '' && $purchaseConfirmed === null;
                    $paidAmount  = isset($reservation['paid_amount']) && $reservation['paid_amount'] !== null
                        ? (float) $reservation['paid_amount']
                        : null;
                    $totalPrice  = $price * $resQty;

                    // Categoriza a contribuição PIX vs preço sugerido do presente
                    // (parcial, completa, ou acima — toda ajuda conta).
                    $pixTier = null;
                    if ($isPix && $paidAmount !== null && $price > 0) {
                        if ($paidAmount + 0.005 < $price) {
                            $pixTier = 'partial';
                        } elseif ($paidAmount > $price + 0.005) {
                            $pixTier = 'extra';
                        } else {
                            $pixTier = 'full';
                        }
                    }

                    $thanksLow = mb_strtolower($thanksWord ?? 'Obrigado', 'UTF-8');
                    $reservedLabel = $needsConfirm
                        ? 'Reservado · ainda preciso saber se você comprou'
                        : ($purchaseConfirmed === 1
                            ? 'Reservado e comprado · ' . $thanksLow . '!'
                            : 'Reservado');
                    $statusLabel = match ($status) {
                        'reserved'  => $reservedLabel,
                        'pix'       => match ($pixTier) {
                            'partial' => 'Contribuição parcial registrada · ' . $thanksLow . ' pelo apoio!',
                            'extra'   => 'Contribuição registrada com extra · que carinho, ' . $thanksLow . '!',
                            'full'    => 'Contribuição via PIX registrada · ' . $thanksLow . '!',
                            default   => 'Contribuição via PIX registrada',
                        },
                        'confirmed' => 'Confirmado · ' . $thanksLow . '!',
                        'cancelled' => 'Cancelada',
                        default     => ucfirst($status),
                    };

                    $statusClass = match ($status) {
                        'reserved'  => 'reservation__status--reserved',
                        'pix'       => 'reservation__status--confirmed',
                        'confirmed' => 'reservation__status--confirmed',
                        'cancelled' => 'reservation__status--cancelled',
                        default     => 'reservation__status--reserved',
                    };

                    $createdRaw = (string) ($reservation['created_at'] ?? '');
                    $createdAt  = $createdRaw !== '' ? date('d/m/Y · H:i', strtotime($createdRaw) ?: time()) : '-';

                    $color  = $paletteColors [(int) ($reservation['gift_id']  ?? $i) % count($paletteColors)];
                    $accent = $paletteAccents[(int) ($reservation['gift_id']  ?? $i) % count($paletteAccents)];

                    $name      = (string) ($reservation['gift_name'] ?? 'Presente');
                    $words     = preg_split('/\s+/', trim($name)) ?: [];
                    $lastWord  = end($words) !== false ? (string) end($words) : '?';
                    $initial   = mb_strtoupper(mb_substr($lastWord, 0, 1, 'UTF-8'), 'UTF-8');
                    $idStr     = str_pad((string) (int) ($reservation['gift_id'] ?? 0), 2, '0', STR_PAD_LEFT);
                    $delay     = ($i * 100) . 'ms';

                    // Helper local pra formatar valor: sem casas se inteiro, 2 se fracionado.
                    $fmt = static function (float $v): string {
                        return number_format($v, ($v == (int) $v ? 0 : 2), ',', '.');
                    };
                ?>
                <article class="reservation" style="animation-delay: <?= $e($delay) ?>;">
                    <div class="reservation__media" style="background: <?= $e($color) ?>;">
                        <div class="reservation__halo" aria-hidden="true"
                             style="background: radial-gradient(circle at 30% 30%, <?= $e($accent) ?>66, transparent 60%);"></div>
                        <?php if (!empty($reservation['image_path'])): ?>
                            <img src="<?= $e($reservation['image_path']) ?>" alt="<?= $e($name) ?>" loading="lazy">
                        <?php else: ?>
                            <div class="reservation__placeholder" aria-hidden="true"><?= $e($initial) ?></div>
                        <?php endif; ?>
                    </div>

                    <div class="reservation__body">
                        <div class="reservation__meta">
                            n° <?= $e($idStr) ?>
                            <?php if (!empty($reservation['category'])): ?>
                                · <?= $e($reservation['category']) ?>
                            <?php endif; ?>
                            <?php if ($resQty > 1): ?>
                                · <?= $e((string) $resQty) ?>× unidades
                            <?php endif; ?>
                            · reservado em <?= $e($createdAt) ?>
                        </div>
                        <h3 class="reservation__title"><?= $e($name) ?></h3>
                        <?php if (!empty($reservation['description'])): ?>
                            <p class="reservation__desc"><?= $e($reservation['description']) ?></p>
                        <?php endif; ?>

                        <div class="reservation__status <?= $e($statusClass) ?>">
                            <span class="reservation__status-dot" aria-hidden="true"></span>
                            <?= $e($statusLabel) ?>
                        </div>
                    </div>

                    <div class="reservation__side">
                        <?php if ($isPix && $paidAmount !== null): ?>
                            <span class="reservation__price"
                                  title="Valor enviado via PIX">
                                R$ <?= $e($fmt($paidAmount)) ?>
                            </span>
                            <?php if ($price > 0): ?>
                                <?php
                                    $rawPct  = ($paidAmount / $price) * 100;
                                    $fillPct = max(0, min(100, $rawPct));
                                ?>
                                <div class="pix-progress pix-progress--<?= $e($pixTier ?? 'full') ?>"
                                     role="progressbar"
                                     aria-valuenow="<?= $e((string) round($rawPct)) ?>"
                                     aria-valuemin="0"
                                     aria-valuemax="100"
                                     aria-label="Progresso da contribuição">
                                    <div class="pix-progress__fill" style="width: <?= $e((string) $fillPct) ?>%;"></div>
                                </div>
                                <?php if ($pixTier === 'partial' || $pixTier === 'extra'): ?>
                                    <span class="reservation__price-sub small text-muted">
                                        <?php if ($pixTier === 'partial'): ?>
                                            <?= $e((string) round($rawPct)) ?>% de R$ <?= $e($fmt($price)) ?> sugerido
                                        <?php else: ?>
                                            +R$ <?= $e($fmt($paidAmount - $price)) ?> acima do sugerido
                                        <?php endif; ?>
                                    </span>
                                <?php endif; ?>
                            <?php endif; ?>
                        <?php else: ?>
                            <span class="reservation__price">
                                <?php if ($totalPrice > 0): ?>
                                    R$ <?= $e($fmt($totalPrice)) ?>
                                <?php else: ?>
                                    —
                                <?php endif; ?>
                            </span>
                            <?php if ($resQty > 1 && $price > 0): ?>
                                <span class="reservation__price-sub small text-muted">
                                    <?= $e((string) $resQty) ?>× R$ <?= $e($fmt($price)) ?>
                                </span>
                            <?php endif; ?>
                        <?php endif; ?>
                        <div class="reservation__actions">
                            <?php if ($isReserved && !$isPix): ?>
                                <?php if ($redirectUrl !== ''): ?>
                                    <a href="<?= $e($redirectUrl) ?>"
                                       class="btn-solid-sm"
                                       target="_blank" rel="noopener">
                                        Abrir link da loja →
                                    </a>
                                <?php endif; ?>
                                <?php if ($needsConfirm): ?>
                                    <form method="POST"
                                          action="/my-gifts/<?= $e((string) ($reservation['id'] ?? '')) ?>/confirm-purchase"
                                          class="d-flex flex-wrap gap-2 m-0">
                                        <input type="hidden" name="csrf_token"
                                               value="<?= htmlspecialchars(\App\Middleware\CsrfMiddleware::token(), ENT_QUOTES, 'UTF-8') ?>">
                                        <button type="submit" name="confirmed" value="1" class="btn-solid-sm">
                                            Já comprei
                                        </button>
                                        <button type="submit" name="confirmed" value="0" class="btn-ghost-sm">
                                            Não comprei
                                        </button>
                                    </form>
                                <?php else: ?>
                                    <?php if ($purchaseConfirmed !== 1): ?>
                                        <form method="POST" action="/my-gifts/cancel" class="m-0">
                                            <input type="hidden" name="csrf_token"
                                                   value="<?= htmlspecialchars(\App\Middleware\CsrfMiddleware::token(), ENT_QUOTES, 'UTF-8') ?>">
                                            <input type="hidden" name="reservation_id"
                                                   value="<?= $e((string) ($reservation['id'] ?? '')) ?>">
                                            <button type="submit" class="btn-ghost-sm">Cancelar reserva</button>
                                        </form>
                                        <a href="/pix?gift_id=<?= $e((string) (int) ($reservation['gift_id'] ?? 0)) ?><?= $resQty > 1 ? '&qty=' . $e((string) $resQty) : '' ?>"
                                           class="btn-solid-sm">Pagar com Pix →</a>
                                    <?php else: ?>
                                        <p class="small text-muted mb-0">Compra confirmada — obrigado pelo carinho.</p>
                                    <?php endif; ?>
                                <?php endif; ?>
                            <?php else: ?>
                                <p class="small text-muted mb-0">Esta reserva não pode mais ser cancelada.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

</main>
