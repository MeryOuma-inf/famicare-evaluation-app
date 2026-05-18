<?php
/**
 * includes/header.php — Charte FamiCare officielle
 * Avec cloche notifications visible dans la navbar
 */
if (!defined('BASE_URL')) {
    require_once __DIR__ . '/../config.php';
}
if (!isset($pdo)) require_once __DIR__ . '/../db.php';

// Compter les notifications non lues (admin uniquement)
$nb_notifs_non_lues = 0;
$notifs_header = [];
if (isset($_SESSION['utilisateur']) && $_SESSION['utilisateur']['role'] === 'admin') {
    try {
        $s = $pdo->query("SELECT COUNT(*) FROM notifications WHERE lue = 0");
        $nb_notifs_non_lues = (int)$s->fetchColumn();
        $sn = $pdo->query("SELECT * FROM notifications ORDER BY cree_le DESC LIMIT 8");
        $notifs_header = $sn->fetchAll();
    } catch (Exception $e) {}
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($page_titre) ? htmlspecialchars($page_titre) . ' — FamiCare' : 'FamiCare Évaluation' ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,600;0,700;1,400&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link href="<?= BASE_URL ?>assets/css/style.css" rel="stylesheet">
    <style>
        :root {
            --fc-bleu:   #D1DCE9;
            --fc-jaune:  #F7E597;
            --fc-rose:   #F5E4EB;
            --fc-blanc:  #FFFFFF;
            --fc-noir:   #2A2727;
            --fc-muted:  #9A9494;
            --fc-border: #E8E0E0;
        }
        body { background:#fff; color:#2A2727; }
        h1,h2,h3,h4 { font-family:'Playfair Display',serif !important; }

        /* CLOCHE */
        .notif-wrap { position:relative; }
        .notif-btn {
            position:relative; width:40px; height:40px;
            border-radius:50%; background:#F5E4EB;
            border:1.5px solid #E8C8D8;
            display:flex; align-items:center; justify-content:center;
            cursor:pointer; font-size:18px;
            transition:all .2s; flex-shrink:0;
        }
        .notif-btn:hover { background:#E8C8D8; transform:scale(1.05); }
        .notif-badge {
            position:absolute; top:-4px; right:-4px;
            background:#EF4444; color:#fff;
            font-size:10px; font-weight:700;
            min-width:18px; height:18px;
            border-radius:50px;
            display:flex; align-items:center; justify-content:center;
            padding:0 4px; border:2px solid #fff;
            font-family:'DM Sans',sans-serif;
            animation: pulse 2s infinite;
        }
        @keyframes pulse {
            0%,100% { box-shadow:0 0 0 0 rgba(239,68,68,.4); }
            50%      { box-shadow:0 0 0 6px rgba(239,68,68,0); }
        }

        /* PANNEAU */
        .notif-panel {
            position:absolute; top:calc(100% + 12px); right:0;
            width:360px; background:#fff;
            border-radius:16px;
            box-shadow:0 8px 40px rgba(42,39,39,.18);
            border:1px solid #E8E0E0;
            z-index:9999; display:none;
            animation:slideDown .2s ease;
            overflow:hidden;
        }
        .notif-panel.open { display:block; }
        @keyframes slideDown {
            from { opacity:0; transform:translateY(-8px); }
            to   { opacity:1; transform:translateY(0); }
        }

        .notif-panel-header {
            padding:16px 20px; border-bottom:1px solid #E8E0E0;
            display:flex; align-items:center; justify-content:space-between;
            background:#2A2727;
        }
        .notif-panel-header h3 {
            font-family:'Playfair Display',serif;
            font-size:15px; font-weight:700; color:#fff; margin:0;
        }
        .notif-tout-lire {
            font-size:11px; color:#F7E597; text-decoration:none; font-weight:500;
        }
        .notif-tout-lire:hover { opacity:.75; color:#F7E597; }

        .notif-item {
            padding:14px 20px; border-bottom:1px solid #E8E0E0;
            display:flex; align-items:flex-start; gap:12px;
            transition:background .15s; text-decoration:none; color:inherit;
        }
        .notif-item:last-child { border-bottom:none; }
        .notif-item:hover { background:#F5F7FF; text-decoration:none; }
        .notif-item.non-lue { background:#FFF8F0; border-left:3px solid #D1DCE9; }

        .notif-dot {
            width:8px; height:8px; border-radius:50%;
            flex-shrink:0; margin-top:5px;
        }
        .notif-dot.rouge { background:#EF4444; }
        .notif-dot.gris  { background:#E8E0E0; }

        .notif-msg {
            flex:1; font-size:13px; color:#2A2727;
            line-height:1.5; font-weight:400;
        }
        .notif-msg.lue { color:#9A9494; font-weight:300; }
        .notif-time { font-size:11px; color:#9A9494; font-weight:300; margin-top:3px; }

        .notif-vide {
            padding:32px 20px; text-align:center;
            color:#9A9494; font-size:13px; font-weight:300;
        }
        .notif-footer {
            padding:12px 20px; border-top:1px solid #E8E0E0;
            text-align:center; background:#F5E4EB;
        }
        .notif-footer a {
            font-size:12px; color:#2A2727; font-weight:500; text-decoration:none;
        }
        .notif-footer a:hover { text-decoration:underline; }
    </style>
</head>
<body>

<nav class="fc-navbar">
    <div class="fc-navbar-inner">
        <a href="<?= BASE_URL ?>" class="fc-navbar-logo">
            <img src="<?= BASE_URL ?>assets/images/monlogo.png" alt="FamiCare">
        </a>

        <?php if (isset($_SESSION['utilisateur'])): ?>
        <div class="fc-navbar-links">
            <?php if ($_SESSION['utilisateur']['role'] === 'admin'): ?>
                <a href="<?= BASE_URL ?>admin/dashboard.php" class="fc-nav-link <?= basename($_SERVER['PHP_SELF']) === 'dashboard.php' ? 'active' : '' ?>">Tableau de bord</a>
                <a href="<?= BASE_URL ?>admin/tests.php"     class="fc-nav-link <?= basename($_SERVER['PHP_SELF']) === 'tests.php'     ? 'active' : '' ?>">Gérer les tests</a>
                <a href="<?= BASE_URL ?>admin/resultats.php" class="fc-nav-link <?= basename($_SERVER['PHP_SELF']) === 'resultats.php' ? 'active' : '' ?>">Résultats</a>
            <?php endif; ?>
        </div>

        <div class="fc-navbar-right">
            <?php if ($_SESSION['utilisateur']['role'] === 'admin'): ?>

            <!-- ===== CLOCHE NOTIFICATIONS ===== -->
            <div class="notif-wrap">
                <button class="notif-btn" onclick="toggleNotif()" title="Notifications">
                    🔔
                    <?php if ($nb_notifs_non_lues > 0): ?>
                    <span class="notif-badge"><?= min($nb_notifs_non_lues, 99) ?></span>
                    <?php endif; ?>
                </button>

                <div class="notif-panel" id="notifPanel">
                    <!-- Header panneau -->
                    <div class="notif-panel-header">
                        <h3>
                            Notifications
                            <?php if ($nb_notifs_non_lues > 0): ?>
                            <span style="background:#EF4444;color:#fff;font-size:10px;padding:2px 8px;border-radius:50px;font-family:'DM Sans',sans-serif;margin-left:6px;">
                                <?= $nb_notifs_non_lues ?> non lue<?= $nb_notifs_non_lues > 1 ? 's' : '' ?>
                            </span>
                            <?php endif; ?>
                        </h3>
                        <?php if ($nb_notifs_non_lues > 0): ?>
                        <a href="<?= BASE_URL ?>admin/notifications.php?tout_lire=1" class="notif-tout-lire">✓ Tout lu</a>
                        <?php endif; ?>
                    </div>

                    <!-- Liste notifications -->
                    <?php if (empty($notifs_header)): ?>
                    <div class="notif-vide">
                        <div style="font-size:32px;margin-bottom:8px;">🔕</div>
                        Aucune notification
                    </div>
                    <?php else: ?>
                    <?php foreach ($notifs_header as $n):
                        $est_lue = (bool)$n['lue'];
                        $lien    = !empty($n['lien']) ? $n['lien'] : '#';
                        $diff    = time() - strtotime($n['cree_le']);
                        if ($diff < 60)        $temps = 'À l\'instant';
                        elseif ($diff < 3600)  $temps = floor($diff/60) . ' min';
                        elseif ($diff < 86400) $temps = floor($diff/3600) . 'h';
                        else                   $temps = date('d/m', strtotime($n['cree_le']));
                    ?>
                    <a href="<?= BASE_URL ?>admin/notifications.php?lire=<?= $n['id'] ?>&redirect=<?= urlencode($lien) ?>"
                       class="notif-item <?= $est_lue ? '' : 'non-lue' ?>">
                        <div class="notif-dot <?= $est_lue ? 'gris' : 'rouge' ?>"></div>
                        <div style="flex:1;">
                            <div class="notif-msg <?= $est_lue ? 'lue' : '' ?>">
                                <?= htmlspecialchars($n['message']) ?>
                            </div>
                            <div class="notif-time"><?= $temps ?></div>
                        </div>
                    </a>
                    <?php endforeach; ?>
                    <div class="notif-footer">
                        <a href="<?= BASE_URL ?>admin/notifications.php">Voir toutes →</a>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>

            <span class="fc-nav-user">👤 <?= htmlspecialchars($_SESSION['utilisateur']['prenom']) ?></span>
            <a href="<?= BASE_URL ?>logout.php" class="fc-btn-logout">Déconnexion</a>
        </div>
        <?php endif; ?>
    </div>
</nav>

<script>
function toggleNotif() {
    const p = document.getElementById('notifPanel');
    p.classList.toggle('open');
    if (p.classList.contains('open')) {
        setTimeout(() => document.addEventListener('click', fermerNotif), 100);
    }
}
function fermerNotif(e) {
    const w = document.querySelector('.notif-wrap');
    if (w && !w.contains(e.target)) {
        document.getElementById('notifPanel').classList.remove('open');
        document.removeEventListener('click', fermerNotif);
    }
}
</script>

<main class="fc-main">