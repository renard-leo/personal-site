<?php

declare(strict_types=1);

/** @var array $nav */

// Technologies affichées sur la carte ; la page du projet les donne toutes.
$stackMax = 4;
?>
        <section class="section section-projects" id="projets" aria-labelledby="projets-title">
            <div class="container">
<?= render('partials/section-head', ['id' => 'projets', 'title' => $nav['projets']]) ?>
                <ul class="project-cards" role="list">
<?php foreach (projects() as $project): ?>
                    <li class="project-card" data-reveal="frame">
                        <div class="project-card-media">
<?= render('partials/project-picture', ['name' => $project['slug'], 'sizes' => '(min-width: 80rem) 35rem, (min-width: 48rem) 46vw, 92vw']) ?>
                        </div>
                        <div class="project-card-body">
                            <h3 class="project-card-title"><a class="project-card-link" href="<?= e(url('projets/' . $project['slug'])) ?>"><?= t($project['title']) ?></a></h3>
                            <p class="project-meta"><span><?= e($project['year']) ?></span><span><span class="visually-hidden">, </span><?= t($project['role']) ?></span></p>
                            <p class="project-card-summary"><?= t($project['summary']) ?></p>
                            <ul class="tag-list project-card-stack" role="list" aria-label="Technologies">
<?php foreach (array_slice($project['stack'], 0, $stackMax) as $technology): ?>
                                <li class="tag"><?= t($technology) ?></li>
<?php endforeach; ?>
                            </ul>
                        </div>
                    </li>
<?php endforeach; ?>
                </ul>
            </div>
        </section>
