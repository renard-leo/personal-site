<?php

declare(strict_types=1);

?>
    <footer class="site-footer">
        <div class="container footer-bar">
            <p class="footer-copy">© <?= e(date('Y')) ?> <?= e(full_name()) ?></p>
            <ul class="footer-links" role="list">
                <li><a class="footer-link" href="<?= e(url('mentions-legales')) ?>">Mentions légales</a></li>
                <li><?= render('partials/consent-opener', ['class' => 'footer-link']) ?></li>
<?php foreach (content('links', []) as $network => $link): ?>
                <li><a class="footer-link" href="<?= e($link['url']) ?>" rel="me noopener"><?= icon($network) ?><?= t($link['label']) ?></a></li>
<?php endforeach; ?>
            </ul>
        </div>
    </footer>
