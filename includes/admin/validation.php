<?php
/**
 * Vérification des formulaires du tableau de bord.
 * Même principe que la réservation : chaque fonction renvoie [$valeurs, $erreurs].
 */

/**
 * Lit un prix saisi à la française : « 18,50 », « 18.5 », « 18 € » → 18.5.
 * Retourne null si la saisie n'est pas un prix valable (0,01 à 999,99).
 */
function lirePrix(string $saisie): ?float
{
    $nettoye = str_replace([',', '€', ' ', "\u{00A0}", "\u{202F}"], ['.', '', '', '', ''], trim($saisie));

    if (!preg_match('/^\d{1,3}(\.\d{1,2})?$/', $nettoye) || (float) $nettoye <= 0) {
        return null;
    }

    return (float) $nettoye;
}

/**
 * Prix pour remplir un champ de formulaire : 18.5 → « 18,50 » ; 19 → « 19 ».
 */
function prixPourChamp(float|string|null $prix): string
{
    if ($prix === null || $prix === '') {
        return '';
    }
    $prix = (float) $prix;
    return floor($prix) == $prix ? (string) (int) $prix : number_format($prix, 2, ',', '');
}

/** Vérifie une longueur de texte ; renvoie un message d'erreur ou null */
function verifierLongueur(string $texte, int $min, int $max, string $nom): ?string
{
    $longueur = mb_strlen($texte);
    if ($longueur < $min) {
        return $min === 1 ? "Indiquez $nom." : "Indiquez $nom ($min caractères minimum).";
    }
    if ($longueur > $max) {
        return ucfirst($nom) . " est trop long ($max caractères maximum).";
    }
    return null;
}


/* ==========================================================================
   Ardoise
   ========================================================================== */

function verifierArdoise(array $post): array
{
    $v = lireChamps($post, ['date', 'plat', 'description', 'prix']);
    $erreurs = [];

    $date = DateTimeImmutable::createFromFormat('!Y-m-d', $v['date']);
    if (!$date || $date->format('Y-m-d') !== $v['date']) {
        $erreurs['date'] = 'Choisissez une date.';
    }

    if ($message = verifierLongueur($v['plat'], 2, 120, 'le nom du plat')) {
        $erreurs['plat'] = $message;
    }
    if ($message = verifierLongueur($v['description'], 0, 255, 'la description')) {
        $erreurs['description'] = $message;
    }

    $v['prix_valeur'] = lirePrix($v['prix']);
    if ($v['prix_valeur'] === null) {
        $erreurs['prix'] = 'Indiquez un prix, par exemple 19 ou 18,50.';
    }

    return [$v, $erreurs];
}


/* ==========================================================================
   Plat
   ========================================================================== */

function verifierPlat(array $post, array $categories): array
{
    $v = lireChamps($post, ['categorie_id', 'nom', 'description', 'prix', 'prix_bouteille', 'origine', 'ordre']);
    $v['visible'] = isset($post['visible']);
    $erreurs = [];

    if (!in_array($v['categorie_id'], array_map('strval', array_column($categories, 'id')), true)) {
        $erreurs['categorie_id'] = 'Choisissez une rubrique.';
    }

    if ($message = verifierLongueur($v['nom'], 2, 120, 'le nom du plat')) {
        $erreurs['nom'] = $message;
    }
    if ($message = verifierLongueur($v['description'], 0, 255, 'la description')) {
        $erreurs['description'] = $message;
    }

    $v['prix_valeur'] = lirePrix($v['prix']);
    if ($v['prix_valeur'] === null) {
        $erreurs['prix'] = 'Indiquez un prix, par exemple 9 ou 6,50.';
    }

    $v['prix_bouteille_valeur'] = null;
    if ($v['prix_bouteille'] !== '') {
        $v['prix_bouteille_valeur'] = lirePrix($v['prix_bouteille']);
        if ($v['prix_bouteille_valeur'] === null) {
            $erreurs['prix_bouteille'] = 'Prix de la bouteille invalide (ou laissez vide).';
        }
    }

    if (!in_array($v['origine'], ['', 'josette', 'lucas'], true)) {
        $erreurs['origine'] = 'Choisissez une mention dans la liste.';
    }

    if ($v['ordre'] === '') {
        $v['ordre'] = '0';
    }
    if (filter_var($v['ordre'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 0, 'max_range' => 999]]) === false) {
        $erreurs['ordre'] = 'Un nombre entre 0 et 999.';
    }

    return [$v, $erreurs];
}


/* ==========================================================================
   Formule
   ========================================================================== */

function verifierFormule(array $post): array
{
    $v = lireChamps($post, ['nom', 'description', 'conditions', 'prix']);
    $v['visible'] = isset($post['visible']);
    $erreurs = [];

    if ($message = verifierLongueur($v['nom'], 2, 80, 'le nom de la formule')) {
        $erreurs['nom'] = $message;
    }
    if ($message = verifierLongueur($v['description'], 2, 255, 'le contenu de la formule')) {
        $erreurs['description'] = $message;
    }
    if ($message = verifierLongueur($v['conditions'], 0, 120, 'les conditions')) {
        $erreurs['conditions'] = $message;
    }

    $v['prix_valeur'] = lirePrix($v['prix']);
    if ($v['prix_valeur'] === null) {
        $erreurs['prix'] = 'Indiquez un prix, par exemple 21.';
    }

    return [$v, $erreurs];
}
