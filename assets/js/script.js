/**
 * assets/js/script.js
 * =====================================================
 * JavaScript global FamiCare
 * Chargé sur TOUTES les pages (via footer.php)
 *
 * Fonctions ajoutées progressivement :
 *  - Semaine 4 : utilitaires de base (lien actif etc.)
 *  - Semaine 5 : ajout dynamique de questions
 *  - Semaine 6 : navigation entre questions du test
 *  - Semaine 7 : confirmation avant soumission
 * =====================================================
 */

console.log('✅ FamiCare — JavaScript chargé');

/* =====================================================
   LIEN ACTIF DANS LA NAVBAR
   Compare l'URL actuelle avec le href de chaque lien
   et ajoute la classe "active" si c'est la même page
===================================================== */
document.addEventListener('DOMContentLoaded', function () {

    const liens = document.querySelectorAll('.fc-nav-link');

    liens.forEach(function(lien) {
        // Si l'URL actuelle contient le nom du fichier du lien
        if (window.location.href.indexOf(lien.getAttribute('href')) !== -1) {
            lien.classList.add('active');
        }
    });

});

/* =====================================================
   FERMETURE AUTOMATIQUE DES ALERTES
   Les alertes disparaissent après 5 secondes
===================================================== */
document.addEventListener('DOMContentLoaded', function () {

    const alertes = document.querySelectorAll('.fc-alert');

    alertes.forEach(function(alerte) {
        setTimeout(function() {
            alerte.style.transition = 'opacity 0.5s ease';
            alerte.style.opacity = '0';
            setTimeout(function() {
                alerte.remove();
            }, 500);
        }, 5000); // 5 secondes
    });

});

/* =====================================================
   CONFIRMATION AVANT SUPPRESSION
   Appeler cette fonction sur les boutons "Supprimer"
   Exemple : onclick="confirmerSuppression(event)"
===================================================== */
function confirmerSuppression(event) {
    if (!confirm('Êtes-vous sûr de vouloir supprimer cet élément ?\nCette action est irréversible.')) {
        event.preventDefault(); // Annule l'action si l'utilisateur clique "Non"
    }
}

/* =====================================================
   FONCTIONS UTILITAIRES
   (seront utilisées dans les semaines suivantes)
===================================================== */

/**
 * Affiche un message de succès temporaire
 * Usage : afficherSucces('Test créé avec succès !');
 */
function afficherSucces(message) {
    const alerte = document.createElement('div');
    alerte.className = 'fc-alert fc-alert-success';
    alerte.textContent = '✅ ' + message;
    document.querySelector('.fc-main').prepend(alerte);

    setTimeout(function() {
        alerte.style.opacity = '0';
        setTimeout(() => alerte.remove(), 500);
    }, 4000);
}

/**
 * Affiche un message d'erreur temporaire
 * Usage : afficherErreur('Champ obligatoire manquant');
 */
function afficherErreur(message) {
    const alerte = document.createElement('div');
    alerte.className = 'fc-alert fc-alert-error';
    alerte.textContent = '❌ ' + message;
    document.querySelector('.fc-main').prepend(alerte);
}