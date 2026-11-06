<?php

declare(strict_types=1);

/** @var array $nav */

$groups = content('skills', []);
?>
        <section class="section section-skills" id="competences" aria-labelledby="competences-title">
            <div class="container">
<?= render('partials/section-head', ['id' => 'competences', 'title' => $nav['competences']]) ?>
                <div class="code-window skills-file" data-reveal="syntax">
                    <div class="code-window-bar" aria-hidden="true"><span class="code-window-tab"><?= icon('file') ?>competences.json</span></div>
                    <div class="code-body">
                        <p class="code-line" aria-hidden="true" style="--line: 0"><span class="tok-punct">{</span></p>
                        <ul class="skill-groups" role="list">
<?php foreach ($groups as $index => $group): ?>
                            <li class="code-line skill-group" style="--line: <?= $index + 1 ?>">
                                <h3 class="skill-title"><span class="tok-punct" aria-hidden="true">"</span><?= t($group['title']) ?><span class="tok-punct" aria-hidden="true">": [</span></h3>
                                <ul class="skill-items" role="list">
<?php foreach ($group['items'] as $itemIndex => $item): ?>
                                    <li class="skill-item"><span class="tok-string"><span aria-hidden="true">"</span><?= t($item) ?><span aria-hidden="true">"</span></span><?php if ($itemIndex !== array_key_last($group['items'])): ?><span class="tok-punct">,</span><?php endif; ?></li>
<?php endforeach; ?>
                                </ul>
                                <span class="tok-punct" aria-hidden="true">]<?= $index !== array_key_last($groups) ? ',' : '' ?></span>
                            </li>
<?php endforeach; ?>
                        </ul>
                        <p class="code-line" aria-hidden="true" style="--line: <?= count($groups) + 1 ?>"><span class="tok-punct">}</span></p>
                    </div>
                </div>
            </div>
        </section>
