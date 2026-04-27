<?php
/**
 * Auth modal partial — shared between home and gifts pages.
 * Expects the parent .auth-modal-backdrop wrapper to provide the CSRF token via dataset.
 */
?>
<div class="auth-modal" data-auth-modal>
    <aside class="auth-modal__poster">
        <div class="auth-modal__poster-inner">
            <div class="auth-modal__eyebrow">Reservando um presente</div>
            <h2 class="auth-modal__title" id="authModalLabel">Quase lá.</h2>
            <div class="auth-modal__sub">É só pegar seu e-mail pra confirmar quem reservou.</div>
            <div class="auth-modal__hint">
                Você tem uma semana pra confirmar com calma. Sem cobrança, sem cerimônia.
            </div>
        </div>
    </aside>

    <section class="auth-modal__panel">
        <div class="auth-modal__head">
            <h3 class="auth-modal__heading" data-auth-heading>Criar conta</h3>
            <button type="button" class="auth-modal__close" data-auth-close aria-label="Fechar">×</button>
        </div>

        <div class="auth-modal__tabs" role="tablist">
            <span class="auth-modal__tabs-thumb" data-auth-thumb></span>
            <button type="button" class="auth-modal__tab is-active" data-tab="signup" role="tab">Criar</button>
            <button type="button" class="auth-modal__tab" data-tab="login" role="tab">Já tenho</button>
        </div>

        <form id="form-signup" class="auth-modal__form" novalidate>
            <div class="field">
                <label class="field__label" for="signup-name">Como você se chama</label>
                <input type="text" id="signup-name" name="name" required autocomplete="name" placeholder="Seu nome">
            </div>
            <div class="field">
                <label class="field__label" for="signup-email">E-mail</label>
                <input type="email" id="signup-email" name="email" required autocomplete="email" placeholder="voce@exemplo.com">
            </div>
            <div class="field">
                <label class="field__label" for="signup-phone">Telefone (opcional)</label>
                <input type="tel" id="signup-phone" name="phone" autocomplete="tel" placeholder="(11) 99999-0000">
            </div>
            <div class="field">
                <label class="field__label" for="signup-password">Senha</label>
                <input type="password" id="signup-password" name="password" required autocomplete="new-password" placeholder="·······">
            </div>
            <div id="signup-error" class="auth-modal__alert is-hidden" role="alert"></div>
            <button type="submit" class="auth-modal__submit">Criar conta e reservar →</button>
        </form>

        <form id="form-login" class="auth-modal__form is-hidden" novalidate hidden>
            <div class="field">
                <label class="field__label" for="login-email">E-mail</label>
                <input type="email" id="login-email" name="email" required autocomplete="email" placeholder="voce@exemplo.com">
            </div>
            <div class="field">
                <label class="field__label" for="login-password">Senha</label>
                <input type="password" id="login-password" name="password" required autocomplete="current-password" placeholder="·······">
            </div>
            <div id="login-error" class="auth-modal__alert is-hidden" role="alert"></div>
            <button type="submit" class="auth-modal__submit">Entrar e reservar →</button>
        </form>

        <div id="auth-success" class="auth-modal__alert auth-modal__alert--success is-hidden" role="status">
            Tudo certo! Continuando…
        </div>
    </section>
</div>
