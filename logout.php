<?php
/**
 * logout.php
 * ================================================
 * Déconnexion de l'utilisateur.
 * Détruit la session et redirige vers l'accueil.
 * ================================================
 */

session_start();
require_once __DIR__ . '/config.php';

// Détruire toutes les données de session
$_SESSION = [];

// Détruire le cookie de session
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time() - 3600, '/');
}

// Détruire la session côté serveur
session_destroy();

// Rediriger vers la page d'accueil
header('Location: ' . BASE_URL . 'index.php');
exit;