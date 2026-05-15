<?php
/**
 * intervenante/accueil.php
 * ================================================
 * Page d'accueil de l'espace intervenante.
 * Affiche les tests disponibles pour elle.
 * ================================================
 */

session_start();
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../includes/auth.php';

// Sécurité : réservé aux intervenantes
verif_connexion();
verif_role('intervenante');

$page_titre = 'Mes tests';
$user = utilisateur_connecte();

// ======================================================
// RÉCUPÉRER LES TESTS DISPONIBLES
// ======================================================

// Récupérer l'id de l'intervenante
$stmt = $pdo->prepare('SELECT id FROM intervenantes WHERE id_utilisateur = :uid');
$stmt->execute([':uid' => $user['id']]);
$intervenante = $stmt->fetch();
$id_intervenante = $intervenante['id'] ?? 0;

// Récupérer les tests actifs avec info si déjà passé
$stmt = $pdo->prepare(
    'SELECT t.id, t.titre, t.description, t.categorie, t.duree_limite,
            COUNT(q.id) AS nb_questions,
            (SELECT COUNT(*) FROM resultats r
             WHERE r.id_test = t.id AND r.id_intervenante = :id_interv) AS deja_passe,
            (SELECT r2.pourcentage FROM resultats r2
             WHERE r2.id_test = t.id AND r2.id_intervenante = :id_interv2
             ORDER BY r2.passe_le DESC LIMIT 1) AS dernier_score
     FROM tests t
     LEFT JOIN questions q ON q.id_test = t.id
     WHERE t.actif = 1
     GROUP BY t.id
     ORDER BY t.cree_le DESC'
);
$stmt->execute([':id_interv' => $id_intervenante, ':id_interv2' => $id_intervenante]);
$tests = $stmt->fetchAll();

// Couleurs par catégorie
$cat_colors = [
    'menage'        => ['bg' => '#FBF0EC', 'color' => '#E8846A', 'icon' => '🧹', 'label' => 'Ménage'],
    'garde_enfant'  => ['bg' => '#E8F5F0', 'color' => '#1B7A5A', 'icon' => '👶', 'label' => 'Garde d\'enfants'],
    'repassage'     => ['bg' => '#EFF6FF', 'color' => '#3B82F6', 'icon' => '👔', 'label' => 'Repassage'],
    'accompagnement'=> ['bg' => '#F5F3FF', 'color' => '#8B5CF6', 'icon' => '🤝', 'label' => 'Accompagnement'],
];

require_once __DIR__ . '/../includes/header.php';
?>

<div class="fc-container">

    <!-- EN-TÊTE -->
    <div style="margin-bottom:36px;">
        <h1 style="font-family:'Cormorant Garamond',serif; font-size:2rem; font-weight:700; color:#1B2B3A; margin-bottom:4px;">
            Bonjour, <?= htmlspecialchars($user['prenom']) ?> 👋
        </h1>
        <p style="font-size:14px; color:#8A9BAD; font-weight:300;">
            Voici vos tests disponibles. Bonne évaluation !
        </p>
    </div>

    <!-- TESTS DISPONIBLES -->
    <?php if (empty($tests)): ?>
    <div style="background:#fff; border-radius:20px; padding:64px; text-align:center; border:1px solid #E5DDD0;">
        <div style="font-size:48px; margin-bottom:16px;">📋</div>
        <h2 style="font-family:'Cormorant Garamond',serif; font-size:24px; font-weight:700; color:#1B2B3A; margin-bottom:8px;">
            Aucun test disponible
        </h2>
        <p style="font-size:14px; color:#8A9BAD; font-weight:300;">
            L'administrateur n'a pas encore créé de tests.<br>Revenez bientôt !
        </p>
    </div>

    <?php else: ?>
    <div style="display:grid; grid-template-columns:repeat(auto-fill, minmax(320px, 1fr)); gap:20px;">

        <?php foreach ($tests as $test):
            $cat = $cat_colors[$test['categorie']] ?? ['bg'=>'#F5F0E8','color'=>'#8A9BAD','icon'=>'📝','label'=>$test['categorie']];
            $deja_passe = (int)$test['deja_passe'] > 0;
            $score = $test['dernier_score'];
        ?>

        <div style="background:#fff; border-radius:20px; border:1px solid #E5DDD0; overflow:hidden; transition:all .25s; cursor:pointer;"
             onmouseover="this.style.boxShadow='0 12px 40px rgba(27,43,58,.12)'; this.style.transform='translateY(-4px)'"
             onmouseout="this.style.boxShadow='none'; this.style.transform='none'">

            <!-- Bandeau coloré de la catégorie -->
            <div style="background:<?= $cat['bg'] ?>; padding:24px; display:flex; align-items:center; justify-content:space-between;">
                <div style="display:flex; align-items:center; gap:12px;">
                    <div style="font-size:32px;"><?= $cat['icon'] ?></div>
                    <div>
                        <div style="font-size:10px; font-weight:700; letter-spacing:1.5px; text-transform:uppercase; color:<?= $cat['color'] ?>; margin-bottom:2px;">
                            <?= $cat['label'] ?>
                        </div>
                        <div style="font-size:12px; color:#8A9BAD; font-weight:300;">
                            <?= $test['nb_questions'] ?> question<?= $test['nb_questions'] > 1 ? 's' : '' ?>
                            <?= $test['duree_limite'] ? ' · ' . $test['duree_limite'] . ' min' : '' ?>
                        </div>
                    </div>
                </div>

                <!-- Badge si déjà passé -->
                <?php if ($deja_passe): ?>
                <span style="background:rgba(255,255,255,.8); color:#10B981; font-size:11px; font-weight:600; padding:4px 12px; border-radius:50px;">
                    ✓ Passé
                </span>
                <?php endif; ?>
            </div>

            <!-- Contenu -->
            <div style="padding:20px 24px 24px;">
                <h3 style="font-family:'Cormorant Garamond',serif; font-size:20px; font-weight:700; color:#1B2B3A; margin-bottom:8px; line-height:1.3;">
                    <?= htmlspecialchars($test['titre']) ?>
                </h3>
                <p style="font-size:13px; color:#3D5166; font-weight:300; line-height:1.65; margin-bottom:20px;">
                    <?= htmlspecialchars($test['description'] ?? 'Évaluez vos compétences sur ce test.') ?>
                </p>

                <!-- Score précédent si déjà passé -->
                <?php if ($deja_passe && $score !== null): ?>
                <div style="background:#F5F0E8; border-radius:10px; padding:12px 14px; margin-bottom:16px; display:flex; align-items:center; justify-content:space-between;">
                    <span style="font-size:12px; color:#8A9BAD; font-weight:300;">Votre dernier score</span>
                    <span style="font-family:'Cormorant Garamond',serif; font-size:22px; font-weight:700; color:<?= $score >= 75 ? '#10B981' : ($score >= 50 ? '#F59E0B' : '#EF4444') ?>;">
                        <?= round($score) ?>%
                    </span>
                </div>
                <?php endif; ?>

                <!-- Bouton -->
                <a href="<?= BASE_URL ?>intervenante/test.php?id=<?= $test['id'] ?>"
                   style="display:block; text-align:center; background:<?= $deja_passe ? '#F5F0E8' : '#E8846A' ?>; color:<?= $deja_passe ? '#1B2B3A' : '#fff' ?>; padding:12px; border-radius:50px; text-decoration:none; font-size:14px; font-weight:500; transition:all .2s;"
                   onmouseover="this.style.background='<?= $deja_passe ? '#EDE6D8' : '#D06A52' ?>'"
                   onmouseout="this.style.background='<?= $deja_passe ? '#F5F0E8' : '#E8846A' ?>'">
                    <?= $deja_passe ? '🔄 Repasser le test' : '▶ Commencer le test' ?>
                </a>
            </div>
        </div>

        <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <!-- LIEN VERS MES RÉSULTATS -->
    <div style="text-align:center; margin-top:36px;">
        <a href="<?= BASE_URL ?>intervenante/mes-resultats.php"
           style="font-size:14px; color:#E8846A; text-decoration:none; font-weight:500;">
            Voir tous mes résultats →
        </a>
    </div>

</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>