<?php
require_once 'includes/init.php';
require_once 'includes/brute.php';
exiger_connexion();

// Un compte = une seule brute
$stmt = $pdo->prepare('SELECT COUNT(*) FROM brute WHERE joueur_id = ?');
$stmt->execute([$_SESSION['joueur_id']]);
if ($stmt->fetchColumn() > 0) {
    header('Location: ma_brute.php');
    exit;
}

$erreurs = [];
$nom = '';
$description = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = trim($_POST['nom'] ?? '');
    $description = trim($_POST['description'] ?? '');

    if (!csrf_valide()) {
        $erreurs[] = 'Session expirée, veuillez réessayer.';
    }
    if (mb_strlen($nom) < 3 || mb_strlen($nom) > 30) {
        $erreurs[] = 'Le nom doit contenir entre 3 et 30 caractères.';
    }
    if (mb_strlen($description) > 255) {
        $erreurs[] = 'La description ne doit pas dépasser 255 caractères.';
    }

    if (!$erreurs) {
        $stmt = $pdo->prepare('SELECT COUNT(*) FROM brute WHERE nom = ?');
        $stmt->execute([$nom]);
        if ($stmt->fetchColumn() > 0) {
            $erreurs[] = 'Ce nom de brute est déjà pris.';
        }
    }

    $avatar = null;
    if (!$erreurs && isset($_FILES['avatar']) && $_FILES['avatar']['error'] !== UPLOAD_ERR_NO_FILE) {
        [$avatar, $erreurAvatar] = enregistrer_avatar($_FILES['avatar']);
        if ($erreurAvatar) {
            $erreurs[] = $erreurAvatar;
        }
    }

    if (!$erreurs) {
        // Caractéristiques attribuées par le serveur
        $force = random_int(5, 8);
        $defense = 10 - $force;
        try {
            $stmt = $pdo->prepare(
                'INSERT INTO brute (joueur_id, nom, description, avatar, pv, force_brute, defense)
                 VALUES (?, ?, ?, ?, 30, ?, ?)'
            );
            $stmt->execute([$_SESSION['joueur_id'], $nom, $description, $avatar, $force, $defense]);
        } catch (PDOException $ex) {
            supprimer_avatar($avatar);
            header('Location: ma_brute.php'); // la contrainte UNIQUE a bloqué un doublon
            exit;
        }
        flash('Ta brute est créée !');
        header('Location: ma_brute.php');
        exit;
    }
}

$titrePage = 'Créer ma brute';
require_once 'includes/header.php';
?>
<div class="container my-4">
  <div class="row justify-content-center">
    <div class="col-12 col-md-8 col-lg-6">
      <h1 class="h3 mb-3">Créer ma brute</h1>
      <p class="text-muted">Ses points de vie, sa force et sa défense seront attribués par le serveur.</p>

      <?php if ($erreurs): ?>
        <div class="alert alert-danger" role="alert">
          <ul class="mb-0">
            <?php foreach ($erreurs as $err): ?><li><?= e($err) ?></li><?php endforeach; ?>
          </ul>
        </div>
      <?php endif; ?>

      <form method="post" action="creer_brute.php" enctype="multipart/form-data">
        <?= csrf_champ() ?>
        <div class="mb-3">
          <label for="nom" class="form-label">Nom (ou pseudo)</label>
          <input type="text" class="form-control" id="nom" name="nom"
                 value="<?= e($nom) ?>" minlength="3" maxlength="30" required>
        </div>
        <div class="mb-3">
          <label for="description" class="form-label">Courte description</label>
          <textarea class="form-control" id="description" name="description"
                    rows="3" maxlength="255"><?= e($description) ?></textarea>
        </div>
        <div class="mb-3">
          <label for="avatar" class="form-label">Avatar (JPG, PNG, GIF ou WebP, 2 Mo maximum)</label>
          <input type="file" class="form-control" id="avatar" name="avatar" accept="image/*">
        </div>
        <button type="submit" class="btn btn-warning">Créer ma brute</button>
      </form>
    </div>
  </div>
</div>
<?php require_once 'includes/footer.php'; ?>
