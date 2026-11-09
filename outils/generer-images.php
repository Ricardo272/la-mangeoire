<?php
/**
 * Génère les images « techniques » du site avec la bibliothèque GD de PHP :
 *   - assets/img/partage.jpg      image de partage (1200 × 630) pour les réseaux
 *   - apple-touch-icon.png        icône d'écran d'accueil iOS (180 × 180)
 *   - favicon.ico                 icône d'onglet pour les anciens navigateurs (32 × 32)
 *
 * À lancer en ligne de commande, depuis la racine du projet :
 *   php outils/generer-images.php
 *
 * Les polices TTF sont dans outils/polices/ (GD ne lit pas le format woff2).
 * Le dossier outils/ est inaccessible depuis le navigateur (voir .htaccess).
 */

if (PHP_SAPI !== 'cli') {
    exit('À lancer en ligne de commande.');
}

$racine  = dirname(__DIR__);
$titres  = __DIR__ . '/polices/DMSerifDisplay-Regular.ttf';
$italique = __DIR__ . '/polices/DMSerifDisplay-Italic.ttf';

/** Couleur GD à partir d'un code « #RRGGBB » */
function couleur($image, string $hex, int $transparence = 0): int
{
    [$r, $v, $b] = sscanf($hex, '#%02x%02x%02x');
    return imagecolorallocatealpha($image, $r, $v, $b, $transparence);
}

/** Écrit un texte centré horizontalement sur la ligne de base $y */
function texteCentre($image, float $taille, int $y, int $couleur, string $police, string $texte, int $largeur): void
{
    $boite = imagettfbbox($taille, 0, $police, $texte);
    $x = (int) (($largeur - ($boite[2] - $boite[0])) / 2) - $boite[0];
    imagettftext($image, $taille, 0, $x, $y, $couleur, $police, $texte);
}

/** Dessine un losange plein centré en ($cx, $cy), de demi-diagonale $r */
function losange($image, int $cx, int $cy, int $r, int $couleur): void
{
    imagefilledpolygon($image, [$cx, $cy - $r, $cx + $r, $cy, $cx, $cy + $r, $cx - $r, $cy], $couleur);
}


/* ==========================================================================
   1. Image de partage 1200 × 630
   ========================================================================== */
$l = 1200;
$h = 630;
$img = imagecreatetruecolor($l, $h);
$bordeaux = couleur($img, '#6B1E2B');
$creme    = couleur($img, '#F5EFE4');
$cremeDoux = couleur($img, '#F5EFE4', 25);
$laiton   = couleur($img, '#B08D57');

imagefill($img, 0, 0, $bordeaux);

// Double filet laiton en cadre
imagesetthickness($img, 3);
imagerectangle($img, 28, 28, $l - 29, $h - 29, $laiton);
imagesetthickness($img, 1);
imagerectangle($img, 40, 40, $l - 41, $h - 41, $laiton);

// Surtitre, nom, filet orné, slogan
texteCentre($img, 20, 175, $laiton, $titres, 'M O N T M A R T R E   ·   D E P U I S   1 9 5 2', $l);
texteCentre($img, 112, 325, $creme, $titres, 'La Mangeoire', $l);

$y = 385;
imagesetthickness($img, 2);
imageline($img, $l / 2 - 170, $y, $l / 2 - 22, $y, $laiton);
imageline($img, $l / 2 + 22, $y, $l / 2 + 170, $y, $laiton);
losange($img, (int) ($l / 2), $y, 9, $laiton);

texteCentre($img, 32, 460, $cremeDoux, $italique, '« Chez nous, on vient comme on est,', $l);
texteCentre($img, 32, 510, $cremeDoux, $italique, 'et on repart le ventre plein. »', $l);

imagejpeg($img, $racine . '/assets/img/partage.jpg', 88);
imagedestroy($img);
echo "assets/img/partage.jpg\n";


/* ==========================================================================
   2. Icônes : losange bordeaux, « M » crème (même dessin que favicon.svg)
   On dessine en grand puis on réduit : les bords sont plus lisses.
   ========================================================================== */
function icone(string $titres, int $taille, bool $fondPlein): GdImage
{
    $grand = 512;
    $img = imagecreatetruecolor($grand, $grand);
    imagesavealpha($img, true);

    if ($fondPlein) {
        // iOS arrondit lui-même les angles : fond crème plein, pas de transparence
        imagefill($img, 0, 0, couleur($img, '#F5EFE4'));
    } else {
        imagealphablending($img, false);
        imagefill($img, 0, 0, couleur($img, '#000000', 127));
        imagealphablending($img, true);
    }

    losange($img, 256, 256, $fondPlein ? 220 : 250, couleur($img, '#6B1E2B'));
    $boite = imagettfbbox($fondPlein ? 190 : 230, 0, $titres, 'M');
    $x = (int) (256 - ($boite[2] - $boite[0]) / 2 - $boite[0]);
    $y = (int) (256 + ($boite[1] - $boite[7]) / 2 - $boite[1]);
    imagettftext($img, $fondPlein ? 190 : 230, 0, $x, $y, couleur($img, '#F5EFE4'), $titres, 'M');

    $petit = imagecreatetruecolor($taille, $taille);
    imagealphablending($petit, false);
    imagesavealpha($petit, true);
    imagecopyresampled($petit, $img, 0, 0, 0, 0, $taille, $taille, $grand, $grand);
    imagedestroy($img);

    return $petit;
}

$apple = icone($titres, 180, true);
imagepng($apple, $racine . '/apple-touch-icon.png');
imagedestroy($apple);
echo "apple-touch-icon.png\n";

// favicon.ico : un fichier ICO peut contenir directement une image PNG.
// En-tête ICO (6 octets) + une entrée de répertoire (16 octets) + le PNG.
$ico = icone($titres, 32, false);
ob_start();
imagepng($ico);
$png = ob_get_clean();
imagedestroy($ico);

$entete   = pack('vvv', 0, 1, 1);                               // réservé, type ICO, 1 image
$repertoire = pack('CCCCvvVV', 32, 32, 0, 0, 1, 32, strlen($png), 6 + 16);
file_put_contents($racine . '/favicon.ico', $entete . $repertoire . $png);
echo "favicon.ico\n";
