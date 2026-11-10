<?php
// Page d'accueil

$pageId  = 'accueil';
$pageCss = 'accueil';
$pageRestaurant = true; // fiche « Restaurant » pour Google, sur l'accueil seulement
// Pas de $pageTitle : l'accueil a un titre complet par défaut (voir head.php).

// Données de la page, lues dans la base AVANT d'afficher quoi que ce soit
require_once __DIR__ . '/includes/donnees.php';
$ardoise = ardoiseAffichee(); // null s'il n'y a pas d'ardoise aujourd'hui (hors mode démo)

require_once __DIR__ . '/includes/head.php';
require_once __DIR__ . '/includes/header.php';

$horaires = resumerHoraires($site['horaires']);
?>

<main id="contenu">

    <!-- ===================================================== Ouverture -->
    <section class="ouverture section">
        <div class="conteneur ouverture__grille">
            <div class="ouverture__texte">
                <p class="surtitre">Montmartre · Depuis <?= e((string) $site['depuis']) ?></p>
                <h1 class="ouverture__titre"><?= e($site['nom']) ?></h1>
                <p class="ouverture__slogan">« <?= e($site['slogan']) ?> »</p>
                <p class="ouverture__intro">
                    Un bistrot de quartier au pied de la Butte. Le zinc de Marcel,
                    les banquettes rouges, et la cuisine de Josette, reprise
                    aujourd'hui par son petit-fils.
                </p>
                <div class="groupe-boutons">
                    <a class="bouton" href="<?= e(url('venir-nous-voir.php#reservation')) ?>">Réserver une table</a>
                    <a class="bouton bouton--contour" href="<?= e(url('carte.php')) ?>">Voir la carte</a>
                </div>
            </div>

            <div class="ouverture__image">
                <?= image('accueil/facade.jpg', 'La terrasse de La Mangeoire : tables et chaises de bistrot alignées sur le trottoir', 960, 1200, '', true) ?>
            </div>
        </div>
    </section>

    <!-- ===================================================== Ardoise du jour -->
    <section class="section section-ardoise" aria-labelledby="titre-ardoise">
        <div class="conteneur section-ardoise__grille">
            <?php require __DIR__ . '/includes/ardoise.php'; ?>

            <div class="section-ardoise__texte">
                <p class="surtitre">Le plat du jour</p>
                <h2 id="titre-ardoise">Écrit à la craie, chaque matin</h2>
                <p>
                    Avant l'ouverture, Lucas fait un tour au marché, puis il prend
                    la craie. Un plat, un seul, tant qu'il y en a.
                </p>
                <p>Quand l'ardoise est effacée, c'est qu'il n'en reste plus.</p>
                <a class="bouton bouton--contour" href="<?= e(url('carte.php')) ?>">Toute la carte</a>
            </div>
        </div>
    </section>

    <!-- ===================================================== Ce qui n'a pas changé -->
    <section class="section" aria-labelledby="titre-inchange">
        <div class="conteneur">
            <hr class="filet filet--orne">

            <header class="entete-section entete-section--centre">
                <p class="surtitre surtitre--centre">Depuis <?= e((string) $site['depuis']) ?></p>
                <h2 id="titre-inchange">Ce qui n'a pas changé</h2>
            </header>

            <!-- Liste numérotée : les chiffres romains sont générés par le CSS -->
            <ol class="inchange" role="list">
                <li class="inchange__element">
                    <h3>Le zinc d'origine</h3>
                    <p>
                        Posé en 1952, poli chaque soir. Marcel y servait le petit
                        blanc ; on y prend toujours le café debout.
                    </p>
                </li>
                <li class="inchange__element">
                    <h3>Les banquettes rouges</h3>
                    <p>
                        Moleskine rouge et clous dorés, un peu creusées là où les
                        habitués s'assoient depuis des années.
                    </p>
                </li>
                <li class="inchange__element">
                    <h3>Le bourguignon de Josette</h3>
                    <p>
                        Sa recette, notée au crayon dans un cahier d'écolier.
                        Lucas la suit à la lettre.
                    </p>
                </li>
            </ol>
        </div>
    </section>

    <!-- ===================================================== Trois générations -->
    <section class="section section-generations" aria-labelledby="titre-generations">
        <div class="conteneur">
            <header class="entete-section entete-section--centre">
                <p class="surtitre surtitre--centre">Une histoire de famille</p>
                <h2 id="titre-generations">Trois générations derrière le même comptoir</h2>
            </header>

            <ol class="generations" role="list">
                <li class="generations__etape">
                    <p class="generations__annee">1952</p>
                    <h3>Marcel &amp; Josette</h3>
                    <p>
                        Ils ouvrent le bistrot. Lui au zinc, elle aux fourneaux ;
                        les peintres de la Butte en font leur cantine.
                    </p>
                </li>
                <li class="generations__etape">
                    <p class="generations__annee">1985</p>
                    <h3>Bernard</h3>
                    <p>
                        Leur fils reprend la maison et ne touche à rien : ni au zinc,
                        ni aux banquettes, ni à l'ardoise.
                    </p>
                </li>
                <li class="generations__etape">
                    <p class="generations__annee">Aujourd'hui</p>
                    <h3>Lucas</h3>
                    <p>
                        Le petit-fils revient après dix ans dans de grandes maisons.
                        Il garde l'âme du lieu, et ajoute en cuisine quelques
                        touches à lui.
                    </p>
                </li>
            </ol>

            <p class="section-generations__lien">
                <a class="bouton bouton--contour" href="<?= e(url('histoire.php')) ?>">Lire notre histoire</a>
            </p>
        </div>
    </section>

    <!-- ===================================================== Bandeau pratique -->
    <section class="bandeau-pratique section" aria-labelledby="titre-pratique">
        <div class="conteneur bandeau-pratique__grille">
            <div>
                <h2 id="titre-pratique">Poussez la porte</h2>
                <p>
                    <?= e($site['adresse']['rue']) ?>,
                    <?= e($site['adresse']['code_postal'] . ' ' . $site['adresse']['ville']) ?><br>
                    Métro <?= e($site['metro']) ?>
                </p>
            </div>

            <div>
                <?php require __DIR__ . '/includes/badge-ouverture.php'; ?>
                <ul class="bandeau-pratique__horaires" role="list">
<?php foreach ($horaires['ouvertures'] as $ligne): ?>
                    <li><strong><?= e($ligne['jours']) ?></strong> : <?= e(implode(' et ', $ligne['creneaux'])) ?></li>
<?php endforeach; ?>
<?php if ($horaires['fermeture']): ?>
                    <li><?= e($horaires['fermeture']) ?></li>
<?php endif; ?>
                </ul>
                <div class="groupe-boutons">
                    <a class="bouton bouton--clair" href="<?= e(url('venir-nous-voir.php')) ?>">Venir nous voir</a>
                    <a class="bandeau-pratique__tel" href="tel:<?= e(lienTelephone($site['telephone'])) ?>"><?= e($site['telephone']) ?></a>
                </div>
            </div>
        </div>
    </section>

</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
