<?php

declare(strict_types=1);

// Copier ce fichier en config/config.php et le remplir. config.php n'est jamais versionné.
return [
    'siteUrl' => 'https://renardleo.fr',

    // Affiche ou masque tout ce qui concerne la recherche d'emploi (statut, mode de travail, permis).
    'openToWork' => true,

    // Version du hero : 'studio' (plan de travail et inspecteur) ou 'classic'.
    'hero' => 'studio',

    // Clé secrète (jeton anti-CSRF, empreinte des IP). Générer avec : php -r "echo bin2hex(random_bytes(32));"
    // Vide, le formulaire de contact refuse les envois.
    'appSecret' => '',

    // Adresses des relais de confiance (proxy, répartiteur de charge) dont on lit l'en-tête X-Forwarded-For.
    // Laisser vide si REMOTE_ADDR contient déjà l'adresse IP du visiteur.
    'trustedProxies' => [],

    'googleAnalytics' => [
        'measurementId' => '',
    ],

    'recaptcha' => [
        'siteKey' => '',
        'secretKey' => '',
        'minScore' => 0.5,
    ],

    'mail' => [
        // 'smtp' : envoi authentifié avec PHPMailer (recommandé, nécessite composer install) ;
        // 'mail' : fonction mail() de PHP ; 'log' : écriture dans var/mail.log (développement).
        'transport' => 'mail',
        'to' => '',
        // Adresse existante du domaine. En SMTP chez Infomaniak, ce doit être l'adresse du compte authentifié
        // (smtp.username), sinon le message est refusé (« Sender mismatch »). Avec mail(), celle déclarée dans l'hébergement.
        'from' => 'noreply@renardleo.fr',
        // Nom affiché de l'expéditeur ; vide : « Formulaire de contact ».
        'fromName' => '',
        // Infomaniak : serveur mail.infomaniak.com, port 465 et 'ssl' (SMTPS) ou port 587 et 'tls' (STARTTLS),
        // identifiant = adresse e-mail complète, mot de passe de cette adresse (FAQ Infomaniak 576 et 468).
        'smtp' => [
            'host' => 'mail.infomaniak.com',
            'port' => 465,
            // 'ssl' (SMTPS, 465), 'tls' (STARTTLS, 587) ou '' (aucun chiffrement, serveur local uniquement).
            'encryption' => 'ssl',
            'username' => '',
            'password' => '',
            // Secondes ; au-delà, le visiteur est prévenu que le formulaire est indisponible.
            'timeout' => 10,
        ],
    ],
];
