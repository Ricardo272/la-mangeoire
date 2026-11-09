<?php
// Page d'erreur 404, affichée par Apache pour toute adresse inconnue (voir .htaccess).

// Indispensable : sans ce code, le navigateur et Google recevraient « 200 OK »
// et prendraient cette page d'erreur pour une vraie page du site.
http_response_code(404);

$pageTitle   = 'Page introuvable';
$pageCss     = 'erreur';
$pageNoIndex = true;

require_once __DIR__ . '/includes/head.php';
require_once __DIR__ . '/includes/header.php';
?>

<main id="contenu">
    <section class="section erreur">
        <div class="conteneur conteneur--etroit">
            <p class="surtitre surtitre--centre">Erreur 404</p>
            <h1>Cette table n'est pas dressée.</h1>
            <p class="erreur__texte">
                La page que vous cherchez n'existe pas, ou plus.
                Le zinc, lui, n'a pas bougé.
            </p>
            <hr class="filet filet--orne">
            <div class="groupe-boutons">
                <a class="bouton" href="<?= e(url()) ?>">Retour à l'accueil</a>
                <a class="bouton bouton--contour" href="<?= e(url('carte.php')) ?>">Voir la carte</a>
            </div>
        </div>
    </section>
</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
