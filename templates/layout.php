<?php

declare(strict_types=1);

/**
 * @var array $page title, description, path, scripts
 * @var string $main
 */

$canonical = absolute_url($page['path']);
$ogImage = absolute_url('assets/img/og-image.jpg');
[$city, $region] = split_location(content('identity.location'));

$person = array_filter([
    '@context' => 'https://schema.org',
    '@type' => 'Person',
    'name' => full_name(),
    'givenName' => content('identity.firstName'),
    'familyName' => content('identity.lastName'),
    'jobTitle' => content('identity.role'),
    'url' => absolute_url(),
    'image' => absolute_url('assets/img/leo-renard-800.jpg'),
    'worksFor' => current_employer() ? ['@type' => 'Organization', 'name' => current_employer()] : null,
    'address' => array_filter(['@type' => 'PostalAddress', 'addressLocality' => $city, 'addressRegion' => $region, 'addressCountry' => 'FR']),
    'sameAs' => array_values(array_map(static fn (array $link): string => $link['url'], content('links', []))),
]);
?>
<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= t($page['title']) ?></title>
    <meta name="description" content="<?= t($page['description']) ?>">
    <link rel="canonical" href="<?= e($canonical) ?>">
    <meta name="color-scheme" content="light dark">
    <meta name="theme-color" content="<?= e(THEME_COLORS['light']) ?>" media="(prefers-color-scheme: light)" data-light="<?= e(THEME_COLORS['light']) ?>" data-dark="<?= e(THEME_COLORS['dark']) ?>">
    <meta name="theme-color" content="<?= e(THEME_COLORS['dark']) ?>" media="(prefers-color-scheme: dark)" data-light="<?= e(THEME_COLORS['light']) ?>" data-dark="<?= e(THEME_COLORS['dark']) ?>">
    <script><?= theme_script() /* autorisé par son empreinte dans la CSP (src/security.php) */ ?></script>
    <link rel="stylesheet" href="<?= e(asset('assets/vendor/cookieconsent/cookieconsent.css')) ?>">
    <link rel="stylesheet" href="<?= e(asset('assets/css/main.css')) ?>">
    <link rel="icon" href="<?= e(url('favicon.ico')) ?>" sizes="32x32">
    <link rel="icon" href="<?= e(asset('favicon.svg')) ?>" type="image/svg+xml">
    <link rel="apple-touch-icon" href="<?= e(url('apple-touch-icon.png')) ?>">

    <meta property="og:type" content="profile">
    <meta property="og:locale" content="fr_FR">
    <meta property="og:site_name" content="<?= e(full_name()) ?>">
    <meta property="og:title" content="<?= t($page['title']) ?>">
    <meta property="og:description" content="<?= t($page['description']) ?>">
    <meta property="og:url" content="<?= e($canonical) ?>">
    <meta property="og:image" content="<?= e($ogImage) ?>">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
    <meta property="og:image:alt" content="<?= e('Portrait de ' . full_name()) ?>">
    <meta property="profile:first_name" content="<?= e(content('identity.firstName')) ?>">
    <meta property="profile:last_name" content="<?= e(content('identity.lastName')) ?>">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?= t($page['title']) ?>">
    <meta name="twitter:description" content="<?= t($page['description']) ?>">
    <meta name="twitter:image" content="<?= e($ogImage) ?>">
    <script type="application/ld+json"><?= json_for_script($person) ?></script>

    <script type="module" src="<?= e(asset('assets/js/main.js')) ?>"></script>
    <script type="module" src="<?= e(asset('assets/js/motion.js')) ?>"></script>
<?php foreach ($page['scripts'] ?? [] as $script): ?>
    <script type="module" src="<?= e(asset($script)) ?>"></script>
<?php endforeach; ?>
</head>
<body>
    <a class="skip-link" href="#contenu">Aller au contenu</a>
<?= render('partials/backdrop') ?>
<?= render('partials/icons') ?>
<?= render('partials/header', ['isHome' => $page['path'] === '']) ?>
    <main id="contenu" tabindex="-1">
<?= $main ?>
    </main>
<?= render('partials/footer') ?>
    <script type="application/json" id="site-config"><?= json_for_script(['consent' => (object) public_config(), 'legalUrl' => url('mentions-legales')]) ?></script>
</body>
</html>
