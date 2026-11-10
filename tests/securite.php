<?php
/**
 * Tests des parties sensibles : connexion (blocage), vérification des
 * réservations, lecture des prix, horaires.
 *
 * À lancer depuis la racine du projet, MySQL démarré :
 *   php tests/securite.php
 *
 * Crée un compte jetable, puis le supprime. Code de sortie 1 si un test échoue.
 */

if (PHP_SAPI !== 'cli') {
    exit('À lancer en ligne de commande.');
}

require_once __DIR__ . '/../includes/admin/auth.php';
require_once __DIR__ . '/../includes/admin/validation.php';
require_once __DIR__ . '/../includes/reservation.php';
require_once __DIR__ . '/../includes/admin/requetes.php';
require_once __DIR__ . '/../includes/donnees.php';
restore_exception_handler(); // ici, on veut voir les erreurs

$echecs = 0;
function verifier(string $nom, bool $condition): void
{
    global $echecs;
    echo ($condition ? '  ok      ' : '  ÉCHEC   ') . $nom . "\n";
    $echecs += $condition ? 0 : 1;
}


echo "Prix\n";
verifier('« 18,50 » → 18.5', lirePrix('18,50') === 18.5);
verifier('« 19 € » → 19', lirePrix('19 €') === 19.0);
verifier('« abc » refusé', lirePrix('abc') === null);
verifier('« 0 » refusé', lirePrix('0') === null);
verifier('« 1000 » refusé', lirePrix('1000') === null);


echo "Horaires\n";
$h = $site['horaires'];
verifier('mardi 12h00 : ouvert', etatOuverture($h, new DateTimeImmutable('2026-11-10 12:00'))['ouvert']);
verifier('mardi 14h30 : fermé', !etatOuverture($h, new DateTimeImmutable('2026-11-10 14:30'))['ouvert']);
verifier('samedi soir → ouvre mardi', str_contains(etatOuverture($h, new DateTimeImmutable('2026-11-14 23:00'))['texte'], 'mardi'));


echo "Réservation\n";
$t = (string) (time() - 10);
$valide = [
    'nom' => 'Jeanne Martin', 'telephone' => '+33 6 12 34 56 78', 'email' => '', 'date' => '2026-11-12',
    'heure' => '19:30', 'couverts' => '4', 'message' => '', 'jeton' => $t . '.' . signerJeton($t), 'site_web' => '',
];
$maintenant = new DateTimeImmutable('2026-11-09 19:30');
$essai = fn (array $modif) => verifierReservation(array_merge($valide, $modif), $h, $maintenant);

[$v, $e, $spam] = $essai([]);
verifier('demande valide acceptée', !$e && !$spam && $v['telephone'] === '06 12 34 56 78');
verifier('jour de fermeture refusé', isset($essai(['date' => '2026-11-16'])[1]['date']));
verifier('horaire hors liste refusé', isset($essai(['heure' => '16:00'])[1]['heure']));
verifier('9 personnes refusé', isset($essai(['couverts' => '9'])[1]['couverts']));
verifier('jeton falsifié refusé', isset($essai(['jeton' => $t . '.abc'])[1]['formulaire']));
verifier('champ piège → robot', $essai(['site_web' => 'x'])[2] === true);
verifier('envoi en moins de 3 s → robot', $essai(['jeton' => time() . '.' . signerJeton((string) time())])[2] === true);
verifier('nom non UTF-8 refusé', isset($essai(['nom' => "Jos\xe9"])[1]['nom']));


echo "Limite de demandes par heure\n";
$marque = 'test-limite-' . bin2hex(random_bytes(3));
try {
    $ajout = db()->prepare("INSERT INTO reservations (nom, telephone, date_repas, heure, couverts) VALUES (:nom, '06 00 00 00 00', CURDATE(), '20:00', 2)");
    for ($i = 0; $i < RESERVATION_MAX_PAR_HEURE; $i++) {
        $ajout->execute(['nom' => $marque]);
    }
    verifier('au-delà de la limite, la demande est refusée', @enregistrerReservation(array_merge($v, ['nom' => $marque, 'date' => date('Y-m-d')])) === false);
} finally {
    db()->prepare('DELETE FROM reservations WHERE nom = :nom')->execute(['nom' => $marque]);
}


echo "Ardoise tournante (mode démo)\n";
$aujourdhui = date('Y-m-d');
$sauvegarde = ardoisePourDate($aujourdhui);
try {
    supprimerArdoise($aujourdhui);
    $site['demo'] = true;
    $demo = ardoiseAffichee();
    verifier('sans ardoise du jour, une ardoise est quand même affichée', $demo !== null && $demo['date']->format('Y-m-d') === $aujourdhui);
    $site['demo'] = false;
    verifier('hors mode démo : « en cours d\'écriture »', ardoiseAffichee() === null);
} finally {
    $site['demo'] = true;
    if ($sauvegarde) {
        enregistrerArdoise(['date' => $aujourdhui, 'plat' => $sauvegarde['plat'], 'description' => $sauvegarde['description'] ?? '', 'prix_valeur' => $sauvegarde['prix']]);
    }
}


echo "Connexion\n";
$identifiant = 'test-auto-' . bin2hex(random_bytes(3));
$motDePasse  = bin2hex(random_bytes(12));
db()->prepare('INSERT INTO admins (identifiant, mot_de_passe) VALUES (:i, :m)')
    ->execute(['i' => $identifiant, 'm' => password_hash($motDePasse, PASSWORD_DEFAULT)]);

try {
    verifier('identifiant inconnu → incorrect', connecter('inconnu-' . $identifiant, 'x') === 'incorrect');

    $resultats = [];
    for ($i = 1; $i <= ADMIN_ECHECS_MAX; $i++) {
        $resultats[] = connecter($identifiant, 'mauvais');
    }
    verifier('4 échecs « incorrect », le 5e bloque', $resultats === array_merge(array_fill(0, ADMIN_ECHECS_MAX - 1, 'incorrect'), ['bloque']));
    verifier('bon mot de passe refusé pendant le blocage', connecter($identifiant, $motDePasse) === 'bloque');

    // Fin du blocage simulée : la connexion doit réussir et remettre le compteur à zéro
    db()->prepare('UPDATE admins SET bloque_jusqua = NOW() - INTERVAL 1 MINUTE WHERE identifiant = :i')->execute(['i' => $identifiant]);
    verifier('après le blocage, bon mot de passe accepté', @connecter($identifiant, $motDePasse) === 'ok');
} finally {
    db()->prepare('DELETE FROM admins WHERE identifiant = :i')->execute(['i' => $identifiant]);
}


echo $echecs ? "\n$echecs test(s) en échec.\n" : "\nTous les tests passent.\n";
exit($echecs ? 1 : 0);
