<?php
require_once 'includes/init.php';
exiger_admin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $cible = filter_input(INPUT_POST, 'joueur_id', FILTER_VALIDATE_INT);
    $action = $_POST['action'] ?? '';

    if (!csrf_valide() || !$cible || !in_array($action, ['desactiver', 'reactiver'], true)) {
        flash('Requête invalide.', 'danger');
    } elseif ($cible === (int)$_SESSION['joueur_id']) {
        flash('Tu ne peux pas modifier ton propre compte.', 'warning');
    } else {
        // Seuls les comptes joueurs sont modifiables, jamais un administrateur
        $stmt = $pdo->prepare("UPDATE joueur SET actif = ? WHERE id = ? AND role = 'joueur'");
        $stmt->execute([$action === 'reactiver' ? 1 : 0, $cible]);
        if ($stmt->rowCount() > 0) {
            flash($action === 'reactiver' ? 'Le compte a été réactivé.' : 'Le compte a été désactivé.');
        } else {
            flash('Aucun changement : compte introuvable, administrateur ou déjà dans cet état.', 'warning');
        }
    }
    header('Location: admin_comptes.php');
    exit;
}

$comptes = $pdo->query(
    'SELECT j.id, j.login, j.email, j.role, j.actif, b.nom AS brute
     FROM joueur j LEFT JOIN brute b ON b.joueur_id = j.id
     ORDER BY j.id'
)->fetchAll();

$titrePage = 'Administration des comptes';
require_once 'includes/header.php';
?>
<div class="container my-4">
  <h1 class="h3 mb-3">Administration</h1>
  <ul class="nav nav-tabs mb-3">
    <li class="nav-item"><a class="nav-link" href="admin_defis.php">Défis ouverts</a></li>
    <li class="nav-item"><a class="nav-link active" aria-current="page" href="admin_comptes.php">Comptes joueurs</a></li>
  </ul>

  <div class="table-responsive">
    <table class="table align-middle">
      <thead>
        <tr><th>Login</th><th>Email</th><th>Brute</th><th>Rôle</th><th>État</th><th></th></tr>
      </thead>
      <tbody>
      <?php foreach ($comptes as $c): ?>
        <tr>
          <td><?= e($c['login']) ?></td>
          <td><?= e($c['email']) ?></td>
          <td><?= e($c['brute'] ?? '-') ?></td>
          <td><?= e($c['role']) ?></td>
          <td>
            <?php if ($c['actif']): ?>
              <span class="badge text-bg-success">Actif</span>
            <?php else: ?>
              <span class="badge text-bg-secondary">Désactivé</span>
            <?php endif; ?>
          </td>
          <td>
            <?php if ($c['role'] === 'joueur'): ?>
              <form method="post" action="admin_comptes.php">
                <?= csrf_champ() ?>
                <input type="hidden" name="joueur_id" value="<?= (int)$c['id'] ?>">
                <?php if ($c['actif']): ?>
                  <input type="hidden" name="action" value="desactiver">
                  <button type="submit" class="btn btn-sm btn-outline-danger">Désactiver</button>
                <?php else: ?>
                  <input type="hidden" name="action" value="reactiver">
                  <button type="submit" class="btn btn-sm btn-outline-success">Réactiver</button>
                <?php endif; ?>
              </form>
            <?php endif; ?>
          </td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
<?php require_once 'includes/footer.php'; ?>
