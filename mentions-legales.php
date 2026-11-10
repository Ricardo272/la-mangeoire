<?php
// Page « Mentions légales »

$pageId          = 'mentions';
$pageTitle       = 'Mentions légales';
$pageDescription = 'Mentions légales, données personnelles et cookies du site de La Mangeoire.';

require_once __DIR__ . '/includes/head.php';
require_once __DIR__ . '/includes/header.php';
?>

<main id="contenu">
    <section class="section">
        <div class="conteneur conteneur--etroit prose">
            <p class="surtitre">Informations</p>
            <h1>Mentions légales</h1>

            <div class="encadre">
                <p>
                    <strong><?= e($site['nom']) ?> est un bistrot fictif.</strong>
                    Ce site a été réalisé par Ricardo Remond dans le cadre de son portfolio
                    de développeur web. Les personnes, l'adresse et le numéro de téléphone
                    sont inventés : aucun repas ne sera servi à la suite d'une réservation.
                </p>
            </div>

            <h2>Éditeur du site</h2>
            <p>
                Ricardo Remond, développeur web.<br>
                Directeur de la publication : Ricardo Remond.<br>
                Code source et contact :
                <a href="https://github.com/Ricardo272/la-mangeoire" rel="noopener">github.com/Ricardo272/la-mangeoire</a>.
            </p>

            <h2>Hébergement</h2>
            <p>
                ALWAYSDATA, SARL au capital de 200 000 €, RCS Paris 492 893 490<br>
                91, rue du Faubourg Saint-Honoré, 75008 Paris<br>
                <a href="https://www.alwaysdata.com" rel="noopener">www.alwaysdata.com</a>
                · Téléphone : <em>à compléter</em>
            </p>

            <h2>Propriété intellectuelle</h2>
            <p>
                Les textes, la mise en page et le code du site sont la propriété de leur auteur.
                Les polices DM Serif Display, Work Sans et Caveat sont distribuées sous licence
                libre SIL Open Font License 1.1.
            </p>
            <p>
                Photographies sous <a href="https://unsplash.com/license" rel="noopener">licence Unsplash</a>,
                d'autres lieux et d'autres personnes que ceux de cette histoire fictive :
            </p>
            <ul>
                <li>Terrasse (accueil) : <a href="https://unsplash.com/photos/o49uRfALSiI" rel="noopener">Megan Bucknall</a></li>
                <li>Le zinc : <a href="https://unsplash.com/photos/aaCAWEAFyGk" rel="noopener">Oliver Frsh</a></li>
                <li>La cocotte : <a href="https://unsplash.com/photos/s6BrlKXy7e0" rel="noopener">Odiseo Castrejon</a></li>
                <li>Le vin servi : <a href="https://unsplash.com/photos/etWlaoFnTl4" rel="noopener">Lefteris Kallergis</a></li>
                <li>Le dressage : <a href="https://unsplash.com/photos/q4nWGwVM2Vk" rel="noopener">Alfonso Betancourt</a></li>
            </ul>
            <p>
                Les toiles des habitués sont en attente d'illustrations.
            </p>

            <h2>Données personnelles</h2>
            <p>
                Le formulaire de réservation recueille votre nom, votre téléphone, votre
                adresse e-mail, la date, l'heure, le nombre de couverts et, si vous le
                souhaitez, un message.
            </p>
            <ul>
                <li>
                    <strong>Pourquoi :</strong> uniquement pour traiter votre demande de
                    réservation et vous recontacter pour la confirmer.
                </li>
                <li>
                    <strong>Sur quelle base :</strong> votre demande (mesures préalables à un
                    contrat, article 6.1.b du RGPD).
                </li>
                <li>
                    <strong>Qui y a accès :</strong> le restaurant seulement. Vos données ne sont
                    ni vendues ni transmises à des tiers.
                </li>
                <li>
                    <strong>Combien de temps :</strong> trois mois après la date du repas,
                    puis elles sont supprimées.
                </li>
            </ul>
            <p>
                Vous pouvez à tout moment demander l'accès à vos données, leur rectification
                ou leur suppression, ou vous opposer à leur traitement, en écrivant à
                <a href="mailto:<?= e($site['email']) ?>"><?= e($site['email']) ?></a>.
                Vous pouvez aussi adresser une réclamation à la CNIL
                (<a href="https://www.cnil.fr" rel="noopener">cnil.fr</a>).
            </p>

            <h2>Cookies</h2>
            <p>
                Le site public ne dépose <strong>aucun cookie</strong> et n'utilise aucun outil de
                mesure d'audience ni de publicité : c'est pourquoi il n'affiche pas de bandeau
                de consentement. Les polices sont hébergées sur le site lui-même, aucune donnée
                n'est donc transmise à un service tiers lors de votre visite.
            </p>
            <p>
                Seul l'espace d'administration, réservé au restaurant, utilise un cookie de
                session strictement nécessaire à la connexion.
            </p>

            <p class="prose__maj">Dernière mise à jour : novembre 2026.</p>
        </div>
    </section>
</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
