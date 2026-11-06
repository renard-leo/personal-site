// Hero « studio » : inspecteur au survol, au focus clavier et au toucher, visite automatique, nom qui se décode pendant l'intro.
// Toutes les surimpressions sont décoratives (aria-hidden) et ne bougent que par transform.
const hero = document.querySelector('[data-studio]');
const root = document.documentElement;
const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)');
// Glyphes de largeur proche, de même casse que la lettre finale : le mot brouillé garde sa silhouette.
const GLYPHS_UPPER = 'ABCDEFHKLNPRSTUVXYZ';
const GLYPHS_LOWER = 'abcdeghknopqsuvxyz0123456789';
const DECODE_LEAD = 80;
const DECODE_STEP = 55;
const SCRAMBLE_RATE = 45;
const NARROW = 640;

function duration(name) {
    const value = getComputedStyle(root).getPropertyValue(name).trim();
    return value.endsWith('ms') ? parseFloat(value) : parseFloat(value) * 1000 || 0;
}

function describe(element) {
    const name = element.tagName.toLowerCase();
    return element.classList.length > 0 ? `${name}.${element.classList[0]}` : name;
}

function pathOf(element) {
    const segments = [];
    for (let node = element; node && node !== document.body; node = node.parentElement) {
        segments.unshift(describe(node));
        if (node.tagName === 'MAIN') {
            break;
        }
    }
    return segments;
}

function isPaused() {
    return root.dataset.motion === 'paused';
}

if (hero) {
    const board = hero.querySelector('.studio-board');
    const name = hero.querySelector('.studio-name');
    const targets = [...hero.querySelectorAll('[data-inspect]')];
    const pick = (selector) => hero.querySelector(selector);
    const edges = Object.fromEntries([...hero.querySelectorAll('[data-edge]')].map((element) => [element.dataset.edge, element]));
    const handles = Object.fromEntries([...hero.querySelectorAll('[data-handle]')].map((element) => [element.dataset.handle, element]));
    const ui = {
        tag: pick('[data-studio-tag]'),
        size: pick('[data-studio-size]'),
        margin: pick('[data-studio-box="margin"]'),
        padding: pick('[data-studio-box="padding"]'),
        guideX: pick('[data-studio-guide="x"]'),
        guideY: pick('[data-studio-guide="y"]'),
        markX: pick('[data-studio-mark="x"]'),
        markY: pick('[data-studio-mark="y"]'),
        bandX: pick('[data-studio-band="x"]'),
        bandY: pick('[data-studio-band="y"]'),
        path: pick('[data-studio-path]'),
        dims: pick('[data-studio-dims]'),
        pointer: pick('[data-studio-pointer]'),
    };
    const tourInterval = duration('--studio-tour');
    const idleDelay = duration('--studio-idle');

    let geometry = null;
    const cache = new Map();
    let selected = null;
    let pointer = null;
    let frame = 0;
    let hovering = false;
    let focusInside = false;
    let visible = true;
    let introRunning = root.classList.contains('is-intro');
    let tourTimer = 0;
    let tourIndex = 0;

    // Mesures relatives au hero, mises en cache ; invalidées au redimensionnement et quand le hero a bougé (défilement).
    const frameGeometry = () => {
        if (!geometry) {
            const heroRect = hero.getBoundingClientRect();
            const boardRect = board.getBoundingClientRect();
            const style = getComputedStyle(board);
            geometry = {
                left: heroRect.left,
                top: heroRect.top,
                width: heroRect.width,
                height: heroRect.height,
                originX: boardRect.left - heroRect.left + parseFloat(style.paddingLeft),
                originY: boardRect.top - heroRect.top + parseFloat(style.paddingTop),
            };
        }
        return geometry;
    };

    const invalidate = () => {
        geometry = null;
        cache.clear();
    };

    const measure = (element) => {
        if (!cache.has(element)) {
            const {left, top} = frameGeometry();
            const rect = element.getBoundingClientRect();
            const style = getComputedStyle(element);
            const sides = (property) => ['Top', 'Right', 'Bottom', 'Left'].map((side) => Math.max(0, parseFloat(style[property + side]) || 0));
            cache.set(element, {x: rect.left - left, y: rect.top - top, w: rect.width, h: rect.height, margin: sides('margin'), padding: sides('padding')});
        }
        return cache.get(element);
    };

    const formatPath = (element) => {
        const segments = pathOf(element);
        const shown = frameGeometry().width < NARROW && segments.length > 3 ? ['…', ...segments.slice(-2)] : segments;
        return shown.join(' › ');
    };

    const size = (width, height) => `${Math.round(width)} × ${Math.round(height)}`;

    // Sans transition le temps d'une image : la sélection apparaît en place au lieu de glisser depuis l'origine.
    const placeInstantly = () => {
        hero.classList.add('is-instant');
        requestAnimationFrame(() => requestAnimationFrame(() => hero.classList.remove('is-instant')));
    };

    const move = (element, x, y, extra = '') => {
        element.style.transform = `translate(${x}px, ${y}px)${extra}`;
    };

    const select = (element) => {
        const {x, y, w, h, margin, padding} = measure(element);
        if (!selected) {
            placeInstantly();
        }
        selected = element;
        move(edges.top, x, y, ` scaleX(${w})`);
        move(edges.bottom, x, y + h - 1, ` scaleX(${w})`);
        move(edges.left, x, y, ` scaleY(${h})`);
        move(edges.right, x + w - 1, y, ` scaleY(${h})`);
        move(handles.nw, x, y);
        move(handles.ne, x + w, y);
        move(handles.se, x + w, y + h);
        move(handles.sw, x, y + h);
        move(ui.tag, x, y);
        move(ui.size, x + w / 2, y + h);
        ui.tag.textContent = describe(element);
        ui.size.textContent = size(w, h);

        const [mt, mr, mb, ml] = margin;
        const [pt, pr, pb, pl] = padding;
        Object.assign(ui.margin.style, {transform: `translate(${x - ml}px, ${y - mt}px)`, width: `${w + ml + mr}px`, height: `${h + mt + mb}px`, borderWidth: `${mt}px ${mr}px ${mb}px ${ml}px`});
        Object.assign(ui.padding.style, {transform: `translate(${x}px, ${y}px)`, width: `${w}px`, height: `${h}px`, borderWidth: `${pt}px ${pr}px ${pb}px ${pl}px`});
        hero.classList.toggle('has-box', [...margin, ...padding].some((value) => value > 0));

        ui.bandX.style.transform = `translateX(${x}px) scaleX(${w})`;
        ui.bandY.style.transform = `translateY(${y}px) scaleY(${h})`;
        ui.path.textContent = formatPath(element);
        ui.dims.textContent = size(w, h);
        hero.classList.add('is-selected');
    };

    const clear = () => {
        selected = null;
        hero.classList.remove('is-selected', 'has-box');
        const {width, height} = frameGeometry();
        ui.path.textContent = formatPath(hero);
        ui.dims.textContent = size(width, height);
    };

    // Visite automatique : lente, seulement si personne n'interagit, hero visible et animations autorisées.
    const canTour = () => visible && !document.hidden && !hovering && !focusInside && !introRunning && !isPaused() && !reducedMotion.matches;

    const stopTour = () => {
        clearTimeout(tourTimer);
        tourTimer = 0;
        hero.classList.remove('is-touring');
    };

    const stepTour = () => {
        if (!canTour()) {
            stopTour();
            return;
        }
        invalidate();
        const viewport = window.innerHeight;
        const {top} = frameGeometry();
        const candidates = targets.filter((target) => {
            const {y, h} = measure(target);
            return top + y + h > 0 && top + y < viewport;
        });
        if (candidates.length > 0) {
            hero.classList.add('is-touring');
            select(candidates[tourIndex % candidates.length]);
            tourIndex += 1;
        }
        tourTimer = setTimeout(stepTour, tourInterval);
    };

    const scheduleTour = (delay) => {
        stopTour();
        if (canTour()) {
            tourTimer = setTimeout(stepTour, delay);
        }
    };

    // Pointeur : lu dans requestAnimationFrame, une mesure du hero par image au plus.
    const update = () => {
        frame = 0;
        if (!pointer || introRunning) {
            return;
        }
        const heroRect = hero.getBoundingClientRect();
        if (geometry && (geometry.left !== heroRect.left || geometry.top !== heroRect.top)) {
            invalidate();
        }
        const {originX, originY} = frameGeometry();
        const x = pointer.x - heroRect.left;
        const y = pointer.y - heroRect.top;
        ui.guideX.style.transform = `translateX(${x}px)`;
        ui.guideY.style.transform = `translateY(${y}px)`;
        ui.markX.style.transform = `translateX(${x}px)`;
        ui.markY.style.transform = `translateY(${y}px)`;
        ui.pointer.textContent = `X ${Math.round(x - originX)}  Y ${Math.round(y - originY)}`;
        hero.classList.add('is-pointing');

        const target = pointer.target instanceof Element ? pointer.target.closest('[data-inspect]') : null;
        if (target && target !== selected) {
            select(target);
        } else if (!target && selected && !focusInside) {
            clear();
        }
    };

    hero.addEventListener('pointermove', (event) => {
        if (event.pointerType === 'touch') {
            return;
        }
        pointer = {x: event.clientX, y: event.clientY, target: event.target};
        if (!hovering) {
            hovering = true;
            stopTour();
        }
        if (!frame) {
            frame = requestAnimationFrame(update);
        }
    }, {passive: true});

    hero.addEventListener('pointerleave', (event) => {
        if (event.pointerType === 'touch') {
            return;
        }
        hovering = false;
        pointer = null;
        hero.classList.remove('is-pointing');
        ui.pointer.textContent = '';
        if (!focusInside && !introRunning) {
            clear();
        }
        scheduleTour(idleDelay);
    });

    // Au toucher : sélection de l'élément touché, la visite reprend plus tard.
    hero.addEventListener('pointerdown', (event) => {
        if (event.pointerType !== 'touch' || introRunning) {
            return;
        }
        const target = event.target instanceof Element ? event.target.closest('[data-inspect]') : null;
        stopTour();
        if (target) {
            select(target);
        } else {
            clear();
        }
        scheduleTour(2 * idleDelay);
    }, {passive: true});

    hero.addEventListener('focusin', (event) => {
        focusInside = true;
        stopTour();
        const target = event.target.closest('[data-inspect]');
        if (target && !introRunning) {
            select(target);
        }
    });

    hero.addEventListener('focusout', (event) => {
        if (hero.contains(event.relatedTarget)) {
            return;
        }
        focusInside = false;
        if (!hovering) {
            clear();
        }
        scheduleTour(idleDelay);
    });

    const resizeObserver = new ResizeObserver(() => {
        invalidate();
        if (selected) {
            placeInstantly();
            select(selected);
        } else if (!introRunning) {
            clear();
        }
    });
    resizeObserver.observe(hero);
    targets.forEach((target) => resizeObserver.observe(target));

    new IntersectionObserver(([entry]) => {
        visible = entry.isIntersecting;
        if (visible) {
            scheduleTour(idleDelay);
        } else {
            stopTour();
        }
    }, {threshold: 0.2}).observe(hero);

    document.addEventListener('visibilitychange', () => (document.hidden ? stopTour() : scheduleTour(idleDelay)));
    document.addEventListener('motionchange', () => (isPaused() ? stopTour() : scheduleTour(idleDelay)));

    // Intro : le nom se décode lettre par lettre, si le script arrive avant que le nom ne soit peint par le CSS.
    const decodeName = () => {
        const paint = name.getAnimations({subtree: true}).find((animation) => animation.animationName === 'studio-paint');
        if (!paint || paint.currentTime > duration('--intro-paint')) {
            return;
        }
        const lines = [...name.querySelectorAll('.studio-name-line')];
        const finals = lines.map((line) => [...line.textContent]);
        const start = performance.now();
        let last = -Infinity;
        name.classList.add('is-decoding');

        const tick = (now) => {
            if (now - last >= SCRAMBLE_RATE) {
                last = now;
                const elapsed = now - start;
                let index = 0;
                let done = true;
                lines.forEach((line, lineIndex) => {
                    line.dataset.glyphs = finals[lineIndex].map((character) => {
                        if (elapsed >= DECODE_LEAD + index++ * DECODE_STEP) {
                            return character;
                        }
                        done = false;
                        const glyphs = character === character.toLowerCase() ? GLYPHS_LOWER : GLYPHS_UPPER;
                        return glyphs[Math.floor(Math.random() * glyphs.length)];
                    }).join('');
                });
                if (done) {
                    name.classList.remove('is-decoding');
                    lines.forEach((line) => delete line.dataset.glyphs);
                    return;
                }
            }
            requestAnimationFrame(tick);
        };
        requestAnimationFrame(tick);
    };

    if (introRunning) {
        decodeName();
        setTimeout(() => {
            root.classList.remove('is-intro');
            introRunning = false;
            invalidate();
            clear();
            scheduleTour(idleDelay);
        }, duration('--dur-intro'));
    } else {
        clear();
        scheduleTour(idleDelay);
    }
}
