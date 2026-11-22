import './bootstrap';

import Alpine from 'alpinejs';

window.Alpine = Alpine;
Alpine.start();

/**
 * 1. Native <dialog> confirmation handler for forms & buttons with [data-confirm]
 *    Replaces inline onsubmit="return confirm(...)" / onclick="return confirm(...)"
 */
function initConfirmDialog() {
    const dialog = document.getElementById('app-confirm-dialog');
    if (!dialog) return;

    const messageEl = dialog.querySelector('[data-confirm-message]');
    const titleEl = dialog.querySelector('[data-confirm-title]');
    let pendingForm = null;

    dialog.addEventListener('close', () => {
        if (dialog.returnValue === 'confirm' && pendingForm) {
            const formToSubmit = pendingForm;
            pendingForm = null;
            formToSubmit.dataset.confirmed = 'true';
            formToSubmit.submit();
        } else {
            pendingForm = null;
        }
    });

    document.addEventListener('submit', (event) => {
        const form = event.target;
        if (!(form instanceof HTMLFormElement)) return;

        const message = form.dataset.confirm;
        if (!message || form.dataset.confirmed === 'true') return;

        event.preventDefault();
        pendingForm = form;
        if (titleEl) {
            titleEl.textContent = form.dataset.confirmTitle || 'Konfirmasi Tindakan';
        }
        if (messageEl) {
            messageEl.textContent = message;
        }
        if (typeof dialog.showModal === 'function') {
            dialog.showModal();
        } else {
            form.dataset.confirmed = 'true';
            form.submit();
        }
    });
}

/**
 * 2. Native <dialog> bridge for custom event-driven modals (e.g., Breeze profile delete)
 */
function initAppModals() {
    const dialogs = document.querySelectorAll('dialog[data-modal-name]');
    if (!dialogs.length) return;

    dialogs.forEach((dialog) => {
        if (dialog.dataset.initialShow === 'true' && typeof dialog.showModal === 'function') {
            dialog.showModal();
        }

        // Light-dismiss fallback for browsers without native closedby="any"
        dialog.addEventListener('click', (event) => {
            if (event.target === dialog) {
                dialog.close('cancel');
            }
        });

        dialog.querySelectorAll('[data-dialog-close]').forEach((btn) => {
            btn.addEventListener('click', () => dialog.close('cancel'));
        });
    });

    window.addEventListener('open-modal', (event) => {
        const name = event.detail;
        const target = document.querySelector(`dialog[data-modal-name="${CSS.escape(String(name))}"]`);
        if (target && typeof target.showModal === 'function' && !target.open) {
            target.showModal();
        }
    });

    window.addEventListener('close-modal', (event) => {
        const name = event.detail;
        const target = document.querySelector(`dialog[data-modal-name="${CSS.escape(String(name))}"]`);
        if (target && target.open) {
            target.close('cancel');
        }
    });
}

/**
 * 3. Native Popover dropdown state sync + viewport-safe fallback positioning
 */
function initPopoverDropdowns() {
    const supportsPopover = Object.hasOwn(HTMLElement.prototype, 'popover');
    const supportsAnchor = typeof CSS !== 'undefined' && CSS.supports('position-area', 'block-end');

    document.querySelectorAll('[data-popover-trigger]').forEach((trigger) => {
        const targetId = trigger.getAttribute('popovertarget') || trigger.getAttribute('aria-controls');
        if (!targetId) return;
        const menu = document.getElementById(targetId);
        if (!menu) return;

        const positionFallback = () => {
            if (supportsAnchor) return;
            const rect = trigger.getBoundingClientRect();
            const menuRect = menu.getBoundingClientRect();
            const margin = 8;
            let top = rect.bottom + margin;
            let left = rect.right - menuRect.width;

            if (top + menuRect.height > window.innerHeight - margin) {
                top = Math.max(margin, rect.top - menuRect.height - margin);
            }
            left = Math.max(margin, Math.min(left, window.innerWidth - menuRect.width - margin));

            menu.style.top = `${top}px`;
            menu.style.left = `${left}px`;
        };

        if (supportsPopover) {
            menu.addEventListener('toggle', (event) => {
                const isOpen = event.newState === 'open';
                trigger.setAttribute('aria-expanded', String(isOpen));
                if (isOpen) {
                    positionFallback();
                }
            });
        } else {
            // Lightweight fallback for pre-Baseline browsers without Popover API
            trigger.addEventListener('click', (event) => {
                event.stopPropagation();
                const willOpen = !menu.classList.contains('is-open');
                menu.classList.toggle('is-open', willOpen);
                trigger.setAttribute('aria-expanded', String(willOpen));
                if (willOpen) {
                    positionFallback();
                }
            });
            document.addEventListener('click', (event) => {
                if (!menu.contains(event.target) && !trigger.contains(event.target)) {
                    menu.classList.remove('is-open');
                    trigger.setAttribute('aria-expanded', 'false');
                }
            });
            document.addEventListener('keydown', (event) => {
                if (event.key === 'Escape' && menu.classList.contains('is-open')) {
                    menu.classList.remove('is-open');
                    trigger.setAttribute('aria-expanded', 'false');
                    trigger.focus();
                }
            });
        }
    });
}

/**
 * 4. Accessible mobile sidebar drawer (inert background, focus management, Escape)
 */
function initSidebarDrawer() {
    const sidebar = document.getElementById('app-sidebar');
    const backdrop = document.getElementById('app-sidebar-backdrop');
    const shell = document.getElementById('app-shell');
    const openBtn = document.getElementById('sidebar-open-btn');
    const closeBtn = document.getElementById('sidebar-close-btn');
    if (!sidebar || !openBtn || !shell) return;

    const desktopQuery = window.matchMedia('(min-width: 1024px)');
    let isOpen = false;

    const setDrawerState = (open) => {
        isOpen = open;
        if (desktopQuery.matches) {
            sidebar.classList.remove('-translate-x-full');
            sidebar.removeAttribute('aria-modal');
            sidebar.removeAttribute('role');
            shell.inert = false;
            backdrop?.classList.add('hidden');
            openBtn.setAttribute('aria-expanded', 'false');
            return;
        }

        sidebar.classList.toggle('translate-x-0', open);
        sidebar.classList.toggle('-translate-x-full', !open);
        backdrop?.classList.toggle('hidden', !open);
        openBtn.setAttribute('aria-expanded', String(open));
        shell.inert = open;

        if (open) {
            sidebar.setAttribute('role', 'dialog');
            sidebar.setAttribute('aria-modal', 'true');
            closeBtn?.focus();
        } else {
            sidebar.removeAttribute('role');
            sidebar.removeAttribute('aria-modal');
            openBtn.focus();
        }
    };

    openBtn.addEventListener('click', () => setDrawerState(true));
    closeBtn?.addEventListener('click', () => setDrawerState(false));
    backdrop?.addEventListener('click', () => setDrawerState(false));

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && isOpen && !desktopQuery.matches) {
            setDrawerState(false);
        }
    });

    desktopQuery.addEventListener('change', () => setDrawerState(false));
}

/**
 * 5. Accessible password visibility toggle (aria-pressed)
 */
function initPasswordToggles() {
    document.querySelectorAll('[data-password-toggle]').forEach((button) => {
        const inputId = button.getAttribute('data-password-toggle');
        const input = inputId ? document.getElementById(inputId) : null;
        if (!input) return;

        button.addEventListener('click', () => {
            const showing = input.type === 'text';
            input.type = showing ? 'password' : 'text';
            button.setAttribute('aria-pressed', String(!showing));
            button.setAttribute('aria-label', showing ? 'Tampilkan kata sandi' : 'Sembunyikan kata sandi');
            button.textContent = showing ? 'Tampilkan' : 'Sembunyikan';
        });
    });
}

/**
 * 6. Geolocation GPS check-in handler (replaces inline <script> & alert())
 */
function initGeolocationButtons() {
    document.querySelectorAll('[data-geo-trigger]').forEach((button) => {
        const latInput = document.getElementById(button.dataset.geoLat || 'latitude');
        const lngInput = document.getElementById(button.dataset.geoLng || 'longitude');
        const statusEl = document.getElementById(button.dataset.geoStatus || 'geo-status');
        if (!latInput || !lngInput) return;

        const announce = (msg, isError = false) => {
            if (!statusEl) return;
            statusEl.textContent = msg;
            statusEl.className = isError
                ? 'text-xs text-red-700 mt-2'
                : 'text-xs text-emerald-700 mt-2';
        };

        button.addEventListener('click', () => {
            if (!('geolocation' in navigator)) {
                announce('Browser tidak mendukung layanan geolokasi.', true);
                return;
            }

            button.disabled = true;
            announce('Mengambil koordinat lokasi...');

            navigator.geolocation.getCurrentPosition(
                (position) => {
                    latInput.value = position.coords.latitude.toFixed(7);
                    lngInput.value = position.coords.longitude.toFixed(7);
                    button.disabled = false;
                    announce('Koordinat lokasi berhasil diperbarui.');
                },
                (error) => {
                    button.disabled = false;
                    announce(`Gagal mengambil koordinat: ${error.message}`, true);
                },
                { enableHighAccuracy: true, timeout: 10000 }
            );
        });
    });
}

document.addEventListener('DOMContentLoaded', () => {
    initConfirmDialog();
    initAppModals();
    initPopoverDropdowns();
    initSidebarDrawer();
    initPasswordToggles();
    initGeolocationButtons();
});
