<?php
$titrePage = 'Les défis';
require_once 'includes/header.php';

$defis = $pdo->query(
    "SELECT d.id, d.titre, d.etat, d.date_creation, b.nom, b.avatar
     FROM defi d JOIN brute b ON b.id = d.brute_id
     ORDER BY (d.etat = 'ouvert') DESC, d.date_creation DESC"
)->fetchAll();
?>
<div class="container my-4">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h3 mb-0">Les défis</h1>
    <?php if ($connecte): ?>
      <a class="btn btn-warning" href="creer_defi.php">Lancer un défi</a>
    <?php endif; ?>
  </div>
  <?php if (!$connecte): ?>
    <p class="text-muted"><a href="connexion.php">Connecte-toi</a> pour consulter le détail des défis et candidater.</p>
  <?php endif; ?>
  <div class="row g-3">
    <?php foreach ($defis as $d): ?>
      <div class="col-12 col-md-6 col-lg-4">
        <div class="card h-100">
          <div class="card-body">
            <div class="d-flex align-items-center mb-2">
              <img src="uploads/avatars/<?= e($d['avatar'] ?: 'default.png') ?>"
                   class="avatar-sm me-2" alt="Avatar de <?= e($d['nom']) ?>">
              <span class="fw-bold"><?= e($d['nom']) ?></span>
              <span class="ms-auto"><?= badge_etat($d['etat']) ?></span>
            </div>
            <h2 class="h5 card-title"><?= e($d['titre']) ?></h2>
            <?php if ($connecte): ?>
              <p class="text-muted small">Publié le <?= e(date('d/m/Y', strtotime($d['date_creation']))) ?></p>
              <a class="btn btn-sm btn-outline-secondary" href="defi.php?id=<?= (int)$d['id'] ?>">Voir le défi</a>
            <?php endif; ?>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
    <?php if (!$defis): ?><p>Aucun défi pour le moment.</p><?php endif; ?>
  </div>
</div>
<?php require_once 'includes/footer.php'; ?>
