-- ==========================================================================
-- Structure de la base La Mangeoire
-- À exécuter en root, dans la base la_mangeoire.
-- ATTENTION : supprime les tables existantes et toutes leurs données,
-- Y COMPRIS LES COMPTES D'ADMINISTRATION (à recréer avec outils/creer-admin.php).
-- ==========================================================================

SET NAMES utf8mb4;

-- On supprime d'abord les tables qui dépendent des autres (clés étrangères)
DROP TABLE IF EXISTS admins;
DROP TABLE IF EXISTS reservations;
DROP TABLE IF EXISTS ardoise;
DROP TABLE IF EXISTS formules;
DROP TABLE IF EXISTS plats;
DROP TABLE IF EXISTS categories;


-- --------------------------------------------------------------------------
-- Rubriques de la carte : Entrées, Plats, Desserts, Vins et boissons
-- --------------------------------------------------------------------------
CREATE TABLE categories (
    id      TINYINT UNSIGNED  NOT NULL AUTO_INCREMENT,
    nom     VARCHAR(60)       NOT NULL,
    slug    VARCHAR(60)       NOT NULL,           -- pour les ancres : carte.php#desserts
    ordre   TINYINT UNSIGNED  NOT NULL DEFAULT 0, -- ordre d'affichage
    PRIMARY KEY (id),
    UNIQUE KEY uk_categories_slug (slug)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- --------------------------------------------------------------------------
-- Plats et boissons
-- Les prix sont en DECIMAL (valeur exacte), jamais en FLOAT (valeur approchée).
-- --------------------------------------------------------------------------
CREATE TABLE plats (
    id              INT UNSIGNED       NOT NULL AUTO_INCREMENT,
    categorie_id    TINYINT UNSIGNED   NOT NULL,
    nom             VARCHAR(120)       NOT NULL,
    description     VARCHAR(255)       NULL,
    prix            DECIMAL(6,2)       NOT NULL,          -- pour un vin : le prix du verre
    prix_bouteille  DECIMAL(6,2)       NULL,              -- vins seulement
    origine         ENUM('josette', 'lucas') NULL,        -- « Recette de Josette » / « Touche de Lucas »
    visible         BOOLEAN            NOT NULL DEFAULT TRUE, -- masquer sans supprimer
    ordre           SMALLINT UNSIGNED  NOT NULL DEFAULT 0,
    cree_le         DATETIME           NOT NULL DEFAULT CURRENT_TIMESTAMP,
    modifie_le      DATETIME           NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    -- Un plat appartient forcément à une catégorie qui existe.
    -- RESTRICT : impossible de supprimer une catégorie qui contient encore des plats.
    CONSTRAINT fk_plats_categorie
        FOREIGN KEY (categorie_id) REFERENCES categories (id)
        ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- --------------------------------------------------------------------------
-- Formules (formule du midi…)
-- --------------------------------------------------------------------------
CREATE TABLE formules (
    id           TINYINT UNSIGNED  NOT NULL AUTO_INCREMENT,
    nom          VARCHAR(80)       NOT NULL,
    description  VARCHAR(255)      NOT NULL,
    conditions   VARCHAR(120)      NULL,      -- ex. « Du mardi au vendredi, le midi »
    prix         DECIMAL(6,2)      NOT NULL,
    visible      BOOLEAN           NOT NULL DEFAULT TRUE,
    ordre        TINYINT UNSIGNED  NOT NULL DEFAULT 0,
    PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- --------------------------------------------------------------------------
-- Ardoise du plat du jour : une seule par jour (clé unique sur la date)
-- --------------------------------------------------------------------------
CREATE TABLE ardoise (
    id           INT UNSIGNED   NOT NULL AUTO_INCREMENT,
    date_jour    DATE           NOT NULL,
    plat         VARCHAR(120)   NOT NULL,
    description  VARCHAR(255)   NULL,
    prix         DECIMAL(6,2)   NOT NULL,
    cree_le      DATETIME       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    modifie_le   DATETIME       NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uk_ardoise_date (date_jour)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- --------------------------------------------------------------------------
-- Demandes de réservation (phase 5)
-- Seulement les données utiles : pas d'adresse IP, pas de compte client.
-- Supprimées automatiquement 3 mois après la date du repas
-- (voir purgerAnciennesReservations() dans includes/reservation.php).
-- --------------------------------------------------------------------------
CREATE TABLE reservations (
    id          INT UNSIGNED      NOT NULL AUTO_INCREMENT,
    nom         VARCHAR(80)       NOT NULL,
    telephone   VARCHAR(20)       NOT NULL,       -- enregistré au format « 06 12 34 56 78 »
    email       VARCHAR(120)      NULL,           -- facultatif
    date_repas  DATE              NOT NULL,
    heure       TIME              NOT NULL,
    couverts    TINYINT UNSIGNED  NOT NULL,
    message     VARCHAR(500)      NULL,
    statut      ENUM('en_attente', 'confirmee', 'annulee') NOT NULL DEFAULT 'en_attente',
    cree_le     DATETIME          NOT NULL DEFAULT CURRENT_TIMESTAMP,
    modifie_le  DATETIME          NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    -- Index : les recherches et le nettoyage se font par date de repas
    KEY idx_reservations_date (date_repas)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- --------------------------------------------------------------------------
-- Comptes du tableau de bord (phase 7)
-- Le mot de passe n'est JAMAIS stocké tel quel : seulement son empreinte
-- calculée par password_hash() (impossible de retrouver le mot de passe).
-- Pas de page d'inscription : les comptes se créent avec outils/creer-admin.php.
-- --------------------------------------------------------------------------
CREATE TABLE admins (
    id                  TINYINT UNSIGNED  NOT NULL AUTO_INCREMENT,
    identifiant         VARCHAR(40)       NOT NULL,
    mot_de_passe        VARCHAR(255)      NOT NULL,    -- empreinte password_hash()
    echecs              TINYINT UNSIGNED  NOT NULL DEFAULT 0,  -- échecs de connexion d'affilée
    bloque_jusqua       DATETIME          NULL,        -- blocage après trop d'échecs
    derniere_connexion  DATETIME          NULL,
    cree_le             DATETIME          NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uk_admins_identifiant (identifiant)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
