<?php

declare(strict_types=1);

require __DIR__ . '/../src/bootstrap.php';

echo render('layout', [
    'page' => [
        'path' => 'mentions-legales',
        'title' => 'Mentions légales · ' . full_name(),
        'description' => 'Mentions légales, données personnelles et gestion des cookies du site de ' . full_name() . '.',
    ],
    'main' => render('pages/legal'),
]);
