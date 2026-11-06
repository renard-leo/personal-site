<?php

declare(strict_types=1);

/** @var bool $isHome */

$anchor = static fn (string $id): string => $isHome ? '#' . $id : url('#' . $id);
?>
    <header class="site-header">
        <div class="container header-bar">
            <a class="brand" href="<?= e(url()) ?>">
                <img class="brand-logo" src="<?= e(asset('assets/img/logo.svg')) ?>" alt="" width="40" height="40">
                <span class="brand-name"><?= e(full_name()) ?><span class="visually-hidden">, accueil</span></span>
            </a>
            <nav class="site-nav" aria-label="Navigation principale">
                <button class="nav-toggle" type="button" popovertarget="nav-menu">
                    <?= icon('menu') ?><span>Menu</span>
                </button>
                <ul class="nav-list" id="nav-menu" popover role="list">
<?php foreach (content('navigation', []) as $item): ?>
                    <li><a class="nav-link" href="<?= e($anchor($item['anchor'])) ?>" data-nav-link="<?= e($item['anchor']) ?>"><?= t($item['label']) ?></a></li>
<?php endforeach; ?>
                </ul>
            </nav>
            <button class="icon-button motion-toggle" type="button" aria-pressed="false" data-motion-toggle>
                <?= icon('pause', 'icon icon-pause') ?><?= icon('play', 'icon icon-play') ?>
                <span class="visually-hidden">Mettre en pause les animations</span>
            </button>
            <button class="icon-button theme-toggle" type="button" aria-pressed="false" data-theme-toggle>
                <?= icon('moon', 'icon icon-moon') ?><?= icon('sun', 'icon icon-sun') ?>
                <span class="visually-hidden">Thème sombre</span>
            </button>
        </div>
    </header>
