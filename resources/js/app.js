import './bootstrap';

function initAuthNavigation(attempt = 0) {
    const authLayout = document.querySelector('.auth-layout');
    const header = document.querySelector('.site-header');
    const navToggle = document.querySelector('.nav-toggle');
    const navMenu = document.querySelector('#menu-principal');

    if (!authLayout || !header || !navToggle || !navMenu) {
        if (attempt < 20) {
            window.setTimeout(() => initAuthNavigation(attempt + 1), 50);
        }

        return;
    }

    if (header.dataset.authNavReady === 'true') {
        return;
    }

    header.dataset.authNavReady = 'true';

    const setMenuOpen = (isOpen) => {
        header.classList.toggle('nav-open', isOpen);
        navToggle.setAttribute('aria-expanded', String(isOpen));
    };

    navToggle.addEventListener('click', (event) => {
        event.stopPropagation();
        const isOpen = header.classList.contains('nav-open');

        setMenuOpen(!isOpen);
    });

    navMenu.addEventListener('click', (event) => {
        if (event.target.closest('a')) {
            setMenuOpen(false);
        }
    });

    document.addEventListener('click', (event) => {
        if (!header.classList.contains('nav-open')) {
            return;
        }

        if (!header.contains(event.target)) {
            setMenuOpen(false);
        }
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
            setMenuOpen(false);
        }
    });
}

function initAppDrawerNavigation() {
    const headers = document.querySelectorAll('[data-app-header]');
    if (!headers.length) return;

    headers.forEach((header) => {
        if (header.dataset.drawerReady === 'true') return;

        const toggle = header.querySelector('.app-nav-toggle');
        const drawer = header.querySelector('.app-drawer-nav');
        const overlay = header.querySelector('[data-app-nav-overlay]');
        const closeButton = header.querySelector('[data-app-nav-close]');

        if (!toggle || !drawer || !overlay) return;

        header.dataset.drawerReady = 'true';

        const setOpen = (isOpen) => {
            header.classList.toggle('nav-open', isOpen);
            document.body.classList.toggle('app-drawer-open', isOpen);
            toggle.setAttribute('aria-expanded', String(isOpen));
            overlay.hidden = !isOpen;
        };

        toggle.addEventListener('click', (event) => {
            event.stopPropagation();
            setOpen(!header.classList.contains('nav-open'));
        });

        closeButton?.addEventListener('click', () => setOpen(false));
        overlay.addEventListener('click', () => setOpen(false));

        drawer.addEventListener('click', (event) => {
            if (event.target.closest('a')) {
                setOpen(false);
            }
        });

        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape') {
                setOpen(false);
            }
        });

        window.addEventListener('resize', () => {
            if (window.matchMedia('(min-width: 1024px)').matches) {
                setOpen(false);
            }
        });
    });
}

let navigationReady = false;

function bootNavigation() {
    if (navigationReady) {
        return;
    }

    navigationReady = true;
    initAuthNavigation();
    initAppDrawerNavigation();
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', bootNavigation, { once: true });
} else {
    bootNavigation();
}
