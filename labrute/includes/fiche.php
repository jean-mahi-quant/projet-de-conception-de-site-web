<?php // Fiche d'une brute : attend $brute et $stats ?>
<div class="card mb-3">
  <div class="card-body">
    <div class="row align-items-center g-3">
      <div class="col-12 col-md-4 text-center">
        <img src="uploads/avatars/<?= e($brute['avatar'] ?: 'default.png') ?>"
             class="avatar" alt="Avatar de <?= e($brute['nom']) ?>">
      </div>
      <div class="col-12 col-md-8">
        <h1 class="h3"><?= e($brute['nom']) ?></h1>
        <p><?= nl2br(e($brute['description'])) ?></p>
        <div class="row">
          <div class="col-6">
            <h2 class="h6">Caractéristiques</h2>
            <ul class="list-unstyled mb-0">
              <li>Points de vie : <?= (int)$brute['pv'] ?></li>
              <li>Force : <?= (int)$brute['force_brute'] ?></li>
              <li>Défense : <?= (int)$brute['defense'] ?></li>
            </ul>
          </div>
          <div class="col-6">
            <h2 class="h6">Résultats</h2>
            <ul class="list-unstyled mb-0">
              <li>Combats : <?= $stats['combats'] ?></li>
              <li>Victoires : <?= $stats['victoires'] ?></li>
              <li>Défaites : <?= $stats['defaites'] ?></li>
            </ul>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>