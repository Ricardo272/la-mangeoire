<?php
// Page « La carte »

$pageId          = 'carte';
$pageTitle       = 'La carte';
$pageDescription = 'La carte de La Mangeoire : les classiques de Josette, quelques idées de Lucas, '
                 . 'l\'ardoise du jour et la formule du midi.';
$pageCss         = 'carte';

// Données de la page
require_once __DIR__ . '/includes/donnees.php';
$ardoise       = ardoiseAffichee();
$ardoiseGrande = true;
$carte         = carte();     // null si la base ne répond pas
$formules      = formules();

// Libellés des mentions sur les plats
$origines = [
    'josette' => 'Recette de Josette',
    'lucas'   => 'Touche de Lucas',
];

require_once __DIR__ . '/includes/head.php';
require_once __DIR__ . '/includes/header.php';
?>

<main id="contenu">

    <!-- ===================================================== Introduction -->
    <section class="section carte-intro">
        <div class="conteneur conteneur--etroit">
            <p class="surtitre surtitre--centre">La carte</p>
            <h1>Les classiques de Josette, quelques idées de Lucas</h1>
            <p class="carte-intro__chapeau">
                La carte change peu, et c'est voulu. Les plats de Josette n'ont pas bougé
                depuis 1952 ; Lucas y glisse quelques assiettes à lui, au fil des saisons.
            </p>
            <!-- Sur papier seulement : où nous trouver -->
            <p class="impression-seulement carte-impression">
                <?= e($site['nom']) ?> · <?= e($site['adresse']['rue']) ?>, <?= e($site['adresse']['code_postal'] . ' ' . $site['adresse']['ville']) ?> · <?= e($site['telephone']) ?>
            </p>
        </div>
    </section>

    <!-- ===================================================== Ardoise + formule -->
    <section class="section carte-du-jour" aria-label="Aujourd'hui">
        <div class="conteneur carte-du-jour__grille">
            <?php require __DIR__ . '/includes/ardoise.php'; ?>

            <div class="carte-du-jour__cote">
<?php foreach ($formules as $formule): ?>
                <div class="encadre formule">
                    <h2 class="formule__nom"><?= e($formule['nom']) ?></h2>
                    <p class="formule__description"><?= e($formule['description']) ?></p>
                    <p class="formule__prix"><?= e(prix($formule['prix'])) ?></p>
<?php if ($formule['conditions']): ?>
                    <p class="formule__conditions"><?= e($formule['conditions']) ?></p>
<?php endif; ?>
                </div>
<?php endforeach; ?>

                <!-- Légende des mentions -->
                <dl class="legende">
                    <div>
                        <dt class="mention mention--josette"><?= e($origines['josette']) ?></dt>
                        <dd>la recette d'origine, inchangée depuis 1952</dd>
                    </div>
                    <div>
                        <dt class="mention mention--lucas"><?= e($origines['lucas']) ?></dt>
                        <dd>les créations du petit-fils</dd>
                    </div>
                </dl>
            </div>
        </div>
    </section>

<?php if ($carte === null): ?>
    <!-- ===================================================== Base indisponible -->
    <section class="section">
        <div class="conteneur conteneur--etroit">
            <div class="encadre carte-indisponible">
                <h2>La carte fait une pause</h2>
                <p>
                    Impossible de l'afficher pour le moment. Appelez-nous au
                    <a href="tel:<?= e(lienTelephone($site['telephone'])) ?>"><?= e($site['telephone']) ?></a>,
                    on vous la lira avec plaisir.
                </p>
            </div>
        </div>
    </section>
<?php else: ?>

    <!-- ===================================================== Rubriques -->
    <!-- Barre qui reste en haut de l'écran pendant le défilement (position: sticky) -->
    <nav class="carte-rubriques" aria-label="Rubriques de la carte">
        <ul class="conteneur carte-rubriques__liste" role="list">
<?php foreach ($carte as $rubrique): ?>
            <li><a href="#<?= e($rubrique['slug']) ?>"><?= e($rubrique['nom']) ?></a></li>
<?php endforeach; ?>
        </ul>
    </nav>

    <div class="conteneur conteneur--etroit carte-contenu">
<?php foreach ($carte as $rubrique): ?>
        <section class="carte-rubrique" id="<?= e($rubrique['slug']) ?>" aria-labelledby="titre-<?= e($rubrique['slug']) ?>">
            <h2 class="carte-rubrique__titre" id="titre-<?= e($rubrique['slug']) ?>"><?= e($rubrique['nom']) ?></h2>
            <hr class="filet filet--orne">

            <ul class="plats" role="list">
<?php foreach ($rubrique['plats'] as $plat): ?>
                <li class="plat">
                    <!-- Nom ····· prix : les pointillés remplissent l'espace entre les deux -->
                    <div class="plat__ligne">
                        <h3 class="plat__nom"><?= e($plat['nom']) ?></h3>
                        <span class="plat__points" aria-hidden="true"></span>
                        <p class="plat__prix">
<?php if ($plat['prix_bouteille'] !== null): ?>
                            <?= e(prix($plat['prix'])) ?> <small>le verre</small><br>
                            <?= e(prix($plat['prix_bouteille'])) ?> <small>la bouteille</small>
<?php else: ?>
                            <?= e(prix($plat['prix'])) ?>
<?php endif; ?>
                        </p>
                    </div>
<?php if ($plat['description']): ?>
                    <p class="plat__description"><?= e($plat['description']) ?></p>
<?php endif; ?>
<?php if ($plat['origine']): ?>
                    <p class="mention mention--<?= e($plat['origine']) ?>"><?= e($origines[$plat['origine']]) ?></p>
<?php endif; ?>
                </li>
<?php endforeach; ?>
            </ul>
        </section>
<?php endforeach; ?>

        <footer class="carte-notes">
            <p>Prix nets, service compris.</p>
            <p>
                Allergènes : la liste des allergènes présents dans chaque plat est
                disponible sur simple demande auprès de l'équipe.
            </p>
            <button class="bouton bouton--contour js-seulement" type="button" data-imprimer>Imprimer la carte</button>
        </footer>
    </div>
<?php endif; ?>

    <!-- ===================================================== Réserver -->
    <section class="section carte-reserver">
        <div class="conteneur conteneur--etroit">
            <hr class="filet filet--orne">
            <h2>Une table vous attend</h2>
            <div class="groupe-boutons">
                <a class="bouton" href="<?= e(url('venir-nous-voir.php#reservation')) ?>">Réserver une table</a>
            </div>
        </div>
    </section>

</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
