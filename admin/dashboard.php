<?php
/**
 * admin/dashboard.php
 * ================================================
 * Tableau de bord de l'administrateur FamiCare.
 * Page principale après connexion admin.
 *
 * Affiche :
 *  - KPIs (stats globales depuis la BDD)
 *  - Derniers résultats des intervenantes
 *  - Alertes scores faibles
 * ================================================
 */

session_start();
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../includes/auth.php';

// Sécurité : réservé aux admins uniquement
verif_connexion();
verif_role('admin');

$page_titre = 'Tableau de bord';
$admin = utilisateur_connecte();

// ======================================================
// RÉCUPÉRATION DES STATS DEPUIS LA BDD
// ======================================================

// Total tests passés
$stmt = $pdo->query('SELECT COUNT(*) FROM resultats_publics');
$total_tests = $stmt->fetchColumn();

// Score moyen global
$stmt = $pdo->query('SELECT ROUND(AVG(pourcentage), 1) FROM resultats_publics');
$score_moyen = $stmt->fetchColumn() ?? 0;

// Nombre d'intervenantes actives
$stmt = $pdo->query("SELECT COUNT(DISTINCT CONCAT(prenom, nom)) FROM resultats_publics");
$total_intervenantes = $stmt->fetchColumn();

// Scores insuffisants (< 50%)
$stmt = $pdo->query("SELECT COUNT(*) FROM resultats_publics WHERE mention = 'insuffisant'");
$scores_faibles = $stmt->fetchColumn();

// Derniers résultats (5 plus récents)
$stmt = $pdo->query(
    'SELECT r.id, r.prenom, r.nom, t.titre, r.pourcentage, r.mention, r.passe_le
     FROM resultats_publics r
     JOIN tests t ON t.id = r.id_test
     ORDER BY r.passe_le DESC
     LIMIT 5'
);
$derniers_resultats = $stmt->fetchAll();

// Notifications non lues
$stmt_notif = $pdo->query(
    "SELECT COUNT(*) FROM notifications WHERE lue = 0"
);
$nb_notifs = $stmt_notif->fetchColumn();

// 5 dernières notifications
$stmt_notif_list = $pdo->query(
    "SELECT * FROM notifications ORDER BY cree_le DESC LIMIT 5"
);
$notifications = $stmt_notif_list->fetchAll();

// Inclure le header commun
require_once __DIR__ . '/../includes/header.php';
?>

<div class="fc-container">

    <!-- EN-TÊTE DE PAGE -->
    <div style="display:flex; align-items:flex-start; justify-content:space-between; margin-bottom:32px; flex-wrap:wrap; gap:16px;">
        <div>
            <h1 style="font-family:'Cormorant Garamond',serif; font-size:2rem; font-weight:700; color:#2A2727; margin-bottom:4px;">
                Bonjour, <?= htmlspecialchars($admin['prenom']) ?> 👋
            </h1>
            <p style="font-size:14px; color:#9A9494; font-weight:300;">
                <?= date('l d F Y') ?> · Tableau de bord administrateur
            </p>
        </div>
        <a href="<?= BASE_URL ?>admin/tests.php" class="fc-btn fc-btn-primary">
            + Créer un test
        </a>
    </div>

    <!-- ===== KPI CARDS ===== -->
    <div style="display:grid; grid-template-columns:repeat(4,1fr); gap:16px; margin-bottom:36px;">

        <!-- Total tests passés -->
        <div style="background:#fff; border-radius:16px; padding:22px; border:1px solid #E8E0E0; transition:all .2s;"
             onmouseover="this.style.boxShadow='0 6px 24px rgba(42,39,39,.1)'; this.style.transform='translateY(-2px)'"
             onmouseout="this.style.boxShadow='none'; this.style.transform='none'">
            <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:14px;">
                <span style="font-size:11px; font-weight:700; letter-spacing:1.5px; text-transform:uppercase; color:#9A9494;">Tests passés</span>
                <div style="width:36px; height:36px; background:#EFF6FF; border-radius:10px; display:flex; align-items:center; justify-content:center;">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#3B82F6" stroke-width="2" stroke-linecap="round"><path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2"/><rect x="9" y="3" width="6" height="4" rx="1"/></svg>
                </div>
            </div>
            <div style="font-family:'Cormorant Garamond',serif; font-size:40px; font-weight:700; color:#3B82F6; line-height:1;"><?= $total_tests ?></div>
            <div style="font-size:12px; color:#9A9494; font-weight:300; margin-top:4px;">évaluations réalisées</div>
        </div>

        <!-- Score moyen -->
        <div style="background:#fff; border-radius:16px; padding:22px; border:1px solid #E8E0E0; transition:all .2s;"
             onmouseover="this.style.boxShadow='0 6px 24px rgba(42,39,39,.1)'; this.style.transform='translateY(-2px)'"
             onmouseout="this.style.boxShadow='none'; this.style.transform='none'">
            <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:14px;">
                <span style="font-size:11px; font-weight:700; letter-spacing:1.5px; text-transform:uppercase; color:#9A9494;">Score moyen</span>
                <div style="width:36px; height:36px; background:#ECFDF5; border-radius:10px; display:flex; align-items:center; justify-content:center;">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#10B981" stroke-width="2" stroke-linecap="round"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>
                </div>
            </div>
            <div style="font-family:'Cormorant Garamond',serif; font-size:40px; font-weight:700; color:#10B981; line-height:1;"><?= $score_moyen ?>%</div>
            <div style="font-size:12px; color:#9A9494; font-weight:300; margin-top:4px;">sur l'ensemble des tests</div>
        </div>

        <!-- Intervenantes actives -->
        <div style="background:#fff; border-radius:16px; padding:22px; border:1px solid #E8E0E0; transition:all .2s;"
             onmouseover="this.style.boxShadow='0 6px 24px rgba(42,39,39,.1)'; this.style.transform='translateY(-2px)'"
             onmouseout="this.style.boxShadow='none'; this.style.transform='none'">
            <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:14px;">
                <span style="font-size:11px; font-weight:700; letter-spacing:1.5px; text-transform:uppercase; color:#9A9494;">Intervenantes</span>
                <div style="width:36px; height:36px; background:#F5F3FF; border-radius:10px; display:flex; align-items:center; justify-content:center;">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#8B5CF6" stroke-width="2" stroke-linecap="round"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 00-3-3.87"/><path d="M16 3.13a4 4 0 010 7.75"/></svg>
                </div>
            </div>
            <div style="font-family:'Cormorant Garamond',serif; font-size:40px; font-weight:700; color:#8B5CF6; line-height:1;"><?= $total_intervenantes ?></div>
            <div style="font-size:12px; color:#9A9494; font-weight:300; margin-top:4px;">comptes actifs</div>
        </div>

        <!-- Scores faibles -->
        <div style="background:#fff; border-radius:16px; padding:22px; border:1px solid #E8E0E0; transition:all .2s;"
             onmouseover="this.style.boxShadow='0 6px 24px rgba(42,39,39,.1)'; this.style.transform='translateY(-2px)'"
             onmouseout="this.style.boxShadow='none'; this.style.transform='none'">
            <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:14px;">
                <span style="font-size:11px; font-weight:700; letter-spacing:1.5px; text-transform:uppercase; color:#9A9494;">Alertes</span>
                <div style="width:36px; height:36px; background:#FEE2E2; border-radius:10px; display:flex; align-items:center; justify-content:center;">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#EF4444" stroke-width="2" stroke-linecap="round"><path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                </div>
            </div>
            <div style="font-family:'Cormorant Garamond',serif; font-size:40px; font-weight:700; color:#EF4444; line-height:1;"><?= $scores_faibles ?></div>
            <div style="font-size:12px; color:#9A9494; font-weight:300; margin-top:4px;">scores insuffisants</div>
        </div>

    </div>

    <!-- ===== DERNIERS RÉSULTATS ===== -->
    <div style="background:#fff; border-radius:18px; border:1px solid #E8E0E0; overflow:hidden; margin-bottom:32px;">

        <!-- Header du tableau -->
        <div style="padding:20px 24px; border-bottom:1px solid #E8E0E0; display:flex; align-items:center; justify-content:space-between;">
            <div>
                <h2 style="font-family:'Cormorant Garamond',serif; font-size:20px; font-weight:700; color:#2A2727; margin-bottom:2px;">Derniers résultats</h2>
                <p style="font-size:12px; color:#9A9494; font-weight:300;">5 évaluations les plus récentes</p>
            </div>
            <a href="<?= BASE_URL ?>admin/resultats.php" style="font-size:13px; color:#D1DCE9; text-decoration:none; font-weight:500;">
                Voir tout →
            </a>
        </div>

        <!-- Tableau -->
        <?php if (empty($derniers_resultats)): ?>
        <div style="padding:48px; text-align:center; color:#9A9494;">
            <div style="font-size:40px; margin-bottom:12px;">📋</div>
            <p style="font-weight:300;">Aucun résultat pour l'instant.</p>
            <a href="<?= BASE_URL ?>admin/tests.php" style="display:inline-block; margin-top:16px; color:#D1DCE9; font-size:13px; font-weight:500;">
                Créer le premier test →
            </a>
        </div>
        <?php else: ?>
        <table style="width:100%; border-collapse:collapse;">
            <thead>
                <tr style="background:#FFFFFF;">
                    <th style="padding:12px 24px; font-size:11px; font-weight:700; letter-spacing:1px; text-transform:uppercase; color:#9A9494; text-align:left;">Intervenante</th>
                    <th style="padding:12px 24px; font-size:11px; font-weight:700; letter-spacing:1px; text-transform:uppercase; color:#9A9494; text-align:left;">Test</th>
                    <th style="padding:12px 24px; font-size:11px; font-weight:700; letter-spacing:1px; text-transform:uppercase; color:#9A9494; text-align:center;">Score</th>
                    <th style="padding:12px 24px; font-size:11px; font-weight:700; letter-spacing:1px; text-transform:uppercase; color:#9A9494; text-align:center;">Mention</th>
                    <th style="padding:12px 24px; font-size:11px; font-weight:700; letter-spacing:1px; text-transform:uppercase; color:#9A9494; text-align:left;">Date</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($derniers_resultats as $r): ?>
                <?php
                    // Couleurs des mentions
                    $badge_styles = [
                        'insuffisant'  => 'background:#FEE2E2; color:#991B1B;',
                        'satisfaisant' => 'background:#FEF3C7; color:#92400E;',
                        'bien'         => 'background:#DBEAFE; color:#1E40AF;',
                        'excellent'    => 'background:#D1FAE5; color:#065F46;',
                    ];
                    $badge = $badge_styles[$r['mention']] ?? '';
                    $score_color = $r['pourcentage'] < 50 ? '#EF4444' : ($r['pourcentage'] < 75 ? '#F59E0B' : '#10B981');
                ?>
                <tr style="border-bottom:1px solid #E8E0E0; transition:background .15s;"
                    onmouseover="this.style.background='#FFFFFF'"
                    onmouseout="this.style.background='transparent'">
                    <td style="padding:14px 24px; font-size:14px; font-weight:500; color:#2A2727;">
                        <?= htmlspecialchars($r['prenom'] . ' ' . $r['nom'] ?? 'Intervenante') ?>
                    </td>
                    <td style="padding:14px 24px; font-size:13px; font-weight:300; color:#5A5555;">
                        <?= htmlspecialchars($r['titre']) ?>
                    </td>
                    <td style="padding:14px 24px; text-align:center;">
                        <span style="font-family:'Cormorant Garamond',serif; font-size:20px; font-weight:700; color:<?= $score_color ?>;">
                            <?= $r['pourcentage'] ?>%
                        </span>
                    </td>
                    <td style="padding:14px 24px; text-align:center;">
                        <span style="<?= $badge ?> font-size:11px; font-weight:600; padding:4px 12px; border-radius:50px; text-transform:capitalize;">
                            <?= ucfirst($r['mention']) ?>
                        </span>
                    </td>
                    <td style="padding:14px 24px; font-size:12px; color:#9A9494; font-weight:300;">
                        <?= date('d/m/Y à H:i', strtotime($r['passe_le'])) ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </div>

    <!-- ===== ACCÈS RAPIDES ===== -->
    <div style="display:grid; grid-template-columns:repeat(3,1fr); gap:16px;">

        <a href="<?= BASE_URL ?>admin/tests.php"
           style="background:#fff; border-radius:16px; padding:22px; border:1px solid #E8E0E0; text-decoration:none; display:flex; align-items:center; gap:14px; transition:all .2s;"
           onmouseover="this.style.boxShadow='0 6px 24px rgba(42,39,39,.1)'; this.style.transform='translateY(-2px)'"
           onmouseout="this.style.boxShadow='none'; this.style.transform='none'">
            <div style="width:44px; height:44px; background:#FBF0EC; border-radius:12px; display:flex; align-items:center; justify-content:center; flex-shrink:0;">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#D1DCE9" stroke-width="2" stroke-linecap="round"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
            </div>
            <div>
                <div style="font-size:14px; font-weight:600; color:#2A2727; margin-bottom:3px;">Gérer les tests</div>
                <div style="font-size:12px; color:#9A9494; font-weight:300;">Créer, modifier, supprimer</div>
            </div>
        </a>

        <a href="<?= BASE_URL ?>admin/utilisateurs.php"
           style="background:#fff; border-radius:16px; padding:22px; border:1px solid #E8E0E0; text-decoration:none; display:flex; align-items:center; gap:14px; transition:all .2s;"
           onmouseover="this.style.boxShadow='0 6px 24px rgba(42,39,39,.1)'; this.style.transform='translateY(-2px)'"
           onmouseout="this.style.boxShadow='none'; this.style.transform='none'">
            <div style="width:44px; height:44px; background:#F5F3FF; border-radius:12px; display:flex; align-items:center; justify-content:center; flex-shrink:0;">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#8B5CF6" stroke-width="2" stroke-linecap="round"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
            </div>
            <div>
                <div style="font-size:14px; font-weight:600; color:#2A2727; margin-bottom:3px;">Intervenantes</div>
                <div style="font-size:12px; color:#9A9494; font-weight:300;">Gérer les comptes</div>
            </div>
        </a>

        <a href="<?= BASE_URL ?>admin/resultats.php"
           style="background:#fff; border-radius:16px; padding:22px; border:1px solid #E8E0E0; text-decoration:none; display:flex; align-items:center; gap:14px; transition:all .2s;"
           onmouseover="this.style.boxShadow='0 6px 24px rgba(42,39,39,.1)'; this.style.transform='translateY(-2px)'"
           onmouseout="this.style.boxShadow='none'; this.style.transform='none'">
            <div style="width:44px; height:44px; background:#ECFDF5; border-radius:12px; display:flex; align-items:center; justify-content:center; flex-shrink:0;">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#10B981" stroke-width="2" stroke-linecap="round"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg>
            </div>
            <div>
                <div style="font-size:14px; font-weight:600; color:#2A2727; margin-bottom:3px;">Tous les résultats</div>
                <div style="font-size:12px; color:#9A9494; font-weight:300;">Statistiques complètes</div>
            </div>
        </a>

    </div>

    <!-- ===== NOTIFICATIONS ===== -->
    <?php if (!empty($notifications)): ?>
    <div style="background:#fff;border-radius:18px;border:1px solid #E8E0E0;overflow:hidden;margin-top:24px;">
        <div style="padding:20px 24px;border-bottom:1px solid #E8E0E0;display:flex;align-items:center;justify-content:space-between;">
            <div>
                <h2 style="font-family:'Playfair Display',serif;font-size:20px;font-weight:700;color:#2A2727;margin-bottom:2px;">
                    🔔 Notifications
                    <?php if ($nb_notifs > 0): ?>
                    <span style="background:#D1DCE9;color:#1E3A5F;font-size:12px;padding:2px 10px;border-radius:50px;font-family:'DM Sans',sans-serif;font-weight:600;margin-left:8px;">
                        <?= $nb_notifs ?> nouvelle<?= $nb_notifs > 1 ? 's' : '' ?>
                    </span>
                    <?php endif; ?>
                </h2>
            </div>
        </div>
        <?php foreach ($notifications as $notif): ?>
        <div style="padding:16px 24px;border-bottom:1px solid #E8E0E0;display:flex;align-items:flex-start;gap:14px;background:<?= $notif['lue'] ? '#fff' : '#F5F7FF' ?>;">
            <div style="width:8px;height:8px;border-radius:50%;background:<?= $notif['lue'] ? '#E8E0E0' : '#D1DCE9' ?>;flex-shrink:0;margin-top:5px;"></div>
            <div style="flex:1;">
                <div style="font-size:14px;color:#2A2727;margin-bottom:4px;font-weight:<?= $notif['lue'] ? '400' : '500' ?>;">
                    <?= htmlspecialchars($notif['message']) ?>
                </div>
                <div style="font-size:11px;color:#9A9494;">
                    <?= date('d/m/Y à H:i', strtotime($notif['cree_le'])) ?>
                </div>
            </div>
            <?php if (!empty($notif['lien'])): ?>
            <a href="<?= htmlspecialchars($notif['lien']) ?>"
               style="font-size:12px;color:#2A2727;text-decoration:none;background:#D1DCE9;padding:5px 12px;border-radius:50px;white-space:nowrap;font-weight:500;">
                Voir →
            </a>
            <?php endif; ?>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>