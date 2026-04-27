<?php
/** @var array $settings */
/** @var \Closure $e */
?>
<div class="admin-page-head mb-4">
    <h1 class="fs-3 fw-semibold mb-1">Configurações do Evento</h1>
    <p class="text-muted mb-0">Esses dados atualizam a home, cabeçalho e contagem regressiva.</p>
</div>

<div class="card shadow-sm border-0">
    <div class="card-body p-4">
        <form method="POST" action="/admin/settings" class="row g-3">
            <input type="hidden" name="csrf_token"
                   value="<?= htmlspecialchars(\App\Middleware\CsrfMiddleware::token(), ENT_QUOTES, 'UTF-8') ?>">

            <div class="col-md-6">
                <label class="form-label" for="event_person_name">Nome da pessoa</label>
                <input type="text" class="form-control" id="event_person_name" name="event_person_name"
                       value="<?= $e($settings['event_person_name'] ?? '') ?>" required>
            </div>

            <div class="col-md-3">
                <label class="form-label" for="event_age">Idade</label>
                <input type="number" min="1" class="form-control" id="event_age" name="event_age"
                       value="<?= $e($settings['event_age'] ?? '30') ?>" required>
            </div>

            <div class="col-md-3">
                <label class="form-label" for="event_date">Data-alvo</label>
                <input type="date" class="form-control" id="event_date" name="event_date"
                       value="<?= $e($settings['event_date'] ?? '') ?>" required>
            </div>

            <div class="col-md-6">
                <label class="form-label" for="event_location">Local</label>
                <input type="text" class="form-control" id="event_location" name="event_location"
                       value="<?= $e($settings['event_location'] ?? '') ?>" placeholder="São Paulo">
            </div>

            <div class="col-12">
                <button type="submit" class="btn btn-primary">Salvar configurações</button>
            </div>
        </form>
    </div>
</div>
