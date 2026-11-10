<?php
// Administration : l'ardoise du plat du jour

require_once __DIR__ . '/../includes/admin/auth.php';
require_once __DIR__ . '/../includes/admin/requetes.php';
require_once __DIR__ . '/../includes/admin/validation.php';

$admin = exigerConnexion();

$aujourdhui = new DateTimeImmutable('today');

// Jour à écrire : ?date=AAAA-MM-JJ, sinon aujourd'hui
$dateDemandee = is_string($_GET['date'] ?? null) ? $_GET['date'] : '';
$dateObjet = DateTimeImmutable::createFromFormat('!Y-m-d', $dateDemandee);
$date = ($dateObjet && $dateObjet->format('Y-m-d') === $dateDemandee) ? $dateDemandee : $aujourdhui->format('Y-m-d');

$existante = ardoisePourDate($date);
$valeurs = [
    'date'        => $date,
    'plat'        => $existante['plat'] ?? '',
    'description' => $existante['description'] ?? '',
    'prix'        => prixPourChamp($existante['prix'] ?? null),
];
$erreurs = [];

/* --------------------------------------------------------------------------
   Actions : enregistrer ou effacer
   -------------------------------------------------------------------------- */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifierCsrf();
    $action = $_POST['action'] ?? '';

    if ($action === 'supprimer') {
        $dateAEffacer = is_string($_POST['date'] ?? null) ? $_POST['date'] : '';
        supprimerArdoise($dateAEffacer);
        flash('Ardoise effacée.');
        rediriger('admin/ardoise.php?date=' . urlencode($dateAEffacer));
    }

    [$valeurs, $erreurs] = verifierArdoise($_POST);

    if (!$erreurs) {
        enregistrerArdoise($valeurs);
        flash('Ardoise du ' . lcfirst(dateLongue(new DateTimeImmutable($valeurs['date']))) . ' enregistrée.');
        rediriger('admin/ardoise.php?date=' . $valeurs['date']);
    }
}

$aVenir = ardoisesAVenir(14);
$dateAffichee = DateTimeImmutable::createFromFormat('!Y-m-d', $valeurs['date']) ?: $aujourdhui;

$titreAdmin = 'Ardoise';
$pageAdmin  = 'ardoise';
require __DIR__ . '/../includes/admin/haut.php';
?>

<h1>L'ardoise</h1>
<p class="admin-sous-titre">Ce que vous écrivez ici s'affiche sur l'accueil et sur la carte, le jour choisi.</p>

<div class="admin-ardoise">

    <!-- Formulaire -->
    <section class="admin-carte" aria-labelledby="titre-formulaire-ardoise">
        <h2 id="titre-formulaire-ardoise"><?= $existante ? 'Modifier' : 'Écrire' ?> l'ardoise</h2>

        <form class="formulaire" method="post" action="<?= e(url('admin/ardoise.php?date=' . $date)) ?>" data-ardoise-formulaire novalidate>
            <?= champCsrf() ?>
            <input type="hidden" name="action" value="enregistrer">

            <div class="champ">
                <label class="champ__label" for="champ-date">Jour</label>
                <input class="champ__saisie" type="date" id="champ-date" name="date" value="<?= e($valeurs['date']) ?>"
                       required<?= attributsChamp('date', $erreurs, 'aide-date') ?>>
                <p class="champ__aide" id="aide-date">Si ce jour a déjà une ardoise, elle sera remplacée.</p>
                <?= messageErreur('date', $erreurs) ?>
            </div>

            <div class="champ">
                <label class="champ__label" for="champ-plat">Plat</label>
                <input class="champ__saisie" type="text" id="champ-plat" name="plat" value="<?= e($valeurs['plat']) ?>"
                       required maxlength="120" placeholder="Blanquette de veau à l'ancienne"<?= attributsChamp('plat', $erreurs) ?>>
                <?= messageErreur('plat', $erreurs) ?>
            </div>

            <div class="champ">
                <label class="champ__label" for="champ-description">Accompagnement <span class="champ__facultatif">(facultatif)</span></label>
                <input class="champ__saisie" type="text" id="champ-description" name="description" value="<?= e($valeurs['description']) ?>"
                       maxlength="255" placeholder="riz pilaf & champignons de Paris"<?= attributsChamp('description', $erreurs) ?>>
                <?= messageErreur('description', $erreurs) ?>
            </div>

            <div class="champ champ--court">
                <label class="champ__label" for="champ-prix">Prix (€)</label>
                <input class="champ__saisie" type="text" id="champ-prix" name="prix" value="<?= e($valeurs['prix']) ?>"
                       required inputmode="decimal" placeholder="19"<?= attributsChamp('prix', $erreurs) ?>>
                <?= messageErreur('prix', $erreurs) ?>
            </div>

            <button class="bouton" type="submit">Enregistrer l'ardoise</button>
        </form>

<?php if ($existante): ?>
        <form class="admin-action-secondaire" method="post" action="<?= e(url('admin/ardoise.php')) ?>"
              data-confirmer="Effacer l'ardoise de ce jour ? Le site affichera « en cours d'écriture ».">
            <?= champCsrf() ?>
            <input type="hidden" name="action" value="supprimer">
            <input type="hidden" name="date" value="<?= e($date) ?>">
            <button class="admin-lien-danger" type="submit">Effacer l'ardoise de ce jour</button>
        </form>
<?php endif; ?>
    </section>

    <!-- Aperçu en direct (mis à jour par admin.js pendant la frappe) -->
    <section class="admin-apercu" aria-labelledby="titre-apercu">
        <h2 id="titre-apercu" class="admin-apercu__titre">Aperçu</h2>
        <!-- aria-hidden : simple doublon visuel du formulaire, inutile à relire -->
        <div aria-hidden="true">
            <article class="ardoise" data-ardoise-apercu>
                <p class="ardoise__titre">Aujourd'hui sur l'ardoise</p>
                <p class="ardoise__date"><?= e(dateLongue($dateAffichee)) ?></p>
                <p class="ardoise__plat"><?= e($valeurs['plat'] !== '' ? $valeurs['plat'] : 'Le plat du jour') ?></p>
                <p class="ardoise__description"<?= $valeurs['description'] === '' ? ' hidden' : '' ?>><?= e($valeurs['description']) ?></p>
                <p class="ardoise__prix"><?= e($valeurs['prix'] !== '' ? $valeurs['prix'] . "\u{00A0}€" : "—\u{00A0}€") ?></p>
            </article>
        </div>
    </section>
</div>

<!-- Les 14 prochains jours -->
<section class="admin-carte" aria-labelledby="titre-a-venir">
    <h2 id="titre-a-venir">Les 14 prochains jours</h2>
    <table class="admin-tableau admin-tableau--ardoises">
        <thead>
            <tr><th scope="col">Jour</th><th scope="col">Plat</th><th scope="col">Prix</th><th scope="col"><span class="visuellement-cache">Action</span></th></tr>
        </thead>
        <tbody>
<?php for ($i = 0; $i < 14; $i++):
    $jour = $aujourdhui->modify("+$i days");
    $cle = $jour->format('Y-m-d');
    $ligne = $aVenir[$cle] ?? null;
    $ferme = empty($site['horaires'][(int) $jour->format('N')]);
?>
            <tr<?= $cle === $date ? ' class="est-selectionne"' : '' ?>>
                <th scope="row"><?= e(dateLongue($jour)) ?></th>
                <td><?= $ligne ? e($ligne['plat']) : ($ferme ? '<span class="admin-discret">fermé</span>' : '<span class="admin-discret">—</span>') ?></td>
                <td><?= $ligne ? e(prix((float) $ligne['prix'])) : '' ?></td>
                <td><a href="<?= e(url('admin/ardoise.php?date=' . $cle)) ?>"><?= $ligne ? 'Modifier' : 'Écrire' ?><span class="visuellement-cache"> l'ardoise du <?= e(lcfirst(dateLongue($jour))) ?></span></a></td>
            </tr>
<?php endfor; ?>
        </tbody>
    </table>
</section>

<?php require __DIR__ . '/../includes/admin/bas.php'; ?>
