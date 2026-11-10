<?php
/**
 * Requêtes SQL du tableau de bord.
 * Ici, pas de repli silencieux comme sur le site public : si la base ne répond
 * pas, l'exception remonte et le gestionnaire d'erreur de auth.php affiche
 * un message (l'administrateur doit savoir que son action n'a pas abouti).
 */

require_once __DIR__ . '/../db.php';


/* ==========================================================================
   Réservations
   ========================================================================== */

const STATUTS_RESERVATION = [
    'en_attente' => 'En attente',
    'confirmee'  => 'Confirmée',
    'annulee'    => 'Annulée',
];

/**
 * Réservations à venir (à partir d'aujourd'hui) ou passées (3 derniers mois),
 * rangées par date : ['2026-11-12' => [ [...], [...] ], …]
 */
function reservationsParDate(bool $passees = false): array
{
    $sql = $passees
        ? 'SELECT * FROM reservations WHERE date_repas < :aujourdhui ORDER BY date_repas DESC, heure'
        : 'SELECT * FROM reservations WHERE date_repas >= :aujourdhui ORDER BY date_repas, heure';

    $requete = db()->prepare($sql);
    $requete->execute(['aujourdhui' => (new DateTimeImmutable('today'))->format('Y-m-d')]);

    $parDate = [];
    foreach ($requete->fetchAll() as $ligne) {
        $parDate[$ligne['date_repas']][] = $ligne;
    }

    return $parDate;
}

/**
 * Demandes à venir encore en attente de confirmation.
 */
function reservationsEnAttente(): array
{
    $requete = db()->prepare(
        "SELECT * FROM reservations
          WHERE statut = 'en_attente' AND date_repas >= :aujourdhui
          ORDER BY date_repas, heure"
    );
    $requete->execute(['aujourdhui' => (new DateTimeImmutable('today'))->format('Y-m-d')]);
    return $requete->fetchAll();
}

/**
 * Couverts prévus (réservations non annulées), midi et soir, sur plusieurs jours :
 * ['2026-11-10' => ['midi' => 6, 'soir' => 14], …]
 */
function couvertsParService(DateTimeImmutable $debut, int $nombreJours): array
{
    $requete = db()->prepare(
        "SELECT date_repas,
                SUM(CASE WHEN heure <  '16:00' THEN couverts ELSE 0 END) AS midi,
                SUM(CASE WHEN heure >= '16:00' THEN couverts ELSE 0 END) AS soir
           FROM reservations
          WHERE date_repas BETWEEN :debut AND :fin AND statut <> 'annulee'
          GROUP BY date_repas"
    );
    $requete->execute([
        'debut' => $debut->format('Y-m-d'),
        'fin'   => $debut->modify('+' . ($nombreJours - 1) . ' days')->format('Y-m-d'),
    ]);

    $resultat = [];
    for ($i = 0; $i < $nombreJours; $i++) {
        $resultat[$debut->modify("+$i days")->format('Y-m-d')] = ['midi' => 0, 'soir' => 0];
    }
    foreach ($requete->fetchAll() as $ligne) {
        $resultat[$ligne['date_repas']] = ['midi' => (int) $ligne['midi'], 'soir' => (int) $ligne['soir']];
    }

    return $resultat;
}

function reservationParId(int $id): ?array
{
    $requete = db()->prepare('SELECT * FROM reservations WHERE id = :id');
    $requete->execute(['id' => $id]);
    return $requete->fetch() ?: null;
}

function changerStatutReservation(int $id, string $statut): void
{
    db()->prepare('UPDATE reservations SET statut = :statut WHERE id = :id')
        ->execute(['statut' => $statut, 'id' => $id]);
}


/* ==========================================================================
   Ardoise
   ========================================================================== */

/**
 * Ardoises des prochains jours, indexées par date ('2026-11-10' => [...]).
 */
function ardoisesAVenir(int $nombreJours = 14): array
{
    $debut = new DateTimeImmutable('today');
    $requete = db()->prepare(
        'SELECT date_jour, plat, description, prix FROM ardoise
          WHERE date_jour BETWEEN :debut AND :fin ORDER BY date_jour'
    );
    $requete->execute([
        'debut' => $debut->format('Y-m-d'),
        'fin'   => $debut->modify('+' . ($nombreJours - 1) . ' days')->format('Y-m-d'),
    ]);

    $parDate = [];
    foreach ($requete->fetchAll() as $ligne) {
        $parDate[$ligne['date_jour']] = $ligne;
    }

    return $parDate;
}

function ardoisePourDate(string $date): ?array
{
    $requete = db()->prepare('SELECT date_jour, plat, description, prix FROM ardoise WHERE date_jour = :date');
    $requete->execute(['date' => $date]);
    return $requete->fetch() ?: null;
}

/**
 * Crée l'ardoise du jour, ou la remplace si ce jour en a déjà une.
 * « ON DUPLICATE KEY UPDATE » s'appuie sur la clé unique de date_jour.
 */
function enregistrerArdoise(array $ardoise): void
{
    db()->prepare(
        'INSERT INTO ardoise (date_jour, plat, description, prix)
         VALUES (:date_jour, :plat, :description, :prix)
         ON DUPLICATE KEY UPDATE plat = VALUES(plat), description = VALUES(description), prix = VALUES(prix)'
    )->execute([
        'date_jour'   => $ardoise['date'],
        'plat'        => $ardoise['plat'],
        'description' => $ardoise['description'] !== '' ? $ardoise['description'] : null,
        'prix'        => $ardoise['prix_valeur'],
    ]);
}

function supprimerArdoise(string $date): void
{
    db()->prepare('DELETE FROM ardoise WHERE date_jour = :date')->execute(['date' => $date]);
}


/* ==========================================================================
   Carte
   ========================================================================== */

function categories(): array
{
    return db()->query('SELECT id, nom, slug FROM categories ORDER BY ordre')->fetchAll();
}

/**
 * Tous les plats, masqués compris, rangés par rubrique.
 */
function platsParCategorie(): array
{
    $plats = db()->query('SELECT * FROM plats ORDER BY ordre, nom')->fetchAll();

    $parCategorie = [];
    foreach (categories() as $categorie) {
        $parCategorie[$categorie['id']] = $categorie + ['plats' => []];
    }
    foreach ($plats as $plat) {
        $parCategorie[$plat['categorie_id']]['plats'][] = $plat;
    }

    return $parCategorie;
}

function platParId(int $id): ?array
{
    $requete = db()->prepare('SELECT * FROM plats WHERE id = :id');
    $requete->execute(['id' => $id]);
    return $requete->fetch() ?: null;
}

/**
 * Crée un plat ($id null) ou modifie le plat $id.
 */
function enregistrerPlat(array $plat, ?int $id): void
{
    $valeurs = [
        'categorie_id'   => (int) $plat['categorie_id'],
        'nom'            => $plat['nom'],
        'description'    => $plat['description'] !== '' ? $plat['description'] : null,
        'prix'           => $plat['prix_valeur'],
        'prix_bouteille' => $plat['prix_bouteille_valeur'],
        'origine'        => $plat['origine'] !== '' ? $plat['origine'] : null,
        'visible'        => $plat['visible'] ? 1 : 0,
        'ordre'          => (int) $plat['ordre'],
    ];

    if ($id === null) {
        db()->prepare(
            'INSERT INTO plats (categorie_id, nom, description, prix, prix_bouteille, origine, visible, ordre)
             VALUES (:categorie_id, :nom, :description, :prix, :prix_bouteille, :origine, :visible, :ordre)'
        )->execute($valeurs);
    } else {
        db()->prepare(
            'UPDATE plats SET categorie_id = :categorie_id, nom = :nom, description = :description, prix = :prix,
                    prix_bouteille = :prix_bouteille, origine = :origine, visible = :visible, ordre = :ordre
              WHERE id = :id'
        )->execute($valeurs + ['id' => $id]);
    }
}

/**
 * Masque un plat visible, ou rend visible un plat masqué. « NOT visible » inverse la valeur.
 */
function basculerVisibilitePlat(int $id): void
{
    db()->prepare('UPDATE plats SET visible = NOT visible WHERE id = :id')->execute(['id' => $id]);
}

function supprimerPlat(int $id): void
{
    db()->prepare('DELETE FROM plats WHERE id = :id')->execute(['id' => $id]);
}

function formulesAdmin(): array
{
    return db()->query('SELECT * FROM formules ORDER BY ordre')->fetchAll();
}

function enregistrerFormule(int $id, array $formule): void
{
    db()->prepare(
        'UPDATE formules SET nom = :nom, description = :description, conditions = :conditions,
                prix = :prix, visible = :visible
          WHERE id = :id'
    )->execute([
        'nom'         => $formule['nom'],
        'description' => $formule['description'],
        'conditions'  => $formule['conditions'] !== '' ? $formule['conditions'] : null,
        'prix'        => $formule['prix_valeur'],
        'visible'     => $formule['visible'] ? 1 : 0,
        'id'          => $id,
    ]);
}
