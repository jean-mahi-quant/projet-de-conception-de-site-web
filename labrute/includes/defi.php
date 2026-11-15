<?php
require_once 'includes/init.php';
require_once 'includes/brute.php';
exiger_connexion();

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$defi = false;
if ($id) {
    $stmt = $pdo->prepare(
        'SELECT d.*, b.nom AS auteur_nom, b.joueur_id AS auteur_joueur
         FROM defi d JOIN brute b ON b.id = d.brute_id
         WHERE d.id = ?'
    );
    $stmt->execute([$id]);
    $defi = $stmt->fetch();
}

if (!$defi) {
    http_response_code(404);
} else {
    $maBrute = brute_du_joueur($pdo, $_SESSION['joueur_id']);
    $estAuteur = (int)$defi['auteur_joueur'] === (int)$_SESSION['joueur_id'];
    $ouvert = $defi['etat'] === 'ouvert';

    // Candidatures : seul l'auteur voit toutes celles de son défi
    $candidatures = [];
    if ($estAuteur) {
        $stmt = $pdo->prepare(
            'SELECT c.id, c.message, c.etat, b.id AS brute_id, b.nom, b.avatar, b.force_brute, b.defense
             FROM candidature c JOIN brute b ON b.id = c.brute_id
             WHERE c.defi_id = ? ORDER BY c.id'
        );
        $stmt->execute([$defi['id']]);
        $candidatures = $stmt->fetchAll();
    }

    // Candidature du joueur connecté (s'il n'est pas l'auteur)
    $maCandidature = false;
    if (!$estAuteur && $maBrute) {
        $stmt = $pdo->prepare('SELECT * FROM candidature WHERE defi_id = ? AND brute_id = ?');
        $stmt->execute([$defi['id'], $maBrute['id']]);
        $maCandidature = $stmt->fetch();
    }

    // Combat : visible par les deux participants uniquement
    $stmt = $pdo->prepare('SELECT id, brute1_id, brute2_id FROM combat WHERE defi_id = ?');
    $stmt->execute([$defi['id']]);
    $combat = $stmt->fetch();
    $peutVoirCombat = $combat && ($estAuteur || ($maBrute && (int)$combat['brute2_id'] === (int)$maBrute['id']));
}

$titrePage = $defi ? $defi['titre'] : 'Défi introuvable';
require_once 'includes/header.php';
?>
<div class="container my-4">
<?php if (!$defi): ?>
  <div class="alert alert-warning" role="alert">Ce défi n'existe pas.</div>
<?php else: ?>
  <div class="card mb-4">
    <div class="card-body">
      <div class="d-flex justify-content-between align-items-start">
        <h1 class="h3"><?= e($defi['titre']) ?></h1>
        <?= badge_etat($defi['etat']) ?>
      </div>
      <p class="text-muted small">
        Publié le <?= e(date('d/m/Y H:i', strtotime($defi['date_creation']))) ?>
        par <a href="brute.php?id=<?= (int)$defi['brute_id'] ?>"><?= e($defi['auteur_nom']) ?></a>
      </p>
      <p><?= nl2br(e($defi['description'])) ?></p>
      <?php if ($peutVoirCombat): ?>
        <a class="btn btn-warning" href="combat.php?id=<?= (int)$combat['id'] ?>">Voir le résultat du combat</a>
      <?php endif; ?>
    </div>
  </div>

  <?php if ($estAuteur): ?>
    <h2 class="h5">Candidatures reçues (<?= count($candidatures) ?>)</h2>
    <?php if (!$candidatures): ?>
      <p class="text-muted">Aucune candidature pour le moment.</p>
    <?php else: ?>
      <ul class="list-group mb-4">
        <?php foreach ($candidatures as $c): ?>
          <li class="list-group-item">
            <div class="row align-items-center g-2">
              <div class="col-12 col-md-8">
                <div class="d-flex align-items-center">
                  <img src="uploads/avatars/<?= e($c['avatar'] ?: 'default.png') ?>"
                       class="avatar-sm me-2" alt="Avatar de <?= e($c['nom']) ?>">
                  <div>
                    <a class="fw-bold" href="brute.php?id=<?= (int)$c['brute_id'] ?>"><?= e($c['nom']) ?></a>
                    <span class="text-muted small">(force <?= (int)$c['force_brute'] ?>, défense <?= (int)$c['defense'] ?>)</span>
                    <?= badge_etat($c['etat']) ?>
                    <div class="small"><?= nl2br(e($c['message'])) ?></div>
                  </div>
                </div>
              </div>
              <div class="col-12 col-md-4 text-md-end">
                <?php if ($ouvert && $c['etat'] === 'attente'): ?>
                  <form method="post" action="choisir.php">
                    <?= csrf_champ() ?>
                    <input type="hidden" name="defi_id" value="<?= (int)$defi['id'] ?>">
                    <input type="hidden" name="candidature_id" value="<?= (int)$c['id'] ?>">
                    <button type="submit" class="btn btn-warning btn-sm">Choisir cet adversaire</button>
                  </form>
                <?php endif; ?>
              </div>
            </div>
          </li>
        <?php endforeach; ?>
      </ul>
    <?php endif; ?>

  <?php elseif (!$maBrute): ?>
    <div class="alert alert-info" role="alert">
      Il te faut une brute pour candidater. <a href="creer_brute.php">Créer ma brute</a>
    </div>

  <?php elseif ($maCandidature): ?>
    <h2 class="h5">Ma candidature</h2>
    <p><?= badge_etat($maCandidature['etat']) ?></p>
    <p class="text-muted"><?= nl2br(e($maCandidature['message'])) ?></p>

  <?php elseif ($ouvert): ?>
    <h2 class="h5">Relever le défi avec <?= e($maBrute['nom']) ?></h2>
    <form method="post" action="candidater.php">
      <?= csrf_champ() ?>
      <input type="hidden" name="defi_id" value="<?= (int)$defi['id'] ?>">
      <div class="mb-3">
        <label for="message" class="form-label">Court message pour l'auteur</label>
        <textarea class="form-control" id="message" name="message" rows="2" maxlength="255"></textarea>
      </div>
      <button type="submit" class="btn btn-warning">Proposer ma brute</button>
    </form>

  <?php else: ?>
    <p class="text-muted">Ce défi est terminé.</p>
  <?php endif; ?>

  <p class="mt-3"><a href="defis.php">Retour aux défis</a></p>
<?php endif; ?>
</div>
<?php require_once 'includes/footer.php'; ?>
