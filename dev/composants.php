<?php
// Page de démonstration des composants (outil de développement).
// Non indexée par les moteurs de recherche. À retirer avant la mise en ligne.

$pageTitle   = 'Composants';
$pageCss     = 'composants';
$pageNoIndex = true;

require_once __DIR__ . '/../includes/head.php';
require_once __DIR__ . '/../includes/header.php';
?>

<main id="contenu" class="conteneur dev">
    <p class="surtitre">Outil de développement</p>
    <h1>Composants</h1>
    <p>Chaque composant du site, dans toutes ses variantes. Les classes CSS sont indiquées sous chaque exemple.</p>

    <!-- ===================================================== -->
    <section class="dev__section" aria-labelledby="t-filets">
        <h2 id="t-filets">Filets dorés</h2>

        <hr class="filet">
        <p class="dev__code">hr.filet</p>

        <hr class="filet filet--orne">
        <p class="dev__code">hr.filet.filet--orne</p>

        <hr class="filet filet--court">
        <p class="dev__code">hr.filet.filet--court</p>
    </section>

    <!-- ===================================================== -->
    <section class="dev__section" aria-labelledby="t-surtitre">
        <h2 id="t-surtitre">Surtitre</h2>

        <p class="surtitre">Depuis 1952</p>
        <h3>Un titre précédé d'un surtitre</h3>
        <p class="dev__code">.surtitre</p>

        <p class="surtitre surtitre--centre">Depuis 1952</p>
        <h3 class="dev__centre">Un titre centré</h3>
        <p class="dev__code dev__centre">.surtitre.surtitre--centre</p>
    </section>

    <!-- ===================================================== -->
    <section class="dev__section" aria-labelledby="t-encadre">
        <h2 id="t-encadre">Encadré à double filet</h2>

        <div class="encadre">
            <h3>Formule du midi</h3>
            <p>Entrée + plat ou plat + dessert : 21 €</p>
        </div>
        <p class="dev__code">.encadre</p>
    </section>

    <!-- ===================================================== -->
    <section class="dev__section" aria-labelledby="t-boutons">
        <h2 id="t-boutons">Boutons</h2>

        <div class="groupe-boutons">
            <a class="bouton" href="#">Réserver une table</a>
            <a class="bouton bouton--contour" href="#">Voir la carte</a>
            <button class="bouton" type="button">Un vrai &lt;button&gt;</button>
        </div>
        <p class="dev__code">.bouton · .bouton.bouton--contour · .groupe-boutons</p>

        <div class="dev__fond-noir">
            <div class="groupe-boutons">
                <a class="bouton bouton--clair" href="#">Sur fond noir</a>
            </div>
            <p class="dev__code">.bouton.bouton--clair</p>
        </div>
    </section>

    <!-- ===================================================== -->
    <section class="dev__section" aria-labelledby="t-ardoise">
        <h2 id="t-ardoise">Ardoise du plat du jour</h2>

        <div class="dev__grille">
            <div>
                <article class="ardoise" aria-labelledby="ardoise-demo">
                    <h3 class="ardoise__titre" id="ardoise-demo">Aujourd'hui sur l'ardoise</h3>
                    <p class="ardoise__date"><time datetime="2026-11-07">Samedi 7 novembre</time></p>
                    <p class="ardoise__plat">Blanquette de veau à l'ancienne</p>
                    <p class="ardoise__description">riz pilaf &amp; champignons de Paris</p>
                    <p class="ardoise__prix">19&nbsp;€</p>
                </article>
                <p class="dev__code dev__centre">.ardoise</p>
            </div>

            <div>
                <article class="ardoise" aria-labelledby="ardoise-vide">
                    <h3 class="ardoise__titre" id="ardoise-vide">Aujourd'hui sur l'ardoise</h3>
                    <p class="ardoise__message">L'ardoise est en cours d'écriture…</p>
                </article>
                <p class="dev__code dev__centre">.ardoise + .ardoise__message (pas d'ardoise ce jour-là)</p>
            </div>
        </div>

        <article class="ardoise ardoise--grande" aria-labelledby="ardoise-grande">
            <h3 class="ardoise__titre" id="ardoise-grande">Aujourd'hui sur l'ardoise</h3>
            <p class="ardoise__date"><time datetime="2026-11-07">Samedi 7 novembre</time></p>
            <p class="ardoise__plat">Bœuf bourguignon de Josette</p>
            <p class="ardoise__description">mijoté sept heures, pommes vapeur</p>
            <p class="ardoise__prix">22&nbsp;€</p>
        </article>
        <p class="dev__code dev__centre">.ardoise.ardoise--grande</p>
    </section>

    <!-- ===================================================== -->
    <section class="dev__section" aria-labelledby="t-images">
        <h2 id="t-images">Images temporaires</h2>
        <p>Générées par <code>image()</code> tant que le fichier n'existe pas dans <code>assets/img/</code>.</p>

        <div class="dev__grille">
            <figure>
                <?= image('histoire/1952-zinc.jpg', 'Marcel derrière le zinc, 1952', 1200, 900) ?>
                <figcaption class="dev__code">Paysage 4:3</figcaption>
            </figure>
            <figure>
                <?= image('histoire/josette-cuisine.jpg', 'Josette dans sa cuisine', 900, 1200) ?>
                <figcaption class="dev__code">Portrait 3:4</figcaption>
            </figure>
        </div>
    </section>
</main>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
