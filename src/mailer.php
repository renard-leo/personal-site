<?php

declare(strict_types=1);

use PHPMailer\PHPMailer\Exception as MailerException;
use PHPMailer\PHPMailer\PHPMailer;

const MAIL_TRANSPORTS = ['mail', 'smtp', 'log'];
// Valeurs de PHPMailer::SMTPSecure : SMTPS (465), STARTTLS (587), aucun chiffrement.
const SMTP_ENCRYPTIONS = ['ssl', 'tls', ''];

/** Raison pour laquelle l'envoi est impossible (à journaliser), ou null si la configuration est complète. */
function mail_config_error(): ?string
{
    $transport = config('mail.transport', 'mail');
    $port = config('mail.smtp.port');

    return match (true) {
        !in_array($transport, MAIL_TRANSPORTS, true) => 'mail.transport doit valoir « mail », « smtp » ou « log »',
        $transport === 'log' => null,
        !is_email(config('mail.to')) => 'mail.to est vide ou invalide',
        !is_email(config('mail.from')) => 'mail.from est vide ou invalide',
        $transport === 'mail' => function_exists('mail') ? null : 'la fonction mail() est désactivée sur l’hébergement',
        !class_exists(PHPMailer::class) => 'PHPMailer est absent (dossier vendor/ manquant : lancer composer install)',
        trim((string) config('mail.smtp.host', '')) === '' => 'mail.smtp.host est vide',
        !is_int($port) || $port < 1 || $port > 65535 => 'mail.smtp.port doit être un numéro de port',
        !in_array(config('mail.smtp.encryption', ''), SMTP_ENCRYPTIONS, true) => 'mail.smtp.encryption doit valoir « ssl », « tls » ou « »',
        default => null,
    };
}

/**
 * Envoie un message en texte brut UTF-8 selon mail.transport. $message : to, from, fromName, replyTo, subject, body.
 * Faux en cas d'échec ; la raison est journalisée, jamais le mot de passe SMTP.
 */
function send_mail(array $message): bool
{
    return match (config('mail.transport', 'mail')) {
        'smtp' => send_mail_smtp($message),
        'log' => send_mail_log($message),
        default => send_mail_php($message),
    };
}

/** « Nom <adresse> » ; le nom est encodé (RFC 2047) dès qu'il sort des lettres ASCII, chiffres, espaces et tirets. */
function mail_address(string $email, string $name): string
{
    $name = trim((string) preg_replace('/[\x00-\x1F\x7F]+/', ' ', $name));
    if ($name === '') {
        return $email;
    }

    return (preg_match('/^[A-Za-z0-9 _-]+$/', $name) ? $name : '=?UTF-8?B?' . base64_encode($name) . '?=') . ' <' . $email . '>';
}

function send_mail_smtp(array $message): bool
{
    $mailer = new PHPMailer(true);
    $timeout = max(1, (int) config('mail.smtp.timeout', 10));
    $username = (string) config('mail.smtp.username', '');

    try {
        $mailer->isSMTP();
        $mailer->Host = (string) config('mail.smtp.host');
        $mailer->Port = (int) config('mail.smtp.port');
        $mailer->SMTPSecure = (string) config('mail.smtp.encryption', '');
        // Seul le chiffrement demandé est utilisé : pas de STARTTLS opportuniste qui masquerait une erreur de réglage.
        $mailer->SMTPAutoTLS = false;
        $mailer->SMTPOptions = ['ssl' => ['verify_peer' => true, 'verify_peer_name' => true, 'allow_self_signed' => false]];
        $mailer->SMTPAuth = $username !== '';
        $mailer->Username = $username;
        $mailer->Password = (string) config('mail.smtp.password', '');
        $mailer->Timeout = $timeout;
        $mailer->getSMTPInstance()->Timelimit = $timeout;
        $mailer->XMailer = ' ';
        $mailer->CharSet = PHPMailer::CHARSET_UTF8;
        $mailer->Encoding = PHPMailer::ENCODING_QUOTED_PRINTABLE;
        $mailer->setFrom($message['from'], $message['fromName'], false);
        $mailer->addAddress($message['to']);
        $mailer->addReplyTo($message['replyTo']);
        $mailer->Subject = $message['subject'];
        $mailer->Body = $message['body'];

        return $mailer->send();
    } catch (MailerException) {
        // ErrorInfo décrit l'étape en échec et la réponse du serveur, sans les identifiants.
        error_log(sprintf('Envoi SMTP impossible vers %s:%d : %s', $mailer->Host, $mailer->Port, $mailer->ErrorInfo));

        return false;
    }
}

function send_mail_php(array $message): bool
{
    $headers = [
        'From' => mail_address($message['from'], $message['fromName']),
        'Reply-To' => $message['replyTo'],
        'MIME-Version' => '1.0',
        'Content-Type' => 'text/plain; charset=UTF-8',
        'Content-Transfer-Encoding' => 'quoted-printable',
    ];

    // Avertissement masqué pour ne pas corrompre la réponse JSON ; il est repris dans le journal.
    $sent = @mail(
        $message['to'],
        mb_encode_mimeheader($message['subject'], 'UTF-8', 'B', "\r\n", strlen('Subject: ')),
        quoted_printable_encode(str_replace("\n", "\r\n", $message['body'])),
        $headers,
    );
    if (!$sent) {
        error_log('Échec de mail() (' . (error_get_last()['message'] ?? 'raison inconnue') . ').');
    }

    return $sent;
}

/** Mode « log » (développement) : le message lisible, sans encodage de transport, est ajouté à var/mail.log. */
function send_mail_log(array $message): bool
{
    $entry = implode("\n", [
        '===== ' . date(DATE_ATOM) . ' =====',
        'To: ' . ($message['to'] !== '' ? $message['to'] : '(mail.to non renseigné)'),
        'Subject: ' . $message['subject'],
        'From: ' . trim($message['fromName'] . ' <' . $message['from'] . '>'),
        'Reply-To: ' . $message['replyTo'],
        'Content-Type: text/plain; charset=UTF-8',
        '',
        $message['body'],
    ]) . "\n";

    if (!storage_ready() || file_put_contents(storage_path('mail.log'), $entry, FILE_APPEND | LOCK_EX) === false) {
        error_log('Écriture impossible dans var/mail.log.');

        return false;
    }

    return true;
}
