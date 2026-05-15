<?php
/**
 * includes/header.php
 * =====================================================
 * Ce fichier est inclus EN HAUT de chaque page de l'app.
 * Il contient :
 *   - L'ouverture du HTML, le <head> avec Bootstrap 5
 *   - Le CSS personnalisé FamiCare
 *   - La barre de navigation avec le logo et les menus
 *
 * Comment l'utiliser dans une page :
 *   $page_titre = 'Mon titre';
 *   require_once __DIR__ . '/../includes/header.php';
 * =====================================================
 */

// Sécurité : vérifier que config.php est chargé
if (!defined('BASE_URL')) {
    require_once __DIR__ . '/../config.php';
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Titre de la page (défini dans chaque page avant d'inclure ce fichier) -->
    <title>
        <?= isset($page_titre) ? htmlspecialchars($page_titre) . ' — FamiCare' : 'FamiCare Évaluation' ?>
    </title>

    <!-- ============================================
         BOOTSTRAP 5 — Framework CSS (CDN)
         Donne les classes : btn, container, row, col...
    ============================================ -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- ============================================
         GOOGLE FONTS — Polices FamiCare
         DM Sans  = texte courant (lisible, moderne)
         Cormorant Garamond = titres (élégant, serif)
    ============================================ -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@600;700&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">

    <!-- ============================================
         CSS PERSONNALISÉ FAMICARE
         Doit être APRÈS Bootstrap pour pouvoir
         écraser ses styles si besoin
    ============================================ -->
    <link href="<?= BASE_URL ?>assets/css/style.css" rel="stylesheet">
</head>
<body>

<!-- ================================================
     BARRE DE NAVIGATION FAMICARE
     Affichée sur toutes les pages sauf index.php
     Le menu change selon le rôle (admin / intervenante)
================================================ -->
<nav class="fc-navbar">
    <div class="fc-navbar-inner">

        <!-- LOGO -->
        <a href="<?= BASE_URL ?>" class="fc-navbar-logo">
            <img src="<?= BASE_URL ?>assets/images/monlogo.png"
                 alt="FamiCare"
                 class="fc-logo-img">
        </a>

        <!-- LIENS DU MENU (visibles seulement si connecté) -->
        <?php if (isset($_SESSION['utilisateur'])): ?>

            <div class="fc-navbar-links">

                <!-- Menu ADMIN -->
                <?php if ($_SESSION['utilisateur']['role'] === 'admin'): ?>

                    <a href="<?= BASE_URL ?>admin/dashboard.php"
                       class="fc-nav-link <?= basename($_SERVER['PHP_SELF']) === 'dashboard.php' ? 'active' : '' ?>">
                        Tableau de bord
                    </a>

                    <a href="<?= BASE_URL ?>admin/tests.php"
                       class="fc-nav-link <?= basename($_SERVER['PHP_SELF']) === 'tests.php' ? 'active' : '' ?>">
                        Gérer les tests
                    </a>

                    <a href="<?= BASE_URL ?>admin/utilisateurs.php"
                       class="fc-nav-link <?= basename($_SERVER['PHP_SELF']) === 'utilisateurs.php' ? 'active' : '' ?>">
                        Utilisateurs
                    </a>

                <!-- Menu INTERVENANTE -->
                <?php else: ?>

                    <a href="<?= BASE_URL ?>intervenante/accueil.php"
                       class="fc-nav-link <?= basename($_SERVER['PHP_SELF']) === 'accueil.php' ? 'active' : '' ?>">
                        Mes tests
                    </a>

                    <a href="<?= BASE_URL ?>intervenante/mes-resultats.php"
                       class="fc-nav-link <?= basename($_SERVER['PHP_SELF']) === 'mes-resultats.php' ? 'active' : '' ?>">
                        Mes résultats
                    </a>

                <?php endif; ?>

            </div><!-- fin .fc-navbar-links -->

            <!-- PRÉNOM + BOUTON DÉCONNEXION -->
            <div class="fc-navbar-right">
                <span class="fc-nav-user">
                    👤 <?= htmlspecialchars($_SESSION['utilisateur']['prenom']) ?>
                </span>
                <a href="<?= BASE_URL ?>logout.php" class="fc-btn-logout">
                    Déconnexion
                </a>
            </div>

        <?php endif; ?>

    </div><!-- fin .fc-navbar-inner -->
</nav>
<!-- ================================================
     FIN NAVBAR
================================================ -->

<!-- Ouverture du contenu principal -->
<main class="fc-main"></main>