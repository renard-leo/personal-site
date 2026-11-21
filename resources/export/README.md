# Contenu du portfolio

Tout ce que le portfolio affiche vient de ce dossier. Rien n'est à modifier dans le code.

`npm run prebuild` vérifie le contenu, prépare les médias dans `public/media/` et génère `src/content/generated/content.json`. La commande est lancée automatiquement par `npm run dev` et `npm run build`. Pendant `npm run dev`, toute modification de ce dossier est prise en compte et la page se recharge. En cas d'erreur (fichier absent, JSON invalide…), le message indique le fichier et le champ en cause.

## Arborescence

```
site/
├── site.json             identité, langues, fichiers (logo, photo, CV), ordre des projets
├── contact.json          e-mail, formulaire, réseaux (identiques dans toutes les langues)
├── assets/               CV, logo, photo, logos des entreprises, image de partage
├── i18n/<langue>/        textes traduits, un fichier JSON par section
│   ├── site.json         titre et description (référencement)
│   ├── profile.json      poste, statut, présentation, compétences, qualités, fiche de la tour
│   ├── career.json       expériences et formations
│   ├── contact.json      phrase d'accroche, libellé du formulaire
│   ├── projects.json     titre de chaque projet pour l'interface (facultatif)
│   └── interface.json    textes de l'interface 3D, du terminal et de la version statique
└── projects/<slug>/
    ├── project.json      année et objet posé sur l'étagère
    ├── fr.html           page web complète du projet, avec son CSS (une par langue)
    └── images/           fichiers de la page (images, CSS…)
```

## site.json

| Champ | Rôle |
|---|---|
| `name` | Nom affiché partout |
| `careerStart` | Début de carrière (`AAAA-MM`), pour calculer les années d'expérience |
| `languages`, `defaultLanguage` | Langues disponibles et langue affichée |
| `url` | Adresse publique du portfolio (balises `canonical`, `og:url`, `og:image`) ; peut rester vide |
| `simpleUrl` | Cible des boutons « Version simple » ; vide : version statique intégrée |
| `themeColor` | Couleur de la barre du navigateur sur mobile |
| `files.logo` | Logo, dans `assets/` |
| `files.photo.file` | Photo de profil, dans `assets/` (la taille d'origine ne pose pas de problème) |
| `files.photo.crop` | Zone à garder, en pixels de l'original (`left`, `top`, `width`, `height`), idéalement au format 4:5 |
| `files.cv.file`, `files.cv.downloadName` | CV dans `assets/`, et nom du fichier téléchargé |
| `files.shareImage` | Image de partage (1200 × 630 px), dans `assets/` |
| `projects` | Projets affichés, dans l'ordre de l'étagère : rang du bas d'abord, puis rang du haut, 8 au maximum |

**Photo.** Elle est recadrée et réduite en portrait 800 × 1000 px. Sans `crop`, le recadrage 4:5 se fait automatiquement sur la zone la plus marquante de l'image. Le résultat est mis en cache tant que la photo et son cadrage ne changent pas.

## Textes et variables

Dans `i18n/<langue>/site.json` (titre, description) et dans `photoAlt` de `profile.json`, deux variables sont remplacées :
- `{name}` : le nom ;
- `{experience}` : les années d'expérience (par exemple « 6 ans »), recalculées à chaque build.

Dans `career.json`, les dates sont au format `AAAA-MM`, avec `"end": null` pour un poste en cours. `duration` remplace la durée calculée si besoin, et `logo` est un chemin dans `assets/`.

## Ajouter un projet

1. Créer `projects/<slug>/` avec `project.json`, `fr.html` et ses fichiers (images, CSS…).
2. Ajouter le slug dans `site.json › projects`, à la place voulue sur l'étagère.
3. Facultatif : ajouter un titre dans `i18n/<langue>/projects.json`, sous la clé du slug. À défaut, le `<title>` de la page est utilisé. Ce titre sert à l'interface : étiquette de l'objet sur l'étagère, barre de la fenêtre, terminal, lecteurs d'écran.

Dans `project.json` :
- `prop.type` choisit l'objet posé sur l'étagère, parmi `planet`, `rocket`, `trophy`, `books`, `robot`, `cube` et `globe` ;
- `prop.color` donne sa couleur ;
- `year` est facultative et n'apparaît que dans la liste du terminal (`projets`).

## Page HTML des projets

`<langue>.html` est une page web complète, avec son `<head>`, son CSS et son contenu, comme n'importe quel site. Elle est publiée à côté de ses fichiers (`media/projects/<slug>/`) et affichée dans un iframe, à la place de la fenêtre de l'écran. Le portfolio n'y ajoute rien, à part les boutons « projet précédent / suivant » sous l'iframe.

- **CSS :** dans un `<style>`, une feuille liée (`<link rel="stylesheet" href="style.css">`) ou des attributs `style`. Il est isolé du portfolio dans les deux sens.
- **Chemins relatifs :** ils partent du dossier du projet (`images/capture.webp`, `style.css`) et fonctionnent tels quels.
- **Liens :** ils s'ouvrent dans un nouvel onglet. Le prébuild ajoute `<base target="_blank">` si la page n'a pas de `<base>`.
- **Scripts :** ils ne s'exécutent pas, car la page est isolée (`sandbox`).
- **Ambiance :** la page reçoit `data-theme="day"` ou `"night"` sur son `<html>`, au rythme de la lampe du bureau. Le CSS peut s'en servir avec `:root[data-theme="night"] { … }`.
- **Échelle :** dans l'écran 3D, la page est agrandie (zoom 1,6) pour avoir la même taille de lecture que le reste de l'interface. Elle s'y affiche donc comme dans une fenêtre d'environ 720 px de large, et en largeur réelle sur mobile et dans la version statique : un CSS responsive s'adapte aux deux.
- **Clavier :** Échap, ← et → et les raccourcis du portfolio fonctionnent même quand le focus est dans la page.
- **Simple fragment :** un fichier sans `<html>` est enveloppé dans un document minimal, mais il n'a alors aucun style.

Les pages existantes servent de modèle : leur `<style>` contient des couleurs jour et nuit, un surtitre (`.eyebrow`), une accroche (`.lead`) et une liste d'étiquettes (`.tags`).

## Ajouter une langue

1. Ajouter son code dans `site.json › languages`.
2. Créer `i18n/<langue>/` avec les fichiers à traduire. Un fichier ou un champ absent est repris de la langue par défaut :
   - les objets sont complétés champ par champ ;
   - les listes d'objets (expériences, qualités…) sont complétées élément par élément, dans le même ordre ;
   - les autres listes (compétences, missions…) remplacent celles de la langue par défaut.
3. Ajouter `<langue>.html` aux projets. À défaut, la page de la langue par défaut est utilisée.

Le site affiche `defaultLanguage`. Toutes les langues déclarées sont validées et générées, prêtes pour un futur sélecteur de langue.
