<?php
/**
 * Layout: main — public-facing editorial shell.
 *
 * Variables expected (set by View::render):
 *   $title   — page <title>
 *   $content — pre-rendered view HTML
 *
 * Optional, set by views to highlight the active nav item:
 *   $activeNav — one of: 'home' | 'gifts' | 'pix' | 'my'
 *
 * Optional, set by views to override header/footer copy:
 *   $brandInitial   — single character on the header brand mark (default: 'M')
 *   $brandName      — header brand name (default: 'Maria, 30.')
 *   $brandLocation  — header location/date label (default 'São Paulo · 12 de junho, 2026')
 */

$activeNav      = $activeNav      ?? 'home';
$brandInitial   = $brandInitial   ?? 'M';
$brandName      = $brandName      ?? 'Maria, 30.';
$brandLocation  = $brandLocation  ?? 'São Paulo · 20 de maio, 2026';

$navItems = [
    ['key' => 'home',  'label' => 'Início',       'href' => '/'],
    ['key' => 'gifts', 'label' => 'Lista',        'href' => '/gifts'],
    ['key' => 'pix',   'label' => 'Pix',          'href' => '/pix'],
    ['key' => 'my',    'label' => 'Minha Lista',  'href' => '/my-gifts'],
];

$flash = \App\Helpers\Flash::get();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars($title ?? APP_NAME, ENT_QUOTES, 'UTF-8') ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet"
          href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
          integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH"
          crossorigin="anonymous">
    <link rel="stylesheet" href="<?= \App\Helpers\Asset::url('/assets/css/app.css') ?>">
</head>
<body>
<div class="shell">

    <header class="site-header">
        <div class="site-header__date"><?= htmlspecialchars($brandLocation, ENT_QUOTES, 'UTF-8') ?></div>
        <a href="/" class="site-header__brand">
            <span class="brand-mark"><?= htmlspecialchars($brandInitial, ENT_QUOTES, 'UTF-8') ?></span>
            <span class="brand-name"><?= htmlspecialchars($brandName, ENT_QUOTES, 'UTF-8') ?></span>
        </a>
        <nav class="site-nav" aria-label="Principal">
            <?php foreach ($navItems as $item): ?>
                <?php
                    $isActive   = $activeNav === $item['key'];
                    $extraAttrs = '';
                    if ($item['key'] === 'my') {
                        $extraAttrs = ' data-nav-mygifts';
                    }
                ?>
                <a href="<?= htmlspecialchars($item['href'], ENT_QUOTES, 'UTF-8') ?>"
                   class="site-nav__link<?= $isActive ? ' is-active' : '' ?>"
                   <?= $isActive ? 'aria-current="page"' : '' ?><?= $extraAttrs ?>>
                    <?= htmlspecialchars($item['label'], ENT_QUOTES, 'UTF-8') ?>
                </a>
            <?php endforeach; ?>
        </nav>
    </header>

    <?php if ($flash): ?>
        <div class="flash-banner flash-banner--<?= $flash['type'] === 'error' ? 'error' : 'success' ?>" role="alert">
            <span><?= htmlspecialchars($flash['msg'], ENT_QUOTES, 'UTF-8') ?></span>
            <button type="button" class="flash-banner__close" data-dismiss-flash aria-label="Fechar">×</button>
        </div>
    <?php endif; ?>

    <div data-route-wrap>
        <?= $content ?>
    </div>

    <footer class="site-footer">
        <div class="site-footer__grid">
            <div>
                <div class="site-footer__brand-name"><?= htmlspecialchars($brandName, ENT_QUOTES, 'UTF-8') ?></div>
                <div class="site-footer__about">
                    Obrigado por chegar até aqui. Se ficar com alguma dúvida, é só me chamar.
                </div>
            </div>
            <div class="site-footer__col">
                <h4>Mapa</h4>
                <a href="/">Início</a>
                <a href="/gifts">Lista</a>
                <a href="/pix">Pix</a>
                <a href="/my-gifts" data-nav-mygifts>Minha Lista</a>
            </div>
            <div class="site-footer__col">
                <h4>Falar comigo</h4>
                <a href="#">WhatsApp</a>
                <a href="#">Instagram</a>
            </div>
            <div class="site-footer__col">
                <h4>Sobre</h4>
                <a href="#">Como funciona</a>
                <a href="#">Privacidade</a>
            </div>
        </div>
    </footer>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?= \App\Helpers\Asset::url('/assets/js/app.js') ?>"></script>
</body>
</html>
