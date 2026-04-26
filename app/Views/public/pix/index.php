<?php
/**
 * public/pix/index.php
 */
?>
<div class="container py-4 pix-page">
    <section class="pix-hero mb-4" data-reveal>
        <h1 class="mb-2 fw-bold">Contribuir via Pix</h1>
        <p class="mb-0">Um fluxo visual mais criativo para dar o presente com agilidade.</p>
    </section>

    <?php if (!empty($selectedGift)): ?>
        <div class="alert alert-warning mb-4 pix-selected-gift">
            Você está contribuindo para o presente:
            <strong><?= $e((string) ($selectedGift['name'] ?? '')) ?></strong>.
            Ao confirmar, ele será marcado como dado.
        </div>
    <?php endif; ?>

    <div class="pix-grid">
        <div class="pix-payment-panel" data-reveal>
            <h2 class="h5 mb-3">Dados para pagamento</h2>
            <div class="pix-keyline"><span>Chave Pix</span><strong><?= $e($pixKey !== '' ? $pixKey : 'Configure no painel admin') ?></strong></div>
            <div class="pix-keyline"><span>Recebedor</span><strong><?= $e($pixReceiver !== '' ? $pixReceiver : APP_NAME) ?></strong></div>
            <div class="pix-keyline mb-3"><span>Cidade</span><strong><?= $e($pixCity !== '' ? $pixCity : '-') ?></strong></div>

            <?php if ($pixQrCode !== ''): ?>
                <img src="<?= $e($pixQrCode) ?>" alt="QR Code Pix" class="pix-qr mb-3">
            <?php else: ?>
                <p class="text-muted small mb-3">QR Code ainda não configurado.</p>
            <?php endif; ?>

            <?php if ($pixCopyPaste !== ''): ?>
                <label for="pix-copy" class="form-label">Pix copia e cola</label>
                <textarea id="pix-copy" class="form-control" rows="3" readonly><?= $e($pixCopyPaste) ?></textarea>
            <?php endif; ?>
        </div>

        <div class="pix-form-panel" data-reveal>
            <h2 class="h5 mb-3">Registrar contribuição</h2>
            <form method="POST" action="/pix/contribute">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(\App\Middleware\CsrfMiddleware::token(), ENT_QUOTES, 'UTF-8') ?>">
                <?php if (!empty($selectedGift['id'])): ?>
                    <input type="hidden" name="gift_id" value="<?= $e((string) $selectedGift['id']) ?>">
                <?php endif; ?>

                <div class="row g-3">
                    <div class="col-md-6">
                        <label for="guest_name" class="form-label">Nome</label>
                        <input type="text" class="form-control" id="guest_name" name="guest_name" value="<?= $e($prefillGuestName) ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label for="guest_email" class="form-label">E-mail</label>
                        <input type="email" class="form-control" id="guest_email" name="guest_email" value="<?= $e($prefillGuestEmail) ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label for="amount" class="form-label">Valor (R$)</label>
                        <input type="number" class="form-control" id="amount" name="amount" min="1" step="0.01" value="<?= $e(number_format((float) ($suggestedAmount ?? 0), 2, '.', '')) ?>" required>
                    </div>
                    <div class="col-12">
                        <label for="message" class="form-label">Mensagem (opcional)</label>
                        <textarea class="form-control" id="message" name="message" rows="3"></textarea>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary w-100 mt-3">Registrar contribuição</button>
            </form>
        </div>
    </div>
</div>
