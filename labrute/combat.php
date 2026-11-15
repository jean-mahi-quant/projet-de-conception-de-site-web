<?php
require_once 'includes/init.php';
exiger_connexion();

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$combat = false;
if ($id) {
    $stmt = $pdo->prepare(
        'SELECT c.*, d.titre,
                b1.nom AS nom1, b1.avatar AS avatar1, b1.joueur_id AS joueur1,
                b2.nom AS nom2, b2.avatar AS avatar2, b2.joueur_id AS joueur2
         FROM combat c
         JOIN defi d ON d.id = c.defi_id
         JOIN brute b1 ON b1.id = c.brute1_id
         JOIN brute b2 ON b2.id = c.brute2_id
         WHERE c.id = ?'
    );
    $stmt->execute([$id]);
    $combat = $stmt->fetch();
}

// Réservé aux deux participants (et à l'administrateur)
$autorise = $combat && (
    (int)$combat['joueur1'] === (int)$_SESSION['joueur_id']
    || (int)$combat['joueur2'] === (int)$_SESSION['joueur_id']
    || est_admin()
);
if (!$combat) {
    http_response_code(404);
} elseif (!$autorise) {
    http_response_code(403);
}

$titrePage = 'Résultat du combat';
require_once 'includes/header.php';
?>
<div class="container my-4">
<?php if (!$combat): ?>
  <div class="alert alert-warning" role="alert">Ce combat n'existe pas.</div>
<?php elseif (!$autorise): ?>
  <div class="alert alert-danger" role="alert">Tu n'as pas accès à ce combat.</div>
<?php else: ?>
  <h1 class="h3">Combat : <?= e($combat['titre']) ?></h1>
  <p class="text-muted">Résolu le <?= e(date('d/m/Y à H:i', strtotime($combat['date_combat']))) ?></p>

  <div class="row g-3 align-items-center text-center mb-4">
    <?php foreach ([1, 2] as $n): ?>
      <?php $gagnant = (int)$combat['vainqueur_id'] === (int)$combat['brute' . $n . '_id']; ?>
      <div class="col-12 col-md-5 <?= $n === 2 ? 'order-md-3' : '' ?>">
        <div class="card <?= $gagnant ? 'border-success border-3' : '' ?>">
          <img src="uploads/avatars/<?= e($combat['avatar' . $n] ?: 'default.png') ?>"
               class="avatar" alt="Avatar de <?= e($combat['nom' . $n]) ?>">
          <div class="card-body">
            <h2 class="h5"><?= e($combat['nom' . $n]) ?></h2>
            <?php if ($gagnant): ?><span class="badge text-bg-success">Vainqueur</span><?php endif; ?>
          </div>
        </div>
      </div>
      <?php if ($n === 1): ?>
        <div class="col-12 col-md-2 order-md-2 fw-bold fs-4">VS</div>
      <?php endif; ?>
    <?php endforeach; ?>
  </div>

  <h2 class="h5">Compte rendu</h2>
  <div class="card">
    <div class="card-body">
      <p class="mb-0"><?= nl2br(e($combat['compte_rendu'])) ?></p>
    </div>
  </div>
<?php endif; ?>
  <p class="mt-3"><a href="ma_brute.php">Retour à ma brute</a></p>
</div>
<?php require_once 'includes/footer.php'; ?>
