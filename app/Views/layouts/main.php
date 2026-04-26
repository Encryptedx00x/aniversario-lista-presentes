<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars($title ?? APP_NAME, ENT_QUOTES, 'UTF-8') ?></title>
    <link rel="stylesheet"
          href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
          integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH"
          crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11/font/bootstrap-icons.min.css">
    <?php $cssV = @filemtime(ROOT_PATH . '/public/assets/css/app.css') ?: time(); ?>
    <link rel="stylesheet" href="/assets/css/app.css?v=<?= $cssV ?>">
</head>
<body class="theme-modern">
<div class="bg-orb bg-orb--a" aria-hidden="true"></div>
<div class="bg-orb bg-orb--b" aria-hidden="true"></div>
<header class="site-header">
    <div class="container site-header__inner">
        <a class="site-logo" href="/"><?= htmlspecialchars(APP_NAME, ENT_QUOTES, 'UTF-8') ?></a>
        <nav class="site-nav" aria-label="Navegação principal">
            <a class="site-nav__link" href="#home">Home</a>
            <a class="site-nav__link" href="/gifts">Presentes</a>
            <?php if (($_SESSION['role'] ?? '') === 'guest'): ?>
                <a class="site-nav__link" href="/my-gifts">Meus Presentes</a>
            <?php endif; ?>
            <a class="site-nav__link" href="/pix">Pix</a>
            <?php if (($_SESSION['role'] ?? '') === 'guest'): ?>
                <form method="POST" action="/auth/guest-logout" class="site-nav__form">
                    <input type="hidden"
                           name="csrf_token"
                           value="<?= htmlspecialchars(\App\Middleware\CsrfMiddleware::token(), ENT_QUOTES, 'UTF-8') ?>">
                    <button type="submit" class="site-nav__link site-nav__button">Sair</button>
                </form>
            <?php endif; ?>
        </nav>
    </div>
</header>
<main class="container py-4 site-main">
    <?php $flash = \App\Helpers\Flash::get(); if ($flash): ?>
        <div class="app-flash app-flash--<?= $flash['type'] === 'error' ? 'error' : 'success' ?>" role="alert">
            <?= htmlspecialchars($flash['msg'], ENT_QUOTES, 'UTF-8') ?>
        </div>
    <?php endif; ?>
    <?= $content ?>
</main>
<footer class="site-footer">
    <div class="container site-footer__inner"><?= htmlspecialchars(APP_NAME, ENT_QUOTES, 'UTF-8') ?></div>
</footer>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="/assets/js/app.js"></script>
</body>
</html>
