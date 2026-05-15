<?php
/**
 * includes/auth.php
 * ================================================
 * Fonctions de sécurité de l'application FamiCare.
 *
 * À inclure EN PREMIER dans chaque page protégée.
 *
 * Fonctions disponibles :
 *   verif_connexion()     → redirige vers login si non connecté
 *   verif_role($role)     → redirige si mauvais rôle
 *   est_admin()           → retourne true si admin
 *   est_intervenante()    → retourne true si intervenante
 * ================================================
 */

// Démarrer la session si pas déjà démarrée
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Charger la config si pas déjà chargée
if (!defined('BASE_URL')) {
    require_once __DIR__ . '/../config.php';
}

/**
 * verif_connexion()
 * -----------------
 * Vérifie que l'utilisateur est connecté.
 * Si non connecté → redirige vers la page de login.
 *
 * Usage : verif_connexion(); // en haut de chaque page protégée
 */
function verif_connexion() {
    if (!isset($_SESSION['utilisateur'])) {
        // Sauvegarder la page demandée pour y revenir après connexion
        $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
        header('Location: ' . BASE_URL . 'index.php');
        exit;
    }
}

/**
 * verif_role($role_requis)
 * ------------------------
 * Vérifie que l'utilisateur a le bon rôle.
 * Si mauvais rôle → affiche une page 403 (accès refusé).
 *
 * Usage : verif_role('admin');        // page réservée aux admins
 *         verif_role('intervenante'); // page réservée aux intervenantes
 *
 * @param string $role_requis  'admin' ou 'intervenante'
 */
function verif_role(string $role_requis) {
    // D'abord vérifier que l'utilisateur est connecté
    verif_connexion();

    // Vérifier le rôle
    if ($_SESSION['utilisateur']['role'] !== $role_requis) {
        http_response_code(403);
        ?>
        <!DOCTYPE html>
        <html lang="fr">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Accès refusé — FamiCare</title>
            <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@700&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
            <style>
                * { margin:0; padding:0; box-sizing:border-box; }
                body {
                    font-family: 'DM Sans', sans-serif;
                    background: #F5F0E8;
                    display: flex; align-items: center; justify-content: center;
                    min-height: 100vh;
                    color: #1B2B3A;
                }
                .error-box {
                    text-align: center;
                    background: #fff;
                    border-radius: 24px;
                    padding: 56px 48px;
                    max-width: 440px;
                    box-shadow: 0 8px 32px rgba(27,43,58,.1);
                }
                .error-code {
                    font-family: 'Cormorant Garamond', serif;
                    font-size: 80px;
                    font-weight: 700;
                    color: #E8846A;
                    line-height: 1;
                    margin-bottom: 16px;
                }
                .error-title {
                    font-family: 'Cormorant Garamond', serif;
                    font-size: 28px;
                    font-weight: 700;
                    margin-bottom: 12px;
                }
                .error-msg {
                    font-size: 14px;
                    font-weight: 300;
                    color: #8A9BAD;
                    line-height: 1.7;
                    margin-bottom: 32px;
                }
                .error-btn {
                    display: inline-block;
                    background: #E8846A;
                    color: #fff;
                    padding: 12px 28px;
                    border-radius: 50px;
                    text-decoration: none;
                    font-size: 14px;
                    font-weight: 500;
                    transition: background .2s;
                }
                .error-btn:hover { background: #D06A52; }
            </style>
        </head>
        <body>
            <div class="error-box">
                <div class="error-code">403</div>
                <h1 class="error-title">Accès refusé</h1>
                <p class="error-msg">
                    Vous n'avez pas les droits nécessaires pour accéder à cette page.<br>
                    Votre rôle : <strong><?= htmlspecialchars($_SESSION['utilisateur']['role']) ?></strong>
                </p>
                <a href="<?= BASE_URL ?>" class="error-btn">← Retour à l'accueil</a>
            </div>
        </body>
        </html>
        <?php
        exit;
    }
}

/**
 * est_admin()
 * -----------
 * Retourne true si l'utilisateur connecté est admin.
 * Utile pour afficher/cacher des éléments dans les vues.
 */
function est_admin(): bool {
    return isset($_SESSION['utilisateur'])
        && $_SESSION['utilisateur']['role'] === 'admin';
}

/**
 * est_intervenante()
 * ------------------
 * Retourne true si l'utilisateur connecté est intervenante.
 */
function est_intervenante(): bool {
    return isset($_SESSION['utilisateur'])
        && $_SESSION['utilisateur']['role'] === 'intervenante';
}

/**
 * utilisateur_connecte()
 * ----------------------
 * Retourne les données de l'utilisateur connecté.
 * Retourne null si non connecté.
 */
function utilisateur_connecte(): ?array {
    return $_SESSION['utilisateur'] ?? null;
}