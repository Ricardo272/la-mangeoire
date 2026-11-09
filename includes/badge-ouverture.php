<?php
/**
 * Badge « Ouvert maintenant / Fermé · ouvre mardi à 12h ».
 * Prévu pour les fonds sombres (pied de page, bandeau bordeaux).
 */
$etat = etatOuverture($site['horaires']);
?>
<p class="badge-ouverture<?= $etat['ouvert'] ? ' badge-ouverture--ouvert' : '' ?>">
    <span class="badge-ouverture__pastille" aria-hidden="true"></span>
    <?= e($etat['texte']) ?>
</p>
