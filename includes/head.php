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
