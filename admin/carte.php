<?php
// Administration : la carte (plats et formule)

require_once __DIR__ . '/../includes/admin/auth.php';
require_once __DIR__ . '/../includes/admin/requetes.php';
require_once __DIR__ . '/../includes/admin/validation.php';

$admin = exigerConnexion();

$categories = categories();
$origines = ['' => 'Aucune', 'josette' => 'Recette de Josette', 'lucas' => 'Touche de Lucas'];

/* --------------------------------------------------------------------------
   Quel formulaire afficher ?
     ?modifier=12   modifier le plat 12
     ?ajouter=3     ajouter un plat dans la rubrique 3
   -------------------------------------------------------------------------- */
$idModifie   = filter_var($_GET['modifier'] ?? '', FILTER_VALIDATE_INT) ?: null;
$platModifie = $idModifie ? platParId($idModifie) : null;
$ajoutDans   = filter_var($_GET['ajouter'] ?? '', FILTER_VALIDATE_INT) ?: null;

$valeursPlat = null; // null = pas de formulaire de plat affiché
if ($platModifie) {
    $valeursPlat = [
        'categorie_id'   => (string) $platModifie['categorie_id'],
        'nom'            => $platModifie['nom'],
        'description'    => $platModifie['description'] ?? '',
        'prix'           => prixPourChamp($platModifie['prix']),
        'prix_bouteille' => prixPourChamp($platModifie['prix_bouteille']),
        'origine'        => $platModifie['origine'] ?? '',
        'ordre'          => (string) $platModifie['ordre'],
        'visible'        => (bool) $platModifie['visible'],
    ];
} elseif ($ajoutDans) {
    $valeursPlat = [
        'categorie_id' => (string) $ajoutDans, 'nom' => '', 'description' => '', 'prix' => '',
        'prix_bouteille' => '', 'origine' => '', 'ordre' => '0', 'visible' => true,
    ];
}
$erreursPlat = [];
$erreursFormule = [];
$valeursFormule = null;

/* --------------------------------------------------------------------------
   Actions
   -------------------------------------------------------------------------- */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifierCsrf();
    $action = $_POST['action'] ?? '';
    $id = filter_var($_POST['id'] ?? '', FILTER_VALIDATE_INT) ?: null;

    switch ($action) {
        case 'enregistrer_plat':
            [$valeursPlat, $erreursPlat] = verifierPlat($_POST, $categories);
            if (!$erreursPlat) {
                enregistrerPlat($valeursPlat, $id);
                flash(($id ? 'Plat modifié : ' : 'Plat ajouté : ') . $valeursPlat['nom'] . '.');
                rediriger('admin/carte.php#plat-' . ($id ?? 'fin'));
            }
            $idModifie = $id; // on réaffiche le formulaire avec les erreurs
            break;

        case 'visibilite':
            if ($id && ($plat = platParId($id))) {
                basculerVisibilitePlat($id);
                flash(($plat['visible'] ? 'Plat masqué : ' : 'Plat de nouveau visible : ') . $plat['nom'] . '.');
            }
            rediriger('admin/carte.php#plat-' . $id);

        case 'supprimer_plat':
            if ($id && ($plat = platParId($id))) {
                supprimerPlat($id);
                flash('Plat supprimé : ' . $plat['nom'] . '.');
            }
            rediriger('admin/carte.php');

        case 'enregistrer_formule':
            [$valeursFormule, $erreursFormule] = verifierFormule($_POST);
            if (!$erreursFormule && $id) {
                enregistrerFormule($id, $valeursFormule);
                flash('Formule enregistrée.');
                rediriger('admin/carte.php#formules');
            }
            break;
    }
}

$parCategorie = platsParCategorie();
$formules = formulesAdmin();

$titreAdmin = 'Carte';
$pageAdmin  = 'carte';
require __DIR__ . '/../includes/admin/haut.php';
?>

<h1>La carte</h1>
<p class="admin-sous-titre">
    Un plat <strong>masqué</strong> disparaît du site sans être supprimé : pratique pour un plat de saison.
</p>

<?php if ($valeursPlat !== null): ?>
<!-- ===================================================== Formulaire d'un plat -->
<section class="admin-carte admin-carte--formulaire" id="formulaire-plat" aria-labelledby="titre-formulaire-plat">
    <h2 id="titre-formulaire-plat"><?= $idModifie ? 'Modifier un plat' : 'Ajouter un plat' ?></h2>

    <form class="formulaire" method="post" action="<?= e(url('admin/carte.php' . ($idModifie ? '?modifier=' . $idModifie : '?ajouter=' . $valeursPlat['categorie_id']))) ?>" novalidate>
        <?= champCsrf() ?>
        <input type="hidden" name="action" value="enregistrer_plat">
<?php if ($idModifie): ?>
        <input type="hidden" name="id" value="<?= (int) $idModifie ?>">
<?php endif; ?>

        <div class="formulaire__ligne">
            <div class="champ">
                <label class="champ__label" for="plat-nom">Nom</label>
                <input class="champ__saisie" type="text" id="plat-nom" name="nom" value="<?= e($valeursPlat['nom']) ?>"
                       required maxlength="120"<?= attributsChamp('nom', $erreursPlat) ?>>
                <?= messageErreur('nom', $erreursPlat) ?>
            </div>
            <div class="champ">
                <label class="champ__label" for="plat-categorie">Rubrique</label>
                <select class="champ__saisie" id="plat-categorie" name="categorie_id"<?= attributsChamp('categorie_id', $erreursPlat) ?>>
<?php foreach ($categories as $categorie): ?>
                    <option value="<?= (int) $categorie['id'] ?>"<?= (string) $categorie['id'] === $valeursPlat['categorie_id'] ? ' selected' : '' ?>><?= e($categorie['nom']) ?></option>
<?php endforeach; ?>
                </select>
                <?= messageErreur('categorie_id', $erreursPlat) ?>
            </div>
        </div>

        <div class="champ">
            <label class="champ__label" for="plat-description">Description <span class="champ__facultatif">(facultatif)</span></label>
            <input class="champ__saisie" type="text" id="plat-description" name="description" value="<?= e($valeursPlat['description']) ?>"
                   maxlength="255"<?= attributsChamp('description', $erreursPlat) ?>>
            <?= messageErreur('description', $erreursPlat) ?>
        </div>

        <div class="formulaire__ligne">
            <div class="champ">
                <label class="champ__label" for="plat-prix">Prix (€)</label>
                <input class="champ__saisie" type="text" id="plat-prix" name="prix" value="<?= e($valeursPlat['prix']) ?>"
                       required inputmode="decimal"<?= attributsChamp('prix', $erreursPlat, 'aide-prix') ?>>
                <p class="champ__aide" id="aide-prix">Pour un vin : le prix du verre.</p>
                <?= messageErreur('prix', $erreursPlat) ?>
            </div>
            <div class="champ">
                <label class="champ__label" for="plat-bouteille">Prix bouteille <span class="champ__facultatif">(vins)</span></label>
                <input class="champ__saisie" type="text" id="plat-bouteille" name="prix_bouteille" value="<?= e($valeursPlat['prix_bouteille']) ?>"
                       inputmode="decimal"<?= attributsChamp('prix_bouteille', $erreursPlat) ?>>
                <?= messageErreur('prix_bouteille', $erreursPlat) ?>
            </div>
            <div class="champ">
                <label class="champ__label" for="plat-origine">Mention</label>
                <select class="champ__saisie" id="plat-origine" name="origine"<?= attributsChamp('origine', $erreursPlat) ?>>
<?php foreach ($origines as $valeur => $libelle): ?>
                    <option value="<?= e($valeur) ?>"<?= $valeur === $valeursPlat['origine'] ? ' selected' : '' ?>><?= e($libelle) ?></option>
<?php endforeach; ?>
                </select>
                <?= messageErreur('origine', $erreursPlat) ?>
            </div>
            <div class="champ">
                <label class="champ__label" for="plat-ordre">Ordre</label>
                <input class="champ__saisie" type="number" id="plat-ordre" name="ordre" value="<?= e($valeursPlat['ordre']) ?>"
                       min="0" max="999"<?= attributsChamp('ordre', $erreursPlat, 'aide-ordre') ?>>
                <p class="champ__aide" id="aide-ordre">Les petits nombres d'abord.</p>
                <?= messageErreur('ordre', $erreursPlat) ?>
            </div>
        </div>

        <label class="case-a-cocher">
            <input type="checkbox" name="visible" value="1"<?= $valeursPlat['visible'] ? ' checked' : '' ?>>
            Visible sur le site
        </label>

        <div class="groupe-boutons">
            <button class="bouton" type="submit"><?= $idModifie ? 'Enregistrer' : 'Ajouter le plat' ?></button>
            <a class="bouton bouton--contour" href="<?= e(url('admin/carte.php')) ?>">Annuler</a>
        </div>
    </form>
</section>
<?php endif; ?>

<!-- ===================================================== Plats par rubrique -->
<?php foreach ($parCategorie as $categorie): ?>
<section class="admin-carte" aria-labelledby="rubrique-<?= e($categorie['slug']) ?>">
    <div class="admin-carte__entete">
        <h2 id="rubrique-<?= e($categorie['slug']) ?>"><?= e($categorie['nom']) ?></h2>
        <a class="bouton bouton--contour bouton--petit" href="<?= e(url('admin/carte.php?ajouter=' . $categorie['id'] . '#formulaire-plat')) ?>">
            + Ajouter<span class="visuellement-cache"> un plat dans « <?= e($categorie['nom']) ?> »</span>
        </a>
    </div>

    <ul class="admin-plats" role="list">
<?php foreach ($categorie['plats'] as $plat): ?>
        <li class="admin-plat<?= $plat['visible'] ? '' : ' admin-plat--masque' ?>" id="plat-<?= (int) $plat['id'] ?>">
            <div class="admin-plat__texte">
                <p class="admin-plat__nom">
                    <?= e($plat['nom']) ?>
<?php if (!$plat['visible']): ?>
                    <span class="statut statut--annulee">Masqué</span>
<?php endif; ?>
                </p>
                <p class="admin-plat__details">
                    <?= e(prix((float) $plat['prix'])) ?><?= $plat['prix_bouteille'] !== null ? ' · bouteille ' . e(prix((float) $plat['prix_bouteille'])) : '' ?>
                    <?= $plat['origine'] ? ' · ' . e($origines[$plat['origine']]) : '' ?>
                </p>
            </div>
            <div class="admin-plat__actions">
                <a class="bouton bouton--contour bouton--petit" href="<?= e(url('admin/carte.php?modifier=' . $plat['id'] . '#formulaire-plat')) ?>">
                    Modifier<span class="visuellement-cache"> « <?= e($plat['nom']) ?> »</span>
                </a>
                <form method="post" action="<?= e(url('admin/carte.php')) ?>">
                    <?= champCsrf() ?>
                    <input type="hidden" name="action" value="visibilite">
                    <input type="hidden" name="id" value="<?= (int) $plat['id'] ?>">
                    <button class="bouton bouton--contour bouton--petit" type="submit">
                        <?= $plat['visible'] ? 'Masquer' : 'Afficher' ?><span class="visuellement-cache"> « <?= e($plat['nom']) ?> »</span>
                    </button>
                </form>
                <form method="post" action="<?= e(url('admin/carte.php')) ?>"
                      data-confirmer="<?= e('Supprimer définitivement « ' . $plat['nom'] . ' » ? Pour le retirer seulement un temps, utilisez plutôt « Masquer ».') ?>">
                    <?= champCsrf() ?>
                    <input type="hidden" name="action" value="supprimer_plat">
                    <input type="hidden" name="id" value="<?= (int) $plat['id'] ?>">
                    <button class="admin-lien-danger" type="submit">
                        Supprimer<span class="visuellement-cache"> « <?= e($plat['nom']) ?> »</span>
                    </button>
                </form>
            </div>
        </li>
<?php endforeach; ?>
<?php if (!$categorie['plats']): ?>
        <li class="admin-discret">Aucun plat dans cette rubrique.</li>
<?php endif; ?>
    </ul>
</section>
<?php endforeach; ?>
<span id="plat-fin"></span>

<!-- ===================================================== Formules -->
<section class="admin-carte" id="formules" aria-labelledby="titre-formules">
    <h2 id="titre-formules">Formule</h2>
<?php foreach ($formules as $formule):
    // Après une erreur, on réaffiche ce qui a été saisi ; sinon, les valeurs en base
    $f = ($valeursFormule !== null && (int) ($_POST['id'] ?? 0) === (int) $formule['id'])
        ? $valeursFormule
        : ['nom' => $formule['nom'], 'description' => $formule['description'], 'conditions' => $formule['conditions'] ?? '',
           'prix' => prixPourChamp($formule['prix']), 'visible' => (bool) $formule['visible']];
    $err = ($valeursFormule !== null && (int) ($_POST['id'] ?? 0) === (int) $formule['id']) ? $erreursFormule : [];
    $pre = 'formule-' . (int) $formule['id'] . '-';
?>
    <form class="formulaire" method="post" action="<?= e(url('admin/carte.php')) ?>#formules" novalidate>
        <?= champCsrf() ?>
        <input type="hidden" name="action" value="enregistrer_formule">
        <input type="hidden" name="id" value="<?= (int) $formule['id'] ?>">

        <div class="formulaire__ligne">
            <div class="champ">
                <label class="champ__label" for="<?= $pre ?>nom">Nom</label>
                <input class="champ__saisie" type="text" id="<?= $pre ?>nom" name="nom" value="<?= e($f['nom']) ?>" maxlength="80"<?= attributsChamp('nom', $err) ?>>
                <?= messageErreur('nom', $err) ?>
            </div>
            <div class="champ">
                <label class="champ__label" for="<?= $pre ?>prix">Prix (€)</label>
                <input class="champ__saisie" type="text" id="<?= $pre ?>prix" name="prix" value="<?= e($f['prix']) ?>" inputmode="decimal"<?= attributsChamp('prix', $err) ?>>
                <?= messageErreur('prix', $err) ?>
            </div>
        </div>
        <div class="champ">
            <label class="champ__label" for="<?= $pre ?>description">Contenu</label>
            <input class="champ__saisie" type="text" id="<?= $pre ?>description" name="description" value="<?= e($f['description']) ?>" maxlength="255"<?= attributsChamp('description', $err) ?>>
            <?= messageErreur('description', $err) ?>
        </div>
        <div class="champ">
            <label class="champ__label" for="<?= $pre ?>conditions">Conditions <span class="champ__facultatif">(facultatif)</span></label>
            <input class="champ__saisie" type="text" id="<?= $pre ?>conditions" name="conditions" value="<?= e($f['conditions']) ?>" maxlength="120"<?= attributsChamp('conditions', $err) ?>>
            <?= messageErreur('conditions', $err) ?>
        </div>
        <label class="case-a-cocher">
            <input type="checkbox" name="visible" value="1"<?= $f['visible'] ? ' checked' : '' ?>>
            Visible sur le site
        </label>
        <div>
            <button class="bouton" type="submit">Enregistrer la formule</button>
        </div>
    </form>
<?php endforeach; ?>
</section>

<?php require __DIR__ . '/../includes/admin/bas.php'; ?>
