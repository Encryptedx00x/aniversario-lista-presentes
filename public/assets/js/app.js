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
        // Quando o select de quantidade muda num multi-gift, atualiza o
        // href do "Mandar pelo Pix" do mesmo card pra incluir &qty=N.
        document.addEventListener('change', (e) => {
            const qtyInput = e.target.closest('[data-gift-qty-input]');
            if (!qtyInput) return;
            const actions = qtyInput.closest('[data-gift-actions]');
            if (!actions) return;
            const qty = Math.max(1, parseInt(qtyInput.value, 10) || 1);
            const pixLink = actions.querySelector('.btn-pix-gift');
            if (pixLink) {
                const base = pixLink.dataset.pixBaseHref || pixLink.getAttribute('href') || '';
                const cleanBase = base.split('&qty=')[0];
                pixLink.setAttribute('href', cleanBase + (qty > 1 ? ('&qty=' + qty) : ''));
            }
        });

        document.addEventListener('click', (e) => {
            const reserveBtn = e.target.closest('.btn-choose-gift');
            const pixLink    = e.target.closest('.btn-pix-gift');
            const btn = reserveBtn || pixLink;
            if (!btn) return;

            const giftId = (btn.getAttribute('data-gift-id') || '').trim();
            if (!giftId) return;

            // Lê quantidade do select irmão dentro de [data-gift-actions].
            const actions = btn.closest('[data-gift-actions]');
            const qtyInput = actions?.querySelector('[data-gift-qty-input]');
            const qty = qtyInput ? Math.max(1, parseInt(qtyInput.value, 10) || 1) : 1;

            const pageMeta = document.getElementById('page-meta');
            const loggedIn = pageMeta?.dataset?.loggedIn === '1'
                || btn.dataset.loggedIn === '1';

            if (!loggedIn) {
                e.preventDefault();
                if (pixLink) {
                    // Going to /pix?gift_id=X — never trigger reserve fallback.
                    window._pendingNavigateUrl = pixLink.getAttribute('href') || '';
                    window._pendingGiftId      = null;
                } else {
                    // Reserve flow — auto-click the same button after auth.
                    window._pendingNavigateUrl = null;
                    window._pendingGiftId      = giftId;
                    window._pendingGiftQty     = qty;
                }
                this._openAuthModal();
                return;
            }

            // Logged in: PIX link navigates naturally to /pix?gift_id=X
            if (pixLink) return;

            e.preventDefault();
            this._reserveGift(reserveBtn, giftId, qty);
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

    _reserveGift(btn, giftId, qty) {
        if (!giftId) return;
        const sendQty = Math.max(1, parseInt(qty, 10) || 1);

        btn.disabled = true;

        // Token fresco — evita 403 quando o token da página foi consumido
        // por algum POST anterior na sessão (CSRF é one-shot).
        this._fetchFreshCsrfToken()
            .then((freshToken) => {
                const body = new URLSearchParams({
                    gift_id:    String(giftId),
                    qty:        String(sendQty),
                    csrf_token: freshToken,
                });
                return fetch('/reserve', {
                    method:  'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
                        'Accept':       'application/json',
                    },
                    body: body.toString(),
                });
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
                if (btn && btn.classList.contains('btn-choose-gift')) {
                    btn.disabled = false;
                }
            });
    },

    _onReserveSuccess(btn, data) {
        const card         = btn.closest('.gift-card');
        const availableQty = Number(data.available_qty);
        const isExhausted  = Boolean(data.is_exhausted);

        if (card) {
            const qtyEl = card.querySelector('.gift-card__qty, .gift-card__qty--out');
            if (qtyEl && Number.isFinite(availableQty)) {
                qtyEl.textContent = availableQty + ' ' + (availableQty === 1 ? 'disponível' : 'disponíveis');
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

        this._markCardAsSelected(card, 'Selecionado');

        const redirectUrl = data?.redirect_url || btn?.dataset?.redirectUrl || '';
        if (redirectUrl) {
            // Presente com link da loja: modal com aviso + countdown 10s.
            this._showReserveToast('Reserva concluída! Veja em "Minha Lista".', false);
            this._showRedirectCountdown(redirectUrl);
        } else {
            // Sem link da loja: a pessoa precisa pagar via PIX. Mostra toast
            // e leva pra Minha Lista depois de ~2.2s pra ela ver os botões
            // (Pagar com PIX, Cancelar) sem precisar navegar manualmente.
            this._showReserveToast('Reserva feita! Vou te levar pra Minha Lista pra você seguir.', false);
            setTimeout(() => { window.location.href = '/my-gifts'; }, 2200);
        }
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

    _markCardAsSelected(card, label) {
        if (!card) return;
        const ctaContainer = card.querySelector('.gift-card__foot');
        if (!ctaContainer) return;
        const currentCtas = ctaContainer.querySelectorAll('.gift-card__cta');
        currentCtas.forEach((node) => node.remove());

        const selectedBtn = document.createElement('button');
        selectedBtn.className = 'gift-card__cta';
        selectedBtn.disabled = true;
        selectedBtn.textContent = label;
        ctaContainer.appendChild(selectedBtn);
    },

    _showRedirectCountdown(redirectUrl) {
        if (!redirectUrl) return;
        const modal = document.getElementById('reserveRedirectModal');
        const countdownEl = document.getElementById('reserve-redirect-countdown');
        const linkEl = document.getElementById('reserve-redirect-link');
        if (!modal || !countdownEl || !linkEl) return;

        linkEl.href = redirectUrl;
        let seconds = 10;
        countdownEl.textContent = String(seconds);
        modal.classList.add('is-open');

        const stop = () => {
            clearInterval(modal._countdownTimer);
            modal._countdownTimer = null;
            modal.classList.remove('is-open');
        };

        clearInterval(modal._countdownTimer);
        modal._countdownTimer = setInterval(() => {
            seconds -= 1;
            countdownEl.textContent = String(Math.max(0, seconds));
            if (seconds <= 0) {
                clearInterval(modal._countdownTimer);
                window.location.href = redirectUrl;
            }
        }, 1000);

        // Botão "Cancelar redirecionamento": para o timer, fecha o modal,
        // deixa o usuário continuar na página. A reserva já foi feita,
        // o link continua disponível em /my-gifts depois.
        const cancelBtn = modal.querySelector('[data-redirect-cancel]');
        if (cancelBtn && !cancelBtn._wired) {
            cancelBtn._wired = true;
            cancelBtn.addEventListener('click', stop);
        }
        modal.addEventListener('click', (e) => {
            if (e.target === modal) stop();
        }, { once: true });
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
                this._submitAuthForm(formSignup, signupError, '/auth/guest-signup');
            });
        }

        // Login
        const formLogin  = modal.querySelector('#form-login');
        const loginError = modal.querySelector('#login-error');
        if (formLogin) {
            formLogin.addEventListener('submit', (e) => {
                e.preventDefault();
                this._submitAuthForm(formLogin, loginError, '/auth/guest-login');
            });
        }
    },

    _submitAuthForm(form, errEl, url) {
        if (errEl) errEl.classList.add('is-hidden');

        // Token fresco antes de POSTar — o CsrfMiddleware consome o token
        // a cada POST, então o que está no DOM pode estar stale e gerar
        // 403, que vinha aparecendo como "Erro inesperado".
        this._fetchFreshCsrfToken()
            .then((freshToken) => {
                const fd = new FormData(form);
                fd.set('csrf_token', freshToken);
                return fetch(url, {
                    method:  'POST',
                    headers: { 'Accept': 'application/json' },
                    body:    fd,
                });
            })
            .then(async (r) => {
                const text = await r.text();
                let data = null;
                try { data = JSON.parse(text); } catch (_) { /* not JSON */ }

                if (!data) {
                    // Servidor devolveu HTML (provável 403 ou 5xx). Surface
                    // uma mensagem mais útil em vez de "erro inesperado".
                    throw new Error(
                        r.status === 403 ? 'Sessão expirou. Recarregue a página e tente de novo.'
                      : r.status >= 500  ? 'O servidor teve um problema. Tente em alguns segundos.'
                      :                    'Não foi possível conectar agora. Tente de novo.'
                    );
                }
                return data;
            })
            .then((data) => {
                if (data.success) this._onAuthSuccess();
                else if (errEl) {
                    errEl.textContent = data.error || 'Não foi possível concluir.';
                    errEl.classList.remove('is-hidden');
                }
            })
            .catch((err) => {
                console.error('[auth] submit failed:', err);
                if (errEl) {
                    errEl.textContent = err.message || 'Erro inesperado. Tente novamente.';
                    errEl.classList.remove('is-hidden');
                }
            });
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

        document.querySelectorAll('.btn-choose-gift[data-gift-id], .btn-pix-gift[data-gift-id]')
            .forEach((btn) => { btn.dataset.loggedIn = '1'; });

        // Reveal the "Minha Lista" nav link without requiring a hard refresh.
        document.querySelectorAll('[data-nav-mygifts]').forEach((el) => {
            el.removeAttribute('hidden');
            el.style.display = '';
        });

        setTimeout(() => {
            this._closeAuthModal();

            const pendingUrl    = window._pendingNavigateUrl;
            const pendingGiftId = window._pendingGiftId;

            if (pendingUrl) {
                window._pendingNavigateUrl = null;
                window._pendingGiftId      = null;
                window.location.href = pendingUrl;
                return;
            }

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
    // Pix page — MercadoPago dynamic flow
    //   - User picks amount.
    //   - Click "Gerar PIX" → POST /pix/create → render QR + copia-e-cola.
    //   - Poll GET /pix/status/{id} every 4s.
    //   - On status='approved' → message + redirect to /my-gifts.
    // ──────────────────────────────────────────────────────────────────────
    initPix() {
        const flowEl = document.querySelector('[data-pix-flow]');
        if (!flowEl) return;

        const amountInput  = flowEl.querySelector('[data-pix-amount]');
        const presets      = flowEl.querySelectorAll('[data-pix-preset]');
        const generateBtn  = flowEl.querySelector('[data-pix-generate]');
        const generateCopy = flowEl.querySelector('[data-pix-generate-copy]');
        const errorEl      = flowEl.querySelector('[data-pix-error]');
        const qrSlot       = flowEl.querySelector('[data-pix-qr-slot]');
        const qrImg        = flowEl.querySelector('[data-pix-qr-img]');
        const qrText       = flowEl.querySelector('[data-pix-qr-text]');
        const qrAmount     = flowEl.querySelector('[data-pix-qr-amount]');
        const qrExpires    = flowEl.querySelector('[data-pix-qr-expires]');
        const copyBtn      = flowEl.querySelector('[data-pix-copy]');
        const statusEl     = flowEl.querySelector('[data-pix-status]');
        const statusMsg    = flowEl.querySelector('[data-pix-status-msg]');

        // Preset / amount input sync (independent of generation flow).
        if (amountInput && presets.length) {
            const sync = (val) => {
                presets.forEach((p) => {
                    p.classList.toggle('is-active', String(p.dataset.value) === String(val));
                });
            };
            presets.forEach((preset) => {
                preset.addEventListener('click', () => {
                    amountInput.value = preset.dataset.value || '0';
                    sync(amountInput.value);
                });
            });
            amountInput.addEventListener('input', () => sync(amountInput.value));
        }

        // Copy-to-clipboard for the dynamic QR code text.
        if (copyBtn) {
            copyBtn.addEventListener('click', () => {
                if (!qrText) return;
                const code     = qrText.textContent.trim();
                const original = copyBtn.dataset.defaultLabel || copyBtn.textContent;
                const copied   = copyBtn.dataset.copiedLabel  || '✓ Copiado';
                const finalize = (ok) => {
                    if (!ok) return;
                    copyBtn.classList.add('is-copied');
                    copyBtn.textContent = copied;
                    setTimeout(() => {
                        copyBtn.classList.remove('is-copied');
                        copyBtn.textContent = original;
                    }, 1800);
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

        // Generate-PIX click → either auth-gate or POST /pix/create.
        if (generateBtn) {
            generateBtn.addEventListener('click', () => {
                const loggedIn = flowEl.dataset.loggedIn === '1';
                if (!loggedIn) {
                    window._pendingNavigateUrl = window.location.pathname + window.location.search;
                    window._pendingGiftId      = null;
                    this._openAuthModal();
                    return;
                }
                this._pixCreate(flowEl);
            });
        }
    },

    _pixCreate(flowEl) {
        // giftId pode ser '0' ou vazio quando o usuário escolhe contribuição livre
        // (sem item específico). O backend resolve pra um gift sentinel nesse caso.
        const giftId      = flowEl.dataset.giftId || '0';
        const amountInput = flowEl.querySelector('[data-pix-amount]');
        const generateBtn = flowEl.querySelector('[data-pix-generate]');
        const generateCopy = flowEl.querySelector('[data-pix-generate-copy]');
        const errorEl     = flowEl.querySelector('[data-pix-error]');

        const amount = Math.max(1, Math.floor(parseFloat(amountInput?.value || '0') || 0));
        if (amount < 1) {
            this._pixShowError(errorEl, 'Confira o valor antes de gerar o PIX.');
            return;
        }

        if (errorEl) errorEl.hidden = true;
        if (generateBtn) {
            generateBtn.disabled    = true;
            generateBtn.textContent = 'Gerando QR…';
        }

        // Token fresco — o data-csrf da página pode estar stale se um POST
        // anterior na sessão consumiu o token (CsrfMiddleware::validate
        // faz unset depois de cada validação).
        this._fetchFreshCsrfToken()
            .then((freshToken) => {
                const body = new URLSearchParams({
                    gift_id:    String(giftId),
                    amount:     String(amount),
                    csrf_token: freshToken,
                });
                return fetch('/pix/create', {
                    method:  'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
                        'Accept':       'application/json',
                    },
                    body: body.toString(),
                });
            })
            .then(async (r) => {
                let data = null;
                try { data = await r.json(); } catch (_) { /* keep null */ }
                if (!r.ok && !data) throw new Error('HTTP ' + r.status);
                return data || { success: false, error: 'Resposta inválida do servidor.' };
            })
            .then((data) => {
                if (!data.success) {
                    this._pixShowError(errorEl, data.error || 'Não foi possível gerar o PIX.');
                    if (generateBtn) {
                        generateBtn.disabled    = false;
                        generateBtn.textContent = 'Tentar novamente →';
                    }
                    return;
                }

                this._pixRenderQr(flowEl, data);
                if (generateBtn)  generateBtn.hidden = true;
                if (generateCopy) generateCopy.hidden = true;
                this._pixStartPolling(flowEl, data.payment_id);
            })
            .catch((err) => {
                console.error('[pix-create] failed:', err);
                this._pixShowError(errorEl, 'Não foi possível conectar agora. Tente de novo em alguns segundos.');
                if (generateBtn) {
                    generateBtn.disabled    = false;
                    generateBtn.textContent = 'Tentar novamente →';
                }
            });
    },

    _pixRenderQr(flowEl, data) {
        const qrSlot    = flowEl.querySelector('[data-pix-qr-slot]');
        const qrImg     = flowEl.querySelector('[data-pix-qr-img]');
        const qrText    = flowEl.querySelector('[data-pix-qr-text]');
        const qrAmount  = flowEl.querySelector('[data-pix-qr-amount]');
        const qrExpires = flowEl.querySelector('[data-pix-qr-expires]');

        if (qrImg && data.qr_code_b64) {
            qrImg.src = 'data:image/png;base64,' + data.qr_code_b64;
        }
        if (qrText) qrText.textContent = data.qr_code || '';
        if (qrAmount) {
            qrAmount.textContent = 'R$ ' + (Number(data.amount) || 0).toLocaleString('pt-BR', {
                minimumFractionDigits: 2, maximumFractionDigits: 2,
            });
        }
        if (qrExpires && data.expires_at) {
            try {
                const dt = new Date(data.expires_at);
                qrExpires.textContent = dt.toLocaleString('pt-BR', {
                    day: '2-digit', month: '2-digit', hour: '2-digit', minute: '2-digit',
                });
            } catch (_) { qrExpires.textContent = '—'; }
        }
        if (qrSlot) qrSlot.hidden = false;
    },

    _pixStartPolling(flowEl, paymentId) {
        const statusEl  = flowEl.querySelector('[data-pix-status]');
        const statusMsg = flowEl.querySelector('[data-pix-status-msg]');
        if (!paymentId) return;

        const setStatus = (cls, msg) => {
            if (statusEl) {
                statusEl.classList.remove('pix-status--idle', 'pix-status--ok', 'pix-status--err');
                statusEl.classList.add('pix-status--' + cls);
            }
            if (statusMsg) statusMsg.textContent = msg;
        };

        setStatus('idle', 'Aguardando o pagamento cair no MercadoPago…');

        let stopped = false;
        const tick = () => {
            if (stopped) return;
            fetch('/pix/status/' + encodeURIComponent(paymentId), {
                method: 'GET',
                headers: { 'Accept': 'application/json' },
            })
                .then((r) => r.ok ? r.json() : null)
                .then((data) => {
                    if (!data || !data.success) return;
                    const status = String(data.status || '');
                    if (status === 'approved') {
                        stopped = true;
                        clearInterval(handle);
                        setStatus('ok', '✓ Pagamento confirmado! Levando você pra Minha Lista…');
                        setTimeout(() => { window.location.href = '/my-gifts'; }, 1500);
                    } else if (status === 'rejected' || status === 'cancelled' || status === 'expired') {
                        stopped = true;
                        clearInterval(handle);
                        setStatus('err', 'O pagamento não foi concluído (' + status + '). Pode gerar um novo.');
                    }
                })
                .catch((err) => { console.warn('[pix-poll]', err); });
        };

        const handle = setInterval(tick, 4000);
        // First poll right away so the user sees something update.
        tick();
    },

    _pixShowError(el, msg) {
        if (!el) { alert(msg); return; }
        el.textContent = msg;
        el.hidden = false;
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
