/**
 * Scripts communs du site.
 * Chargé avec « defer » : il s'exécute une fois le HTML entièrement lu.
 */

/* ==========================================================================
   Menu burger
   Le script ne fait qu'une chose : changer aria-expanded sur le bouton.
   Le CSS lit cet attribut pour ouvrir ou fermer le menu, et les lecteurs
   d'écran l'annoncent (« Menu, réduit » / « Menu, développé »).
   ========================================================================== */
{
    const bouton = document.querySelector('.nav-bouton');

    if (bouton) {
        const menu = document.getElementById(bouton.getAttribute('aria-controls'));
        const estOuvert = () => bouton.getAttribute('aria-expanded') === 'true';
        const basculer = (ouvrir) => bouton.setAttribute('aria-expanded', String(ouvrir));

        // Clic sur le bouton : ouvre ou ferme
        bouton.addEventListener('click', () => basculer(!estOuvert()));

        // Échap : ferme, et rend le focus au bouton pour ne pas perdre l'utilisateur clavier
        document.addEventListener('keydown', (evenement) => {
            if (evenement.key === 'Escape' && estOuvert()) {
                basculer(false);
                bouton.focus();
            }
        });

        // Clic sur un lien du menu : on ferme (utile pour les liens vers une ancre)
        menu.addEventListener('click', (evenement) => {
            if (evenement.target.closest('a')) {
                basculer(false);
            }
        });
    }
}


/* ==========================================================================
   Formulaire de réservation : aide immédiate pendant la saisie
   Le serveur revérifie tout ; ce script ne fait que prévenir plus tôt :
     - un jour de fermeture est signalé dès qu'on le choisit ;
     - le jour même, les horaires trop proches sont grisés.
   ========================================================================== */
{
    const champDate = document.getElementById('champ-date');
    const champHeure = document.getElementById('champ-heure');

    if (champDate && champHeure) {
        const joursOuverts = JSON.parse(champDate.dataset.joursOuverts); // ex. [2, 3, 4, 5, 6]
        const messageFerme = champDate.dataset.messageFerme;
        const delaiMinutes = Number(champDate.dataset.delai);
        const aide = document.getElementById('aide-date');
        const aideInitiale = aide.textContent;

        const verifierDate = () => {
            if (!champDate.value) {
                champDate.setCustomValidity('');
                aide.textContent = aideInitiale;
                return;
            }

            // « 2026-11-10 » → date locale (sans décalage de fuseau)
            const [annee, mois, jour] = champDate.value.split('-').map(Number);
            const date = new Date(annee, mois - 1, jour);
            const jourSemaine = date.getDay() || 7; // getDay() : 0 = dimanche → 7

            // Jour de fermeture : message + champ marqué invalide pour le navigateur
            const ferme = !joursOuverts.includes(jourSemaine);
            champDate.setCustomValidity(ferme ? messageFerme : '');
            aide.textContent = ferme ? messageFerme : aideInitiale;

            // Le jour même : on grise les horaires qui commencent dans moins d'une heure
            const maintenant = new Date();
            const estAujourdhui = date.toDateString() === maintenant.toDateString();
            const limite = maintenant.getHours() * 60 + maintenant.getMinutes() + delaiMinutes;

            for (const option of champHeure.querySelectorAll('option[value]:not([value=""])')) {
                const [h, m] = option.value.split(':').map(Number);
                option.disabled = estAujourdhui && h * 60 + m < limite;
                if (option.disabled && option.selected) {
                    champHeure.value = '';
                }
            }
        };

        champDate.addEventListener('change', verifierDate);
        verifierDate();
    }
}
