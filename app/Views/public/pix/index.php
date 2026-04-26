<?php
/**
 * public/pix/index.php
 * Variables:
 * - $pixKey, $pixReceiver, $pixCity, $pixQrCode, $pixCopyPaste
 * - $prefillGuestName, $prefillGuestEmail
 * - $selectedGift, $suggestedAmount
 */
?>
<div class="container py-4">
    <h1 class="mb-4 fw-bold">Contribuir via Pix</h1>

    <?php if (!empty($selectedGift)): ?>
        <div class="alert alert-warning mb-4">
            Você está contribuindo para o presente:
            <strong><?= $e((string) ($selectedGift['name'] ?? '')) ?></strong>.
            Ao confirmar, ele será marcado como dado.
        </div>
    <?php endif; ?>

    <div class="row g-4">
        <div class="col-lg-6">
            <div class="card shadow-sm h-100">
                <div class="card-body">
                    <h2 class="h5 mb-3">Dados para pagamento</h2>
                    <p class="mb-2"><strong>Chave Pix:</strong><br><?= $e($pixKey !== '' ? $pixKey : 'Configure no painel admin') ?></p>
                    <p class="mb-2"><strong>Recebedor:</strong><br><?= $e($pixReceiver !== '' ? $pixReceiver : APP_NAME) ?></p>
                    <p class="mb-3"><strong>Cidade:</strong><br><?= $e($pixCity !== '' ? $pixCity : '-') ?></p>

                    <?php if ($pixQrCode !== ''): ?>
                        <img src="<?= $e($pixQrCode) ?>"
                             alt="QR Code Pix"
                             class="img-fluid border rounded mb-3"
                             style="max-width: 280px;">
                    <?php else: ?>
                        <p class="text-muted small mb-3">QR Code ainda não configurado.</p>
                    <?php endif; ?>

                    <?php if ($pixCopyPaste !== ''): ?>
                        <label for="pix-copy" class="form-label">Pix copia e cola</label>
                        <textarea id="pix-copy" class="form-control" rows="3" readonly><?= $e($pixCopyPaste) ?></textarea>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card shadow-sm h-100">
                <div class="card-body">
                    <h2 class="h5 mb-3">Registrar contribuição</h2>
                    <form method="POST" action="/pix/contribute">
                        <input type="hidden"
                               name="csrf_token"
                               value="<?= htmlspecialchars(\App\Middleware\CsrfMiddleware::token(), ENT_QUOTES, 'UTF-8') ?>">
                        <?php if (!empty($selectedGift['id'])): ?>
                            <input type="hidden"
                                   name="gift_id"
                                   value="<?= $e((string) $selectedGift['id']) ?>">
                        <?php endif; ?>

                        <div class="mb-3">
                            <label for="guest_name" class="form-label">Nome</label>
                            <input type="text"
                                   class="form-control"
                                   id="guest_name"
                                   name="guest_name"
                                   value="<?= $e($prefillGuestName) ?>"
                                   required>
                        </div>

                        <div class="mb-3">
                            <label for="guest_email" class="form-label">E-mail</label>
                            <input type="email"
                                   class="form-control"
                                   id="guest_email"
                                   name="guest_email"
                                   value="<?= $e($prefillGuestEmail) ?>"
                                   required>
                        </div>

                        <div class="mb-3">
                            <label for="amount" class="form-label">Valor (R$)</label>
                            <input type="number"
                                   class="form-control"
                                   id="amount"
                                   name="amount"
                                   min="1"
                                   step="0.01"
                                   value="<?= $e(number_format((float) ($suggestedAmount ?? 0), 2, '.', '')) ?>"
                                   required>
                        </div>

                        <div class="mb-3">
                            <label for="message" class="form-label">Mensagem (opcional)</label>
                            <textarea class="form-control"
                                      id="message"
                                      name="message"
                                      rows="3"></textarea>
                        </div>

                        <button type="submit" class="btn btn-primary w-100">Registrar contribuição</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
