<?php

declare(strict_types=1);

/**
 * @var string $id
 * @var string $title
 */
$eyebrow = '// ' . mb_strtolower($title);
?>
                <div class="section-head" data-reveal="title" style="--chars: <?= mb_strlen($eyebrow) ?>">
                    <p class="eyebrow" aria-hidden="true"><span class="eyebrow-mark">//</span> <?= e(mb_strtolower($title)) ?></p>
                    <h2 class="section-title" id="<?= e($id) ?>-title"><?= t($title) ?></h2>
                </div>
