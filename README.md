# renardleo.fr

Site personnel de **Léo Renard**, développeur full-stack à Épinal : profil, compétences, parcours et formulaire de contact.

Le site est volontairement sobre côté technique : du PHP sans framework, du SCSS compilé et quelques modules JavaScript natifs. Le navigateur ne charge qu'une bibliothèque, servie par le site lui-même (consentement aux cookies), et le serveur une seule, [PHPMailer](https://github.com/PHPMailer/PHPMailer), pour l'envoi des e-mails en SMTP.

## Stack

- **PHP 8.3**, sans framework : gabarits PHP, contenu centralisé dans un seul fichier. Composer n'installe que PHPMailer (transport SMTP du formulaire).
- **SCSS** compilé avec [Sass](https://sass-lang.com/) ; design system en propriétés personnalisées CSS, thèmes clair et sombre.
- **JavaScript** natif en modules ES (thème, navigation, consentement, formulaire), minifié par [esbuild](https://esbuild.github.io/) ; seule bibliothèque : [CookieConsent v3](https://cookieconsent.orestbida.com/) pour le bandeau de consentement.
- **Node.js** uniquement pour la compilation (Sass), la minification du JavaScript (esbuild), la génération des images ([sharp](https://sharp.pixelplumbing.com/)) et la copie de CookieConsent dans `public/assets/vendor/`.
- Hébergement mutualisé **Infomaniak** (Apache).

## Arborescence

```
.githooks/post-merge     mise à jour du serveur après chaque git pull
.github/workflows/       vérification de chaque commit (GitHub Actions, sans accès au serveur)
composer.json, .lock     dépendance PHP (PHPMailer), versions figées
config/
    config.example.php   modèle de configuration (versionné)
    config.php           configuration réelle, secrets compris (non versionné)
js/                      modules JavaScript sources (minifiés dans public/assets/js/)
public/                  racine web
    index.php            accueil
    mentions-legales.php
    contact.php          traitement du formulaire (POST uniquement)
    sitemap.xml.php      crée public/sitemap.xml au premier appel
    robots.txt.php       crée public/robots.txt au premier appel
    assets/              CSS compilé, JavaScript minifié, images générées, CV (docs/), bibliothèques (vendor/)
    .htaccess            URL sans .php, cache des fichiers statiques, compression
resources/images/        images sources (portrait, logo)
scripts/                 génération des images et du SVG du logo, copie des bibliothèques
scss/                    variables, design system, mixins, composants
src/
    bootstrap.php        chargement commun à toutes les pages
    content.php          tous les textes du site
    helpers.php          échappement, URL, typographie française…
    security.php         en-têtes de sécurité et Content Security Policy
    csrf.php             jeton anti-CSRF signé et contrôle de l'origine
    rate-limit.php       limitation des envois par adresse IP (hachée)
    recaptcha.php        vérification reCAPTCHA v3 côté serveur
    contact.php          validation du formulaire et composition de l'e-mail
    mailer.php           envoi : SMTP (PHPMailer), mail() ou journal de développement
templates/               mise en page, pages, sections et fragments
var/                     données d'exécution, créé automatiquement (non versionné)
vendor/                  dépendances Composer (non versionné, créé par composer install)
.htaccess                repli si la racine du site ne peut pas pointer sur public/
```

## Installation locale

Prérequis : PHP 8.3 (extensions `mbstring`, `intl`, `curl`, `openssl`), Apache avec `mod_rewrite` (WAMP, MAMP, Laragon…), [Composer](https://getcomposer.org/) et Node.js 20 ou plus.

```bash
composer install
npm install
npm run build          # images, logo SVG, bibliothèques, CSS et JavaScript
cp config/config.example.php config/config.php
php -r "echo bin2hex(random_bytes(32));"   # à coller dans appSecret
```

Dans `config/config.php`, en local, passer `mail.transport` à `'log'` : les messages du formulaire sont alors écrits dans `var/mail.log` au lieu d'être envoyés.

Pendant le développement, `npm run watch:css` recompile le SCSS et `npm run watch:js` régénère les modules JavaScript, non minifiés, à chaque modification. Les fichiers de `public/assets/js/` sont générés : modifier ceux de `js/`, puis lancer `npm run build:js` avant un déploiement.

Sous WAMP, PHP ne connaît souvent aucune autorité de certification : la vérification reCAPTCHA côté serveur échoue alors (erreur journalisée) et l'envoi est accepté sans elle. Pour la tester en local, renseigner `curl.cainfo` dans le `php.ini` (et `openssl.cafile` pour un envoi SMTP chiffré, dont le certificat est toujours vérifié).

Le site fonctionne dans un sous-dossier (par exemple `http://localhost/claude-site/`) : le `.htaccess` racine redirige les requêtes vers `public/` et les URL sont calculées automatiquement.

## Configuration

Tout se règle dans `config/config.php` :

| Clé | Rôle |
|---|---|
| `siteUrl` | URL publique, sans barre finale (URL canoniques, Open Graph, sitemap, robots.txt, objet des e-mails). |
| `openToWork` | `true` affiche tout ce qui concerne la recherche d'emploi : statut, mode de travail, permis, introduction « Un poste… » du contact. Textes dans `content.php`, clé `openToWork`. |
| `hero` | Version du hero : `'studio'` (plan de travail et inspecteur) ou `'classic'` (ancienne version, conservée). |
| `appSecret` | Clé secrète de 64 caractères hexadécimaux : signature du jeton anti-CSRF et empreinte des adresses IP. Vide, le formulaire refuse les envois. La changer invalide les formulaires déjà ouverts (le visiteur n'a qu'à renvoyer). |
| `trustedProxies` | Adresses des relais (proxy, répartiteur de charge) dont on lit `X-Forwarded-For`. À laisser vide si `REMOTE_ADDR` contient l'IP du visiteur. |
| `googleAnalytics.measurementId` | Identifiant GA4 (`G-…`). Vide, Google Analytics n'est ni proposé ni chargé. |
| `recaptcha.siteKey`, `recaptcha.secretKey` | Clés **reCAPTCHA v3** (console d'administration Google, domaine du site déclaré). Les deux renseignées, reCAPTCHA devient **obligatoire** : sans l'accord du visiteur, le bouton d'envoi reste désactivé et le serveur refuse tout envoi sans jeton. Vides, reCAPTCHA n'est ni proposé ni chargé. |
| `recaptcha.minScore` | Score minimal accepté, de 0 à 1 (0,5 par défaut). |
| `mail.transport` | `'smtp'` (envoi authentifié avec PHPMailer, recommandé), `'mail'` (fonction `mail()` de PHP) ou `'log'` (écriture dans `var/mail.log`, pour le développement). |
| `mail.to` | Adresse qui reçoit les messages. |
| `mail.from` | Adresse d'expédition, **existante et du domaine du site** (SPF, délivrabilité). En SMTP chez Infomaniak, elle doit être celle du compte authentifié (`mail.smtp.username`). |
| `mail.fromName` | Nom affiché de l'expéditeur (« Formulaire de contact » si vide). |
| `mail.smtp.host`, `mail.smtp.port` | Serveur SMTP et port : chez Infomaniak, `mail.infomaniak.com`, port 465 (SMTPS) ou 587 (STARTTLS). |
| `mail.smtp.encryption` | `'ssl'` (SMTPS, port 465), `'tls'` (STARTTLS, port 587) ou `''` (aucun chiffrement, à réserver à un serveur local). Le certificat du serveur est toujours vérifié. |
| `mail.smtp.username`, `mail.smtp.password` | Identifiants du compte d'envoi (chez Infomaniak : adresse e-mail complète et mot de passe de cette adresse). Le mot de passe n'est jamais journalisé. |
| `mail.smtp.timeout` | Délai d'attente en secondes (10 par défaut) : au-delà, le visiteur reçoit un message d'indisponibilité. |

Les services Google n'apparaissent dans le bandeau de consentement et dans la Content Security Policy que si leur clé est renseignée.

## Déploiement sur Infomaniak

Les étapes 1 et 2 sont remplacées par la [mise à jour par git pull](#mise-à-jour-par-git-pull) ; elles ne servent qu'en cas d'envoi manuel par SFTP.

1. **Dépendances PHP** : en local, `composer install --no-dev --optimize-autoloader` (sans les outils de développement, chargement optimisé). Envoyer ensuite le dossier `vendor/` ; ou, avec un accès SSH chez Infomaniak, envoyer `composer.json` et `composer.lock` et lancer la même commande sur le serveur. Sans `vendor/`, les pages fonctionnent mais le transport `'smtp'` répond « indisponible » et le journalise.
2. **Fichiers à envoyer** (SFTP) : `config/` (avec un `config.php` de production), `public/`, `src/`, `templates/`, `vendor/` et le `.htaccess` racine.
   **À ne pas envoyer** : `node_modules/`, `js/`, `resources/`, `scripts/`, `scss/`, `var/`, `.claude/`, `package*.json`, `README.md`. Le CSS, le JavaScript et les images étant compilés dans `public/assets/` (`npm run build`), Node.js n'est pas nécessaire sur le serveur.
3. **Racine du site** : envoyer le projet dans un dossier de l'hébergement (par exemple `/sites/renardleo.fr`), puis, dans le Manager (Hébergements & sites › nom du site › Paramètres avancés › Gérer), faire pointer le dossier du site sur son sous-dossier `public/` ([FAQ 963](https://www.infomaniak.com/fr/support/faq/963/modifier-le-dossier-dun-site-web)). Ainsi, `config/`, `src/`, `var/` et `vendor/` ne sont jamais accessibles depuis le web.
4. **PHP 8.3** : à choisir dans les paramètres avancés du site.
5. **HTTPS** : installer un certificat Let's Encrypt et activer la redirection HTTPS. L'en-tête HSTS n'est envoyé qu'en HTTPS.
6. **E-mail en SMTP (recommandé)** : `mail.transport` à `'smtp'`, serveur `mail.infomaniak.com`, port 465 avec `'ssl'` (ou 587 avec `'tls'`), identifiant = adresse e-mail complète du compte d'envoi et mot de passe de cette adresse. `mail.from` doit être **cette même adresse**, sinon Infomaniak refuse le message (« Sender mismatch », code 550) ; l'adresse du visiteur reste en `Reply-To`. Sources : [FAQ 576 (PHPMailer)](https://www.infomaniak.com/fr/support/faq/576/using-phpmailer-on-infomaniak), [FAQ 468 (ports)](https://www.infomaniak.com/fr/support/faq/468/comprendre-les-ports-et-protocoles-de-messagerie).
   **Avec `mail()`** : la fonction est **désactivée par défaut**. L'activer site par site (Gérer les paramètres avancés › onglet PHP / Apache), puis renseigner dans l'onglet Général une **adresse d'expédition existante**, identique à `mail.from`. Ces envois non authentifiés sont limités (50 par 24 heures par défaut) et moins bien délivrés. Sources : [FAQ 2150](https://www.infomaniak.com/fr/support/faq/2150/utiliser-lenvoi-non-authentifie-non-recommande-de-mail-depuis-un-site-web), [FAQ 2065](https://faq.infomaniak.com/2065).
7. **Dossier `var/`** : créé automatiquement au premier envoi, à côté de `public/` ; PHP doit pouvoir écrire dans le dossier du projet.
8. **Vérifier** les en-têtes (`curl -I https://renardleo.fr/`), envoyer un message de test et consulter le journal d'erreurs PHP en cas d'échec : les erreurs de configuration y sont décrites en clair.

## Mise à jour par git pull

Le site se met à jour quand on lance `git pull` en SSH sur le serveur : GitHub n'a aucun accès au serveur. Après chaque pull, git exécute `.githooks/post-merge`, qui :

1. réinstalle les dépendances PHP (`composer install --no-dev`) si `composer.lock` a changé, et celles de Node.js (`npm ci`) si `package-lock.json` a changé ;
2. recompile seulement ce dont les sources ont changé (images, logo, bibliothèques, CSS, JavaScript) : les autres fichiers gardent leur date, donc leur `?v=`, et restent en cache chez les visiteurs ;
3. supprime `public/robots.txt` et `public/sitemap.xml`, régénérés au prochain appel.

Les fichiers compilés ne sont pas versionnés : le dépôt ne contient que les sources. `config/config.php` et `var/`, absents du dépôt, ne sont jamais touchés. En cas d'erreur, le hook s'arrête et donne la commande de reprise, `FULL=1 sh .githooks/post-merge`, qui refait tout.

Le workflow `.github/workflows/verification.yml` vérifie à chaque push la syntaxe PHP et la compilation, sans secret ni accès au serveur : attendre sa coche verte sur GitHub avant de lancer le pull.

Le hook est versionné : un pull exécute la version du dépôt. Garder pour soi l'accès en écriture au dépôt ; pour relire avant d'appliquer, `git fetch`, `git log HEAD..origin/main`, puis `git merge --ff-only` (le hook s'exécute aussi).

### Mise en place (une seule fois)

1. **Dépôt** (en local) : au premier commit, marquer le hook comme exécutable avec `git add --chmod=+x .githooks/post-merge`. Le `.gitattributes` garde les fins de ligne LF, indispensables au hook sur le serveur.
2. **Accès SSH** : dans le Manager, créer un compte FTP + SSH pour le site et y autoriser sa clé publique ed25519 dans `~/.ssh/authorized_keys` (dossier `.ssh` en 700, fichier en 600). Infomaniak refuse les clés RSA, et la connexion par clé n'existe pas sur les offres Starter ([FAQ 2054](https://www.infomaniak.com/fr/support/faq/2054/connect-with-ssh-key)).
3. **Outils du serveur** : en SSH, `git --version`, `composer --version`, `php -v` et `node -v` doivent répondre. Git et Composer 2 sont fournis par Infomaniak ([FAQ 2463](https://www.infomaniak.com/fr/support/faq/2463/using-git-and-github-with-web-hosting), [FAQ 2118](https://www.infomaniak.com/fr/support/faq/2118/use-composer-2-via-ssh-on-your-hosting)).
    - Si `php -v` n'affiche pas 8.3, indiquer une commande Composer lancée avec PHP 8.3 (alias décrits dans la FAQ 2118) : `git config deploy.composer "<commande>"` dans le dossier du site.
    - **Node.js 20.19 ou plus est indispensable** pour compiler. Sans lui, le hook s'arrête avant toute compilation et le site garde ses fichiers précédents.
4. **Lecture du dépôt** : dépôt public, rien à faire (clone en HTTPS). Dépôt privé : sur le serveur, `ssh-keygen -t ed25519 -f ~/.ssh/github_renardleo -N ""`, puis ajouter `github_renardleo.pub` dans GitHub (Settings › Deploy keys, **sans** « Allow write access ») et dans `~/.ssh/config` :

    ```
    Host github.com
        IdentityFile ~/.ssh/github_renardleo
        IdentitiesOnly yes
    ```
5. **Clone et réglages**, dans le dossier de l'hébergement :

    ```bash
    git clone git@github.com:<compte>/<dépôt>.git renardleo.fr   # https://github.com/… pour un dépôt public
    cd renardleo.fr
    git config core.hooksPath .githooks   # lance le hook versionné après chaque pull
    git config pull.ff only               # le serveur ne fait jamais de commit
    ```
6. **Configuration de production** : créer `config/config.php` (voir [Configuration](#configuration)).
7. **Premier déploiement** : le clone ne déclenche pas le hook, le lancer une fois avec `FULL=1 sh .githooks/post-merge`. Puis faire pointer le site sur le sous-dossier `public/` (étape 3 du déploiement sur Infomaniak).

### À chaque mise à jour

```bash
ssh <utilisateur>@<hôte>
cd renardleo.fr
git pull
```

## Choix techniques

### Une seule dépendance côté navigateur

Pas de framework JavaScript, de bibliothèque d'animation ni de police web : les polices système suffisent et les modules ES natifs sont chargés en `type="module"`. esbuild les minifie un par un, sans bundle : chaque fichier reste un module et ses imports relatifs restent valables. Seule exception, CookieConsent est installé par npm à une version fixe, puis copié dans `public/assets/vendor/` par `npm run build:vendor` : aucun CDN. Les fichiers appelés par les gabarits sont versionnés par leur date de modification (`?v=`) et mis en cache un an par Apache ; les modules qu'ils importent (`consent.js`, CookieConsent) sont revalidés à chaque visite.

### Référencement

`/sitemap.xml` et `/robots.txt` sont créés par PHP à partir de `siteUrl` : aucune URL de production n'est écrite en dur. Tant que `public/sitemap.xml` n'existe pas, la réécriture « /page → page.php » du `.htaccess` appelle `public/sitemap.xml.php`, qui écrit le fichier puis le renvoie ; Apache le sert ensuite directement, sans PHP. Même principe pour `robots.txt`. Pour les régénérer (nouvelle page, changement de `siteUrl`), il suffit de supprimer le fichier : la mise à jour par `git pull` le fait à chaque fois. Si `public/` n'est pas accessible en écriture, l'échec est journalisé et PHP continue de répondre à chaque appel. Toute nouvelle page est à ajouter à la liste de `sitemap.xml.php`.

### CV

Déposer le PDF dans `public/assets/docs/cv-leo-renard.pdf` (chemin réglé dans `content.php`, clé `cv`) : le lien « Télécharger mon CV » apparaît alors en tête des liens de la section Contact, avec son format et son poids calculés automatiquement. Sans fichier, rien ne s'affiche. Le PDF est versionné par `?v=` comme les autres fichiers statiques, et Apache lui ajoute `X-Robots-Tag: noindex` pour qu'il n'apparaisse pas dans les résultats des moteurs de recherche.

### Animations CSS

Les lueurs du fond, la parallaxe du portrait et la réduction de l'en-tête reposent sur les *scroll-driven animations* (`animation-timeline: scroll()` et `view()`), sans JavaScript. Les apparitions prolongent le hero « du design au code », avec un effet par famille d'éléments : le repère `// profil` se tape puis le titre se compile, les paragraphes se rendent ligne à ligne, les cartes tracent leur cadre avant de se peindre, `competences.json` reçoit sa coloration syntaxique, la frise du parcours se déploie comme un graphe de commits et la bordure des champs du formulaire se dessine. Ce sont des animations CSS (`clip-path`, `transform`, `opacity`, couleurs) déclenchées par un `IntersectionObserver` à l'entrée dans l'écran, pour qu'elles se jouent en entier : liées au défilement, elles passaient inaperçues. Sans JavaScript, avec le mouvement réduit ou le site en pause, tout reste visible et immobile. Les changements de thème et de page utilisent les *View Transitions*.

### Consentement conforme aux recommandations de la CNIL

Le consentement est géré par [CookieConsent v3](https://cookieconsent.orestbida.com/) (`js/consent.js` l'adapte au site, `scss/components/_cookieconsent.scss` relie ses couleurs aux thèmes). Google Analytics et Google reCAPTCHA ne sont chargés qu'après un accord explicite, recueilli par un bandeau non bloquant où « Tout refuser » a le même poids que « Tout accepter ». Le choix, enregistré dans le cookie `cc_cookie` (strictement nécessaire), est modifiable à tout moment par le bouton « Gestion des cookies » du pied de page (toujours présent : sans service configuré, il ouvre une fenêtre qui le précise et aucun bandeau ne s'affiche) et redemandé après 6 mois ; incrémenter `REVISION` dans `consent.js` le redemande à tous, par exemple après l'ajout d'un service. Les cookies Google Analytics expirent au bout de 13 mois au plus, sans prolongation à chaque visite, et sont effacés si l'accord est retiré. Le thème est gardé dans le stockage local du navigateur.

### Sécurité du formulaire de contact

Sans cookie ni session, le formulaire combine plusieurs protections indépendantes :

- **Jeton anti-CSRF signé** : horodatage et aléa signés en HMAC-SHA256 avec `appSecret`, comparés en temps constant. Un envoi moins de 3 secondes après l'affichage de la page (robot) ou plus de 2 heures après est refusé ; en cas d'expiration, un nouveau jeton est renvoyé pour que le visiteur ne perde pas sa saisie.
- **Contrôle de l'origine** : l'en-tête `Origin` (ou `Referer` à défaut) doit désigner le site lui-même.
- **Pot de miel** : un champ invisible ; s'il est rempli, le serveur simule un succès sans rien envoyer.
- **Limitation** : 5 messages par heure et par adresse IP (par préfixe /64 en IPv6). L'IP n'est jamais stockée en clair, seulement son empreinte HMAC, dans un fichier verrouillé (`flock`) purgé automatiquement après une heure.
- **reCAPTCHA v3**, obligatoire dès que ses deux clés sont configurées, et chargé uniquement avec consentement : le jeton est demandé juste avant l'envoi et vérifié côté serveur (succès, action, score, nom d'hôte). Sans accord, une notice placée avant le bouton d'envoi (désactivé) propose « Autoriser reCAPTCHA », qui n'accepte que cette finalité en conservant les autres choix, ou l'accès à la gestion des cookies, avec LinkedIn comme alternative. Si le script est bloqué (bloqueur de publicités, proxy), le visiteur en est averti au lieu d'envoyer pour rien. Le serveur refuse tout envoi sans jeton, y compris sans JavaScript (message explicite après redirection). Seule exception, si Google ne répond pas à la vérification, l'envoi est accepté grâce aux autres protections. Le badge Google n'est masqué que lorsque la mention obligatoire est affichée sous le bouton.
- **Validation serveur** : normalisation des saisies, longueurs maximales, adresse vérifiée avec `FILTER_VALIDATE_EMAIL` et refus de tout retour à la ligne dans le nom, l'adresse et l'objet (injection d'en-têtes). L'e-mail part en texte brut UTF-8 (objet encodé, corps en *quoted-printable*), avec l'adresse du visiteur en `Reply-To`.

Le formulaire fonctionne aussi sans JavaScript (redirection avec message de confirmation) et renvoie du JSON quand il est envoyé par `fetch`.

### En-têtes de sécurité

Envoyés par PHP pour ne pas dépendre de `mod_headers` :

- **Content Security Policy** générée à chaque requête : seuls le site et, s'ils sont configurés, les domaines documentés par Google pour GA4 et reCAPTCHA sont autorisés. Le seul script en ligne (application du thème avant le premier affichage) est autorisé par son empreinte SHA-256, calculée à partir du même fichier que celui inséré dans la page. Les balises `<style>` sont interdites ; seuls les attributs `style` restent permis (variables CSS de l'animation du code, badge reCAPTCHA). `frame-ancestors 'none'` empêche l'intégration du site dans un cadre.
- `X-Content-Type-Options`, `Referrer-Policy: strict-origin-when-cross-origin`, `Permissions-Policy` restrictive et `Strict-Transport-Security` en HTTPS.

### Accessibilité et performance

HTML sémantique, navigation au clavier, contrastes vérifiés (WCAG 2.2 AA), respect de `prefers-reduced-motion` et de `prefers-color-scheme`. Images en AVIF, WebP et JPEG à plusieurs largeurs (`srcset`), générées depuis une source unique.
