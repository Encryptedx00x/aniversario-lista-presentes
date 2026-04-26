<section class="home-hero mb-4">
    <p class="home-hero__kicker mb-2">Gift Experience</p>
    <h1 class="home-hero__title mb-3">Uma home independente, marcante e premium.</h1>
    <p class="home-hero__subtitle mb-4">
        Uma experiência visual mais masculina, com base branco/preto e detalhes roxos: direta, elegante e moderna.
    </p>
    <div class="home-hero__actions">
        <a href="/gifts" class="btn btn-primary px-4">Ver lista de presentes</a>
        <a href="/pix" class="btn btn-outline-primary px-4">Contribuir via Pix</a>
    </div>
</section>

<section class="home-highlights mb-4">
    <div class="showcase-chip"><span>Curadoria</span><strong><?= (int) $giftsCount ?> presentes</strong></div>
    <div class="showcase-chip"><span>Fluxo</span><strong>Reserva + Pix</strong></div>
    <div class="showcase-chip"><span>UX</span><strong>Responsivo e dinâmico</strong></div>
</section>

<?php if (!empty($featured)): ?>
    <section class="home-featured">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h2 class="h4 mb-0 fw-bold">Destaques da lista</h2>
            <a href="/gifts" class="small text-decoration-none fw-semibold">Ver todos</a>
        </div>
        <div class="row g-4">
            <?php foreach ($featured as $gift): ?>
                <div class="col-12 col-md-6 col-lg-4">
                    <article class="home-gift-card h-100">
                        <div class="home-gift-card__head">
                            <span class="badge rounded-pill text-bg-dark">Destaque</span>
                            <span class="home-gift-card__qty">Disponível: <?= htmlspecialchars((string) $gift['available_qty'], ENT_QUOTES, 'UTF-8') ?></span>
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
