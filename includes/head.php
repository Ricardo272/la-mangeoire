<?php
/**
 * Début de chaque page : <head> et ouverture du <body>.
 *
 * Variables que la page peut définir AVANT d'inclure ce fichier :
 *   $pageId          identifiant de la page (« accueil », « carte »…) pour le menu
 *   $pageTitle       titre de la page (sans le nom du bistrot, ajouté automatiquement)
 *   $pageDescription description pour les moteurs de recherche
 *   $pageCss         nom d'une feuille propre à la page, dans assets/css/pages/
 *   $pageNoIndex     true pour demander aux moteurs de ne pas indexer la page
 *   $pageRestaurant  true pour ajouter la fiche « Restaurant » pour Google (accueil)
 */
require_once __DIR__ . '/fonctions.php';

$pageId          = $pageId ?? '';
$pageDescription = $pageDescription ?? $site['description'];
$pageCss         = $pageCss ?? null;

// Accueil : « La Mangeoire — Bistrot à Montmartre depuis 1952 »
// Autres pages : « Notre histoire — La Mangeoire »
$titreComplet = isset($pageTitle)
    ? $pageTitle . ' — ' . $site['nom']
    : $site['nom'] . ' — Bistrot à Montmartre depuis ' . $site['depuis'];

// Adresse officielle de la page (sans paramètres) : « /index.php » devient « / »
$fichierPage = basename($_SERVER['SCRIPT_NAME']);
$urlCanonique = urlAbsolue($fichierPage === 'index.php' ? '' : $fichierPage);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($titreComplet) ?></title>
    <meta name="description" content="<?= e($pageDescription) ?>">
    <meta name="theme-color" content="#6B1E2B">
<?php if (!empty($pageNoIndex)): ?>
    <meta name="robots" content="noindex, nofollow">
<?php else: ?>
    <!-- Adresse officielle de la page, pour éviter les doublons dans Google -->
    <link rel="canonical" href="<?= e($urlCanonique) ?>">
<?php endif; ?>

    <!-- Icônes : SVG pour les navigateurs récents, PNG pour les écrans d'accueil iOS -->
    <link rel="icon" href="<?= e(url('favicon.svg')) ?>" type="image/svg+xml">
    <link rel="apple-touch-icon" href="<?= e(url('apple-touch-icon.png')) ?>">

    <!-- Open Graph : aperçu quand le lien est partagé (LinkedIn, WhatsApp, Facebook…) -->
    <meta property="og:type" content="website">
    <meta property="og:locale" content="fr_FR">
    <meta property="og:site_name" content="<?= e($site['nom']) ?>">
    <meta property="og:title" content="<?= e($titreComplet) ?>">
    <meta property="og:description" content="<?= e($pageDescription) ?>">
    <meta property="og:url" content="<?= e($urlCanonique) ?>">
    <meta property="og:image" content="<?= e(urlAbsolue('assets/img/partage.jpg')) ?>">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
    <meta property="og:image:alt" content="<?= e($site['nom'] . ' — ' . $site['slogan']) ?>">
    <meta name="twitter:card" content="summary_large_image">
<?php if (!empty($pageRestaurant)): ?>

    <!-- Fiche « Restaurant » pour Google (données structurées schema.org) -->
    <script type="application/ld+json">
<?= donneesStructurees($site) ?>

    </script>
<?php endif; ?>

    <!-- Signale au CSS que JavaScript fonctionne. Sans cette classe, le menu
         reste affiché en liste : le site marche aussi sans JavaScript. -->
    <script>document.documentElement.classList.add('js');</script>

    <!-- Polices visibles dès le premier écran : chargées en priorité -->
    <link rel="preload" href="<?= e(url('assets/fonts/dm-serif-display-latin-400-normal.woff2')) ?>" as="font" type="font/woff2" crossorigin>
    <link rel="preload" href="<?= e(url('assets/fonts/work-sans-latin-wght-normal.woff2')) ?>" as="font" type="font/woff2" crossorigin>

    <link rel="stylesheet" href="<?= e(asset('assets/css/fonts.css')) ?>">
    <link rel="stylesheet" href="<?= e(asset('assets/css/variables.css')) ?>">
    <link rel="stylesheet" href="<?= e(asset('assets/css/base.css')) ?>">
    <link rel="stylesheet" href="<?= e(asset('assets/css/layout.css')) ?>">
    <link rel="stylesheet" href="<?= e(asset('assets/css/components.css')) ?>">
<?php if ($pageCss): ?>
    <link rel="stylesheet" href="<?= e(asset('assets/css/pages/' . $pageCss . '.css')) ?>">
<?php endif; ?>
</head>
<body>
