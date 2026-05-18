<?php
/**
 * includes/footer.php
 * Pied de page commun — Charte FamiCare officielle
 */
?>

</main>

<!-- FOOTER -->
<footer class="fc-footer">
    <div class="fc-footer-inner">
        <div class="fc-footer-logo">
            <img src="<?= BASE_URL ?>assets/images/monlogo.png"
                 alt="FamiCare"
                 style="height:28px;opacity:0.7;">
        </div>
        <p class="fc-footer-text">
            © <?= date('Y') ?> FamiCare — Application d'évaluation des intervenantes
        </p>
        <p class="fc-footer-sub">
            Développé dans le cadre d'un stage L3 Informatique
        </p>
    </div>
</footer>

<!-- Bootstrap 5 JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<!-- JS FamiCare -->
<script src="<?= BASE_URL ?>assets/js/script.js"></script>

</body>
</html>