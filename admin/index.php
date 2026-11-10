<?php
// Administration : tableau de bord (vue d'ensemble)

require_once __DIR__ . '/../includes/admin/auth.php';
require_once __DIR__ . '/../includes/admin/requetes.php';
require_once __DIR__ . '/../includes/donnees.php';

$admin = exigerConnexion();

$aujourdhui = new DateTimeImmutable('today');
$ardoise    = ardoiseDuJour();
$enAttente  = reservationsEnAttente();
$couverts   = couvertsParService($aujourdhui, 2); // aujourd'hui et demain

$titreAdmin = 'Tableau de bord';
$pageAdmin  = 'accueil';
require __DIR__ . '/../includes/admin/haut.php';
?>

<h1>Bonjour <?= e(ucfirst($admin['identifiant'])) ?></h1>
<p class="admin-sous-titre"><?= e(dateLongue($aujourdhui)) ?> · <?= e(etatOuverture($site['horaires'])['texte']) ?></p>

<div class="admin-grille">

    <!-- Couverts prévus -->
    <section class="admin-carte" aria-labelledby="titre-couverts">
        <h2 id="titre-couverts">Couverts prévus</h2>
        <table class="admin-tableau">
            <thead>
                <tr><th scope="col">Jour</th><th scope="col">Midi</th><th scope="col">Soir</th></tr>
            </thead>
            <tbody>
<?php foreach ($couverts as $date => $services): ?>
                <tr>
                    <th scope="row"><?= $date === $aujourdhui->format('Y-m-d') ? 'Aujourd\'hui' : 'Demain' ?></th>
                    <td><?= $services['midi'] ?></td>
                    <td><?= $services['soir'] ?></td>
                </tr>
<?php endforeach; ?>
            </tbody>
        </table>
        <p class="admin-note">Réservations confirmées ou en attente.</p>
    </section>

    <!-- Ardoise du jour -->
    <section class="admin-carte" aria-labelledby="titre-ardoise-jour">
        <h2 id="titre-ardoise-jour">Ardoise du jour</h2>
<?php if ($ardoise): ?>
        <p class="admin-ardoise-resume">
            <strong><?= e($ardoise['plat']) ?></strong> · <?= e(prix($ardoise['prix'])) ?>
<?php if ($ardoise['description']): ?>
            <br><span><?= e($ardoise['description']) ?></span>
<?php endif; ?>
        </p>
        <a class="bouton bouton--contour" href="<?= e(url('admin/ardoise.php?date=' . $aujourdhui->format('Y-m-d'))) ?>">Modifier</a>
<?php else: ?>
        <p>Pas encore d'ardoise aujourd'hui : le site affiche « L'ardoise est en cours d'écriture… ».</p>
        <a class="bouton" href="<?= e(url('admin/ardoise.php')) ?>">Écrire l'ardoise</a>
<?php endif; ?>
    </section>

    <!-- Demandes en attente -->
    <section class="admin-carte admin-carte--large" aria-labelledby="titre-attente">
        <h2 id="titre-attente">
            Demandes à confirmer
            <span class="admin-compteur"><?= count($enAttente) ?></span>
        </h2>
<?php if (!$enAttente): ?>
        <p>Aucune demande en attente.</p>
<?php else: ?>
        <ul class="admin-liste" role="list">
<?php foreach (array_slice($enAttente, 0, 6) as $resa): ?>
            <li>
                <strong><?= e(dateLongue(new DateTimeImmutable($resa['date_repas']))) ?>, <?= e(formaterHeure(substr($resa['heure'], 0, 5))) ?></strong>
                — <?= e($resa['nom']) ?>, <?= (int) $resa['couverts'] ?> pers.
                · <a href="tel:<?= e(lienTelephone($resa['telephone'])) ?>"><?= e($resa['telephone']) ?></a>
            </li>
<?php endforeach; ?>
        </ul>
<?php endif; ?>
        <a class="bouton bouton--contour" href="<?= e(url('admin/reservations.php')) ?>">Toutes les réservations</a>
    </section>

</div>

<?php require __DIR__ . '/../includes/admin/bas.php'; ?>
