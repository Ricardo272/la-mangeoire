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


/* ==========================================================================
   Agrandissement des toiles (page « Notre histoire »)
   Chaque toile est enveloppée dans un bouton ; un clic ouvre la fenêtre
   <dialog> avec l'image et sa légende. Échap ferme (géré par le navigateur),
   un clic sur le voile aussi, et le focus revient sur la toile ouverte.
   ========================================================================== */
{
    const dialogue = document.getElementById('agrandissement');
    const toiles = document.querySelectorAll('.toile');

    if (dialogue && toiles.length && typeof dialogue.showModal === 'function') {
        const image = document.createElement('img');
        image.className = 'agrandissement__image';
        image.alt = ''; // remplacé par la description de la toile à l'ouverture
        dialogue.querySelector('.agrandissement__contenu').prepend(image);
        const legende = dialogue.querySelector('.agrandissement__legende');
        let declencheur = null;

        toiles.forEach((toile) => {
            const img = toile.querySelector('img');
            const legendeToile = toile.querySelector('figcaption');
            const titre = toile.querySelector('cite')?.textContent ?? img.alt;

            // Le bouton est créé ici : sans JavaScript, pas de bouton inutile
            const bouton = document.createElement('button');
            bouton.type = 'button';
            bouton.className = 'toile__bouton';
            bouton.setAttribute('aria-label', `Agrandir « ${titre} »`);
            img.replaceWith(bouton);
            bouton.append(img);

            bouton.addEventListener('click', () => {
                image.src = img.currentSrc || img.src;
                image.alt = img.alt;
                // Dimensions réelles de la toile : indispensables pour une image SVG
                image.width = img.getAttribute('width');
                image.height = img.getAttribute('height');
                // Copie de la légende (cloneNode : pas de HTML injecté à la main)
                legende.replaceChildren(...legendeToile.cloneNode(true).childNodes);
                declencheur = bouton;
                dialogue.showModal();
            });
        });

        // Clic sur le voile : la cible est la fenêtre elle-même, pas son contenu
        dialogue.addEventListener('click', (evenement) => {
            if (evenement.target === dialogue) {
                dialogue.close();
            }
        });

        dialogue.addEventListener('close', () => declencheur?.focus());
    }
}


/* ==========================================================================
   Animations au défilement : filets dorés et titres de section
   IntersectionObserver prévient quand un élément entre à l'écran, sans
   surveiller le défilement en continu (plus léger pour le navigateur).
   ========================================================================== */
{
    const moinsDAnimations = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    if (!moinsDAnimations && 'IntersectionObserver' in window) {
        const filets = document.querySelectorAll('main .filet');
        const titres = document.querySelectorAll('main h2:not(.visuellement-cache):not(.formule__nom)');

        filets.forEach((filet) => filet.classList.add('anime-filet'));
        titres.forEach((titre) => titre.classList.add('anime-titre'));

        const observateur = new IntersectionObserver((entrees) => {
            entrees.forEach((entree) => {
                if (entree.isIntersecting) {
                    entree.target.classList.add('est-visible');
                    observateur.unobserve(entree.target); // une seule fois
                }
            });
        }, { rootMargin: '0px 0px -10% 0px' });

        [...filets, ...titres].forEach((element) => observateur.observe(element));
    }
}


/* ==========================================================================
   Carte : rubrique en cours mise en évidence dans la barre des rubriques
   Une rubrique est « en cours » quand elle traverse le milieu de l'écran.
   ========================================================================== */
{
    const liens = document.querySelectorAll('.carte-rubriques a');

    if (liens.length && 'IntersectionObserver' in window) {
        const lienPour = (id) => document.querySelector(`.carte-rubriques a[href="#${id}"]`);

        const observateur = new IntersectionObserver((entrees) => {
            entrees.forEach((entree) => {
                if (!entree.isIntersecting) {
                    return;
                }
                liens.forEach((lien) => lien.removeAttribute('aria-current'));
                const lien = lienPour(entree.target.id);
                if (lien) {
                    lien.setAttribute('aria-current', 'true');
                    // Sur mobile, la barre défile pour garder la rubrique active visible
                    lien.scrollIntoView({ block: 'nearest', inline: 'nearest' });
                }
            });
        }, { rootMargin: '-45% 0px -50% 0px' }); // une bande fine au milieu de l'écran

        document.querySelectorAll('.carte-rubrique').forEach((rubrique) => observateur.observe(rubrique));
    }
}


/* ==========================================================================
   Bouton « Imprimer la carte »
   ========================================================================== */
document.querySelectorAll('[data-imprimer]').forEach((bouton) => {
    bouton.addEventListener('click', () => window.print());
});
