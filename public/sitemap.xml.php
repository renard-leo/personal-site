<?php

declare(strict_types=1);

// Appelé par la réécriture « /page -> page.php » tant que public/sitemap.xml n'existe pas : crée ce fichier,
// qu'Apache sert ensuite directement. Le supprimer pour le régénérer (chaque git pull sur le serveur le fait).
require __DIR__ . '/../src/bootstrap.php';

$pages = ['', 'mentions-legales'];

$urls = implode('', array_map(static fn (string $path): string => '    <url><loc>' . e(absolute_url($path)) . "</loc></url>\n", $pages));
$xml = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<urlset xmlns=\"http://www.sitemaps.org/schemas/sitemap/0.9\">\n" . $urls . "</urlset>\n";

write_public_file('sitemap.xml', $xml);

header('Content-Security-Policy: ' . ENDPOINT_CSP);
header('Content-Type: application/xml; charset=utf-8');
echo $xml;
