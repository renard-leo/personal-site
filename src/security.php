<?php

declare(strict_types=1);

const PERMISSIONS_POLICY = [
    'accelerometer', 'autoplay', 'browsing-topics', 'camera', 'display-capture', 'encrypted-media', 'fullscreen', 'geolocation',
    'gyroscope', 'hid', 'magnetometer', 'microphone', 'midi', 'payment', 'picture-in-picture', 'publickey-credentials-get',
    'screen-wake-lock', 'serial', 'usb', 'xr-spatial-tracking',
];

// Réponses sans page HTML (contact.php, sitemap.xml, robots.txt) : aucune ressource à charger.
const ENDPOINT_CSP = "default-src 'none'; base-uri 'none'; form-action 'none'; frame-ancestors 'none'";

function app_secret(): string
{
    return (string) config('appSecret', '');
}

/** HMAC-SHA256 préfixé par l'usage : une signature produite pour un usage n'est jamais valable pour un autre. */
function sign(string $purpose, string $data): string
{
    return hash_hmac('sha256', $purpose . '|' . $data, app_secret());
}

/** Script bloquant du <head> (thème appliqué avant le premier rendu) : la page et l'empreinte CSP lisent le même texte. */
function theme_script(): string
{
    static $script = null;

    return $script ??= trim((string) file_get_contents(ROOT . '/templates/inline/theme.js'));
}

function csp_hash(string $script): string
{
    return "'sha256-" . base64_encode(hash('sha256', $script, true)) . "'";
}

/** Seuls les services tiers configurés (GA4, reCAPTCHA) sont autorisés, selon les listes publiées par Google. */
function content_security_policy(): string
{
    $policy = [
        'default-src' => ["'self'"],
        'script-src' => ["'self'", csp_hash(theme_script())],
        'style-src' => ["'self'"],
        // Attributs style="" : variables de l'animation du code (hero) et badge reCAPTCHA. Les balises <style> restent interdites.
        'style-src-attr' => ["'unsafe-inline'"],
        'img-src' => ["'self'"],
        'connect-src' => ["'self'"],
        'frame-src' => [],
        'object-src' => [],
        'base-uri' => [],
        'form-action' => ["'self'"],
        'frame-ancestors' => [],
    ];

    if (config('googleAnalytics.measurementId')) {
        array_push($policy['script-src'], 'https://www.googletagmanager.com');
        array_push($policy['img-src'], 'https://www.googletagmanager.com', 'https://*.google-analytics.com');
        array_push($policy['connect-src'], 'https://www.googletagmanager.com', 'https://*.google-analytics.com', 'https://*.google.com');
    }
    if (config('recaptcha.siteKey')) {
        array_push($policy['script-src'], 'https://www.google.com/recaptcha/', 'https://www.gstatic.com/recaptcha/');
        array_push($policy['connect-src'], 'https://www.google.com/recaptcha/');
        array_push($policy['frame-src'], 'https://www.google.com/recaptcha/', 'https://recaptcha.google.com/recaptcha/');
    }

    $directives = [];
    foreach ($policy as $name => $sources) {
        $directives[] = $name . ' ' . implode(' ', array_unique($sources) ?: ["'none'"]);
    }
    if (is_https()) {
        $directives[] = 'upgrade-insecure-requests';
    }

    return implode('; ', $directives);
}

function send_security_headers(?string $policy = null): void
{
    if (headers_sent()) {
        return;
    }

    header_remove('X-Powered-By');
    header('Content-Security-Policy: ' . ($policy ?? content_security_policy()));
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: DENY');
    header('Referrer-Policy: strict-origin-when-cross-origin');
    header('Permissions-Policy: ' . implode(', ', array_map(static fn (string $feature): string => $feature . '=()', PERMISSIONS_POLICY)));
    if (is_https()) {
        header('Strict-Transport-Security: max-age=31536000');
    }
}
