<?php
$s = $settings ?? [];
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 m-0">Configurações</h1>
</div>

<form method="POST" action="/admin/settings" class="card p-4">
    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(\App\Middleware\CsrfMiddleware::token(), ENT_QUOTES, 'UTF-8') ?>">

    <h2 class="h5 mb-3">Aniversário</h2>
    <div class="mb-4">
        <label class="form-label" for="birthday_date">Data do aniversário</label>
        <input class="form-control" type="date" id="birthday_date" name="birthday_date" value="<?= htmlspecialchars((string) ($s['birthday_date'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
        <div class="form-text">Essa data é usada para exibir o contador regressivo na Home.</div>
    </div>

    <h2 class="h5 mb-3">Pix</h2>
    <div class="row g-3">
        <div class="col-md-6">
            <label class="form-label" for="pix_key">Chave Pix</label>
            <input class="form-control" type="text" id="pix_key" name="pix_key" value="<?= htmlspecialchars((string) ($s['pix_key'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
        </div>
        <div class="col-md-6">
            <label class="form-label" for="pix_receiver">Recebedor</label>
            <input class="form-control" type="text" id="pix_receiver" name="pix_receiver" value="<?= htmlspecialchars((string) ($s['pix_receiver'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
        </div>
        <div class="col-md-6">
            <label class="form-label" for="pix_city">Cidade</label>
            <input class="form-control" type="text" id="pix_city" name="pix_city" value="<?= htmlspecialchars((string) ($s['pix_city'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
        </div>
        <div class="col-md-6">
            <label class="form-label" for="pix_qr_code">URL do QR Code</label>
            <input class="form-control" type="url" id="pix_qr_code" name="pix_qr_code" value="<?= htmlspecialchars((string) ($s['pix_qr_code'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
        </div>
        <div class="col-12">
            <label class="form-label" for="pix_copy_paste">Pix copia e cola</label>
            <textarea class="form-control" id="pix_copy_paste" name="pix_copy_paste" rows="3"><?= htmlspecialchars((string) ($s['pix_copy_paste'] ?? ''), ENT_QUOTES, 'UTF-8') ?></textarea>
        </div>
    </div>

    <div class="mt-4">
        <button class="btn btn-primary" type="submit">Salvar configurações</button>
    </div>
</form>
