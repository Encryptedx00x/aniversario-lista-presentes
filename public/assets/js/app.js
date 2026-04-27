// App JS — editorial design (countdown, parallax, filter, auth, reservation, pix)

const App = {
    _debounceTimer: null,

    init() {
        [
            'initFlashDismiss',
            'initFilter',
            'initCategoryPills',
            'initChooseGift',
            'initAuthModal',
            'initToastDismiss',
            'initSlugGeneration',
            'initImagePreview',
            'initCountdown',
            'initCoverParallax',
            'initPix',
        ].forEach((fn) => {
            try { this[fn](); } catch (err) { console.error('[App] ' + fn + ' failed:', err); }
        });
    },

    // ──────────────────────────────────────────────────────────────────────
    // Header / flash banner
    // ──────────────────────────────────────────────────────────────────────
    initFlashDismiss() {
        document.addEventListener('click', (e) => {
            const btn = e.target.closest('[data-dismiss-flash]');
            if (!btn) return;
            const banner = btn.closest('.flash-banner');
            if (banner) banner.remove();
        });
    },

    // ──────────────────────────────────────────────────────────────────────
    // Toast (replaces previous Bootstrap toast)
    // ──────────────────────────────────────────────────────────────────────
    initToastDismiss() {
        document.addEventListener('click', (e) => {
            const btn = e.target.closest('[data-dismiss-toast]');
            if (!btn) return;
            const toast = btn.closest('.toast');
            if (toast) toast.hidden = true;
        });
    },

    _showReserveToast(message, isError = false) {
        const toastEl = document.getElementById('reserve-toast');
        const msgEl   = document.getElementById('reserve-toast-message');

        if (!toastEl || !msgEl) {
            alert(message);
            return;
        }

        msgEl.textContent = message;
        toastEl.classList.remove('toast--error', 'toast--success');
        toastEl.classList.add(isError ? 'toast--error' : 'toast--success');
        toastEl.hidden = false;

        clearTimeout(toastEl._hideTimer);
        toastEl._hideTimer = setTimeout(() => { toastEl.hidden = true; }, 4500);
    },

    // ──────────────────────────────────────────────────────────────────────
    // Gifts filter — search + category pills + sort
    // ──────────────────────────────────────────────────────────────────────
    initFilter() {
        const searchInput    = document.getElementById('search-input');
        const categoryFilter = document.getElementById('category-filter');
        const sortFilter     = document.getElementById('sort-filter');
        const container      = document.getElementById('gifts-container');
        const countLabel     = document.getElementById('filter-count');

        if (!searchInput || !categoryFilter || !sortFilter || !container) return;

        const fetchGifts = () => {
            const params = new URLSearchParams({
                search:   searchInput.value,
                category: categoryFilter.value,
                sort:     sortFilter.value,
            });

            container.style.opacity = '0.5';
            container.style.pointerEvents = 'none';

            fetch('/gifts/filter?' + params.toString())
                .then((r) => r.text())
                .then((html) => {
                    container.innerHTML = html;
                    container.style.opacity = '1';
                    container.style.pointerEvents = '';
                    if (countLabel) {
                        const n = container.querySelectorAll('.gift-card').length;
                        countLabel.textContent = `${n} ${n === 1 ? 'item' : 'itens'}`;
                    }
                })
                .catch((err) => {
                    console.error('[filter] fetch failed:', err);
                    container.style.opacity = '1';
                    container.style.pointerEvents = '';
                });
        };

        searchInput.addEventListener('input', () => {
            clearTimeout(App._debounceTimer);
            App._debounceTimer = setTimeout(fetchGifts, 300);
        });
        sortFilter.addEventListener('change', fetchGifts);

        // expose so initCategoryPills can trigger
        App._fetchGifts = fetchGifts;
    },

    initCategoryPills() {
        const pills = document.querySelectorAll('[data-category-pill]');
        const hidden = document.getElementById('category-filter');
        if (!pills.length || !hidden) return;

        pills.forEach((pill) => {
            pill.addEventListener('click', () => {
                pills.forEach((p) => {
                    p.classList.remove('is-active');
                    p.setAttribute('aria-pressed', 'false');
                });
                pill.classList.add('is-active');
                pill.setAttribute('aria-pressed', 'true');
                hidden.value = pill.dataset.value || '';
                if (typeof App._fetchGifts === 'function') App._fetchGifts();
            });
        });
    },

    // ──────────────────────────────────────────────────────────────────────
    // Choose-gift / reservation flow
    // ──────────────────────────────────────────────────────────────────────
    initChooseGift() {
        document.addEventListener('click', (e) => {
            const btn = e.target.closest('.btn-choose-gift');
            if (!btn) return;

            e.preventDefault();
            const giftId = (btn.getAttribute('data-gift-id') || '').trim();
            if (!giftId) return;

            const pageMeta = document.getElementById('page-meta');
            const loggedIn = pageMeta?.dataset?.loggedIn || btn.dataset.loggedIn || '0';

            window._pendingGiftId = giftId;

            if (loggedIn !== '1') {
                this._openAuthModal();
                return;
            }
            this._reserveGift(btn, giftId);
        });
    },

    _openAuthModal() {
        const modal = document.getElementById('authModal');
        if (modal) modal.classList.add('is-open');
    },

    _closeAuthModal() {
        const modal = document.getElementById('authModal');
        if (modal) modal.classList.remove('is-open');
    },

    _getCurrentCsrfToken() {
        const modal = document.getElementById('authModal');
        return modal?.dataset?.csrfToken || '';
    },

    _setCurrentCsrfToken(token) {
        const modal = document.getElementById('authModal');
        if (modal && token) modal.dataset.csrfToken = token;
    },

    _fetchFreshCsrfToken() {
        return fetch('/csrf-token', {
            method:  'GET',
            headers: { 'Accept': 'application/json' },
        })
            .then((r) => {
                if (!r.ok) throw new Error('csrf HTTP ' + r.status);
                return r.json();
            })
            .then((data) => {
                if (!data || typeof data.token !== 'string' || data.token.length < 16) {
                    throw new Error('csrf payload inválido');
                }
                this._setCurrentCsrfToken(data.token);
                return data.token;
            });
    },

    _reserveGift(btn, giftId) {
        if (!giftId) return;

        const csrfToken = this._getCurrentCsrfToken();
        if (!csrfToken) {
            this._showReserveToast('Não foi possível validar sua sessão. Recarregue a página.', true);
            return;
        }

        btn.disabled = true;

        const body = new URLSearchParams({
            gift_id:    String(giftId),
            csrf_token: csrfToken,
        });

        fetch('/reserve', {
            method:  'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
                'Accept':       'application/json',
            },
            body: body.toString(),
        })
            .then(async (r) => {
                let data = null;
                try { data = await r.json(); } catch (_) { /* keep null */ }
                if (!r.ok && !data) throw new Error('reserve HTTP ' + r.status);
                return data || { success: false, error: 'Resposta inválida do servidor.' };
            })
            .then((data) => {
                if (data.success) {
                    this._onReserveSuccess(btn, data);
                } else {
                    this._onReserveError(btn, data);
                }
            })
            .catch((err) => {
                console.error('[reserve] request failed:', err);
                this._onReserveError(btn, {
                    success:      false,
                    error:        'Não foi possível concluir sua reserva agora. Tente novamente.',
                    is_exhausted: false,
                });
            })
            .finally(() => {
                if (!btn.disabled || !btn.classList.contains('gift-card__cta')) {
                    return;
                }
                if (!btn.matches('[disabled]')) btn.disabled = false;
            });
    },

    _onReserveSuccess(btn, data) {
        const card         = btn.closest('.gift-card');
        const availableQty = Number(data.available_qty);
        const isExhausted  = Boolean(data.is_exhausted);

        if (card) {
            const qtyEl = card.querySelector('.gift-card__qty, .gift-card__qty--out');
            if (qtyEl && Number.isFinite(availableQty)) {
                qtyEl.textContent = availableQty + ' ' + (availableQty === 1 ? 'vaga' : 'vagas');
            }
            const qtyText = card.querySelector('p.small.text-muted.mt-auto.mb-0');
            if (qtyText && Number.isFinite(availableQty)) {
                qtyText.textContent = `Disponível: ${availableQty}`;
            }

            if (isExhausted) {
                card.classList.add('gift-card--esgotado');
                btn.textContent = 'Indisponível';
                btn.disabled    = true;
                btn.classList.remove('btn-choose-gift');
                delete btn.dataset.giftId;
                delete btn.dataset.loggedIn;
            }
        }

        this._showReserveToast('Reserva concluída! Veja em "Minhas reservas".', false);
    },

    _onReserveError(btn, data) {
        const message = data?.error || 'Não foi possível concluir a reserva.';

        if (data?.is_exhausted) {
            const card = btn.closest('.gift-card');
            if (card) card.classList.add('gift-card--esgotado');

            btn.textContent = 'Indisponível';
            btn.disabled    = true;
            btn.classList.remove('btn-choose-gift');
            delete btn.dataset.giftId;
            delete btn.dataset.loggedIn;
        }

        this._showReserveToast(message, true);
    },

    // ──────────────────────────────────────────────────────────────────────
    // Auth modal — tabs + signup/login submit
    // ──────────────────────────────────────────────────────────────────────
    initAuthModal() {
        const modal = document.getElementById('authModal');
        if (!modal) return;

        // Backdrop click closes
        modal.addEventListener('click', (e) => {
            if (e.target === modal) this._closeAuthModal();
        });

        // Close button + tabs (delegated)
        modal.addEventListener('click', (e) => {
            if (e.target.closest('[data-auth-close]')) {
                this._closeAuthModal();
                return;
            }

            const tabBtn = e.target.closest('[data-tab]');
            if (!tabBtn) return;

            e.preventDefault();
            this._switchAuthTab(modal, tabBtn.dataset.tab);
        });

        // Signup
        const formSignup  = modal.querySelector('#form-signup');
        const signupError = modal.querySelector('#signup-error');
        if (formSignup) {
            formSignup.addEventListener('submit', (e) => {
                e.preventDefault();
                if (signupError) signupError.classList.add('is-hidden');

                const fd = new FormData(formSignup);
                fd.append('csrf_token', this._getCurrentCsrfToken());

                fetch('/auth/guest-signup', { method: 'POST', body: fd })
                    .then((r) => r.json())
                    .then((data) => {
                        if (data.success) this._onAuthSuccess();
                        else if (signupError) {
                            signupError.textContent = data.error;
                            signupError.classList.remove('is-hidden');
                        }
                    })
                    .catch(() => {
                        if (signupError) {
                            signupError.textContent = 'Erro inesperado. Tente novamente.';
                            signupError.classList.remove('is-hidden');
                        }
                    });
            });
        }

        // Login
        const formLogin  = modal.querySelector('#form-login');
        const loginError = modal.querySelector('#login-error');
        if (formLogin) {
            formLogin.addEventListener('submit', (e) => {
                e.preventDefault();
                if (loginError) loginError.classList.add('is-hidden');

                const fd = new FormData(formLogin);
                fd.append('csrf_token', this._getCurrentCsrfToken());

                fetch('/auth/guest-login', { method: 'POST', body: fd })
                    .then((r) => r.json())
                    .then((data) => {
                        if (data.success) this._onAuthSuccess();
                        else if (loginError) {
                            loginError.textContent = data.error;
                            loginError.classList.remove('is-hidden');
                        }
                    })
                    .catch(() => {
                        if (loginError) {
                            loginError.textContent = 'Erro inesperado. Tente novamente.';
                            loginError.classList.remove('is-hidden');
                        }
                    });
            });
        }
    },

    _switchAuthTab(modal, target) {
        const isLogin = target === 'login';

        modal.querySelectorAll('[data-tab]').forEach((el) => {
            el.classList.toggle('is-active', el.dataset.tab === target);
        });

        const thumb = modal.querySelector('[data-auth-thumb]');
        if (thumb) thumb.style.left = isLogin ? 'calc(50% + 0px)' : '4px';

        const heading = modal.querySelector('[data-auth-heading]');
        if (heading) heading.textContent = isLogin ? 'Já tenho conta' : 'Criar conta';

        const formSignup = modal.querySelector('#form-signup');
        const formLogin  = modal.querySelector('#form-login');
        if (formSignup) {
            formSignup.classList.toggle('is-hidden', isLogin);
            formSignup.hidden = isLogin;
        }
        if (formLogin) {
            formLogin.classList.toggle('is-hidden', !isLogin);
            formLogin.hidden = !isLogin;
        }
    },

    _onAuthSuccess() {
        const modal       = document.getElementById('authModal');
        const successDiv  = modal?.querySelector('#auth-success');
        const pageMeta    = document.getElementById('page-meta');

        if (successDiv) successDiv.classList.remove('is-hidden');
        if (pageMeta)   pageMeta.dataset.loggedIn = '1';

        document.querySelectorAll('.btn-choose-gift[data-gift-id]').forEach((btn) => {
            btn.dataset.loggedIn = '1';
        });

        setTimeout(() => {
            this._closeAuthModal();

            const pendingGiftId = window._pendingGiftId;
            if (!pendingGiftId) return;

            this._fetchFreshCsrfToken()
                .catch((err) => {
                    console.error('[auth] failed to refresh csrf token:', err);
                    return null;
                })
                .finally(() => {
                    window._pendingGiftId = null;
                    const btn = document.querySelector(
                        `.btn-choose-gift[data-gift-id="${pendingGiftId}"]`
                    );
                    if (btn) btn.click();
                    else window.location.reload();
                });
        }, 1200);
    },

    // ──────────────────────────────────────────────────────────────────────
    // Countdown + cover parallax
    // ──────────────────────────────────────────────────────────────────────
    initCountdown() {
        const cover = document.querySelector('[data-cover]');
        if (!cover) return;

        const targetIso = cover.getAttribute('data-target') || '';
        const target    = targetIso ? new Date(targetIso).getTime() : 0;
        if (!target) return;

        const $days = document.querySelector('[data-countdown-days]');
        const $hh   = document.querySelector('[data-countdown-hours]');
        const $mm   = document.querySelector('[data-countdown-minutes]');
        const $ss   = document.querySelector('[data-countdown-seconds]');
        const $eye  = document.querySelector('[data-countdown-days-eyebrow]');

        const pad = (n) => String(Math.max(0, n)).padStart(2, '0');

        const tick = () => {
            const diff = Math.max(0, target - Date.now());
            const d = Math.floor(diff / 86_400_000);
            const h = Math.floor((diff % 86_400_000) / 3_600_000);
            const m = Math.floor((diff % 3_600_000)  / 60_000);
            const s = Math.floor((diff % 60_000)     / 1_000);

            if ($days) $days.textContent = pad(d);
            if ($hh)   $hh.textContent   = pad(h);
            if ($mm)   $mm.textContent   = pad(m);
            if ($ss)   $ss.textContent   = pad(s);
            if ($eye)  $eye.textContent  = `Faltam ${d} dias`;
        };

        tick();
        setInterval(tick, 1000);
    },

    initCoverParallax() {
        const cover = document.querySelector('[data-cover]');
        if (!cover) return;
        if (window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;

        const blob1 = cover.querySelector('[data-cover-blob1]');
        const blob2 = cover.querySelector('[data-cover-blob2]');
        const rings = cover.querySelector('[data-cover-rings]');
        const days  = cover.querySelector('[data-cover-days-wrap]');

        const onMove = (e) => {
            const px = (e.clientX / window.innerWidth  - 0.5) * 16;
            const py = (e.clientY / window.innerHeight - 0.5) * 16;

            if (blob1) blob1.style.transform = `translate(${px * 2}px, ${py * 2}px)`;
            if (blob2) blob2.style.transform = `translate(${-px}px, ${-py}px)`;
            if (rings) rings.style.transform = `translate(-50%, -50%) translate(${px}px, ${py}px)`;
            if (days)  days.style.transform  = `translate(${px * 0.4}px, ${py * 0.4}px)`;
        };

        window.addEventListener('mousemove', onMove, { passive: true });
    },

    // ──────────────────────────────────────────────────────────────────────
    // Pix page interactions
    // ──────────────────────────────────────────────────────────────────────
    initPix() {
        const copyBtn = document.getElementById('pix-copy-btn');
        if (copyBtn) {
            copyBtn.addEventListener('click', () => {
                const code     = copyBtn.dataset.code || '';
                const original = copyBtn.dataset.defaultLabel || copyBtn.textContent;
                const copied   = copyBtn.dataset.copiedLabel  || '✓ Copiado';

                const finalize = (ok) => {
                    if (!ok) return;
                    copyBtn.classList.add('is-copied');
                    copyBtn.textContent = copied;
                    setTimeout(() => {
                        copyBtn.classList.remove('is-copied');
                        copyBtn.textContent = original;
                    }, 1500);
                };

                if (navigator.clipboard?.writeText) {
                    navigator.clipboard.writeText(code).then(() => finalize(true)).catch(() => finalize(false));
                } else {
                    const ta = document.createElement('textarea');
                    ta.value = code;
                    document.body.appendChild(ta);
                    ta.select();
                    try { document.execCommand('copy'); finalize(true); } catch (_) { finalize(false); }
                    document.body.removeChild(ta);
                }
            });
        }

        const amount  = document.getElementById('pix-amount');
        const presets = document.querySelectorAll('[data-pix-preset]');
        if (amount && presets.length) {
            const sync = (val) => {
                presets.forEach((p) => {
                    p.classList.toggle('is-active', String(p.dataset.value) === String(val));
                });
            };
            presets.forEach((preset) => {
                preset.addEventListener('click', () => {
                    amount.value = preset.dataset.value || '0';
                    sync(amount.value);
                });
            });
            amount.addEventListener('input', () => sync(amount.value));
        }

        const confirmBtn = document.getElementById('pix-confirm');
        if (confirmBtn) {
            confirmBtn.addEventListener('click', () => {
                confirmBtn.textContent = '✓ Recadinho registrado, obrigado!';
                confirmBtn.disabled    = true;
                setTimeout(() => {
                    confirmBtn.textContent = 'Confirmar e mandar →';
                    confirmBtn.disabled    = false;
                }, 2500);
            });
        }
    },

    // ──────────────────────────────────────────────────────────────────────
    // Admin form helpers (preserved from previous version)
    // ──────────────────────────────────────────────────────────────────────
    initSlugGeneration() {
        const nameInput = document.querySelector('[name="name"]');
        const slugInput = document.querySelector('[name="slug"]');
        if (!nameInput || !slugInput) return;

        slugInput.addEventListener('input', () => {
            slugInput.dataset.auto = 'false';
        });

        nameInput.addEventListener('input', () => {
            if (slugInput.dataset.auto === 'false') return;
            slugInput.value = nameInput.value
                .toLowerCase()
                .normalize('NFD').replace(/[\u0300-\u036f]/g, '')
                .replace(/[^a-z0-9\s-]/g, '')
                .trim()
                .replace(/\s+/g, '-');
        });
    },

    initImagePreview() {
        const fileInput = document.querySelector('[name="image"]');
        const imgPreview = document.getElementById('image-preview');
        if (!fileInput || !imgPreview) return;

        fileInput.addEventListener('change', () => {
            const file = fileInput.files[0];
            if (!file) return;
            const reader = new FileReader();
            reader.addEventListener('load', (e) => {
                imgPreview.src = e.target.result;
                imgPreview.style.display = 'block';
            });
            reader.readAsDataURL(file);
        });
    },
};

document.addEventListener('DOMContentLoaded', () => App.init());

// Expose for legacy callers
window.GiftPage = App;
