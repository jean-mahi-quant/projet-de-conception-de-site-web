<?php
require_once 'includes/init.php';
require_once 'includes/brute.php';
exiger_connexion();

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$brute = false;
if ($id) {
    $stmt = $pdo->prepare('SELECT * FROM brute WHERE id = ?');
    $stmt->execute([$id]);
    $brute = $stmt->fetch();
}
if (!$brute) {
    http_response_code(404);
}

$titrePage = $brute ? $brute['nom'] : 'Brute introuvable';
require_once 'includes/header.php';
?>
<div class="container my-4">
  <?php if ($brute): ?>
    <?php $stats = stats_brute($pdo, $brute['id']); require 'includes/fiche.php'; ?>
  <?php else: ?>
    <div class="alert alert-warning" role="alert">Cette brute n'existe pas.</div>
  <?php endif; ?>
  <a href="brutes.php">Retour à la liste des brutes</a>
</div>
<?php require_once 'includes/footer.php'; ?>