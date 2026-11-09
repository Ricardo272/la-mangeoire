<?php
/**
 * Badge « Ouvert maintenant / Fermé · ouvre mardi à 12h ».
 * Par défaut pour les fonds sombres (pied de page, bandeau bordeaux).
 * Sur fond crème : définir $badgeClair = true avant l'inclusion.
 */
$etat = etatOuverture($site['horaires']);
$classesBadge = 'badge-ouverture'
    . ($etat['ouvert'] ? ' badge-ouverture--ouvert' : '')
    . (!empty($badgeClair) ? ' badge-ouverture--clair' : '');
$badgeClair = false; // ne s'applique qu'à cette inclusion
?>
<p class="<?= $classesBadge ?>">
    <span class="badge-ouverture__pastille" aria-hidden="true"></span>
    <?= e($etat['texte']) ?>
</p>
