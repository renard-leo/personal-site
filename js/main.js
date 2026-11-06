import './consent.js';

const root = document.documentElement;
const darkScheme = window.matchMedia('(prefers-color-scheme: dark)');
const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)');

function readToken(name) {
    return getComputedStyle(root).getPropertyValue(name).trim();
}

function currentTheme() {
    return root.dataset.theme ?? (darkScheme.matches ? 'dark' : 'light');
}

function syncTheme() {
    const theme = currentTheme();
    for (const toggle of document.querySelectorAll('[data-theme-toggle]')) {
        toggle.setAttribute('aria-pressed', String(theme === 'dark'));
    }
    for (const meta of document.querySelectorAll('meta[name="theme-color"]')) {
        meta.content = root.dataset.theme ? meta.dataset[theme] : meta.dataset[meta.media.includes('dark') ? 'dark' : 'light'];
    }
}

function applyTheme(theme) {
    root.dataset.theme = theme;
    try {
        localStorage.setItem('theme', theme);
    } catch {
        // Stockage indisponible (navigation privée stricte) : le choix vaut pour la page en cours.
    }
    syncTheme();
}

function toggleTheme(event) {
    const next = currentTheme() === 'dark' ? 'light' : 'dark';

    if (!document.startViewTransition || reducedMotion.matches) {
        applyTheme(next);
        return;
    }

    const {left, top, width, height} = event.currentTarget.getBoundingClientRect();
    const x = left + width / 2;
    const y = top + height / 2;
    const radius = Math.hypot(Math.max(x, window.innerWidth - x), Math.max(y, window.innerHeight - y));

    root.classList.add('is-theme-transition');
    const transition = document.startViewTransition(() => applyTheme(next));
    transition.ready.then(() => {
        root.animate(
            {clipPath: [`circle(0 at ${x}px ${y}px)`, `circle(${radius}px at ${x}px ${y}px)`]},
            {duration: parseFloat(readToken('--dur-theme')) || 0, easing: readToken('--ease-out') || 'ease-out', pseudoElement: '::view-transition-new(root)'},
        );
    }).catch(() => {});
    transition.finished.finally(() => root.classList.remove('is-theme-transition'));
}

for (const toggle of document.querySelectorAll('[data-theme-toggle]')) {
    toggle.addEventListener('click', toggleTheme);
}
darkScheme.addEventListener('change', syncTheme);
syncTheme();

// Menu mobile (popover natif) : se referme après le choix d'une rubrique, ou quand le focus clavier en sort,
// pour ne pas recouvrir l'élément qui reçoit le focus (WCAG 2.4.11).
const menu = document.getElementById('nav-menu');
if (menu && typeof menu.hidePopover === 'function') {
    const menuToggle = document.querySelector('[popovertarget="nav-menu"]');
    menu.addEventListener('click', (event) => {
        if (event.target.closest('a') && menu.matches(':popover-open')) {
            menu.hidePopover();
        }
    });
    menu.addEventListener('focusout', ({relatedTarget}) => {
        if (relatedTarget && !menu.contains(relatedTarget) && relatedTarget !== menuToggle && menu.matches(':popover-open')) {
            menu.hidePopover();
        }
    });
}

// Rubrique courante dans la navigation.
const navLinks = new Map([...document.querySelectorAll('[data-nav-link]')].map((link) => [link.dataset.navLink, link]));
const sections = [...navLinks.keys()].map((id) => document.getElementById(id)).filter(Boolean);

if (sections.length > 0 && 'IntersectionObserver' in window) {
    const visible = new Set();
    const update = () => {
        const current = sections.find((section) => visible.has(section));
        for (const [id, link] of navLinks) {
            if (current?.id === id) {
                link.setAttribute('aria-current', 'true');
            } else {
                link.removeAttribute('aria-current');
            }
        }
    };
    const observer = new IntersectionObserver((entries) => {
        for (const entry of entries) {
            if (entry.isIntersecting) {
                visible.add(entry.target);
            } else {
                visible.delete(entry.target);
            }
        }
        update();
    }, {rootMargin: '-35% 0px -60% 0px'});
    sections.forEach((section) => observer.observe(section));
}
