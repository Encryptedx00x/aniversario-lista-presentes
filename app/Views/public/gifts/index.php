<?php
/**
 * public/gifts/index.php — Full public gifts listing page (editorial layout).
 * Variables expected: $gifts, $categories, $e (from View::render extract), $brandName.
 */
$activeNav = 'gifts';
$brandName = $brandName ?? 'Maria, 30.';
?>
<main class="section--tight">

    <div class="page-head">
        <div class="page-eyebrow">A lista inteira · <?= $e(count($gifts)) ?> <?= count($gifts) === 1 ? 'item' : 'itens' ?></div>
        <h1 class="page-title">Tudo que tá <em>na lista.</em></h1>
    </div>

    <div class="filter-bar">
        <label class="filter-bar__field">
            <span class="visually-hidden" style="position:absolute;left:-9999px;">Buscar presente</span>
            <input type="text"
                   id="search-input"
                   placeholder="Buscar por nome…"
                   autocomplete="off">
        </label>

        <button type="button"
                class="filter-bar__pill is-active"
                data-category-pill
                data-value=""
                aria-pressed="true">Todos</button>
        <?php foreach ($categories as $cat): ?>
            <button type="button"
                    class="filter-bar__pill"
                    data-category-pill
                    data-value="<?= $e($cat) ?>"
                    aria-pressed="false"><?= $e($cat) ?></button>
        <?php endforeach; ?>

        <label class="filter-bar__field" style="margin-left: 8px;">
            <select id="sort-filter">
                <option value="available">Disponíveis primeiro</option>
                <option value="price_asc">Menor preço</option>
                <option value="price_desc">Maior preço</option>
                <option value="featured">Destaques</option>
            </select>
        </label>

        <span class="filter-bar__count" id="filter-count">
            <?= $e(count($gifts)) ?> <?= count($gifts) === 1 ? 'item' : 'itens' ?>
        </span>
    </div>

    <input type="hidden" id="category-filter" value="">

    <div id="gifts-container" class="gifts-grid">
        <?php require __DIR__ . '/_cards.php'; ?>
    </div>

</main>

<div id="page-meta" data-logged-in="<?= isset($_SESSION['user_id']) ? '1' : '0' ?>"></div>

<div class="auth-modal-backdrop"
     id="authModal"
     data-csrf-token="<?= htmlspecialchars(\App\Middleware\CsrfMiddleware::token(), ENT_QUOTES, 'UTF-8') ?>"
     role="dialog" aria-modal="true" aria-labelledby="authModalLabel">
    <?php require __DIR__ . '/../_auth_modal.php'; ?>
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
