<?php
/**
 * Ardoise du plat du jour, réutilisée sur l'accueil et la carte.
 *
 * Variables attendues avant l'inclusion :
 *   $ardoise         tableau ['plat', 'description', 'prix', 'date' (DateTimeInterface)]
 *                    ou null s'il n'y a pas d'ardoise aujourd'hui
 *   $ardoiseGrande   (facultatif) true pour la variante grande
 *
 * Phase 3 : les données sont écrites en dur dans la page.
 * Phase 4 : elles viendront de la base, ce fichier ne changera pas.
 */
$classes = 'ardoise' . (!empty($ardoiseGrande) ? ' ardoise--grande' : '');
?>
<article class="<?= $classes ?>" aria-labelledby="ardoise-titre">
    <p class="ardoise__titre" id="ardoise-titre">Aujourd'hui sur l'ardoise</p>
<?php if ($ardoise): ?>
    <p class="ardoise__date">
        <time datetime="<?= e($ardoise['date']->format('Y-m-d')) ?>"><?= e(dateLongue($ardoise['date'])) ?></time>
    </p>
    <p class="ardoise__plat"><?= e($ardoise['plat']) ?></p>
<?php if (!empty($ardoise['description'])): ?>
    <p class="ardoise__description"><?= e($ardoise['description']) ?></p>
<?php endif; ?>
    <p class="ardoise__prix"><?= e(prix($ardoise['prix'])) ?></p>
<?php else: ?>
    <p class="ardoise__message">L'ardoise est en cours d'écriture…</p>
<?php endif; ?>
</article>
