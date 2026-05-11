# 📦 Documentation de la Base de Données — FamiCare

Application web d'évaluation des intervenantes FamiCare  
Stack : **PHP 8 / MySQL 8 / PDO**

---

## 🗂️ Structure générale

La base de données comporte **7 tables** :

| Table | Rôle |
|-------|------|
| `utilisateurs` | Tous les comptes (admin + intervenantes) |
| `intervenantes` | Profil étendu des intervenantes |
| `tests` | Les tests d'évaluation créés par l'admin |
| `questions` | Les questions de chaque test |
| `choix_reponses` | Les choix de réponse pour chaque question |
| `resultats` | Les résultats obtenus après chaque passage de test |
| `reponses_detail` | Le détail question par question de chaque résultat |
| `notifications` | Les notifications internes de l'application |

---

## 📋 Description des tables

---

### 🔵 `utilisateurs`
Stocke tous les comptes utilisateurs de l'application (admin et intervenantes).

| Colonne | Type | Description |
|---------|------|-------------|
| `id` | INT (PK, AUTO_INCREMENT) | Identifiant unique de l'utilisateur |
| `nom` | VARCHAR(100) | Nom de famille |
| `prenom` | VARCHAR(100) | Prénom |
| `email` | VARCHAR(150) UNIQUE | Adresse email — sert d'identifiant de connexion |
| `mot_de_passe` | VARCHAR(255) | Mot de passe hashé avec `password_hash()` |
| `role` | ENUM('admin','intervenante') | Rôle dans l'application |
| `actif` | TINYINT(1) | 1 = compte actif, 0 = compte désactivé |
| `cree_le` | DATETIME | Date et heure de création du compte |

---

### 🟢 `intervenantes`
Profil complémentaire des intervenantes, lié à leur compte utilisateur.

| Colonne | Type | Description |
|---------|------|-------------|
| `id` | INT (PK, AUTO_INCREMENT) | Identifiant unique |
| `id_utilisateur` | INT (FK → utilisateurs.id) | Lien vers le compte utilisateur |
| `telephone` | VARCHAR(20) | Numéro de téléphone |
| `categorie` | ENUM('menage','garde_enfant','repassage','accompagnement') | Spécialité de l'intervenante |
| `date_inscription` | DATETIME | Date d'inscription dans le système |

---

### 🟡 `tests`
Les tests d'évaluation créés par l'administrateur.

| Colonne | Type | Description |
|---------|------|-------------|
| `id` | INT (PK, AUTO_INCREMENT) | Identifiant unique du test |
| `titre` | VARCHAR(150) | Titre du test (ex: "Évaluation ménage débutant") |
| `description` | TEXT | Description du test affichée à l'intervenante |
| `categorie` | ENUM('menage','garde_enfant','repassage','accompagnement') | Catégorie du test |
| `duree_limite` | INT | Durée maximale en minutes (NULL = pas de limite) |
| `actif` | TINYINT(1) | 1 = test visible par les intervenantes |
| `id_createur` | INT (FK → utilisateurs.id) | Admin qui a créé le test |
| `cree_le` | DATETIME | Date de création du test |

---

### 🟣 `questions`
Les questions associées à chaque test.

| Colonne | Type | Description |
|---------|------|-------------|
| `id` | INT (PK, AUTO_INCREMENT) | Identifiant unique de la question |
| `id_test` | INT (FK → tests.id) | Test auquel appartient la question |
| `texte` | TEXT | Texte de la question |
| `type` | ENUM('qcm','mise_en_situation') | Type de question |
| `image_path` | VARCHAR(255) | Chemin vers l'image illustrative (NULL si aucune) |
| `points` | INT | Points attribués si bonne réponse |
| `ordre` | INT | Ordre d'affichage de la question dans le test |

---

### 🟩 `choix_reponses`
Les choix de réponse proposés pour chaque question.

| Colonne | Type | Description |
|---------|------|-------------|
| `id` | INT (PK, AUTO_INCREMENT) | Identifiant unique du choix |
| `id_question` | INT (FK → questions.id) | Question à laquelle appartient ce choix |
| `texte` | VARCHAR(255) | Texte du choix de réponse |
| `est_correcte` | TINYINT(1) | 1 = c'est la bonne réponse, 0 = mauvaise réponse |

---

### 🔴 `resultats`
Enregistre le résultat global d'un passage de test par une intervenante.

| Colonne | Type | Description |
|---------|------|-------------|
| `id` | INT (PK, AUTO_INCREMENT) | Identifiant unique du résultat |
| `id_intervenante` | INT (FK → intervenantes.id) | Intervenante qui a passé le test |
| `id_test` | INT (FK → tests.id) | Test qui a été passé |
| `score` | INT | Score brut obtenu (en points) |
| `pourcentage` | FLOAT | Score en pourcentage (0.00 à 100.00) |
| `mention` | ENUM('insuffisant','satisfaisant','bien','excellent') | Mention automatique selon le pourcentage |
| `duree_sec` | INT | Durée du passage en secondes |
| `passe_le` | DATETIME | Date et heure du passage du test |

---

### 🩷 `reponses_detail`
Détail de chaque réponse donnée par l'intervenante, question par question.

| Colonne | Type | Description |
|---------|------|-------------|
| `id` | INT (PK, AUTO_INCREMENT) | Identifiant unique |
| `id_resultat` | INT (FK → resultats.id) | Résultat auquel appartient ce détail |
| `id_question` | INT (FK → questions.id) | Question concernée |
| `id_choix` | INT (FK → choix_reponses.id) | Choix sélectionné par l'intervenante |
| `est_correcte` | TINYINT(1) | 1 = la réponse était correcte, 0 = incorrecte |

---

### 🔔 `notifications`
Notifications internes envoyées aux utilisateurs de l'application.

| Colonne | Type | Description |
|---------|------|-------------|
| `id` | INT (PK, AUTO_INCREMENT) | Identifiant unique |
| `id_destinataire` | INT (FK → utilisateurs.id) | Utilisateur qui reçoit la notification |
| `message` | TEXT | Contenu de la notification |
| `lien` | VARCHAR(255) | Lien de redirection au clic (ex: vers un résultat) |
| `lue` | TINYINT(1) | 0 = non lue, 1 = lue |
| `cree_le` | DATETIME | Date de création de la notification |

---

## 🔗 Schéma des relations

```
utilisateurs ──< intervenantes
utilisateurs ──< tests (via id_createur)
utilisateurs ──< notifications (via id_destinataire)
tests        ──< questions
tests        ──< resultats
questions    ──< choix_reponses
intervenantes──< resultats
resultats    ──< reponses_detail
questions    ──< reponses_detail
choix_reponses──< reponses_detail
```

Le fichier MCD complet est disponible dans [`docs/mcd_famicare.xml`](../docs/mcd_famicare.xml) (à ouvrir avec [Draw.io](https://app.diagrams.net)).

---

## 💡 Exemples de requêtes SQL

### Connexion d'un utilisateur
```sql
SELECT id, nom, prenom, role
FROM utilisateurs
WHERE email = 'marie@famicare.fr'
  AND actif = 1;
```

### Liste des tests disponibles pour une intervenante
```sql
SELECT t.id, t.titre, t.description, t.duree_limite,
       COUNT(q.id) AS nb_questions
FROM tests t
JOIN questions q ON q.id_test = t.id
WHERE t.actif = 1
GROUP BY t.id;
```

### Vérifier si une intervenante a déjà passé un test
```sql
SELECT COUNT(*) AS deja_passe
FROM resultats
WHERE id_intervenante = 3
  AND id_test = 1;
```

### Score moyen par test (tableau de bord admin)
```sql
SELECT t.titre,
       COUNT(r.id)        AS nb_passages,
       ROUND(AVG(r.pourcentage), 1) AS score_moyen,
       MIN(r.pourcentage) AS score_min,
       MAX(r.pourcentage) AS score_max
FROM tests t
LEFT JOIN resultats r ON r.id_test = t.id
GROUP BY t.id
ORDER BY score_moyen DESC;
```

### Détail des réponses d'un résultat
```sql
SELECT q.texte          AS question,
       cr.texte         AS reponse_donnee,
       rd.est_correcte,
       q.points
FROM reponses_detail rd
JOIN questions q       ON q.id  = rd.id_question
JOIN choix_reponses cr ON cr.id = rd.id_choix
WHERE rd.id_resultat = 12
ORDER BY q.ordre;
```

### Intervenantes avec score insuffisant (alerte admin)
```sql
SELECT u.prenom, u.nom, u.email,
       t.titre AS test,
       r.pourcentage,
       r.passe_le
FROM resultats r
JOIN intervenantes i ON i.id = r.id_intervenante
JOIN utilisateurs u  ON u.id = i.id_utilisateur
JOIN tests t         ON t.id = r.id_test
WHERE r.mention = 'insuffisant'
ORDER BY r.passe_le DESC;
```

---

## ⚙️ Règles métier importantes

- Un mot de passe est toujours hashé avec `password_hash($mdp, PASSWORD_DEFAULT)` — jamais en clair
- Une intervenante **ne peut pas repasser** un test déjà validé (vérification via `resultats`)
- Les mentions sont calculées automatiquement : `< 50%` = insuffisant · `50-74%` = satisfaisant · `75-89%` = bien · `≥ 90%` = excellent
- La suppression d'un test supprime en cascade ses questions, choix et résultats associés
- Les images de questions sont stockées dans `/uploads/questions/` et nommées avec `uniqid()`

---

*Documentation rédigée dans le cadre du stage L3 — FamiCare · Application d'évaluation des intervenantes*
