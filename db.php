<?php
/**
 * db.php — Connexion PDO à la BDD famicare_evaluation
 */

if (!defined('BASE_URL')) {
    require_once __DIR__ . '/config.php';
}

try {
    $dsn = 'mysql:host=' . DB_HOST
         . ';dbname='    . DB_NAME
         . ';charset='   . DB_CHARSET;

    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];

    $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);

} catch (PDOException $e) {
    error_log('Erreur BDD : ' . $e->getMessage());
    die('<div style="font-family:sans-serif;padding:40px;text-align:center;background:#FEE2E2;color:#991B1B;border-radius:12px;margin:40px auto;max-width:500px;">
        <h2>❌ Connexion BDD impossible</h2>
        <p>Vérifie que WAMP est vert et que MySQL est démarré.</p>
        <p style="font-size:12px;opacity:.7">' . $e->getMessage() . '</p>
    </div>');
}