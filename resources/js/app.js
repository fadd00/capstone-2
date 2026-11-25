import './bootstrap';

import Alpine from 'alpinejs';
import gsap from 'gsap';
import { ScrollTrigger } from 'gsap/ScrollTrigger';
import Lenis from 'lenis';
import { animate } from 'motion';

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
            animateDialogContent(dialog);
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
            animateDialogContent(target);
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

function animateDialogContent(dialog) {
    const content = dialog.firstElementChild;
    if (!content || window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;

    animate(content, { opacity: [0, 1], y: [10, 0] }, {
        duration: 0.24,
        ease: [0.22, 1, 0.36, 1],
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
            if (!window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
                animate(sidebar, { opacity: 1 }, { duration: 0.18, ease: 'easeOut' });
            }
            return;
        }

        sidebar.classList.toggle('translate-x-0', open);
        sidebar.classList.toggle('-translate-x-full', !open);
        backdrop?.classList.toggle('hidden', !open);
        openBtn.setAttribute('aria-expanded', String(open));
        shell.inert = open;
        if (!window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
            animate(sidebar, { opacity: open ? [0.94, 1] : [1, 0.96] }, {
                duration: 0.2,
                ease: 'easeOut',
            });
        }

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

function initServerCheckinClock() {
    const timeElement = document.querySelector('[data-checkin-time][data-server-time]');
    const dateElement = document.querySelector('[data-checkin-date]');
    const dayElement = document.querySelector('[data-checkin-day]');
    if (!timeElement || !dateElement || !dayElement) return;

    const serverTime = Number(timeElement.dataset.serverTime) * 1000;
    const startedAt = performance.now();
    const dateFormatter = new Intl.DateTimeFormat('id-ID', {
        timeZone: 'Asia/Jakarta',
        day: '2-digit',
        month: 'long',
        year: 'numeric',
    });
    const dayFormatter = new Intl.DateTimeFormat('id-ID', {
        timeZone: 'Asia/Jakarta',
        weekday: 'long',
    });
    const timeFormatter = new Intl.DateTimeFormat('id-ID', {
        timeZone: 'Asia/Jakarta',
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit',
        hourCycle: 'h23',
    });

    const updateClock = () => {
        const currentTime = new Date(serverTime + performance.now() - startedAt);
        dateElement.textContent = dateFormatter.format(currentTime);
        dayElement.textContent = dayFormatter.format(currentTime);
        timeElement.textContent = timeFormatter.format(currentTime);
    };

    updateClock();
    window.setInterval(updateClock, 1000);
}

function initCheckinPhotoPreview() {
    const input = document.querySelector('[data-checkin-photo]');
    const container = document.querySelector('[data-photo-preview-container]');
    const preview = document.querySelector('[data-photo-preview]');
    const retake = document.querySelector('[data-photo-retake]');
    if (!(input instanceof HTMLInputElement) || !container || !(preview instanceof HTMLImageElement) || !retake) return;

    let previewUrl = null;

    const clearPhoto = () => {
        if (previewUrl) URL.revokeObjectURL(previewUrl);
        previewUrl = null;
        preview.removeAttribute('src');
        input.value = '';
        container.classList.add('hidden');
    };

    input.addEventListener('change', () => {
        const photo = input.files?.[0];
        if (!photo) {
            clearPhoto();
            return;
        }

        if (previewUrl) URL.revokeObjectURL(previewUrl);
        previewUrl = URL.createObjectURL(photo);
        preview.src = previewUrl;
        container.classList.remove('hidden');
    });

    retake.addEventListener('click', () => {
        clearPhoto();
        input.click();
    });
}

function initMotionInteractions() {
    if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;

    const cards = new Set(document.querySelectorAll(
        '#main-content [class*="grid"] > a, #main-content [class*="grid"] > article, #main-content [class*="grid"] > div[class*="border"], #main-content article'
    ));

    cards.forEach((card) => {
        card.addEventListener('pointerenter', () => {
            animate(card, { y: -3 }, { duration: 0.2, ease: 'easeOut' });
        });
        card.addEventListener('pointerleave', () => {
            animate(card, { y: 0 }, { duration: 0.22, ease: 'easeOut' });
        });
    });

    document.querySelectorAll('button[type="submit"]').forEach((button) => {
        button.addEventListener('pointerdown', () => {
            if (!button.disabled) {
                animate(button, { scale: [1, 0.97, 1] }, { duration: 0.22, ease: 'easeOut' });
            }
        });
    });
}

function initPageAnimations() {
    gsap.registerPlugin(ScrollTrigger);

    const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    if (!reducedMotion) {
        const lenis = new Lenis({
            autoRaf: false,
            smoothWheel: true,
            syncTouch: false,
        });

        lenis.on('scroll', ScrollTrigger.update);
        gsap.ticker.add((time) => lenis.raf(time * 1000));
        gsap.ticker.lagSmoothing(0);
        window.lenis = lenis;

        const heading = document.querySelector('.app-page-header h1');
        if (heading) {
            gsap.from(heading, {
                opacity: 0,
                y: 12,
                duration: 0.55,
                ease: 'power2.out',
                clearProps: 'opacity,transform',
            });
        }

        const cards = gsap.utils.toArray(
            '#main-content [class*="grid"] > a, #main-content [class*="grid"] > article, #main-content [class*="grid"] > div[class*="border"], #main-content article'
        );
        cards.forEach((card, index) => {
            gsap.from(card, {
                opacity: 0,
                y: 18,
                duration: 0.5,
                delay: Math.min(index % 4, 3) * 0.07,
                ease: 'power2.out',
                clearProps: 'opacity,transform',
                scrollTrigger: {
                    trigger: card,
                    start: 'top 94%',
                    once: true,
                },
            });
        });

        const tableRows = document.querySelectorAll('.responsive-table tbody tr');
        if (tableRows.length) {
            ScrollTrigger.batch(tableRows, {
                start: 'top 96%',
                once: true,
                onEnter: (batch) => {
                    gsap.from(batch, {
                        opacity: 0,
                        y: 12,
                        duration: 0.4,
                        stagger: 0.035,
                        ease: 'power2.out',
                        clearProps: 'opacity,transform',
                    });
                },
            });
        }

        document.querySelectorAll('#main-content form').forEach((element) => {
            gsap.from(element, {
                opacity: 0,
                y: 12,
                duration: 0.4,
                ease: 'power2.out',
                clearProps: 'opacity,transform',
                scrollTrigger: {
                    trigger: element,
                    start: 'top 96%',
                    once: true,
                },
            });
        });
    }

    window.addEventListener('load', () => ScrollTrigger.refresh(), { once: true });
}

document.addEventListener('DOMContentLoaded', () => {
    initConfirmDialog();
    initAppModals();
    initPopoverDropdowns();
    initSidebarDrawer();
    initPasswordToggles();
    initGeolocationButtons();
    initServerCheckinClock();
    initCheckinPhotoPreview();
    initPageAnimations();
    initMotionInteractions();
});
