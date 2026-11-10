<?php
/**
 * Haut des pages d'administration.
 *
 * Variables attendues :
 *   $titreAdmin   titre de la page
 *   $pageAdmin    identifiant de la page pour le menu (« accueil », « ardoise »…)
 *   $admin        administrateur connecté, ou null (page de connexion)
 */
$liensAdmin = [
    'accueil'      => ['Tableau de bord', 'admin/'],
    'ardoise'      => ['Ardoise',         'admin/ardoise.php'],
    'reservations' => ['Réservations',    'admin/reservations.php'],
    'carte'        => ['Carte',           'admin/carte.php'],
];
$flash = lireFlash();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title><?= e($titreAdmin) ?> — Administration — <?= e($site['nom']) ?></title>
    <link rel="icon" href="<?= e(url('favicon.svg')) ?>" type="image/svg+xml">
    <script>document.documentElement.classList.add('js');</script>

    <link rel="stylesheet" href="<?= e(asset('assets/css/fonts.css')) ?>">
    <link rel="stylesheet" href="<?= e(asset('assets/css/variables.css')) ?>">
    <link rel="stylesheet" href="<?= e(asset('assets/css/base.css')) ?>">
    <link rel="stylesheet" href="<?= e(asset('assets/css/layout.css')) ?>">
    <link rel="stylesheet" href="<?= e(asset('assets/css/components.css')) ?>">
    <link rel="stylesheet" href="<?= e(asset('assets/css/admin.css')) ?>">
</head>
<body class="admin">
<a class="lien-evitement" href="#contenu">Aller au contenu</a>

<?php if ($admin): ?>
<header class="admin-entete">
    <div class="conteneur admin-entete__interieur">
        <p class="admin-entete__nom">
            <?= e($site['nom']) ?> <span>administration</span>
        </p>

        <nav aria-label="Administration">
            <ul class="admin-nav" role="list">
<?php foreach ($liensAdmin as $id => [$libelle, $chemin]): ?>
                <li><a href="<?= e(url($chemin)) ?>"<?= $id === $pageAdmin ? ' aria-current="page"' : '' ?>><?= e($libelle) ?></a></li>
<?php endforeach; ?>
                <li>
                    <a href="<?= e(url()) ?>" target="_blank" rel="noopener">
                        Voir le site<span class="visuellement-cache"> (nouvel onglet)</span>
                    </a>
                </li>
            </ul>
        </nav>

        <!-- Déconnexion en POST avec jeton CSRF : un simple lien (GET) pourrait
             être déclenché par une image piégée sur un autre site -->
        <form class="admin-entete__deconnexion" method="post" action="<?= e(url('admin/deconnexion.php')) ?>">
            <?= champCsrf() ?>
            <button type="submit">Déconnexion (<?= e($admin['identifiant']) ?>)</button>
        </form>
    </div>
</header>
<?php endif; ?>

<main id="contenu" class="conteneur admin-page">
<?php if ($flash): ?>
    <p class="flash flash--<?= e($flash['type']) ?>" role="status"><?= e($flash['message']) ?></p>
<?php endif; ?>
