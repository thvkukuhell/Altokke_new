document.addEventListener('DOMContentLoaded', function () {
    const observer = new IntersectionObserver((entries) => {
        entries.forEach((entry) => {
            if (entry.isIntersecting) {
                entry.target.classList.add('visible');
                observer.unobserve(entry.target);
            }
        });
    }, {
        threshold: 0.12
    });

    document.querySelectorAll('.reveal').forEach((element) => {
        observer.observe(element);
    });

    const header = document.querySelector('.site-header');
    const navToggle = document.querySelector('.nav-toggle');
    const navMenu = document.querySelector('#menu-principal');

    window.addEventListener('scroll', () => {
        if (header) {
            header.classList.toggle('scrolled', window.scrollY > 24);
        }
    });

    if (navToggle && header?.dataset.authNavReady !== 'true') {
        navToggle.addEventListener('click', () => {
            const isOpen = navToggle.getAttribute('aria-expanded') === 'true';
            navToggle.setAttribute('aria-expanded', String(!isOpen));

            if (header) {
                header.classList.toggle('nav-open', !isOpen);
            }
        });
    }

    if (navMenu && header?.dataset.authNavReady !== 'true') {
        navMenu.querySelectorAll('a').forEach((link) => {
            link.addEventListener('click', () => {
                if (navToggle) {
                    navToggle.setAttribute('aria-expanded', 'false');
                }

                if (header) {
                    header.classList.remove('nav-open');
                }
            });
        });
    }
});
