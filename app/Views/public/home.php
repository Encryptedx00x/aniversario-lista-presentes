<?php
$days = $countdownDays ?? null;
$nextDate = $nextBirthdayLabel ?? null;
?>
<section class="home-simple-wrap py-5 text-center" data-reveal>
    <div class="home-simple-wrap__paper p-4 p-md-5 mx-auto">
        <p class="home-simple-wrap__kicker mb-2">Aniversário especial</p>
        <h1 class="home-simple-wrap__title mb-3">Aniversário de Maria</h1>
        <p class="home-simple-wrap__subtitle mb-4">Escolha um presente e celebre junto com uma experiência simples, bonita e objetiva.</p>

        <div class="d-flex flex-wrap justify-content-center gap-2 mb-4">
            <a href="/gifts" class="btn btn-primary btn-lg px-4">Ver lista de presentes</a>
            <a href="/pix" class="btn btn-outline-dark btn-lg px-4">Contribuir via Pix</a>
        </div>

        <div class="home-simple-wrap__countdown mx-auto">
            <p class="mb-1 small text-uppercase">Contagem regressiva</p>
            <?php if ($days !== null && $nextDate !== null): ?>
                <div class="display-4 fw-bold"><?= (int) $days ?></div>
                <p class="mb-0 small">dias • próxima data: <?= htmlspecialchars($nextDate, ENT_QUOTES, 'UTF-8') ?></p>
            <?php else: ?>
                <div class="display-4 fw-bold">--</div>
                <p class="mb-0 small">Defina no admin em Configurações</p>
            <?php endif; ?>
        </div>
    </div>
</section>
