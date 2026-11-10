<?php
/**
 * Informations du bistrot, utilisées sur tout le site.
 *
 * Ce fichier ne contient rien de secret : il est versionné dans Git.
 * Les identifiants de la base iront dans config.local.php (exclu de Git).
 */

return [
    'nom'    => 'La Mangeoire',
    'slogan' => 'Chez nous, on vient comme on est, et on repart le ventre plein.',
    'depuis' => 1952,

    // Racine du site. « / » sur la-mangeoire.test ; à changer si le site
    // est un jour hébergé dans un sous-dossier (ex. « /portfolio/la-mangeoire/ »).
    'base_url' => '/',

    // Site de démonstration (portfolio) : ardoise tournante s'il n'y en a pas
    // ce jour-là, et bandeau « aucune réservation n'est réellement prise ».
    // Mettre false pour un vrai restaurant.
    'demo' => true,

    // Pour les données structurées (Google)
    'cuisine'    => ['Française', 'Bistrot'],
    'gamme_prix' => '€€',

    'description' => 'La Mangeoire, bistrot traditionnel de Montmartre depuis 1952 : '
                   . 'zinc d\'origine, banquettes rouges et bœuf bourguignon de Josette.',

    'adresse' => [
        'rue'         => '8, rue de la Butte-aux-Peintres',
        'code_postal' => '75018',
        'ville'       => 'Paris',
    ],
    'metro' => 'Abbesses (ligne 12)',

    // Numéro fictif : la tranche 01 99 00 xx xx serait réservée par l'ARCEP
    // aux œuvres de fiction (À VÉRIFIER avant la mise en ligne).
    'telephone' => '01 99 00 19 52',
    // Le domaine .test est réservé : il ne peut appartenir à personne.
    'email'     => 'bonjour@la-mangeoire.test',

    // Horaires par jour, numérotés comme date('N') en PHP : 1 = lundi … 7 = dimanche.
    // Chaque jour contient une liste de créneaux [ouverture, fermeture].
    // Un tableau vide signifie « fermé ».
    'horaires' => [
        1 => [],
        2 => [['12:00', '14:30'], ['19:00', '22:30']],
        3 => [['12:00', '14:30'], ['19:00', '22:30']],
        4 => [['12:00', '14:30'], ['19:00', '22:30']],
        5 => [['12:00', '14:30'], ['19:00', '22:30']],
        6 => [['12:00', '14:30'], ['19:00', '22:30']],
        7 => [],
    ],

    // Liens du menu principal. « id » permet de repérer la page en cours.
    'navigation' => [
        ['id' => 'accueil',  'label' => 'Accueil',         'url' => ''],
        ['id' => 'histoire', 'label' => 'Notre histoire',  'url' => 'histoire.php'],
        ['id' => 'carte',    'label' => 'La carte',        'url' => 'carte.php'],
        ['id' => 'venir',    'label' => 'Venir nous voir', 'url' => 'venir-nous-voir.php'],
    ],
];
