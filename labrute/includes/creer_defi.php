<?php
require_once 'includes/init.php';
require_once 'includes/brute.php';
exiger_connexion();

$brute = brute_du_joueur($pdo, $_SESSION['joueur_id']);
if (!$brute) {
    flash('Crée d\'abord ta brute pour lancer un défi.', 'warning');
    header('Location: creer_brute.php');
    exit;
}

$erreurs = [];
$titre = '';
$description = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titre = trim($_POST['titre'] ?? '');
    $description = trim($_POST['description'] ?? '');

    if (!csrf_valide()) {
        $erreurs[] = 'Session expirée, veuillez réessayer.';
    }
    if (mb_strlen($titre) < 3 || mb_strlen($titre) > 100) {
        $erreurs[] = 'Le titre doit contenir entre 3 et 100 caractères.';
    }
    if (mb_strlen($description) > 1000) {
        $erreurs[] = 'La description ne doit pas dépasser 1000 caractères.';
    }

    if (!$erreurs) {
        // Transaction : on verrouille la brute pour qu'un double envoi ne crée pas deux défis actifs
        $pdo->beginTransaction();
        try {
            $stmt = $pdo->prepare('SELECT id FROM brute WHERE id = ? FOR UPDATE');
            $stmt->execute([$brute['id']]);
            $stmt->fetchAll();

            if (defi_actif($pdo, $brute['id'])) {
                $pdo->rollBack();
                $erreurs[] = 'Tu as déjà un défi actif.';
            } else {
                // La date et l'état initial sont renseignés automatiquement par la base
                $stmt = $pdo->prepare('INSERT INTO defi (titre, description, brute_id) VALUES (?, ?, ?)');
                $stmt->execute([$titre, $description, $brute['id']]);
                $id = (int)$pdo->lastInsertId();
                $pdo->commit();
                flash('Ton défi est publié !');
                header('Location: defi.php?id=' . $id);
                exit;
            }
        } catch (PDOException $ex) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            $erreurs[] = 'Une erreur est survenue, veuillez réessayer.';
        }
    }
}

$actif = defi_actif($pdo, $brute['id']);
$titrePage = 'Lancer un défi';
require_once 'includes/header.php';
?>
<div class="container my-4">
  <div class="row justify-content-center">
    <div class="col-12 col-md-8 col-lg-6">
      <h1 class="h3 mb-3">Lancer un défi</h1>

      <?php if ($erreurs): ?>
        <div class="alert alert-danger" role="alert">
          <ul class="mb-0">
            <?php foreach ($erreurs as $err): ?><li><?= e($err) ?></li><?php endforeach; ?>
          </ul>
        </div>
      <?php endif; ?>

      <?php if ($actif): ?>
        <div class="alert alert-warning" role="alert">
          Tu as déjà un défi actif. Attends qu'il soit terminé pour en lancer un autre.
          <a href="mes_defis.php">Voir mes défis</a>
        </div>
      <?php else: ?>
        <form method="post" action="creer_defi.php">
          <?= csrf_champ() ?>
          <div class="mb-3">
            <label for="titre" class="form-label">Titre</label>
            <input type="text" class="form-control" id="titre" name="titre"
                   value="<?= e($titre) ?>" minlength="3" maxlength="100" required>
          </div>
          <div class="mb-3">
            <label for="description" class="form-label">Description</label>
            <textarea class="form-control" id="description" name="description"
                      rows="4" maxlength="1000"><?= e($description) ?></textarea>
          </div>
          <p class="text-muted small">Tu défieras avec ta brute : <?= e($brute['nom']) ?>.</p>
          <button type="submit" class="btn btn-warning">Publier le défi</button>
        </form>
      <?php endif; ?>
    </div>
  </div>
</div>
<?php require_once 'includes/footer.php'; ?>
