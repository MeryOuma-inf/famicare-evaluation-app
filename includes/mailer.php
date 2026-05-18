<?php
/**
 * includes/mailer.php
 * =====================================================
 * Configuration PHPMailer + fonctions d'envoi email
 *
 * Fonctions disponibles :
 *   envoyer_email_intervenante($prenom, $nom, $email, $resultat)
 *   envoyer_email_famicare($prenom, $nom, $resultat)
 * =====================================================
 */

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../config.php';

/**
 * Créer et configurer une instance PHPMailer
 */
function creer_mailer(): PHPMailer {
    $mail = new PHPMailer(true);

    try {
        // ---- Configuration SMTP ----
        // Pour les tests : utilise Mailtrap.io (gratuit)
        // Pour la prod : remplace par le vrai SMTP FamiCare

        $mail->isSMTP();
        $mail->Host       = MAIL_HOST;
        $mail->SMTPAuth   = true;
        $mail->Username   = MAIL_USER;
        $mail->Password   = MAIL_PASS;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = MAIL_PORT;
        $mail->CharSet    = 'UTF-8';

        // Expéditeur
        $mail->setFrom(MAIL_FROM, MAIL_FROM_NAME);

    } catch (Exception $e) {
        error_log('Erreur configuration mailer : ' . $e->getMessage());
    }

    return $mail;
}

/**
 * Email envoyé à l'intervenante après son test
 *
 * @param string $prenom    Prénom de l'intervenante
 * @param string $nom       Nom de l'intervenante
 * @param string $email     Email de l'intervenante
 * @param array  $resultat  Données du résultat (pourcentage, mention, titre_test, score, duree_sec)
 * @return bool
 */
function envoyer_email_intervenante(string $prenom, string $nom, string $email, array $resultat): bool {
    try {
        $mail = creer_mailer();

        $mail->addAddress($email, $prenom . ' ' . $nom);
        $mail->Subject = '📋 Votre résultat de test FamiCare';

        // Couleurs selon la mention
        $couleurs = [
            'insuffisant'  => ['bg' => '#FEE2E2', 'color' => '#991B1B', 'emoji' => '😔'],
            'satisfaisant' => ['bg' => '#F7E597', 'color' => '#78610A', 'emoji' => '👍'],
            'bien'         => ['bg' => '#D1DCE9', 'color' => '#1E3A5F', 'emoji' => '😊'],
            'excellent'    => ['bg' => '#D1FAE5', 'color' => '#065F46', 'emoji' => '🌟'],
        ];
        $c = $couleurs[$resultat['mention']] ?? $couleurs['bien'];

        // Durée formatée
        $duree = $resultat['duree_sec'] ?? 0;
        $duree_str = floor($duree/60) . ' min ' . ($duree%60) . ' sec';

        $mail->isHTML(true);
        $mail->Body = '
<!DOCTYPE html>
<html lang="fr">
<head><meta charset="UTF-8"></head>
<body style="margin:0;padding:0;background:#F5E4EB;font-family:DM Sans,Arial,sans-serif;">

<div style="max-width:560px;margin:32px auto;background:#ffffff;border-radius:20px;overflow:hidden;box-shadow:0 4px 24px rgba(42,39,39,.1);">

    <!-- EN-TÊTE -->
    <div style="background:#2A2727;padding:32px;text-align:center;">
        <h1 style="font-family:Georgia,serif;color:#ffffff;font-size:24px;margin:0 0 6px;">
            FamiCare
        </h1>
        <p style="color:rgba(255,255,255,.6);font-size:13px;margin:0;">
            Plateforme d\'évaluation des intervenantes
        </p>
    </div>

    <!-- SCORE PRINCIPAL -->
    <div style="background:' . $c['bg'] . ';padding:40px;text-align:center;">
        <div style="font-size:48px;margin-bottom:8px;">' . $c['emoji'] . '</div>
        <div style="font-family:Georgia,serif;font-size:64px;font-weight:700;color:' . $c['color'] . ';line-height:1;margin-bottom:12px;">
            ' . $resultat['pourcentage'] . '%
        </div>
        <div style="display:inline-block;background:' . $c['color'] . ';color:#fff;padding:6px 20px;border-radius:50px;font-size:14px;font-weight:700;text-transform:capitalize;">
            ' . ucfirst($resultat['mention']) . '
        </div>
    </div>

    <!-- CONTENU -->
    <div style="padding:32px;">
        <p style="font-size:16px;color:#2A2727;margin:0 0 16px;">
            Bonjour <strong>' . htmlspecialchars($prenom) . '</strong>,
        </p>
        <p style="font-size:14px;color:#5A5555;line-height:1.7;margin:0 0 24px;">
            Vous venez de terminer votre évaluation <strong>' . htmlspecialchars($resultat['titre_test'] ?? 'FamiCare') . '</strong>.
            Voici le détail de votre résultat :
        </p>

        <!-- STATS -->
        <div style="background:#F5E4EB;border-radius:12px;padding:20px;margin-bottom:24px;display:table;width:100%;">
            <div style="display:table-cell;text-align:center;width:33%;">
                <div style="font-family:Georgia,serif;font-size:28px;font-weight:700;color:#2A2727;">' . $resultat['pourcentage'] . '%</div>
                <div style="font-size:11px;color:#9A9494;margin-top:4px;">Score obtenu</div>
            </div>
            <div style="display:table-cell;text-align:center;width:33%;border-left:1px solid #E8C8D8;border-right:1px solid #E8C8D8;">
                <div style="font-family:Georgia,serif;font-size:28px;font-weight:700;color:#2A2727;">' . $duree_str . '</div>
                <div style="font-size:11px;color:#9A9494;margin-top:4px;">Durée</div>
            </div>
            <div style="display:table-cell;text-align:center;width:33%;">
                <div style="font-family:Georgia,serif;font-size:28px;font-weight:700;color:#2A2727;">' . date('d/m/Y') . '</div>
                <div style="font-size:11px;color:#9A9494;margin-top:4px;">Date</div>
            </div>
        </div>

        <p style="font-size:13px;color:#5A5555;line-height:1.7;margin:0 0 24px;">
            Votre résultat a été transmis automatiquement à l\'équipe FamiCare.
            Un responsable prendra contact avec vous si nécessaire.
        </p>

        <p style="font-size:13px;color:#5A5555;line-height:1.7;margin:0;">
            Merci pour votre participation,<br>
            <strong>L\'équipe FamiCare</strong>
        </p>
    </div>

    <!-- FOOTER -->
    <div style="background:#F5E4EB;padding:20px;text-align:center;border-top:1px solid #E8C8D8;">
        <p style="font-size:11px;color:#9A9494;margin:0;">
            73 Rue de Lourmel, 75015 Paris · contact@famicare.fr · 01 84 60 48 38
        </p>
    </div>

</div>
</body>
</html>';

        $mail->AltBody = 'Bonjour ' . $prenom . ', votre score : ' . $resultat['pourcentage'] . '% — ' . ucfirst($resultat['mention']);

        $mail->send();
        return true;

    } catch (Exception $e) {
        error_log('Erreur email intervenante : ' . $e->getMessage());
        return false;
    }
}

/**
 * Email envoyé à FamiCare quand une intervenante termine un test
 *
 * @param string $prenom   Prénom de l'intervenante
 * @param string $nom      Nom de l'intervenante
 * @param array  $resultat Données du résultat
 * @return bool
 */
function envoyer_email_famicare(string $prenom, string $nom, array $resultat): bool {
    try {
        $mail = creer_mailer();

        // Envoyer à l'email FamiCare
        $mail->addAddress(MAIL_FAMICARE, 'FamiCare Évaluation');
        $mail->Subject = ($resultat['mention'] === 'insuffisant' ? '⚠️ ALERTE' : '✅ Nouveau résultat')
                       . ' — ' . $prenom . ' ' . $nom;

        $couleur_score = $resultat['pourcentage'] < 50
            ? '#EF4444'
            : ($resultat['pourcentage'] < 75 ? '#F59E0B' : '#10B981');

        $alerte = $resultat['mention'] === 'insuffisant'
            ? '<div style="background:#FEE2E2;border:1px solid #FCA5A5;border-radius:10px;padding:14px 18px;margin-bottom:20px;font-size:13px;color:#991B1B;">
                ⚠️ <strong>Score insuffisant</strong> — Cette intervenante nécessite un suivi particulier.
               </div>'
            : '';

        $mail->isHTML(true);
        $mail->Body = '
<!DOCTYPE html>
<html lang="fr">
<head><meta charset="UTF-8"></head>
<body style="margin:0;padding:0;background:#D1DCE9;font-family:Arial,sans-serif;">

<div style="max-width:560px;margin:32px auto;background:#ffffff;border-radius:20px;overflow:hidden;box-shadow:0 4px 24px rgba(42,39,39,.1);">

    <div style="background:#2A2727;padding:28px;text-align:center;">
        <h1 style="font-family:Georgia,serif;color:#ffffff;font-size:20px;margin:0 0 4px;">
            Nouveau résultat de test
        </h1>
        <p style="color:rgba(255,255,255,.6);font-size:12px;margin:0;">FamiCare · Plateforme évaluation</p>
    </div>

    <div style="padding:28px;">
        ' . $alerte . '

        <table style="width:100%;border-collapse:collapse;margin-bottom:20px;">
            <tr>
                <td style="padding:10px 0;border-bottom:1px solid #E8E0E0;font-size:13px;color:#9A9494;">Intervenante</td>
                <td style="padding:10px 0;border-bottom:1px solid #E8E0E0;font-size:14px;font-weight:600;color:#2A2727;text-align:right;">' . htmlspecialchars($prenom . ' ' . $nom) . '</td>
            </tr>
            <tr>
                <td style="padding:10px 0;border-bottom:1px solid #E8E0E0;font-size:13px;color:#9A9494;">Test</td>
                <td style="padding:10px 0;border-bottom:1px solid #E8E0E0;font-size:14px;color:#2A2727;text-align:right;">' . htmlspecialchars($resultat['titre_test'] ?? '') . '</td>
            </tr>
            <tr>
                <td style="padding:10px 0;border-bottom:1px solid #E8E0E0;font-size:13px;color:#9A9494;">Score</td>
                <td style="padding:10px 0;border-bottom:1px solid #E8E0E0;font-size:22px;font-weight:700;color:' . $couleur_score . ';text-align:right;">' . $resultat['pourcentage'] . '%</td>
            </tr>
            <tr>
                <td style="padding:10px 0;border-bottom:1px solid #E8E0E0;font-size:13px;color:#9A9494;">Mention</td>
                <td style="padding:10px 0;border-bottom:1px solid #E8E0E0;font-size:14px;color:#2A2727;text-align:right;text-transform:capitalize;">' . ucfirst($resultat['mention']) . '</td>
            </tr>
            <tr>
                <td style="padding:10px 0;font-size:13px;color:#9A9494;">Date & heure</td>
                <td style="padding:10px 0;font-size:13px;color:#2A2727;text-align:right;">' . date('d/m/Y à H:i') . '</td>
            </tr>
        </table>

        <p style="font-size:12px;color:#9A9494;margin:0;">
            Connectez-vous au tableau de bord pour voir le détail complet.
        </p>
    </div>

    <div style="background:#F5E4EB;padding:16px;text-align:center;">
        <p style="font-size:11px;color:#9A9494;margin:0;">
            FamiCare · contact@famicare.fr
        </p>
    </div>
</div>
</body>
</html>';

        $mail->AltBody = 'Nouveau résultat : ' . $prenom . ' ' . $nom . ' — ' . $resultat['pourcentage'] . '% — ' . ucfirst($resultat['mention']);

        $mail->send();
        return true;

    } catch (Exception $e) {
        error_log('Erreur email FamiCare : ' . $e->getMessage());
        return false;
    }
}

/**
 * Créer une notification dans le dashboard admin
 *
 * @param PDO    $pdo      Connexion BDD
 * @param string $message  Message de la notification
 * @param string $lien     Lien vers le détail
 * @return bool
 */
function creer_notification(PDO $pdo, string $message, string $lien = ''): bool {
    try {
        // Récupérer l'id du premier admin
        $stmt = $pdo->query("SELECT id FROM utilisateurs WHERE role = 'admin' LIMIT 1");
        $admin = $stmt->fetch();
        if (!$admin) return false;

        $stmt = $pdo->prepare(
            'INSERT INTO notifications (id_destinataire, message, lien, lue)
             VALUES (:id, :message, :lien, 0)'
        );
        $stmt->execute([
            ':id'      => $admin['id'],
            ':message' => $message,
            ':lien'    => $lien,
        ]);
        return true;
    } catch (PDOException $e) {
        error_log('Erreur notification : ' . $e->getMessage());
        return false;
    }
}
