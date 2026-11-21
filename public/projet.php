<?php

declare(strict_types=1);

// Pages /projets/<slug>, réécrites vers ce fichier par public/.htaccess.
require __DIR__ . '/../src/bootstrap.php';

$slug = (string) ($_GET['slug'] ?? '');
$project = find_project($slug);

if ($project === null) {
    http_response_code(404);
    echo render('layout', [
        'page' => [
            'path' => 'projets/' . $slug,
            'title' => 'Page introuvable · ' . full_name(),
            'description' => 'Cette page n’existe pas ou a été déplacée.',
        ],
        'main' => render('pages/not-found'),
    ]);
    exit;
}

echo render('layout', [
    'page' => [
        'path' => 'projets/' . $project['slug'],
        'title' => $project['title'] . ' · ' . full_name(),
        'description' => $project['summary'],
        'image' => ['path' => 'assets/img/projects/' . $project['slug'] . '-og.jpg', 'alt' => $project['imageAlt']],
    ],
    'main' => render('pages/project', ['project' => $project]),
]);
