window.StarterTemplate = window.StarterTemplate || {
    normalizeUrl(url) {
        const parsed = new URL(url, window.location.href);
        parsed.hash = '';
        parsed.search = '';
        parsed.pathname = parsed.pathname.replace(/\/+$/, '') || '/';

        return parsed.href;
    },
    isSameUrl(url, compareUrl = window.location.href) {
        return this.normalizeUrl(url) === this.normalizeUrl(compareUrl);
    },
    showNavigateLoader() {
        this.navigating = true;
        clearTimeout(this.navigateLoaderTimer);
        document.body?.classList.add('starter-is-navigating');
    },
    hideNavigateLoader() {
        this.navigating = false;
        clearTimeout(this.navigateLoaderTimer);
        this.navigateLoaderTimer = setTimeout(() => {
            if (! this.navigating) {
                document.body?.classList.remove('starter-is-navigating');
            }
        }, 140);
    },
    authLoginUrl(redirect = window.location.href) {
        const configured = document.querySelector('meta[name="starter-auth-login-url"]')?.content;
        const fallback = `${window.location.protocol}//auth.${window.location.hostname.replace(/^auth\./, '')}/login`;
        const url = new URL(configured || fallback, window.location.href);

        if (redirect) {
            url.searchParams.set('redirect', redirect);
        }

        return url.href;
    },
    redirectToLogin(redirect = window.location.href) {
        window.location.assign(this.authLoginUrl(redirect));
    },
    extractRedirectUrl(body) {
        try {
            const parsed = JSON.parse(body);

            return parsed?.redirect || null;
        } catch (error) {
            return null;
        }
    },
    navigate(url) {
        if (! url) return;

        const target = new URL(url, window.location.href);
        const current = new URL(window.location.href);

        if (this.isSameUrl(target.href, current.href)) {
            return;
        }

        if (target.origin === current.origin && window.Livewire && typeof window.Livewire.navigate === 'function') {
            this.showNavigateLoader();
            window.Livewire.navigate(target.href);
            return;
        }

        this.showNavigateLoader();
        window.location.assign(target.href);
    },
    closeOpenMenus() {
        document.querySelectorAll('[data-starter-details][open]').forEach((element) => {
            element.removeAttribute('open');
        });

        document.querySelectorAll('.navbar-collapse.show').forEach((element) => {
            const instance = window.bootstrap?.Collapse?.getInstance(element);
            instance ? instance.hide() : element.classList.remove('show');
        });
    },
    disposeBootstrap() {
        document.querySelectorAll('[data-starter-details][open]').forEach((element) => {
            element.removeAttribute('open');
        });

        document.querySelectorAll('.dropdown-menu.show').forEach((element) => element.classList.remove('show'));
        document.querySelectorAll('.dropdown-toggle[aria-expanded="true"]').forEach((element) => element.setAttribute('aria-expanded', 'false'));
        document.querySelectorAll('[data-bs-toggle="dropdown"]').forEach((element) => {
            window.bootstrap?.Dropdown?.getInstance(element)?.dispose();
        });

        document.querySelectorAll('[data-bs-toggle="collapse"]').forEach((element) => {
            window.bootstrap?.Collapse?.getInstance(element)?.dispose();
        });

        document.querySelectorAll('.collapse.show').forEach((element) => element.classList.remove('show'));
    },
    activateSidebar() {
        const sidebar = document.querySelector('#starter-sidebar-menu');
        const topbar = document.querySelector('#navbar-menu');

        document.querySelectorAll('[data-starter-menu-url]').forEach((link) => {
            link.classList.remove('active');
            link.removeAttribute('data-current');
            link.closest('.nav-item')?.classList.remove('active');
        });

        sidebar?.querySelectorAll('.starter-sidebar-details[open]').forEach((detail) => {
            detail.removeAttribute('open');
        });

        const activeLinks = Array.from(document.querySelectorAll('[data-starter-menu-url]'))
            .filter((link) => this.isSameUrl(link.dataset.starterMenuUrl));

        activeLinks.forEach((activeLink) => {
            activeLink.classList.add('active');
            activeLink.setAttribute('data-current', 'true');
            activeLink.closest('.nav-item')?.classList.add('active');

            if (! sidebar?.contains(activeLink)) {
                return;
            }

            let detail = activeLink.closest('.starter-sidebar-details');

            while (detail && sidebar.contains(detail)) {
                detail.setAttribute('open', '');
                detail.closest('.nav-item')?.classList.add('active');
                detail = detail.parentElement?.closest('.starter-sidebar-details') ?? null;
            }
        });

        topbar?.querySelectorAll('.dropdown-menu.show').forEach((menu) => menu.classList.remove('show'));
        topbar?.querySelectorAll('[aria-expanded="true"]').forEach((toggle) => toggle.setAttribute('aria-expanded', 'false'));
    },
    updateAccountSummary(detail = {}) {
        document.querySelectorAll('[data-starter-account-summary]').forEach((summary) => {
            const avatar = summary.querySelector('[data-starter-account-avatar]');
            const name = summary.querySelector('[data-starter-account-name]');
            const role = summary.querySelector('[data-starter-account-role]');

            if (detail.avatarUrl && avatar) {
                avatar.style.backgroundImage = `url("${String(detail.avatarUrl).replace(/"/g, '\\"')}")`;
            }

            if (detail.name && name) {
                name.textContent = detail.name;
            }

            if (detail.roleName && role) {
                role.textContent = detail.roleName;
            }
        });
    },
    toastStack() {
        let stack = document.querySelector('[data-starter-toast-stack]');

        if (! stack) {
            stack = document.createElement('div');
            stack.className = 'starter-toast-stack';
            stack.setAttribute('data-starter-toast-stack', '');
            stack.setAttribute('aria-live', 'polite');
            stack.setAttribute('aria-atomic', 'false');
            document.body.appendChild(stack);
        }

        return stack;
    },
    normalizeToastType(type) {
        return ['success', 'info', 'warning', 'danger', 'error'].includes(type) ? (type === 'error' ? 'danger' : type) : 'info';
    },
    toastIcon(type) {
        const icons = {
            success: '<path d="M5 12l5 5l10 -10"></path>',
            info: '<path d="M12 9h.01"></path><path d="M11 12h1v4h1"></path><path d="M12 3a9 9 0 1 0 0 18a9 9 0 0 0 0 -18"></path>',
            warning: '<path d="M12 9v4"></path><path d="M12 17h.01"></path><path d="M10.29 3.86l-8.09 14a2 2 0 0 0 1.71 3h16.18a2 2 0 0 0 1.71 -3l-8.09 -14a2 2 0 0 0 -3.42 0z"></path>',
            danger: '<path d="M12 12m-9 0a9 9 0 1 0 18 0a9 9 0 1 0 -18 0"></path><path d="M10 10l4 4m0 -4l-4 4"></path>',
        };

        return `<svg xmlns="http://www.w3.org/2000/svg" class="icon m-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">${icons[type]}</svg>`;
    },
    toast(detail = {}) {
        const payload = typeof detail === 'string' ? { message: detail } : (detail || {});
        const type = this.normalizeToastType(String(payload.type || payload.status || 'info'));
        const title = payload.title || {
            success: 'Berhasil',
            info: 'Informasi',
            warning: 'Perhatian',
            danger: 'Gagal',
        }[type];
        const message = payload.message || payload.text || '';
        const duration = Number(payload.duration ?? payload.timeout ?? 4500);
        const stack = this.toastStack();
        const toast = document.createElement('div');

        toast.className = `starter-toast starter-toast-${type}`;
        toast.setAttribute('data-starter-toast', '');
        toast.setAttribute('role', ['danger', 'warning'].includes(type) ? 'alert' : 'status');
        toast.style.setProperty('--starter-toast-duration', `${Math.max(duration, 1)}ms`);

        const icon = document.createElement('span');
        icon.className = 'starter-toast-icon';
        icon.innerHTML = this.toastIcon(type);

        const body = document.createElement('div');
        body.className = 'starter-toast-body';

        if (title) {
            const titleElement = document.createElement('div');
            titleElement.className = 'starter-toast-title';
            titleElement.textContent = title;
            body.appendChild(titleElement);
        }

        if (message) {
            const messageElement = document.createElement('div');
            messageElement.className = 'starter-toast-message';
            messageElement.textContent = message;
            body.appendChild(messageElement);
        }

        const close = document.createElement('button');
        close.type = 'button';
        close.className = 'btn-close starter-toast-close';
        close.setAttribute('aria-label', 'Close');
        close.setAttribute('data-starter-toast-dismiss', '');

        toast.append(icon, body, close);

        if (duration > 0) {
            const progress = document.createElement('div');
            progress.className = 'starter-toast-progress';
            toast.appendChild(progress);
        }

        stack.appendChild(toast);

        requestAnimationFrame(() => toast.classList.add('is-visible'));

        if (duration > 0) {
            toast.starterToastTimer = setTimeout(() => this.removeToast(toast), duration);
        }

        return toast;
    },
    removeToast(toast) {
        if (! toast) return;

        clearTimeout(toast.starterToastTimer);
        toast.classList.add('is-leaving');
        toast.classList.remove('is-visible');
        setTimeout(() => toast.remove(), 180);
    },
    bind() {
        if (this.bound) return;

        document.addEventListener('submit', (event) => {
            const form = event.target.closest('form[data-starter-logout-form]');
            const redirect = form?.querySelector('[data-starter-logout-redirect]');

            if (redirect) {
                redirect.value = window.location.href;
            }
        });

        document.addEventListener('click', (event) => {
            const toastDismiss = event.target.closest('[data-starter-toast-dismiss]');

            if (toastDismiss) {
                this.removeToast(toastDismiss.closest('[data-starter-toast]'));
                return;
            }

            const alertDismiss = event.target.closest('[data-starter-alert-dismiss]');

            if (alertDismiss) {
                alertDismiss.closest('[data-starter-alert]')?.remove();
                return;
            }

            document.querySelectorAll('[data-starter-details][open]').forEach((element) => {
                if (! element.contains(event.target)) {
                    element.removeAttribute('open');
                }
            });

            const link = event.target.closest('a[data-starter-navigate]');

            if (! link || event.defaultPrevented || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey || link.target === '_blank') {
                return;
            }

            event.preventDefault();
            this.closeOpenMenus();
            this.navigate(link.href);
        });

        document.addEventListener('starter-account-updated', (event) => {
            this.updateAccountSummary(event.detail || {});
        });

        ['starter-toast', 'toast', 'notify'].forEach((name) => {
            window.addEventListener(name, (event) => this.toast(event.detail || {}));
        });

        window.addEventListener('unhandledrejection', (event) => {
            const message = String(event.reason?.message || event.reason || '');

            if (! this.navigating || ! /NetworkError|Failed to fetch|Load failed/i.test(message)) {
                return;
            }

            event.preventDefault();
            this.redirectToLogin();
        });

        this.bound = true;
    },
    bindLivewire() {
        if (this.livewireBound || ! window.Livewire?.interceptRequest) return;

        window.Livewire.interceptRequest(({ onError }) => {
            onError(({ response, body, preventDefault }) => {
                if (![401, 419].includes(response.status)) {
                    return;
                }

                const redirect = this.extractRedirectUrl(body) || this.authLoginUrl();

                preventDefault();
                window.location.assign(redirect);
            });
        });

        this.livewireBound = true;
    },
    init() {
        this.bind();
        this.bindLivewire();
        this.activateSidebar();
    },
};

document.addEventListener('DOMContentLoaded', () => window.StarterTemplate.init());
document.addEventListener('livewire:initialized', () => window.StarterTemplate.bindLivewire());
document.addEventListener('livewire:navigate', () => window.StarterTemplate.showNavigateLoader());
document.addEventListener('livewire:navigating', () => window.StarterTemplate.disposeBootstrap());
document.addEventListener('livewire:navigated', () => {
    window.StarterTemplate.hideNavigateLoader();
    window.StarterTemplate.init();
});
window.addEventListener('pageshow', () => {
    window.StarterTemplate.hideNavigateLoader();
    window.StarterTemplate.init();
});
