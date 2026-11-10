<?php
// Page « Venir nous voir » : infos pratiques, plan, réservation, contact

$pageId          = 'venir';
$pageTitle       = 'Venir nous voir';
$pageDescription = 'Adresse, horaires, plan d\'accès et réservation en ligne à La Mangeoire, '
                 . 'bistrot de Montmartre à trois minutes du métro Abbesses.';
$pageCss         = 'venir';

require_once __DIR__ . '/includes/reservation.php';
require_once __DIR__ . '/includes/formulaire.php';

/* --------------------------------------------------------------------------
   Traitement du formulaire
   Schéma « Post / Redirect / Get » :
     1. le navigateur ENVOIE le formulaire (POST) ;
     2. si tout est bon, on enregistre puis on REDIRIGE vers cette page (GET) ;
     3. rafraîchir la page de confirmation ne renvoie donc pas la demande.
   S'il y a des erreurs, pas de redirection : on réaffiche le formulaire rempli.
   -------------------------------------------------------------------------- */
$valeurs = reservationVide();
$erreurs = [];
$succes  = ($_GET['reservation'] ?? '') === 'ok';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    [$valeurs, $erreurs, $spam] = verifierReservation($_POST, $site['horaires']);

    if ($spam) {
        // Un robot : on fait comme si tout allait bien, sans rien enregistrer.
        // Inutile de lui indiquer qu'il a été repéré.
        $erreurs = [];
    } elseif (!$erreurs && !enregistrerReservation($valeurs)) {
        $erreurs['formulaire'] = 'Un souci technique nous empêche d\'enregistrer votre demande. '
                               . 'Appelez-nous au ' . $site['telephone'] . ', nous la noterons au téléphone.';
    }

    if (!$erreurs) {
        // 303 « See Other » : la réponse classique après un envoi de formulaire réussi
        header('Location: ' . url('venir-nous-voir.php?reservation=ok#reservation'), true, 303);
        exit;
    }
}

// Jeton anti-robots du formulaire (nécessite la clé secrète de config.local.php)
try {
    $jeton = creerJeton();
} catch (Throwable $e) {
    error_log('[La Mangeoire] jeton : ' . $e->getMessage());
    $jeton = null; // le formulaire sera remplacé par un message « appelez-nous »
}

$creneaux     = creneauxAffiches($site['horaires']);
$aujourdhui   = new DateTimeImmutable('today');
$dateMax      = $aujourdhui->modify(RESERVATION_HORIZON);
$joursOuverts = array_keys(array_filter($site['horaires'])); // ex. [2, 3, 4, 5, 6]
$horaires     = resumerHoraires($site['horaires']);

// Libellés des champs, réutilisés dans le résumé des erreurs
$libelles = [
    'formulaire' => null,
    'nom'        => 'Nom',
    'telephone'  => 'Téléphone',
    'email'      => 'E-mail',
    'date'       => 'Date',
    'heure'      => 'Horaire',
    'couverts'   => 'Nombre de personnes',
    'message'    => 'Message',
];

require_once __DIR__ . '/includes/head.php';
require_once __DIR__ . '/includes/header.php';
?>

<main id="contenu">

    <!-- ===================================================== Introduction -->
    <section class="section venir-intro">
        <div class="conteneur conteneur--etroit">
            <p class="surtitre surtitre--centre">Venir nous voir</p>
            <h1>Poussez la porte</h1>
            <p class="venir-intro__chapeau">
                Au pied de la Butte, à trois minutes du métro Abbesses.
                Réservez en ligne, ou passez simplement : il reste souvent
                une place au zinc.
            </p>
        </div>
    </section>

    <!-- ===================================================== Infos + plan -->
    <section class="section venir-infos" aria-labelledby="titre-infos">
        <div class="conteneur venir-infos__grille">

            <div class="infos">
                <h2 id="titre-infos" class="visuellement-cache">Infos pratiques</h2>

                <div class="infos__bloc">
                    <h3 class="infos__titre">Adresse</h3>
                    <address>
                        <?= e($site['adresse']['rue']) ?><br>
                        <?= e($site['adresse']['code_postal'] . ' ' . $site['adresse']['ville']) ?><br>
                        Métro <?= e($site['metro']) ?>
                    </address>
                </div>

                <div class="infos__bloc">
                    <h3 class="infos__titre">Horaires</h3>
                    <?php $badgeClair = true; require __DIR__ . '/includes/badge-ouverture.php'; ?>
                    <ul class="infos__liste" role="list">
<?php foreach ($horaires['ouvertures'] as $ligne): ?>
                        <li><?= e($ligne['jours']) ?> : <?= e(implode(' et ', $ligne['creneaux'])) ?></li>
<?php endforeach; ?>
                        <li><?= e($horaires['fermeture']) ?></li>
                    </ul>
                </div>

                <div class="infos__bloc">
                    <h3 class="infos__titre">Nous joindre</h3>
                    <p>
                        <a href="tel:<?= e(lienTelephone($site['telephone'])) ?>"><?= e($site['telephone']) ?></a><br>
                        <a href="mailto:<?= e($site['email']) ?>"><?= e($site['email']) ?></a>
                    </p>
                </div>
            </div>

            <!-- Plan illustré, dessiné en SVG (plan stylisé : la rue est fictive) -->
            <figure class="plan-acces">
                <svg class="plan" viewBox="0 0 600 440" role="img" aria-labelledby="plan-titre plan-desc">
                    <title id="plan-titre">Plan d'accès à La Mangeoire</title>
                    <desc id="plan-desc">
                        Depuis la station de métro Abbesses, sur la place des Abbesses,
                        remonter la rue de la Butte-aux-Peintres : La Mangeoire est au numéro 8,
                        à trois minutes à pied.
                    </desc>

                    <defs>
                        <!-- Tracé des rues, réutilisé deux fois (bordure sombre puis chaussée claire) -->
                        <path id="rue-butte" d="M200 250 C300 210 380 170 560 60"/>
                        <path id="rue-2" d="M-10 360 L610 385"/>
                        <path id="rue-3" d="M120 -10 C140 100 160 180 175 250"/>
                        <path id="rue-4" d="M230 262 C300 330 330 380 360 450"/>
                        <path id="rue-5" d="M330 190 C360 120 380 60 390 -10"/>
                        <path id="rue-6" d="M-10 180 C60 200 110 220 150 238"/>
                        <path id="rue-7" d="M560 60 L610 40"/>
                    </defs>

                    <!-- Fond : les îlots, teinte sépia -->
                    <rect class="plan__fond" width="600" height="440"/>

                    <!-- 1. Bordures des rues (trait sombre large) -->
                    <g class="plan__rue-bord">
                        <use href="#rue-butte"/><use href="#rue-2"/><use href="#rue-3"/>
                        <use href="#rue-4"/><use href="#rue-5"/><use href="#rue-6"/><use href="#rue-7"/>
                    </g>
                    <!-- La place, avec son contour -->
                    <circle class="plan__place" cx="180" cy="248" r="50"/>
                    <!-- 2. Chaussées (trait clair un peu plus fin, par-dessus : il ne reste que les bords) -->
                    <g class="plan__rue">
                        <use href="#rue-butte"/><use href="#rue-2"/><use href="#rue-3"/>
                        <use href="#rue-4"/><use href="#rue-5"/><use href="#rue-6"/><use href="#rue-7"/>
                    </g>

                    <!-- Itinéraire à pied : du métro jusqu'au bistrot -->
                    <path class="plan__itineraire" d="M168 236 L200 250 C275 220 338.75 190 441.9 130.4"/>
                    <text class="plan__duree" x="300" y="245">3 min à pied</text>

                    <!-- Noms -->
                    <text class="plan__nom-rue" dy="-20"><textPath href="#rue-butte" startOffset="12%">Rue de la Butte-aux-Peintres</textPath></text>
                    <text class="plan__nom-place" x="180" y="268" text-anchor="middle">Place des Abbesses</text>
                    <text class="plan__repere" x="300" y="30" text-anchor="middle">↑ vers le Sacré-Cœur</text>

                    <!-- Métro -->
                    <g class="plan__metro">
                        <circle cx="165" cy="232" r="15"/>
                        <text x="165" y="238" text-anchor="middle">M</text>
                    </g>
                    <text class="plan__metro-nom" x="180" y="330" text-anchor="middle">Métro Abbesses · ligne 12</text>

                    <!-- La Mangeoire -->
                    <rect class="plan__bistrot" x="431" y="119" width="22" height="22" transform="rotate(45 442 130)"/>
                    <text class="plan__bistrot-nom" x="500" y="198" text-anchor="middle">La Mangeoire</text>
                    <text class="plan__bistrot-numero" x="500" y="220" text-anchor="middle">n° 8</text>

                    <!-- Rose des vents -->
                    <g class="plan__nord" transform="translate(548 400)">
                        <path d="M0 -24 L7 0 L0 -5 L-7 0 Z"/>
                        <text x="0" y="16" text-anchor="middle">N</text>
                    </g>

                    <!-- Double filet laiton en cadre -->
                    <rect class="plan__cadre" x="6" y="6" width="588" height="428"/>
                    <rect class="plan__cadre plan__cadre--fin" x="12" y="12" width="576" height="416"/>
                </svg>
                <figcaption>Plan du quartier (illustration)</figcaption>
            </figure>
        </div>
    </section>

    <!-- ===================================================== Réservation -->
    <section class="section venir-reservation" id="reservation" aria-labelledby="titre-reservation">
        <div class="conteneur conteneur--etroit">
            <div class="encadre reservation">
                <h2 id="titre-reservation">Réserver une table</h2>

<?php if ($succes): ?>
                <!-- role="status" : le lecteur d'écran annonce ce message dès son apparition -->
                <div class="message-succes" role="status">
                    <p class="message-succes__titre">Merci, votre demande est bien enregistrée.</p>
                    <p>
                        Nous vous rappelons au plus vite pour la confirmer.
                        En cas d'urgence : <a href="tel:<?= e(lienTelephone($site['telephone'])) ?>"><?= e($site['telephone']) ?></a>.
                    </p>
                    <p><a class="bouton bouton--contour" href="<?= e(url('venir-nous-voir.php#reservation')) ?>">Faire une autre demande</a></p>
                </div>

<?php elseif ($jeton === null): ?>
                <p>
                    La réservation en ligne est momentanément indisponible.
                    Appelez-nous au <a href="tel:<?= e(lienTelephone($site['telephone'])) ?>"><?= e($site['telephone']) ?></a>.
                </p>

<?php else: ?>
                <p class="reservation__intro">
                    Remplissez ce formulaire : nous vous rappelons pour confirmer.
                    Plus de <?= RESERVATION_COUVERTS_MAX ?> personnes ? Appelez-nous au
                    <a href="tel:<?= e(lienTelephone($site['telephone'])) ?>"><?= e($site['telephone']) ?></a>.
                </p>

<?php if (!empty($site['demo'])): ?>
                <p class="bandeau-demo">
                    <strong>Site de démonstration :</strong> La Mangeoire est un bistrot fictif,
                    aucune réservation n'est réellement prise. N'indiquez pas vos vraies coordonnées.
                </p>
<?php endif; ?>

<?php if ($erreurs): ?>
                <!-- role="alert" : annoncé immédiatement ; chaque lien mène au champ concerné -->
                <div class="resume-erreurs" role="alert">
                    <h3><?= count($erreurs) > 1 ? 'Quelques points à corriger' : 'Un point à corriger' ?></h3>
                    <ul>
<?php foreach ($erreurs as $champ => $message): ?>
<?php if ($libelles[$champ] ?? null): ?>
                        <li><a href="#champ-<?= e($champ) ?>"><?= e($libelles[$champ]) ?> : <?= e($message) ?></a></li>
<?php else: ?>
                        <li><?= e($message) ?></li>
<?php endif; ?>
<?php endforeach; ?>
                    </ul>
                </div>
<?php endif; ?>

                <form class="formulaire" method="post" action="<?= e(url('venir-nous-voir.php')) ?>#reservation">
                    <input type="hidden" name="jeton" value="<?= e($jeton) ?>">

                    <!-- Champ piège : invisible pour les humains, rempli par les robots -->
                    <div class="champ-piege" aria-hidden="true">
                        <label for="site_web">Ne pas remplir ce champ</label>
                        <input type="text" id="site_web" name="site_web" tabindex="-1" autocomplete="off">
                    </div>

                    <div class="champ">
                        <label class="champ__label" for="champ-nom">Nom</label>
                        <input class="champ__saisie" type="text" id="champ-nom" name="nom"
                               value="<?= e($valeurs['nom']) ?>" required minlength="2" maxlength="80"
                               autocomplete="name"<?= attributsChamp('nom', $erreurs) ?>>
                        <?= messageErreur('nom', $erreurs) ?>
                    </div>

                    <div class="formulaire__ligne">
                        <div class="champ">
                            <label class="champ__label" for="champ-telephone">Téléphone</label>
                            <input class="champ__saisie" type="tel" id="champ-telephone" name="telephone"
                                   value="<?= e($valeurs['telephone']) ?>" required maxlength="20"
                                   autocomplete="tel" inputmode="tel"<?= attributsChamp('telephone', $erreurs, 'aide-telephone') ?>>
                            <p class="champ__aide" id="aide-telephone">Pour vous rappeler et confirmer.</p>
                            <?= messageErreur('telephone', $erreurs) ?>
                        </div>

                        <div class="champ">
                            <label class="champ__label" for="champ-email">E-mail <span class="champ__facultatif">(facultatif)</span></label>
                            <input class="champ__saisie" type="email" id="champ-email" name="email"
                                   value="<?= e($valeurs['email']) ?>" maxlength="120"
                                   autocomplete="email"<?= attributsChamp('email', $erreurs) ?>>
                            <?= messageErreur('email', $erreurs) ?>
                        </div>
                    </div>

                    <div class="formulaire__ligne">
                        <div class="champ">
                            <label class="champ__label" for="champ-date">Date</label>
                            <!-- data-* : infos lues par le script pour prévenir des jours de fermeture -->
                            <input class="champ__saisie" type="date" id="champ-date" name="date"
                                   value="<?= e($valeurs['date']) ?>" required
                                   min="<?= $aujourdhui->format('Y-m-d') ?>" max="<?= $dateMax->format('Y-m-d') ?>"
                                   data-jours-ouverts="<?= e(json_encode($joursOuverts)) ?>"
                                   data-message-ferme="<?= e('Nous sommes fermés ce jour-là. ' . $horaires['fermeture'] . '.') ?>"
                                   data-delai="<?= RESERVATION_DELAI_MINUTES ?>"<?= attributsChamp('date', $erreurs, 'aide-date') ?>>
                            <p class="champ__aide" id="aide-date" aria-live="polite"><?= e($horaires['fermeture']) ?>.</p>
                            <?= messageErreur('date', $erreurs) ?>
                        </div>

                        <div class="champ">
                            <label class="champ__label" for="champ-heure">Horaire</label>
                            <select class="champ__saisie" id="champ-heure" name="heure" required<?= attributsChamp('heure', $erreurs) ?>>
                                <option value="">Choisir…</option>
<?php foreach ($creneaux as $service => $liste): ?>
                                <optgroup label="<?= e($service) ?>">
<?php foreach ($liste as $creneau): ?>
                                    <option value="<?= e($creneau) ?>"<?= $valeurs['heure'] === $creneau ? ' selected' : '' ?>><?= e(formaterHeure($creneau)) ?></option>
<?php endforeach; ?>
                                </optgroup>
<?php endforeach; ?>
                            </select>
                            <?= messageErreur('heure', $erreurs) ?>
                        </div>

                        <div class="champ">
                            <label class="champ__label" for="champ-couverts">Personnes</label>
                            <select class="champ__saisie" id="champ-couverts" name="couverts" required<?= attributsChamp('couverts', $erreurs) ?>>
<?php for ($n = 1; $n <= RESERVATION_COUVERTS_MAX; $n++): ?>
                                <option value="<?= $n ?>"<?= $valeurs['couverts'] === (string) $n ? ' selected' : '' ?>><?= $n ?></option>
<?php endfor; ?>
                            </select>
                            <?= messageErreur('couverts', $erreurs) ?>
                        </div>
                    </div>

                    <div class="champ">
                        <label class="champ__label" for="champ-message">Message <span class="champ__facultatif">(facultatif)</span></label>
                        <textarea class="champ__saisie" id="champ-message" name="message" maxlength="500"
                                  rows="4"<?= attributsChamp('message', $erreurs, 'aide-message') ?>><?= e($valeurs['message']) ?></textarea>
                        <p class="champ__aide" id="aide-message">Une allergie, une chaise haute, un anniversaire…</p>
                        <?= messageErreur('message', $erreurs) ?>
                    </div>

                    <div class="reservation__envoi">
                        <button class="bouton" type="submit">Envoyer ma demande</button>
                        <p class="champ__aide">
                            Vos informations servent uniquement à traiter votre réservation et sont
                            supprimées trois mois après le repas.
                            <a href="<?= e(url('mentions-legales.php')) ?>">En savoir plus</a>.
                        </p>
                    </div>
                </form>
<?php endif; ?>
            </div>
        </div>
    </section>

    <!-- ===================================================== Une question ? -->
    <section class="section venir-question" aria-labelledby="titre-question">
        <div class="conteneur conteneur--etroit">
            <hr class="filet filet--orne">
            <h2 id="titre-question">Une question ?</h2>
            <p>Une privatisation, un régime particulier, une question sur la carte : on vous répond volontiers.</p>
            <div class="groupe-boutons">
                <a class="bouton" href="tel:<?= e(lienTelephone($site['telephone'])) ?>">Appeler le <?= e($site['telephone']) ?></a>
                <a class="bouton bouton--contour" href="mailto:<?= e($site['email']) ?>">Nous écrire</a>
            </div>
        </div>
    </section>

</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
