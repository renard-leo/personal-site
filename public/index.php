<?php

declare(strict_types=1);

require __DIR__ . '/../src/bootstrap.php';

[$city] = split_location(content('identity.location'));

echo render('layout', [
    'page' => [
        'path' => '',
        'title' => full_name() . ' · ' . content('identity.role'),
        'description' => sprintf('%s, %s à %s, %s d’expérience. %s', full_name(), mb_strtolower(content('identity.role')), $city, plural((int) content('identity.yearsOfExperience'), 'an', 'ans'), content('hero.lead')),
        'scripts' => array_merge(hero_variant() === 'studio' ? ['assets/js/hero-studio.js'] : [], ['assets/js/contact-form.js']),
    ],
    'main' => render('pages/home'),
]);
