<?php
require_once 'includes/init.php';

if (connecte()) {
    header('Location: index.php');
    exit;
}

$erreur = '';
$login = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login = trim($_POST['login'] ?? '');
    $mdp = $_POST['mdp'] ?? '';

    if (!csrf_valide()) {
        $erreur = 'Session expirée, veuillez réessayer.';
    } else {
        $stmt = $pdo->prepare('SELECT id, login, mdp_hash, role, actif FROM joueur WHERE login = ?');
        $stmt->execute([$login]);
        $joueur = $stmt->fetch();

        if (!$joueur || !password_verify($mdp, $joueur['mdp_hash'])) {
            $erreur = 'Login ou mot de passe incorrect.';
        } elseif (!$joueur['actif']) {
            $erreur = 'Ce compte a été désactivé.';
        } else {
            session_regenerate_id(true);
            $_SESSION['joueur_id'] = (int)$joueur['id'];
            $_SESSION['login'] = $joueur['login'];
            $_SESSION['role'] = $joueur['role'];

            // Pas encore de brute : on envoie le joueur la créer
            $stmt = $pdo->prepare('SELECT COUNT(*) FROM brute WHERE joueur_id = ?');
            $stmt->execute([$joueur['id']]);
            header('Location: ' . ($stmt->fetchColumn() > 0 ? 'index.php' : 'creer_brute.php'));
            exit;
        }
    }
}

$titrePage = 'Connexion';
require_once 'includes/header.php';
?>
<div class="container my-4">
  <div class="row justify-content-center">
    <div class="col-12 col-md-8 col-lg-5">
      <h1 class="h3 mb-3">Connexion</h1>

      <?php if ($erreur): ?>
        <div class="alert alert-danger" role="alert"><?= e($erreur) ?></div>
      <?php endif; ?>

      <form method="post" action="connexion.php">
        <?= csrf_champ() ?>
        <div class="mb-3">
          <label for="login" class="form-label">Login</label>
          <input type="text" class="form-control" id="login" name="login"
                 value="<?= e($login) ?>" required autocomplete="username">
        </div>
        <div class="mb-3">
          <label for="mdp" class="form-label">Mot de passe</label>
          <input type="password" class="form-control" id="mdp" name="mdp"
                 required autocomplete="current-password">
        </div>
        <button type="submit" class="btn btn-warning">Se connecter</button>
        <a class="btn btn-link" href="inscription.php">Créer un compte</a>
      </form>
    </div>
  </div>
</div>
<?php require_once 'includes/footer.php'; ?>
