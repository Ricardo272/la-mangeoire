<?php
/**
 * Sécurité du tableau de bord : session, connexion, jeton CSRF, messages.
 * Inclus en tout premier par chaque page de admin/.
 */

require_once __DIR__ . '/../fonctions.php';
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../formulaire.php';

const ADMIN_INACTIVITE       = 7200; // secondes : déconnexion après 2 h sans activité
const ADMIN_ECHECS_MAX       = 5;    // échecs de connexion d'affilée avant blocage
const ADMIN_BLOCAGE_MINUTES  = 15;

// En cas d'erreur imprévue (base arrêtée…) : on la note dans le journal
// et on affiche un message sobre, jamais le détail technique.
set_exception_handler(function (Throwable $erreur): void {
    error_log('[La Mangeoire admin] ' . $erreur->getMessage());
    http_response_code(500);
    echo '<!DOCTYPE html><html lang="fr"><meta charset="utf-8"><title>Erreur</title>'
       . '<p style="font-family:sans-serif;padding:2rem">Une erreur technique est survenue. '
       . 'Vérifiez que MySQL est démarré, puis réessayez.</p></html>';
});


/* ==========================================================================
   Session
   ========================================================================== */

/**
 * Démarre la session de l'administration, avec un cookie bien protégé.
 */
function demarrerSession(): void
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }

    // Mode strict : PHP refuse un identifiant de session qu'il n'a pas créé lui-même
    ini_set('session.use_strict_mode', '1');

    session_name('mangeoire_admin');
    session_set_cookie_params([
        'lifetime' => 0,                  // supprimé à la fermeture du navigateur
        'path'     => url('admin/'),      // envoyé SEULEMENT pour /admin/ : le site public reste sans cookie
        'secure'   => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off', // HTTPS seulement, une fois en ligne
        'httponly' => true,               // inaccessible au JavaScript
        'samesite' => 'Strict',           // jamais envoyé depuis un autre site
    ]);

    session_start();
}

/**
 * L'administrateur connecté (['id', 'identifiant']), ou null.
 * Déconnecte au passage après 2 h d'inactivité.
 */
function adminConnecte(): ?array
{
    demarrerSession();

    if (empty($_SESSION['admin'])) {
        return null;
    }

    if (time() - ($_SESSION['derniere_activite'] ?? 0) > ADMIN_INACTIVITE) {
        deconnecter();
        return null;
    }

    $_SESSION['derniere_activite'] = time();
    return $_SESSION['admin'];
}

/**
 * À appeler en tête de chaque page protégée : renvoie vers la connexion si besoin.
 */
function exigerConnexion(): array
{
    $admin = adminConnecte();

    if ($admin === null) {
        header('Location: ' . url('admin/connexion.php'), true, 303);
        exit;
    }

    return $admin;
}


/* ==========================================================================
   Connexion / déconnexion
   ========================================================================== */

/**
 * Tente une connexion. Retourne 'ok', 'incorrect' ou 'bloque'.
 */
function connecter(string $identifiant, string $motDePasse): string
{
    $requete = db()->prepare(
        'SELECT id, identifiant, mot_de_passe, echecs, bloque_jusqua FROM admins WHERE identifiant = :identifiant'
    );
    $requete->execute(['identifiant' => $identifiant]);
    $compte = $requete->fetch();

    if (!$compte) {
        // Identifiant inconnu : on vérifie quand même un mot de passe factice,
        // pour que la réponse prenne le même temps. Sinon, mesurer le temps de
        // réponse suffirait à savoir quels identifiants existent.
        password_verify($motDePasse, password_hash('mot de passe factice', PASSWORD_DEFAULT));
        return 'incorrect';
    }

    $maintenant = new DateTimeImmutable();

    if ($compte['bloque_jusqua'] !== null && new DateTimeImmutable($compte['bloque_jusqua']) > $maintenant) {
        return 'bloque';
    }

    if (!password_verify($motDePasse, $compte['mot_de_passe'])) {
        $echecs = $compte['echecs'] + 1;
        $bloque = $echecs >= ADMIN_ECHECS_MAX;

        db()->prepare('UPDATE admins SET echecs = :echecs, bloque_jusqua = :bloque WHERE id = :id')->execute([
            'echecs' => $bloque ? 0 : $echecs,
            'bloque' => $bloque ? $maintenant->modify('+' . ADMIN_BLOCAGE_MINUTES . ' minutes')->format('Y-m-d H:i:s') : null,
            'id'     => $compte['id'],
        ]);

        return $bloque ? 'bloque' : 'incorrect';
    }

    // Réussite. Si PHP recommande désormais un algorithme plus solide,
    // on recalcule l'empreinte (possible seulement maintenant : on a le mot de passe).
    if (password_needs_rehash($compte['mot_de_passe'], PASSWORD_DEFAULT)) {
        db()->prepare('UPDATE admins SET mot_de_passe = :mdp WHERE id = :id')->execute([
            'mdp' => password_hash($motDePasse, PASSWORD_DEFAULT),
            'id'  => $compte['id'],
        ]);
    }

    db()->prepare('UPDATE admins SET echecs = 0, bloque_jusqua = NULL, derniere_connexion = NOW() WHERE id = :id')
        ->execute(['id' => $compte['id']]);

    // Nouvel identifiant de session : une session obtenue AVANT la connexion
    // (par un tiers, par exemple) ne vaut plus rien après.
    session_regenerate_id(true);
    $_SESSION['admin'] = ['id' => (int) $compte['id'], 'identifiant' => $compte['identifiant']];
    $_SESSION['derniere_activite'] = time();
    unset($_SESSION['csrf']); // nouveau jeton CSRF pour la session connectée

    return 'ok';
}

function deconnecter(): void
{
    demarrerSession();
    $_SESSION = [];

    // On supprime aussi le cookie dans le navigateur
    $parametres = session_get_cookie_params();
    setcookie(session_name(), '', [
        'expires'  => time() - 3600,
        'path'     => $parametres['path'],
        'secure'   => $parametres['secure'],
        'httponly' => true,
        'samesite' => 'Strict',
    ]);

    session_destroy();
}


/* ==========================================================================
   Jeton CSRF
   Un jeton aléatoire, gardé en session et glissé dans chaque formulaire.
   Un site malveillant peut pousser votre navigateur à envoyer un formulaire
   vers /admin/, mais il ne peut pas connaître ce jeton : la demande est refusée.
   ========================================================================== */

function jetonCsrf(): string
{
    demarrerSession();
    $_SESSION['csrf'] ??= bin2hex(random_bytes(32));
    return $_SESSION['csrf'];
}

function champCsrf(): string
{
    return '<input type="hidden" name="csrf" value="' . e(jetonCsrf()) . '">';
}

/**
 * Arrête tout si le jeton envoyé ne correspond pas à celui de la session.
 */
function verifierCsrf(): void
{
    demarrerSession();
    $envoye = is_string($_POST['csrf'] ?? null) ? $_POST['csrf'] : '';

    if (empty($_SESSION['csrf']) || !hash_equals($_SESSION['csrf'], $envoye)) {
        http_response_code(400);
        exit('Demande refusée : la page a expiré. Revenez en arrière, rechargez la page et recommencez.');
    }
}


/* ==========================================================================
   Messages « flash » : affichés une seule fois, après une redirection
   ========================================================================== */

function flash(string $message, string $type = 'succes'): void
{
    demarrerSession();
    $_SESSION['flash'] = ['message' => $message, 'type' => $type];
}

function lireFlash(): ?array
{
    demarrerSession();
    $flash = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);
    return $flash;
}

/**
 * Redirection après un envoi de formulaire (schéma Post/Redirect/Get).
 */
function rediriger(string $chemin): never
{
    header('Location: ' . url($chemin), true, 303);
    exit;
}
