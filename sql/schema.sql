-- ==========================================================================
-- Structure de la base La Mangeoire — phase 4 (carte et ardoise)
-- À exécuter en root, dans la base la_mangeoire.
-- ATTENTION : supprime les tables existantes et toutes leurs données.
--
-- Viendront plus tard : reservations (phase 5), admins (phase 7).
-- ==========================================================================

SET NAMES utf8mb4;

-- On supprime d'abord les tables qui dépendent des autres (clés étrangères)
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
