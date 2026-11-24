<?php

declare(strict_types=1);

/** @var array $project */

$projects = projects();
$index = array_search($project['slug'], array_column($projects, 'slug'), true);
$siblings = array_filter([
    'prev' => $projects[$index - 1] ?? null,
    'next' => $projects[$index + 1] ?? null,
]);
$live = project_url($project['url']);
?>
        <article class="page project" aria-labelledby="project-title">
            <div class="container">
                <header class="page-head project-head">
                    <p class="eyebrow"><a class="eyebrow-link" href="<?= e(url('#projets')) ?>"><span class="eyebrow-mark" aria-hidden="true">//</span> <span class="visually-hidden">Tous les </span>projets</a></p>
                    <h1 class="page-title" id="project-title"><?= t($project['title']) ?></h1>
                    <p class="project-lead"><?= t($project['summary']) ?></p>
                    <p class="project-meta"><span><?= e($project['year']) ?></span><span><span class="visually-hidden">, </span><?= t($project['role']) ?></span></p>
<?php if ($live || $project['code']): ?>
                    <p class="page-actions">
<?php if ($live): ?>
                        <a class="button button-primary" href="<?= e($live) ?>" rel="noopener">Voir le projet<span class="visually-hidden"> <?= t($project['title']) ?></span><?= icon('external') ?></a>
<?php endif; ?>
<?php if ($project['code']): ?>
                        <a class="button button-secondary" href="<?= e($project['code']) ?>" rel="noopener"><?= icon('github') ?>Voir le code</a>
<?php endif; ?>
                    </p>
<?php endif; ?>
                </header>
                <figure class="project-media">
<?= render('partials/project-picture', ['name' => $project['slug'], 'sizes' => '(min-width: 80rem) 72rem, 94vw', 'alt' => $project['imageAlt'], 'eager' => true]) ?>
                </figure>
                <div class="project-layout">
                    <div class="project-body">
<?php foreach ($project['description'] as $paragraph): ?>
                        <p class="project-text" data-reveal="paint"><?= t($paragraph) ?></p>
<?php endforeach; ?>
<?php if (!empty($project['process'])): ?>
                        <h2 class="project-subtitle" data-reveal="heading">Démarche</h2>
                        <ol class="project-steps" role="list">
<?php foreach ($project['process'] as $step): ?>
                            <li class="project-step" data-reveal="paint">
                                <h3 class="project-step-title"><?= t($step['title']) ?></h3>
<?php foreach ((array) $step['text'] as $paragraph): ?>
                                <p class="project-step-text"><?= rich($paragraph) ?></p>
<?php endforeach; ?>
<?php foreach ($step['images'] ?? [] as $image): ?>
                                <figure class="project-step-media">
<?= render('partials/project-picture', ['name' => $project['slug'] . '/' . $image['file'], 'sizes' => '(min-width: 48rem) 41rem, 84vw', 'alt' => $image['alt']]) ?>
<?php if (!empty($image['caption'])): ?>
                                    <figcaption class="project-step-caption"><?= t($image['caption']) ?></figcaption>
<?php endif; ?>
                                </figure>
<?php endforeach; ?>
                            </li>
<?php endforeach; ?>
                        </ol>
<?php endif; ?>
                        <h2 class="project-subtitle" data-reveal="heading">Points forts</h2>
                        <ul class="project-highlights" role="list" data-reveal="list">
<?php foreach ($project['highlights'] as $highlight): ?>
                            <li><?= t($highlight) ?></li>
<?php endforeach; ?>
                        </ul>
                    </div>
                    <div class="project-aside">
                        <h2 class="minor-title" data-reveal="heading">Technologies</h2>
                        <ul class="tag-list" role="list">
<?php foreach ($project['stack'] as $technology): ?>
                            <li class="tag"><?= t($technology) ?></li>
<?php endforeach; ?>
                        </ul>
                    </div>
                </div>
<?php if ($siblings): ?>
                <nav class="project-pager" aria-label="Autres projets">
                    <ul role="list">
<?php foreach ($siblings as $direction => $sibling): ?>
                        <li class="project-pager-item project-pager-<?= e($direction) ?>">
                            <a class="project-pager-link" href="<?= e(url('projets/' . $sibling['slug'])) ?>" rel="<?= e($direction) ?>">
                                <span class="project-pager-label"><?= $direction === 'prev' ? 'Projet précédent' : 'Projet suivant' ?></span>
                                <span class="project-pager-title"><?= t($sibling['title']) ?></span>
                                <?= icon('arrow', 'icon project-pager-icon') ?>
                            </a>
                        </li>
<?php endforeach; ?>
                    </ul>
                </nav>
<?php endif; ?>
            </div>
        </article>
