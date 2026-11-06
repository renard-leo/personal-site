<?php

declare(strict_types=1);

const RECAPTCHA_VERIFY_URL = 'https://www.google.com/recaptcha/api/siteverify';
const RECAPTCHA_ACTION = 'contact';

/** Les deux clés configurées rendent reCAPTCHA obligatoire : sans consentement ni jeton, le formulaire ne s'envoie pas. */
function recaptcha_required(): bool
{
    return config('recaptcha.siteKey', '') !== '' && config('recaptcha.secretKey', '') !== '';
}

/**
 * Vérifie un jeton reCAPTCHA v3 auprès de Google. Faux seulement si l'envoi est jugé suspect :
 * en cas de panne de Google ou d'erreur de configuration, le visiteur n'est pas bloqué (erreur journalisée).
 */
function recaptcha_passes(string $token): bool
{
    if (strlen($token) > 10000) {
        return false;
    }
    if (!function_exists('curl_init')) {
        error_log('reCAPTCHA : extension curl absente, envoi accepté sans vérification.');

        return true;
    }

    $curl = curl_init(RECAPTCHA_VERIFY_URL);
    curl_setopt_array($curl, [
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => http_build_query(['secret' => (string) config('recaptcha.secretKey'), 'response' => $token]),
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CONNECTTIMEOUT => 2,
        CURLOPT_TIMEOUT => 4,
    ]);
    $body = curl_exec($curl);
    $status = (int) curl_getinfo($curl, CURLINFO_RESPONSE_CODE);
    $result = is_string($body) && $status === 200 ? json_decode($body, true) : null;

    if (!is_array($result) || !is_bool($result['success'] ?? null)) {
        error_log(sprintf('reCAPTCHA : service indisponible (HTTP %d, %s), envoi accepté sans vérification.', $status, curl_error($curl) ?: 'réponse illisible'));

        return true;
    }

    $codes = array_map('strval', (array) ($result['error-codes'] ?? []));
    if (!$result['success'] && array_intersect($codes, ['missing-input-secret', 'invalid-input-secret', 'bad-request']) !== []) {
        error_log('reCAPTCHA : erreur de configuration (' . implode(', ', $codes) . '), envoi accepté sans vérification.');

        return true;
    }

    $score = (float) ($result['score'] ?? 0);
    $action = (string) ($result['action'] ?? '');
    $hostname = strtolower((string) ($result['hostname'] ?? ''));
    if ($result['success'] && $action === RECAPTCHA_ACTION && $score >= (float) config('recaptcha.minScore', 0.5) && $hostname === request_hostname()) {
        return true;
    }

    error_log(sprintf('reCAPTCHA : envoi refusé (score %.1f, action « %s », hôte « %s », erreurs : %s).', $score, $action, $hostname, implode(', ', $codes) ?: 'aucune'));

    return false;
}
