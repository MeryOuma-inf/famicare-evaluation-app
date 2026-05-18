<?php
/**
 * intervenante/test.php — Token unique par intervenante
 */
session_start();
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../includes/mailer.php';

$token = trim($_GET['token'] ?? '');
if (empty($token)) afficher_erreur('Lien invalide', 'Ce lien est incorrect. Contactez votre responsable FamiCare.');

$stmt = $pdo->prepare('SELECT tt.*, t.titre, t.description, t.categorie, t.duree_limite, t.actif FROM tokens_test tt JOIN tests t ON t.id = tt.id_test WHERE tt.token = :token LIMIT 1');
$stmt->execute([':token' => $token]);
$td = $stmt->fetch();

if (!$td) afficher_erreur('Lien invalide', 'Ce lien n\'existe pas. Contactez votre responsable FamiCare.');
if (!$td['actif']) afficher_erreur('Test indisponible', 'Ce test n\'est pas disponible actuellement.');

if ($td['utilise']) {
    $stmt = $pdo->prepare('SELECT rp.*, t.titre AS titre_test FROM resultats_publics rp JOIN tests t ON t.id = rp.id_test WHERE rp.token = :token LIMIT 1');
    $stmt->execute([':token' => $token]);
    $res = $stmt->fetch();
    afficher_resultat_existant($td, $res);
    exit;
}

$id_test = (int)$td['id_test'];
$stmt = $pdo->prepare('SELECT q.id, q.texte, q.image_path, q.points, q.ordre, GROUP_CONCAT(cr.id ORDER BY cr.id SEPARATOR "||") AS choix_ids, GROUP_CONCAT(cr.texte ORDER BY cr.id SEPARATOR "||") AS choix_textes, GROUP_CONCAT(cr.est_correcte ORDER BY cr.id SEPARATOR "||") AS choix_corrects FROM questions q LEFT JOIN choix_reponses cr ON cr.id_question = q.id WHERE q.id_test = :id GROUP BY q.id ORDER BY q.ordre ASC');
$stmt->execute([':id' => $id_test]);
$raws = $stmt->fetchAll();

$questions = [];
foreach ($raws as $q) {
    $ids = explode('||', $q['choix_ids'] ?? '');
    $txts = explode('||', $q['choix_textes'] ?? '');
    $cors = explode('||', $q['choix_corrects'] ?? '');
    $choix = [];
    for ($i = 0; $i < count($ids); $i++) {
        if (!empty($ids[$i])) $choix[] = ['id' => $ids[$i], 'texte' => $txts[$i], 'est_correcte' => $cors[$i]];
    }
    shuffle($choix);
    $questions[] = ['id' => $q['id'], 'texte' => $q['texte'], 'image_path' => $q['image_path'], 'points' => $q['points'], 'choix' => $choix];
}
$nb = count($questions);

$cle = 'tok_' . md5($token);
if (!isset($_SESSION[$cle])) {
    $_SESSION[$cle] = ['etape' => 'intro', 'q_index' => 0, 'reponses' => [], 'debut' => 0];
}
$s = &$_SESSION[$cle];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    if ($action === 'commencer') { $s['etape'] = 'questions'; $s['debut'] = time(); }
    if ($action === 'repondre') {
        $iq = (int)($_POST['id_question'] ?? 0);
        $ic = (int)($_POST['id_choix'] ?? 0);
        if ($iq && $ic) $s['reponses'][$iq] = $ic;
        $s['q_index']++;
        if ($s['q_index'] >= $nb) $s['etape'] = 'calcul';
    }
    if ($action === 'precedent' && $s['q_index'] > 0) $s['q_index']--;
    header('Location: test.php?token=' . urlencode($token)); exit;
}

if ($s['etape'] === 'calcul') {
    $score = 0; $max = 0; $details = [];
    foreach ($questions as $q) {
        $max += $q['points'];
        $ic = $s['reponses'][$q['id']] ?? null;
        $ok = false;
        foreach ($q['choix'] as $c) {
            if ($c['id'] == $ic && $c['est_correcte'] == 1) { $ok = true; $score += $q['points']; break; }
        }
        $details[] = ['question' => $q['texte'], 'image_path' => $q['image_path'], 'choix' => $q['choix'], 'id_choix_donne' => $ic, 'est_correcte' => $ok];
    }
    $pct = $max > 0 ? round(($score / $max) * 100) : 0;
    $mention = $pct < 50 ? 'insuffisant' : ($pct < 75 ? 'satisfaisant' : ($pct < 90 ? 'bien' : 'excellent'));
    $duree = time() - $s['debut'];
    try {
        $pdo->beginTransaction();
        $pdo->prepare('INSERT INTO resultats_publics (nom, prenom, id_test, token, score, pourcentage, mention, duree_sec, passe_le) VALUES (:nom, :prenom, :id_test, :token, :score, :pct, :mention, :duree, NOW())')
            ->execute([':nom' => $td['nom'], ':prenom' => $td['prenom'], ':id_test' => $id_test, ':token' => $token, ':score' => $score, ':pct' => $pct, ':mention' => $mention, ':duree' => $duree]);
        $id_res = $pdo->lastInsertId();
        $pdo->prepare('UPDATE tokens_test SET utilise = 1, utilise_le = NOW() WHERE token = :token')->execute([':token' => $token]);
        $pdo->commit();
        envoyer_email_famicare($td['prenom'], $td['nom'], ['titre_test' => $td['titre'], 'pourcentage' => $pct, 'mention' => $mention, 'score' => $score, 'duree_sec' => $duree]);
        $emojis = ['insuffisant' => '⚠️', 'satisfaisant' => '👍', 'bien' => '✅', 'excellent' => '🌟'];
        creer_notification($pdo, ($emojis[$mention] ?? '📋') . ' ' . $td['prenom'] . ' ' . $td['nom'] . ' — "' . $td['titre'] . '" — ' . $pct . '% (' . ucfirst($mention) . ')', BASE_URL . 'admin/intervenante-detail.php?id=' . $id_res);
    } catch (PDOException $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        error_log('Erreur: ' . $e->getMessage());
    }
    $s['etape'] = 'resultat'; $s['score'] = $score; $s['score_max'] = $max;
    $s['pourcentage'] = $pct; $s['mention'] = $mention; $s['duree_sec'] = $duree; $s['details'] = $details;
    header('Location: test.php?token=' . urlencode($token)); exit;
}

$ms = ['insuffisant' => ['bg' => '#FEE2E2','color' => '#991B1B','emoji' => '😔','msg' => 'Ne vous découragez pas ! La pratique fait la différence.'], 'satisfaisant' => ['bg' => '#F7E597','color' => '#78610A','emoji' => '👍','msg' => 'Bon début ! Vous progressez dans la bonne direction.'], 'bien' => ['bg' => '#D1DCE9','color' => '#1E3A5F','emoji' => '😊','msg' => 'Très bien ! Vous maîtrisez bien votre métier.'], 'excellent' => ['bg' => '#D1FAE5','color' => '#065F46','emoji' => '🌟','msg' => 'Excellent ! Vous êtes prête pour toutes les missions FamiCare.']];
$cat_labels = ['menage' => 'Ménage & Repassage','garde_enfant' => 'Garde d\'enfants','repassage' => 'Repassage','accompagnement' => 'Accompagnement'];
$cat_emojis = ['menage' => '🧹','garde_enfant' => '👶','repassage' => '👔','accompagnement' => '🤝'];
$cat_label = $cat_labels[$td['categorie']] ?? $td['categorie'];
$cat_emoji = $cat_emojis[$td['categorie']] ?? '📋';

function afficher_erreur($titre, $msg) {
    echo '<!DOCTYPE html><html lang="fr"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>FamiCare</title><link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=DM+Sans:wght@300;400&display=swap" rel="stylesheet"></head><body style="margin:0;background:#F5E4EB;font-family:DM Sans,sans-serif;display:flex;align-items:center;justify-content:center;min-height:100vh;"><div style="max-width:420px;background:#fff;border-radius:24px;padding:48px;text-align:center;box-shadow:0 8px 32px rgba(42,39,39,.12);"><div style="font-size:56px;margin-bottom:20px;">❌</div><h2 style="font-family:Playfair Display,serif;font-size:22px;color:#2A2727;margin-bottom:12px;">' . htmlspecialchars($titre) . '</h2><p style="color:#9A9494;font-size:14px;font-weight:300;line-height:1.7;">' . htmlspecialchars($msg) . '</p></div></body></html>';
    exit;
}

function afficher_resultat_existant($td, $res) {
    $ms = ['insuffisant' => ['bg' => '#FEE2E2','color' => '#991B1B','emoji' => '😔'],'satisfaisant' => ['bg' => '#F7E597','color' => '#78610A','emoji' => '👍'],'bien' => ['bg' => '#D1DCE9','color' => '#1E3A5F','emoji' => '😊'],'excellent' => ['bg' => '#D1FAE5','color' => '#065F46','emoji' => '🌟']];
    echo '<!DOCTYPE html><html lang="fr"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Résultat — FamiCare</title><link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet"></head><body style="margin:0;background:#F5E4EB;font-family:DM Sans,sans-serif;display:flex;align-items:center;justify-content:center;min-height:100vh;padding:20px;"><div style="max-width:480px;width:100%;background:#fff;border-radius:24px;overflow:hidden;box-shadow:0 8px 32px rgba(42,39,39,.12);">';
    if ($res) {
        $st = $ms[$res['mention']] ?? $ms['bien'];
        $d = $res['duree_sec'];
        echo '<div style="background:' . $st['bg'] . ';padding:36px;text-align:center;"><div style="font-size:48px;margin-bottom:10px;">' . $st['emoji'] . '</div><div style="font-family:Playfair Display,serif;font-size:64px;font-weight:700;color:' . $st['color'] . ';line-height:1;">' . $res['pourcentage'] . '%</div><div style="display:inline-block;background:' . $st['color'] . '22;color:' . $st['color'] . ';padding:5px 18px;border-radius:50px;font-size:13px;font-weight:700;text-transform:capitalize;margin-top:8px;">' . ucfirst($res['mention']) . '</div></div><div style="padding:28px;text-align:center;"><h2 style="font-family:Playfair Display,serif;font-size:20px;color:#2A2727;margin-bottom:8px;">Test déjà passé</h2><p style="color:#9A9494;font-size:13px;font-weight:300;line-height:1.7;margin-bottom:20px;">Bonjour <strong style="color:#2A2727;">' . htmlspecialchars($td['prenom']) . '</strong>, vous avez passé ce test le <strong>' . date('d/m/Y', strtotime($res['passe_le'])) . '</strong>.</p><div style="display:grid;grid-template-columns:repeat(3,1fr);border:1px solid #E8E0E0;border-radius:14px;overflow:hidden;margin-bottom:20px;"><div style="padding:16px;text-align:center;border-right:1px solid #E8E0E0;"><div style="font-family:Playfair Display,serif;font-size:22px;font-weight:700;color:#2A2727;">' . $res['score'] . '</div><div style="font-size:11px;color:#9A9494;font-weight:300;margin-top:3px;">Points</div></div><div style="padding:16px;text-align:center;border-right:1px solid #E8E0E0;"><div style="font-family:Playfair Display,serif;font-size:22px;font-weight:700;color:#2A2727;">' . floor($d/60) . 'min</div><div style="font-size:11px;color:#9A9494;font-weight:300;margin-top:3px;">Durée</div></div><div style="padding:16px;text-align:center;"><div style="font-family:Playfair Display,serif;font-size:22px;font-weight:700;color:#2A2727;">' . date('d/m', strtotime($res['passe_le'])) . '</div><div style="font-size:11px;color:#9A9494;font-weight:300;margin-top:3px;">Date</div></div></div><div style="background:#D1DCE9;border-radius:12px;padding:14px;font-size:13px;color:#1E3A5F;">📧 Votre résultat a été transmis à FamiCare.</div></div>';
    } else {
        echo '<div style="padding:48px;text-align:center;"><div style="font-size:48px;margin-bottom:16px;">✅</div><h2 style="font-family:Playfair Display,serif;font-size:22px;color:#2A2727;margin-bottom:12px;">Test déjà passé</h2><p style="color:#9A9494;font-size:13px;font-weight:300;">Bonjour ' . htmlspecialchars($td['prenom']) . ', votre résultat a été transmis à FamiCare.</p></div>';
    }
    echo '</div></body></html>';
    exit;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($td['titre']) ?> — FamiCare</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,700&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        :root{--bleu:#D1DCE9;--jaune:#F7E597;--rose:#F5E4EB;--noir:#2A2727;--muted:#9A9494;--blanc:#FFFFFF;--border:#E8E0E0;}
        *{margin:0;padding:0;box-sizing:border-box;}
        body{font-family:'DM Sans',sans-serif;background:var(--rose);color:var(--noir);min-height:100vh;}
        .t-header{background:var(--blanc);border-bottom:1px solid var(--border);padding:14px 24px;display:flex;align-items:center;justify-content:space-between;position:sticky;top:0;z-index:10;box-shadow:0 2px 8px rgba(42,39,39,.06);}
        .t-header img{height:32px;}
        .t-badge{background:var(--bleu);color:#1E3A5F;border-radius:50px;padding:5px 14px;font-size:12px;font-weight:600;}
        .progress-wrap{background:var(--blanc);padding:12px 24px 0;border-bottom:1px solid var(--border);}
        .progress-info{display:flex;justify-content:space-between;font-size:13px;color:var(--muted);margin-bottom:8px;}
        .progress-info strong{color:var(--noir);}
        .progress-track{height:6px;background:var(--border);border-radius:50px;overflow:hidden;margin-bottom:12px;}
        .progress-fill{height:100%;background:var(--noir);border-radius:50px;transition:width .4s ease;}
        .t-wrap{max-width:660px;margin:0 auto;padding:28px 16px 60px;}
        .t-card{background:var(--blanc);border-radius:20px;border:1px solid var(--border);box-shadow:0 4px 20px rgba(42,39,39,.07);overflow:hidden;animation:fadeUp .35s cubic-bezier(.22,1,.36,1);}
        @keyframes fadeUp{from{opacity:0;transform:translateY(18px)}to{opacity:1;transform:translateY(0)}}
        .intro-visual{height:200px;display:flex;align-items:center;justify-content:center;font-size:80px;background:linear-gradient(135deg,var(--bleu),var(--rose));}
        .intro-body{padding:32px;text-align:center;}
        .intro-titre{font-family:'Playfair Display',serif;font-size:26px;font-weight:700;color:var(--noir);margin-bottom:8px;line-height:1.2;}
        .intro-sub{font-size:14px;color:var(--muted);font-weight:300;line-height:1.7;margin-bottom:20px;}
        .intro-pills{display:flex;justify-content:center;gap:10px;flex-wrap:wrap;margin-bottom:24px;}
        .intro-pill{background:var(--bleu);color:#1E3A5F;border-radius:50px;padding:6px 16px;font-size:12px;font-weight:600;}
        .avertissement{background:var(--jaune);border-radius:12px;padding:14px 18px;margin-bottom:24px;display:flex;align-items:flex-start;gap:10px;font-size:13px;color:#78610A;line-height:1.6;text-align:left;}
        .btn-main{width:100%;height:56px;background:var(--noir);color:var(--blanc);border:none;border-radius:50px;font-family:'DM Sans',sans-serif;font-size:16px;font-weight:600;cursor:pointer;transition:all .22s;display:flex;align-items:center;justify-content:center;gap:10px;}
        .btn-main:hover{background:#444;transform:translateY(-2px);box-shadow:0 8px 24px rgba(42,39,39,.25);}
        .question-img{width:100%;height:320px;object-fit:cover;object-position:center;display:block;}
        .question-body{padding:24px;}
        .question-num{font-size:11px;font-weight:700;letter-spacing:1.5px;text-transform:uppercase;color:var(--muted);margin-bottom:10px;display:block;}
        .question-texte{font-family:'Playfair Display',serif;font-size:22px;font-weight:700;color:var(--noir);line-height:1.35;margin-bottom:24px;}
        .choix-list{display:flex;flex-direction:column;gap:10px;margin-bottom:24px;}
        .choix-btn{width:100%;min-height:56px;padding:14px 18px;background:var(--rose);border:2px solid var(--border);border-radius:14px;font-family:'DM Sans',sans-serif;font-size:15px;font-weight:400;color:var(--noir);cursor:pointer;text-align:left;transition:all .2s;display:flex;align-items:center;gap:14px;line-height:1.4;}
        .choix-btn:hover{border-color:var(--noir);background:var(--blanc);transform:translateX(4px);}
        .choix-btn.selected{border-color:var(--noir);background:var(--bleu);font-weight:500;}
        .choix-lettre{width:32px;height:32px;border-radius:50%;background:var(--border);display:flex;align-items:center;justify-content:center;font-size:13px;font-weight:700;color:var(--muted);flex-shrink:0;transition:all .2s;}
        .choix-btn.selected .choix-lettre{background:var(--noir);color:var(--blanc);}
        .q-nav{display:flex;gap:12px;}
        .btn-prec{height:48px;padding:0 22px;background:var(--rose);border:1.5px solid var(--border);border-radius:50px;font-family:'DM Sans',sans-serif;font-size:14px;font-weight:500;color:var(--muted);cursor:pointer;transition:all .2s;}
        .btn-prec:hover{background:var(--border);}
        .btn-next{flex:1;height:48px;background:var(--noir);color:var(--blanc);border:none;border-radius:50px;font-family:'DM Sans',sans-serif;font-size:15px;font-weight:600;cursor:pointer;transition:all .22s;opacity:.4;pointer-events:none;}
        .btn-next.actif{opacity:1;pointer-events:all;}
        .btn-next.actif:hover{background:#444;transform:translateY(-1px);}
        .result-header{padding:36px;text-align:center;}
        .result-emoji{font-size:56px;margin-bottom:12px;}
        .result-score{font-family:'Playfair Display',serif;font-size:72px;font-weight:700;line-height:1;margin-bottom:10px;}
        .result-mention{display:inline-block;padding:6px 20px;border-radius:50px;font-size:14px;font-weight:700;text-transform:capitalize;margin-bottom:12px;}
        .result-msg{font-size:14px;color:var(--muted);font-weight:300;line-height:1.65;}
        .result-stats{display:grid;grid-template-columns:repeat(3,1fr);border-top:1px solid var(--border);}
        .rs-item{padding:18px;text-align:center;border-right:1px solid var(--border);}
        .rs-item:last-child{border-right:none;}
        .rs-num{font-family:'Playfair Display',serif;font-size:26px;font-weight:700;color:var(--noir);display:block;line-height:1;}
        .rs-label{font-size:11px;color:var(--muted);font-weight:300;margin-top:4px;display:block;}
        .result-detail{padding:22px 24px;border-top:1px solid var(--border);}
        .detail-title{font-size:12px;font-weight:700;letter-spacing:1px;text-transform:uppercase;color:var(--muted);margin-bottom:14px;}
        .detail-item{padding:14px;border-radius:12px;margin-bottom:8px;border:1px solid var(--border);}
        .detail-item.ok{background:#F0FDF4;border-color:#86EFAC;}
        .detail-item.ko{background:#FFF1F2;border-color:#FCA5A5;}
        .detail-q{font-size:13px;font-weight:500;color:var(--noir);margin-bottom:5px;display:flex;align-items:center;gap:8px;}
        .detail-r{font-size:12px;color:var(--muted);font-weight:300;}
        .message-termine{background:var(--bleu);border-radius:0 0 20px 20px;padding:16px 24px;text-align:center;font-size:13px;color:#1E3A5F;font-weight:500;}
        @media(max-width:600px){.t-wrap{padding:16px 10px 40px;}.question-texte{font-size:18px;}.question-img{height:220px;}.result-score{font-size:56px;}}
    </style>
</head>
<body>

<div class="t-header">
    <img src="<?= BASE_URL ?>assets/images/monlogo.png" alt="FamiCare">
    <div class="t-badge"><?= $cat_emoji ?> <?= htmlspecialchars($cat_label) ?></div>
</div>

<?php if ($s['etape'] === 'intro'): ?>
<div class="t-wrap"><div class="t-card">
    <div class="intro-visual"><?= $cat_emoji ?></div>
    <div class="intro-body">
        <p style="font-size:15px;color:var(--muted);margin-bottom:4px;">Bonjour,</p>
        <h1 class="intro-titre"><?= htmlspecialchars($td['prenom'] . ' ' . $td['nom']) ?> 👋</h1>
        <p class="intro-sub">Vous êtes invité(e) à passer le test<br><strong style="color:var(--noir);font-size:16px;"><?= htmlspecialchars($td['titre']) ?></strong></p>
        <div class="intro-pills">
            <span class="intro-pill">📝 <?= $nb ?> questions</span>
            <?php if ($td['duree_limite']): ?><span class="intro-pill">⏱️ <?= $td['duree_limite'] ?> min</span><?php endif; ?>
            <span class="intro-pill"><?= $cat_emoji ?> <?= htmlspecialchars($cat_label) ?></span>
        </div>
        <div class="avertissement">
            <span style="font-size:20px;flex-shrink:0;">⚠️</span>
            <div><strong>Important :</strong> Ce test ne peut être passé <strong>qu'une seule fois</strong>. Assurez-vous d'être dans un endroit calme avant de commencer.</div>
        </div>
        <form method="POST" action="">
            <input type="hidden" name="action" value="commencer">
            <button type="submit" class="btn-main">Évaluer mes compétences <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" style="width:18px;height:18px"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg></button>
        </form>
    </div>
</div></div>

<?php elseif ($s['etape'] === 'questions'):
    $qi = $s['q_index'];
    if ($qi >= $nb) { $s['etape'] = 'calcul'; header('Location: test.php?token=' . urlencode($token)); exit; }
    $q = $questions[$qi];
    $pct_bar = round(($qi / $nb) * 100);
    $lettres = ['A','B','C','D','E','F'];
    $rep = $s['reponses'][$q['id']] ?? null;
?>
<div class="progress-wrap">
    <div class="progress-info"><span>Question <strong><?= $qi + 1 ?></strong> / <?= $nb ?></span><span><?= htmlspecialchars($td['prenom']) ?> <?= htmlspecialchars($td['nom']) ?></span></div>
    <div class="progress-track"><div class="progress-fill" style="width:<?= $pct_bar ?>%"></div></div>
</div>
<div class="t-wrap"><div class="t-card">
    <?php if (!empty($q['image_path'])): ?>
    <img src="<?= BASE_URL . htmlspecialchars($q['image_path']) ?>" alt="Illustration" class="question-img" onerror="this.style.display='none'">
    <?php endif; ?>
    <div class="question-body">
        <span class="question-num">Question <?= $qi + 1 ?> sur <?= $nb ?></span>
        <p class="question-texte"><?= htmlspecialchars($q['texte']) ?></p>
        <form method="POST" action="" id="frmQ">
            <input type="hidden" name="action" value="repondre">
            <input type="hidden" name="id_question" value="<?= $q['id'] ?>">
            <input type="hidden" name="id_choix" id="choix_val" value="">
            <div class="choix-list">
            <?php foreach ($q['choix'] as $i => $c): ?>
                <button type="button" class="choix-btn <?= $rep == $c['id'] ? 'selected' : '' ?>" onclick="choisir(this,'<?= $c['id'] ?>')">
                    <div class="choix-lettre"><?= $lettres[$i] ?? ($i+1) ?></div>
                    <span><?= htmlspecialchars($c['texte']) ?></span>
                </button>
            <?php endforeach; ?>
            </div>
            <div class="q-nav">
                <?php if ($qi > 0): ?><button type="button" class="btn-prec" onclick="goBack()">← Précédent</button><?php endif; ?>
                <button type="submit" class="btn-next <?= $rep ? 'actif' : '' ?>" id="btnNext"><?= ($qi + 1 >= $nb) ? 'Terminer le test ✓' : 'Question suivante →' ?></button>
            </div>
        </form>
    </div>
</div></div>
<script>
function choisir(btn, id) {
    document.querySelectorAll('.choix-btn').forEach(b => b.classList.remove('selected'));
    btn.classList.add('selected');
    document.getElementById('choix_val').value = id;
    document.getElementById('btnNext').classList.add('actif');
}
function goBack() {
    document.querySelector('input[name="action"]').value = 'precedent';
    document.getElementById('frmQ').submit();
}
<?php if ($rep): ?>document.getElementById('choix_val').value = '<?= $rep ?>';<?php endif; ?>
</script>

<?php elseif ($s['etape'] === 'resultat'):
    $st = $ms[$s['mention']];
    $min = floor($s['duree_sec']/60); $sec = $s['duree_sec'] % 60;
?>
<div class="t-wrap"><div class="t-card">
    <div class="result-header" style="background:<?= $st['bg'] ?>;">
        <div class="result-emoji"><?= $st['emoji'] ?></div>
        <div class="result-score" style="color:<?= $st['color'] ?>;"><?= $s['pourcentage'] ?>%</div>
        <div class="result-mention" style="background:<?= $st['color'] ?>22;color:<?= $st['color'] ?>;"><?= ucfirst($s['mention']) ?></div>
        <p class="result-msg"><?= $st['msg'] ?></p>
    </div>
    <div class="result-stats">
        <div class="rs-item"><strong class="rs-num"><?= $s['score'] ?>/<?= $s['score_max'] ?></strong><span class="rs-label">Points</span></div>
        <div class="rs-item"><strong class="rs-num"><?= $nb ?></strong><span class="rs-label">Questions</span></div>
        <div class="rs-item"><strong class="rs-num"><?= $min ?>min<?= $sec ?>s</strong><span class="rs-label">Durée</span></div>
    </div>
    <div class="result-detail">
        <div class="detail-title">Détail de vos réponses</div>
        <?php foreach ($s['details'] as $d): ?>
        <div class="detail-item <?= $d['est_correcte'] ? 'ok' : 'ko' ?>">
            <div class="detail-q"><?= $d['est_correcte'] ? '✅' : '❌' ?> <?= htmlspecialchars($d['question']) ?></div>
            <?php if (!$d['est_correcte']): foreach ($d['choix'] as $c): if ($c['est_correcte'] == 1): ?>
            <div class="detail-r">✓ Bonne réponse : <strong><?= htmlspecialchars($c['texte']) ?></strong></div>
            <?php endif; endforeach; endif; ?>
        </div>
        <?php endforeach; ?>
    </div>
    <div class="message-termine">📧 Résultat transmis à FamiCare · <?= htmlspecialchars($td['prenom'] . ' ' . $td['nom']) ?></div>
</div></div>
<?php endif; ?>
</body>
</html>