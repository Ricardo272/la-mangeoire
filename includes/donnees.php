<?php
/**
 * Accès aux données : toutes les requêtes SQL du site public sont ici.
 * Les pages appellent ces fonctions et ne contiennent jamais de SQL.
 *
 * Si la base ne répond pas, les fonctions renvoient null (ou une liste vide) :
 * la page s'affiche quand même, avec un message adapté. L'erreur est inscrite
 * dans le journal de PHP, jamais montrée au visiteur.
 */

require_once __DIR__ . '/fonctions.php';
require_once __DIR__ . '/db.php';

/**
 * Note une erreur de base de données dans le journal de PHP.
 */
function journaliserErreur(string $contexte, Throwable $erreur): void
{
    error_log('[La Mangeoire] ' . $contexte . ' : ' . $erreur->getMessage());
}

/**
 * L'ardoise du jour, ou null s'il n'y en a pas (ou si la base ne répond pas).
 *
 * La date est calculée par PHP (heure de Paris) et transmise à MySQL,
 * plutôt que d'utiliser CURDATE() côté MySQL, dont le fuseau peut différer.
 */
function ardoiseDuJour(?DateTimeImmutable $jour = null): ?array
{
    $jour ??= new DateTimeImmutable('today');

    try {
        // :jour est un « marqueur » : la valeur est envoyée à part par execute()
        $requete = db()->prepare(
            'SELECT date_jour, plat, description, prix
               FROM ardoise
              WHERE date_jour = :jour'
        );
        $requete->execute(['jour' => $jour->format('Y-m-d')]);
        $ligne = $requete->fetch();
    } catch (Throwable $e) {
        journaliserErreur('ardoise du jour', $e);
        return null;
    }

    if (!$ligne) {
        return null;
    }

    return [
        'plat'        => $ligne['plat'],
        'description' => $ligne['description'],
        'prix'        => (float) $ligne['prix'],          // MySQL renvoie les DECIMAL en texte
        'date'        => new DateTimeImmutable($ligne['date_jour']),
    ];
}

/**
 * L'ardoise affichée sur le site public.
 * En mode démo (config/site.php), s'il n'y a pas d'ardoise ce jour-là, on en
 * reprend une déjà écrite, choisie selon le jour : le site de portfolio ne
 * reste jamais sur « en cours d'écriture ». Le tableau de bord, lui, garde
 * ardoiseDuJour() pour voir la réalité.
 */
function ardoiseAffichee(): ?array
{
    global $site;
    $aujourdhui = new DateTimeImmutable('today');
    $ardoise = ardoiseDuJour($aujourdhui);

    if ($ardoise !== null || empty($site['demo'])) {
        return $ardoise;
    }

    try {
        $nombre = (int) db()->query('SELECT COUNT(*) FROM ardoise')->fetchColumn();
        if ($nombre === 0) {
            return null;
        }
        // Une ardoise différente chaque jour : le numéro du jour dans l'année, modulo le nombre d'ardoises
        $requete = db()->prepare('SELECT plat, description, prix FROM ardoise ORDER BY date_jour LIMIT 1 OFFSET :rang');
        $requete->bindValue('rang', (int) $aujourdhui->format('z') % $nombre, PDO::PARAM_INT);
        $requete->execute();
        $ligne = $requete->fetch();
    } catch (Throwable $e) {
        journaliserErreur('ardoise de démonstration', $e);
        return null;
    }

    return [
        'plat'        => $ligne['plat'],
        'description' => $ligne['description'],
        'prix'        => (float) $ligne['prix'],
        'date'        => $aujourdhui, // affichée à la date du jour
    ];
}

/**
 * La carte, rangée par rubrique :
 *   [
 *     ['nom' => 'Entrées', 'slug' => 'entrees', 'plats' => [ [...], [...] ]],
 *     …
 *   ]
 * Renvoie null si la base ne répond pas.
 */
function carte(): ?array
{
    try {
        // Pas de valeur venant du visiteur dans cette requête : query() suffit,
        // pas besoin de requête préparée.
        $lignes = db()->query(
            'SELECT c.nom AS categorie, c.slug,
                    p.nom, p.description, p.prix, p.prix_bouteille, p.origine
               FROM categories c
               JOIN plats p ON p.categorie_id = c.id
              WHERE p.visible = 1
              ORDER BY c.ordre, p.ordre, p.nom'
        )->fetchAll();
    } catch (Throwable $e) {
        journaliserErreur('carte', $e);
        return null;
    }

    // Regroupement par rubrique (les lignes arrivent déjà dans le bon ordre)
    $rubriques = [];
    foreach ($lignes as $ligne) {
        $slug = $ligne['slug'];
        $rubriques[$slug] ??= ['nom' => $ligne['categorie'], 'slug' => $slug, 'plats' => []];

        $rubriques[$slug]['plats'][] = [
            'nom'            => $ligne['nom'],
            'description'    => $ligne['description'],
            'prix'           => (float) $ligne['prix'],
            'prix_bouteille' => $ligne['prix_bouteille'] !== null ? (float) $ligne['prix_bouteille'] : null,
            'origine'        => $ligne['origine'],
        ];
    }

    return array_values($rubriques);
}

/**
 * Les formules visibles (formule du midi…). Liste vide si la base ne répond pas.
 */
function formules(): array
{
    try {
        $lignes = db()->query(
            'SELECT nom, description, conditions, prix
               FROM formules
              WHERE visible = 1
              ORDER BY ordre'
        )->fetchAll();
    } catch (Throwable $e) {
        journaliserErreur('formules', $e);
        return [];
    }

    return array_map(
        fn ($ligne) => ['prix' => (float) $ligne['prix']] + $ligne,
        $lignes
    );
}
