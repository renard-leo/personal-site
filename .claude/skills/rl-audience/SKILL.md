---
name: rl-audience
description: Soumet une fonctionnalité demandée à l'audience de personas utilisateurs (.claude/personas.md), qui donnent chacun leur ressenti et une note sur 100. À utiliser quand l'utilisateur veut l'avis des personas ou de l'audience sur une idée ou une fonctionnalité, avant de la réaliser.
argument-hint: <fonctionnalité à évaluer> [panel=6 | panel=tous | avec Prénom, Prénom]
---

# Audience de personas

Fonctionnalité à évaluer : $ARGUMENTS

Aucune modification de fichier dans ce skill : on recueille des avis, on ne réalise rien.

## Étapes

1. Si la fonctionnalité n'est pas décrite, demande-la à l'utilisateur et arrête-toi.
2. Lis `.claude/personas.md` : chaque section `###` est un persona, rangé sous sa catégorie `##`. Si le fichier est absent ou vide, demande à l'utilisateur de le compléter et arrête-toi.
3. Reformule la fonctionnalité en 2 à 4 lignes : objectif, utilisateurs concernés, périmètre, et ce que l'utilisateur verrait et ferait concrètement. Ajoute les fichiers concernés s'ils existent.
4. Compose le panel :
   - personas nommés par l'utilisateur (« avec Sophie, Karim ») : uniquement ceux-là ;
   - `panel=tous` : tous les personas ;
   - sinon `panel=N` personas, 6 par défaut, choisis parmi ceux que la fonctionnalité concerne le plus. Couvre toutes les catégories, varie les genres, et inclus au moins un persona dont les freins sont directement mis à l'épreuve par la fonctionnalité.

   Annonce le panel en une ligne par persona, avec la raison du choix.
5. Lance un agent `persona` par membre du panel, **tous en parallèle, dans un seul message**. Chacun reçoit :
   - sa fiche complète, copiée telle quelle ;
   - la reformulation ;
   - le format de réponse ci-dessous.
6. Présente la synthèse.

## Format de réponse demandé à chaque persona

```
Note : <entier de 0 à 100>
Verdict : Pour | Réservé | Contre
Objectif atteint : Oui | En partie | Non, avec une phrase d'explication
Ce qui m'aide :
- ...
Ce qui me bloque :
- ...
Ce que j'aimerais :
- ...
```

Une note sous 50 doit être justifiée par au moins un blocage.

## Synthèse

- Tableau : persona, note, verdict, objectif atteint, ressenti principal en une ligne.
- Note moyenne (moyenne arithmétique, arrondie à l'entier).
- Blocages partagés par plusieurs personas, puis besoins propres à un seul.
- Recommandations classées par priorité, avec les personas concernés.
- Ta propre recommandation finale : réaliser telle quelle, réaliser avec ajustements, ou abandonner.

Les avis des personas éclairent la décision ; ils ne remplacent ni la demande de l'utilisateur ni les règles du CLAUDE.md.
