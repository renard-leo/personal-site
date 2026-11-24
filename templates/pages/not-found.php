<?php

declare(strict_types=1);

?>
        <article class="page" aria-labelledby="not-found-title">
            <div class="container">
                <header class="page-head">
                    <p class="eyebrow" aria-hidden="true"><span class="eyebrow-mark">//</span> erreur 404</p>
                    <h1 class="page-title" id="not-found-title">Page introuvable</h1>
                </header>
                <p class="page-text">Cette page n’existe pas ou a été déplacée.</p>
                <p class="page-actions">
                    <a class="button button-primary" href="<?= e(url()) ?>">Retour à l’accueil<?= icon('arrow') ?></a>
                    <a class="button button-secondary" href="<?= e(url('#projets')) ?>">Voir les projets</a>
                </p>
            </div>
        </article>
