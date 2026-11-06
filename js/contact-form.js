import { grantConsent, hasConsent, purposeConfig } from './consent.js';

const RECAPTCHA_TIMEOUT = 5000;
const RECAPTCHA_LOAD_TIMEOUT = 10000;

const MESSAGES = {
    required: {
        name: 'Indiquez votre nom.',
        email: 'Indiquez votre adresse e-mail.',
        subject: 'Indiquez l’objet de votre message.',
        message: 'Écrivez votre message.',
    },
    email: 'Saisissez une adresse e-mail valide, par exemple nom@entreprise.fr.',
    tooLong: (max) => `Ce champ est limité à ${max} caractères.`,
    invalid: 'Certains champs sont à corriger.',
    invalidCount: (count) => (count > 1 ? `${count} champs sont à corriger.` : 'Un champ est à corriger.'),
    failure: 'Le message n’a pas pu être envoyé. Vérifiez votre connexion puis réessayez, ou écrivez-moi sur LinkedIn.',
    success: 'Merci, votre message a bien été envoyé. Je vous réponds dès que possible.',
    antispamRequired: 'Pour envoyer votre message, autorisez d’abord la protection anti-spam Google reCAPTCHA.',
    antispamGranted: 'Protection anti-spam autorisée : vous pouvez envoyer votre message.',
    antispamUnavailable: 'Votre message n’a pas été envoyé : la protection anti-spam Google reCAPTCHA n’a pas pu se charger. Désactivez le blocage pour ce site puis rechargez la page, ou écrivez-moi sur LinkedIn.',
};

const ICONS = {success: 'check', error: 'alert'};

const siteKey = purposeConfig('antispam')?.siteKey ?? '';
let recaptcha = null;

function withTimeout(promise, delay) {
    return Promise.race([promise, new Promise((resolve, reject) => {
        setTimeout(() => reject(new Error('Délai dépassé')), delay);
    })]);
}

/**
 * Google reCAPTCHA v3 n'est chargé qu'avec le consentement « antispam », une seule fois.
 * Le badge n'est masqué (CSS) que pendant que la mention obligatoire est affichée dans le formulaire.
 * La promesse est rejetée si le script est bloqué (bloqueur de publicités, proxy) ou ne répond pas.
 */
function loadRecaptcha() {
    if (!siteKey || !hasConsent('antispam')) {
        return null;
    }
    recaptcha ??= withTimeout(new Promise((resolve, reject) => {
        const script = document.createElement('script');
        script.src = `https://www.google.com/recaptcha/api.js?render=${encodeURIComponent(siteKey)}`;
        script.async = true;
        script.addEventListener('load', () => {
            // Un proxy peut répondre autre chose que le script attendu.
            if (typeof window.grecaptcha?.ready !== 'function') {
                reject(new Error('reCAPTCHA indisponible'));
                return;
            }
            window.grecaptcha.ready(() => resolve(window.grecaptcha));
        });
        script.addEventListener('error', () => reject(new Error('reCAPTCHA indisponible')));
        document.head.append(script);
        setRecaptchaNotice(true);
    }), RECAPTCHA_LOAD_TIMEOUT).catch((error) => {
        recaptcha = null;
        setRecaptchaNotice(false);
        throw error;
    });
    return recaptcha;
}

function setRecaptchaNotice(visible) {
    const notice = document.querySelector('[data-recaptcha-notice]');
    if (notice) {
        notice.hidden = !visible;
        document.documentElement.classList.toggle('has-recaptcha-notice', visible);
    }
}

/** Jeton reCAPTCHA demandé juste avant l'envoi ; vide sans consentement, rejeté si Google ne répond pas. */
async function antispamToken() {
    const loading = loadRecaptcha();
    if (!loading) {
        return '';
    }
    const grecaptcha = await loading;
    return withTimeout(grecaptcha.execute(siteKey, {action: 'contact'}), RECAPTCHA_TIMEOUT);
}

function init(form) {
    const status = form.querySelector('[data-form-status]');
    const submit = form.querySelector('[type="submit"]');
    const submitLabel = submit.querySelector('.form-submit-label');
    const idleLabel = submitLabel.textContent;
    const inputs = [...form.querySelectorAll('.field-input')];
    const hints = new Map(inputs.map((input) => [input, input.getAttribute('aria-describedby') ?? '']));

    // Présente seulement si reCAPTCHA est obligatoire (deux clés configurées) : l'envoi attend le consentement « antispam ».
    const gate = form.querySelector('[data-antispam-gate]');

    form.noValidate = true;

    /**
     * État de la notice reCAPTCHA : '' (envoi possible), 'consent' (accord manquant) ou 'error' (script bloqué).
     * La notice précède le bouton dans l'ordre de lecture et le décrit ; ses changements sont annoncés par la zone de statut.
     */
    function setGate(state, announce = false) {
        if (!gate) {
            return;
        }
        const changed = gate.dataset.state !== state;
        gate.dataset.state = state;
        gate.hidden = state === '';
        gate.classList.toggle('is-error', state === 'error');
        let message = null;
        for (const text of gate.querySelectorAll('[data-antispam-message]')) {
            text.hidden = text.dataset.antispamMessage !== state;
            message = text.hidden ? message : text;
        }
        gate.querySelector('[data-antispam-actions]').hidden = state !== 'consent';
        // disabled natif : ni clic ni touche Entrée ne peuvent soumettre le formulaire.
        const hadFocus = document.activeElement === submit;
        submit.disabled = state !== '';
        if (hadFocus && submit.disabled) {
            // Un bouton désactivé perd le focus : il passe à la notice qui explique pourquoi.
            gate.focus({preventScroll: true});
        }
        if (message) {
            submit.setAttribute('aria-describedby', message.id);
        } else {
            submit.removeAttribute('aria-describedby');
        }
        if (announce && changed && message) {
            clearStatus(message.innerText.trim());
        }
    }

    function syncGate(announce = false) {
        setGate(hasConsent('antispam') ? '' : 'consent', announce);
    }

    const startRecaptcha = () => loadRecaptcha()?.catch(() => setGate('error', true));

    syncGate();

    // reCAPTCHA à la demande : première interaction avec le formulaire, ou dès que le consentement est donné.
    form.addEventListener('focusin', startRecaptcha, {once: true});
    document.addEventListener('consent:change', (event) => {
        syncGate(true);
        if (event.detail.antispam) {
            startRecaptcha();
        }
    });

    gate?.querySelector('[data-antispam-accept]').addEventListener('click', () => {
        grantConsent('antispam');
        if (hasConsent('antispam')) {
            clearStatus(MESSAGES.antispamGranted);
            // Le bouton cliqué disparaît avec la notice : le focus passe au bouton d'envoi, désormais actif.
            submit.focus();
        }
    });

    function setError(input, message) {
        const error = document.getElementById(`${input.id}-error`);
        error.querySelector('span').textContent = message;
        error.hidden = !message;
        // L'erreur est lue en premier, puis le format attendu et la mention « obligatoire » déjà associés au champ.
        input.setAttribute('aria-describedby', [message ? error.id : '', hints.get(input)].filter(Boolean).join(' '));
        if (message) {
            input.setAttribute('aria-invalid', 'true');
        } else {
            input.removeAttribute('aria-invalid');
        }
    }

    function validate(input) {
        const value = input.value.trim();
        let message = '';
        if (!value) {
            message = MESSAGES.required[input.name] ?? MESSAGES.invalid;
        } else if (input.validity.typeMismatch) {
            message = MESSAGES.email;
        } else if (input.maxLength > 0 && value.length > input.maxLength) {
            message = MESSAGES.tooLong(input.maxLength);
        }
        setError(input, message);
        return !message;
    }

    function showStatus(type, message) {
        status.className = `form-status is-${type}`;
        status.querySelector('.form-status-text').textContent = message;
        status.querySelector('svg')?.remove();
        status.insertAdjacentHTML('afterbegin', `<svg class="icon" aria-hidden="true" focusable="false"><use href="#i-${ICONS[type]}"></use></svg>`);
        status.scrollIntoView({block: 'nearest'});
    }

    /** Message masqué à l'écran mais annoncé par les lecteurs d'écran (zone role="status"). */
    function clearStatus(announcement = '') {
        status.className = 'form-status';
        status.querySelector('.form-status-text').textContent = announcement;
        status.querySelector('svg')?.remove();
    }

    // Pas d'aria-busy sur le formulaire : il pourrait retarder ou supprimer l'annonce du résultat, contenu dans la zone de statut.
    function setBusy(busy) {
        submit.classList.toggle('is-busy', busy);
        submitLabel.textContent = busy ? submit.dataset.labelBusy : idleLabel;
        if (busy) {
            submit.setAttribute('aria-disabled', 'true');
            clearStatus(submit.dataset.labelBusy);
        } else {
            submit.removeAttribute('aria-disabled');
        }
    }

    for (const input of inputs) {
        input.addEventListener('input', () => {
            if (input.getAttribute('aria-invalid') === 'true') {
                validate(input);
            }
        });
        input.addEventListener('blur', () => {
            if (input.type === 'email' && input.value.trim()) {
                validate(input);
            }
        });
    }

    form.addEventListener('submit', async (event) => {
        event.preventDefault();
        if (submit.classList.contains('is-busy')) {
            return;
        }
        if (submit.disabled) {
            showStatus('error', MESSAGES.antispamRequired);
            return;
        }
        clearStatus();

        const invalid = inputs.filter((input) => !validate(input));
        if (invalid.length > 0) {
            showStatus('error', MESSAGES.invalidCount(invalid.length));
            invalid[0].focus();
            return;
        }

        setBusy(true);
        try {
            try {
                form.elements.recaptcha_token.value = await antispamToken();
            } catch {
                form.elements.recaptcha_token.value = '';
                // Obligatoire, reCAPTCHA manquant ferait refuser l'envoi : on prévient plutôt que d'envoyer pour rien.
                if (gate) {
                    setGate('error');
                    showStatus('error', MESSAGES.antispamUnavailable);
                    return;
                }
            }
            const response = await fetch(form.action, {method: 'POST', body: new FormData(form), headers: {Accept: 'application/json'}, credentials: 'same-origin'});
            const data = await response.json().catch(() => null);
            if (!data || typeof data.ok !== 'boolean') {
                throw new Error('Réponse inattendue');
            }
            if (typeof data.csrfToken === 'string') {
                form.elements.csrf_token.value = data.csrfToken;
            }
            if (data.ok) {
                form.reset();
                showStatus('success', data.message || MESSAGES.success);
                return;
            }
            const fieldErrors = Object.entries(data.errors ?? {}).filter(([name]) => inputs.some((input) => input.name === name));
            for (const [name, message] of fieldErrors) {
                setError(form.elements[name], String(message));
            }
            showStatus('error', data.message || MESSAGES.invalid);
            if (fieldErrors.length > 0) {
                form.elements[fieldErrors[0][0]].focus();
            }
        } catch {
            showStatus('error', MESSAGES.failure);
        } finally {
            setBusy(false);
        }
    });
}

const form = document.querySelector('[data-contact-form]');
if (form) {
    init(form);
}

// Après un envoi sans JavaScript (?contact=sent|error), on retire le paramètre pour qu'un rechargement ne réaffiche pas le message.
const url = new URL(window.location.href);
if (url.searchParams.has('contact')) {
    url.searchParams.delete('contact');
    window.history.replaceState(null, '', url);
}
