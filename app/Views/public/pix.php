<?php
/**
 * public/pix.php — Pix landing page (static info, no backend integration).
 * Variables expected (optional): $pixData (array), $brandName.
 */
$activeNav = 'pix';
$brandName = $brandName ?? 'Maria, 30.';

$pixData = $pixData ?? [
    'name'    => 'Maria S. Andrade',
    'key'     => 'maria.aniv@email.com',
    'bank'    => '260 — Nu Pagamentos',
    'city'    => 'São Paulo — SP',
    'code'    => '00020126360014BR.GOV.BCB.PIX0114maria.aniv@email5204000053039865406150.005802BR5919MARIA S ANDRADE6009SAO PAULO62070503***6304B7C9',
];

$presets = [50, 100, 150, 250, 500, 1000];
?>
<main class="section--tight">

    <div class="page-head">
        <div class="page-eyebrow">Mandar pelo Pix</div>
        <h1 class="page-title">Sem cadastro,<br>sem <em>pressa.</em></h1>
        <p class="hero__lead" style="max-width: 560px; margin-top: 18px;">
            Se preferir mandar um valor — qualquer valor — eu somo com as outras contribuições e cuido dos itens da
            lista. Funciona igualzinho um Pix normal.
        </p>
    </div>

    <div class="pix-grid">
        <section class="pix-card" aria-labelledby="pix-info-title">
            <div class="pix-eyebrow" id="pix-info-title">Dados do Pix</div>
            <div class="pix-card__inner-grid">
                <div>
                    <div class="pix-meta">
                        <div class="pix-meta__label">Quem recebe</div>
                        <div class="pix-meta__value"><?= $e($pixData['name']) ?></div>
                    </div>
                    <div class="pix-meta">
                        <div class="pix-meta__label">Chave (e-mail)</div>
                        <div class="pix-meta__value"><?= $e($pixData['key']) ?></div>
                    </div>
                    <div class="pix-meta">
                        <div class="pix-meta__label">Banco</div>
                        <div class="pix-meta__value"><?= $e($pixData['bank']) ?></div>
                    </div>
                    <div class="pix-meta">
                        <div class="pix-meta__label">Cidade</div>
                        <div class="pix-meta__value"><?= $e($pixData['city']) ?></div>
                    </div>
                </div>

                <div class="pix-qr" aria-label="QR code Pix (visual)">
                    <?php
                        for ($i = 0; $i < 196; $i++) {
                            $r      = $i % 14;
                            $c      = intdiv($i, 14);
                            $corner = (($r < 3 && $c < 3) || ($r >= 11 && $c < 3) || ($r < 3 && $c >= 11));
                            $on     = (($i * 37 + ($i % 11) * 7) % 5) < 2;
                            $cls    = ($corner || $on) ? 'pix-qr__cell pix-qr__cell--on' : 'pix-qr__cell';
                            echo '<div class="' . $cls . '"></div>';
                        }
                    ?>
                    <div class="pix-qr__scan" aria-hidden="true"></div>
                </div>
            </div>

            <div class="pix-copy">
                <div class="pix-meta__label" style="margin-bottom: 10px;">Pix copia e cola</div>
                <div class="pix-copy__code" id="pix-code"><?= $e($pixData['code']) ?></div>
                <button type="button" class="pix-copy__btn" id="pix-copy-btn"
                        data-code="<?= $e($pixData['code']) ?>"
                        data-default-label="Copiar código do Pix"
                        data-copied-label="✓ Copiado, é só colar no app">
                    Copiar código do Pix
                </button>
            </div>
        </section>

        <section class="pix-card pix-sender" aria-labelledby="pix-send-title">
            <div class="pix-sender__inner">
                <div class="pix-sender__pill">
                    <span class="eyebrow__dot"></span>
                    <span>Vai pra: lista de aniversário</span>
                </div>

                <div class="pix-eyebrow" id="pix-send-title">Quanto você quer mandar</div>
                <div class="pix-sender__amount-row">
                    <span class="pix-sender__currency">R$</span>
                    <input type="number" class="pix-sender__amount"
                           id="pix-amount"
                           value="150" min="0" step="1"
                           inputmode="numeric"
                           aria-label="Valor em reais">
                </div>

                <div class="pix-sender__presets">
                    <?php foreach ($presets as $v): ?>
                        <button type="button"
                                class="filter-bar__pill<?= $v === 150 ? ' is-active' : '' ?>"
                                data-pix-preset
                                data-value="<?= $e((string) $v) ?>">R$ <?= $e((string) $v) ?></button>
                    <?php endforeach; ?>
                </div>

                <div class="pix-sender__form">
                    <div class="field">
                        <label class="field__label" for="pix-name">Seu nome</label>
                        <input type="text" id="pix-name" placeholder="Como você assina embaixo">
                    </div>
                    <div class="field">
                        <label class="field__label" for="pix-email">E-mail (pra eu te agradecer)</label>
                        <input type="email" id="pix-email" placeholder="voce@exemplo.com">
                    </div>
                    <div class="field">
                        <label class="field__label" for="pix-msg">Quer deixar um recado?</label>
                        <textarea id="pix-msg" rows="2" placeholder="Parabéns…"></textarea>
                    </div>
                </div>

                <button type="button" class="pix-sender__submit" id="pix-confirm">
                    Confirmar e mandar →
                </button>
            </div>
        </section>
    </div>

</main>
