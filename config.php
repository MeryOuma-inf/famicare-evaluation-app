<?php
/**
 * config.php — Configuration générale FamiCare
 * =====================================================
 * Constantes BDD + Email + URLs
 * NE JAMAIS COMMITTER ce fichier avec de vrais mots de passe !
 * =====================================================
 */

// ===== BASE DE DONNÉES =====
define('DB_HOST',    'localhost');
define('DB_NAME',    'famicare_evaluation');
define('DB_USER',    'root');
define('DB_PASS',    '');
define('DB_CHARSET', 'utf8mb4');

// ===== URL DE BASE =====
define('BASE_URL', 'http://localhost/famicare/');

// ===== CONFIGURATION EMAIL (Mailtrap pour les tests) =====
// 1. Crée un compte gratuit sur https://mailtrap.io
// 2. Va dans Email Testing → Inboxes → Mon Inbox → SMTP Settings
// 3. Copie les identifiants ici

define('MAIL_HOST',      'sandbox.smtp.mailtrap.io'); // Mailtrap SMTP
define('MAIL_USER',      'TON_USERNAME_MAILTRAP');    // ← à remplacer
define('MAIL_PASS',      'TON_PASSWORD_MAILTRAP');    // ← à remplacer
define('MAIL_PORT',      2525);                        // Port Mailtrap
define('MAIL_FROM',      'noreply@famicare.fr');       // Expéditeur
define('MAIL_FROM_NAME', 'FamiCare Évaluation');       // Nom expéditeur
define('MAIL_FAMICARE',  'contact@famicare.fr');       // Email FamiCare responsable

// ===== CHEMINS =====
define('UPLOAD_PATH', __DIR__ . '/uploads/questions/');