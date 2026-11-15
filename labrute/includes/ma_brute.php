<?php
require_once 'includes/init.php';
require_once 'includes/brute.php';
exiger_connexion();

$stmt = $pdo->prepare('SELECT * FROM brute WHERE joueur_id = ?');
$stmt->execute([$_SESSION['joueur_id']]);
$brute = $stmt->fetch();
if (!$brute) {
    header('Location: creer_brute.php');
    exit;
}

$erreurs = [];
$description = $brute['description'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $description = trim($_POST['description'] ?? '');

    if (!csrf_valide()) {
        $erreurs[] = 'Session expirée, veuillez réessayer.';
    }
    if (mb_strlen($description) > 255) {
        $erreurs[] = 'La description ne doit pas dépasser 255 caractères.';
    }

    $nouvelAvatar = null;
    if (!$erreurs && isset($_FILES['avatar']) && $_FILES['avatar']['error'] !== UPLOAD_ERR_NO_FILE) {
        [$nouvelAvatar, $erreurAvatar] = enregistrer_avatar($_FILES['avatar']);
        if ($erreurAvatar) {
            $erreurs[] = $erreurAvatar;
        }
    }

    if (!$erreurs) {
        // Seuls la description et l'avatar sont modifiables
        $stmt = $pdo->prepare('UPDATE brute SET description = ?, avatar = ? WHERE id = ? AND joueur_id = ?');
        $stmt->execute([$description, $nouvelAvatar ?? $brute['avatar'], $brute['id'], $_SESSION['joueur_id']]);
        if ($nouvelAvatar) {
            supprimer_avatar($brute['avatar']);
        }
        flash('Ta brute a été mise à jour.');
        header('Location: ma_brute.php');
        exit;
    }
}

$stats = stats_brute($pdo, $brute['id']);
$combats = combats_brute($pdo, $brute['id']);
$titrePage = 'Ma brute';
require_once 'includes/header.php';
?>
<div class="container my-4">
  <?php require 'includes/fiche.php'; ?>

  <h2 class="h5">Mes combats</h2>
  <?php if (!$combats): ?>
    <p class="text-muted">Aucun combat pour le moment.</p>
  <?php else: ?>
    <div class="table-responsive mb-4">
      <table class="table align-middle">
        <thead><tr><th>Date</th><th>Défi</th><th>Adversaire</th><th>Résultat</th><th></th></tr></thead>
        <tbody>
        <?php foreach ($combats as $c): ?>
          <?php $adversaire = (int)$c['brute1_id'] === (int)$brute['id'] ? $c['nom2'] : $c['nom1']; ?>
          <tr>
            <td><?= e(date('d/m/Y', strtotime($c['date_combat']))) ?></td>
            <td><?= e($c['titre']) ?></td>
            <td><?= e($adversaire) ?></td>
            <td>
              <?php if ((int)$c['vainqueur_id'] === (int)$brute['id']): ?>
                <span class="badge text-bg-success">Victoire</span>
              <?php else: ?>
                <span class="badge text-bg-danger">Défaite</span>
              <?php endif; ?>
            </td>
            <td><a href="combat.php?id=<?= (int)$c['id'] ?>">Compte rendu</a></td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>

  <h2 class="h5">Modifier ma brute</h2>
  <?php if ($erreurs): ?>
    <div class="alert alert-danger" role="alert">
      <ul class="mb-0">
        <?php foreach ($erreurs as $err): ?><li><?= e($err) ?></li><?php endforeach; ?>
      </ul>
    </div>
  <?php endif; ?>
  <form method="post" action="ma_brute.php" enctype="multipart/form-data">
    <?= csrf_champ() ?>
    <div class="mb-3">
      <label for="description" class="form-label">Description</label>
      <textarea class="form-control" id="description" name="description"
                rows="3" maxlength="255"><?= e($description) ?></textarea>
    </div>
    <div class="mb-3">
      <label for="avatar" class="form-label">Nouvel avatar (facultatif)</label>
      <input type="file" class="form-control" id="avatar" name="avatar" accept="image/*">
    </div>
    <button type="submit" class="btn btn-warning">Enregistrer</button>
  </form>
</div>
<?php require_once 'includes/footer.php'; ?>
