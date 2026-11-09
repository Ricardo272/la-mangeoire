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
