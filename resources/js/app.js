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

initAuthNavigation();
document.addEventListener('DOMContentLoaded', () => initAuthNavigation());
window.addEventListener('load', () => initAuthNavigation());
