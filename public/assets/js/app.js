// App JS — Gift page interactions + auth + reservations

const GiftPage = {
    _debounceTimer: null,

    init() {
        [
            'initFilter',
            'initChooseGift',
            'initAuthModal',
            'initSlugGeneration',
            'initImagePreview',
        ].forEach(fn => {
            try { this[fn](); } catch (err) { console.error('[GiftPage] ' + fn + ' failed:', err); }
        });
    },

    // --- Filter bar: search debounce + category/sort AJAX swap ---
    initFilter() {
        const searchInput    = document.getElementById('search-input');
        const categoryFilter = document.getElementById('category-filter');
        const sortFilter     = document.getElementById('sort-filter');
        const container      = document.getElementById('gifts-container');

        if (!searchInput || !categoryFilter || !sortFilter || !container) return;

        const fetchGifts = () => {
            const params = new URLSearchParams({
                search: searchInput.value,
                category: categoryFilter.value,
                sort: sortFilter.value,
            });

            container.style.opacity = '0.5';
            container.style.pointerEvents = 'none';

            fetch('/gifts/filter?' + params.toString())
                .then(r => {
                    if (!r.ok) { console.error('[filter] HTTP ' + r.status); }
                    return r.text();
                })
                .then(html => {
                    container.innerHTML = html;
                    container.style.opacity = '1';
                    container.style.pointerEvents = '';
                })
                .catch(err => {
                    console.error('[filter] fetch failed:', err);
                    container.style.opacity = '1';
                    container.style.pointerEvents = '';
                });
        };

        searchInput.addEventListener('input', () => {
            clearTimeout(GiftPage._debounceTimer);
            GiftPage._debounceTimer = setTimeout(fetchGifts, 300);
        });

        categoryFilter.addEventListener('change', fetchGifts);
        sortFilter.addEventListener('change', fetchGifts);
    },

    // --- Gift card "Escolher este presente" buttons (delegated) ---
    initChooseGift() {
        document.addEventListener('click', (e) => {
            const target = e.target;
            if (!(target instanceof Element)) return;

            const btn = target.closest('.btn-choose-gift');
            if (!btn) return;

            e.preventDefault();

            const giftId = (btn.getAttribute('data-gift-id') || '').trim();
            if (!giftId) {
                console.error('[choose-gift] missing data-gift-id on clicked button');
                return;
            }

            const pageLoggedIn = document.getElementById('page-meta')?.dataset?.loggedIn;
            const loggedIn = pageLoggedIn === '1' || btn.dataset.loggedIn === '1';

            window._pendingGiftId = giftId;

            if (!loggedIn) {
                const modalEl = document.getElementById('authModal');
                if (!modalEl) {
                    console.error('[choose-gift] auth modal not found');
                    return;
                }

                if (typeof bootstrap === 'undefined' || !bootstrap.Modal) {
                    console.error('[choose-gift] bootstrap modal unavailable');
                    return;
                }

                bootstrap.Modal.getOrCreateInstance(modalEl).show();
                return;
            }

            this._reserveGift(btn, giftId);
        });
    },

    _getCurrentCsrfToken() {
        const modalEl = document.getElementById('authModal');
        return modalEl?.dataset?.csrfToken || '';
    },

    _setCurrentCsrfToken(token) {
        const modalEl = document.getElementById('authModal');
        if (modalEl && token) {
            modalEl.dataset.csrfToken = token;
        }
    },

    _fetchFreshCsrfToken() {
        return fetch('/csrf-token', {
            method: 'GET',
            headers: { 'Accept': 'application/json' },
        })
            .then(r => {
                if (!r.ok) {
                    throw new Error('csrf-token HTTP ' + r.status);
                }
                return r.json();
            })
            .then(data => {
                if (!data || typeof data.token !== 'string' || data.token.length < 16) {
                    throw new Error('csrf-token payload inválido');
                }
                this._setCurrentCsrfToken(data.token);
                return data.token;
            });
    },

    _reserveGift(btn, giftId) {
        if (!giftId) return;

        btn.disabled = true;

        this._fetchFreshCsrfToken()
            .catch((err) => {
                console.error('[reserve] failed to refresh csrf token:', err);
                return null;
            })
            .then(() => {
                const csrfToken = this._getCurrentCsrfToken();
                if (!csrfToken) {
                    throw new Error('csrf token ausente');
                }

                const body = new URLSearchParams({
                    gift_id: String(giftId),
                    csrf_token: csrfToken,
                });

                return fetch('/reserve', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
                        'Accept': 'application/json',
                    },
                    body: body.toString(),
                });
            })
            .then(async (r) => {
                let data = null;
                try {
                    data = await r.json();
                } catch (err) {
                    // Keep null; handled below.
                }

                if (!r.ok && !data) {
                    throw new Error('reserve HTTP ' + r.status);
                }

                return data || { success: false, error: 'Resposta inválida do servidor.' };
            })
            .then((data) => {
                if (data.success) {
                    this._onReserveSuccess(btn, data);
                } else {
                    console.error('[reserve] backend returned error:', data);
                    this._onReserveError(btn, data);
                }
            })
            .catch((err) => {
                console.error('[reserve] request failed:', err);
                this._onReserveError(btn, {
                    success: false,
                    error: 'Não foi possível concluir sua reserva agora. Atualize a página e tente novamente.',
                    is_exhausted: false,
                });
            })
            .finally(() => {
                if (!btn.classList.contains('btn-secondary')) {
                    btn.disabled = false;
                }
            });
    },

    _showReserveToast(message, isError = false) {
        this._showReserveBanner(message, isError);

        const toastEl = document.getElementById('reserve-toast');
        const msgEl = document.getElementById('reserve-toast-message');

        if (!toastEl || !msgEl) {
            alert(message);
            return;
        }

        msgEl.textContent = message;
        toastEl.classList.remove('text-bg-success', 'text-bg-danger');
        toastEl.classList.add(isError ? 'text-bg-danger' : 'text-bg-success');

        if (typeof bootstrap === 'undefined' || !bootstrap.Toast) {
            console.error('[reserve] bootstrap toast unavailable:', message);
            alert(message);
            return;
        }

        bootstrap.Toast.getOrCreateInstance(toastEl, { delay: 4500 }).show();
    },

    _showReserveBanner(message, isError = false) {
        const banner = document.getElementById('reserve-feedback');
        if (!banner) return;

        banner.textContent = message;
        banner.classList.remove('d-none', 'alert-success', 'alert-danger');
        banner.classList.add(isError ? 'alert-danger' : 'alert-success');
    },

    _onReserveSuccess(btn, data) {
        const card = btn.closest('.card');
        const availableQty = Number(data.available_qty);
        const isExhausted = Boolean(data.is_exhausted);

        if (card) {
            const qtyText = card.querySelector('p.small.text-muted.mt-auto.mb-0');
            if (qtyText && Number.isFinite(availableQty)) {
                qtyText.textContent = `Disponível: ${availableQty}`;
            }

            if (isExhausted) {
                card.classList.add('gift-card--esgotado');
                btn.className = 'btn btn-secondary w-100';
                btn.textContent = 'Indisponível';
                btn.disabled = true;
                btn.classList.remove('btn-choose-gift');
                delete btn.dataset.giftId;
                delete btn.dataset.loggedIn;
            }
        }

        this._showReserveToast('Reserva concluída! Veja em "Meus Presentes".', false);
    },

    _onReserveError(btn, data) {
        const message = data?.error || 'Não foi possível concluir a reserva.';

        if (data?.requires_auth) {
            const pageMeta = document.getElementById('page-meta');
            if (pageMeta) pageMeta.dataset.loggedIn = '0';
            document.querySelectorAll('.btn-choose-gift[data-gift-id]').forEach((el) => {
                el.dataset.loggedIn = '0';
            });
        }

        if (data?.is_exhausted) {
            const card = btn.closest('.card');
            if (card) {
                const qtyText = card.querySelector('p.small.text-muted.mt-auto.mb-0');
                if (qtyText) {
                    qtyText.textContent = 'Disponível: 0';
                }

                card.classList.add('gift-card--esgotado');
            }

            btn.className = 'btn btn-secondary w-100';
            btn.textContent = 'Indisponível';
            btn.disabled = true;
            btn.classList.remove('btn-choose-gift');
            delete btn.dataset.giftId;
            delete btn.dataset.loggedIn;
        }

        this._showReserveToast(message, true);

        if (data?.requires_auth) {
            const modalEl = document.getElementById('authModal');
            if (modalEl && typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                bootstrap.Modal.getOrCreateInstance(modalEl).show();
            }
        }
    },

    // --- Auth modal: tab switching + form submission ---
    initAuthModal() {
        const modalEl = document.getElementById('authModal');
        if (!modalEl) return;

        modalEl.addEventListener('show.bs.modal', () => {
            this._resetAuthModalState(true);
        });

        // Tab switching
        modalEl.addEventListener('click', (e) => {
            const tab = e.target.closest('[data-tab]');
            if (!tab) return;

            e.preventDefault();

            const target = tab.dataset.tab;
            const tabSignup = document.getElementById('tab-signup');
            const tabLogin = document.getElementById('tab-login');

            if (!tabSignup || !tabLogin) return;

            if (target === 'signup') {
                tabSignup.classList.remove('d-none');
                tabLogin.classList.add('d-none');
            } else {
                tabLogin.classList.remove('d-none');
                tabSignup.classList.add('d-none');
            }

            modalEl.querySelectorAll('[data-tab]').forEach(el => {
                el.classList.toggle('active', el.dataset.tab === target);
            });
        });

        // Signup form submission
        const formSignup = document.getElementById('form-signup');
        const signupError = document.getElementById('signup-error');

        if (formSignup) {
            formSignup.addEventListener('submit', (e) => {
                e.preventDefault();
                if (signupError) signupError.classList.add('d-none');

                const formData = new FormData(formSignup);
                formData.append('csrf_token', this._getCurrentCsrfToken());

                fetch('/auth/guest-signup', { method: 'POST', body: formData })
                    .then(r => r.json())
                    .then(data => {
                        if (data.success) {
                            GiftPage._onAuthSuccess();
                        } else if (signupError) {
                            signupError.textContent = data.error;
                            signupError.classList.remove('d-none');
                        }
                    })
                    .catch(() => {
                        if (signupError) {
                            signupError.textContent = 'Erro inesperado. Tente novamente.';
                            signupError.classList.remove('d-none');
                        }
                    });
            });
        }

        // Login form submission
        const formLogin = document.getElementById('form-login');
        const loginError = document.getElementById('login-error');

        if (formLogin) {
            formLogin.addEventListener('submit', (e) => {
                e.preventDefault();
                if (loginError) loginError.classList.add('d-none');

                const formData = new FormData(formLogin);
                formData.append('csrf_token', this._getCurrentCsrfToken());

                fetch('/auth/guest-login', { method: 'POST', body: formData })
                    .then(r => r.json())
                    .then(data => {
                        if (data.success) {
                            GiftPage._onAuthSuccess();
                        } else if (loginError) {
                            loginError.textContent = data.error;
                            loginError.classList.remove('d-none');
                        }
                    })
                    .catch(() => {
                        if (loginError) {
                            loginError.textContent = 'Erro inesperado. Tente novamente.';
                            loginError.classList.remove('d-none');
                        }
                    });
            });
        }
    },

    _onAuthSuccess() {
        const modalEl = document.getElementById('authModal');
        const successDiv = document.getElementById('auth-success');
        const pageMeta = document.getElementById('page-meta');

        if (successDiv) {
            successDiv.classList.remove('d-none');
        }

        if (pageMeta) {
            pageMeta.dataset.loggedIn = '1';
        }

        document.querySelectorAll('.btn-choose-gift[data-gift-id]').forEach((btn) => {
            btn.dataset.loggedIn = '1';
        });

        setTimeout(() => {
            if (modalEl) {
                bootstrap.Modal.getOrCreateInstance(modalEl).hide();
            }

            const pendingGiftId = window._pendingGiftId;
            if (!pendingGiftId) {
                return;
            }

            this._fetchFreshCsrfToken()
                .catch((err) => {
                    console.error('[auth] failed to refresh csrf token:', err);
                    return null;
                })
                .finally(() => {
                    window._pendingGiftId = null;
                    const btn = document.querySelector(`.btn-choose-gift[data-gift-id="${pendingGiftId}"]`);
                    if (btn) {
                        this._reserveGift(btn, pendingGiftId);
                    } else {
                        window.location.reload();
                    }
                });
        }, 1200);
    },

    _resetAuthModalState(clearForms = false) {
        const successDiv = document.getElementById('auth-success');
        const signupError = document.getElementById('signup-error');
        const loginError = document.getElementById('login-error');
        const tabSignup = document.getElementById('tab-signup');
        const tabLogin = document.getElementById('tab-login');
        const formSignup = document.getElementById('form-signup');
        const formLogin = document.getElementById('form-login');

        if (successDiv) successDiv.classList.add('d-none');
        if (signupError) signupError.classList.add('d-none');
        if (loginError) loginError.classList.add('d-none');

        if (tabSignup && tabLogin) {
            tabSignup.classList.remove('d-none');
            tabLogin.classList.add('d-none');
        }

        document.querySelectorAll('#authModal [data-tab]').forEach((el) => {
            el.classList.toggle('active', el.dataset.tab === 'signup');
        });

        if (clearForms) {
            if (formSignup) formSignup.reset();
            if (formLogin) formLogin.reset();
        }
    },

    // --- Slug auto-generation (admin gift form only) ---
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
                .normalize('NFD').replace(/[̀-ͯ]/g, '')
                .replace(/[^a-z0-9\s-]/g, '')
                .trim()
                .replace(/\s+/g, '-');
        });
    },

    // --- Image preview (admin gift form only) ---
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

document.addEventListener('DOMContentLoaded', () => GiftPage.init());
