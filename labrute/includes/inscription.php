<?php
require_once 'includes/init.php';

if (connecte()) {
    header('Location: index.php');
    exit;
}

$erreurs = [];
$login = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login = trim($_POST['login'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $mdp = $_POST['mdp'] ?? '';
    $mdp2 = $_POST['mdp2'] ?? '';

    if (!csrf_valide()) {
        $erreurs[] = 'Session expirée, veuillez réessayer.';
    }
    if (!preg_match('/^[A-Za-z0-9_-]{3,30}$/', $login)) {
        $erreurs[] = 'Le login doit contenir 3 à 30 caractères (lettres, chiffres, _ ou -).';
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 100) {
        $erreurs[] = 'Adresse email invalide.';
    }
    if (strlen($mdp) < 8) {
        $erreurs[] = 'Le mot de passe doit contenir au moins 8 caractères.';
    }
    if ($mdp !== $mdp2) {
        $erreurs[] = 'Les deux mots de passe sont différents.';
    }

    if (!$erreurs) {
        $stmt = $pdo->prepare('SELECT COUNT(*) FROM joueur WHERE login = ? OR email = ?');
        $stmt->execute([$login, $email]);
        if ($stmt->fetchColumn() > 0) {
            $erreurs[] = 'Ce login ou cet email est déjà utilisé.';
        }
    }

    if (!$erreurs) {
        $stmt = $pdo->prepare('INSERT INTO joueur (login, email, mdp_hash) VALUES (?, ?, ?)');
        $stmt->execute([$login, $email, password_hash($mdp, PASSWORD_DEFAULT)]);

        // Connexion automatique après l'inscription
        session_regenerate_id(true);
        $_SESSION['joueur_id'] = (int)$pdo->lastInsertId();
        $_SESSION['login'] = $login;
        $_SESSION['role'] = 'joueur';
        header('Location: creer_brute.php');
        exit;
    }
}

$titrePage = 'Inscription';
require_once 'includes/header.php';
?>
<div class="container my-4">
  <div class="row justify-content-center">
    <div class="col-12 col-md-8 col-lg-5">
      <h1 class="h3 mb-3">Créer un compte</h1>

      <?php if ($erreurs): ?>
        <div class="alert alert-danger" role="alert">
          <ul class="mb-0">
            <?php foreach ($erreurs as $err): ?>
              <li><?= e($err) ?></li>
            <?php endforeach; ?>
          </ul>
        </div>
      <?php endif; ?>

      <form method="post" action="inscription.php">
        <?= csrf_champ() ?>
        <div class="mb-3">
          <label for="login" class="form-label">Login</label>
          <input type="text" class="form-control" id="login" name="login"
                 value="<?= e($login) ?>" maxlength="30" required autocomplete="username">
        </div>
        <div class="mb-3">
          <label for="email" class="form-label">Email</label>
          <input type="email" class="form-control" id="email" name="email"
                 value="<?= e($email) ?>" maxlength="100" required autocomplete="email">
        </div>
        <div class="mb-3">
          <label for="mdp" class="form-label">Mot de passe (8 caractères minimum)</label>
          <input type="password" class="form-control" id="mdp" name="mdp"
                 minlength="8" required autocomplete="new-password">
        </div>
        <div class="mb-3">
          <label for="mdp2" class="form-label">Confirmation du mot de passe</label>
          <input type="password" class="form-control" id="mdp2" name="mdp2"
                 minlength="8" required autocomplete="new-password">
        </div>
        <button type="submit" class="btn btn-warning">S'inscrire</button>
        <a class="btn btn-link" href="connexion.php">J'ai déjà un compte</a>
      </form>
    </div>
  </div>
</div>
<?php require_once 'includes/footer.php'; ?>
