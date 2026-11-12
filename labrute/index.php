<?php
$titrePage = 'Accueil';
require_once 'includes/header.php';

// Aperçu : 3 dernières brutes (nom + avatar)
$brutes = $pdo->query(
    'SELECT id, nom, avatar FROM brute ORDER BY id DESC LIMIT 3'
)->fetchAll();

// Aperçu : 3 derniers défis ouverts (titre + brute auteur)
$defis = $pdo->query(
    "SELECT d.id, d.titre, b.nom AS auteur
     FROM defi d JOIN brute b ON b.id = d.brute_id
     WHERE d.etat = 'ouvert'
     ORDER BY d.date_creation DESC LIMIT 3"
)->fetchAll();
?>
<section class="hero py-5">
  <div class="container">
    <div class="row align-items-center g-4">
      <div class="col-12 col-md-7">
        <h1 class="display-5 fw-bold">Bienvenue dans l'arène</h1>
        <p class="lead">Crée ta brute, lance des défis et affronte les autres joueurs.
          Les combats sont résolus automatiquement par le serveur.</p>
        <?php if (!$connecte): ?>
          <a class="btn btn-warning btn-lg me-2 mb-2" href="inscription.php">Créer un compte</a>
          <a class="btn btn-outline-light btn-lg mb-2" href="connexion.php">Se connecter</a>
        <?php else: ?>
          <a class="btn btn-warning btn-lg mb-2" href="defis.php">Voir les défis</a>
        <?php endif; ?>
      </div>
      <div class="col-12 col-md-5 text-center">
        <img src="img/arene.png" class="img-fluid rounded" alt="Illustration de l'arène">
      </div>
    </div>
  </div>
</section>

<section class="container my-4" id="regles">
  <h2>Les règles du jeu</h2>
  <ul>
    <li>Chaque joueur possède une seule brute (30 PV, force de 5 à 8, défense = 10 - force).</li>
    <li>Un joueur publie un défi, les autres proposent leur brute comme adversaire.</li>
    <li>L'auteur choisit un candidat : le combat est résolu automatiquement et son compte rendu est enregistré.</li>
    <li>Un défi terminé ne peut plus être relancé.</li>
  </ul>
</section>

<section class="container my-4">
  <h2>Dernières brutes</h2>
  <div class="row g-3">
    <?php foreach ($brutes as $b): ?>
      <div class="col-12 col-md-4">
        <div class="card h-100 text-center">
          <img src="uploads/avatars/<?= e($b['avatar'] ?: 'default.png') ?>"
               class="card-img-top avatar" alt="Avatar de <?= e($b['nom']) ?>">
          <div class="card-body">
            <h3 class="h5 card-title"><?= e($b['nom']) ?></h3>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
    <?php if (!$brutes): ?><p>Aucune brute pour le moment.</p><?php endif; ?>
  </div>
</section>

<section class="container my-4">
  <h2>Défis ouverts</h2>
  <div class="row g-3">
    <?php foreach ($defis as $d): ?>
      <div class="col-12 col-md-4">
        <div class="card h-100">
          <div class="card-body">
            <h3 class="h5 card-title"><?= e($d['titre']) ?></h3>
            <p class="card-text text-muted">Lancé par <?= e($d['auteur']) ?></p>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
    <?php if (!$defis): ?><p>Aucun défi ouvert pour le moment.</p><?php endif; ?>
  </div>
  <p class="mt-3"><a href="defis.php">Voir tous les défis</a></p>
</section>

<?php require_once 'includes/footer.php'; ?>
