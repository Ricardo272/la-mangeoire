/**
 * Scripts du tableau de bord.
 * Tout fonctionne sans JavaScript : ces scripts ajoutent seulement du confort.
 */

/* ==========================================================================
   Confirmation avant une action irréversible (supprimer, annuler…)
   Sur les formulaires qui portent l'attribut data-confirmer="Question ?"
   ========================================================================== */
document.querySelectorAll('form[data-confirmer]').forEach((formulaire) => {
    formulaire.addEventListener('submit', (evenement) => {
        if (!window.confirm(formulaire.dataset.confirmer)) {
            evenement.preventDefault();
        }
    });
});


/* ==========================================================================
   Ardoise : l'aperçu se met à jour pendant la frappe
   ========================================================================== */
{
    const formulaire = document.querySelector('[data-ardoise-formulaire]');
    const apercu = document.querySelector('[data-ardoise-apercu]');

    if (formulaire && apercu) {
        const champ = (nom) => formulaire.elements[nom];
        const zone = (classe) => apercu.querySelector(classe);
        const formatDate = new Intl.DateTimeFormat('fr-FR', { weekday: 'long', day: 'numeric', month: 'long' });

        const mettreAJour = () => {
            zone('.ardoise__plat').textContent = champ('plat').value.trim() || 'Le plat du jour';

            const description = champ('description').value.trim();
            zone('.ardoise__description').textContent = description;
            zone('.ardoise__description').hidden = description === '';

            // « 18.5 » ou « 18,50 € » → « 18,5 € » : on garde la virgule française
            let prix = champ('prix').value.trim().replace('.', ',').replace(/\s*€$/, '');
            if (/,\d$/.test(prix)) {
                prix += '0'; // « 18,5 » → « 18,50 », comme sur le site
            }
            zone('.ardoise__prix').textContent = (prix || '—') + ' €';

            if (champ('date').value) {
                const [annee, mois, jour] = champ('date').value.split('-').map(Number);
                const texte = formatDate.format(new Date(annee, mois - 1, jour));
                zone('.ardoise__date').textContent = texte.charAt(0).toUpperCase() + texte.slice(1);
            }
        };

        // « input » : à chaque caractère tapé, dans n'importe quel champ du formulaire
        formulaire.addEventListener('input', mettreAJour);
        mettreAJour();
    }
}
