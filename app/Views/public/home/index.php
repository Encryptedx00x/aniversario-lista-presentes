<?php
$days = $countdownDays ?? null;
$nextDate = $nextBirthdayLabel ?? null;
$previewA = !empty($featured[0]['image_path']) ? (string) $featured[0]['image_path'] : 'https://images.unsplash.com/photo-1519671482749-fd09be7ccebf?w=900&auto=format&fit=crop';
$previewB = !empty($featured[1]['image_path']) ? (string) $featured[1]['image_path'] : 'https://images.unsplash.com/photo-1513151233558-d860c5398176?w=900&auto=format&fit=crop';
$previewC = !empty($featured[2]['image_path']) ? (string) $featured[2]['image_path'] : 'https://images.unsplash.com/photo-1464349153735-7db50ed83c84?w=900&auto=format&fit=crop';
?>

<div class="editorial-stage">
    <video id="bg-scroll-video" class="bg-scroll-video" muted autoplay loop playsinline preload="auto" aria-hidden="true">
        <source src="https://cdn.coverr.co/videos/coverr-ink-in-water-1579/1080p.mp4" type="video/mp4">
    </video>
    <div class="editorial-grain" aria-hidden="true"></div>

    <section class="home-editorial-hero" data-reveal>
        <p class="home-editorial-kicker">edição de aniversário</p>
        <h1 class="home-editorial-title" data-kinetic data-speed="0.22" data-reveal>
            Celebre em <span class="ink">movimento</span>.<br>
            Escolha presentes como quem compõe uma obra.
        </h1>

        <p class="home-editorial-subtitle" data-kinetic data-speed="0.14" data-reveal>
            Entre na lista, reserve com fluidez e contribua via Pix em uma experiência
            editorial viva — com ritmo, textura e presença.
        </p>

        <div class="home-editorial-actions" data-kinetic data-speed="0.12" data-reveal>
            <a href="/gifts" class="btn btn-dark px-4">Ver presentes</a>
            <a href="/pix" class="btn btn-outline-dark px-4">Contribuir via Pix</a>
        </div>

        <div class="countdown-editorial" data-kinetic data-speed="0.08" data-reveal>
            <p class="countdown-editorial__label">contagem para o aniversário</p>
            <?php if ($days !== null && $nextDate !== null): ?>
                <div class="countdown-editorial__days"><?= (int) $days ?></div>
                <p class="countdown-editorial__date">próxima data: <?= htmlspecialchars($nextDate, ENT_QUOTES, 'UTF-8') ?></p>
            <?php else: ?>
                <div class="countdown-editorial__days">--</div>
                <p class="countdown-editorial__date">defina a data em Admin → Configurações</p>
            <?php endif; ?>
        </div>
    </section>

    <section class="home-manifesto" data-parallax="0.12" data-reveal>
        <p>
            Uma noite com atmosfera de
            <span class="hover-keyword" data-preview="<?= htmlspecialchars($previewA, ENT_QUOTES, 'UTF-8') ?>">festa autoral</span>,
            trilha de
            <span class="hover-keyword" data-preview="<?= htmlspecialchars($previewB, ENT_QUOTES, 'UTF-8') ?>">energia cinética</span>
            e detalhes de
            <span class="hover-keyword" data-preview="<?= htmlspecialchars($previewC, ENT_QUOTES, 'UTF-8') ?>">luxo contemporâneo</span>.
        </p>
    </section>

    <section class="birthday-storyline" data-reveal>
        <article class="birthday-storyline__item" data-parallax="0.06">
            <span>01</span>
            <h4>Escolher</h4>
            <p>Descubra presentes com curadoria visual e interação viva.</p>
        </article>
        <article class="birthday-storyline__item" data-parallax="0.1">
            <span>02</span>
            <h4>Reservar</h4>
            <p>Fluxo instantâneo, autenticação no modal e confirmação elegante.</p>
        </article>
        <article class="birthday-storyline__item" data-parallax="0.14">
            <span>03</span>
            <h4>Celebrar</h4>
            <p>Contribua por Pix e transforme o presente em experiência memorável.</p>
        </article>
    </section>

    <?php if (!empty($featured)): ?>
        <section class="gift-collage-grid" data-reveal>
            <?php foreach ($featured as $idx => $gift): ?>
                <article class="gift-collage-card" data-parallax="<?= htmlspecialchars((string) (0.04 * ($idx + 1)), ENT_QUOTES, 'UTF-8') ?>">
                    <div class="gift-collage-card__meta">
                        <span><?= htmlspecialchars((string) ($gift['category'] ?? 'presente'), ENT_QUOTES, 'UTF-8') ?></span>
                        <strong>Disponível: <?= htmlspecialchars((string) ($gift['available_qty'] ?? 0), ENT_QUOTES, 'UTF-8') ?></strong>
                    </div>
                    <h3><?= htmlspecialchars((string) ($gift['name'] ?? ''), ENT_QUOTES, 'UTF-8') ?></h3>
                    <a href="/gifts" class="gift-collage-card__link">Escolher presente</a>
                </article>
            <?php endforeach; ?>
        </section>
    <?php endif; ?>

    <div id="cursor-preview" class="cursor-preview" aria-hidden="true">
        <img id="cursor-preview-img" src="" alt="">
    </div>
</div>
