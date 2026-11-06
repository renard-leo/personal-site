/**
 * Consentement aux traceurs (CNIL) avec CookieConsent v3 : finalités « analytics » (Google Analytics) et « antispam »
 * (Google reCAPTCHA), proposées seulement si leur clé est configurée. Rien n'est chargé avant l'accord.
 *
 * API : hasConsent(purpose), getConsent(), purposeConfig(purpose), grantConsent(purpose)
 * Événement : document « consent:change », detail = {analytics: bool, antispam: bool} (finalités configurées).
 */
import * as CookieConsent from '../vendor/cookieconsent/cookieconsent.esm.js';

// À incrémenter pour redemander leur choix à tous les visiteurs (nouveau service, finalité modifiée).
const REVISION = 1;
const VALIDITY_DAYS = 182;
// 13 mois au plus, sans prolongation à chaque visite (recommandation CNIL, annoncée dans les mentions légales).
const ANALYTICS_COOKIE_SECONDS = 13 * 30 * 24 * 60 * 60;

const siteConfig = readConfig();
const config = siteConfig.consent ?? {};

const PURPOSES = {
    analytics: {
        use: 'Google Analytics pour mesurer son audience',
        title: 'Mesure d’audience',
        description: 'Google Analytics mesure la fréquentation du site. Cookies _ga et _ga_*, conservés 13\u00A0mois au plus et supprimés si vous retirez votre accord.',
        autoClear: {cookies: [{name: /^_ga(_|$)/}]},
    },
    antispam: {
        use: 'Google reCAPTCHA pour protéger le formulaire de contact contre le spam',
        title: 'Protection anti-spam',
        description: config.antispam?.required
            ? 'Google reCAPTCHA protège le formulaire de contact contre les envois automatisés. Il est nécessaire pour envoyer un message ; sans votre accord, vous pouvez m’écrire sur LinkedIn.'
            : 'Google reCAPTCHA protège le formulaire de contact contre les envois automatisés. Sans votre accord, le formulaire reste utilisable.',
    },
};

const purposes = Object.keys(PURPOSES).filter((purpose) => purpose in config);
const legalUrl = escapeAttribute(siteConfig.legalUrl ?? 'mentions-legales');
let analyticsLoaded = false;

function readConfig() {
    try {
        return JSON.parse(document.getElementById('site-config')?.textContent ?? '{}');
    } catch {
        return {};
    }
}

function escapeAttribute(value) {
    return String(value).replace(/[&"<>]/g, (character) => `&#${character.charCodeAt(0)};`);
}

export function hasConsent(purpose) {
    return purposes.includes(purpose) && CookieConsent.acceptedCategory(purpose);
}

export function getConsent() {
    return Object.fromEntries(purposes.map((purpose) => [purpose, hasConsent(purpose)]));
}

/** Configuration publique d'une finalité (par exemple {siteKey, required} pour « antispam »), ou null si elle n'est pas configurée. */
export function purposeConfig(purpose) {
    return config[purpose] ?? null;
}

/** Accepte une seule finalité en conservant les autres choix (bouton « Autoriser reCAPTCHA » du formulaire). */
export function grantConsent(purpose) {
    if (purposes.includes(purpose) && !hasConsent(purpose)) {
        CookieConsent.acceptCategory([...CookieConsent.getUserPreferences().acceptedCategories, purpose]);
        // Le choix est enregistré : le bandeau encore ouvert n'a plus lieu d'être (les autres finalités restent modifiables).
        CookieConsent.hide();
    }
}

function notify() {
    document.dispatchEvent(new CustomEvent('consent:change', {detail: getConsent()}));
}

function loadAnalytics() {
    const id = config.analytics?.measurementId;
    if (!id) {
        return;
    }
    window[`ga-disable-${id}`] = false;
    if (analyticsLoaded) {
        return;
    }
    analyticsLoaded = true;
    window.dataLayer = window.dataLayer || [];
    window.gtag = function gtag() {
        window.dataLayer.push(arguments);
    };
    window.gtag('consent', 'default', {analytics_storage: 'granted', ad_storage: 'denied', ad_user_data: 'denied', ad_personalization: 'denied'});
    window.gtag('js', new Date());
    window.gtag('config', id, {cookie_expires: ANALYTICS_COOKIE_SECONDS, cookie_update: false});

    const script = document.createElement('script');
    script.async = true;
    script.src = `https://www.googletagmanager.com/gtag/js?id=${encodeURIComponent(id)}`;
    document.head.append(script);
}

function onChange({changedCategories}) {
    if (changedCategories.includes('analytics') && !hasConsent('analytics')) {
        // Les cookies _ga et _ga_* sont effacés par CookieConsent (autoClear) ; le script déjà chargé n'envoie plus rien.
        window[`ga-disable-${config.analytics.measurementId}`] = true;
    }
    // Un script reCAPTCHA déjà chargé continuerait d'observer la page : seul un rechargement l'arrête.
    if (changedCategories.includes('antispam') && !hasConsent('antispam') && window.grecaptcha) {
        window.location.reload();
        return;
    }
    if (hasConsent('analytics')) {
        loadAnalytics();
    }
    notify();
}

// Bandeau non bloquant : la page réserve sa hauteur pour que l'élément qui reçoit le focus ne soit jamais caché dessous
// (CSS : scroll-padding-bottom et padding-bottom du body).
const root = document.documentElement;
const bannerResize = typeof ResizeObserver === 'function' ? new ResizeObserver(reserveBannerSpace) : null;
let banner = null;

function reserveBannerSpace() {
    if (banner && root.classList.contains('show--consent')) {
        root.style.setProperty('--consent-offset', `${Math.ceil(banner.offsetHeight + parseFloat(getComputedStyle(banner).bottom))}px`);
    } else {
        root.style.removeProperty('--consent-offset');
    }
}

function onModalReady({modalName, modal}) {
    if (modalName !== 'consentModal') {
        return;
    }
    // Bandeau non modal : Tab doit pouvoir en sortir, alors que CookieConsent y retient le focus comme dans une fenêtre.
    modal.addEventListener('keydown', (event) => {
        if (event.key === 'Tab') {
            event.stopPropagation();
        }
    });
}

function onModalShow({modalName}) {
    if (modalName !== 'consentModal') {
        return;
    }
    banner = document.querySelector('#cc-main .cm');
    bannerResize?.observe(banner);
    window.addEventListener('resize', reserveBannerSpace);
    reserveBannerSpace();
}

function onModalHide({modalName}) {
    if (modalName !== 'consentModal') {
        return;
    }
    bannerResize?.unobserve(banner);
    window.removeEventListener('resize', reserveBannerSpace);
    reserveBannerSpace();
    // Le focus resté dans le bandeau masqué revient au début du contenu.
    const focused = document.activeElement;
    if (!focused || focused === document.body || focused.closest('#cc-main')) {
        document.getElementById('contenu')?.focus({preventScroll: true});
    }
}

function translation() {
    const uses = purposes.map((purpose) => PURPOSES[purpose].use).join(' et ');
    return {
        consentModal: {
            title: 'Cookies et confidentialité',
            description: `{{revisionMessage}}Avec votre accord, ce site utilise ${uses}. Rien n’est chargé avant votre choix, modifiable à tout moment via «\u202FGestion des cookies\u202F» en bas de page. Détails dans les <a href="${legalUrl}#cookies">mentions légales</a>.`,
            revisionMessage: 'Les services utilisés par ce site ont changé depuis votre dernier choix. ',
            acceptAllBtn: 'Tout accepter',
            acceptNecessaryBtn: 'Tout refuser',
            showPreferencesBtn: 'Personnaliser',
        },
        preferencesModal: {
            title: 'Gestion des cookies',
            ...(purposes.length > 0 ? {acceptAllBtn: 'Tout accepter', acceptNecessaryBtn: 'Tout refuser', savePreferencesBtn: 'Enregistrer mes choix'} : {savePreferencesBtn: 'J’ai compris'}),
            closeIconLabel: 'Fermer',
            sections: [
                {description: purposes.length > 0
                    ? 'Choisissez les services que vous acceptez. Rien n’est chargé sans votre accord et votre choix est conservé 6\u00A0mois.'
                    : 'Ce site n’utilise actuellement aucun cookie ni service soumis à votre accord.'},
                {title: 'Strictement nécessaire', description: 'Le cookie cc_cookie enregistre vos choix pendant 6\u00A0mois. Indispensable, il est toujours actif.', linkedCategory: 'necessary'},
                ...purposes.map((purpose) => ({title: PURPOSES[purpose].title, description: PURPOSES[purpose].description, linkedCategory: purpose})),
                {title: 'En savoir plus', description: `${purposes.length > 0 ? 'Les données traitées par Google et vos droits' : 'Vos données et vos droits'} sont détaillés dans les <a href="${legalUrl}#donnees">mentions légales</a>.`},
            ],
        },
    };
}

// Toujours initialisé pour que « Gestion des cookies » rouvre la fenêtre ; le bandeau ne s'ouvre seul que si un service est soumis à accord.
CookieConsent.run({
    revision: REVISION,
    autoShow: purposes.length > 0,
    manageScriptTags: false,
    cookie: {name: 'cc_cookie', expiresAfterDays: VALIDITY_DAYS, sameSite: 'Lax'},
    guiOptions: {
        consentModal: {layout: 'box wide', position: 'bottom left', equalWeightButtons: true, flipButtons: false},
        preferencesModal: {layout: 'box', equalWeightButtons: true, flipButtons: false},
    },
    categories: {
        necessary: {enabled: true, readOnly: true},
        ...Object.fromEntries(purposes.map((purpose) => [purpose, PURPOSES[purpose].autoClear ? {autoClear: PURPOSES[purpose].autoClear} : {}])),
    },
    language: {default: 'fr', translations: {fr: translation()}},
    onFirstConsent: notify,
    onConsent: () => {
        if (hasConsent('analytics')) {
            loadAnalytics();
        }
    },
    onChange,
    onModalReady,
    onModalShow,
    onModalHide,
});
