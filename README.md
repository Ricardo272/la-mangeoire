# La Mangeoire

Site vitrine d'un bistrot parisien **fictif**, créé pour mon portfolio.

> « Chez nous, on vient comme on est, et on repart le ventre plein. »

La Mangeoire, bistrot à l'ancienne du 18e arrondissement (Montmartre / Abbesses),
tenu par la même famille depuis 1952 : Marcel et Josette, puis Bernard,
et aujourd'hui Lucas, le petit-fils.

## Technique

- PHP 8 (header, footer et navigation en includes)
- MySQL, accès via PDO et requêtes préparées
- HTML sémantique, CSS et JavaScript sans framework
- Mobile en priorité, variables CSS pour la palette et les typographies
- Polices hébergées localement (pas d'appel à Google Fonts, respect du RGPD)

## Prérequis

- [Laragon](https://laragon.org/) (Apache, PHP 8+, MySQL)

## Installation

1. Placer le projet dans `C:\laragon\www\la-mangeoire`
2. Dans Laragon, cliquer sur **Recharger** : le site est servi sur `http://la-mangeoire.test`
3. Base de données (MySQL de Laragon, en `root`) :
   - choisir un mot de passe et le mettre à la place de `CHANGER_CE_MOT_DE_PASSE`
     dans `sql/creation-base.sql`, puis exécuter ce fichier (base + utilisateur dédié) ;
   - exécuter `sql/schema.sql` (tables) puis `sql/seed.sql` (données de démonstration)
     dans la base `la_mangeoire`.
4. Copier `config/config.example.php` en `config/config.local.php`
   et y mettre le même mot de passe, ainsi qu'une clé secrète aléatoire
   (`php -r "echo bin2hex(random_bytes(32));"`). Ce fichier est exclu de Git.

Les ardoises de démonstration couvrent 14 jours à partir de l'import :
relancer `sql/seed.sql` pour les prolonger.

## Tableau de bord

Adresse : `http://la-mangeoire.test/admin/` (aucun lien depuis le site public).

Créer un compte, ou changer son mot de passe (12 caractères minimum, demandé au clavier) :

```
php outils/creer-admin.php lucas
```

Supprimer un compte : `php outils/creer-admin.php --supprimer lucas`

Attention : relancer `sql/schema.sql` supprime aussi les comptes (à recréer ensuite).
`sql/seed.sql` ne touche pas aux comptes.

## Images générées

L'image de partage (`assets/img/partage.jpg`), `favicon.ico` et `apple-touch-icon.png`
sont produites par un script PHP (bibliothèque GD) :

```
php outils/generer-images.php
```

## Structure

```
la-mangeoire/
├── *.php               pages publiques
├── includes/           head, header, nav, footer, connexion à la base
├── config/             configuration (identifiants exclus de Git)
├── sql/                création des tables et données de départ
├── admin/              tableau de bord (dernière phase)
└── assets/
    ├── css/            variables, base, layout, composants, pages
    ├── js/
    ├── fonts/          polices .woff2 + licences OFL
    └── img/            images (les temporaires sont préfixées « temp- »)
```

## Avancement

- [x] Phase 0 : mise en place
- [x] Phase 1 : fondations (variables CSS, includes)
- [x] Phase 2 : composants (ardoise, filets dorés, boutons, menu)
- [x] Phase 3 : pages sans base de données (accueil, histoire, mentions légales, 404)
- [x] Phase 4 : base de données (carte, ardoise, badge « Ouvert »)
- [x] Phase 5 : Venir nous voir et réservation (plan SVG, formulaire, anti-robots sans cookie,
  suppression automatique des réservations 3 mois après le repas)
- [x] Phase 6 : finitions (agrandissement des toiles, animations, rubrique en cours, carte imprimable,
  JSON-LD, Open Graph, favicon, robots/sitemap, cache et compression, contrôle d'accessibilité)
- [x] Phase 7 : tableau de bord (ardoise avec aperçu, réservations, carte et formule ;
  session limitée à /admin/, CSRF, blocage après 5 échecs, mots de passe hachés)
- [ ] Phase 8 : mise en ligne
  (compléter l'hébergeur et le contact dans les mentions légales ; vérifier le numéro fictif ;
  retirer dev/composants.php ; remplacer le domaine dans config/site.php (url_absolue),
  robots.txt et sitemap.xml ; tester la fiche Google avec l'outil « Résultats enrichis »)

## Crédits

Polices sous licence SIL Open Font License 1.1 (textes dans `assets/fonts/licences/`) :
DM Serif Display, Work Sans, Caveat.
