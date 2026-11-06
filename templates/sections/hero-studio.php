<?php

declare(strict_types=1);

$identity = content('identity');
$years = (int) $identity['yearsOfExperience'];
$github = content('links.github');
$widths = [320, 480, 640, 800, 1040];
$srcset = static fn (string $extension, array $sizes): string => implode(', ', array_map(
    static fn (int $width): string => asset("assets/img/leo-renard-{$width}.{$extension}") . " {$width}w",
    $sizes,
));
$sizes = '(min-width: 80rem) 23rem, (min-width: 64rem) 30vw, (min-width: 48rem) 38vw, 80vw';
// Graduations tous les 100 px de part et d'autre de l'origine du plan de travail (début du contenu).
$rulerX = range(-15, 26);
$rulerY = range(-2, 14);
?>
        <section class="hero hero-studio" aria-labelledby="hero-title" data-studio>
            <div class="studio-rulers" aria-hidden="true">
                <div class="studio-ruler studio-ruler-x">
                    <div class="studio-scale"><?php foreach ($rulerX as $n): ?><span style="--n: <?= $n ?>"><?= $n * 100 ?></span><?php endforeach; ?></div>
                    <span class="studio-ruler-band" data-studio-band="x"></span>
                    <span class="studio-ruler-mark" data-studio-mark="x"></span>
                </div>
                <div class="studio-ruler studio-ruler-y">
                    <div class="studio-scale"><?php foreach ($rulerY as $n): ?><span style="--n: <?= $n ?>"><?= $n * 100 ?></span><?php endforeach; ?></div>
                    <span class="studio-ruler-band" data-studio-band="y"></span>
                    <span class="studio-ruler-mark" data-studio-mark="y"></span>
                </div>
                <span class="studio-ruler-corner"></span>
            </div>
            <div class="container studio-board">
                <div class="studio-columns" aria-hidden="true"><?= str_repeat('<span></span>', 12) ?></div>
                <h1 class="studio-name" id="hero-title" data-inspect="h1"><span class="studio-name-line"><?= e($identity['firstName']) ?></span> <span class="studio-name-line"><?= e($identity['lastName']) ?></span></h1>
                <p class="studio-role" data-inspect="p">
                    <span class="studio-role-title"><?= t($identity['role']) ?><span class="visually-hidden">,</span></span>
                    <span class="studio-role-meta"><span><?= e(plural($years, 'an', 'ans')) ?> d’expérience</span><span><span class="visually-hidden">, </span><?= t($identity['location']) ?></span></span>
                </p>
                <p class="studio-lead" data-inspect="p"><?= t(content('hero.lead')) ?></p>
                <div class="studio-actions">
                    <a class="button button-primary" href="#contact" data-inspect="a">Me contacter<?= icon('arrow') ?></a>
                    <a class="studio-link" href="<?= e($github['url']) ?>" rel="me noopener" data-inspect="a"><?= icon('github') ?><span><?= t($github['label']) ?></span><?= icon('external', 'icon icon-trailing') ?></a>
                </div>
                <figure class="studio-portrait" data-inspect="img">
                    <picture class="studio-photo">
                        <source type="image/avif" srcset="<?= e($srcset('avif', $widths)) ?>" sizes="<?= e($sizes) ?>">
                        <source type="image/webp" srcset="<?= e($srcset('webp', $widths)) ?>" sizes="<?= e($sizes) ?>">
                        <img src="<?= e(asset('assets/img/leo-renard-640.jpg')) ?>" srcset="<?= e($srcset('jpg', $widths)) ?>" sizes="<?= e($sizes) ?>" width="1040" height="1300" alt="<?= e('Portrait de ' . full_name()) ?>" loading="eager" fetchpriority="high">
                    </picture>
                </figure>
<?php if (open_to_work()): ?>
                <div class="studio-comment" data-inspect="div">
                    <picture class="studio-comment-pin" aria-hidden="true">
                        <source type="image/avif" srcset="<?= e(asset('assets/img/leo-renard-320.avif')) ?>">
                        <source type="image/webp" srcset="<?= e(asset('assets/img/leo-renard-320.webp')) ?>">
                        <img src="<?= e(asset('assets/img/leo-renard-320.jpg')) ?>" width="320" height="400" alt="" decoding="async">
                    </picture>
                    <div class="studio-comment-body">
                        <p class="studio-comment-author" aria-hidden="true"><?= e(full_name()) ?></p>
                        <p class="studio-comment-text"><?= t(content('openToWork.status')) ?></p>
                        <dl class="studio-comment-facts">
                            <div><dt class="visually-hidden">Mode de travail</dt><dd><?= t(content('openToWork.workModes')) ?></dd></div>
                            <div><dt class="visually-hidden">Permis de conduire</dt><dd><?= t(content('openToWork.drivingLicence')) ?></dd></div>
                        </dl>
                    </div>
                </div>
<?php endif; ?>
            </div>
            <div class="studio-overlay" aria-hidden="true">
                <span class="studio-guide studio-guide-x" data-studio-guide="x"></span>
                <span class="studio-guide studio-guide-y" data-studio-guide="y"></span>
                <span class="studio-box studio-box-margin" data-studio-box="margin"></span>
                <span class="studio-box studio-box-padding" data-studio-box="padding"></span>
                <div class="studio-selection" data-studio-selection>
                    <span class="studio-edge" data-edge="top"></span>
                    <span class="studio-edge" data-edge="right"></span>
                    <span class="studio-edge" data-edge="bottom"></span>
                    <span class="studio-edge" data-edge="left"></span>
                    <span class="studio-handle" data-handle="nw"></span>
                    <span class="studio-handle" data-handle="ne"></span>
                    <span class="studio-handle" data-handle="se"></span>
                    <span class="studio-handle" data-handle="sw"></span>
                    <span class="studio-tag" data-studio-tag></span>
                    <span class="studio-size" data-studio-size></span>
                </div>
            </div>
            <div class="studio-statusbar" aria-hidden="true">
                <p class="studio-path" data-studio-path>main › section.hero</p>
                <p class="studio-readout"><span data-studio-dims></span><span data-studio-pointer></span></p>
            </div>
        </section>
