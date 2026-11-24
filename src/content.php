<?php

declare(strict_types=1);

$careerStart = new DateTimeImmutable('2020-09-01');
$linkedin = 'https://www.linkedin.com/in/l%C3%A9o-renard/';

return [
    'identity' => [
        'firstName' => 'Léo',
        'lastName' => 'Renard',
        'role' => 'Développeur full-stack',
        'careerStart' => $careerStart->format('Y-m'),
        'yearsOfExperience' => $careerStart->diff(new DateTimeImmutable('today'))->y,
        'location' => 'Épinal (Vosges)',
    ],

    // Affiché uniquement si config('openToWork') vaut true.
    'openToWork' => [
        'status' => 'En poste chez Section4, ouvert aux opportunités',
        'workModes' => 'Hybride ou sur site',
        'drivingLicence' => 'Permis B',
        'contactIntro' => 'Un poste, un projet ou une simple question ? Écrivez-moi, je vous réponds rapidement.',
    ],

    'hero' => [
        'lead' => 'Je conçois, développe et maintiens des sites et des applications web sur mesure, de l’intégration d’une maquette Figma jusqu’au back-end et à la base de données.',
    ],

    'links' => [
        'github' => ['label' => 'GitHub', 'url' => 'https://github.com/renard-leo'],
        'linkedin' => ['label' => 'LinkedIn', 'url' => $linkedin],
    ],

    // Lien affiché seulement si le fichier existe dans public/.
    'cv' => [
        'label' => 'Télécharger mon CV',
        'file' => 'assets/docs/cv-leo-renard.pdf',
    ],

    'navigation' => [
        ['label' => 'Profil', 'anchor' => 'profil'],
        ['label' => 'Compétences', 'anchor' => 'competences'],
        ['label' => 'Projets', 'anchor' => 'projets'],
        ['label' => 'Parcours', 'anchor' => 'parcours'],
        ['label' => 'Contact', 'anchor' => 'contact'],
    ],

    'about' => [
        'paragraphs' => [
            'Depuis 2020, je développe en agence des projets très variés : sites vitrines sur mesure, boutiques en ligne et applications métier, souvent reliés à des services tiers par leurs API. Cette diversité m’a appris à choisir l’outil adapté à chaque besoin, de WordPress à Symfony ou Node.js.',
            'Mon parcours en graphisme me permet d’échanger facilement avec les designers et de reproduire fidèlement une maquette Figma, jusque dans les détails.',
        ],
        'projectTypes' => [
            'Sites vitrines sur mesure',
            'E-commerce',
            'Applications métier',
            'Intégration d’API tierces',
        ],
        'beyondCode' => [
            ['title' => 'Esprit d’équipe', 'text' => 'Je partage volontiers mes connaissances et je donne un coup de main à mes collègues, nouveaux arrivants compris.'],
            ['title' => 'Choix techniques', 'text' => 'J’ai contribué aux choix techniques de deux projets clients, en les soumettant à la validation de l’équipe.'],
            ['title' => 'Relation client', 'text' => 'J’échange directement avec les clients pendant le développement de leur projet et lors du suivi de maintenance.'],
            ['title' => 'Organisation', 'text' => 'Je priorise mes tâches selon les délais et les contraintes techniques de chaque projet.'],
            ['title' => 'Accessibilité', 'text' => 'Je m’appuie sur le RGAA pour rendre mes intégrations plus accessibles, un sujet que j’approfondis projet après projet.'],
        ],
    ],

    'skills' => [
        ['title' => 'Back-end', 'items' => ['PHP orienté objet', 'Symfony', 'Twig', 'Node.js', 'Utilisation d’API REST', 'MySQL / MariaDB']],
        ['title' => 'Front-end', 'items' => ['HTML sémantique', 'SCSS', 'JavaScript', 'React', 'Next.js']],
        ['title' => 'CMS', 'items' => ['WordPress']],
        ['title' => 'Méthodes et outils', 'items' => ['Git','Accessibilité RGAA', 'Figma']],
        ['title' => 'Design', 'items' => ['Photoshop', 'Illustrator', 'Adobe XD']],
    ],

    // Une page par projet (/projets/<slug>), du plus récent au plus ancien. Image : resources/images/projects/<slug>.png (1600 × 900).
    // url : adresse absolue, ou chemin ajouté à config('projectsUrl'). code : dépôt public, sinon null.
    // process : étapes de la démarche ; text accepte un ou plusieurs paragraphes et des liens [texte](https://…).
    // images d'une étape : resources/images/projects/<slug>/<file>.png (1600 × 900), avec alt et une légende facultative (caption).
    // prod : false tant que le projet n'est pas en ligne ; il n'apparaît alors qu'en préprod (npm run build:preprod).
    'projects' => [
        [
            'slug' => 'hit-or-miss',
            'prod' => false,
            'title' => 'Hit or Miss',
            'year' => '2026',
            'role' => 'Conception et développement',
            'summary' => 'Un quiz musical multijoueur pour les soirées : une seconde d’un morceau pour trouver le titre, puis trois, puis dix.',
            'description' => [
                'Hit or Miss est un jeu télévisé musical à jouer entre amis. À chaque manche, il faut reconnaître un morceau à partir d’un extrait d’une seconde, qui s’allonge à trois puis à dix secondes : plus la réponse est rapide, plus elle rapporte de points.',
                'J’ai conçu et développé l’ensemble du projet, du moteur de jeu en temps réel à la bibliothèque musicale, jusqu’à sa mise en ligne.',
            ],
            'process' => [
                [
                    'title' => 'L’idée',
                    'text' => 'Le jeu part de « Devine la chanson », auquel j’ai ajouté le multijoueur pour pouvoir y jouer en soirée. Un appareil principal sert d’écran à l’émission. Un téléphone peut servir de télécommande, pour passer les manches et valider à la main certaines réponses libres. Chaque joueur rejoint ensuite la partie en scannant un QR code ou en saisissant le code de la partie, sans créer de compte.',
                    'images' => [
                        [
                            'file' => 'menu',
                            'alt' => 'Menu de Hit or Miss en thème sombre : à gauche « Créer une partie », à lancer sur l’écran que tout le monde voit, à droite « Rejoindre une partie » avec le champ du code à six chiffres.',
                            'caption' => 'Le menu : la partie se crée sur l’écran principal, puis chaque joueur la rejoint avec son code ou en scannant le QR code.',
                        ],
                    ],
                ],
                [
                    'title' => 'Le choix de React',
                    'text' => 'Un jeu doit répondre sans délai. J’ai choisi React pour son interface réactive, qui se met à jour dès qu’un joueur répond, avec Socket.IO pour transmettre en temps réel l’état de la partie à l’écran principal, à la télécommande et à chaque téléphone.',
                ],
                [
                    'title' => 'Le logo',
                    'text' => 'Pour l’identité, je suis parti de l’univers des jeux télévisés et des quiz musicaux. Le logo associe un disque vinyle au nom « HIT — or — MISS », et son animation de chargement reprend la mécanique du jeu : un arc s’allonge d’une à trois, puis à dix secondes pendant que le disque tourne.',
                    'images' => [
                        [
                            'file' => 'chargement',
                            'alt' => 'Trois états de l’animation de chargement : autour du disque vinyle, un arc violet couvre un dixième du cercle à 1 s, près d’un tiers à 3 s, puis presque tout le cercle à 10 s.',
                            'caption' => 'L’animation de chargement : l’arc grandit comme l’extrait, d’une à trois, puis à dix secondes.',
                        ],
                    ],
                ],
                [
                    'title' => 'Trois identités',
                    'text' => 'Le logo se décline en trois identités complètes, violet, corail et menthe, chacune en version claire et sombre, avec favicons et écran de présentation. L’identité se choisit à la compilation, et les couleurs du texte et des graphiques ont été ajustées pour rester lisibles, y compris par les personnes daltoniennes.',
                    'images' => [
                        [
                            'file' => 'identites',
                            'alt' => 'Le logo Hit or Miss décliné six fois : en violet, corail et menthe, sur fond clair en haut et sur fond sombre en bas.',
                            'caption' => 'Le logo dans les trois identités, en version claire puis sombre.',
                        ],
                    ],
                ],
                [
                    'title' => 'Les présentateurs',
                    'text' => [
                        'Pour incarner l’émission, j’ai imaginé des présentateurs en 3D. Un animateur et une animatrice partagent le même squelette et les mêmes animations, avec cinq teintes de peau et cinq couleurs de cheveux, et l’un d’eux est tiré au sort à chaque partie. S’y ajoute mon sosie, modelé d’après des photos.',
                        'Ils sont entièrement générés par code, en style cartoon, sans modèle 3D à télécharger : ils saluent, présentent chaque extrait, dansent pendant l’écoute et réagissent aux bonnes comme aux mauvaises réponses. Décoratifs, ils ne font que doubler ce que le texte et les lecteurs d’écran annoncent déjà.',
                    ],
                    'images' => [
                        [
                            'file' => 'presentateurs',
                            'alt' => 'Cinq présentateurs cartoon en veste violette et nœud papillon jaune, micro en main : deux animatrices et deux animateurs aux teintes de peau et couleurs de cheveux différentes, avec au centre le sosie du créateur.',
                            'caption' => 'Quelques tirages possibles, rendus avec le code du jeu. Au centre, mon sosie.',
                        ],
                    ],
                ],
            ],
            'highlights' => [
                'Partie synchronisée en temps réel entre l’écran partagé, la télécommande de l’animateur et les téléphones des joueurs (Socket.IO).',
                'Extraits découpés pendant la partie avec ffmpeg, depuis un point aléatoire, et son lancé au même instant sur tous les téléphones.',
                'Bibliothèque musicale : import de morceaux, volume harmonisé (EBU R128), playlists publiques ou privées.',
                'Tests unitaires (Vitest) et parties de bout en bout sur plusieurs navigateurs (Playwright), avec contrôle d’accessibilité WCAG 2.2 AA.',
            ],
            'stack' => ['TypeScript', 'React', 'Fastify', 'Socket.IO', 'Three.js', 'SQLite', 'Drizzle ORM', 'ffmpeg', 'Sass', 'Playwright'],
            'url' => 'https://hit-or-miss.renardleo.fr/',
            'code' => 'https://github.com/renard-leo/hit-or-miss',
            'imageAlt' => 'Écran-titre de Hit or Miss en thème sombre : le logo au disque vinyle et l’invitation « Cliquez pour commencer », qui mène au menu.',
        ],
        [
            'slug' => 'portfolio-3d',
            'title' => 'Portfolio 3D',
            'year' => '2026',
            'role' => 'Conception et développement',
            'summary' => 'Mon portfolio sous la forme d’un bureau en 3D, à explorer au clavier, à la souris ou au toucher.',
            'description' => [
                'Au chargement, l’ordinateur s’allume puis l’écran affiche mon profil. Chaque objet du bureau ouvre un contenu : les projets sur les étagères, un terminal sur le second écran, le contact sur le smartphone, le CV à l’imprimante et l’ambiance jour ou nuit avec la lampe.',
                'Toute la scène est modélisée en code, sans modèle 3D ni texture à télécharger. Son contenu est généré à partir de ce site.',
            ],
            'process' => [
                [
                    'title' => 'L’idée',
                    'text' => 'L’idée est venue en découvrant des projets similaires, des CRM d’entreprise en 3D : ils m’ont donné envie de faire une version de mon portfolio en 3D.',
                ],
                [
                    'title' => 'Tout voir en un clic',
                    'text' => 'Le but était de pouvoir tout voir en un clic : chaque objet du bureau ouvre directement son contenu, qu’il s’agisse du profil, des projets, du parcours, du contact ou du CV.',
                ],
            ],
            'highlights' => [
                'Scène WebGL avec Three.js, et contenus des écrans en vrai HTML : sélectionnables, cliquables et lus par les lecteurs d’écran.',
                'Qualité de rendu adaptée à l’appareil, puis abaissée automatiquement si l’animation passe sous 45 images par seconde.',
                'Navigation complète au clavier, annonces vocales, mouvement réduit respecté et version statique pour les navigateurs sans WebGL.',
                'Terminal interactif avec ses propres commandes, et quelques secrets à découvrir.',
            ],
            'stack' => ['Three.js', 'GSAP', 'JavaScript', 'Vite', 'Sass'],
            'url' => 'portfolio-3d',
            'code' => 'https://github.com/renard-leo/project--portfolio-3d',
            'imageAlt' => 'Le bureau en 3D du portfolio : ordinateur, écrans, étagères d’objets, lampe et plantes.',
        ],
        [
            'slug' => 'revoicechat',
            'title' => 'ReVoiceChat',
            'year' => '2026',
            'role' => 'Site vitrine et CMS',
            'summary' => 'Le site de présentation de ReVoiceChat, une alternative open source à Discord, produit par un CMS que j’ai développé.',
            'description' => [
                'ReVoiceChat est une application de chat et d’appels vocaux open source, que chacun peut héberger sur son propre serveur. J’ai conçu et développé son site de présentation : arguments clés, partage d’écran simultané, comparatif avec Discord et Slack.',
                'Le site est généré par rl-site-builder, un CMS que j’ai créé : un backoffice React pour éditer les pages, les menus, le référencement, les médias et les couleurs, puis une compilation qui produit un site en HTML statique, mis en forme avec Tailwind CSS.',
            ],
            'process' => [
                [
                    'title' => 'Le besoin',
                    'text' => 'Il fallait un site dont les textes se modifient facilement et rapidement, et dont l’apparence puisse évoluer vite au besoin.',
                ],
                [
                    'title' => 'Un premier essai avec ChaiBuilder',
                    'text' => 'Le projet a commencé début 2026 avec un fork de [ChaiBuilder](https://github.com/chaibuilder), un constructeur visuel de sites open source. Sa mise en production a buté sur l’hébergeur, qui bloque certains ports entrants et sortants dont l’outil avait besoin : le site a donc été exporté en statique pour pouvoir être mis en ligne.',
                ],
                [
                    'title' => 'Un CMS sur mesure',
                    'text' => 'À l’automne 2026, j’ai décidé de refaire le projet avec un outil plus adapté à ce que je souhaitais, qui serve aussi de base à d’autres sites. rl-site-builder est né pour résoudre ce problème : il compile automatiquement le site statique, en reprenant d’abord l’apparence du site existant. Certains composants ont ensuite été améliorés graphiquement, sans bouleverser l’architecture.',
                ],
                [
                    'title' => 'Un backoffice hors ligne',
                    'text' => 'Le backoffice n’est pas en ligne, ce qui évite l’exploitation de ses éventuelles failles, et le site publié est entièrement statique : il n’a besoin d’aucune base de données pour fonctionner.',
                ],
            ],
            'highlights' => [
                'Backoffice React avec éditeur de texte riche (Tiptap) et aperçu en direct des pages.',
                'Site entièrement statique en production : pages dans chaque langue, sitemap, robots.txt et page 404 générés à la compilation.',
                'Données stockées avec le module SQLite intégré à Node.js, sans dépendance native à compiler.',
                'Mise en ligne automatique : GitHub Actions publie le site compilé à chaque modification.',
            ],
            'stack' => ['React', 'TypeScript', 'Node.js', 'Express', 'Tailwind CSS', 'SQLite', 'Vite', 'GitHub Actions'],
            'url' => 'https://www.revoicechat.fr/',
            'code' => null,
            'imageAlt' => 'Accueil du site de ReVoiceChat, avec un aperçu de l’application de chat.',
        ],
        [
            'slug' => 'systeme-solaire',
            'title' => 'Système solaire interactif',
            'year' => '2026',
            'role' => 'Conception et développement',
            'summary' => 'Le système solaire en 3D temps réel : on tourne autour du Soleil et on suit chaque planète.',
            'description' => [
                'Animation 3D du système solaire avec Three.js. On clique sur un astre pour le suivre et afficher sa fiche (rotation, révolution, gravité, masse…), d’après les données de la NASA.',
            ],
            'process' => [
                [
                    'title' => 'Un ancien projet',
                    'text' => 'J’en avais réalisé une première version il y a plusieurs années, en HTML, CSS et JavaScript, pour jouer avec les effets d’animation CSS de l’époque : les ombres, la lumière du Soleil, la rotation de chaque planète.',
                ],
                [
                    'title' => 'Une refonte avec l’IA',
                    'text' => 'Le but était de le refaire avec l’IA en un minimum de prompts, en gardant l’idée de départ, pour m’exercer à travailler avec elle.',
                ],
                [
                    'title' => 'Des idées en plus',
                    'text' => 'Le premier résultat était plutôt satisfaisant, puis quelques idées sont venues enrichir le projet.',
                ],
            ],
            'highlights' => [
                'Textures des planètes générées procéduralement dans des Web Workers.',
                'Les 21 principaux satellites naturels orbitent autour de leur planète.',
                'Vitesse réglable, du temps réel à une année par seconde, avec la date simulée.',
                'Navigation au clavier : 0 à 8 pour choisir un astre, ← et → pour passer au suivant, Échap pour revenir, Espace pour la pause.',
            ],
            'stack' => ['Three.js', 'JavaScript', 'Sass', 'Web Workers'],
            'url' => 'solar-system',
            'code' => 'https://github.com/renard-leo/project--animated-solar-system',
            'imageAlt' => 'Vue d’ensemble du système solaire en 3D, avec les orbites des planètes et la ceinture d’astéroïdes.',
        ],
    ],

    'experiences' => [
        [
            'company' => 'Section4',
            'description' => 'Agence de communication et web',
            'logo' => 'assets/img/logos/section4.svg',
            'location' => 'Épinal (Vosges)',
            'positions' => [
                ['title' => 'Développeur web full-stack', 'contract' => 'CDI', 'start' => '2021-09', 'end' => null],
                ['title' => 'Développeur web', 'contract' => 'CDD', 'start' => '2020-09', 'end' => '2021-09'],
            ],
            'missions' => [
                'Développement de sites vitrines sur mesure, de boutiques en ligne et d’applications métier (Symfony, Node.js, PHP, WordPress).',
                'Intégration d’API et de services tiers.',
                'Suivi des clients pendant le développement et la maintenance.',
                'Participation aux choix techniques de deux projets clients.',
            ],
        ],
        [
            'company' => 'Interaview Production',
            'description' => null,
            'logo' => 'assets/img/logos/interaview.svg',
            'location' => 'Angers (Maine-et-Loire)',
            'positions' => [
                ['title' => 'Développeur front-end', 'contract' => 'Stage', 'start' => '2020-03', 'end' => '2020-07'],
            ],
            'missions' => [
                'Développement d’un site web.',
                'Maintenance de sites et ajout de fonctionnalités.',
            ],
        ],
        [
            'company' => 'Constructeur Industriel Lambert',
            'description' => null,
            'logo' => 'assets/img/logos/constructeur-industriel-lambert.svg',
            'location' => 'Damvillers (Meuse)',
            'positions' => [
                ['title' => 'Technicien informatique', 'contract' => 'Stage', 'start' => '2018-06', 'end' => '2018-07', 'duration' => '6 semaines'],
            ],
            'missions' => [
                'Mise en place d’un ERP.',
                'Maintenance informatique.',
            ],
        ],
    ],

    'education' => [
        ['year' => '2020', 'title' => 'Licence Conception, Rédaction et Réalisation Web', 'school' => 'EiSINe', 'city' => 'Charleville-Mézières'],
        ['year' => '2019', 'title' => 'BTS Systèmes Numériques, option Informatique et Réseaux', 'school' => 'Lycée Raymond Poincaré', 'city' => 'Bar-le-Duc'],
        ['year' => '2017', 'title' => 'Baccalauréat scientifique', 'school' => 'Lycée Alfred Kastler', 'city' => 'Stenay'],
    ],

    'contact' => [
        'intro' => 'Une question ou une proposition ? Écrivez-moi, je vous réponds rapidement.',
    ],

    'legal' => [
        'host' => [
            'name' => 'Infomaniak Network SA',
            'address' => 'Rue Eugène-Marziano 25, 1227 Les Acacias (GE), Suisse',
            'url' => 'https://www.infomaniak.com',
        ],
        // Liens : [texte](#ancre-de-l-accueil), [texte](page) ou [texte](https://…).
        'publisher' => [
            'Ce site est édité par Léo Renard, particulier, qui en est aussi le directeur de la publication.',
            'Pour toute question, écrivez-moi via le [formulaire de contact](#contact).',
        ],
        'intellectualProperty' => [
            'Les textes et la photographie de ce site sont la propriété de Léo Renard. Toute reproduction, même partielle, nécessite son accord préalable.',
            'Les logos des entreprises citées appartiennent à leurs propriétaires respectifs.',
        ],
        'privacy' => [
            'form' => [
                'Les informations saisies dans le formulaire de contact (nom, adresse e-mail, objet et message) servent uniquement à vous répondre.',
                'Elles me sont transmises par e-mail : le site ne les enregistre ni en base de données ni dans un fichier. Elles sont conservées le temps de nos échanges, et au plus 3 ans après notre dernier contact.',
                'Pour limiter les abus, chaque envoi est associé à une empreinte de votre adresse IP, calculée avec une clé secrète : l’adresse elle-même n’est jamais enregistrée. Cette empreinte sert uniquement à limiter les envois à 5 messages par heure ; elle est conservée au plus 1 heure, puis effacée automatiquement.',
            ],
            'analytics' => [
                'Uniquement si vous l’acceptez, ce site utilise Google Analytics pour mesurer son audience. Les cookies _ga et _ga_* sont alors déposés pour une durée maximale de 13 mois.',
                'Si vous retirez votre accord, ces cookies sont supprimés et la mesure s’arrête.',
            ],
            'antispam' => [
                'Uniquement si vous l’acceptez, Google reCAPTCHA protège le formulaire de contact contre les envois automatisés (spam). Le service n’est chargé depuis les serveurs de Google qu’après votre accord : au moment où vous le donnez, ou lorsque vous commencez à remplir le formulaire.',
                'Juste avant l’envoi, reCAPTCHA attribue un score à votre visite ; le serveur du site le vérifie auprès de Google et refuse les envois jugés automatisés. Les [règles de confidentialité](https://policies.google.com/privacy) et les [conditions d’utilisation](https://policies.google.com/terms) de Google s’appliquent.',
                'Le formulaire de contact ne peut être envoyé qu’avec cet accord : sans lui, le bouton d’envoi reste désactivé et vous pouvez m’écrire sur [LinkedIn](' . $linkedin . '). Vous pouvez le donner depuis le formulaire, avec le bouton « Autoriser reCAPTCHA » qui conserve vos autres choix, ou via « Gestion des cookies ».',
                'D’autres protections complètent reCAPTCHA : jeton de sécurité, vérification de la provenance de l’envoi, limitation du nombre de messages.',
                'Si vous retirez votre accord alors que le service est chargé, la page est rechargée pour l’arrêter.',
            ],
            'rights' => [
                'Vous disposez d’un droit d’accès, de rectification, d’effacement et d’opposition sur vos données. Pour l’exercer, écrivez-moi via le [formulaire de contact](#contact).',
                'Vous pouvez également adresser une réclamation à la [CNIL](https://www.cnil.fr).',
            ],
        ],
        'cookies' => [
            'Sans votre accord, ce site ne dépose qu’un seul cookie, cc_cookie : il enregistre vos choix de consentement (services acceptés, date et identifiant aléatoire de votre choix). Strictement nécessaire, il n’est pas soumis à votre accord. Il est conservé 6 mois, puis votre choix vous est à nouveau demandé.',
            'Aucun cookie de mesure d’audience ni de service tiers n’est déposé sans votre accord. Le bandeau affiché lors de votre première visite propose « Tout accepter », « Tout refuser » ou « Personnaliser ». Vous pouvez modifier ou retirer votre accord à tout moment avec le bouton « Gestion des cookies », en bas de chaque page.',
            'Le site n’ouvre aucune session : le formulaire de contact est protégé par un jeton de sécurité intégré à la page, sans cookie.',
            'Vos préférences d’affichage (thème clair ou sombre, mise en pause des animations) sont enregistrées dans le stockage local de votre navigateur, sans être transmises.',
        ],
    ],
];
