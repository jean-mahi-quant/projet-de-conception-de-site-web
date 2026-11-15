<?php
$titrePage = 'Les brutes';
require_once 'includes/header.php';

$brutes = $pdo->query('SELECT id, nom, avatar FROM brute ORDER BY nom')->fetchAll();
?>
<div class="container my-4">
  <h1 class="h3 mb-3">Les brutes</h1>
  <?php if (!$connecte): ?>
    <p class="text-muted"><a href="connexion.php">Connecte-toi</a> pour consulter les fiches détaillées.</p>
  <?php endif; ?>
  <div class="row g-3">
    <?php foreach ($brutes as $b): ?>
      <div class="col-12 col-sm-6 col-md-4 col-lg-3">
        <div class="card h-100 text-center">
          <img src="uploads/avatars/<?= e($b['avatar'] ?: 'default.png') ?>"
               class="card-img-top avatar" alt="Avatar de <?= e($b['nom']) ?>">
          <div class="card-body">
            <h2 class="h5 card-title"><?= e($b['nom']) ?></h2>
            <?php if ($connecte): ?>
              <a class="btn btn-sm btn-outline-secondary" href="brute.php?id=<?= (int)$b['id'] ?>">Voir la fiche</a>
            <?php endif; ?>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
    <?php if (!$brutes): ?><p>Aucune brute pour le moment.</p><?php endif; ?>
  </div>
</div>
<?php require_once 'includes/footer.php'; ?>
