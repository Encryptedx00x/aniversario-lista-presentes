<?php
/**
 * public/home.php — Home (hero + countdown + featured + thank-you note).
 *
 * Variables expected (from HomeController::index):
 *   $featured     — array of up to 3 gifts to highlight
 *   $countdown    — ['days' => int, 'percent' => int, 'totalGifts' => int, 'reservedCount' => int, 'daysToConfirm' => int, 'targetIso' => string]
 *   $brandInitial, $brandName, $brandLocation — used by layout
 */
$featured        = $featured        ?? [];
$countdown       = $countdown       ?? [
    'days'           => 47,
    'percent'        => 0,
    'totalGifts'     => 0,
    'reservedCount'  => 0,
    'daysToConfirm'  => 7,
    'targetIso'      => '',
];
$brandName       = $brandName       ?? 'Maria, 30.';
$eventAge        = (int) ($eventAge ?? 30);
$eventDayMonthPt = (string) ($eventDayMonthPt ?? '');
$eventYear       = (int) ($eventYear ?? (int) date('Y'));
$activeNav       = 'home';
?>
<main>

    <section class="hero">
        <div class="hero__blob" aria-hidden="true"></div>

        <div class="hero__copy">
            <div>
                <span class="eyebrow">
                    <span class="eyebrow__dot"></span>
                    <span class="eyebrow__text" data-countdown-days-eyebrow>Faltam <?= $e($countdown['days']) ?> dias</span>
                </span>
                <h1 class="hero__headline">
                    <?= $e((string) $eventAge) ?> anos<br>
                    ao lado de<br>
                    pessoas <span class="hero__highlight">
                        boas
                        <svg class="hero__squiggle" viewBox="0 0 220 22" preserveAspectRatio="none" aria-hidden="true">
                            <path d="M2,12 Q60,2 120,11 T218,9"></path>
                        </svg>
                    </span>.
                </h1>
                <p class="hero__lead">
                    Não sabe o que me dar de aniversário? Fiz uma listinha com algumas coisas que eu ia adorar ganhar
                    pra facilitar sua vida. Se quiser, escolhe ou manda o valor pelo Pix, sem problemas ❤️
                </p>
                <div class="hero__ctas">
                    <a href="/gifts" class="btn-solid btn-magnetic">
                        Ver a lista <span class="btn-arrow">→</span>
                    </a>
                    <a href="/pix" class="btn-ghost">Mandar pelo Pix</a>
                </div>
            </div>

            <div class="hero__stats">
                <div class="hero__stat" style="animation-delay: 0ms;">
                    <div class="hero__stat-value"><?= $e(str_pad((string)$countdown['totalGifts'], 2, '0', STR_PAD_LEFT)) ?></div>
                    <div class="hero__stat-label">presentes</div>
                </div>
                <div class="hero__stat" style="animation-delay: 80ms;">
                    <div class="hero__stat-value"><?= $e(str_pad((string)$countdown['reservedCount'], 2, '0', STR_PAD_LEFT)) ?></div>
                    <div class="hero__stat-label">reservados</div>
                </div>
                <div class="hero__stat hero__stat--accent" style="animation-delay: 160ms;">
                    <div class="hero__stat-value"><?= $e($countdown['percent']) ?>%</div>
                    <div class="hero__stat-label">do total</div>
                </div>
                <div class="hero__stat" style="animation-delay: 240ms;">
                    <div class="hero__stat-value"><?= $e(str_pad((string)$countdown['daysToConfirm'], 2, '0', STR_PAD_LEFT)) ?></div>
                    <div class="hero__stat-label">dias pra confirmar</div>
                </div>
            </div>
        </div>

        <?php
            $targetIso = $countdown['targetIso'] !== ''
                ? $countdown['targetIso']
                : date('c', time() + ($countdown['days'] * 86400));
        ?>
        <div class="cover" data-cover data-target="<?= $e($targetIso) ?>">
            <div class="cover__dots" aria-hidden="true"></div>
            <div class="cover__blob1" aria-hidden="true" data-cover-blob1></div>
            <div class="cover__blob2" aria-hidden="true" data-cover-blob2></div>

            <div class="cover__caption-tl">Faltam apenas</div>
            <div class="cover__caption-tr"><?= $e(date('d · m · y', strtotime($targetIso) ?: time())) ?></div>

            <svg class="cover__rings" viewBox="0 0 400 400" aria-hidden="true" data-cover-rings>
                <circle cx="200" cy="200" r="160" stroke-dasharray="64 32"></circle>
                <circle cx="200" cy="200" r="130" stroke-dasharray="52 26"></circle>
                <circle cx="200" cy="200" r="100" stroke-dasharray="40 20"></circle>
                <circle cx="200" cy="200" r="70"  stroke-dasharray="28 14"></circle>
            </svg>

            <div class="cover__days" data-cover-days-wrap>
                <div class="cover__days-num" data-countdown-days><?= $e(str_pad((string)$countdown['days'], 2, '0', STR_PAD_LEFT)) ?></div>
                <div class="cover__days-label">dias até chegar.</div>
            </div>

            <div class="cover__time">
                <div class="cover__time-row">
                    <div class="cover__time-cell">
                        <div class="cover__time-num" data-countdown-hours>00</div>
                        <div class="cover__time-label">h</div>
                    </div>
                    <div class="cover__time-cell">
                        <div class="cover__time-num" data-countdown-minutes>00</div>
                        <div class="cover__time-label">m</div>
                    </div>
                    <div class="cover__time-cell cover__time-cell--sec">
                        <div class="cover__time-num" data-countdown-seconds>00</div>
                        <div class="cover__time-label">s</div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="marquee" aria-hidden="true">
        <div class="marquee__track">
            <?php
                $marqueeDate = $eventDayMonthPt !== '' ? $eventDayMonthPt : 'a data certa';
            ?>
            <?php for ($i = 0; $i < 4; $i++): ?>
                <span class="marquee__item marquee__item--accent">✺ <?= $e((string) $eventAge) ?> anos</span>
                <span class="marquee__item">vamos comemorar</span>
                <span class="marquee__item">★ <?= $e($marqueeDate) ?></span>
                <span class="marquee__item marquee__item--accent">sua presença é o melhor presente</span>
                <span class="marquee__item">✺ <?= $e(mb_strtolower($thanksWord ?? 'Obrigado', 'UTF-8')) ?> por estar aqui</span>
                <span class="marquee__item"><?= $e($brandName) ?> &middot; <?= $e((string) $eventYear) ?></span>
            <?php endfor; ?>
        </div>
    </section>

    <?php if (!empty($featured)): ?>
        <section class="section">
            <div class="section-head">
                <div>
                    <div class="section-eyebrow">Em destaque</div>
                    <h2 class="section-title">O que eu quero <em>mesmo</em>.</h2>
                </div>
                <a href="/gifts" class="section-link">Ver os <?= $e($countdown['totalGifts']) ?> →</a>
            </div>

            <div class="gifts-grid" id="gifts-container">
                <?php
                    $gifts = $featured;
                    $cardSize = 'big';
                    require __DIR__ . '/gifts/_cards.php';
                ?>
            </div>
        </section>
    <?php endif; ?>

    <section class="section" style="padding-top: 24px;">
        <div class="thank-you">
            <div class="thank-you__blob" aria-hidden="true"></div>
            <div class="thank-you__inner">
                <div>
                    <div class="thank-you__eyebrow">Recadinho</div>
                    <p class="thank-you__quote">
                        “Não estou esperando presente. Mas, se quiser mandar alguma coisa,
                        essas são as ideias que eu mais ia gostar. Ser amigo de vocês
                        já é mais que suficiente.”
                    </p>
                    <div class="thank-you__sig">com carinho, <?= $e($brandName) ?></div>
                </div>
                <div class="thank-you__cards" aria-hidden="true">
                    <div class="thank-you__card thank-you__card--1"></div>
                    <div class="thank-you__card thank-you__card--2"></div>
                    <div class="thank-you__card thank-you__card--3"></div>
                </div>
            </div>
        </div>
    </section>

</main>

<?php
// Auth modal + toast for the featured cards reservation flow
$includeAuthModal = !empty($featured);
if ($includeAuthModal):
?>
<div id="page-meta" data-logged-in="<?= isset($_SESSION['user_id']) ? '1' : '0' ?>"></div>

<div class="auth-modal-backdrop"
     id="authModal"
     data-csrf-token="<?= htmlspecialchars(\App\Middleware\CsrfMiddleware::token(), ENT_QUOTES, 'UTF-8') ?>"
     role="dialog" aria-modal="true" aria-labelledby="authModalLabel">
    <?php require __DIR__ . '/_auth_modal.php'; ?>
</div>

<div class="toast-stack">
    <div class="toast" id="reserve-toast" hidden role="status" aria-live="polite">
        <div>
            <span id="reserve-toast-message">Reserva concluída!</span>
            <a href="/my-gifts">Ver meus presentes</a>
        </div>
        <button type="button" class="toast__close" data-dismiss-toast aria-label="Fechar">×</button>
    </div>
</div>
<div class="auth-modal-backdrop" id="reserveRedirectModal" role="dialog" aria-modal="true" aria-labelledby="reserveRedirectTitle">
    <div class="auth-modal auth-modal--compact">
        <h2 id="reserveRedirectTitle">Presente reservado com sucesso</h2>
        <p>Você será redirecionado em <strong id="reserve-redirect-countdown">10</strong>s.</p>
        <p class="small text-muted mb-2">Se não acontecer automaticamente, use o link abaixo.</p>
        <a href="#" id="reserve-redirect-link" target="_blank" rel="noopener">Abrir link do presente</a>
    </div>
</div>
<?php endif; ?>
