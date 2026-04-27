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
                    $status     = (string) ($reservation['status'] ?? 'reserved');
                    $isReserved = $status === 'reserved';

                    $statusLabel = match ($status) {
                        'reserved'  => 'Reservado · você tem 4 dias e 12h pra confirmar',
                        'pix'       => 'Contribuição via PIX registrada',
                        'confirmed' => 'Confirmado · obrigada!',
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
                    $price     = (float) ($reservation['price'] ?? 0);
                    $delay     = ($i * 100) . 'ms';
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
                        <span class="reservation__price">
                            <?= $price > 0
                                ? 'R$ ' . $e(number_format($price, ($price == (int) $price ? 0 : 2), ',', '.'))
                                : '—' ?>
                        </span>
                        <div class="reservation__actions">
                            <?php if ($isReserved): ?>
                                <form method="POST" action="/my-gifts/cancel">
                                    <input type="hidden" name="csrf_token"
                                           value="<?= htmlspecialchars(\App\Middleware\CsrfMiddleware::token(), ENT_QUOTES, 'UTF-8') ?>">
                                    <input type="hidden" name="reservation_id"
                                           value="<?= $e((string) ($reservation['id'] ?? '')) ?>">
                                    <button type="submit" class="btn-ghost-sm">Cancelar reserva</button>
                                </form>
                                <a href="/pix" class="btn-solid-sm">Pagar com Pix →</a>
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
