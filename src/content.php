<?php

declare(strict_types=1);

$careerStart = new DateTimeImmutable('2020-09-01');
$linkedin = 'https://www.linkedin.com/in/l%C3%A9o-renard/';

return [
    'identity' => [
        'firstName' => 'Léo',
        'lastName' => 'Renard',
        'role' => 'Développeur full-stack',
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
