---
name: rl-boucle-personas
description: Réalise une tâche, la fait relire et noter par les personas utilisateurs (.claude/personas.md), puis la corrige et la refait relire tant que la note moyenne est inférieure au seuil (70 par défaut). À utiliser quand l'utilisateur demande une tâche revue ou validée par les personas.
argument-hint: <tâche> [seuil=70] [max=3] [panel=6 | panel=tous | avec Prénom, Prénom]
---

# Tâche revue par les personas

Demande : $ARGUMENTS

## Paramètres

- `seuil` : note moyenne minimale sur 100 pour valider. Défaut : 70.
- `max` : nombre maximal d'itérations, pour éviter une boucle infinie. Défaut : 3.
- `panel` : nombre de personas relecteurs, ou `tous`. Défaut : 6. L'utilisateur peut aussi les nommer (« avec Sophie, Karim »).

Lis-les dans la demande s'ils y figurent, sinon applique les défauts.

## Préparation

1. Lis `.claude/personas.md` : chaque section `###` est un persona, rangé sous sa catégorie `##`. Si le fichier est absent ou vide, demande à l'utilisateur de le compléter et arrête-toi.
2. Compose le panel :
   - personas nommés par l'utilisateur : uniquement ceux-là ;
   - `panel=tous` : tous les personas ;
   - sinon les N personas que la tâche concerne le plus. Couvre toutes les catégories, varie les genres, et inclus au moins un persona dont les freins sont directement mis à l'épreuve par la tâche.

   Annonce le panel avec la raison de chaque choix. Le panel reste le même à toutes les itérations, pour que les notes restent comparables.
3. Reformule la tâche et fixe 3 à 6 critères d'acceptation vérifiables, en tenant compte des objectifs et des freins du panel.

## Boucle (itération n = 1 à max)

1. **Réalisation**
   - Itération 1 : réalise la tâche en respectant le CLAUDE.md et les skills du projet.
   - Itérations suivantes : corrige à partir des retours précédents, en commençant par les blocages partagés et ceux des personas les moins bien notés. On améliore l'existant, on ne repart de zéro que si la majorité des personas rejette l'approche.
2. **Revue** : lance un agent `persona` par membre du panel, **tous en parallèle, dans un seul message**. Chacun reçoit :
   - sa fiche complète, copiée telle quelle ;
   - la tâche et ce que l'utilisateur verra et fera concrètement ;
   - la liste des fichiers créés ou modifiés, qu'il pourra lire lui-même ;
   - à partir de l'itération 2, ce qui a changé depuis son dernier retour ;
   - le format de réponse ci-dessous.
3. **Notation** : calcule la moyenne arithmétique des notes, arrondie à l'entier. Affiche un tableau : persona, note, verdict, blocage principal.
4. **Décision**
   - Moyenne ≥ seuil : la tâche est validée, sors de la boucle.
   - Moyenne < seuil et n < max : annonce les corrections prévues, puis passe à l'itération suivante.
   - Moyenne < seuil et n = max : arrête-toi, présente l'état et les blocages non résolus, et demande à l'utilisateur comment poursuivre.

## Format de réponse demandé à chaque persona

```
Note : <entier de 0 à 100>
Verdict : Valide | À améliorer | Bloquant
Objectif atteint : Oui | En partie | Non, avec une phrase d'explication
Ce qui marche pour moi :
- ...
Ce qui me bloque (du plus gênant au moins gênant) :
- ...
Ce qu'il faudrait changer :
- ...
```

Une note sous 50 doit être justifiée par au moins un blocage.

## Règles

- Les personas relisent et notent, ils ne modifient jamais de fichier.
- Tu arbitres les retours contradictoires entre personas. La demande de l'utilisateur et le CLAUDE.md priment sur l'avis d'un persona ; un retour écarté est signalé avec sa raison.
- Ne cherche pas à gonfler la note : ne réduis pas le périmètre et n'oriente pas les personas pour obtenir une meilleure note.
- Ne commite rien sans demande explicite.

## Compte rendu final

- Évolution de la note moyenne à chaque itération.
- Fichiers créés ou modifiés.
- Blocages restés ouverts ou écartés, avec leur raison.
