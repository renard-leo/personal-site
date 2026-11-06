<?php

declare(strict_types=1);

$identity = content('identity');
$years = (int) $identity['yearsOfExperience'];
$widths = [320, 480, 640, 800, 1040];
$srcset = static fn (string $extension): string => implode(', ', array_map(
    static fn (int $width): string => asset("assets/img/leo-renard-{$width}.{$extension}") . " {$width}w",
    $widths,
));
$sizes = '(min-width: 64rem) 26rem, (min-width: 48rem) 17rem, 16rem';
$github = content('links.github');

// Extrait de code décoratif : chaque ligne est une liste de [type de jeton, texte].
$code = [
    [['var', '$leo'], ['punct', ' = '], ['keyword', 'new'], ['plain', ' '], ['type', 'Developer'], ['punct', '('], ['string', "'" . full_name() . "'"], ['punct', ');']],
    [['var', '$leo'], ['punct', '->'], ['fn', 'role'], ['punct', '('], ['string', "'" . $identity['role'] . "'"], ['punct', ')']],
    [['plain', '    '], ['punct', '->'], ['fn', 'experience'], ['punct', '('], ['string', "'" . plural($years, 'an', 'ans') . "'"], ['punct', ')']],
    [['plain', '    '], ['punct', '->'], ['fn', 'location'], ['punct', '('], ['string', "'" . $identity['location'] . "'"], ['punct', open_to_work() ? ')' : ');']],
    ...(open_to_work() ? [[['plain', '    '], ['punct', '->'], ['fn', 'openTo'], ['punct', '('], ['string', "'opportunités'"], ['punct', ');']]] : []),
];
$typed = 0;
?>
        <section class="hero hero-classic" aria-labelledby="hero-title">
            <div class="container hero-grid">
                <div class="hero-text">
<?php if (open_to_work()): ?>
                    <p class="hero-status"><span class="status-dot" aria-hidden="true"></span><?= t(content('openToWork.status')) ?></p>
<?php endif; ?>
                    <h1 class="hero-title" id="hero-title"><?= e(full_name()) ?></h1>
                    <p class="hero-role">
                        <span class="hero-role-title"><?= t($identity['role']) ?></span>
                        <span class="hero-role-years"><?= e(plural($years, 'an', 'ans')) ?> d’expérience</span>
                    </p>
                    <p class="hero-lead"><?= t(content('hero.lead')) ?></p>
                    <div class="hero-actions">
                        <a class="button button-primary" href="#contact">Me contacter<?= icon('arrow') ?></a>
                        <a class="button button-secondary" href="<?= e($github['url']) ?>" rel="me noopener"><?= icon('github') ?><?= t($github['label']) ?></a>
                    </div>
                    <dl class="hero-facts">
                        <div class="hero-fact">
                            <dt><?= icon('pin') ?><span class="visually-hidden">Localisation</span></dt>
                            <dd><?= t($identity['location']) ?></dd>
                        </div>
<?php if (open_to_work()): ?>
                        <div class="hero-fact">
                            <dt><?= icon('briefcase') ?><span class="visually-hidden">Mode de travail</span></dt>
                            <dd><?= t(content('openToWork.workModes')) ?></dd>
                        </div>
                        <div class="hero-fact">
                            <dt><?= icon('licence') ?><span class="visually-hidden">Permis de conduire</span></dt>
                            <dd><?= t(content('openToWork.drivingLicence')) ?></dd>
                        </div>
<?php endif; ?>
                    </dl>
                </div>
                <div class="hero-media">
                    <picture class="hero-photo">
                        <source type="image/avif" srcset="<?= e($srcset('avif')) ?>" sizes="<?= e($sizes) ?>">
                        <source type="image/webp" srcset="<?= e($srcset('webp')) ?>" sizes="<?= e($sizes) ?>">
                        <img src="<?= e(asset('assets/img/leo-renard-640.jpg')) ?>" srcset="<?= e($srcset('jpg')) ?>" sizes="<?= e($sizes) ?>" width="1040" height="1300" alt="<?= e('Portrait de ' . full_name()) ?>" fetchpriority="high">
                    </picture>
                    <div class="code-window hero-code" aria-hidden="true">
                        <div class="code-window-bar"><span class="code-window-tab"><?= icon('file') ?>developer.php</span></div>
                        <ol class="code-body">
<?php foreach ($code as $index => $tokens):
    $text = implode('', array_column($tokens, 1));
    $length = mb_strlen($text);
?>
                            <li class="code-line" style="--start: <?= $typed ?>; --chars: <?= $length ?>"><span class="code-line-text"><?php foreach ($tokens as [$type, $value]): ?><span class="tok-<?= e($type) ?>"><?= e($value) ?></span><?php endforeach; ?></span><?php if ($index === array_key_last($code)): ?><span class="code-caret"></span><?php endif; ?></li>
<?php
    $typed += $length;
endforeach;
?>
                        </ol>
                    </div>
                </div>
            </div>
        </section>
