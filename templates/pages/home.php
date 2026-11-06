<?php

declare(strict_types=1);

$nav = array_column(content('navigation', []), 'label', 'anchor');
?>
<?= render('sections/hero-' . hero_variant()) ?>
<?= render('sections/profile', ['nav' => $nav]) ?>
<?= render('sections/skills', ['nav' => $nav]) ?>
<?= render('sections/career', ['nav' => $nav]) ?>
<?= render('sections/contact', ['nav' => $nav]) ?>
