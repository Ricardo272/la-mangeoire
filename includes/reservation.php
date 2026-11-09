<?php
/**
 * Réservation : créneaux, protection anti-robots, vérification et enregistrement.
 *
 * Règle d'or : on ne fait JAMAIS confiance à ce qui arrive du navigateur.
 * Les attributs HTML5 (required, min, max…) aident le visiteur, mais n'importe
 * qui peut les contourner : tout est revérifié ici, côté serveur.
 */

require_once __DIR__ . '/fonctions.php';
require_once __DIR__ . '/db.php';

const RESERVATION_COUVERTS_MAX   = 8;      // au-delà : « appelez-nous »
const RESERVATION_PAS_MINUTES    = 30;     // un créneau toutes les 30 minutes
const RESERVATION_DERNIER_AVANT  = 60;     // dernier créneau 1 h avant la fermeture
const RESERVATION_DELAI_MINUTES  = 60;     // réserver au plus tard 1 h avant
const RESERVATION_HORIZON        = '+2 months';
const RESERVATION_CONSERVATION   = '-3 months';
const JETON_AGE_MIN              = 3;      // secondes : plus rapide, c'est un robot
const JETON_AGE_MAX              = 7200;   // secondes : au-delà, le formulaire a expiré


/* ==========================================================================
   Créneaux
   ========================================================================== */

/**
 * Créneaux d'un jour de la semaine (1 = lundi … 7 = dimanche), d'après les horaires.
 * Ex. mardi : ['12:00', '12:30', '13:00', '13:30', '19:00', … '21:30']
 */
function creneauxDuJourSemaine(array $horaires, int $jourSemaine): array
{
    $creneaux = [];

    foreach ($horaires[$jourSemaine] ?? [] as [$ouverture, $fermeture]) {
        $heure   = DateTimeImmutable::createFromFormat('!H:i', $ouverture);
        $dernier = DateTimeImmutable::createFromFormat('!H:i', $fermeture)
            ->modify('-' . RESERVATION_DERNIER_AVANT . ' minutes');

        while ($heure <= $dernier) {
            $creneaux[] = $heure->format('H:i');
            $heure = $heure->modify('+' . RESERVATION_PAS_MINUTES . ' minutes');
        }
    }

    return $creneaux;
}

/**
 * Créneaux encore réservables pour une date donnée.
 * Le jour même, on retire ceux qui commencent dans moins d'une heure.
 */
function creneauxPourDate(array $horaires, DateTimeImmutable $date, ?DateTimeImmutable $maintenant = null): array
{
    $maintenant ??= new DateTimeImmutable();
    $creneaux = creneauxDuJourSemaine($horaires, (int) $date->format('N'));

    if ($date->format('Y-m-d') === $maintenant->format('Y-m-d')) {
        $limite = $maintenant->modify('+' . RESERVATION_DELAI_MINUTES . ' minutes')->format('H:i');
        $creneaux = array_values(array_filter($creneaux, fn ($c) => $c >= $limite));
    }

    return $creneaux;
}

/**
 * Tous les créneaux possibles dans la semaine (pour la liste déroulante),
 * rangés en deux groupes : midi et soir.
 */
function creneauxAffiches(array $horaires): array
{
    $tous = [];
    for ($jour = 1; $jour <= 7; $jour++) {
        $tous = array_merge($tous, creneauxDuJourSemaine($horaires, $jour));
    }
    $tous = array_unique($tous);
    sort($tous);

    return [
        'Midi' => array_values(array_filter($tous, fn ($c) => $c < '16:00')),
        'Soir' => array_values(array_filter($tous, fn ($c) => $c >= '16:00')),
    ];
}


/* ==========================================================================
   Jeton anti-robots (sans session ni cookie)
   Le jeton contient l'heure d'affichage du formulaire, signée avec la clé
   secrète : « 1762700000.3f9a… ». Sans la clé, impossible d'en fabriquer un,
   ni de modifier l'heure sans casser la signature.
   ========================================================================== */

function signerJeton(string $horodatage): string
{
    return hash_hmac('sha256', 'reservation|' . $horodatage, configLocale()['secret']);
}

function creerJeton(): string
{
    $horodatage = (string) time();
    return $horodatage . '.' . signerJeton($horodatage);
}

/**
 * Vérifie le jeton. Retourne 'ok', 'invalide' (falsifié ou expiré) ou 'trop_rapide'.
 */
function verifierJeton(string $jeton): string
{
    $morceaux = explode('.', $jeton, 2);
    if (count($morceaux) !== 2 || !ctype_digit($morceaux[0])) {
        return 'invalide';
    }

    [$horodatage, $signature] = $morceaux;

    // hash_equals compare en temps constant : on ne peut pas deviner la
    // signature caractère par caractère en mesurant le temps de réponse.
    if (!hash_equals(signerJeton($horodatage), $signature)) {
        return 'invalide';
    }

    $age = time() - (int) $horodatage;
    if ($age < JETON_AGE_MIN) {
        return 'trop_rapide';
    }
    if ($age > JETON_AGE_MAX) {
        return 'invalide';
    }

    return 'ok';
}


/* ==========================================================================
   Vérification d'une demande
   ========================================================================== */

/**
 * Valeurs vides du formulaire (premier affichage).
 */
function reservationVide(): array
{
    return ['nom' => '', 'telephone' => '', 'email' => '', 'date' => '', 'heure' => '', 'couverts' => '2', 'message' => ''];
}

/**
 * « 06.12.34.56.78 », « +33 6 12 34 56 78 »… → « 06 12 34 56 78 », ou null si invalide.
 */
function normaliserTelephone(string $saisie): ?string
{
    $chiffres = preg_replace('/[\s.\-()]/', '', $saisie);
    $chiffres = preg_replace('/^(?:\+33|0033)/', '0', $chiffres);

    if (!preg_match('/^0[1-9]\d{8}$/', $chiffres)) {
        return null;
    }

    return implode(' ', str_split($chiffres, 2));
}

/**
 * Vérifie une demande envoyée par le formulaire.
 *
 * Retourne [$valeurs, $erreurs, $spam] :
 *   $valeurs  les valeurs nettoyées (pour réafficher le formulaire ou enregistrer)
 *   $erreurs  ['champ' => 'message'] ; vide si tout est bon
 *   $spam     true si c'est (très probablement) un robot
 */
function verifierReservation(array $post, array $horaires, ?DateTimeImmutable $maintenant = null): array
{
    $maintenant ??= new DateTimeImmutable();
    $aujourdhui = $maintenant->setTime(0, 0);

    // On ne garde que les champs attendus, toujours sous forme de texte
    $valeurs = [];
    foreach (reservationVide() as $champ => $defaut) {
        $valeurs[$champ] = is_string($post[$champ] ?? null) ? trim($post[$champ]) : '';
    }

    // --- Robots : champ piège rempli, ou formulaire envoyé en moins de 3 s ---
    $jeton = verifierJeton(is_string($post['jeton'] ?? null) ? $post['jeton'] : '');
    if (($post['site_web'] ?? '') !== '' || $jeton === 'trop_rapide') {
        return [$valeurs, [], true];
    }

    $erreurs = [];

    if ($jeton === 'invalide') {
        $erreurs['formulaire'] = 'Le formulaire a expiré. Merci de vérifier les informations puis de l\'envoyer à nouveau.';
    }

    // --- Nom ---
    $longueur = mb_strlen($valeurs['nom']);
    if ($longueur < 2 || $longueur > 80) {
        $erreurs['nom'] = 'Indiquez votre nom (entre 2 et 80 caractères).';
    }

    // --- Téléphone ---
    $telephone = normaliserTelephone($valeurs['telephone']);
    if ($telephone === null) {
        $erreurs['telephone'] = 'Indiquez un numéro de téléphone français, par exemple 06 12 34 56 78.';
    } else {
        $valeurs['telephone'] = $telephone;
    }

    // --- E-mail (facultatif) ---
    if ($valeurs['email'] !== ''
        && (mb_strlen($valeurs['email']) > 120 || !filter_var($valeurs['email'], FILTER_VALIDATE_EMAIL))) {
        $erreurs['email'] = 'Cette adresse e-mail ne semble pas valide.';
    }

    // --- Date ---
    // « ! » remet l'heure à zéro ; on revérifie le format pour refuser « 2026-02-31 »
    $date = DateTimeImmutable::createFromFormat('!Y-m-d', $valeurs['date']);
    if (!$date || $date->format('Y-m-d') !== $valeurs['date']) {
        $erreurs['date'] = 'Choisissez une date.';
        $date = null;
    } elseif ($date < $aujourdhui) {
        $erreurs['date'] = 'Cette date est déjà passée.';
    } elseif ($date > $aujourdhui->modify(RESERVATION_HORIZON)) {
        $erreurs['date'] = 'Nous prenons les réservations jusqu\'à deux mois à l\'avance.';
    } elseif (empty($horaires[(int) $date->format('N')])) {
        $erreurs['date'] = 'Nous sommes fermés ce jour-là. ' . resumerHoraires($horaires)['fermeture'] . '.';
    }

    // --- Créneau (seulement si la date est correcte) ---
    if ($date && !isset($erreurs['date'])) {
        $possibles = creneauxPourDate($horaires, $date, $maintenant);
        if ($possibles === []) {
            $erreurs['heure'] = 'Il n\'y a plus de créneau disponible ce jour-là. Appelez-nous, ou choisissez une autre date.';
        } elseif (!in_array($valeurs['heure'], $possibles, true)) {
            $existe = in_array($valeurs['heure'], creneauxDuJourSemaine($horaires, (int) $date->format('N')), true);
            $erreurs['heure'] = $existe
                ? 'Cet horaire n\'est plus disponible : réservez au moins une heure à l\'avance.'
                : 'Choisissez un horaire dans la liste.';
        }
    }

    // --- Couverts ---
    $couverts = filter_var($valeurs['couverts'], FILTER_VALIDATE_INT, [
        'options' => ['min_range' => 1, 'max_range' => RESERVATION_COUVERTS_MAX],
    ]);
    if ($couverts === false) {
        $erreurs['couverts'] = 'De 1 à ' . RESERVATION_COUVERTS_MAX . ' personnes. Au-delà, appelez-nous.';
    }

    // --- Message ---
    if (mb_strlen($valeurs['message']) > 500) {
        $erreurs['message'] = 'Votre message est trop long (500 caractères maximum).';
    }

    return [$valeurs, $erreurs, false];
}


/* ==========================================================================
   Enregistrement et nettoyage
   ========================================================================== */

/**
 * Enregistre une demande déjà vérifiée. Retourne false en cas de souci technique.
 */
function enregistrerReservation(array $valeurs): bool
{
    try {
        $requete = db()->prepare(
            'INSERT INTO reservations (nom, telephone, email, date_repas, heure, couverts, message)
             VALUES (:nom, :telephone, :email, :date_repas, :heure, :couverts, :message)'
        );
        $requete->execute([
            'nom'        => $valeurs['nom'],
            'telephone'  => $valeurs['telephone'],
            'email'      => $valeurs['email'] !== '' ? $valeurs['email'] : null,
            'date_repas' => $valeurs['date'],
            'heure'      => $valeurs['heure'],
            'couverts'   => (int) $valeurs['couverts'],
            'message'    => $valeurs['message'] !== '' ? $valeurs['message'] : null,
        ]);
    } catch (Throwable $e) {
        error_log('[La Mangeoire] réservation : ' . $e->getMessage());
        return false;
    }

    purgerAnciennesReservations();
    return true;
}

/**
 * Supprime les réservations dont le repas date de plus de 3 mois
 * (durée annoncée dans les mentions légales). Appelée à chaque nouvelle
 * réservation : pas besoin de tâche planifiée.
 */
function purgerAnciennesReservations(): void
{
    try {
        $limite = (new DateTimeImmutable('today'))->modify(RESERVATION_CONSERVATION);
        $requete = db()->prepare('DELETE FROM reservations WHERE date_repas < :limite');
        $requete->execute(['limite' => $limite->format('Y-m-d')]);
    } catch (Throwable $e) {
        // Le nettoyage peut attendre la prochaine fois : on ne bloque pas la réservation
        error_log('[La Mangeoire] nettoyage des réservations : ' . $e->getMessage());
    }
}
