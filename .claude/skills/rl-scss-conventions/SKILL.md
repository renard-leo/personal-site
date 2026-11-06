---
name: rl-scss-conventions
description: Conventions d'écriture SCSS/CSS du projet (propriétés sur une ligne, imbrication, variables responsive, tokens du design system). À utiliser dès qu'il faut écrire, modifier ou relire du SCSS ou du CSS.
---

# Conventions SCSS

## Format des propriétés

- Toutes les propriétés d'un sélecteur sur **une seule ligne**.
- Un espace après chaque `:`, `;` et `,`.
- Les propriétés sont séparées par `;`. La virgule reste réservée aux valeurs multiples (`transition`, `box-shadow`, `font-family`…).
- Pas de `;` après la dernière propriété d'un bloc sur une ligne.
- Indentation de 4 espaces.

```scss
// ✅
.title {font-size: var(--fs-xl); font-weight: var(--fw-bold); color: var(--color-primary); transition: color .2s, opacity .2s}

// ❌
.title {
    font-size: 2rem;
    font-weight: 700;
}
```

## Imbrication

Imbriquer dès que possible, dans cet ordre :

1. Propriétés du sélecteur courant (une ligne).
2. Variables CSS locales, si besoin.
3. Pseudo-classes et pseudo-éléments (`&:hover`, `&::before`).
4. Modificateurs (`&.is-active`, `&--large`).
5. Enfants (`.child`).
6. Media queries.

```scss
.card {
    display: flex; flex-direction: column; padding: var(--space-md); border: var(--bw-sm) solid var(--color-border);
    &:hover {border-color: var(--color-primary)}
    &.is-active {background: var(--color-bg-alt)}
    .card-title {font-size: var(--fs-lg); font-weight: var(--fw-semibold)}
    .card-body {flex: 1; color: var(--color-text-muted)}
    @media (max-width: $bp-md) {padding: var(--space-sm)}
}
```

Limiter l'imbrication à environ 3 niveaux pour garder une spécificité raisonnable. Au-delà, créer un nouveau bloc racine.

## Variables pour le responsive

Lorsqu'une même valeur est utilisée par plusieurs propriétés et doit changer selon le breakpoint (notamment avec flex), la déclarer en variable CSS locale. Au breakpoint, seule la variable est modifiée.

```scss
// Avant
.col {width: 50%; flex: 0 0 50%}

// Après
.col {
    --width: 50%;
    width: var(--width); flex: 0 0 var(--width);
    @media (max-width: $bp-md) {--width: 100%}
}
```

Le même principe s'applique aux colonnes de grille, aux gaps et aux tailles répétées.

## Organisation des fichiers

Variables, design system et mixins ont chacun leur propre partial, inclus dans le fichier principal. Ne jamais les redéfinir localement dans un fichier de composant.

```
scss/
├── _variables.scss    // tokens : couleurs, tailles, épaisseurs, espacements, breakpoints
├── _design-system.scss // classes utilitaires construites sur les variables
├── _s4_mixin.scss     // mixins existants du projet
├── components/
└── main.scss
```

```scss
// main.scss : l'ordre compte, chaque fichier dépend des précédents
@import "variables";
@import "s4_mixin";
@import "design-system";
@import "components/card";
```

Si le projet utilise déjà `@use` plutôt que `@import`, suivre sa méthode. Les noms de fichiers ci-dessus sont une base : respecter ceux du projet s'ils existent.

## Variables

- Ne jamais coder en dur une valeur réutilisable : tailles de texte, épaisseurs de police, épaisseurs de bordure, espacements, couleurs, rayons, ombres, breakpoints.
- Chercher la variable existante avant d'en créer une. Si elle manque, l'ajouter dans `_variables.scss`, jamais localement.
- Les variables CSS locales servant au responsive (voir plus haut) sont la seule exception.

```scss
// _variables.scss
:root {
    --fs-sm: .875rem; --fs-md: 1rem; --fs-lg: 1.25rem; --fs-xl: 2rem;
    --fw-regular: 400; --fw-semibold: 600; --fw-bold: 700;
    --bw-sm: 1px; --bw-md: 2px; --bw-lg: 4px;
    --space-xs: .25rem; --space-sm: .5rem; --space-md: 1rem; --space-lg: 2rem;
    --radius-sm: 4px; --radius-md: 8px;
}
$bp-sm: 576px; $bp-md: 768px; $bp-lg: 1024px;
```

Les breakpoints sont des variables SCSS, car les custom properties ne fonctionnent pas dans les media queries.

## Design system

- Classes utilitaires réunies dans `_design-system.scss`, construites uniquement à partir des variables.
- Préférer une classe utilitaire existante à une règle dupliquée. Si un besoin revient souvent, ajouter la classe dans ce fichier.

```scss
// _design-system.scss
.fs-lg {font-size: var(--fs-lg)}
.fw-bold {font-weight: var(--fw-bold)}
.bw-md {border-width: var(--bw-md)}
```

## Mixins

- Avant d'écrire une propriété, vérifier si un mixin existe dans le fichier de mixins du projet (généralement `s4_mixin.scss` ou `_s4_mixin.scss`) et l'utiliser dès que possible.
- C'est en particulier le cas pour les propriétés nécessitant des préfixes navigateurs (`-webkit-`, `-moz-`, `-ms-`) : ne jamais écrire les préfixes à la main si un mixin les gère.
- Cible de compatibilité : navigateurs des **5 dernières années**. Ajouter un préfixe seulement s'il est encore utile pour cette cible.
- Si un mixin manque pour une propriété préfixée ou un motif répété, l'ajouter dans le fichier de mixins plutôt que de dupliquer le code.

```scss
// ✅ mixin existant (nom à adapter à celui du projet)
.modal {position: fixed; inset: 0; @include transform(translateY(-50%)); @include backdrop-filter(blur(4px))}

// ❌ préfixes écrits à la main
.modal {-webkit-transform: translateY(-50%); transform: translateY(-50%)}
```

Un `@include` se place sur la même ligne que les propriétés, comme une propriété classique.

## Commentaires

Uniquement si nécessaire, sur une ligne, en `//`. Pas de commentaire qui décrit ce que la propriété fait déjà.
