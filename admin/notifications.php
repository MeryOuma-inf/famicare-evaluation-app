<?php
session_start();
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../includes/auth.php';

verif_connexion();
verif_role('admin');
$page_titre = 'Notifications';

// Marquer une notification comme lue et rediriger
if (isset($_GET['lire']) && is_numeric($_GET['lire'])) {
    $pdo->prepare("UPDATE notifications SET lue = 1 WHERE id = :id")
        ->execute([':id' => (int)$_GET['lire']]);
    $redirect = $_GET['redirect'] ?? (BASE_URL . 'admin/dashboard.php');
    header('Location: ' . $redirect);
    exit;
}

// Tout marquer comme lu
if (isset($_GET['tout_lire'])) {
    $pdo->exec("UPDATE notifications SET lue = 1");
    header('Location: notifications.php');
    exit;
}

// Supprimer toutes les notifications lues
if (isset($_GET['supprimer_lues'])) {
    $pdo->exec("DELETE FROM notifications WHERE lue = 1");
    header('Location: notifications.php');
    exit;
}

// Toutes les notifications
$stmt = $pdo->query("SELECT * FROM notifications ORDER BY cree_le DESC");
$toutes = $stmt->fetchAll();

$nb_non_lues = count(array_filter($toutes, fn($n) => !$n['lue']));

require_once __DIR__ . '/../includes/header.php';
?>

<div class="fc-container">

    <div class="fc-page-header">
        <div>
            <h1 class="fc-page-title">🔔 Notifications</h1>
            <p class="fc-page-subtitle">
                <?= count($toutes) ?> notification<?= count($toutes) > 1 ? 's' : '' ?>
                · <?= $nb_non_lues ?> non lue<?= $nb_non_lues > 1 ? 's' : '' ?>
            </p>
        </div>
        <div style="display:flex;gap:10px;">
            <?php if ($nb_non_lues > 0): ?>
            <a href="notifications.php?tout_lire=1" class="fc-btn fc-btn-primary">
                ✓ Tout marquer comme lu
            </a>
            <?php endif; ?>
            <a href="notifications.php?supprimer_lues=1"
               onclick="return confirm('Supprimer toutes les notifications lues ?')"
               class="fc-btn fc-btn-outline">
                🗑️ Supprimer les lues
            </a>
        </div>
    </div>

    <div style="background:#fff;border-radius:20px;border:1px solid #E8E0E0;overflow:hidden;">

        <?php if (empty($toutes)): ?>
        <div style="padding:64px;text-align:center;color:#9A9494;">
            <div style="font-size:48px;margin-bottom:16px;">🔕</div>
            <h2 style="font-family:'Playfair Display',serif;font-size:20px;color:#2A2727;margin-bottom:8px;">Aucune notification</h2>
            <p style="font-size:14px;font-weight:300;">Les notifications apparaîtront ici quand des intervenantes passeront leurs tests.</p>
        </div>

        <?php else: ?>

        <!-- Filtre -->
        <div style="padding:16px 20px;border-bottom:1px solid #E8E0E0;display:flex;gap:8px;background:#F5F7FF;">
            <span style="font-size:13px;color:#9A9494;align-self:center;">Filtrer :</span>
            <button onclick="filtrer('toutes')"  id="btn-toutes"   class="fc-btn fc-btn-sm fc-btn-primary" style="border-radius:50px;">Toutes (<?= count($toutes) ?>)</button>
            <button onclick="filtrer('non-lues')" id="btn-non-lues" class="fc-btn fc-btn-sm fc-btn-outline" style="border-radius:50px;">Non lues (<?= $nb_non_lues ?>)</button>
            <button onclick="filtrer('lues')"    id="btn-lues"    class="fc-btn fc-btn-sm fc-btn-outline" style="border-radius:50px;">Lues (<?= count($toutes) - $nb_non_lues ?>)</button>
        </div>

        <div id="liste-notifs">
        <?php foreach ($toutes as $n):
            $est_lue = (bool)$n['lue'];
            $lien    = !empty($n['lien']) ? $n['lien'] : null;
            $diff    = time() - strtotime($n['cree_le']);
            if ($diff < 60)        $temps = 'À l\'instant';
            elseif ($diff < 3600)  $temps = 'Il y a ' . floor($diff/60) . ' min';
            elseif ($diff < 86400) $temps = 'Il y a ' . floor($diff/3600) . 'h';
            else                   $temps = 'Le ' . date('d/m/Y à H:i', strtotime($n['cree_le']));
        ?>
        <div class="notif-ligne <?= $est_lue ? 'lue' : 'non-lue' ?>"
             style="padding:18px 24px;border-bottom:1px solid #E8E0E0;display:flex;align-items:center;gap:16px;transition:background .15s;background:<?= $est_lue ? '#fff' : '#FFF8F0' ?>;"
             onmouseover="this.style.background='#F5F7FF'"
             onmouseout="this.style.background='<?= $est_lue ? '#fff' : '#FFF8F0' ?>'">

            <!-- Indicateur lu/non lu -->
            <div style="width:10px;height:10px;border-radius:50%;background:<?= $est_lue ? '#E8E0E0' : '#D1DCE9' ?>;flex-shrink:0;"></div>

            <!-- Contenu -->
            <div style="flex:1;">
                <div style="font-size:14px;font-weight:<?= $est_lue ? '300' : '500' ?>;color:<?= $est_lue ? '#9A9494' : '#2A2727' ?>;margin-bottom:4px;line-height:1.5;">
                    <?= htmlspecialchars($n['message']) ?>
                </div>
                <div style="font-size:12px;color:#9A9494;font-weight:300;">
                    🕐 <?= $temps ?>
                    · <?= $est_lue
                        ? '<span style="color:#10B981;font-weight:500;">✓ Lue</span>'
                        : '<span style="color:#D1DCE9;font-weight:600;">● Non lue</span>' ?>
                </div>
            </div>

            <!-- Actions -->
            <div style="display:flex;gap:8px;align-items:center;flex-shrink:0;">
                <?php if ($lien): ?>
                <a href="notifications.php?lire=<?= $n['id'] ?>&redirect=<?= urlencode($lien) ?>"
                   style="background:#D1DCE9;color:#1E3A5F;border-radius:50px;padding:6px 14px;font-size:12px;font-weight:500;text-decoration:none;white-space:nowrap;transition:background .2s;"
                   onmouseover="this.style.background='#B8CAE0'"
                   onmouseout="this.style.background='#D1DCE9'">
                    Voir le détail →
                </a>
                <?php endif; ?>
                <?php if (!$est_lue): ?>
                <a href="notifications.php?lire=<?= $n['id'] ?>"
                   style="background:#F5E4EB;color:#7A2A4A;border-radius:50px;padding:6px 14px;font-size:12px;font-weight:500;text-decoration:none;transition:background .2s;"
                   onmouseover="this.style.background='#E8C8D8'"
                   onmouseout="this.style.background='#F5E4EB'">
                    ✓ Marquer lu
                </a>
                <?php endif; ?>
            </div>
        </div>
        <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</div>

<script>
function filtrer(type) {
    const lignes = document.querySelectorAll('.notif-ligne');
    lignes.forEach(l => {
        if (type === 'toutes') l.style.display = 'flex';
        else if (type === 'non-lues') l.style.display = l.classList.contains('non-lue') ? 'flex' : 'none';
        else l.style.display = l.classList.contains('lue') ? 'flex' : 'none';
    });
    document.querySelectorAll('[id^="btn-"]').forEach(b => b.className = 'fc-btn fc-btn-sm fc-btn-outline');
    document.getElementById('btn-' + type.replace('-','') === 'btn-nonlues' ? 'btn-non-lues' : 'btn-' + type).className = 'fc-btn fc-btn-sm fc-btn-primary';
}
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>