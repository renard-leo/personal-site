<?php

declare(strict_types=1);

/** @var array $nav */

$period = static function (string $start, ?string $end): string {
    $from = '<time datetime="' . e($start) . '">' . format_month($start) . '</time>';
    $to = $end !== null ? '<time datetime="' . e($end) . '">' . format_month($end) . '</time>' : 'aujourd’hui';

    return $from . ' – ' . $to;
};
?>
        <section class="section section-career" id="parcours" aria-labelledby="parcours-title">
            <div class="container">
<?= render('partials/section-head', ['id' => 'parcours', 'title' => $nav['parcours']]) ?>
                <div class="career-grid">
                    <div class="career-column">
                        <h3 class="minor-title" data-reveal="heading">Expériences</h3>
                        <ol class="timeline" role="list">
<?php foreach (content('experiences', []) as $experience): ?>
                            <li class="timeline-item" data-reveal="graph">
                                <article class="experience">
                                    <header class="experience-head">
                                        <span class="logo-tile"><img src="<?= e(asset($experience['logo'])) ?>" alt="" width="40" height="40" loading="lazy" decoding="async"></span>
                                        <div class="experience-heading">
                                            <h4 class="experience-company"><?= t($experience['company']) ?></h4>
                                            <p class="experience-meta">
<?php if ($experience['description']): ?>
                                                <span><?= t($experience['description']) ?></span>
                                                <span><span class="visually-hidden">, </span><?= t($experience['location']) ?></span>
<?php else: ?>
                                                <span><?= t($experience['location']) ?></span>
<?php endif; ?>
                                            </p>
                                        </div>
                                    </header>
                                    <ul class="positions" role="list">
<?php foreach ($experience['positions'] as $position): ?>
                                        <li class="position">
                                            <p class="position-title"><?= t($position['title']) ?> <span class="tag"><?= t($position['contract']) ?></span></p>
                                            <p class="position-dates"><?= $period($position['start'], $position['end']) ?><span class="position-duration"><span class="visually-hidden">, </span><?= e($position['duration'] ?? format_duration($position['start'], $position['end'])) ?></span></p>
                                        </li>
<?php endforeach; ?>
                                    </ul>
                                    <ul class="missions" role="list">
<?php foreach ($experience['missions'] as $mission): ?>
                                        <li><?= t($mission) ?></li>
<?php endforeach; ?>
                                    </ul>
                                </article>
                            </li>
<?php endforeach; ?>
                        </ol>
                    </div>
                    <div class="career-column">
                        <h3 class="minor-title" data-reveal="heading">Formations</h3>
                        <ol class="timeline timeline-compact" role="list">
<?php foreach (content('education', []) as $degree): ?>
                            <li class="timeline-item" data-reveal="graph">
                                <article class="degree">
                                    <p class="degree-year"><time datetime="<?= e($degree['year']) ?>"><?= e($degree['year']) ?></time></p>
                                    <h4 class="degree-title"><?= t($degree['title']) ?></h4>
                                    <p class="degree-school"><?= t($degree['school']) ?><span class="degree-city"><span class="visually-hidden">, </span><?= t($degree['city']) ?></span></p>
                                </article>
                            </li>
<?php endforeach; ?>
                        </ol>
                    </div>
                </div>
            </div>
        </section>
