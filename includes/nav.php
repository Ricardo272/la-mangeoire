<?php
/**
 * Navigation principale, construite à partir de $site['navigation'].
 * La page en cours reçoit aria-current="page" (lu par les lecteurs d'écran
 * et utilisé par le CSS pour la mettre en évidence).
 */
?>
<nav class="nav-principale" aria-label="Navigation principale">
    <!-- role="list" : Safari retire la sémantique de liste quand on supprime les puces en CSS -->
    <ul class="nav-principale__liste" id="nav-principale-liste" role="list">
<?php foreach ($site['navigation'] as $lien): ?>
        <li>
            <a class="nav-principale__lien" href="<?= e(url($lien['url'])) ?>"<?= $lien['id'] === $pageId ? ' aria-current="page"' : '' ?>><?= e($lien['label']) ?></a>
        </li>
<?php endforeach; ?>
    </ul>
</nav>
