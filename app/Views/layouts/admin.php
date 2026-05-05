<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars($title ?? 'Admin', ENT_QUOTES, 'UTF-8') ?> — Admin</title>
    <link rel="stylesheet"
          href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
          integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH"
          crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="<?= \App\Helpers\Asset::url('/assets/css/app.css') ?>">
</head>
<body>

<!-- Top header with admin name (D-07) and logout button (D-06) -->
<nav class="navbar px-3 admin-topbar">
    <a class="navbar-brand fw-semibold" href="/admin/dashboard">Painel Admin</a>
    <div class="d-flex align-items-center gap-3">
        <span class="small text-muted">
            <?= htmlspecialchars($_SESSION['name'] ?? '', ENT_QUOTES, 'UTF-8') ?>
        </span>
        <!-- Logout form — POST with CSRF (SEC-02, D-06) -->
        <form method="POST" action="/admin/logout" class="d-inline m-0">
            <input type="hidden" name="csrf_token"
                   value="<?= htmlspecialchars(\App\Middleware\CsrfMiddleware::token(), ENT_QUOTES, 'UTF-8') ?>">
            <button type="submit" class="btn btn-outline-secondary btn-sm">Sair</button>
        </form>
    </div>
</nav>

<div class="container-fluid">
    <div class="row">
        <!-- Sidebar (D-05: all items visible and clickable) -->
        <nav class="col-md-2 col-lg-2 d-md-block bg-white sidebar py-3 border-end" style="min-height: calc(100vh - 56px)">
            <ul class="nav flex-column">
                <li class="nav-item">
                    <a class="nav-link<?= (strpos($_SERVER['REQUEST_URI'], '/admin/dashboard') === 0) ? ' active fw-semibold' : '' ?>"
                       href="/admin/dashboard">Dashboard</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link<?= (strpos($_SERVER['REQUEST_URI'], '/admin/gifts') === 0) ? ' active fw-semibold' : '' ?>"
                       href="/admin/gifts">Presentes</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link<?= (strpos($_SERVER['REQUEST_URI'], '/admin/reservations') === 0) ? ' active fw-semibold' : '' ?>"
                       href="/admin/reservations">Reservas</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link<?= (strpos($_SERVER['REQUEST_URI'], '/admin/pix') === 0) ? ' active fw-semibold' : '' ?>"
                       href="/admin/pix">Pix</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link<?= (strpos($_SERVER['REQUEST_URI'], '/admin/settings') === 0) ? ' active fw-semibold' : '' ?>"
                       href="/admin/settings">Configurações</a>
                </li>
            </ul>
        </nav>

        <!-- Main content -->
        <main class="col-md-10 col-lg-10 py-4 px-4 admin-main">
            <?php $flash = \App\Helpers\Flash::get(); if ($flash): ?>
                <div class="alert alert-<?= $flash['type'] === 'error' ? 'danger' : 'success' ?> alert-dismissible fade show" role="alert">
                    <?= htmlspecialchars($flash['msg'], ENT_QUOTES, 'UTF-8') ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
                </div>
            <?php endif; ?>
            <?= $content ?>
        </main>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc4s9bIOgUxi8T/jzmDGYiMj9gdA2Jk0Xt9rZpQB5qm"
        crossorigin="anonymous"></script>
<script src="<?= \App\Helpers\Asset::url('/assets/js/app.js') ?>"></script>
</body>
</html>
