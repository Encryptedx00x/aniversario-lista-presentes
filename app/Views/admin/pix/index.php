<?php
/** @var array $settings */
/** @var \Closure $e */
$mode = ($settings['mp_mode'] ?? 'test') === 'live' ? 'live' : 'test';
?>
<div class="admin-page-head mb-4">
    <h1 class="fs-3 fw-semibold mb-1">Configurações do PIX</h1>
    <p class="text-muted mb-0">
        O PIX dinâmico é gerado pelo MercadoPago. Os campos abaixo guardam as credenciais
        e os dados de exibição que aparecem na página pública <code>/pix</code>.
    </p>
</div>

<form method="POST" action="/admin/pix">
    <input type="hidden" name="csrf_token"
           value="<?= htmlspecialchars(\App\Middleware\CsrfMiddleware::token(), ENT_QUOTES, 'UTF-8') ?>">

    <!-- ── MercadoPago ──────────────────────────────────────────────── -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body p-4">
            <h2 class="h5 mb-1">MercadoPago</h2>
            <p class="text-muted small mb-4">
                A confirmação dos PIX cai aqui automaticamente, sem precisar conferir banco no extrato.
            </p>

            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label" for="mp_mode">Modo</label>
                    <select class="form-select" id="mp_mode" name="mp_mode">
                        <option value="test" <?= $mode === 'test' ? 'selected' : '' ?>>Sandbox (testes)</option>
                        <option value="live" <?= $mode === 'live' ? 'selected' : '' ?>>Produção (cobrança real)</option>
                    </select>
                    <div class="form-text">
                        Use Sandbox até confirmar que o webhook está funcionando.
                    </div>
                </div>

                <div class="col-md-8">
                    <label class="form-label" for="mp_public_url">URL pública do site</label>
                    <input type="url" class="form-control" id="mp_public_url" name="mp_public_url"
                           value="<?= $e($settings['mp_public_url'] ?? '') ?>"
                           placeholder="https://meusite.com.br">
                    <div class="form-text">
                        Endereço onde o site está publicado. Em dev, use o link do ngrok.
                        O webhook completo fica <code>&lt;url&gt;/webhooks/mercadopago</code>.
                    </div>
                </div>

                <div class="col-md-6">
                    <label class="form-label" for="mp_access_token_test">Access Token (Sandbox)</label>
                    <input type="password" class="form-control font-monospace" id="mp_access_token_test"
                           name="mp_access_token_test"
                           value="<?= $e($settings['mp_access_token_test'] ?? '') ?>"
                           placeholder="TEST-...">
                </div>

                <div class="col-md-6">
                    <label class="form-label" for="mp_webhook_secret_test">Webhook Secret (Sandbox)</label>
                    <input type="password" class="form-control font-monospace" id="mp_webhook_secret_test"
                           name="mp_webhook_secret_test"
                           value="<?= $e($settings['mp_webhook_secret_test'] ?? '') ?>"
                           placeholder="copie do painel MP > Webhooks">
                </div>

                <div class="col-md-6">
                    <label class="form-label" for="mp_access_token_live">Access Token (Produção)</label>
                    <input type="password" class="form-control font-monospace" id="mp_access_token_live"
                           name="mp_access_token_live"
                           value="<?= $e($settings['mp_access_token_live'] ?? '') ?>"
                           placeholder="APP_USR-...">
                </div>

                <div class="col-md-6">
                    <label class="form-label" for="mp_webhook_secret_live">Webhook Secret (Produção)</label>
                    <input type="password" class="form-control font-monospace" id="mp_webhook_secret_live"
                           name="mp_webhook_secret_live"
                           value="<?= $e($settings['mp_webhook_secret_live'] ?? '') ?>"
                           placeholder="copie do painel MP > Webhooks">
                </div>
            </div>

            <details class="mt-4">
                <summary class="text-muted small">Como configurar (passo a passo)</summary>
                <ol class="small mt-3 mb-0 ps-3">
                    <li>Entre em <a href="https://www.mercadopago.com.br/developers/panel/app" target="_blank" rel="noopener">painel.mercadopago.com.br/developers</a> e crie uma aplicação.</li>
                    <li>Em <strong>Credenciais</strong>, copie o <code>Access Token</code> de Sandbox e cole no campo "Access Token (Sandbox)" acima.</li>
                    <li>Repita pra produção quando quiser cobrar de verdade.</li>
                    <li>Em <strong>Webhooks</strong>, adicione a URL <code>&lt;sua-url&gt;/webhooks/mercadopago</code> e marque o evento <code>Pagamentos</code>.</li>
                    <li>Copie o <strong>Secret</strong> que o MP gera e cole no campo "Webhook Secret" correspondente.</li>
                    <li>Salve aqui, depois faça um pagamento de teste pra confirmar.</li>
                </ol>
            </details>
        </div>
    </div>

    <!-- ── Exibição visual (legado) ────────────────────────────────── -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body p-4">
            <h2 class="h5 mb-1">Como aparece em <code>/pix</code></h2>
            <p class="text-muted small mb-4">
                Esses campos só aparecem como referência visual na página pública.
                Não influenciam a cobrança. Quem cobra é o MercadoPago acima.
            </p>

            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label" for="pix_name">Nome do recebedor</label>
                    <input type="text" class="form-control" id="pix_name" name="pix_name"
                           value="<?= $e($settings['pix_name'] ?? '') ?>"
                           placeholder="Maria S. Andrade" required>
                </div>

                <div class="col-md-6">
                    <label class="form-label" for="pix_key">Chave PIX (referência)</label>
                    <input type="text" class="form-control" id="pix_key" name="pix_key"
                           value="<?= $e($settings['pix_key'] ?? '') ?>"
                           placeholder="email, CPF, telefone ou aleatória">
                    <div class="form-text">Mostrada como informação. O QR real é o do MP.</div>
                </div>

                <div class="col-md-6">
                    <label class="form-label" for="pix_bank">Banco</label>
                    <input type="text" class="form-control" id="pix_bank" name="pix_bank"
                           value="<?= $e($settings['pix_bank'] ?? '') ?>"
                           placeholder="260 · Nu Pagamentos">
                </div>

                <div class="col-md-6">
                    <label class="form-label" for="pix_city">Cidade</label>
                    <input type="text" class="form-control" id="pix_city" name="pix_city"
                           value="<?= $e($settings['pix_city'] ?? '') ?>"
                           placeholder="São Paulo / SP">
                </div>

                <div class="col-12">
                    <label class="form-label" for="pix_code">Código PIX estático (opcional)</label>
                    <textarea class="form-control font-monospace" id="pix_code" name="pix_code"
                              rows="3"
                              placeholder="Pode deixar vazio. O MP já gera o código copia-e-cola dinâmico."><?= $e($settings['pix_code'] ?? '') ?></textarea>
                </div>
            </div>
        </div>
    </div>

    <button type="submit" class="btn btn-primary">Salvar configurações</button>
</form>
