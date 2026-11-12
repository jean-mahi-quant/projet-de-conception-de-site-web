<?php
require_once __DIR__ . '/init.php';
$connecte = connecte();
$estAdmin = est_admin();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= e($titrePage ?? 'La Brute') ?> - La Brute</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="css/style.css" rel="stylesheet">
</head>
<body class="d-flex flex-column min-vh-100">
<header>
  <nav class="navbar navbar-expand-md navbar-dark bg-brute">
    <div class="container">
      <a class="navbar-brand fw-bold" href="index.php">La Brute</a>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse"
              data-bs-target="#menu" aria-controls="menu" aria-expanded="false" aria-label="Ouvrir le menu">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse" id="menu">
        <ul class="navbar-nav me-auto">
          <li class="nav-item"><a class="nav-link" href="index.php">Accueil</a></li>
          <li class="nav-item"><a class="nav-link" href="brutes.php">Brutes</a></li>
          <li class="nav-item"><a class="nav-link" href="defis.php">Défis</a></li>
          <?php if ($connecte): ?>
            <li class="nav-item"><a class="nav-link" href="mes_defis.php">Mes défis</a></li>
            <li class="nav-item"><a class="nav-link" href="mes_candidatures.php">Mes candidatures</a></li>
            <li class="nav-item"><a class="nav-link" href="ma_brute.php">Ma brute</a></li>
            <?php if ($estAdmin): ?>
              <li class="nav-item"><a class="nav-link" href="admin_defis.php">Administration</a></li>
            <?php endif; ?>
          <?php endif; ?>
        </ul>
        <ul class="navbar-nav">
          <?php if ($connecte): ?>
            <li class="nav-item"><a class="nav-link" href="deconnexion.php">Déconnexion</a></li>
          <?php else: ?>
            <li class="nav-item"><a class="nav-link" href="inscription.php">Inscription</a></li>
            <li class="nav-item"><a class="nav-link" href="connexion.php">Connexion</a></li>
          <?php endif; ?>
        </ul>
      </div>
    </div>
  </nav>
</header>
<main class="flex-grow-1">
