<?php
/**
 * Chargé en premier par chaque page (via head.php).
 * Charge les informations du site et définit les petites fonctions communes.
 */

// Toutes les heures du site (badge « Ouvert », réservations) sont celles de Paris.
date_default_timezone_set('Europe/Paris');

$site = require __DIR__ . '/../config/site.php';

/**
 * Échappe un texte avant de l'afficher dans le HTML.
 * Empêche qu'un texte contenant des balises soit interprété par le navigateur.
 */
function e(?string $texte): string
{
    return htmlspecialchars($texte ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/**
 * Construit une adresse à partir de la racine du site.
 * url('carte.php') → « /carte.php » ; url() → « / » (accueil).
 * Fonctionne aussi depuis admin/, contrairement à un lien relatif.
 */
function url(string $chemin = ''): string
{
    global $site;
    return rtrim($site['base_url'], '/') . '/' . ltrim($chemin, '/');
}

/**
 * Transforme « 01 99 00 19 52 » en « +33199001952 » pour un lien tel:.
 */
function lienTelephone(string $numero): string
{
    $chiffres = preg_replace('/\D/', '', $numero);
    return '+33' . substr($chiffres, 1);
}

/**
 * « 14:30 » → « 14h30 » ; « 12:00 » → « 12h ».
 */
function formaterHeure(string $heure): string
{
    [$h, $m] = explode(':', $heure);
    return (int) $h . 'h' . ($m === '00' ? '' : $m);
}

/**
 * Regroupe les jours consécutifs qui ont les mêmes horaires.
 *
 * Retourne par exemple :
 *   'ouvertures' => [['jours' => 'Du mardi au samedi', 'creneaux' => ['12h – 14h30', '19h – 22h30']]]
 *   'fermeture'  => 'Fermé le lundi et le dimanche'
 */
function resumerHoraires(array $horaires): array
{
    $noms = [1 => 'lundi', 'mardi', 'mercredi', 'jeudi', 'vendredi', 'samedi', 'dimanche'];
    $ouvertures = [];
    $fermes = [];
    $debut = null; // premier jour du groupe en cours

    // On va jusqu'à 8 (jour qui n'existe pas) pour clore le dernier groupe.
    for ($jour = 1; $jour <= 8; $jour++) {
        $creneaux = $horaires[$jour] ?? null;

        // Le groupe en cours s'arrête si les horaires changent.
        if ($debut !== null && $creneaux !== $horaires[$debut]) {
            $fin = $jour - 1;
            $ouvertures[] = [
                'jours'    => $debut === $fin
                    ? ucfirst($noms[$debut])
                    : 'Du ' . $noms[$debut] . ' au ' . $noms[$fin],
                'creneaux' => array_map(
                    fn ($c) => formaterHeure($c[0]) . ' – ' . formaterHeure($c[1]),
                    $horaires[$debut]
                ),
            ];
            $debut = null;
        }

        if ($creneaux === []) {
            $fermes[] = $noms[$jour];
        } elseif ($creneaux !== null && $debut === null) {
            $debut = $jour;
        }
    }

    $fermeture = '';
    if ($fermes) {
        $dernier = array_pop($fermes);
        $fermeture = 'Fermé le ' . ($fermes ? implode(', le ', $fermes) . ' et le ' : '') . $dernier;
    }

    return ['ouvertures' => $ouvertures, 'fermeture' => $fermeture];
}
