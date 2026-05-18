<?php
/**
 * admin/tests.php
 * =====================================================
 * Gestion des tests — Interface admin
 * - Créer un test avec questions et images
 * - Lister les tests existants
 * - Modifier / Supprimer un test
 * =====================================================
 */
session_start();
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../includes/auth.php';

verif_connexion();
verif_role('admin');

$page_titre = 'Gérer les tests';
$admin      = utilisateur_connecte();
$message    = '';
$erreur     = '';

// ======================================================
// TRAITEMENT : CRÉER UN NOUVEAU TEST
// ======================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'creer_test') {

    $titre      = trim($_POST['titre']      ?? '');
    $description= trim($_POST['description']?? '');
    $categorie  = $_POST['categorie']       ?? '';
    $duree      = !empty($_POST['duree_limite']) ? (int)$_POST['duree_limite'] : null;
    $actif      = isset($_POST['actif']) ? 1 : 0;

    // Validation
    if (empty($titre)) {
        $erreur = 'Le titre du test est obligatoire.';
    } elseif (!in_array($categorie, ['menage','garde_enfant','repassage','accompagnement'])) {
        $erreur = 'Veuillez choisir une catégorie valide.';
    } else {

        try {
            $pdo->beginTransaction();

            // 1. Insérer le test
            $stmt = $pdo->prepare(
                'INSERT INTO tests (titre, description, categorie, duree_limite, actif, id_createur, cree_le)
                 VALUES (:titre, :desc, :cat, :duree, :actif, :createur, NOW())'
            );
            $stmt->execute([
                ':titre'    => $titre,
                ':desc'     => $description,
                ':cat'      => $categorie,
                ':duree'    => $duree,
                ':actif'    => $actif,
                ':createur' => $admin['id'],
            ]);

            $id_test = $pdo->lastInsertId();

            // 2. Insérer les questions
            $questions = $_POST['questions'] ?? [];

            foreach ($questions as $ordre => $q) {
                $texte_q = trim($q['texte'] ?? '');
                $type_q  = $q['type'] ?? 'qcm';
                $points  = (int)($q['points'] ?? 1);

                if (empty($texte_q)) continue;

                // Gérer l'upload d'image
                $image_path = null;
                $file_key   = 'q_image_' . $ordre;

                if (isset($_FILES[$file_key]) && $_FILES[$file_key]['error'] === UPLOAD_ERR_OK) {
                    $file    = $_FILES[$file_key];
                    $ext     = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
                    $allowed = ['jpg', 'jpeg', 'png', 'webp', 'gif'];

                    if (in_array($ext, $allowed) && $file['size'] <= 2 * 1024 * 1024) {
                        $dossier = __DIR__ . '/../uploads/questions/';
                        if (!is_dir($dossier)) mkdir($dossier, 0755, true);

                        $nom_fichier = uniqid('q_', true) . '.' . $ext;
                        if (move_uploaded_file($file['tmp_name'], $dossier . $nom_fichier)) {
                            $image_path = 'uploads/questions/' . $nom_fichier;
                        }
                    }
                }

                // Insérer la question
                $stmt_q = $pdo->prepare(
                    'INSERT INTO questions (id_test, texte, type, image_path, points, ordre)
                     VALUES (:id_test, :texte, :type, :image, :points, :ordre)'
                );
                $stmt_q->execute([
                    ':id_test' => $id_test,
                    ':texte'   => $texte_q,
                    ':type'    => $type_q,
                    ':image'   => $image_path,
                    ':points'  => $points,
                    ':ordre'   => $ordre + 1,
                ]);

                $id_question = $pdo->lastInsertId();

                // Insérer les choix de réponses
                $choix_list    = $q['choix']          ?? [];
                $bonne_reponse = (int)($q['bonne']    ?? 0);

                foreach ($choix_list as $i => $texte_choix) {
                    $texte_choix = trim($texte_choix);
                    if (empty($texte_choix)) continue;

                    $stmt_c = $pdo->prepare(
                        'INSERT INTO choix_reponses (id_question, texte, est_correcte)
                         VALUES (:id_q, :texte, :correct)'
                    );
                    $stmt_c->execute([
                        ':id_q'    => $id_question,
                        ':texte'   => $texte_choix,
                        ':correct' => ($i === $bonne_reponse) ? 1 : 0,
                    ]);
                }
            }

            $pdo->commit();
            $message = '✅ Test "' . htmlspecialchars($titre) . '" créé avec succès ! (ID : ' . $id_test . ')';

        } catch (PDOException $e) {
            $pdo->rollBack();
            error_log('Erreur création test : ' . $e->getMessage());
            $erreur = 'Erreur lors de la création. Réessayez.';
        }
    }
}

// ======================================================
// TRAITEMENT : SUPPRIMER UN TEST
// ======================================================
if (isset($_GET['supprimer']) && is_numeric($_GET['supprimer'])) {
    try {
        $stmt = $pdo->prepare('DELETE FROM tests WHERE id = :id');
        $stmt->execute([':id' => (int)$_GET['supprimer']]);
        $message = '🗑️ Test supprimé avec succès.';
    } catch (PDOException $e) {
        $erreur = 'Impossible de supprimer ce test.';
    }
}

// ======================================================
// TRAITEMENT : TOGGLE ACTIF/INACTIF
// ======================================================
if (isset($_GET['toggle']) && is_numeric($_GET['toggle'])) {
    $stmt = $pdo->prepare('UPDATE tests SET actif = NOT actif WHERE id = :id');
    $stmt->execute([':id' => (int)$_GET['toggle']]);
    header('Location: tests.php');
    exit;
}

// ======================================================
// RÉCUPÉRER LA LISTE DES TESTS
// ======================================================
$stmt = $pdo->query(
    'SELECT t.*, COUNT(q.id) AS nb_questions
     FROM tests t
     LEFT JOIN questions q ON q.id_test = t.id
     GROUP BY t.id
     ORDER BY t.cree_le DESC'
);
$tests = $stmt->fetchAll();

// Couleurs catégories
$cat_info = [
    'menage'         => ['label' => 'Ménage',          'bg' => '#D1DCE9', 'color' => '#1E3A5F', 'emoji' => '🧹'],
    'garde_enfant'   => ['label' => 'Garde d\'enfants','bg' => '#D1FAE5', 'color' => '#065F46', 'emoji' => '👶'],
    'repassage'      => ['label' => 'Repassage',       'bg' => '#F7E597', 'color' => '#78610A', 'emoji' => '👔'],
    'accompagnement' => ['label' => 'Accompagnement',  'bg' => '#F5E4EB', 'color' => '#7A2A4A', 'emoji' => '🤝'],
];

require_once __DIR__ . '/../includes/header.php';
?>

<style>
  /* Styles spécifiques à la page tests */
  .q-block {
    background: #fff;
    border: 2px solid #D1DCE9;
    border-radius: 16px;
    padding: 22px;
    margin-bottom: 16px;
    position: relative;
    transition: border-color .2s;
  }
  .q-block:hover { border-color: #B8CAE0; }

  .q-block-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 16px;
  }

  .q-num-badge {
    background: #2A2727;
    color: #fff;
    font-family: 'Playfair Display', serif;
    font-size: 14px;
    font-weight: 700;
    width: 32px; height: 32px;
    border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
  }

  .btn-suppr-q {
    background: #FEE2E2;
    color: #991B1B;
    border: none;
    border-radius: 50px;
    padding: 5px 14px;
    font-size: 12px;
    font-weight: 500;
    cursor: pointer;
    transition: background .2s;
  }
  .btn-suppr-q:hover { background: #FCA5A5; }

  .choix-row {
    display: grid;
    grid-template-columns: 28px 1fr 80px;
    align-items: center;
    gap: 10px;
    margin-bottom: 8px;
  }

  .choix-lettre {
    width: 28px; height: 28px;
    background: #D1DCE9;
    border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    font-size: 12px; font-weight: 700; color: #1E3A5F;
    flex-shrink: 0;
  }

  .choix-lettre.correct-selected {
    background: #2A2727;
    color: #fff;
  }

  .img-preview {
    max-width: 120px;
    max-height: 80px;
    border-radius: 8px;
    object-fit: cover;
    display: none;
    margin-top: 8px;
    border: 1px solid #E8E0E0;
  }

  .fc-input-file {
    font-size: 13px;
    color: #5A5555;
    font-family: 'DM Sans', sans-serif;
  }

  /* Table des tests */
  .test-row {
    display: grid;
    grid-template-columns: 1fr 140px 100px 80px 80px 160px;
    align-items: center;
    padding: 16px 20px;
    border-bottom: 1px solid #E8E0E0;
    gap: 12px;
    transition: background .15s;
  }
  .test-row:hover { background: #F5F7FF; }
  .test-row:last-child { border-bottom: none; }

  .test-row-header {
    background: #D1DCE9;
    border-radius: 12px 12px 0 0;
    font-size: 11px;
    font-weight: 700;
    letter-spacing: 1px;
    text-transform: uppercase;
    color: #1E3A5F;
    padding: 12px 20px;
  }

  @media (max-width: 768px) {
    .test-row { grid-template-columns: 1fr; }
    .test-row-header { display: none; }
    .choix-row { grid-template-columns: 28px 1fr 60px; }
  }
</style>

<div class="fc-container">

    <!-- EN-TÊTE -->
    <div class="fc-page-header">
        <div>
            <h1 class="fc-page-title">📝 Gérer les tests</h1>
            <p class="fc-page-subtitle">Créez des tests d'évaluation pour vos intervenantes</p>
        </div>
        <div style="display:flex;gap:10px;">
            <a href="<?= BASE_URL ?>admin/generer-lien.php" class="fc-btn fc-btn-primary fc-btn-lg">
                🔗 Générer un lien
            </a>
            <button onclick="toggleFormCreation()" class="fc-btn fc-btn-accent fc-btn-lg">
                + Créer un test
            </button>
        </div>
    </div>

    <!-- MESSAGES -->
    <?php if ($message): ?>
    <div class="fc-alert fc-alert-success" style="margin-bottom:20px;">
        <?= $message ?>
    </div>
    <?php endif; ?>

    <?php if ($erreur): ?>
    <div class="fc-alert fc-alert-error" style="margin-bottom:20px;">
        ❌ <?= $erreur ?>
    </div>
    <?php endif; ?>

    <!-- ===================================================
         FORMULAIRE DE CRÉATION
    =================================================== -->
    <div id="form-creation" style="display:none; margin-bottom:32px;">
        <div style="background:#fff;border-radius:20px;border:2px solid #D1DCE9;overflow:hidden;">

            <!-- Header formulaire -->
            <div style="background:#2A2727;padding:24px 28px;display:flex;align-items:center;justify-content:space-between;">
                <h2 style="font-family:'Playfair Display',serif;font-size:20px;font-weight:700;color:#fff;margin:0;">
                    Créer un nouveau test
                </h2>
                <button onclick="toggleFormCreation()"
                        style="background:rgba(255,255,255,.15);border:none;color:#fff;width:32px;height:32px;border-radius:50%;cursor:pointer;font-size:18px;display:flex;align-items:center;justify-content:center;">
                    ×
                </button>
            </div>

            <form method="POST"
                  action=""
                  enctype="multipart/form-data"
                  id="formTest">
                <input type="hidden" name="action" value="creer_test">

                <div style="padding:28px;">

                    <!-- INFOS GÉNÉRALES DU TEST -->
                    <div style="background:#F5F7FF;border-radius:14px;padding:22px;margin-bottom:24px;">
                        <h3 style="font-family:'Playfair Display',serif;font-size:16px;font-weight:700;color:#2A2727;margin-bottom:18px;">
                            📋 Informations du test
                        </h3>

                        <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:16px;">

                            <!-- Titre -->
                            <div class="fc-form-group" style="margin:0;">
                                <label class="fc-label">Titre du test *</label>
                                <input type="text"
                                       name="titre"
                                       class="fc-input"
                                       placeholder="Ex : Évaluation ménage débutant"
                                       required>
                            </div>

                            <!-- Catégorie -->
                            <div class="fc-form-group" style="margin:0;">
                                <label class="fc-label">Catégorie *</label>
                                <select name="categorie" class="fc-select" required>
                                    <option value="">-- Choisir une catégorie --</option>
                                    <option value="menage">🧹 Ménage & Repassage</option>
                                    <option value="garde_enfant">👶 Garde d'enfants</option>
                                    <option value="repassage">👔 Repassage</option>
                                    <option value="accompagnement">🤝 Accompagnement / Maintien à domicile</option>
                                </select>
                            </div>

                        </div>

                        <div style="display:grid;grid-template-columns:2fr 1fr 1fr;gap:16px;">

                            <!-- Description -->
                            <div class="fc-form-group" style="margin:0;">
                                <label class="fc-label">Description (optionnel)</label>
                                <textarea name="description"
                                          class="fc-textarea"
                                          style="min-height:72px;"
                                          placeholder="Ex : Test de base pour évaluer les connaissances en ménage..."></textarea>
                            </div>

                            <!-- Durée limite -->
                            <div class="fc-form-group" style="margin:0;">
                                <label class="fc-label">Durée limite (min)</label>
                                <input type="number"
                                       name="duree_limite"
                                       class="fc-input"
                                       placeholder="Ex : 20"
                                       min="1" max="120">
                                <span style="font-size:11px;color:#9A9494;margin-top:4px;display:block;">Laisser vide = pas de limite</span>
                            </div>

                            <!-- Statut -->
                            <div class="fc-form-group" style="margin:0;">
                                <label class="fc-label">Statut</label>
                                <div style="display:flex;align-items:center;gap:10px;height:44px;">
                                    <label style="display:flex;align-items:center;gap:8px;cursor:pointer;font-size:14px;color:#2A2727;">
                                        <input type="checkbox"
                                               name="actif"
                                               checked
                                               style="width:18px;height:18px;accent-color:#2A2727;cursor:pointer;">
                                        Test actif (visible)
                                    </label>
                                </div>
                                <span style="font-size:11px;color:#9A9494;margin-top:4px;display:block;">Coché = intervenantes peuvent le passer</span>
                            </div>

                        </div>
                    </div>

                    <!-- QUESTIONS -->
                    <div style="margin-bottom:20px;">
                        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px;">
                            <h3 style="font-family:'Playfair Display',serif;font-size:16px;font-weight:700;color:#2A2727;margin:0;">
                                ❓ Questions
                                <span id="nb-questions-badge"
                                      style="background:#D1DCE9;color:#1E3A5F;font-size:12px;padding:2px 10px;border-radius:50px;font-family:'DM Sans',sans-serif;font-weight:600;margin-left:8px;">
                                    0 question
                                </span>
                            </h3>
                            <button type="button"
                                    onclick="ajouterQuestion()"
                                    class="fc-btn fc-btn-primary">
                                + Ajouter une question
                            </button>
                        </div>

                        <div id="questions-container">
                            <!-- Les questions sont ajoutées ici par JavaScript -->
                            <div id="no-questions"
                                 style="text-align:center;padding:40px;background:#F5F7FF;border-radius:14px;border:2px dashed #D1DCE9;color:#9A9494;">
                                <div style="font-size:32px;margin-bottom:10px;">❓</div>
                                <p style="font-size:14px;font-weight:300;">
                                    Cliquez sur "+ Ajouter une question" pour commencer
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- BOUTON SOUMETTRE -->
                    <div style="display:flex;gap:12px;justify-content:flex-end;padding-top:20px;border-top:1px solid #E8E0E0;">
                        <button type="button"
                                onclick="toggleFormCreation()"
                                class="fc-btn fc-btn-outline">
                            Annuler
                        </button>
                        <button type="submit"
                                class="fc-btn fc-btn-accent fc-btn-lg"
                                id="btn-soumettre"
                                disabled>
                            💾 Enregistrer le test
                        </button>
                    </div>

                </div>
            </form>
        </div>
    </div>

    <!-- ===================================================
         LISTE DES TESTS EXISTANTS
    =================================================== -->
    <div style="background:#fff;border-radius:20px;border:1px solid #E8E0E0;overflow:hidden;">

        <div style="padding:20px 24px;border-bottom:1px solid #E8E0E0;">
            <h2 style="font-family:'Playfair Display',serif;font-size:20px;font-weight:700;color:#2A2727;margin-bottom:2px;">
                Tests existants
            </h2>
            <p style="font-size:12px;color:#9A9494;font-weight:300;margin:0;">
                <?= count($tests) ?> test<?= count($tests) > 1 ? 's' : '' ?> créé<?= count($tests) > 1 ? 's' : '' ?>
            </p>
        </div>

        <?php if (empty($tests)): ?>
        <div style="padding:48px;text-align:center;color:#9A9494;">
            <div style="font-size:40px;margin-bottom:12px;">📋</div>
            <p style="font-size:14px;font-weight:300;">Aucun test créé pour l'instant.</p>
        </div>

        <?php else: ?>

        <!-- En-tête colonnes -->
        <div class="test-row test-row-header">
            <span>Titre</span>
            <span>Catégorie</span>
            <span>Questions</span>
            <span>Durée</span>
            <span>Statut</span>
            <span>Actions</span>
        </div>

        <?php foreach ($tests as $test):
            $cat = $cat_info[$test['categorie']] ?? ['label'=>$test['categorie'],'bg'=>'#E8E0E0','color'=>'#2A2727','emoji'=>'📋'];
            $lien_test = BASE_URL . 'intervenante/test.php?id=' . $test['id'];
        ?>
        <div class="test-row">

            <!-- Titre -->
            <div>
                <div style="font-size:14px;font-weight:600;color:#2A2727;margin-bottom:3px;">
                    <?= htmlspecialchars($test['titre']) ?>
                </div>
                <?php if ($test['description']): ?>
                <div style="font-size:12px;color:#9A9494;font-weight:300;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:300px;">
                    <?= htmlspecialchars(substr($test['description'], 0, 60)) ?>...
                </div>
                <?php endif; ?>
            </div>

            <!-- Catégorie -->
            <div>
                <span style="background:<?= $cat['bg'] ?>;color:<?= $cat['color'] ?>;font-size:12px;font-weight:600;padding:4px 12px;border-radius:50px;">
                    <?= $cat['emoji'] ?> <?= $cat['label'] ?>
                </span>
            </div>

            <!-- Nb questions -->
            <div style="font-size:14px;font-weight:600;color:#2A2727;text-align:center;">
                <?= $test['nb_questions'] ?>
                <span style="font-size:11px;font-weight:300;color:#9A9494;display:block;">question<?= $test['nb_questions'] > 1 ? 's' : '' ?></span>
            </div>

            <!-- Durée -->
            <div style="font-size:13px;color:#5A5555;text-align:center;">
                <?= $test['duree_limite'] ? $test['duree_limite'] . ' min' : '∞' ?>
            </div>

            <!-- Statut -->
            <div>
                <span style="background:<?= $test['actif'] ? '#D1FAE5' : '#FEE2E2' ?>;color:<?= $test['actif'] ? '#065F46' : '#991B1B' ?>;font-size:11px;font-weight:600;padding:4px 12px;border-radius:50px;">
                    <?= $test['actif'] ? '✅ Actif' : '⏸ Inactif' ?>
                </span>
            </div>

            <!-- Actions -->
            <div style="display:flex;align-items:center;gap:6px;flex-wrap:wrap;">

                <!-- Générer lien unique pour une intervenante -->
                <a href="<?= BASE_URL ?>admin/generer-lien.php?id_test=<?= $test['id'] ?>"
                   title="Générer un lien pour une intervenante"
                   style="background:#F7E597;color:#78610A;border-radius:50px;padding:5px 12px;font-size:12px;font-weight:500;text-decoration:none;transition:background .2s;"
                   onmouseover="this.style.background='#E8CF60'"
                   onmouseout="this.style.background='#F7E597'">
                    🔗 Lien
                </a>

                <!-- Modifier -->
                <a href="<?= BASE_URL ?>admin/modifier-test.php?id=<?= $test['id'] ?>"
                   title="Modifier ce test"
                   style="background:#D1DCE9;color:#1E3A5F;border-radius:50px;padding:5px 12px;font-size:12px;font-weight:500;text-decoration:none;transition:background .2s;"
                   onmouseover="this.style.background='#B8CAE0'"
                   onmouseout="this.style.background='#D1DCE9'">
                    ✏️ Modifier
                </a>

                <!-- Toggle actif -->
                <a href="tests.php?toggle=<?= $test['id'] ?>"
                   title="<?= $test['actif'] ? 'Désactiver' : 'Activer' ?>"
                   style="background:#D1DCE9;color:#1E3A5F;border-radius:50px;padding:5px 12px;font-size:12px;font-weight:500;text-decoration:none;transition:background .2s;"
                   onmouseover="this.style.background='#B8CAE0'"
                   onmouseout="this.style.background='#D1DCE9'">
                    <?= $test['actif'] ? '⏸' : '▶' ?>
                </a>

                <!-- Supprimer -->
                <a href="tests.php?supprimer=<?= $test['id'] ?>"
                   onclick="return confirm('Supprimer ce test ? Toutes les questions et résultats seront supprimés.')"
                   title="Supprimer"
                   style="background:#FEE2E2;color:#991B1B;border-radius:50px;padding:5px 12px;font-size:12px;font-weight:500;text-decoration:none;transition:background .2s;"
                   onmouseover="this.style.background='#FCA5A5'"
                   onmouseout="this.style.background='#FEE2E2'">
                    🗑️
                </a>
            </div>

        </div>
        <?php endforeach; ?>

        <?php endif; ?>
    </div>

    <!-- TOAST copie lien -->
    <div id="toast-lien"
         style="position:fixed;bottom:28px;left:50%;transform:translateX(-50%) translateY(80px);
                background:#2A2727;color:#fff;padding:12px 24px;border-radius:50px;
                font-size:13px;font-weight:500;opacity:0;transition:all .3s;z-index:9999;">
        ✅ Lien copié dans le presse-papier !
    </div>

</div>

<script>
// ===================================================
// GESTION DU FORMULAIRE
// ===================================================

let nbQuestions = 0;
const lettres   = ['A', 'B', 'C', 'D'];

function toggleFormCreation() {
    const form = document.getElementById('form-creation');
    form.style.display = form.style.display === 'none' ? 'block' : 'none';
    if (form.style.display === 'block') {
        form.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }
}

function ajouterQuestion() {
    const index     = nbQuestions;
    const container = document.getElementById('questions-container');
    const noQ       = document.getElementById('no-questions');

    if (noQ) noQ.style.display = 'none';

    const div = document.createElement('div');
    div.className  = 'q-block';
    div.id         = 'question-' + index;

    div.innerHTML = `
        <div class="q-block-header">
            <div style="display:flex;align-items:center;gap:10px;">
                <div class="q-num-badge">${index + 1}</div>
                <span style="font-size:14px;font-weight:600;color:#2A2727;">Question ${index + 1}</span>
            </div>
            <button type="button" class="btn-suppr-q" onclick="supprimerQuestion(${index})">
                ✕ Supprimer
            </button>
        </div>

        <!-- Texte de la question -->
        <div class="fc-form-group" style="margin-bottom:14px;">
            <label class="fc-label">Texte de la question *</label>
            <textarea name="questions[${index}][texte]"
                      class="fc-textarea"
                      style="min-height:64px;"
                      placeholder="Ex : Quel produit utiliser pour nettoyer les vitres ?"
                      required
                      oninput="verifierFormulaire()"></textarea>
        </div>

        <div style="display:grid;grid-template-columns:1fr 1fr 100px;gap:12px;margin-bottom:16px;">

            <!-- Type -->
            <div class="fc-form-group" style="margin:0;">
                <label class="fc-label">Type</label>
                <select name="questions[${index}][type]" class="fc-select">
                    <option value="qcm">QCM</option>
                    <option value="mise_en_situation">Mise en situation</option>
                </select>
            </div>

            <!-- Image -->
            <div class="fc-form-group" style="margin:0;">
                <label class="fc-label">Image illustrative (optionnel)</label>
                <input type="file"
                       name="q_image_${index}"
                       class="fc-input-file"
                       accept="image/jpeg,image/png,image/webp"
                       onchange="previewImage(this, 'preview-${index}')">
                <img id="preview-${index}" class="img-preview" alt="Aperçu">
            </div>

            <!-- Points -->
            <div class="fc-form-group" style="margin:0;">
                <label class="fc-label">Points</label>
                <input type="number"
                       name="questions[${index}][points]"
                       class="fc-input"
                       value="1"
                       min="1" max="10">
            </div>
        </div>

        <!-- Choix de réponses -->
        <div>
            <label class="fc-label" style="margin-bottom:10px;">
                Choix de réponses — 
                <span style="color:#9A9494;font-weight:300;font-size:12px;">Sélectionnez la bonne réponse avec le bouton radio</span>
            </label>
            <div id="choix-${index}">
                ${genererChoix(index, 0)}
                ${genererChoix(index, 1)}
                ${genererChoix(index, 2)}
                ${genererChoix(index, 3)}
            </div>
        </div>
    `;

    container.appendChild(div);
    nbQuestions++;
    majBadgeNbQuestions();
    verifierFormulaire();
}

function genererChoix(qIndex, cIndex) {
    return `
        <div class="choix-row">
            <div class="choix-lettre" id="lettre-${qIndex}-${cIndex}">${lettres[cIndex]}</div>
            <input type="text"
                   name="questions[${qIndex}][choix][${cIndex}]"
                   class="fc-input"
                   placeholder="Réponse ${lettres[cIndex]}..."
                   style="margin:0;"
                   oninput="verifierFormulaire()">
            <label style="display:flex;align-items:center;gap:6px;cursor:pointer;font-size:12px;color:#2A2727;white-space:nowrap;">
                <input type="radio"
                       name="questions[${qIndex}][bonne]"
                       value="${cIndex}"
                       style="width:16px;height:16px;accent-color:#2A2727;cursor:pointer;"
                       onchange="majLettresBonne(${qIndex}, ${cIndex})"
                       required>
                ✓ Bonne
            </label>
        </div>
    `;
}

function majLettresBonne(qIndex, bonneIndex) {
    for (let i = 0; i < 4; i++) {
        const lettre = document.getElementById('lettre-' + qIndex + '-' + i);
        if (lettre) {
            lettre.className = 'choix-lettre' + (i === bonneIndex ? ' correct-selected' : '');
        }
    }
}

function supprimerQuestion(index) {
    const el = document.getElementById('question-' + index);
    if (el) {
        el.style.animation = 'fadeOut .2s ease forwards';
        setTimeout(() => {
            el.remove();
            const container = document.getElementById('questions-container');
            if (container.children.length === 0) {
                document.getElementById('no-questions').style.display = 'block';
            }
            majBadgeNbQuestions();
            verifierFormulaire();
        }, 200);
    }
}

function majBadgeNbQuestions() {
    const nb = document.querySelectorAll('.q-block').length;
    const badge = document.getElementById('nb-questions-badge');
    badge.textContent = nb + ' question' + (nb > 1 ? 's' : '');
}

function verifierFormulaire() {
    const titre    = document.querySelector('input[name="titre"]');
    const categorie= document.querySelector('select[name="categorie"]');
    const questions= document.querySelectorAll('.q-block');
    const btn      = document.getElementById('btn-soumettre');

    const ok = titre && titre.value.trim() !== ''
            && categorie && categorie.value !== ''
            && questions.length > 0;

    btn.disabled = !ok;
    btn.style.opacity = ok ? '1' : '0.5';
}

function previewImage(input, previewId) {
    const preview = document.getElementById(previewId);
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = e => {
            preview.src     = e.target.result;
            preview.style.display = 'block';
        };
        reader.readAsDataURL(input.files[0]);
    }
}

function copierLien(lien) {
    navigator.clipboard.writeText(lien).then(() => {
        const toast = document.getElementById('toast-lien');
        toast.style.opacity = '1';
        toast.style.transform = 'translateX(-50%) translateY(0)';
        setTimeout(() => {
            toast.style.opacity = '0';
            toast.style.transform = 'translateX(-50%) translateY(80px)';
        }, 2500);
    });
}

// Animation suppression
const style = document.createElement('style');
style.textContent = '@keyframes fadeOut { to { opacity:0; transform:translateY(-10px); } }';
document.head.appendChild(style);

// Ouvrir le formulaire si message de création
<?php if ($message): ?>
document.getElementById('form-creation').style.display = 'none';
<?php endif; ?>
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>