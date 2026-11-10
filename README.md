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
   et y mettre l'adresse du site, le même mot de passe, ainsi qu'une clé secrète
   aléatoire (`php -r "echo bin2hex(random_bytes(32));"`). Ce fichier est exclu de Git.

**Attention : `sql/seed.sql` vide puis remplit la carte, les ardoises et les réservations.**
Ne jamais le relancer sur le site en ligne : il effacerait tout ce qui a été saisi
dans le tableau de bord. Ce n'est plus nécessaire pour la démonstration : en mode démo
(`'demo' => true` dans `config/site.php`), un jour sans ardoise en affiche une déjà écrite.

## Tests

Connexion (blocage), réservation, prix et horaires, MySQL démarré :

```
php tests/securite.php
```

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
├── admin/              tableau de bord
├── outils/             scripts en ligne de commande (comptes, images)
├── tests/              tests en ligne de commande
└── assets/
    ├── css/            variables, base, layout, composants, pages
    ├── js/
    ├── fonts/          polices .woff2 + licences OFL
    └── img/            images (une image absente est remplacée par une image temporaire)
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
  - [x] Préparation : mode démo, adresse du site dans config.local.php, HTTPS forcé en ligne,
    page de développement retirée, correctifs de sécurité et tests
  - [x] Hébergeur (alwaysdata), photos Unsplash, hébergeur et crédits dans les mentions légales
  - [x] Sur le serveur : config.local.php (secrets propres au serveur, adresse en https), base
    importée, robots.txt et sitemap.xml, erreurs PHP masquées ; HTTPS, cookies et tests vérifiés
  - [ ] Compte admin `lucas` ; téléphone d'alwaysdata et lien GitHub dans les mentions légales ;
    numéro fictif à vérifier
  - [ ] Vérifications en ligne (Lighthouse, W3C, résultats enrichis Google, partage)

## En ligne

Site : https://la-mangeoire-demo.alwaysdata.net (hébergement alwaysdata, MariaDB).

Le code est envoyé par Git en SSH dans un dépôt nu (`~/depot.git`), puis déposé dans `~/www`.
`config/config.local.php` existe seulement sur le serveur. Pour publier une mise à jour :

```
git push ssh://la-mangeoire-demo@ssh-la-mangeoire-demo.alwaysdata.net/home/la-mangeoire-demo/depot.git main
ssh la-mangeoire-demo@ssh-la-mangeoire-demo.alwaysdata.net "git --git-dir=depot.git --work-tree=www checkout -f main"
```

Ne jamais lancer `sql/schema.sql` ni `sql/seed.sql` sur le serveur : ils effaceraient les données.

## Crédits

Polices sous licence SIL Open Font License 1.1 (textes dans `assets/fonts/licences/`) :
DM Serif Display, Work Sans, Caveat.
