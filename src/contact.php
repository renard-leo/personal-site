<?php

declare(strict_types=1);

// Longueurs maximales, alignées sur les attributs maxlength de templates/sections/contact.php.
const CONTACT_FIELDS = ['name' => 100, 'email' => 254, 'subject' => 150, 'message' => 5000];

const CONTACT_MESSAGES = [
    'required' => [
        'name' => 'Indiquez votre nom.',
        'email' => 'Indiquez votre adresse e-mail.',
        'subject' => 'Indiquez l’objet de votre message.',
        'message' => 'Écrivez votre message.',
    ],
    'singleLine' => [
        'name' => 'Votre nom doit tenir sur une seule ligne.',
        'email' => 'Saisissez une adresse e-mail valide, par exemple nom@entreprise.fr.',
        'subject' => 'L’objet doit tenir sur une seule ligne.',
    ],
    'email' => 'Saisissez une adresse e-mail valide, par exemple nom@entreprise.fr.',
    'tooLong' => 'Ce champ est limité à %d caractères.',
    'invalid' => 'Certains champs sont à corriger.',
    'success' => 'Merci, votre message a bien été envoyé. Je vous réponds dès que possible.',
    'method' => 'Ce formulaire s’envoie uniquement par la méthode POST.',
    'origin' => 'Votre envoi n’a pas pu être vérifié : il doit partir du formulaire de ce site. Rechargez la page, puis réessayez.',
    'token' => 'Le formulaire a expiré ou n’a pas pu être vérifié. Cliquez de nouveau sur « Envoyer le message ».',
    'early' => 'Le formulaire a été envoyé trop vite après l’ouverture de la page. Patientez quelques secondes, puis cliquez de nouveau sur « Envoyer le message ».',
    'rateLimit' => 'Vous avez envoyé %d messages en moins d’une heure, c’est le maximum. Réessayez dans %s, ou écrivez-moi sur LinkedIn.',
    'spam' => 'Votre message a été bloqué par la protection anti-spam. Réessayez dans quelques instants, ou écrivez-moi sur LinkedIn.',
    'antispamRequired' => 'Votre message n’a pas été envoyé : autorisez d’abord la protection anti-spam Google reCAPTCHA, puis renvoyez-le. Vous pouvez aussi m’écrire sur LinkedIn.',
    'unavailable' => 'Le formulaire est momentanément indisponible. Réessayez plus tard, ou écrivez-moi sur LinkedIn.',
];

function wants_json(): bool
{
    return str_contains(strtolower((string) ($_SERVER['HTTP_ACCEPT'] ?? '')), 'application/json');
}

function send_json(int $status, array $payload): never
{
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
    exit;
}

/**
 * Réponse JSON pour l'envoi en JavaScript, sinon redirection vers le formulaire : ?contact=sent, ?contact=error
 * ou $redirect, l'un des messages de templates/sections/contact.php.
 */
function contact_respond(int $status, bool $ok, string $message, array $errors = [], array $extra = [], string $redirect = ''): never
{
    if (wants_json()) {
        send_json($status, ['ok' => $ok, 'message' => typo($message), 'errors' => (object) array_map('typo', $errors)] + $extra);
    }

    header('Location: ' . url('?contact=' . ($redirect ?: ($ok ? 'sent' : 'error')) . '#contact'), true, 303);
    exit;
}

/** Raison pour laquelle le formulaire ne peut pas fonctionner (à journaliser), ou null s'il est prêt. */
function contact_config_error(): ?string
{
    return app_secret() === '' ? 'appSecret n’est pas renseigné' : mail_config_error();
}

/**
 * Normalise (espaces en bordure, fins de ligne, caractères de contrôle) et valide les champs.
 *
 * @return array{0: array<string, string>, 1: array<string, string>} valeurs normalisées, erreurs par champ
 */
function contact_validate(array $input): array
{
    $values = [];
    $errors = [];
    foreach (CONTACT_FIELDS as $field => $max) {
        $value = $input[$field] ?? '';
        $value = is_string($value) && mb_check_encoding($value, 'UTF-8') ? $value : '';
        $multiline = $field === 'message';
        if ($multiline) {
            $value = str_replace(["\r\n", "\r"], "\n", $value);
        }
        $value = (string) preg_replace('/^[\s\p{Z}]+|[\s\p{Z}]+$/u', '', $value);

        // Un retour à la ligne dans un champ repris en en-tête d'e-mail permettrait d'injecter des en-têtes.
        $error = match (true) {
            $value === '' => CONTACT_MESSAGES['required'][$field],
            !$multiline && preg_match('/[\r\n]/', $value) === 1 => CONTACT_MESSAGES['singleLine'][$field],
            mb_strlen($value) > $max => sprintf(CONTACT_MESSAGES['tooLong'], $max),
            $field === 'email' && !is_email($value) => CONTACT_MESSAGES['email'],
            default => null,
        };
        if ($error !== null) {
            $errors[$field] = $error;
        }

        $values[$field] = (string) preg_replace($multiline ? '/[^\P{Cc}\n\t]/u' : '/\p{Cc}/u', '', $value);
    }

    return [$values, $errors];
}

/** Envoie le message par le transport configuré (voir src/mailer.php). Faux en cas d'échec (journalisé). */
function contact_send(array $values): bool
{
    $site = (string) (parse_url((string) config('siteUrl'), PHP_URL_HOST) ?: request_hostname());

    return send_mail([
        'to' => (string) config('mail.to', ''),
        'from' => (string) config('mail.from', ''),
        'fromName' => trim((string) config('mail.fromName', '')) ?: 'Formulaire de contact',
        // L'expéditeur reste l'adresse du site (SPF, compte SMTP) : le visiteur est joignable par « Répondre ».
        'replyTo' => $values['email'],
        'subject' => '[' . $site . '] ' . $values['subject'],
        'body' => contact_body($values, $site),
    ]);
}

function contact_body(array $values, string $site): string
{
    return implode("\n", [
        'Nouveau message envoyé depuis le formulaire de contact de ' . $site . '.',
        '',
        'Nom : ' . $values['name'],
        'E-mail : ' . $values['email'],
        'Objet : ' . $values['subject'],
        'Date : ' . contact_date(),
        '',
        $values['message'],
        '',
        '-- ',
        'Répondez à cet e-mail pour écrire directement à ' . $values['name'] . '.',
    ]) . "\n";
}

function contact_date(): string
{
    $now = new DateTimeImmutable('now', new DateTimeZone('Europe/Paris'));
    if (!class_exists(IntlDateFormatter::class)) {
        return $now->format('d/m/Y à H:i');
    }

    return (string) (new IntlDateFormatter('fr_FR', IntlDateFormatter::FULL, IntlDateFormatter::SHORT, 'Europe/Paris'))->format($now);
}
