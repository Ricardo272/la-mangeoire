<?php
/**
 * En-tête commun : lien d'évitement, nom du bistrot, navigation.
 */
?>
<a class="lien-evitement" href="#contenu">Aller au contenu</a>

<header class="entete">
    <div class="conteneur entete__interieur">
        <a class="entete__logo" href="<?= e(url()) ?>">
            <span class="entete__nom"><?= e($site['nom']) ?></span>
            <span class="entete__depuis">Bistrot depuis <?= e((string) $site['depuis']) ?></span>
        </a>

        <?php require __DIR__ . '/nav.php'; ?>
    </div>
</header>
