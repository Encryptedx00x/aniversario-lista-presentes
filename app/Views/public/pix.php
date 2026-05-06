<?php
/**
 * public/pix.php — PIX landing page (MercadoPago-backed).
 *
 * When `?gift_id=X` is present and the guest is logged in, this page
 * lets them generate a real PIX cobrança via MercadoPago. The flow:
 *
 *   1. User picks/typed an amount.
 *   2. Click "Gerar PIX" → JS POST /pix/create.
 *   3. Page renders the dynamic QR (data URL, base64 PNG) + copia-e-cola.
 *   4. JS polls GET /pix/status/{id} every 4s until status='approved'.
 *   5. On approved → "Pagamento confirmado!" → redirect to /my-gifts.
 *
 * Variables expected (from HomeController::pix):
 *   $pixName, $pixKey, $pixBank, $pixCity, $pixCode (legacy display only)
 *   $brandName, $eventDayMonthPt, $eventYear (from SiteConfig)
 *   $selectedGift   — gift row when arriving from /pix?gift_id=X (or null)
 *   $alreadyOnList  — true if the user already reserved/contributed for this gift
 *   $csrfToken      — token for the /pix/create POST
 *   $isLoggedIn     — bool
 */

$activeNav      = 'pix';
$brandName      = $brandName      ?? 'Maria, 30.';
$pixName        = $pixName        ?? 'Maria S. Andrade';
$pixKey         = $pixKey         ?? '';
$pixBank        = $pixBank        ?? '';
$pixCity        = $pixCity        ?? '';
$pixCode        = $pixCode        ?? '';
$selectedGift   = $selectedGift   ?? null;
$alreadyOnList  = (bool) ($alreadyOnList ?? false);
$csrfToken      = (string) ($csrfToken ?? '');
$isLoggedIn     = (bool) ($isLoggedIn ?? false);
$pixQty         = max(1, (int) ($pixQty ?? 1));

$presets       = [50, 100, 150, 250, 500, 1000];
$defaultAmount = 150;
if ($selectedGift && (float) ($selectedGift['price'] ?? 0) > 0) {
    // Multiplica pela quantidade quando vem de /pix?gift_id=X&qty=N
    $defaultAmount = (int) round((float) $selectedGift['price'] * $pixQty);
}
?>
<main class="section--tight">

    <div class="page-head">
        <div class="page-eyebrow">Mandar pelo Pix</div>
        <h1 class="page-title">Sem cadastro,<br>sem <em>pressa.</em></h1>
        <p class="hero__lead" style="max-width: 560px; margin-top: 18px;">
            Se preferir mandar qualquer valor, eu uno com as outras contribuições e cuido dos itens
            da lista. Funciona como um PIX normal, sem complicação.
        </p>
    </div>

    <?php if ($selectedGift): ?>
        <section class="pix-selected" aria-labelledby="pix-selected-title">
            <div class="pix-selected__inner">
                <div class="pix-selected__eyebrow">Você está contribuindo para</div>
                <div class="pix-selected__row">
                    <div class="pix-selected__info">
                        <h2 class="pix-selected__title" id="pix-selected-title">
                            <?= $e($selectedGift['name']) ?>
                        </h2>
                        <?php if (!empty($selectedGift['category'])): ?>
                            <div class="pix-selected__meta"><?= $e($selectedGift['category']) ?></div>
                        <?php endif; ?>
                    </div>
                    <?php if ((float) ($selectedGift['price'] ?? 0) > 0): ?>
                        <div class="pix-selected__price">
                            R$ <?= $e(number_format((float) $selectedGift['price'], 2, ',', '.')) ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </section>
    <?php endif; ?>

    <div class="pix-grid">
        <section class="pix-card" aria-labelledby="pix-info-title">
            <div class="pix-eyebrow" id="pix-info-title">Quem recebe</div>
            <div class="pix-meta">
                <div class="pix-meta__label">Nome</div>
                <div class="pix-meta__value"><?= $e($pixName) ?></div>
            </div>
            <?php if ($pixKey !== ''): ?>
                <div class="pix-meta">
                    <div class="pix-meta__label">Chave PIX (referência)</div>
                    <div class="pix-meta__value"><?= $e($pixKey) ?></div>
                </div>
            <?php endif; ?>
            <?php if ($pixBank !== ''): ?>
                <div class="pix-meta">
                    <div class="pix-meta__label">Banco</div>
                    <div class="pix-meta__value"><?= $e($pixBank) ?></div>
                </div>
            <?php endif; ?>
            <?php if ($pixCity !== ''): ?>
                <div class="pix-meta">
                    <div class="pix-meta__label">Cidade</div>
                    <div class="pix-meta__value"><?= $e($pixCity) ?></div>
                </div>
            <?php endif; ?>

            <p class="pix-step__hint" style="margin-top: 22px;">
                O QR code e o código copia-e-cola são gerados pelo MercadoPago no momento
                em que você clica em <em>Gerar PIX</em>. A confirmação do pagamento acontece
                automaticamente, sem precisar avisar.
            </p>
        </section>

        <section class="pix-card pix-sender" aria-labelledby="pix-send-title">
            <div class="pix-sender__inner"
                 data-pix-flow
                 data-gift-id="<?= $selectedGift ? $e($selectedGift['id']) : '0' ?>"
                 data-csrf="<?= $e($csrfToken) ?>"
                 data-logged-in="<?= $isLoggedIn ? '1' : '0' ?>">

                <div class="pix-sender__pill">
                    <span class="eyebrow__dot"></span>
                    <span>
                        <?= $selectedGift
                            ? 'Para: ' . $e($selectedGift['name'])
                            : 'Para: contribuição livre' ?>
                    </span>
                </div>

                <?php if ($alreadyOnList): ?>
                    <div class="pix-confirm-done" style="margin-top: 22px;">
                        ✓ Esse presente já está na sua lista. <?= $e($thanksWord ?? 'Obrigado') ?>!
                        <a href="/my-gifts" class="pix-confirm-done__link">Ver Minha Lista →</a>
                    </div>
                <?php else: ?>
                    <?php if (!$selectedGift): ?>
                        <p class="pix-step__text" style="margin-top: 4px; margin-bottom: 18px;">
                            Sem item específico? É só escolher um valor e mandar. A contribuição
                            entra na minha lista geral. Se preferir mandar pra um presente,
                            vá na <a href="/gifts">lista</a> e clique em <em>Mandar pelo Pix</em>.
                        </p>
                    <?php endif; ?>

                    <!-- Etapa 1 — valor -->
                    <div class="pix-step" data-pix-step="1">
                        <div class="pix-step__head">
                            <span class="pix-step__num">1</span>
                            <span class="pix-step__title">Decida o valor</span>
                        </div>
                        <div class="pix-step__body">
                            <div class="pix-sender__amount-row">
                                <span class="pix-sender__currency">R$</span>
                                <input type="number" class="pix-sender__amount"
                                       id="pix-amount" data-pix-amount
                                       value="<?= $e((string) $defaultAmount) ?>" min="1" step="1"
                                       inputmode="numeric"
                                       aria-label="Valor em reais">
                            </div>
                            <div class="pix-sender__presets">
                                <?php foreach ($presets as $v): ?>
                                    <button type="button"
                                            class="filter-bar__pill<?= $v === $defaultAmount ? ' is-active' : '' ?>"
                                            data-pix-preset
                                            data-value="<?= $e((string) $v) ?>">R$ <?= $e((string) $v) ?></button>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Etapa 2 — gerar PIX -->
                    <div class="pix-step" data-pix-step="2">
                        <div class="pix-step__head">
                            <span class="pix-step__num">2</span>
                            <span class="pix-step__title">Gere o código PIX</span>
                        </div>
                        <div class="pix-step__body">
                            <p class="pix-step__text" data-pix-generate-copy>
                                Clique abaixo pra eu gerar o QR code e o código copia-e-cola.
                                A cobrança vale por 24h.
                            </p>
                            <button type="button"
                                    class="pix-sender__submit"
                                    data-pix-generate
                                    data-logged-in="<?= $isLoggedIn ? '1' : '0' ?>">
                                Gerar PIX agora →
                            </button>
                            <p class="pix-step__error" data-pix-error hidden></p>

                            <div class="pix-dynamic" data-pix-qr-slot hidden>
                                <div class="pix-dynamic__row">
                                    <img class="pix-dynamic__qr"
                                         data-pix-qr-img
                                         alt="QR code PIX"
                                         loading="lazy">
                                    <div class="pix-dynamic__info">
                                        <div class="pix-meta__label">Valor</div>
                                        <div class="pix-dynamic__amount" data-pix-qr-amount>R$ 0,00</div>
                                        <div class="pix-meta__label" style="margin-top: 14px;">Expira em</div>
                                        <div class="pix-dynamic__expires" data-pix-qr-expires>aguardando</div>
                                    </div>
                                </div>
                                <div class="pix-meta__label" style="margin-top: 18px; margin-bottom: 8px;">
                                    Pix copia e cola
                                </div>
                                <div class="pix-copy__code" data-pix-qr-text></div>
                                <button type="button" class="pix-copy__btn" data-pix-copy
                                        data-default-label="Copiar código do Pix"
                                        data-copied-label="✓ Copiado, é só colar no app">
                                    Copiar código do Pix
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Etapa 3 — aguardando confirmação -->
                    <div class="pix-step" data-pix-step="3">
                        <div class="pix-step__head">
                            <span class="pix-step__num">3</span>
                            <span class="pix-step__title">Aguardando confirmação</span>
                        </div>
                        <div class="pix-step__body">
                            <div class="pix-status pix-status--idle" data-pix-status>
                                <span class="pix-status__dot" aria-hidden="true"></span>
                                <span class="pix-status__msg" data-pix-status-msg>
                                    Vou ficar de olho aqui. Assim que o pagamento cair, essa
                                    tela vira sozinha.
                                </span>
                            </div>
                            <p class="pix-step__hint">
                                Pode fechar a aba. Quando o pagamento for confirmado, ele aparece
                                em <strong>Minha Lista</strong> também.
                            </p>
                        </div>
                    </div>
                <?php endif; ?>

            </div>
        </section>
    </div>

</main>

<?php if (!$alreadyOnList && !$isLoggedIn): ?>
    <!-- Auth modal so guests can sign up/login from /pix (com ou sem gift_id) -->
    <div id="page-meta" data-logged-in="0"></div>
    <div class="auth-modal-backdrop" id="authModal"
         data-csrf-token="<?= $e($csrfToken) ?>"
         role="dialog" aria-modal="true" aria-labelledby="authModalLabel">
        <?php require __DIR__ . '/_auth_modal.php'; ?>
    </div>
<?php endif; ?>
