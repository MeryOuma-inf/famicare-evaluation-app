<?php
/**
 * admin/generer-lien.php
 * L'admin génère un lien unique pour une intervenante
 */
session_start();
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../includes/auth.php';

verif_connexion();
verif_role('admin');

$page_titre = 'Générer un lien';
$lien_genere = '';
$message = '';
$erreur  = '';

// Récupérer tous les tests actifs
$tests = $pdo->query("SELECT id, titre, categorie FROM tests WHERE actif = 1 ORDER BY titre")->fetchAll();

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $prenom  = trim($_POST['prenom']  ?? '');
    $nom     = trim($_POST['nom']     ?? '');
    $id_test = (int)($_POST['id_test'] ?? 0);

    if (!$prenom || !$nom || !$id_test) {
        $erreur = 'Tous les champs sont obligatoires.';
    } else {
        // Générer un token unique
        $token = bin2hex(random_bytes(24)); // 48 caractères aléatoires

        try {
            // Désactiver les anciens liens non utilisés pour cette personne + ce test
            $pdo->prepare(
                'UPDATE tokens_test
                 SET utilise = 1, utilise_le = NOW()
                 WHERE id_test = :id_test
                 AND LOWER(nom) = LOWER(:nom)
                 AND LOWER(prenom) = LOWER(:prenom)
                 AND utilise = 0'
            )->execute([
                ':id_test' => $id_test,
                ':nom'     => $nom,
                ':prenom'  => $prenom,
            ]);

            $pdo->prepare(
                'INSERT INTO tokens_test (token, id_test, nom, prenom, cree_le)
                 VALUES (:token, :id_test, :nom, :prenom, NOW())'
            )->execute([
                ':token'   => $token,
                ':id_test' => $id_test,
                ':nom'     => strtoupper($nom),
                ':prenom'  => ucfirst(strtolower($prenom)),
            ]);

            $lien_genere = BASE_URL . 'intervenante/test.php?token=' . $token;
            $message = 'Lien généré pour <strong>' . htmlspecialchars(ucfirst(strtolower($prenom)) . ' ' . strtoupper($nom)) . '</strong> !';

        } catch (PDOException $e) {
            $erreur = 'Erreur lors de la génération. Réessayez.';
            error_log($e->getMessage());
        }
    }
}

// Récupérer tous les tokens générés
$stmt = $pdo->query(
    'SELECT tt.*, t.titre, t.categorie
     FROM tokens_test tt
     JOIN tests t ON t.id = tt.id_test
     ORDER BY tt.cree_le DESC'
);
$tokens = $stmt->fetchAll();

$cat_emojis = ['menage' => '🧹', 'garde_enfant' => '👶', 'repassage' => '👔', 'accompagnement' => '🤝'];

require_once __DIR__ . '/../includes/header.php';
?>

<style>
    .lien-box {
        background: #D1DCE9;
        border-radius: 14px;
        padding: 18px 20px;
        display: flex;
        align-items: center;
        gap: 12px;
        margin-top: 16px;
    }
    .lien-text {
        flex: 1;
        font-size: 13px;
        color: #1E3A5F;
        font-weight: 500;
        word-break: break-all;
        font-family: 'Courier New', monospace;
    }
    .btn-copier {
        background: #2A2727;
        color: #fff;
        border: none;
        border-radius: 50px;
        padding: 8px 18px;
        font-size: 13px;
        font-weight: 500;
        cursor: pointer;
        white-space: nowrap;
        font-family: 'DM Sans', sans-serif;
        transition: all .2s;
        flex-shrink: 0;
    }
    .btn-copier:hover { background: #444; }
</style>

<div class="fc-container">

    <div class="fc-page-header">
        <div>
            <h1 class="fc-page-title">🔗 Générer un lien de test</h1>
            <p class="fc-page-subtitle">Créez un lien unique et sécurisé pour chaque intervenante</p>
        </div>
        <a href="<?= BASE_URL ?>admin/tests.php" class="fc-btn fc-btn-outline">← Retour aux tests</a>
    </div>

    <div style="display:grid;grid-template-columns:1fr 1.5fr;gap:24px;">

        <!-- FORMULAIRE -->
        <div>
            <div style="background:#fff;border-radius:20px;border:1px solid #E8E0E0;padding:28px;">
                <h2 style="font-family:'Playfair Display',serif;font-size:18px;font-weight:700;color:#2A2727;margin-bottom:20px;">
                    Nouveau lien
                </h2>

                <?php if ($erreur): ?>
                <div class="fc-alert fc-alert-error"><?= $erreur ?></div>
                <?php endif; ?>

                <?php if ($message): ?>
                <div class="fc-alert fc-alert-success"><?= $message ?></div>
                <div class="lien-box">
                    <div class="lien-text" id="lien-affiche"><?= htmlspecialchars($lien_genere) ?></div>
                    <button class="btn-copier" onclick="copierLien()">📋 Copier</button>
                </div>
                <div style="margin-top:12px;padding:12px;background:#F7E597;border-radius:10px;font-size:12px;color:#78610A;">
                    💬 Envoyez ce lien à l'intervenante par <strong>WhatsApp ou email</strong>.
                    Il ne fonctionnera qu'<strong>une seule fois</strong>.
                </div>
                <?php endif; ?>

                <form method="POST" action="" style="margin-top:20px;">
                    <div class="fc-form-group">
                        <label class="fc-label">Prénom de l'intervenante *</label>
                        <input type="text" name="prenom" class="fc-input" placeholder="Ex : Marie" required autocomplete="off">
                    </div>
                    <div class="fc-form-group">
                        <label class="fc-label">Nom de l'intervenante *</label>
                        <input type="text" name="nom" class="fc-input" placeholder="Ex : Dupont" required autocomplete="off">
                    </div>
                    <div class="fc-form-group">
                        <label class="fc-label">Test à passer *</label>
                        <select name="id_test" class="fc-select" required>
                            <option value="">-- Choisir un test --</option>
                            <?php foreach ($tests as $t): ?>
                            <option value="<?= $t['id'] ?>">
                                <?= ($cat_emojis[$t['categorie']] ?? '📋') . ' ' . htmlspecialchars($t['titre']) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <button type="submit" class="fc-btn fc-btn-accent fc-btn-full" style="height:48px;font-size:15px;">
                        ✨ Générer le lien unique
                    </button>
                </form>
            </div>

            <!-- COMMENT ÇA MARCHE -->
            <div style="background:#F5E4EB;border-radius:14px;padding:20px;margin-top:16px;">
                <h3 style="font-family:'Playfair Display',serif;font-size:15px;font-weight:700;color:#2A2727;margin-bottom:14px;">
                    📖 Comment ça marche ?
                </h3>
                <div style="display:flex;flex-direction:column;gap:12px;">
                    <?php foreach ([
                        ['1', 'Entrez le nom, prénom et choisissez le test'],
                        ['2', 'Un lien unique est généré pour CETTE intervenante'],
                        ['3', 'Copiez et envoyez le lien par WhatsApp ou email'],
                        ['4', 'L\'intervenante clique → passe son test → résultat automatique'],
                        ['5', 'Le lien est désactivé après utilisation — 1 seul passage'],
                    ] as [$n, $txt]): ?>
                    <div style="display:flex;align-items:center;gap:10px;">
                        <div style="width:24px;height:24px;background:#2A2727;color:#fff;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:11px;font-weight:700;flex-shrink:0;"><?= $n ?></div>
                        <span style="font-size:13px;color:#5A5555;font-weight:300;"><?= $txt ?></span>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- LISTE DES LIENS GÉNÉRÉS -->
        <div>
            <div style="background:#fff;border-radius:20px;border:1px solid #E8E0E0;overflow:hidden;">
                <div style="padding:20px 24px;border-bottom:1px solid #E8E0E0;display:flex;align-items:center;justify-content:space-between;">
                    <div>
                        <h2 style="font-family:'Playfair Display',serif;font-size:18px;font-weight:700;color:#2A2727;margin-bottom:2px;">Liens générés</h2>
                        <p style="font-size:12px;color:#9A9494;font-weight:300;margin:0;"><?= count($tokens) ?> lien<?= count($tokens) > 1 ? 's' : '' ?> au total</p>
                    </div>
                </div>

                <?php if (empty($tokens)): ?>
                <div style="padding:48px;text-align:center;color:#9A9494;">
                    <div style="font-size:40px;margin-bottom:12px;">🔗</div>
                    <p style="font-size:14px;font-weight:300;">Aucun lien généré pour l'instant.</p>
                </div>

                <?php else: ?>
                <?php foreach ($tokens as $tk):
                    $lien = BASE_URL . 'intervenante/test.php?token=' . $tk['token'];
                    $utilise = (bool)$tk['utilise'];
                ?>
                <div style="padding:16px 20px;border-bottom:1px solid #E8E0E0;transition:background .15s;"
                     onmouseover="this.style.background='#F5F7FF'"
                     onmouseout="this.style.background='#fff'">

                    <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:12px;margin-bottom:8px;">
                        <div>
                            <div style="font-size:14px;font-weight:600;color:#2A2727;margin-bottom:2px;">
                                <?= htmlspecialchars($tk['prenom'] . ' ' . $tk['nom']) ?>
                            </div>
                            <div style="font-size:12px;color:#9A9494;font-weight:300;">
                                <?= ($cat_emojis[$tk['categorie']] ?? '📋') ?> <?= htmlspecialchars($tk['titre']) ?>
                                · <?= date('d/m/Y à H:i', strtotime($tk['cree_le'])) ?>
                            </div>
                        </div>
                        <div style="display:flex;align-items:center;gap:8px;flex-shrink:0;">
                            <!-- Statut -->
                            <span style="background:<?= $utilise ? '#D1FAE5' : '#F7E597' ?>;color:<?= $utilise ? '#065F46' : '#78610A' ?>;font-size:11px;font-weight:600;padding:3px 10px;border-radius:50px;">
                                <?= $utilise ? '✅ Utilisé' : '⏳ En attente' ?>
                            </span>
                            <!-- Copier le lien si pas encore utilisé -->
                            <?php if (!$utilise): ?>
                            <button onclick="copierLienDirect('<?= htmlspecialchars($lien) ?>', this)"
                                    style="background:#D1DCE9;color:#1E3A5F;border:none;border-radius:50px;padding:4px 12px;font-size:11px;font-weight:500;cursor:pointer;transition:background .2s;font-family:'DM Sans',sans-serif;"
                                    onmouseover="this.style.background='#B8CAE0'"
                                    onmouseout="this.style.background='#D1DCE9'">
                                📋 Copier
                            </button>
                            <?php endif; ?>
                        </div>
                    </div>

                    <?php if ($utilise && $tk['utilise_le']): ?>
                    <div style="font-size:11px;color:#10B981;font-weight:500;">
                        ✓ Test passé le <?= date('d/m/Y à H:i', strtotime($tk['utilise_le'])) ?>
                    </div>
                    <?php endif; ?>

                </div>
                <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- TOAST -->
    <div id="toast" style="position:fixed;bottom:28px;left:50%;transform:translateX(-50%) translateY(80px);background:#2A2727;color:#fff;padding:12px 24px;border-radius:50px;font-size:13px;font-weight:500;opacity:0;transition:all .3s;z-index:9999;">
        ✅ Lien copié dans le presse-papier !
    </div>

</div>

<script>
function copierLien() {
    const lien = document.getElementById('lien-affiche').textContent;
    navigator.clipboard.writeText(lien).then(() => showToast());
}

function copierLienDirect(lien, btn) {
    navigator.clipboard.writeText(lien).then(() => {
        btn.textContent = '✅ Copié !';
        setTimeout(() => btn.textContent = '📋 Copier', 2000);
        showToast();
    });
}

function showToast() {
    const t = document.getElementById('toast');
    t.style.opacity = '1';
    t.style.transform = 'translateX(-50%) translateY(0)';
    setTimeout(() => {
        t.style.opacity = '0';
        t.style.transform = 'translateX(-50%) translateY(80px)';
    }, 2500);
}
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>