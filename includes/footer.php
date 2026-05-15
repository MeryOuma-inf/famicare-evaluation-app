<?php
/**
 * includes/footer.php
 * =====================================================
 * Ce fichier est inclus EN BAS de chaque page de l'app.
 * Il contient :
 *   - La fermeture du <main>
 *   - Le pied de page avec copyright
 *   - Bootstrap 5 JS (pour menus mobiles, modales...)
 *   - Chart.js (pour les graphiques du tableau de bord)
 *   - Notre JS personnalisé FamiCare
 *   - La fermeture de </body> et </html>
 *
 * Comment l'utiliser dans une page :
 *   require_once __DIR__ . '/../includes/footer.php';
 *   (toujours à la toute fin de la page)
 * =====================================================
 */
?>

</main><!-- Fermeture de .fc-main ouvert dans header.php -->

<!-- ================================================
     PIED DE PAGE FAMICARE
================================================ -->
<footer class="fc-footer">
    <div class="fc-footer-inner">

        <div class="fc-footer-logo">
            <img src="<?= BASE_URL ?>assets/images/monlogo.png"
                 alt="FamiCare"
                 style="height:28px; opacity:0.6;">
        </div>

        <p class="fc-footer-text">
            © <?= date('Y') ?> FamiCare — Application d'évaluation des intervenantes
        </p>
        <p class="fc-footer-sub">
            Développé dans le cadre d'un stage L3 Informatique
        </p>

    </div>
</footer>
<!-- ================================================
     FIN PIED DE PAGE
================================================ -->


<!-- ================================================
     SCRIPTS JAVASCRIPT — chargés EN BAS de page
     (bonne pratique : la page s'affiche plus vite)
================================================ -->

<!-- Bootstrap 5 JS : nécessaire pour les menus mobiles,
     les modales, les dropdowns Bootstrap -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<!-- Chart.js : pour les graphiques du tableau de bord admin
     (sera utilisé en semaine 8) -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<!-- Notre JavaScript personnalisé FamiCare
     (fonctions réutilisables dans toutes les pages) -->
<script src="<?= BASE_URL ?>assets/js/script.js"></script>

</body>
</html>