<?php

declare(strict_types=1);

require __DIR__ . '/../src/bootstrap.php';
require ROOT . '/src/mailer.php';
require ROOT . '/src/contact.php';

header('Content-Security-Policy: ' . ENDPOINT_CSP);
header('Cache-Control: no-store');

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    header('Allow: POST');
    if (wants_json()) {
        send_json(405, ['ok' => false, 'message' => typo(CONTACT_MESSAGES['method']), 'errors' => (object) []]);
    }
    http_response_code(405);
    header('Content-Type: text/plain; charset=utf-8');
    echo CONTACT_MESSAGES['method'];
    exit;
}

if (!is_same_origin_request()) {
    contact_respond(403, false, CONTACT_MESSAGES['origin']);
}

$configError = contact_config_error();
if ($configError !== null) {
    error_log('Formulaire de contact indisponible : ' . $configError . '.');
    contact_respond(503, false, CONTACT_MESSAGES['unavailable']);
}

// Pot de miel rempli : on simule un succès pour ne rien apprendre au robot.
$trap = $_POST['website'] ?? '';
if (!is_string($trap) || trim($trap) !== '') {
    contact_respond(200, true, CONTACT_MESSAGES['success']);
}

$csrfError = csrf_error($_POST['csrf_token'] ?? null);
if ($csrfError === 'early') {
    contact_respond(403, false, CONTACT_MESSAGES['early']);
}
if ($csrfError !== null) {
    // Jeton neuf : le visiteur peut renvoyer son message sans recharger la page ni perdre sa saisie.
    contact_respond(403, false, CONTACT_MESSAGES['token'], [], ['csrfToken' => csrf_token()]);
}

// Les deux clés reCAPTCHA configurées le rendent obligatoire : sans jeton (accord refusé, JavaScript absent), pas d'envoi.
$recaptchaToken = is_string($_POST['recaptcha_token'] ?? null) ? $_POST['recaptcha_token'] : '';
if ($recaptchaToken === '' && recaptcha_required()) {
    contact_respond(403, false, CONTACT_MESSAGES['antispamRequired'], redirect: 'antispam');
}

[$values, $errors] = contact_validate($_POST);
if ($errors !== []) {
    contact_respond(422, false, CONTACT_MESSAGES['invalid'], $errors);
}

$wait = rate_limit_hit(client_ip() ?: 'inconnue');
if ($wait > 0) {
    header('Retry-After: ' . $wait);
    contact_respond(429, false, sprintf(CONTACT_MESSAGES['rateLimit'], RATE_LIMIT_MAX, plural((int) ceil($wait / 60), 'minute', 'minutes')));
}

// Google injoignable : recaptcha_passes() laisse passer, les autres protections restent actives.
if ($recaptchaToken !== '' && config('recaptcha.secretKey') && !recaptcha_passes($recaptchaToken)) {
    contact_respond(403, false, CONTACT_MESSAGES['spam']);
}

if (!contact_send($values)) {
    contact_respond(503, false, CONTACT_MESSAGES['unavailable']);
}

contact_respond(200, true, CONTACT_MESSAGES['success']);
