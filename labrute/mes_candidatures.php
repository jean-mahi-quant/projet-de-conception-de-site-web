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
    'SELECT c.id, c.etat, c.message, d.id AS defi_id, d.titre, ba.nom AS auteur, cb.id AS combat_id
     FROM candidature c
     JOIN defi d ON d.id = c.defi_id
     JOIN brute ba ON ba.id = d.brute_id
     LEFT JOIN combat cb ON cb.defi_id = d.id
     WHERE c.brute_id = ?
     ORDER BY c.id DESC'
);
$stmt->execute([$brute['id']]);
$candidatures = $stmt->fetchAll();

$titrePage = 'Mes candidatures';
require_once 'includes/header.php';
?>
<div class="container my-4">
  <h1 class="h3 mb-3">Mes candidatures</h1>
  <?php if (!$candidatures): ?>
    <p>Tu n'as pas encore candidaté. <a href="defis.php">Voir les défis</a></p>
  <?php else: ?>
    <div class="table-responsive">
      <table class="table align-middle">
        <thead><tr><th>Défi</th><th>Auteur</th><th>État</th><th></th></tr></thead>
        <tbody>
        <?php foreach ($candidatures as $c): ?>
          <tr>
            <td><a href="defi.php?id=<?= (int)$c['defi_id'] ?>"><?= e($c['titre']) ?></a></td>
            <td><?= e($c['auteur']) ?></td>
            <td><?= badge_etat($c['etat']) ?></td>
            <td>
              <?php if ($c['etat'] === 'acceptee' && $c['combat_id']): ?>
                <a href="combat.php?id=<?= (int)$c['combat_id'] ?>">Voir le résultat</a>
              <?php endif; ?>
            </td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>
</div>
<?php require_once 'includes/footer.php'; ?>
