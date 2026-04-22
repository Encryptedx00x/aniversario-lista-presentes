<?php
/**
 * public/gifts/index.php — Full public gifts listing page.
 * Injected into the 'main' layout as $content.
 * Variables available: $gifts, $categories, $e (from View::render extract).
 */
?>
<div class="container py-4">

    <h1 class="mb-4 fw-bold">Lista de Presentes</h1>

    <!-- Filter bar -->
    <div class="row g-3 mb-4 align-items-end">
        <div class="col-md-5">
            <label for="search-input" class="form-label visually-hidden">Buscar presente</label>
            <input type="text"
                   id="search-input"
                   class="form-control"
                   placeholder="Buscar por nome...">
        </div>
        <div class="col-md-4">
            <label for="category-filter" class="form-label visually-hidden">Categoria</label>
            <select id="category-filter" class="form-select">
                <option value="">Todas as categorias</option>
                <?php foreach ($categories as $cat): ?>
                    <option value="<?= $e($cat) ?>"><?= $e($cat) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-3">
            <label for="sort-filter" class="form-label visually-hidden">Ordenar por</label>
            <select id="sort-filter" class="form-select">
                <option value="available">Disponíveis primeiro</option>
                <option value="price_asc">Menor preço</option>
                <option value="price_desc">Maior preço</option>
                <option value="featured">Destaques</option>
            </select>
        </div>
    </div>

    <!-- Card grid — initial server render; JS replaces innerHTML on filter -->
    <div id="gifts-container" class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
        <?php require __DIR__ . '/_cards.php'; ?>
    </div>

</div>

<!-- Hidden page meta for JS to read guest session state -->
<div id="page-meta"
     data-logged-in="<?= isset($_SESSION['user_id']) ? '1' : '0' ?>">
</div>

<!-- Auth modal -->
<div class="modal fade"
     id="authModal"
     tabindex="-1"
     data-bs-backdrop="static"
     data-bs-keyboard="false"
     data-csrf-token="<?= htmlspecialchars(\App\Middleware\CsrfMiddleware::token(), ENT_QUOTES, 'UTF-8') ?>"
     aria-labelledby="authModalLabel"
     aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title" id="authModalLabel">Identificação</h5>
                <button type="button"
                        class="btn-close"
                        data-bs-dismiss="modal"
                        aria-label="Fechar"></button>
            </div>

            <div class="modal-body">

                <!-- Tab nav -->
                <ul class="nav nav-tabs mb-4" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active" href="#" data-tab="signup" role="tab">Criar conta</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#" data-tab="login" role="tab">Entrar</a>
                    </li>
                </ul>

                <!-- Sign-up tab -->
                <div id="tab-signup">
                    <form id="form-signup" novalidate>
                        <div class="mb-3">
                            <label for="signup-name" class="form-label">Nome</label>
                            <input type="text"
                                   id="signup-name"
                                   name="name"
                                   class="form-control"
                                   required
                                   autocomplete="name">
                        </div>
                        <div class="mb-3">
                            <label for="signup-email" class="form-label">E-mail</label>
                            <input type="email"
                                   id="signup-email"
                                   name="email"
                                   class="form-control"
                                   required
                                   autocomplete="email">
                        </div>
                        <div class="mb-3">
                            <label for="signup-phone" class="form-label">Telefone <span class="text-muted small">(opcional)</span></label>
                            <input type="tel"
                                   id="signup-phone"
                                   name="phone"
                                   class="form-control"
                                   autocomplete="tel">
                        </div>
                        <div class="mb-3">
                            <label for="signup-password" class="form-label">Senha</label>
                            <input type="password"
                                   id="signup-password"
                                   name="password"
                                   class="form-control"
                                   required
                                   autocomplete="new-password">
                        </div>
                        <div id="signup-error" class="alert alert-danger d-none" role="alert"></div>
                        <button type="submit" class="btn btn-primary w-100">Criar conta</button>
                    </form>
                </div>

                <!-- Login tab -->
                <div id="tab-login" class="d-none">
                    <form id="form-login" novalidate>
                        <div class="mb-3">
                            <label for="login-email" class="form-label">E-mail</label>
                            <input type="email"
                                   id="login-email"
                                   name="email"
                                   class="form-control"
                                   required
                                   autocomplete="email">
                        </div>
                        <div class="mb-3">
                            <label for="login-password" class="form-label">Senha</label>
                            <input type="password"
                                   id="login-password"
                                   name="password"
                                   class="form-control"
                                   required
                                   autocomplete="current-password">
                        </div>
                        <div id="login-error" class="alert alert-danger d-none" role="alert"></div>
                        <button type="submit" class="btn btn-primary w-100">Entrar</button>
                    </form>
                </div>

                <!-- Success message (shown by JS after auth) -->
                <div id="auth-success" class="alert alert-success d-none" role="alert">
                    Tudo certo! Continuando...
                </div>

            </div>

            <div class="modal-footer d-none">
                <!-- intentionally empty — actions are inside the form tabs -->
            </div>

        </div>
    </div>
</div>
