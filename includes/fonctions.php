<?php
/**
 * Chargé en premier par chaque page (via head.php).
 * Charge les informations du site et définit les petites fonctions communes.
 */

// Toutes les heures du site (badge « Ouvert », réservations) sont celles de Paris.
date_default_timezone_set('Europe/Paris');

$site = require __DIR__ . '/../config/site.php';

/**
 * Configuration locale (config/config.local.php) : identifiants de la base,
 * clé secrète. Lue une seule fois, puis gardée en mémoire.
 */
function configLocale(): array
{
    static $config = null;

    if ($config === null) {
        $fichier = __DIR__ . '/../config/config.local.php';
        if (!is_file($fichier)) {
            throw new RuntimeException('config/config.local.php introuvable : copier config.example.php.');
        }
        $config = require $fichier;
    }

    return $config;
}

/**
 * Échappe un texte avant de l'afficher dans le HTML.
 * Empêche qu'un texte contenant des balises soit interprété par le navigateur.
 */
function e(?string $texte): string
{
    return htmlspecialchars($texte ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/**
 * Construit une adresse à partir de la racine du site.
 * url('carte.php') → « /carte.php » ; url() → « / » (accueil).
 * Fonctionne aussi depuis admin/, contrairement à un lien relatif.
 */
function url(string $chemin = ''): string
{
    global $site;
    return rtrim($site['base_url'], '/') . '/' . ltrim($chemin, '/');
}

/**
 * Adresse complète : urlAbsolue('carte.php') → « http://la-mangeoire.test/carte.php ».
 * Indispensable quand l'adresse est lue hors du site (Google, réseaux sociaux).
 */
function urlAbsolue(string $chemin = ''): string
{
    // Dans config.local.php : l'adresse change entre Laragon et l'hébergeur
    return rtrim(configLocale()['url_absolue'], '/') . url($chemin);
}

/**
 * Données structurées « Restaurant » (schema.org, format JSON-LD) :
 * une fiche que Google lit pour afficher adresse, horaires, téléphone…
 * Les horaires sont générés à partir de config/site.php.
 */
function donneesStructurees(array $site): string
{
    $joursAnglais = [1 => 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];

    // Regroupe les jours par créneau : « 12:00-14:30 » => [Tuesday, Wednesday…]
    $parCreneau = [];
    foreach ($site['horaires'] as $jour => $creneaux) {
        foreach ($creneaux as [$ouverture, $fermeture]) {
            $parCreneau[$ouverture . '-' . $fermeture][] = $joursAnglais[$jour];
        }
    }

    $horaires = [];
    foreach ($parCreneau as $creneau => $jours) {
        [$ouverture, $fermeture] = explode('-', $creneau);
        $horaires[] = [
            '@type'     => 'OpeningHoursSpecification',
            'dayOfWeek' => $jours,
            'opens'     => $ouverture,
            'closes'    => $fermeture,
        ];
    }

    $donnees = [
        '@context'      => 'https://schema.org',
        '@type'         => 'Restaurant',
        'name'          => $site['nom'],
        'description'   => $site['description'],
        'slogan'        => $site['slogan'],
        'url'           => urlAbsolue(),
        'image'         => urlAbsolue('assets/img/partage.jpg'),
        'telephone'     => lienTelephone($site['telephone']),
        'email'         => $site['email'],
        'foundingDate'  => (string) $site['depuis'],
        'servesCuisine' => $site['cuisine'],
        'priceRange'    => $site['gamme_prix'],
        'hasMenu'       => urlAbsolue('carte.php'),
        'acceptsReservations' => urlAbsolue('venir-nous-voir.php#reservation'),
        'address'       => [
            '@type'           => 'PostalAddress',
            'streetAddress'   => $site['adresse']['rue'],
            'postalCode'      => $site['adresse']['code_postal'],
            'addressLocality' => $site['adresse']['ville'],
            'addressCountry'  => 'FR',
        ],
        'openingHoursSpecification' => $horaires,
    ];

    // JSON_HEX_TAG : un « </script> » dans un texte ne pourrait pas fermer la balise
    return json_encode($donnees, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT | JSON_HEX_TAG);
}

/**
 * Adresse d'un fichier CSS ou JS, suivie de sa date de modification :
 * asset('assets/js/main.js') → « /assets/js/main.js?v=1762712345 ».
 * Quand le fichier change, l'adresse change : le navigateur ne peut plus
 * se servir d'une ancienne version gardée en cache.
 */
function asset(string $chemin): string
{
    $fichier = __DIR__ . '/../' . $chemin;
    $version = is_file($fichier) ? filemtime($fichier) : 0;
    return url($chemin) . '?v=' . $version;
}

/**
 * Transforme « 01 99 00 19 52 » en « +33199001952 » pour un lien tel:.
 */
function lienTelephone(string $numero): string
{
    $chiffres = preg_replace('/\D/', '', $numero);
    return '+33' . substr($chiffres, 1);
}

/**
 * Affiche une image du dossier assets/img/.
 *
 * Si le fichier n'existe pas encore, affiche à la place une image temporaire
 * aux mêmes proportions, avec le chemin attendu écrit dessus.
 * Le jour où la vraie photo arrive, il suffit de la déposer au bon endroit :
 * aucun code à modifier.
 *
 * @param string $chemin       chemin dans assets/img/, ex. « histoire/1952.jpg »
 * @param string $alt          description de l'image (lue par les lecteurs d'écran)
 * @param int    $largeur      largeur réelle de l'image, en pixels
 * @param int    $hauteur      hauteur réelle de l'image, en pixels
 * @param string $classe       classe CSS éventuelle
 * @param bool   $prioritaire  true pour une image visible dès l'ouverture de la page
 *                             (chargée tout de suite au lieu d'attendre le défilement)
 */
function image(string $chemin, string $alt, int $largeur, int $hauteur, string $classe = '', bool $prioritaire = false): string
{
    // width et height : le navigateur réserve la place avant le chargement,
    // la page ne « saute » pas.
    $attributs = sprintf('alt="%s" width="%d" height="%d"', e($alt), $largeur, $hauteur);
    $attributs .= $prioritaire ? ' fetchpriority="high"' : ' loading="lazy" decoding="async"';

    if (is_file(__DIR__ . '/../assets/img/' . $chemin)) {
        // Avec numéro de version : une photo remplacée est rechargée tout de suite
        $src = asset('assets/img/' . $chemin);
    } else {
        $src = imageTemporaire($chemin, $alt, $largeur, $hauteur);
        $classe = trim($classe . ' image-temp');
    }

    if ($classe !== '') {
        $attributs .= ' class="' . e($classe) . '"';
    }

    return '<img src="' . e($src) . '" ' . $attributs . '>';
}

/**
 * Fabrique une image SVG temporaire (fond sépia hachuré + étiquette)
 * et la renvoie sous forme d'adresse « data: » : aucun fichier n'est créé.
 */
function imageTemporaire(string $chemin, string $alt, int $largeur, int $hauteur): string
{
    $x = fn (string $t) => htmlspecialchars($t, ENT_XML1 | ENT_QUOTES, 'UTF-8');
    $taille = max(14, (int) round(min($largeur, $hauteur) / 18));
    $description = mb_strlen($alt) > 55 ? mb_substr($alt, 0, 52) . '…' : $alt;
    $milieu = $hauteur / 2;

    $svg = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 ' . $largeur . ' ' . $hauteur . '">'
         . '<defs><pattern id="h" width="18" height="18" patternUnits="userSpaceOnUse" patternTransform="rotate(45)">'
         . '<line x1="0" y1="0" x2="0" y2="18" stroke="#B08D57" stroke-opacity=".22" stroke-width="3"/></pattern></defs>'
         . '<rect width="100%" height="100%" fill="#E8DCC6"/>'   // teinte sépia, réservée aux images temporaires
         . '<rect width="100%" height="100%" fill="url(#h)"/>'
         . '<g font-family="Segoe UI, Arial, sans-serif" text-anchor="middle" fill="#6B1E2B">'
         . '<text x="50%" y="' . ($milieu - $taille * 1.4) . '" font-size="' . $taille . '" font-weight="600">À remplacer</text>'
         . '<text x="50%" y="' . ($milieu) . '" font-size="' . round($taille * 0.8) . '">' . $x($chemin) . ' (' . $largeur . ' × ' . $hauteur . ')</text>'
         . '<text x="50%" y="' . ($milieu + $taille * 1.3) . '" font-size="' . round($taille * 0.7) . '" fill="#1C1A19" fill-opacity=".7">' . $x($description) . '</text>'
         . '</g></svg>';

    return 'data:image/svg+xml,' . rawurlencode($svg);
}

/**
 * Date en toutes lettres, en français : « Samedi 7 novembre ».
 * Utilise l'extension intl de PHP (active dans Laragon).
 */
function dateLongue(?DateTimeInterface $date = null): string
{
    $date ??= new DateTimeImmutable();
    $format = new IntlDateFormatter('fr_FR', IntlDateFormatter::FULL, IntlDateFormatter::NONE, 'Europe/Paris', null, 'EEEE d MMMM');
    return ucfirst($format->format($date));
}

/**
 * Prix à la française : 19 → « 19 € » ; 8.5 → « 8,50 € ».
 * L'espace avant « € » est insécable : le symbole ne part jamais seul à la ligne.
 */
function prix(float $montant): string
{
    $decimales = floor($montant) == $montant ? 0 : 2;
    return number_format($montant, $decimales, ',', "\u{202F}") . "\u{00A0}€";
}

/**
 * « 14:30 » → « 14h30 » ; « 12:00 » → « 12h ».
 */
function formaterHeure(string $heure): string
{
    [$h, $m] = explode(':', $heure);
    return (int) $h . 'h' . ($m === '00' ? '' : $m);
}

/**
 * Le bistrot est-il ouvert en ce moment ? Et sinon, quand rouvre-t-il ?
 *
 * Retourne par exemple :
 *   ['ouvert' => true,  'texte' => 'Ouvert · jusqu\'à 14h30']
 *   ['ouvert' => false, 'texte' => 'Fermé · réouverture à 19h']
 *   ['ouvert' => false, 'texte' => 'Fermé · ouvre demain à 12h']
 *   ['ouvert' => false, 'texte' => 'Fermé · ouvre mardi à 12h']
 */
function etatOuverture(array $horaires, ?DateTimeImmutable $maintenant = null): array
{
    $maintenant ??= new DateTimeImmutable();
    $noms  = [1 => 'lundi', 'mardi', 'mercredi', 'jeudi', 'vendredi', 'samedi', 'dimanche'];
    $jour  = (int) $maintenant->format('N');
    // Les heures au format « HH:MM » se comparent correctement comme du texte
    $heure = $maintenant->format('H:i');

    // 1. Aujourd'hui : en plein service, ou un service plus tard dans la journée ?
    foreach ($horaires[$jour] ?? [] as $numero => [$debut, $fin]) {
        if ($heure >= $debut && $heure < $fin) {
            return ['ouvert' => true, 'texte' => 'Ouvert · jusqu\'à ' . formaterHeure($fin)];
        }
        if ($heure < $debut) {
            // Le matin : « ouvre à 12h » ; entre deux services : « réouverture à 19h »
            $verbe = $numero === 0 ? 'ouvre à ' : 'réouverture à ';
            return ['ouvert' => false, 'texte' => 'Fermé · ' . $verbe . formaterHeure($debut)];
        }
    }

    // 2. Sinon, le premier jour suivant qui a au moins un service
    for ($decalage = 1; $decalage <= 7; $decalage++) {
        $suivant = ($jour + $decalage - 1) % 7 + 1; // après dimanche (7), on repart à lundi (1)
        if (!empty($horaires[$suivant])) {
            $quand = $decalage === 1 ? 'demain' : $noms[$suivant];
            return ['ouvert' => false, 'texte' => 'Fermé · ouvre ' . $quand . ' à ' . formaterHeure($horaires[$suivant][0][0])];
        }
    }

    return ['ouvert' => false, 'texte' => 'Fermé'];
}

/**
 * Regroupe les jours consécutifs qui ont les mêmes horaires.
 *
 * Retourne par exemple :
 *   'ouvertures' => [['jours' => 'Du mardi au samedi', 'creneaux' => ['12h – 14h30', '19h – 22h30']]]
 *   'fermeture'  => 'Fermé le lundi et le dimanche'
 */
function resumerHoraires(array $horaires): array
{
    $noms = [1 => 'lundi', 'mardi', 'mercredi', 'jeudi', 'vendredi', 'samedi', 'dimanche'];
    $ouvertures = [];
    $fermes = [];
    $debut = null; // premier jour du groupe en cours

    // On va jusqu'à 8 (jour qui n'existe pas) pour clore le dernier groupe.
    for ($jour = 1; $jour <= 8; $jour++) {
        $creneaux = $horaires[$jour] ?? null;

        // Le groupe en cours s'arrête si les horaires changent.
        if ($debut !== null && $creneaux !== $horaires[$debut]) {
            $fin = $jour - 1;
            $ouvertures[] = [
                'jours'    => $debut === $fin
                    ? ucfirst($noms[$debut])
                    : 'Du ' . $noms[$debut] . ' au ' . $noms[$fin],
                'creneaux' => array_map(
                    fn ($c) => formaterHeure($c[0]) . ' – ' . formaterHeure($c[1]),
                    $horaires[$debut]
                ),
            ];
            $debut = null;
        }

        if ($creneaux === []) {
            $fermes[] = $noms[$jour];
        } elseif ($creneaux !== null && $debut === null) {
            $debut = $jour;
        }
    }

    $fermeture = '';
    if ($fermes) {
        $dernier = array_pop($fermes);
        $fermeture = 'Fermé le ' . ($fermes ? implode(', le ', $fermes) . ' et le ' : '') . $dernier;
    }

    return ['ouvertures' => $ouvertures, 'fermeture' => $fermeture];
}
