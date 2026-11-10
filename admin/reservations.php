<?php
// Administration : les réservations

require_once __DIR__ . '/../includes/admin/auth.php';
require_once __DIR__ . '/../includes/admin/requetes.php';

$admin = exigerConnexion();

$passees = ($_GET['vue'] ?? '') === 'passees';

/* --------------------------------------------------------------------------
   Action : changer le statut d'une réservation (confirmer, annuler, remettre en attente)
   -------------------------------------------------------------------------- */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifierCsrf();

    $id     = filter_var($_POST['id'] ?? '', FILTER_VALIDATE_INT);
    $statut = is_string($_POST['statut'] ?? null) ? $_POST['statut'] : '';
    $resa   = $id ? reservationParId($id) : null;

    if ($resa && array_key_exists($statut, STATUTS_RESERVATION)) {
        changerStatutReservation($id, $statut);
        $messages = [
            'confirmee'  => 'Réservation de %s confirmée.',
            'annulee'    => 'Réservation de %s annulée.',
            'en_attente' => 'Réservation de %s remise en attente.',
        ];
        flash(sprintf($messages[$statut], $resa['nom']));
    } else {
        flash('Cette réservation est introuvable.', 'erreur');
    }

    // Retour à la même vue, à la hauteur de la réservation modifiée
    rediriger('admin/reservations.php' . ($passees ? '?vue=passees' : '') . ($resa ? '#resa-' . $resa['id'] : ''));
}

$parDate = reservationsParDate($passees);

$titreAdmin = 'Réservations';
$pageAdmin  = 'reservations';
require __DIR__ . '/../includes/admin/haut.php';
?>

<h1>Réservations</h1>

<nav class="admin-onglets" aria-label="Période">
    <a href="<?= e(url('admin/reservations.php')) ?>"<?= !$passees ? ' aria-current="page"' : '' ?>>À venir</a>
    <a href="<?= e(url('admin/reservations.php?vue=passees')) ?>"<?= $passees ? ' aria-current="page"' : '' ?>>Passées (3 derniers mois)</a>
</nav>

<?php if (!$parDate): ?>
<p class="admin-carte"><?= $passees ? 'Aucune réservation passée.' : 'Aucune réservation à venir.' ?></p>
<?php endif; ?>

<?php foreach ($parDate as $date => $reservations):
    // Couverts du jour par service, sans compter les annulations
    $midi = $soir = 0;
    foreach ($reservations as $r) {
        if ($r['statut'] === 'annulee') continue;
        if ($r['heure'] < '16:00') { $midi += $r['couverts']; } else { $soir += $r['couverts']; }
    }
?>
<section class="admin-jour" aria-labelledby="jour-<?= e($date) ?>">
    <h2 id="jour-<?= e($date) ?>">
        <?= e(dateLongue(new DateTimeImmutable($date))) ?>
        <span class="admin-jour__couverts">Midi : <?= $midi ?> · Soir : <?= $soir ?> couverts</span>
    </h2>

    <ul class="admin-resas" role="list">
<?php foreach ($reservations as $resa): ?>
        <li class="resa resa--<?= e($resa['statut']) ?>" id="resa-<?= (int) $resa['id'] ?>">
            <div class="resa__principal">
                <p class="resa__heure"><?= e(formaterHeure(substr($resa['heure'], 0, 5))) ?></p>
                <div>
                    <p class="resa__nom"><?= e($resa['nom']) ?> · <?= (int) $resa['couverts'] ?> pers.</p>
                    <p class="resa__contact">
                        <a href="tel:<?= e(lienTelephone($resa['telephone'])) ?>"><?= e($resa['telephone']) ?></a>
<?php if ($resa['email']): ?>
                        · <a href="mailto:<?= e($resa['email']) ?>"><?= e($resa['email']) ?></a>
<?php endif; ?>
                    </p>
<?php if ($resa['message']): ?>
                    <p class="resa__message">« <?= e($resa['message']) ?> »</p>
<?php endif; ?>
                </div>
                <p class="statut statut--<?= e($resa['statut']) ?>"><?= e(STATUTS_RESERVATION[$resa['statut']]) ?></p>
            </div>

<?php if (!$passees): ?>
            <div class="resa__actions">
<?php foreach (['confirmee' => 'Confirmer', 'annulee' => 'Annuler', 'en_attente' => 'Remettre en attente'] as $statut => $libelle):
        if ($statut === $resa['statut']) continue; ?>
                <form method="post" action="<?= e(url('admin/reservations.php')) ?>"
                      <?= $statut === 'annulee' ? 'data-confirmer="' . e('Annuler la réservation de ' . $resa['nom'] . ' ?') . '"' : '' ?>>
                    <?= champCsrf() ?>
                    <input type="hidden" name="id" value="<?= (int) $resa['id'] ?>">
                    <input type="hidden" name="statut" value="<?= e($statut) ?>">
                    <button class="bouton<?= $statut === 'confirmee' ? '' : ' bouton--contour' ?> bouton--petit" type="submit">
                        <?= e($libelle) ?><span class="visuellement-cache"> la réservation de <?= e($resa['nom']) ?></span>
                    </button>
                </form>
<?php endforeach; ?>
            </div>
<?php endif; ?>
        </li>
<?php endforeach; ?>
    </ul>
</section>
<?php endforeach; ?>

<?php require __DIR__ . '/../includes/admin/bas.php'; ?>
