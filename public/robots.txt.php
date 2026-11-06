<?php

declare(strict_types=1);

// Appelé par la réécriture « /page -> page.php » tant que public/robots.txt n'existe pas : crée ce fichier,
// qu'Apache sert ensuite directement. Le supprimer pour le régénérer (chaque git pull sur le serveur le fait).
require __DIR__ . '/../src/bootstrap.php';

$robots = "User-agent: *\nAllow: /\n\nSitemap: " . absolute_url('sitemap.xml') . "\n";

write_public_file('robots.txt', $robots);

header('Content-Security-Policy: ' . ENDPOINT_CSP);
header('Content-Type: text/plain; charset=utf-8');
echo $robots;
