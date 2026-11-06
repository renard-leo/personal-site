// Animations du site : bouton pause (RGAA 13.8), apparitions à l'entrée dans l'écran, fond qui réagit au pointeur.
const root = document.documentElement;
const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)');
const finePointer = window.matchMedia('(hover: hover) and (pointer: fine)');
const PARALLAX_EASING = 0.06;

function isPaused() {
    return root.dataset.motion === 'paused';
}

function syncToggles() {
    for (const toggle of document.querySelectorAll('[data-motion-toggle]')) {
        toggle.setAttribute('aria-pressed', String(isPaused()));
    }
}

function setPaused(paused) {
    if (paused) {
        root.dataset.motion = 'paused';
    } else {
        delete root.dataset.motion;
    }
    try {
        if (paused) {
            localStorage.setItem('motion', 'paused');
        } else {
            localStorage.removeItem('motion');
        }
    } catch {
        // Stockage indisponible : le choix vaut pour la page en cours.
    }
    syncToggles();
    document.dispatchEvent(new CustomEvent('motionchange', {detail: {paused}}));
}

for (const toggle of document.querySelectorAll('[data-motion-toggle]')) {
    toggle.addEventListener('click', () => setPaused(!isPaused()));
}
syncToggles();

// Apparitions [data-reveal] déclenchées à l'entrée dans l'écran (scss/components/_reveal.scss) : l'animation se joue en entier,
// quelle que soit la vitesse de défilement. Ce qui est déjà à l'écran au chargement, ou en attente quand on met en pause,
// perd son attribut et reste simplement visible.
function setupReveal() {
    if (reducedMotion.matches || isPaused() || !('IntersectionObserver' in window)) {
        return;
    }
    const pending = new Set(document.querySelectorAll('[data-reveal]'));
    const settle = (item) => {
        item.removeAttribute('data-reveal');
        pending.delete(item);
    };
    // Les familles qui déclarent --reveal-sequence (cartes, champs) passent une par une : chaque élément attend
    // la fin de l'intervalle du précédent de sa famille, même s'ils entrent dans l'écran ensemble.
    const queues = new Map();
    const reveal = (item) => {
        const step = parseFloat(getComputedStyle(item).getPropertyValue('--reveal-sequence'));
        if (step > 0) {
            const now = performance.now();
            const start = Math.max(now, (queues.get(item.dataset.reveal) ?? -Infinity) + step);
            queues.set(item.dataset.reveal, start);
            item.style.setProperty('--reveal-wait', `${Math.round(start - now)}ms`);
        }
        item.classList.add('is-revealed');
        pending.delete(item);
        observer.unobserve(item);
    };
    const observer = new IntersectionObserver((entries) => {
        entries
            .filter((entry) => entry.isIntersecting)
            .sort((a, b) => a.boundingClientRect.top - b.boundingClientRect.top || a.boundingClientRect.left - b.boundingClientRect.left)
            .forEach((entry) => reveal(entry.target));
    }, {rootMargin: '0px 0px -15% 0px'});

    for (const item of [...pending]) {
        if (item.getBoundingClientRect().top < window.innerHeight) {
            settle(item);
        } else {
            observer.observe(item);
        }
    }
    root.classList.add('reveal-io');

    // Un bloc qui reçoit le focus avant la fin de son apparition s'affiche aussitôt : l'élément focalisé reste visible (RGAA 10.7),
    // y compris dans le bas de l'écran, que l'observateur ignore.
    document.addEventListener('focusin', ({target}) => {
        for (let item = target.closest('[data-reveal]'); item; item = item.parentElement?.closest('[data-reveal]')) {
            observer.unobserve(item);
            settle(item);
        }
    });

    document.addEventListener('motionchange', ({detail}) => {
        if (detail.paused) {
            observer.disconnect();
            pending.forEach(settle);
        }
    });
}

// Les lueurs du fond glissent doucement à l'opposé du pointeur (souris uniquement).
function setupBackdropParallax() {
    const layer = document.querySelector('[data-backdrop-parallax]');
    if (!layer) {
        return;
    }
    const strength = parseFloat(getComputedStyle(root).getPropertyValue('--glow-parallax')) || 0;
    let targetX = 0;
    let targetY = 0;
    let x = 0;
    let y = 0;
    let frame = 0;

    const tick = () => {
        x += (targetX - x) * PARALLAX_EASING;
        y += (targetY - y) * PARALLAX_EASING;
        layer.style.transform = `translate3d(${x.toFixed(2)}px, ${y.toFixed(2)}px, 0)`;
        frame = Math.abs(targetX - x) > 0.1 || Math.abs(targetY - y) > 0.1 ? requestAnimationFrame(tick) : 0;
    };

    window.addEventListener('pointermove', (event) => {
        if (event.pointerType !== 'mouse' || !finePointer.matches || reducedMotion.matches || isPaused()) {
            return;
        }
        targetX = (0.5 - event.clientX / window.innerWidth) * strength * window.innerWidth / 100;
        targetY = (0.5 - event.clientY / window.innerHeight) * strength * window.innerHeight / 100;
        if (!frame) {
            frame = requestAnimationFrame(tick);
        }
    }, {passive: true});
}

setupReveal();
setupBackdropParallax();
