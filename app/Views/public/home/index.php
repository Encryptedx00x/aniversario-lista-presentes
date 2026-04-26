<?php
$days = $countdownDays ?? null;
$nextDate = $nextBirthdayLabel ?? null;
?>
<section class="home-hero mb-4">
    <div class="home-hero__grid">
        <div>
            <p class="home-hero__kicker mb-2">Aniversário em foco</p>
            <h1 class="home-hero__title mb-3">Escolha presentes com uma experiência mais viva, criativa e memorável.</h1>
            <p class="home-hero__subtitle mb-4">
                Navegue pela lista, reserve itens e contribua por Pix com uma interface pensada para celebrar — não para parecer um dashboard corporativo.
            </p>
            <div class="home-hero__actions">
                <a href="/gifts" class="btn btn-primary px-4">Explorar presentes</a>
                <a href="/pix" class="btn btn-outline-primary px-4">Contribuir via Pix</a>
            </div>
        </div>

        <aside class="countdown-card">
            <p class="countdown-card__label">Contagem regressiva</p>
            <?php if ($days !== null && $nextDate !== null): ?>
                <div class="countdown-card__days"><?= (int) $days ?></div>
                <p class="countdown-card__text">dias para o aniversário</p>
                <p class="countdown-card__date">Próxima data: <?= htmlspecialchars($nextDate, ENT_QUOTES, 'UTF-8') ?></p>
            <?php else: ?>
                <div class="countdown-card__days">--</div>
                <p class="countdown-card__text">Defina a data no painel admin</p>
                <p class="countdown-card__date">Menu: Admin → Configurações</p>
            <?php endif; ?>
        </aside>
    </div>
</section>

<section class="home-mosaic mb-4">
    <article class="mosaic-tile mosaic-tile--dark">
        <h2>Presentes incríveis</h2>
        <p>Cards criativos com categoria, status e ações rápidas para reservar ou contribuir.</p>
    </article>
    <article class="mosaic-tile mosaic-tile--light">
        <h2>Fluxo sem atrito</h2>
        <p>Autenticação no modal + continuação da ação de reserva automaticamente.</p>
    </article>
    <article class="mosaic-tile mosaic-tile--accent">
        <h2>Pix integrado</h2>
        <p>Contribuição com sugestão de valor e vínculo direto ao presente selecionado.</p>
    </article>
</section>

<?php if (!empty($featured)): ?>
    <section class="home-featured">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h2 class="h4 mb-0 fw-bold">Sugestões em destaque</h2>
            <a href="/gifts" class="small text-decoration-none fw-semibold">Ver lista completa</a>
        </div>
        <div class="row g-4">
            <?php foreach ($featured as $gift): ?>
                <div class="col-12 col-md-6 col-lg-4">
                    <article class="home-gift-card h-100">
                        <div class="home-gift-card__head">
                            <span class="showcase-chip showcase-chip--mini"><span>Disponível</span><strong><?= htmlspecialchars((string) $gift['available_qty'], ENT_QUOTES, 'UTF-8') ?></strong></span>
                            <?php if (!empty($gift['category'])): ?>
                                <span class="showcase-chip showcase-chip--mini"><span>Categoria</span><strong><?= htmlspecialchars((string) $gift['category'], ENT_QUOTES, 'UTF-8') ?></strong></span>
                            <?php endif; ?>
                        </div>
                        <h3 class="h5 fw-bold mb-2"><?= htmlspecialchars((string) $gift['name'], ENT_QUOTES, 'UTF-8') ?></h3>
                        <?php if (!empty($gift['description'])): ?>
                            <p class="text-secondary small mb-3"><?= htmlspecialchars((string) $gift['description'], ENT_QUOTES, 'UTF-8') ?></p>
                        <?php endif; ?>
                        <a class="btn btn-sm btn-primary" href="/gifts">Escolher presente</a>
                    </article>
                </div>
            <?php endforeach; ?>
        </div>
    </section>
<?php endif; ?>
