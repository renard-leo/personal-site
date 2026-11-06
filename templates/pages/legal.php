<?php

declare(strict_types=1);

$legal = content('legal');
$host = $legal['host'];
$paragraphs = static function (array $items): string {
    return implode("\n", array_map(static fn (string $item): string => '                            <p>' . rich($item) . '</p>', $items));
};
$toc = [
    'editeur' => 'Éditeur',
    'hebergeur' => 'Hébergeur',
    'propriete' => 'Propriété intellectuelle',
    'donnees' => 'Données personnelles',
    'cookies' => 'Cookies',
];
?>
        <article class="legal" aria-labelledby="legal-title">
            <div class="container">
                <header class="legal-head">
                    <p class="eyebrow" aria-hidden="true"><span class="eyebrow-mark">//</span> mentions légales</p>
                    <h1 class="legal-title" id="legal-title">Mentions légales</h1>
                </header>
                <div class="legal-grid">
                    <nav class="legal-toc" aria-labelledby="legal-toc-title">
                        <h2 class="legal-toc-title" id="legal-toc-title">Sur cette page</h2>
                        <ol role="list">
<?php foreach ($toc as $id => $label): ?>
                            <li><a href="#<?= e($id) ?>"><?= t($label) ?></a></li>
<?php endforeach; ?>
                        </ol>
                    </nav>
                    <div class="legal-body prose">
                        <section id="editeur" aria-labelledby="editeur-title">
                            <h2 id="editeur-title"><?= t($toc['editeur']) ?></h2>
<?= $paragraphs($legal['publisher']) ?>
                        </section>
                        <section id="hebergeur" aria-labelledby="hebergeur-title">
                            <h2 id="hebergeur-title"><?= t($toc['hebergeur']) ?></h2>
                            <p><?= t($host['name']) ?><br><?= t($host['address']) ?><br><a href="<?= e($host['url']) ?>" rel="noopener"><?= e(preg_replace('#^https?://#', '', $host['url'])) ?></a></p>
                        </section>
                        <section id="propriete" aria-labelledby="propriete-title">
                            <h2 id="propriete-title"><?= t($toc['propriete']) ?></h2>
<?= $paragraphs($legal['intellectualProperty']) ?>
                        </section>
                        <section id="donnees" aria-labelledby="donnees-title">
                            <h2 id="donnees-title"><?= t($toc['donnees']) ?></h2>
                            <h3>Formulaire de contact</h3>
<?= $paragraphs($legal['privacy']['form']) ?>
                            <h3>Google Analytics</h3>
<?= $paragraphs($legal['privacy']['analytics']) ?>
                            <h3>Google reCAPTCHA</h3>
<?= $paragraphs($legal['privacy']['antispam']) ?>
                            <h3>Vos droits</h3>
<?= $paragraphs($legal['privacy']['rights']) ?>
                        </section>
                        <section id="cookies" aria-labelledby="cookies-title">
                            <h2 id="cookies-title"><?= t($toc['cookies']) ?></h2>
<?= $paragraphs($legal['cookies']) ?>
                            <p class="legal-action"><?= render('partials/consent-opener', ['class' => 'button button-secondary']) ?></p>
                        </section>
                    </div>
                </div>
            </div>
        </article>
