<?php
// Page « Notre histoire »

$pageId          = 'histoire';
$pageTitle       = 'Notre histoire';
$pageDescription = 'De Marcel et Josette en 1952 à Lucas aujourd\'hui : '
                 . 'trois générations derrière le même zinc, à Montmartre.';
$pageCss         = 'histoire';

require_once __DIR__ . '/includes/head.php';
require_once __DIR__ . '/includes/header.php';
?>

<main id="contenu">

    <!-- ===================================================== Introduction -->
    <section class="section histoire-intro">
        <div class="conteneur conteneur--etroit">
            <p class="surtitre surtitre--centre">Notre histoire</p>
            <h1>Plus de soixante-dix ans au même comptoir</h1>
            <p class="histoire-intro__chapeau">
                En 1952, Marcel et Josette Duval poussent pour la première fois la porte
                du 8, rue de la Butte-aux-Peintres. Trois générations plus tard, le zinc
                est le même, les banquettes aussi, et on y mange toujours le bourguignon
                de Josette.
            </p>
        </div>
    </section>

    <!-- ===================================================== Frise -->
    <section class="section histoire-frise" aria-label="Les grandes étapes">
        <div class="conteneur">
            <ol class="frise" role="list">

                <!-- 1952 -->
                <li class="frise__etape">
                    <p class="frise__jalon">1952</p>
                    <div class="frise__corps">
                        <div class="frise__texte">
                            <h2>Marcel au zinc, Josette aux fourneaux</h2>
                            <p>
                                Marcel tient le comptoir comme on tient une conversation :
                                il connaît le prénom de chacun, et le verre qui va avec.
                                En cuisine, Josette fait tout elle-même, du pot-au-feu du
                                lundi au bourguignon du vendredi, qui devient vite la
                                fierté de la rue.
                            </p>
                            <p>
                                La salle est petite, la porte toujours ouverte. On y vient
                                pour manger, mais surtout pour ne pas rester seul.
                            </p>
                        </div>
                        <div class="frise__photos">
                            <figure class="photo-ancienne">
                                <?= image('histoire/1952-zinc.jpg', 'Marcel derrière le zinc, un torchon sur l\'épaule', 1200, 900) ?>
                                <figcaption>Marcel au zinc, vers 1953</figcaption>
                            </figure>
                            <figure class="photo-ancienne">
                                <?= image('histoire/1952-josette.jpg', 'Josette devant ses fourneaux', 900, 1200) ?>
                                <figcaption>Josette dans sa cuisine, vers 1955</figcaption>
                            </figure>
                        </div>
                    </div>
                </li>

                <!-- Années 50 : les peintres -->
                <li class="frise__etape">
                    <p class="frise__jalon">Les années 50</p>
                    <div class="frise__corps">
                        <div class="frise__texte">
                            <h2>La cantine de la Butte</h2>
                            <p>
                                Montmartre est encore un village de peintres et de musiciens.
                                Beaucoup n'ont pas de quoi payer tous les jours : Josette tient
                                un carnet, Marcel ferme les yeux. Certains finissent par régler
                                leur ardoise autrement…
                            </p>
                            <p>
                                Quelques-unes de ces toiles sont encore accrochées aux murs,
                                à l'endroit exact où Marcel les avait pendues.
                            </p>
                        </div>
                    </div>

                    <div class="toiles">
                        <h3 class="toiles__titre">Les toiles des habitués</h3>
                        <ul class="toiles__liste" role="list">
                            <li>
                                <figure class="toile">
                                    <?= image('toiles/zinc-un-matin.jpg', 'Tableau : le zinc de La Mangeoire au petit matin', 800, 1000) ?>
                                    <figcaption><cite>Le Zinc, un matin</cite><br>Paulette Verdier, 1954</figcaption>
                                </figure>
                            </li>
                            <li>
                                <figure class="toile">
                                    <?= image('toiles/butte-sous-la-neige.jpg', 'Tableau : la rue de la Butte-aux-Peintres sous la neige', 800, 1000) ?>
                                    <figcaption><cite>La Butte sous la neige</cite><br>Gaston Brévannes, 1956</figcaption>
                                </figure>
                            </li>
                            <li>
                                <figure class="toile">
                                    <?= image('toiles/josette-au-tablier.jpg', 'Tableau : portrait de Josette en tablier bleu', 800, 1000) ?>
                                    <figcaption><cite>Josette au tablier bleu</cite><br>Jules Ferrandou, 1957</figcaption>
                                </figure>
                            </li>
                            <li>
                                <figure class="toile">
                                    <?= image('toiles/nature-morte-au-litron.jpg', 'Tableau : nature morte avec une bouteille de vin et du pain', 800, 1000) ?>
                                    <figcaption><cite>Nature morte au litron</cite><br>Signé « R. », jamais identifié</figcaption>
                                </figure>
                            </li>
                        </ul>
                    </div>
                </li>

                <!-- 1985 -->
                <li class="frise__etape">
                    <p class="frise__jalon">1985</p>
                    <div class="frise__corps">
                        <div class="frise__texte">
                            <h2>Bernard ne touche à rien</h2>
                            <p>
                                Marcel raccroche son tablier. Son fils Bernard, qui a grandi
                                entre la cave et la salle, reprend la maison avec une seule
                                règle : ne rien changer.
                            </p>
                            <p>
                                Le zinc d'origine, les banquettes en moleskine rouge, l'ardoise
                                du plat du jour : tout reste à sa place. Les habitués aussi.
                                Pendant près de quarante ans, Bernard tient la maison comme ses
                                parents l'avaient laissée, et c'est exactement ce qu'on lui
                                demandait.
                            </p>
                        </div>
                        <div class="frise__photos">
                            <figure class="photo-ancienne">
                                <?= image('histoire/1985-bernard.jpg', 'Bernard accoudé au comptoir', 1200, 900) ?>
                                <figcaption>Bernard au comptoir, 1985</figcaption>
                            </figure>
                        </div>
                    </div>
                </li>

                <!-- Aujourd'hui -->
                <li class="frise__etape">
                    <p class="frise__jalon">Aujourd'hui</p>
                    <div class="frise__corps">
                        <div class="frise__texte">
                            <h2>Lucas revient à la maison</h2>
                            <p>
                                Lucas a grandi sur les banquettes rouges, ses devoirs étalés
                                entre deux cafés. À dix-neuf ans, il part apprendre ailleurs :
                                dix ans dans de grandes maisons, de brigade en brigade.
                            </p>
                            <p>
                                Aujourd'hui, à 29 ans, il reprend le bistrot de son grand-père.
                                La salle ne bouge pas. En cuisine, les recettes de Josette
                                restent à la carte, et Lucas y ajoute, sans bruit, quelques
                                assiettes à lui.
                            </p>

                            <blockquote class="citation encadre">
                                <p>
                                    « Dans les grandes maisons, j'ai appris à faire compliqué.
                                    Ici, j'apprends à faire simple, comme ma grand-mère.
                                    Le bourguignon, je n'y touche pas : il est parfait comme il est. »
                                </p>
                                <footer>Lucas</footer>
                            </blockquote>
                        </div>
                        <div class="frise__photos">
                            <!-- Aujourd'hui : noir et blanc plutôt que sépia -->
                            <figure class="photo-ancienne photo-ancienne--nb">
                                <?= image('histoire/aujourdhui-lucas.jpg', 'Lucas en tablier, dressant une assiette en cuisine', 900, 1200) ?>
                                <figcaption>Lucas en cuisine, aujourd'hui</figcaption>
                            </figure>
                        </div>
                    </div>
                </li>

            </ol>
        </div>
    </section>

    <!-- ===================================================== Conclusion -->
    <section class="section histoire-suite" aria-labelledby="titre-suite">
        <div class="conteneur conteneur--etroit">
            <hr class="filet filet--orne">
            <h2 id="titre-suite">Venez goûter la suite</h2>
            <p>
                Le zinc vous attend, la banquette aussi. Il ne manque plus que vous.
            </p>
            <div class="groupe-boutons">
                <a class="bouton" href="<?= e(url('carte.php')) ?>">Découvrir la carte</a>
                <a class="bouton bouton--contour" href="<?= e(url('venir-nous-voir.php#reservation')) ?>">Réserver une table</a>
            </div>
        </div>
    </section>

</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
