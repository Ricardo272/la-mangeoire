-- ==========================================================================
-- Données de départ (démonstration) — à exécuter en root, après schema.sql.
-- Peut être relancé à tout moment : il vide les tables puis les remplit.
--
-- Les ardoises sont datées à partir du jour de l'import (CURDATE()) :
-- la démonstration fonctionne 14 jours, il suffit de relancer ce fichier
-- pour la prolonger.
-- ==========================================================================

SET NAMES utf8mb4;

-- On désactive un instant le contrôle des clés étrangères pour pouvoir vider
-- les tables dans n'importe quel ordre, puis on le réactive aussitôt.
SET FOREIGN_KEY_CHECKS = 0;
TRUNCATE TABLE reservations;
TRUNCATE TABLE ardoise;
TRUNCATE TABLE formules;
TRUNCATE TABLE plats;
TRUNCATE TABLE categories;
SET FOREIGN_KEY_CHECKS = 1;


-- --------------------------------------------------------------------------
-- Rubriques
-- --------------------------------------------------------------------------
INSERT INTO categories (id, nom, slug, ordre) VALUES
    (1, 'Entrées',          'entrees',  1),
    (2, 'Plats',            'plats',    2),
    (3, 'Desserts',         'desserts', 3),
    (4, 'Vins et boissons', 'vins',     4);


-- --------------------------------------------------------------------------
-- Carte
-- --------------------------------------------------------------------------
INSERT INTO plats (categorie_id, nom, description, prix, prix_bouteille, origine, ordre) VALUES
    -- Entrées
    (1, 'Poireaux vinaigrette',          'moutarde à l''ancienne, œuf mimosa',             8.00, NULL, 'josette', 1),
    (1, 'Œufs mayonnaise',               'œufs fermiers, mayonnaise maison',               7.00, NULL, 'josette', 2),
    (1, 'Terrine de campagne maison',    'cornichons, pain de campagne grillé',            9.00, NULL, 'josette', 3),
    (1, 'Maquereau mariné au cidre',     'pomme verte, raifort, pain de seigle',          11.00, NULL, 'lucas',   4),
    (1, 'Velouté de potimarron',         'noisettes torréfiées, crème crue',               9.00, NULL, 'lucas',   5),

    -- Plats
    (2, 'Bœuf bourguignon de Josette',   'mijoté sept heures, pommes vapeur',             22.00, NULL, 'josette', 1),
    (2, 'Blanquette de veau à l''ancienne', 'riz pilaf, champignons de Paris',            21.00, NULL, 'josette', 2),
    (2, 'Steak frites',                  'sauce au poivre, frites maison',                23.00, NULL, NULL,      3),
    (2, 'Coquelet rôti',                 'jus corsé, purée au beurre',                    20.00, NULL, NULL,      4),
    (2, 'Lieu jaune, beurre blanc au cidre', 'poireaux fondants, pommes grenaille',       24.00, NULL, 'lucas',   5),
    (2, 'Risotto d''épeautre aux champignons', 'plat végétarien, parmesan affiné',        19.00, NULL, 'lucas',   6),

    -- Desserts
    (3, 'Mousse au chocolat',            'la recette du cahier de Josette',                8.00, NULL, 'josette', 1),
    (3, 'Île flottante',                 'crème anglaise vanillée, pralines roses',        8.00, NULL, 'josette', 2),
    (3, 'Crème brûlée à la vanille',     NULL,                                             8.00, NULL, NULL,      3),
    (3, 'Tarte Tatin revisitée',         'caramel au beurre salé, crème crue',            10.00, NULL, 'lucas',   4),
    (3, 'Assiette de fromages affinés',  'selon l''arrivage du fromager',                 10.00, NULL, NULL,      5),

    -- Vins et boissons (prix = le verre ; prix_bouteille = la bouteille)
    (4, 'Côtes-du-Rhône',                'rouge, souple et fruité',                        6.00, 28.00, NULL,     1),
    (4, 'Beaujolais-Villages',           'rouge, léger, à boire frais',                    6.50, 30.00, NULL,     2),
    (4, 'Muscadet sur lie',              'blanc, sec et iodé',                             6.00, 27.00, NULL,     3),
    (4, 'Bière blonde artisanale',       'pression, 25 cl',                                5.00, NULL,  NULL,     4),
    (4, 'Limonade artisanale',           '33 cl',                                          4.50, NULL,  NULL,     5),
    (4, 'Café',                          'servi au zinc ou en salle',                      2.50, NULL,  NULL,     6);


-- --------------------------------------------------------------------------
-- Formule du midi
-- --------------------------------------------------------------------------
INSERT INTO formules (nom, description, conditions, prix, ordre) VALUES
    ('Formule du midi', 'Entrée + plat ou plat + dessert', 'Du mardi au vendredi, le midi', 21.00, 1);


-- --------------------------------------------------------------------------
-- Ardoises : 14 jours à partir d'aujourd'hui
-- --------------------------------------------------------------------------
INSERT INTO ardoise (date_jour, plat, description, prix) VALUES
    (CURDATE() + INTERVAL  0 DAY, 'Blanquette de veau à l''ancienne', 'riz pilaf & champignons de Paris', 19.00),
    (CURDATE() + INTERVAL  1 DAY, 'Hachis parmentier de Josette',     'salade de mâche',                   17.00),
    (CURDATE() + INTERVAL  2 DAY, 'Petit salé aux lentilles',         'lentilles vertes du Puy',           18.00),
    (CURDATE() + INTERVAL  3 DAY, 'Pot-au-feu',                       'os à moelle & gros sel',            19.00),
    (CURDATE() + INTERVAL  4 DAY, 'Joue de bœuf braisée',             'au vin rouge, purée maison',        21.00),
    (CURDATE() + INTERVAL  5 DAY, 'Lapin à la moutarde',              'tagliatelles fraîches',             19.00),
    (CURDATE() + INTERVAL  6 DAY, 'Saucisse-purée',                   'oignons confits',                   16.00),
    (CURDATE() + INTERVAL  7 DAY, 'Coq au vin',                       'lardons & champignons',             20.00),
    (CURDATE() + INTERVAL  8 DAY, 'Parmentier de canard',             'salade aux noix',                   19.00),
    (CURDATE() + INTERVAL  9 DAY, 'Cabillaud, aïoli',                 'légumes du marché',                 21.00),
    (CURDATE() + INTERVAL 10 DAY, 'Poule au pot',                     'bouillon & légumes d''hiver',       19.00),
    (CURDATE() + INTERVAL 11 DAY, 'Choucroute garnie',                'trois viandes, pommes vapeur',      20.00),
    (CURDATE() + INTERVAL 12 DAY, 'Tête de veau, sauce gribiche',     'comme le faisait Marcel',           18.00),
    (CURDATE() + INTERVAL 13 DAY, 'Navarin d''agneau',                'petits légumes glacés',             21.00);


-- --------------------------------------------------------------------------
-- Réservations de démonstration (pour tester le tableau de bord)
-- Sur les 10 prochains jours, en gardant seulement les jours d'ouverture :
-- WEEKDAY() vaut 0 le lundi … 6 le dimanche → on garde 1 à 5 (mardi → samedi).
-- --------------------------------------------------------------------------
INSERT INTO reservations (nom, telephone, email, date_repas, heure, couverts, message, statut)
SELECT nom, telephone, email, jour, heure, couverts, message, statut
  FROM (
            SELECT 'Jeanne Martin'    AS nom, '06 12 34 56 78' AS telephone, 'jeanne.martin@example.com' AS email, CURDATE() + INTERVAL 0 DAY AS jour, '12:30' AS heure, 2 AS couverts, NULL AS message, 'confirmee' AS statut
  UNION ALL SELECT 'Paul Lefèvre',      '07 98 76 54 32', NULL,                         CURDATE() + INTERVAL 0 DAY, '20:00', 4, 'Une chaise haute, merci', 'en_attente'
  UNION ALL SELECT 'Amina Benali',      '06 55 44 33 22', 'amina.b@example.com',        CURDATE() + INTERVAL 1 DAY, '12:00', 3, NULL, 'en_attente'
  UNION ALL SELECT 'Hugo Moreau',       '06 11 22 33 44', NULL,                         CURDATE() + INTERVAL 1 DAY, '19:30', 6, 'Anniversaire de ma mère', 'confirmee'
  UNION ALL SELECT 'Claire Dubois',     '01 99 00 11 22', 'claire.dubois@example.com',  CURDATE() + INTERVAL 2 DAY, '21:00', 2, NULL, 'en_attente'
  UNION ALL SELECT 'Marc Petit',        '06 70 80 90 10', NULL,                         CURDATE() + INTERVAL 3 DAY, '13:00', 5, NULL, 'annulee'
  UNION ALL SELECT 'Sophie Laurent',    '07 12 12 12 12', 'sophie@example.com',         CURDATE() + INTERVAL 3 DAY, '20:30', 2, 'Table près de la fenêtre si possible', 'en_attente'
  UNION ALL SELECT 'Karim Haddad',      '06 45 45 45 45', NULL,                         CURDATE() + INTERVAL 4 DAY, '12:30', 8, 'Déjeuner d''équipe', 'confirmee'
  UNION ALL SELECT 'Élise Fontaine',    '06 30 30 30 30', NULL,                         CURDATE() + INTERVAL 5 DAY, '19:00', 2, NULL, 'en_attente'
  UNION ALL SELECT 'Thomas Girard',     '07 60 60 60 60', 'thomas.g@example.com',       CURDATE() + INTERVAL 7 DAY, '20:00', 4, 'Allergie aux fruits à coque', 'en_attente'
  UNION ALL SELECT 'Nadia Roux',        '06 90 90 90 90', NULL,                         CURDATE() + INTERVAL 8 DAY, '12:00', 3, NULL, 'en_attente'
  UNION ALL SELECT 'Louis Garnier',     '06 21 21 21 21', NULL,                         CURDATE() + INTERVAL 9 DAY, '21:30', 2, NULL, 'en_attente'
  UNION ALL SELECT 'Inès Mercier',      '06 81 81 81 81', NULL,                         CURDATE() - INTERVAL 6 DAY, '20:00', 4, NULL, 'confirmee'
  ) AS exemples
 WHERE WEEKDAY(jour) BETWEEN 1 AND 5;
