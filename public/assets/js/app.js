// App JS — Phase 1 stub

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
        const searchInput   = document.getElementById('search-input');
        const categoryFilter = document.getElementById('category-filter');
        const sortFilter    = document.getElementById('sort-filter');
        const container     = document.getElementById('gifts-container');

        if (!searchInput || !categoryFilter || !sortFilter || !container) return;

        const fetchGifts = () => {
            const params = new URLSearchParams({
                search:   searchInput.value,
                category: categoryFilter.value,
                sort:     sortFilter.value,
            });

            container.style.opacity       = '0.5';
            container.style.pointerEvents = 'none';

            fetch('/gifts/filter?' + params.toString())
                .then(r => {
                    if (!r.ok) { console.error('[filter] HTTP ' + r.status); }
                    return r.text();
                })
                .then(html => {
                    container.innerHTML           = html;
                    container.style.opacity       = '1';
                    container.style.pointerEvents = '';
                })
                .catch(err => {
                    console.error('[filter] fetch failed:', err);
                    container.style.opacity       = '1';
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
            const btn = e.target.closest('.btn-choose-gift');
            if (!btn) return;

            const giftId   = btn.dataset.giftId;
            const loggedIn = btn.dataset.loggedIn;

            window._pendingGiftId = giftId;

            if (loggedIn !== '1') {
                const modalEl = document.getElementById('authModal');
                if (!modalEl) return;
                bootstrap.Modal.getOrCreateInstance(modalEl).show();
            } else {
                // Phase 3 hook — reservation will be wired here
                console.log('Gift selected:', giftId);
            }
        });
    },

    // --- Auth modal: tab switching + form submission ---
    initAuthModal() {
        const modalEl = document.getElementById('authModal');
        if (!modalEl) return;

        // Tab switching
        modalEl.addEventListener('click', (e) => {
            const tab = e.target.closest('[data-tab]');
            if (!tab) return;

            const target    = tab.dataset.tab;
            const tabSignup = document.getElementById('tab-signup');
            const tabLogin  = document.getElementById('tab-login');

            if (!tabSignup || !tabLogin) return;

            if (target === 'signup') {
                tabSignup.classList.remove('d-none');
                tabLogin.classList.add('d-none');
            } else {
                tabLogin.classList.remove('d-none');
                tabSignup.classList.add('d-none');
            }

            // Toggle active on nav links
            modalEl.querySelectorAll('[data-tab]').forEach(el => {
                el.classList.toggle('active', el.dataset.tab === target);
            });
        });

        // Signup form submission
        const formSignup  = document.getElementById('form-signup');
        const signupError = document.getElementById('signup-error');

        if (formSignup) {
            formSignup.addEventListener('submit', (e) => {
                e.preventDefault();
                if (signupError) signupError.classList.add('d-none');

                const csrfToken = modalEl.dataset.csrfToken;
                const formData  = new FormData(formSignup);
                formData.append('csrf_token', csrfToken);

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
        const formLogin  = document.getElementById('form-login');
        const loginError = document.getElementById('login-error');

        if (formLogin) {
            formLogin.addEventListener('submit', (e) => {
                e.preventDefault();
                if (loginError) loginError.classList.add('d-none');

                const csrfToken = modalEl.dataset.csrfToken;
                const formData  = new FormData(formLogin);
                formData.append('csrf_token', csrfToken);

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
        const modalEl     = document.getElementById('authModal');
        const successDiv  = document.getElementById('auth-success');

        if (successDiv) {
            successDiv.classList.remove('d-none');
        }

        setTimeout(() => {
            bootstrap.Modal.getOrCreateInstance(modalEl).hide();

            const pendingGiftId = window._pendingGiftId;
            if (pendingGiftId) {
                window._pendingGiftId = null;
                const btn = document.querySelector(`.btn-choose-gift[data-gift-id="${pendingGiftId}"]`);
                if (btn) {
                    btn.dataset.loggedIn = '1';
                    btn.click();
                } else {
                    window.location.reload();
                }
            }
        }, 1200);
    },

    // --- Slug auto-generation (admin gift form only) ---
    initSlugGeneration() {
        const nameInput = document.querySelector('[name="name"]');
        const slugInput = document.querySelector('[name="slug"]');

        if (!nameInput || !slugInput) return;

        // Track manual edits
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
        const fileInput  = document.querySelector('[name="image"]');
        const imgPreview = document.getElementById('image-preview');

        if (!fileInput || !imgPreview) return;

        fileInput.addEventListener('change', () => {
            const file = fileInput.files[0];
            if (!file) return;

            const reader = new FileReader();
            reader.addEventListener('load', (e) => {
                imgPreview.src           = e.target.result;
                imgPreview.style.display = 'block';
            });
            reader.readAsDataURL(file);
        });
    },
};

document.addEventListener('DOMContentLoaded', () => GiftPage.init());
