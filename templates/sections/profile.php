<?php

declare(strict_types=1);

/** @var array $nav */

$about = content('about');
?>
        <section class="section section-profile" id="profil" aria-labelledby="profil-title">
            <div class="container">
<?= render('partials/section-head', ['id' => 'profil', 'title' => $nav['profil']]) ?>
                <div class="profile-grid">
                    <div class="profile-story">
<?php foreach ($about['paragraphs'] as $index => $paragraph): ?>
                        <p class="<?= $index === 0 ? 'profile-lead' : 'profile-text' ?>" data-reveal="paint"><?= t($paragraph) ?></p>
<?php endforeach; ?>
                    </div>
                    <div class="profile-projects">
                        <h3 class="minor-title" data-reveal="heading">Types de projets</h3>
                        <ul class="project-types" role="list" data-reveal="list">
<?php foreach ($about['projectTypes'] as $type): ?>
                            <li><?= t($type) ?></li>
<?php endforeach; ?>
                        </ul>
                    </div>
                </div>
                <h3 class="subsection-title" data-reveal="heading">Au-delà du code</h3>
                <ul class="qualities" role="list">
<?php foreach ($about['beyondCode'] as $quality): ?>
                    <li class="quality" data-reveal="frame">
                        <h4 class="quality-title"><?= t($quality['title']) ?></h4>
                        <p class="quality-text"><?= t($quality['text']) ?></p>
                    </li>
<?php endforeach; ?>
                </ul>
            </div>
        </section>
