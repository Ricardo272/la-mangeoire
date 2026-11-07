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
3. *(à venir)* Créer la base avec `sql/schema.sql` puis `sql/seed.sql`
4. *(à venir)* Copier `config/config.example.php` en `config/config.local.php`
   et y renseigner les identifiants de la base

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
- [ ] Phase 1 : fondations (variables CSS, includes)
- [ ] Phase 2 : composants (ardoise, filets dorés, boutons, menu)
- [ ] Phase 3 : pages sans base de données
- [ ] Phase 4 : base de données (carte, ardoise, badge « Ouvert »)
- [ ] Phase 5 : Venir nous voir et réservation
- [ ] Phase 6 : finitions (animations, accessibilité, SEO)
- [ ] Phase 7 : tableau de bord
- [ ] Phase 8 : mise en ligne

## Crédits

Polices sous licence SIL Open Font License 1.1 (textes dans `assets/fonts/licences/`) :
DM Serif Display, Work Sans, Caveat.
