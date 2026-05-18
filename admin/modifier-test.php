<?php
session_start();
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../includes/auth.php';
verif_connexion();
verif_role('admin');

$page_titre = 'Modifier un test';
$message = ''; $erreur = '';

$id_test = (int)($_GET['id'] ?? $_POST['id_test'] ?? 0);
if (!$id_test) { header('Location: ' . BASE_URL . 'admin/tests.php'); exit; }

$stmt = $pdo->prepare('SELECT * FROM tests WHERE id = :id');
$stmt->execute([':id' => $id_test]);
$test = $stmt->fetch();
if (!$test) { header('Location: ' . BASE_URL . 'admin/tests.php'); exit; }

function charger_questions(PDO $pdo, int $id_test): array {
    $stmt = $pdo->prepare('SELECT q.id, q.texte, q.type, q.image_path, q.points, q.ordre, GROUP_CONCAT(cr.id ORDER BY cr.id SEPARATOR "||") AS choix_ids, GROUP_CONCAT(cr.texte ORDER BY cr.id SEPARATOR "||") AS choix_textes, GROUP_CONCAT(cr.est_correcte ORDER BY cr.id SEPARATOR "||") AS choix_corrects FROM questions q LEFT JOIN choix_reponses cr ON cr.id_question = q.id WHERE q.id_test = :id GROUP BY q.id ORDER BY q.ordre ASC');
    $stmt->execute([':id' => $id_test]);
    $raws = $stmt->fetchAll();
    $result = [];
    foreach ($raws as $q) {
        $ids = explode('||', $q['choix_ids'] ?? ''); $txts = explode('||', $q['choix_textes'] ?? ''); $cors = explode('||', $q['choix_corrects'] ?? '');
        $choix = []; $bonne = 0;
        for ($i = 0; $i < count($ids); $i++) {
            if (!empty($ids[$i])) { $choix[] = ['id' => $ids[$i], 'texte' => $txts[$i], 'est_correcte' => $cors[$i]]; if ($cors[$i] == 1) $bonne = $i; }
        }
        $result[] = ['id' => $q['id'], 'texte' => $q['texte'], 'type' => $q['type'], 'image_path' => $q['image_path'], 'points' => $q['points'], 'choix' => $choix, 'bonne_index' => $bonne];
    }
    return $result;
}

$questions_data = charger_questions($pdo, $id_test);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'modifier_test') {
    $titre = trim($_POST['titre'] ?? ''); $description = trim($_POST['description'] ?? '');
    $categorie = $_POST['categorie'] ?? ''; $duree = !empty($_POST['duree_limite']) ? (int)$_POST['duree_limite'] : null;
    $actif = isset($_POST['actif']) ? 1 : 0;
    if (empty($titre)) { $erreur = 'Le titre est obligatoire.'; }
    elseif (!in_array($categorie, ['menage','garde_enfant','repassage','accompagnement'])) { $erreur = 'Catégorie invalide.'; }
    else {
        try {
            $pdo->beginTransaction();
            $pdo->prepare('UPDATE tests SET titre=:t, description=:d, categorie=:c, duree_limite=:dur, actif=:a WHERE id=:id')
                ->execute([':t'=>$titre,':d'=>$description,':c'=>$categorie,':dur'=>$duree,':a'=>$actif,':id'=>$id_test]);
            foreach ($_POST['supprimer_questions'] ?? [] as $id_q) {
                $r = $pdo->prepare('SELECT image_path FROM questions WHERE id=:id'); $r->execute([':id'=>(int)$id_q]);
                $img = $r->fetchColumn();
                if ($img && file_exists(__DIR__.'/../'.$img)) unlink(__DIR__.'/../'.$img);
                $pdo->prepare('DELETE FROM questions WHERE id=:id AND id_test=:it')->execute([':id'=>(int)$id_q,':it'=>$id_test]);
            }
            foreach ($_POST['questions_existantes'] ?? [] as $id_q => $q) {
                $texte_q = trim($q['texte'] ?? ''); if (empty($texte_q)) continue;
                $image_path = $q['image_actuelle'] ?? null;
                $fk = 'q_image_modif_'.$id_q;
                if (isset($_FILES[$fk]) && $_FILES[$fk]['error'] === UPLOAD_ERR_OK) {
                    $f = $_FILES[$fk]; $ext = strtolower(pathinfo($f['name'], PATHINFO_EXTENSION));
                    if (in_array($ext,['jpg','jpeg','png','webp']) && $f['size'] <= 2*1024*1024) {
                        $dir = __DIR__.'/../uploads/questions/'; if (!is_dir($dir)) mkdir($dir,0755,true);
                        if ($image_path && file_exists(__DIR__.'/../'.$image_path)) unlink(__DIR__.'/../'.$image_path);
                        $fn = uniqid('q_',true).'.'.$ext;
                        if (move_uploaded_file($f['tmp_name'],$dir.$fn)) $image_path = 'uploads/questions/'.$fn;
                    }
                }
                if (isset($q['supprimer_image']) && $image_path) { if (file_exists(__DIR__.'/../'.$image_path)) unlink(__DIR__.'/../'.$image_path); $image_path = null; }
                $pdo->prepare('UPDATE questions SET texte=:t,type=:ty,image_path=:img,points=:p WHERE id=:id AND id_test=:it')
                    ->execute([':t'=>$texte_q,':ty'=>$q['type']??'qcm',':img'=>$image_path,':p'=>(int)($q['points']??1),':id'=>(int)$id_q,':it'=>$id_test]);
                $bonne = (int)($q['bonne'] ?? 0);
                $pdo->prepare('DELETE FROM choix_reponses WHERE id_question=:id')->execute([':id'=>(int)$id_q]);
                foreach ($q['choix'] ?? [] as $i => $tc) {
                    $tc = trim($tc); if (empty($tc)) continue;
                    $pdo->prepare('INSERT INTO choix_reponses (id_question,texte,est_correcte) VALUES (:q,:t,:c)')->execute([':q'=>(int)$id_q,':t'=>$tc,':c'=>($i===$bonne)?1:0]);
                }
            }
            $nb_ex = count($questions_data) - count($_POST['supprimer_questions'] ?? []);
            foreach ($_POST['nouvelles_questions'] ?? [] as $ord => $q) {
                $texte_q = trim($q['texte'] ?? ''); if (empty($texte_q)) continue;
                $img = null; $fk = 'nq_image_'.$ord;
                if (isset($_FILES[$fk]) && $_FILES[$fk]['error'] === UPLOAD_ERR_OK) {
                    $f = $_FILES[$fk]; $ext = strtolower(pathinfo($f['name'], PATHINFO_EXTENSION));
                    if (in_array($ext,['jpg','jpeg','png','webp']) && $f['size'] <= 2*1024*1024) {
                        $dir = __DIR__.'/../uploads/questions/'; if (!is_dir($dir)) mkdir($dir,0755,true);
                        $fn = uniqid('q_',true).'.'.$ext;
                        if (move_uploaded_file($f['tmp_name'],$dir.$fn)) $img = 'uploads/questions/'.$fn;
                    }
                }
                $pdo->prepare('INSERT INTO questions (id_test,texte,type,image_path,points,ordre) VALUES (:it,:t,:ty,:img,:p,:o)')
                    ->execute([':it'=>$id_test,':t'=>$texte_q,':ty'=>$q['type']??'qcm',':img'=>$img,':p'=>(int)($q['points']??1),':o'=>$nb_ex+$ord+1]);
                $nqid = $pdo->lastInsertId(); $bonne = (int)($q['bonne'] ?? 0);
                foreach ($q['choix'] ?? [] as $i => $tc) {
                    $tc = trim($tc); if (empty($tc)) continue;
                    $pdo->prepare('INSERT INTO choix_reponses (id_question,texte,est_correcte) VALUES (:q,:t,:c)')->execute([':q'=>$nqid,':t'=>$tc,':c'=>($i===$bonne)?1:0]);
                }
            }
            $pdo->commit();
            $message = '✅ Test modifié avec succès !';
            $test['titre']=$titre; $test['description']=$description; $test['categorie']=$categorie; $test['duree_limite']=$duree; $test['actif']=$actif;
            $questions_data = charger_questions($pdo, $id_test);
        } catch (PDOException $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            $erreur = 'Erreur lors de la modification. Réessayez.';
            error_log($e->getMessage());
        }
    }
}

$cat_info = ['menage'=>['label'=>'Ménage & Repassage','emoji'=>'🧹'],'garde_enfant'=>['label'=>"Garde d'enfants",'emoji'=>'👶'],'repassage'=>['label'=>'Repassage','emoji'=>'👔'],'accompagnement'=>['label'=>'Accompagnement','emoji'=>'🤝']];
require_once __DIR__ . '/../includes/header.php';
?>
<style>
.q-block-m{background:#fff;border:2px solid #D1DCE9;border-radius:16px;padding:22px;margin-bottom:16px;transition:border-color .2s;}
.q-block-m:hover{border-color:#B8CAE0;}
.q-block-m.a-suppr{border-color:#FCA5A5;background:#FFF1F2;opacity:.6;}
.choix-row-m{display:grid;grid-template-columns:28px 1fr 100px;align-items:center;gap:10px;margin-bottom:8px;}
.clettre{width:28px;height:28px;background:#D1DCE9;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:12px;font-weight:700;color:#1E3A5F;flex-shrink:0;transition:all .2s;}
.clettre.ok{background:#2A2727;color:#fff;}
.img-prev-m{max-width:140px;max-height:90px;border-radius:8px;object-fit:cover;border:1px solid #E8E0E0;margin-top:6px;}
.btn-suppr-m{background:#FEE2E2;color:#991B1B;border:none;border-radius:50px;padding:5px 14px;font-size:12px;font-weight:500;cursor:pointer;}
.btn-suppr-m:hover{background:#FCA5A5;}
.btn-rest{background:#D1FAE5;color:#065F46;border:none;border-radius:50px;padding:5px 14px;font-size:12px;font-weight:500;cursor:pointer;display:none;}
</style>

<div class="fc-container">
<div class="fc-page-header">
    <div>
        <h1 class="fc-page-title">✏️ Modifier le test</h1>
        <p class="fc-page-subtitle"><?= htmlspecialchars($test['titre']) ?></p>
    </div>
    <a href="<?= BASE_URL ?>admin/tests.php" class="fc-btn fc-btn-outline">← Retour</a>
</div>

<?php if ($message): ?><div class="fc-alert fc-alert-success" style="margin-bottom:20px;"><?= $message ?></div><?php endif; ?>
<?php if ($erreur): ?><div class="fc-alert fc-alert-error" style="margin-bottom:20px;">❌ <?= $erreur ?></div><?php endif; ?>

<form method="POST" action="" enctype="multipart/form-data">
<input type="hidden" name="action" value="modifier_test">
<input type="hidden" name="id_test" value="<?= $id_test ?>">

<div style="display:grid;grid-template-columns:360px 1fr;gap:24px;align-items:start;">

<!-- INFOS GÉNÉRALES -->
<div style="background:#fff;border-radius:20px;border:1px solid #E8E0E0;padding:28px;position:sticky;top:80px;">
    <h2 style="font-family:'Playfair Display',serif;font-size:18px;font-weight:700;color:#2A2727;margin-bottom:20px;">📋 Informations</h2>
    <div class="fc-form-group">
        <label class="fc-label">Titre *</label>
        <input type="text" name="titre" class="fc-input" value="<?= htmlspecialchars($test['titre']) ?>" required>
    </div>
    <div class="fc-form-group">
        <label class="fc-label">Description</label>
        <textarea name="description" class="fc-textarea"><?= htmlspecialchars($test['description'] ?? '') ?></textarea>
    </div>
    <div class="fc-form-group">
        <label class="fc-label">Catégorie *</label>
        <select name="categorie" class="fc-select" required>
        <?php foreach ($cat_info as $val => $info): ?>
            <option value="<?= $val ?>" <?= $test['categorie']===$val?'selected':'' ?>><?= $info['emoji'].' '.$info['label'] ?></option>
        <?php endforeach; ?>
        </select>
    </div>
    <div class="fc-form-group">
        <label class="fc-label">Durée (min)</label>
        <input type="number" name="duree_limite" class="fc-input" value="<?= $test['duree_limite']??'' ?>" placeholder="Vide = illimitée" min="1" max="120">
    </div>
    <div class="fc-form-group">
        <label style="display:flex;align-items:center;gap:10px;cursor:pointer;font-size:14px;">
            <input type="checkbox" name="actif" <?= $test['actif']?'checked':'' ?> style="width:18px;height:18px;accent-color:#2A2727;">
            Test actif (visible)
        </label>
    </div>
    <button type="submit" class="fc-btn fc-btn-accent fc-btn-full" style="height:48px;font-size:15px;">💾 Enregistrer</button>
</div>

<!-- QUESTIONS -->
<div>
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px;">
        <h2 style="font-family:'Playfair Display',serif;font-size:18px;font-weight:700;color:#2A2727;margin:0;">
            ❓ Questions
            <span id="nbq" style="background:#D1DCE9;color:#1E3A5F;font-size:12px;padding:2px 10px;border-radius:50px;font-family:'DM Sans',sans-serif;font-weight:600;margin-left:8px;"><?= count($questions_data) ?></span>
        </h2>
        <button type="button" onclick="ajouterQ()" class="fc-btn fc-btn-primary">+ Ajouter</button>
    </div>

    <!-- Questions existantes -->
    <?php foreach ($questions_data as $idx => $q): $L=['A','B','C','D']; ?>
    <div class="q-block-m" id="qb-<?= $q['id'] ?>">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px;">
            <div style="display:flex;align-items:center;gap:10px;">
                <div style="width:32px;height:32px;background:#2A2727;color:#fff;border-radius:50%;display:flex;align-items:center;justify-content:center;font-family:'Playfair Display',serif;font-size:14px;font-weight:700;"><?= $idx+1 ?></div>
                <span style="font-size:14px;font-weight:600;color:#2A2727;">Question existante</span>
            </div>
            <div style="display:flex;gap:8px;">
                <button type="button" class="btn-suppr-m" id="bs-<?= $q['id'] ?>" onclick="suppQ(<?= $q['id'] ?>)">🗑️ Supprimer</button>
                <button type="button" class="btn-rest"    id="br-<?= $q['id'] ?>" onclick="restQ(<?= $q['id'] ?>)">↩️ Restaurer</button>
            </div>
        </div>
        <input type="checkbox" name="supprimer_questions[]" value="<?= $q['id'] ?>" id="sc-<?= $q['id'] ?>" style="display:none;">

        <div class="fc-form-group" style="margin-bottom:14px;">
            <label class="fc-label">Question *</label>
            <textarea name="questions_existantes[<?= $q['id'] ?>][texte]" class="fc-textarea" style="min-height:60px;" required><?= htmlspecialchars($q['texte']) ?></textarea>
        </div>

        <div style="display:grid;grid-template-columns:1fr 1fr 80px;gap:12px;margin-bottom:14px;">
            <div>
                <label class="fc-label">Type</label>
                <select name="questions_existantes[<?= $q['id'] ?>][type]" class="fc-select">
                    <option value="qcm" <?= $q['type']==='qcm'?'selected':'' ?>>QCM</option>
                    <option value="mise_en_situation" <?= $q['type']==='mise_en_situation'?'selected':'' ?>>Mise en situation</option>
                </select>
            </div>
            <div>
                <label class="fc-label">Image</label>
                <?php if (!empty($q['image_path'])): ?>
                <img src="<?= BASE_URL.htmlspecialchars($q['image_path']) ?>" class="img-prev-m" alt="Image actuelle">
                <div style="margin-top:6px;display:flex;align-items:center;gap:6px;">
                    <input type="checkbox" name="questions_existantes[<?= $q['id'] ?>][supprimer_image]" style="width:13px;height:13px;">
                    <span style="font-size:12px;color:#991B1B;">Supprimer l'image</span>
                </div>
                <?php endif; ?>
                <input type="hidden" name="questions_existantes[<?= $q['id'] ?>][image_actuelle]" value="<?= htmlspecialchars($q['image_path']??'') ?>">
                <input type="file" name="q_image_modif_<?= $q['id'] ?>" accept="image/*" style="font-size:12px;color:#5A5555;margin-top:4px;" onchange="prevImg(this,'pm-<?= $q['id'] ?>')">
                <img id="pm-<?= $q['id'] ?>" class="img-prev-m" style="display:none;" alt="Aperçu">
            </div>
            <div>
                <label class="fc-label">Points</label>
                <input type="number" name="questions_existantes[<?= $q['id'] ?>][points]" class="fc-input" value="<?= $q['points'] ?>" min="1" max="10">
            </div>
        </div>

        <label class="fc-label" style="margin-bottom:8px;">Choix de réponses</label>
        <?php foreach ($q['choix'] as $i => $c): ?>
        <div class="choix-row-m">
            <div class="clettre <?= $c['est_correcte']==1?'ok':'' ?>" id="cl-<?= $q['id'] ?>-<?= $i ?>"><?= $L[$i]??($i+1) ?></div>
            <input type="text" name="questions_existantes[<?= $q['id'] ?>][choix][<?= $i ?>]" class="fc-input" style="margin:0;" value="<?= htmlspecialchars($c['texte']) ?>">
            <label style="display:flex;align-items:center;gap:5px;cursor:pointer;font-size:12px;white-space:nowrap;">
                <input type="radio" name="questions_existantes[<?= $q['id'] ?>][bonne]" value="<?= $i ?>" <?= $c['est_correcte']==1?'checked':'' ?> style="width:15px;height:15px;accent-color:#2A2727;" onchange="majCL(<?= $q['id'] ?>,<?= $i ?>)"> ✓ Bonne
            </label>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endforeach; ?>

    <!-- Nouvelles questions -->
    <div id="nq-container"></div>
</div>
</div>

<div style="text-align:center;margin-top:24px;padding-top:24px;border-top:1px solid #E8E0E0;">
    <button type="submit" class="fc-btn fc-btn-accent" style="height:52px;font-size:15px;padding:0 48px;">💾 Enregistrer toutes les modifications</button>
</div>
</form>
</div>

<script>
let nqi = 0;
const LL = ['A','B','C','D'];

function suppQ(id) {
    document.getElementById('sc-'+id).checked = true;
    document.getElementById('qb-'+id).classList.add('a-suppr');
    document.getElementById('bs-'+id).style.display = 'none';
    document.getElementById('br-'+id).style.display = 'inline-flex';
    majBadge();
}
function restQ(id) {
    document.getElementById('sc-'+id).checked = false;
    document.getElementById('qb-'+id).classList.remove('a-suppr');
    document.getElementById('bs-'+id).style.display = 'inline-flex';
    document.getElementById('br-'+id).style.display = 'none';
    majBadge();
}
function majCL(qid, bi) {
    for(let i=0;i<4;i++){const e=document.getElementById('cl-'+qid+'-'+i);if(e)e.className='clettre'+(i===bi?' ok':'');}
}
function majBadge() {
    const n = document.querySelectorAll('.q-block-m:not(.a-suppr)').length;
    document.getElementById('nbq').textContent = n + ' question' + (n>1?'s':'');
}
function prevImg(input, pid) {
    const p = document.getElementById(pid);
    if(input.files && input.files[0]){const r=new FileReader();r.onload=e=>{p.src=e.target.result;p.style.display='block';};r.readAsDataURL(input.files[0]);}
}
function majNQL(idx, bi) {
    for(let i=0;i<4;i++){const e=document.getElementById('nql-'+idx+'-'+i);if(e)e.className='clettre'+(i===bi?' ok':'');}
}
function ajouterQ() {
    const idx = nqi;
    const div = document.createElement('div');
    div.className = 'q-block-m';
    div.id = 'nqb-'+idx;
    div.style.cssText = 'border-color:#F7E597;background:#FFFDF0;';
    div.innerHTML = `
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px;">
            <div style="display:flex;align-items:center;gap:10px;">
                <div style="width:32px;height:32px;background:#F7E597;color:#78610A;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:11px;font-weight:700;">NEW</div>
                <span style="font-size:14px;font-weight:600;color:#78610A;">Nouvelle question</span>
            </div>
            <button type="button" class="btn-suppr-m" onclick="document.getElementById('nqb-${idx}').remove();majBadge();">✕ Annuler</button>
        </div>
        <div class="fc-form-group" style="margin-bottom:14px;">
            <label class="fc-label">Question *</label>
            <textarea name="nouvelles_questions[${idx}][texte]" class="fc-textarea" style="min-height:60px;" placeholder="Votre question..."></textarea>
        </div>
        <div style="display:grid;grid-template-columns:1fr 1fr 80px;gap:12px;margin-bottom:14px;">
            <div><label class="fc-label">Type</label><select name="nouvelles_questions[${idx}][type]" class="fc-select"><option value="qcm">QCM</option><option value="mise_en_situation">Mise en situation</option></select></div>
            <div><label class="fc-label">Image</label><input type="file" name="nq_image_${idx}" accept="image/*" style="font-size:12px;color:#5A5555;" onchange="prevImg(this,'nqp-${idx}')"><img id="nqp-${idx}" class="img-prev-m" style="display:none;" alt="Aperçu"></div>
            <div><label class="fc-label">Points</label><input type="number" name="nouvelles_questions[${idx}][points]" class="fc-input" value="1" min="1" max="10"></div>
        </div>
        <label class="fc-label" style="margin-bottom:8px;">Choix de réponses</label>
        ${[0,1,2,3].map(i=>`
        <div class="choix-row-m">
            <div class="clettre" id="nql-${idx}-${i}">${LL[i]}</div>
            <input type="text" name="nouvelles_questions[${idx}][choix][${i}]" class="fc-input" style="margin:0;" placeholder="Réponse ${LL[i]}...">
            <label style="display:flex;align-items:center;gap:5px;cursor:pointer;font-size:12px;white-space:nowrap;">
                <input type="radio" name="nouvelles_questions[${idx}][bonne]" value="${i}" style="width:15px;height:15px;accent-color:#2A2727;" onchange="majNQL(${idx},${i})" required> ✓ Bonne
            </label>
        </div>`).join('')}
    `;
    document.getElementById('nq-container').appendChild(div);
    nqi++; majBadge();
}
</script>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>