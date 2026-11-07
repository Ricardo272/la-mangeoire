<?php
// Page d'accueil — version minimale de la phase 1, pour tester les fondations.
// Le vrai contenu arrive en phase 3.

$pageId  = 'accueil';
$pageCss = 'accueil';
// Pas de $pageTitle : l'accueil a un titre complet par défaut (voir head.php).

require_once __DIR__ . '/includes/head.php';
require_once __DIR__ . '/includes/header.php';
?>

<main id="contenu" class="conteneur">
    <h1><?= e($site['nom']) ?></h1>
    <p class="slogan">« <?= e($site['slogan']) ?> »</p>

    <!-- Échantillon temporaire pour vérifier les typographies et les couleurs -->
    <section class="echantillon" aria-labelledby="echantillon-titre">
        <h2 id="echantillon-titre">Un titre de section</h2>
        <p>
            Depuis 1952, le zinc n'a pas bougé. Les banquettes en moleskine rouge
            non plus, et le bœuf bourguignon de Josette reste la fierté de la rue.
            <a href="<?= e(url('histoire.php')) ?>">Lire notre histoire</a>.
        </p>
        <h3>Un sous-titre</h3>
        <p class="echantillon__doux">Texte secondaire, un peu plus doux.</p>
    </section>
</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
