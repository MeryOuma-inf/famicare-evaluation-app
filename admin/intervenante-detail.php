<?php
/**
 * admin/intervenante-detail.php
 * Vue détaillée : toutes les réponses + historique d'une intervenante
 */
session_start();
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../includes/auth.php';

verif_connexion();
verif_role('admin');

$page_titre = 'Détail intervenante';

// Récupérer le résultat demandé
$id_resultat = (int)($_GET['id'] ?? 0);
if (!$id_resultat) {
    header('Location: ' . BASE_URL . 'admin/dashboard.php');
    exit;
}

// Infos du résultat
$stmt = $pdo->prepare(
    'SELECT rp.*, t.titre AS titre_test, t.categorie
     FROM resultats_publics rp
     JOIN tests t ON t.id = rp.id_test
     WHERE rp.id = :id'
);
$stmt->execute([':id' => $id_resultat]);
$resultat = $stmt->fetch();

if (!$resultat) {
    header('Location: ' . BASE_URL . 'admin/dashboard.php');
    exit;
}

// Historique de cette intervenante (même nom + prénom)
$stmt = $pdo->prepare(
    'SELECT rp.*, t.titre AS titre_test
     FROM resultats_publics rp
     JOIN tests t ON t.id = rp.id_test
     WHERE rp.nom = :nom AND rp.prenom = :prenom
     ORDER BY rp.passe_le DESC'
);
$stmt->execute([':nom' => $resultat['nom'], ':prenom' => $resultat['prenom']]);
$historique = $stmt->fetchAll();

// Badges mentions
$badge_styles = [
    'insuffisant'  => 'background:#FEE2E2;color:#991B1B;',
    'satisfaisant' => 'background:#F7E597;color:#78610A;',
    'bien'         => 'background:#D1DCE9;color:#1E3A5F;',
    'excellent'    => 'background:#D1FAE5;color:#065F46;',
];

// Durée formatée
$duree = $resultat['duree_sec'];
$duree_str = floor($duree/60) . 'min ' . ($duree%60) . 's';

require_once __DIR__ . '/../includes/header.php';
?>

<div class="fc-container">

    <!-- Bouton retour -->
    <div style="margin-bottom:24px;">
        <a href="<?= BASE_URL ?>admin/dashboard.php"
           style="display:inline-flex;align-items:center;gap:8px;font-size:14px;color:#5A5555;text-decoration:none;padding:8px 16px;background:#F5E4EB;border-radius:50px;transition:all .2s;"
           onmouseover="this.style.background='#E8C8D8'"
           onmouseout="this.style.background='#F5E4EB'">
            ← Retour au tableau de bord
        </a>
    </div>

    <!-- En-tête intervenante -->
    <div style="background:#fff;border-radius:20px;padding:32px;border:1px solid #E8E0E0;margin-bottom:24px;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:16px;">
        <div style="display:flex;align-items:center;gap:20px;">
            <div style="width:64px;height:64px;background:#D1DCE9;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:24px;font-weight:700;color:#2A2727;font-family:'Playfair Display',serif;">
                <?= strtoupper(substr($resultat['prenom'],0,1)) ?>
            </div>
            <div>
                <h1 style="font-family:'Playfair Display',serif;font-size:24px;font-weight:700;color:#2A2727;margin-bottom:4px;">
                    <?= htmlspecialchars($resultat['prenom'] . ' ' . $resultat['nom']) ?>
                </h1>
                <p style="font-size:13px;color:#9A9494;font-weight:300;">
                    <?= count($historique) ?> test<?= count($historique) > 1 ? 's' : '' ?> passé<?= count($historique) > 1 ? 's' : '' ?>
                </p>
            </div>
        </div>

        <!-- Score du résultat actuel -->
        <div style="text-align:center;">
            <div style="font-family:'Playfair Display',serif;font-size:48px;font-weight:700;color:#2A2727;line-height:1;">
                <?= $resultat['pourcentage'] ?>%
            </div>
            <span style="<?= $badge_styles[$resultat['mention']] ?> padding:4px 16px;border-radius:50px;font-size:12px;font-weight:700;text-transform:capitalize;">
                <?= ucfirst($resultat['mention']) ?>
            </span>
        </div>
    </div>

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;">

        <!-- COLONNE GAUCHE : Détail du test -->
        <div>
            <h2 style="font-family:'Playfair Display',serif;font-size:18px;font-weight:700;color:#2A2727;margin-bottom:16px;">
                📋 Détail du test — <?= htmlspecialchars($resultat['titre_test']) ?>
            </h2>

            <!-- Infos générales -->
            <div style="background:#F5E4EB;border-radius:14px;padding:18px;margin-bottom:16px;display:grid;grid-template-columns:1fr 1fr 1fr;gap:12px;text-align:center;">
                <div>
                    <div style="font-family:'Playfair Display',serif;font-size:24px;font-weight:700;color:#2A2727;"><?= $resultat['score'] ?></div>
                    <div style="font-size:11px;color:#9A9494;font-weight:300;">Points obtenus</div>
                </div>
                <div>
                    <div style="font-family:'Playfair Display',serif;font-size:24px;font-weight:700;color:#2A2727;"><?= $duree_str ?></div>
                    <div style="font-size:11px;color:#9A9494;font-weight:300;">Durée</div>
                </div>
                <div>
                    <div style="font-family:'Playfair Display',serif;font-size:24px;font-weight:700;color:#2A2727;"><?= date('d/m/Y', strtotime($resultat['passe_le'])) ?></div>
                    <div style="font-size:11px;color:#9A9494;font-weight:300;">Date</div>
                </div>
            </div>

            <!-- Message selon mention -->
            <?php
            $messages = [
                'insuffisant'  => ['msg' => 'Cette intervenante nécessite un accompagnement supplémentaire.', 'bg' => '#FEE2E2', 'border' => '#FCA5A5'],
                'satisfaisant' => ['msg' => 'Des bases solides mais des points à améliorer.', 'bg' => '#FEF9E7', 'border' => '#F7E597'],
                'bien'         => ['msg' => 'Bonne maîtrise des compétences évaluées.', 'bg' => '#EFF6FF', 'border' => '#D1DCE9'],
                'excellent'    => ['msg' => 'Excellente intervenante, prête pour toutes les missions.', 'bg' => '#F0FDF4', 'border' => '#86EFAC'],
            ];
            $m = $messages[$resultat['mention']];
            ?>
            <div style="background:<?= $m['bg'] ?>;border:1px solid <?= $m['border'] ?>;border-radius:12px;padding:14px 18px;margin-bottom:16px;font-size:13px;color:#2A2727;">
                💬 <?= $m['msg'] ?>
            </div>

            <!-- Note : les détails question par question nécessitent une table reponses_detail -->
            <div style="background:#fff;border:1px solid #E8E0E0;border-radius:14px;padding:20px;">
                <p style="font-size:13px;color:#9A9494;font-weight:300;text-align:center;padding:16px 0;">
                    📊 Le détail des réponses question par question<br>sera disponible après configuration de PHPMailer
                </p>
            </div>
        </div>

        <!-- COLONNE DROITE : Historique -->
        <div>
            <h2 style="font-family:'Playfair Display',serif;font-size:18px;font-weight:700;color:#2A2727;margin-bottom:16px;">
                📈 Historique des tests
            </h2>

            <div style="background:#fff;border-radius:14px;border:1px solid #E8E0E0;overflow:hidden;">
                <?php if (empty($historique)): ?>
                <div style="padding:32px;text-align:center;color:#9A9494;">
                    Aucun historique disponible
                </div>
                <?php else: ?>
                <?php foreach ($historique as $h): ?>
                <div style="padding:16px 20px;border-bottom:1px solid #E8E0E0;display:flex;align-items:center;justify-content:space-between;<?= $h['id'] === $id_resultat ? 'background:#D1DCE9;' : '' ?>"
                     onmouseover="this.style.background='#F5E4EB'"
                     onmouseout="this.style.background='<?= $h['id'] === $id_resultat ? '#D1DCE9' : '#fff' ?>'">
                    <div>
                        <div style="font-size:14px;font-weight:500;color:#2A2727;margin-bottom:2px;">
                            <?= htmlspecialchars($h['titre_test']) ?>
                            <?php if ($h['id'] === $id_resultat): ?>
                            <span style="font-size:10px;background:#2A2727;color:#fff;padding:2px 8px;border-radius:50px;margin-left:6px;">Actuel</span>
                            <?php endif; ?>
                        </div>
                        <div style="font-size:12px;color:#9A9494;font-weight:300;">
                            <?= date('d/m/Y à H:i', strtotime($h['passe_le'])) ?>
                        </div>
                    </div>
                    <div style="text-align:right;">
                        <div style="font-family:'Playfair Display',serif;font-size:20px;font-weight:700;color:#2A2727;">
                            <?= $h['pourcentage'] ?>%
                        </div>
                        <span style="<?= $badge_styles[$h['mention']] ?> font-size:10px;font-weight:600;padding:2px 10px;border-radius:50px;text-transform:capitalize;">
                            <?= ucfirst($h['mention']) ?>
                        </span>
                    </div>
                </div>
                <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>