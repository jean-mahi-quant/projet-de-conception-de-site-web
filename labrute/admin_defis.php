<?php
require_once 'includes/init.php';
exiger_admin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $defiId = filter_input(INPUT_POST, 'defi_id', FILTER_VALIDATE_INT);
    if (!csrf_valide() || !$defiId) {
        flash('Requête invalide.', 'danger');
    } else {
        $pdo->beginTransaction();
        try {
            // Verrou sur le défi : on vérifie qu'il est toujours ouvert au moment de la suppression
            $stmt = $pdo->prepare('SELECT etat FROM defi WHERE id = ? FOR UPDATE');
            $stmt->execute([$defiId]);
            $etat = $stmt->fetchColumn();

            if ($etat !== 'ouvert') {
                $pdo->rollBack();
                flash('Seuls les défis encore ouverts peuvent être supprimés.', 'warning');
            } else {
                $pdo->prepare('DELETE FROM candidature WHERE defi_id = ?')->execute([$defiId]);
                $pdo->prepare('DELETE FROM defi WHERE id = ?')->execute([$defiId]);
                $pdo->commit();
                flash('Le défi a été supprimé.');
            }
        } catch (PDOException $ex) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            flash('Une erreur est survenue, le défi n\'a pas été supprimé.', 'danger');
        }
    }
    header('Location: admin_defis.php');
    exit;
}

$defis = $pdo->query(
    "SELECT d.id, d.titre, d.date_creation, b.nom AS brute, j.login,
            (SELECT COUNT(*) FROM candidature c WHERE c.defi_id = d.id) AS nb
     FROM defi d
     JOIN brute b ON b.id = d.brute_id
     JOIN joueur j ON j.id = b.joueur_id
     WHERE d.etat = 'ouvert'
     ORDER BY d.date_creation DESC, d.id DESC"
)->fetchAll();

$titrePage = 'Administration des défis';
require_once 'includes/header.php';
?>
<div class="container my-4">
  <h1 class="h3 mb-3">Administration</h1>
  <ul class="nav nav-tabs mb-3">
    <li class="nav-item"><a class="nav-link active" aria-current="page" href="admin_defis.php">Défis ouverts</a></li>
    <li class="nav-item"><a class="nav-link" href="admin_comptes.php">Comptes joueurs</a></li>
  </ul>

  <?php if (!$defis): ?>
    <p>Aucun défi ouvert.</p>
  <?php else: ?>
    <div class="table-responsive">
      <table class="table align-middle">
        <thead>
          <tr><th>Titre</th><th>Joueur</th><th>Brute</th><th>Date</th><th>Candidatures</th><th></th></tr>
        </thead>
        <tbody>
        <?php foreach ($defis as $d): ?>
          <tr>
            <td><a href="defi.php?id=<?= (int)$d['id'] ?>"><?= e($d['titre']) ?></a></td>
            <td><?= e($d['login']) ?></td>
            <td><?= e($d['brute']) ?></td>
            <td><?= e(date('d/m/Y', strtotime($d['date_creation']))) ?></td>
            <td><?= (int)$d['nb'] ?></td>
            <td>
              <form method="post" action="admin_defis.php"
                    onsubmit="return confirm('Supprimer ce défi et ses candidatures ?');">
                <?= csrf_champ() ?>
                <input type="hidden" name="defi_id" value="<?= (int)$d['id'] ?>">
                <button type="submit" class="btn btn-sm btn-outline-danger">Supprimer</button>
              </form>
            </td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>
</div>
<?php require_once 'includes/footer.php'; ?>
