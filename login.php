<?php
/**
 * login.php
 * ================================================
 * Traitement du formulaire de connexion.
 * Ce fichier est appelé en POST depuis index.php.
 *
 * Ce qu'il fait :
 *  1. Récupère email + mot de passe du formulaire
 *  2. Cherche l'utilisateur en BDD avec PDO
 *  3. Vérifie le mot de passe avec password_verify()
 *  4. Crée la session avec les infos utilisateur
 *  5. Redirige selon le rôle (admin ou intervenante)
 * ================================================
 */

session_start();
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';

// Sécurité : ce fichier n'accepte que les requêtes POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . BASE_URL . 'index.php');
    exit;
}

// ======================================================
// 1. RÉCUPÉRER ET NETTOYER LES DONNÉES DU FORMULAIRE
// ======================================================
$email      = trim($_POST['email']      ?? '');
$mot_de_passe = trim($_POST['mot_de_passe'] ?? '');
$role_choisi  = $_POST['role'] ?? 'intervenante';

// Vérification basique : champs non vides
if (empty($email) || empty($mot_de_passe)) {
    $_SESSION['login_erreur'] = 'Veuillez remplir tous les champs.';
    header('Location: ' . BASE_URL . 'index.php');
    exit;
}

// Vérification format email
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $_SESSION['login_erreur'] = 'Adresse email invalide.';
    header('Location: ' . BASE_URL . 'index.php');
    exit;
}

// ======================================================
// 2. CHERCHER L'UTILISATEUR EN BASE DE DONNÉES
// ======================================================
try {
    // Requête préparée PDO — protège contre les injections SQL
    $stmt = $pdo->prepare(
        'SELECT id, nom, prenom, email, mot_de_passe, role, actif
         FROM utilisateurs
         WHERE email = :email
         LIMIT 1'
    );
    $stmt->execute([':email' => $email]);
    $utilisateur = $stmt->fetch(); // Retourne un tableau ou false

} catch (PDOException $e) {
    error_log('Erreur SQL login: ' . $e->getMessage());
    $_SESSION['login_erreur'] = 'Erreur de connexion. Réessayez.';
    header('Location: ' . BASE_URL . 'index.php');
    exit;
}

// ======================================================
// 3. VÉRIFICATIONS DE SÉCURITÉ
// ======================================================

// Utilisateur introuvable
if (!$utilisateur) {
    $_SESSION['login_erreur'] = 'Email ou mot de passe incorrect.';
    header('Location: ' . BASE_URL . 'index.php');
    exit;
}

// Compte désactivé
if (!$utilisateur['actif']) {
    $_SESSION['login_erreur'] = 'Votre compte est désactivé. Contactez l\'administrateur.';
    header('Location: ' . BASE_URL . 'index.php');
    exit;
}

// Vérification du mot de passe avec password_verify()
// (les mots de passe sont hashés avec password_hash() en BDD)
if (!password_verify($mot_de_passe, $utilisateur['mot_de_passe'])) {
    $_SESSION['login_erreur'] = 'Email ou mot de passe incorrect.';
    header('Location: ' . BASE_URL . 'index.php');
    exit;
}

// ======================================================
// 4. CRÉER LA SESSION UTILISATEUR
// ======================================================
// Regénérer l'ID de session pour éviter le session fixation
session_regenerate_id(true);

// Stocker les infos dans la session
$_SESSION['utilisateur'] = [
    'id'     => $utilisateur['id'],
    'nom'    => $utilisateur['nom'],
    'prenom' => $utilisateur['prenom'],
    'email'  => $utilisateur['email'],
    'role'   => $utilisateur['role'],
];

// Nettoyer l'erreur précédente si elle existe
unset($_SESSION['login_erreur']);

// ======================================================
// 5. REDIRECTION SELON LE RÔLE
// ======================================================
if ($utilisateur['role'] === 'admin') {
    // L'admin va au tableau de bord
    header('Location: ' . BASE_URL . 'admin/dashboard.php');
} else {
    // L'intervenante va à son espace
    header('Location: ' . BASE_URL . 'intervenante/accueil.php');
}
exit;