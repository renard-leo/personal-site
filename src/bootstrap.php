<?php

declare(strict_types=1);

const ROOT = __DIR__ . '/..';

date_default_timezone_set('Europe/Paris');

// Dépendances Composer (PHPMailer, pour le transport SMTP) : sans vendor/, seul ce transport est indisponible.
if (is_file(ROOT . '/vendor/autoload.php')) {
    require ROOT . '/vendor/autoload.php';
}

require __DIR__ . '/helpers.php';
require __DIR__ . '/security.php';
require __DIR__ . '/csrf.php';
require __DIR__ . '/rate-limit.php';
require __DIR__ . '/recaptcha.php';

$content = require __DIR__ . '/content.php';
$config = require ROOT . '/config/' . (is_file(ROOT . '/config/config.php') ? 'config.php' : 'config.example.php');

send_security_headers();
rate_limit_purge();
