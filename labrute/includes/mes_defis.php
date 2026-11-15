<?php
require_once 'includes/init.php';
require_once 'includes/brute.php';
exiger_connexion();

$brute = brute_du_joueur($pdo, $_SESSION['joueur_id']);
if (!$brute) {
    header('Location: creer_brute.php');
    exit;
}

$stmt = $pdo->prepare(
    'SELECT d.id, d.titre, d.etat, d.date_creation,
            (SELECT COUNT(*) FROM candidature c WHERE c.defi_id = d.id) AS nb
     FROM defi d WHERE d.brute_id = ? ORDER BY d.date_creation DESC, d.id DESC'
);
$stmt->execute([$brute['id']]);
$defis = $stmt->fetchAll();
$actif = defi_actif($pdo, $brute['id']);

$titrePage = 'Mes défis';
require_once 'includes/header.php';
?>
<div class="container my-4">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h3 mb-0">Mes défis</h1>
    <?php if (!$actif): ?>
      <a class="btn btn-warning" href="creer_defi.php">Lancer un défi</a>
    <?php endif; ?>
  </div>
  <?php if ($actif): ?>
    <p class="text-muted">Tu as un défi actif : termine-le pour en lancer un nouveau.</p>
  <?php endif; ?>

  <?php if (!$defis): ?>
    <p>Tu n'as pas encore publié de défi.</p>
  <?php else: ?>
    <div class="table-responsive">
      <table class="table align-middle">
        <thead><tr><th>Titre</th><th>Date</th><th>État</th><th>Candidatures</th><th></th></tr></thead>
        <tbody>
        <?php foreach ($defis as $d): ?>
          <tr>
            <td><?= e($d['titre']) ?></td>
            <td><?= e(date('d/m/Y', strtotime($d['date_creation']))) ?></td>
            <td><?= badge_etat($d['etat']) ?></td>
            <td><?= (int)$d['nb'] ?></td>
            <td><a href="defi.php?id=<?= (int)$d['id'] ?>">Ouvrir</a></td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>
</div>
<?php require_once 'includes/footer.php'; ?>
