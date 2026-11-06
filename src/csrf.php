<?php

declare(strict_types=1);

// Jeton sans cookie ni session : horodatage + aléa, signés avec appSecret. Il prouve que le formulaire
// a été servi par ce site, ni trop tôt (robots) ni trop tard ; l'en-tête Origin bloque les envois croisés.
const CSRF_MIN_AGE = 3;
const CSRF_MAX_AGE = 7200;

/** Chaîne vide si appSecret n'est pas configuré : l'envoi sera alors refusé proprement. */
function csrf_token(): string
{
    if (app_secret() === '') {
        return '';
    }
    $payload = time() . '.' . bin2hex(random_bytes(8));

    return $payload . '.' . sign('csrf', $payload);
}

/** Renvoie null si le jeton est valable, sinon la raison : missing, invalid, early ou expired. */
function csrf_error(mixed $token): ?string
{
    if (!is_string($token) || $token === '') {
        return 'missing';
    }
    if (!preg_match('/^(\d{1,12})\.([0-9a-f]{16})\.([0-9a-f]{64})$/', $token, $match)
        || !hash_equals(sign('csrf', $match[1] . '.' . $match[2]), $match[3])) {
        return 'invalid';
    }

    $age = time() - (int) $match[1];

    return match (true) {
        $age < CSRF_MIN_AGE => 'early',
        $age > CSRF_MAX_AGE => 'expired',
        default => null,
    };
}

/** Vrai si l'en-tête Origin (ou, à défaut, Referer) désigne l'hôte courant. */
function is_same_origin_request(): bool
{
    $origin = (string) ($_SERVER['HTTP_ORIGIN'] ?? '');
    // « null » : origine masquée par le navigateur (politique de référent stricte) ; on se rabat sur Referer.
    $source = $origin !== '' && $origin !== 'null' ? $origin : (string) ($_SERVER['HTTP_REFERER'] ?? '');
    $parts = parse_url($source);
    if (!is_array($parts) || !isset($parts['scheme'], $parts['host']) || !in_array(strtolower($parts['scheme']), ['http', 'https'], true)) {
        return false;
    }
    $host = strtolower($parts['host']) . (isset($parts['port']) ? ':' . $parts['port'] : '');

    return request_host() !== '' && request_host() === $host;
}
