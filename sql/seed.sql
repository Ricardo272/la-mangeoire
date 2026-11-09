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
