<?php
/**
 * Petites aides pour afficher les formulaires de manière accessible.
 * Réutilisées par la réservation, puis par le tableau de bord.
 */

/**
 * Attributs ARIA d'un champ :
 *   aria-describedby  relie le champ à son aide et à son message d'erreur
 *                     (le lecteur d'écran les lit quand on arrive sur le champ)
 *   aria-invalid      signale le champ en erreur
 */
function attributsChamp(string $champ, array $erreurs, ?string $idAide = null): string
{
    $ids = [];
    if ($idAide !== null) {
        $ids[] = $idAide;
    }
    if (isset($erreurs[$champ])) {
        $ids[] = 'erreur-' . $champ;
    }

    $attributs = $ids ? ' aria-describedby="' . e(implode(' ', $ids)) . '"' : '';
    if (isset($erreurs[$champ])) {
        $attributs .= ' aria-invalid="true"';
    }

    return $attributs;
}

/**
 * Message d'erreur sous un champ (rien s'il n'y a pas d'erreur).
 */
function messageErreur(string $champ, array $erreurs): string
{
    if (!isset($erreurs[$champ])) {
        return '';
    }

    return '<p class="champ__erreur" id="erreur-' . e($champ) . '">' . e($erreurs[$champ]) . '</p>';
}
