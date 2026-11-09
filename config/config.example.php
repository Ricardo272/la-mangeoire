<?php
/**
 * Modèle de configuration locale.
 *
 * Copier ce fichier en « config.local.php » (même dossier) et y mettre les vrais
 * identifiants. config.local.php est exclu de Git : le mot de passe ne doit
 * jamais se retrouver dans le dépôt.
 */

return [
    'db' => [
        'hote'         => 'localhost',
        'port'         => 3306,
        'base'         => 'la_mangeoire',
        'utilisateur'  => 'la_mangeoire',
        'mot_de_passe' => 'CHANGER_CE_MOT_DE_PASSE', // le même que dans sql/creation-base.sql
    ],
];
