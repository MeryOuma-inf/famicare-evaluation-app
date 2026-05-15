<?php
/**
 * config.php
 * ================================================
 * Configuration globale de l'application FamiCare.
 * Ce fichier est inclus en premier dans chaque page.
 * ================================================
 */

// --- Base de données (correspond a ta BDD dans phpMyAdmin) ---
define('DB_HOST',    'localhost');
define('DB_NAME',    'famicare_evaluation'); // Nom exact de ta BDD creee en semaine 2
define('DB_USER',    'root');                // Utilisateur WAMP par defaut
define('DB_PASS',    '');                    // Mot de passe vide par defaut avec WAMP
define('DB_CHARSET', 'utf8mb4');

// --- URL du site dans le navigateur ---
define('BASE_URL', 'http://localhost/famicare/');

// --- Chemins vers les dossiers ---
define('UPLOAD_PATH', __DIR__ . '/uploads/questions/');
define('UPLOAD_URL',  BASE_URL . 'uploads/questions/');

// --- Seuils mentions (coherents avec ta table resultats) ---
// Ta BDD : insuffisant / satisfaisant / bien / excellent
define('SEUIL_INSUFFISANT',  50); // < 50%  = insuffisant (rouge)
define('SEUIL_SATISFAISANT', 75); // 50-74% = satisfaisant (orange)
define('SEUIL_BIEN',         90); // 75-89% = bien (vert clair)
                                  // >= 90% = excellent (vert fonce)

// --- Upload images questions ---
define('UPLOAD_MAX_SIZE',        2 * 1024 * 1024);
define('UPLOAD_TYPES_AUTORISES', ['image/jpeg', 'image/png', 'image/webp']);