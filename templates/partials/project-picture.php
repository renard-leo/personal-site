<?php

declare(strict_types=1);

/**
 * @var string $name image sous assets/img/projects, sans largeur ni extension (« hit-or-miss », « hit-or-miss/logo »)
 * @var string $sizes
 * @var string $alt vide pour une image décorative
 * @var bool $eager image visible au chargement
 */

$alt ??= '';
$eager ??= false;
$base = 'assets/img/projects/' . $name;
$srcset = static fn (string $extension): string => implode(', ', array_map(
    static fn (int $width): string => asset("{$base}-{$width}.{$extension}") . " {$width}w",
    PROJECT_IMAGE_WIDTHS,
));
$width = max(PROJECT_IMAGE_WIDTHS);
?>
                        <picture>
                            <source type="image/avif" srcset="<?= e($srcset('avif')) ?>" sizes="<?= e($sizes) ?>">
                            <source type="image/webp" srcset="<?= e($srcset('webp')) ?>" sizes="<?= e($sizes) ?>">
                            <img src="<?= e(asset("{$base}-800.jpg")) ?>" srcset="<?= e($srcset('jpg')) ?>" sizes="<?= e($sizes) ?>" width="<?= $width ?>" height="<?= intdiv($width * 9, 16) ?>" alt="<?= t($alt) ?>" <?= $eager ? 'loading="eager" fetchpriority="high"' : 'loading="lazy" decoding="async"' ?>>
                        </picture>
