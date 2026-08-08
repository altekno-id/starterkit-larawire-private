window.StarterThemeAdapter = Object.assign(window.StarterThemeAdapter || {}, {
    syncNavigationDetails(root = document) {
        root.querySelectorAll('[data-starter-navigation-details]').forEach((details) => {
            details.querySelector(':scope > summary')?.setAttribute('aria-expanded', String(details.open));
        });
    },
    syncDisclosureDetails(root = document) {
        root.querySelectorAll('details[data-starter-details]').forEach((details) => {
            details.querySelector(':scope > summary')?.setAttribute('aria-expanded', String(details.open));
        });
    },
    toggleNavigationDetails(summary) {
        const details = summary?.parentElement;

        if (! details?.matches('[data-starter-navigation-details]')) return;

        const opening = ! details.open;
        const navigation = details.closest('[data-starter-navigation]');

        if (opening && navigation) {
            navigation.querySelectorAll('[data-starter-navigation-details][open]').forEach((openDetails) => {
                const related = openDetails === details
                    || openDetails.contains(details)
                    || details.contains(openDetails);

                if (! related) {
                    openDetails.open = false;
                }
            });
        }

        details.open = opening;
        this.syncNavigationDetails(navigation || document);
    },
    closeNavigation() {
        document.querySelectorAll('.sidebar-wrapper.starter-sidebar-open').forEach((sidebar) => {
            sidebar.classList.remove('starter-sidebar-open');
        });

        document.querySelectorAll('[data-starter-sidebar-open][aria-expanded="true"]').forEach((button) => {
            button.setAttribute('aria-expanded', 'false');
        });
    },
    openNavigation(button = null) {
        document.querySelector('.sidebar-wrapper')?.classList.add('starter-sidebar-open');
        button?.setAttribute('aria-expanded', 'true');
    },
    closeAppMenu(menu) {
        menu?.removeAttribute('data-starter-open');
    },
    openAppMenu(menu) {
        menu?.setAttribute('data-starter-open', 'true');
    },
    modal(target) {
        if (! target) return null;

        const selector = target.startsWith('#') ? target : `#${target}`;

        try {
            return document.querySelector(selector);
        } catch (error) {
            return null;
        }
    },
    openModal(modal) {
        if (! modal) return;

        modal.classList.add('show', 'd-block');
        modal.setAttribute('aria-hidden', 'false');
        document.body.classList.add('starter-modal-open');
        requestAnimationFrame(() => {
            modal.querySelector('input:not([type="hidden"]), select, textarea, button, a[href]')?.focus();
        });
    },
    closeModal(modal) {
        if (! modal) return;

        modal.classList.remove('show', 'd-block');
        modal.setAttribute('aria-hidden', 'true');

        if (! document.querySelector('.modal.show, .modal.d-block')) {
            document.body.classList.remove('starter-modal-open');
        }
    },
    dispose() {
        this.closeNavigation();
        document.querySelectorAll('.modal.show, .modal.d-block').forEach((modal) => this.closeModal(modal));
    },
    prepare() {
        this.syncNavigationDetails();
        this.syncDisclosureDetails();

        if (this.eventsBound) return;

        document.addEventListener('click', (event) => {
            const sidebarOpen = event.target.closest('[data-starter-sidebar-open]');

            if (sidebarOpen) {
                event.preventDefault();
                this.openNavigation(sidebarOpen);
                return;
            }

            if (event.target.closest('[data-starter-sidebar-close]')) {
                event.preventDefault();
                this.closeNavigation();
                return;
            }

            const navigationSummary = event.target.closest('[data-starter-navigation-details] > summary');

            if (navigationSummary) {
                event.preventDefault();
                this.toggleNavigationDetails(navigationSummary);
                return;
            }

            const modalTrigger = event.target.closest('[data-bs-toggle="modal"]');

            if (modalTrigger) {
                event.preventDefault();
                this.openModal(this.modal(modalTrigger.getAttribute('data-bs-target') || modalTrigger.getAttribute('href')));
                return;
            }

            const modalDismiss = event.target.closest('[data-bs-dismiss="modal"]');

            if (modalDismiss) {
                if (modalDismiss.matches('.modal') && event.target !== modalDismiss) return;

                event.preventDefault();
                this.closeModal(modalDismiss.closest('.modal'));
            }
        });

        document.addEventListener('toggle', (event) => {
            if (event.target.matches?.('[data-starter-navigation-details]')) {
                this.syncNavigationDetails(event.target.closest('[data-starter-navigation]') || document);
            }

            if (event.target.matches?.('details[data-starter-details]')) {
                this.syncDisclosureDetails(event.target.parentElement || document);
            }
        }, true);

        document.addEventListener('keydown', (event) => {
            if (event.key !== 'Escape') return;

            const modal = document.querySelector('.modal.show, .modal.d-block');

            if (modal && ! modal.hasAttribute('wire:click.self')) {
                this.closeModal(modal);
                return;
            }

            this.closeNavigation();
        });

        this.eventsBound = true;
    },
});
