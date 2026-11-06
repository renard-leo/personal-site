# CLAUDE.md

## Général
- Indentation : 4 espaces, jamais de tabulations.
- Commentaires : rares, concis, uniquement s'ils apportent une info absente du code.

## Design system
Ne jamais coder en dur une valeur réutilisable (taille et épaisseur de texte, bordures, espacements, couleurs, rayons, breakpoints) : utiliser ou compléter les variables, classes utilitaires et mixins du projet, chacun dans son propre fichier. Vérifier l'existant avant d'ajouter.

## SCSS
Pour tout SCSS/CSS, appliquer le skill `rl-scss-conventions`.



## Agents

### Lecture de fichiers
Pour lire ou explorer plusieurs fichiers, délègue au sous-agent Explore
plutôt que de les lire toi-même. Fais toi-même l'analyse à partir de son compte rendu.

### Personas
Les personas utilisateurs du projet sont décrits dans `.claude/personas.md` et incarnés par l'agent `persona`, qui note sur 100. Avis sur une fonctionnalité : skill `rl-audience`. Tâche revue jusqu'à une note moyenne d'au moins 70 : skill `rl-boucle-personas`. Ne jamais lancer l'agent `persona` hors de ces skills.