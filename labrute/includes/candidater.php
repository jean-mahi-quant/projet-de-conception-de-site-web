<?php
require_once 'includes/init.php';
require_once 'includes/brute.php';
exiger_connexion();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: defis.php');
    exit;
}

$defiId = filter_input(INPUT_POST, 'defi_id', FILTER_VALIDATE_INT);
$retour = $defiId ? 'defi.php?id=' . $defiId : 'defis.php';
$message = trim($_POST['message'] ?? '');

if (!csrf_valide()) {
    flash('Session expirée, veuillez réessayer.', 'danger');
    header('Location: ' . $retour);
    exit;
}

$brute = brute_du_joueur($pdo, $_SESSION['joueur_id']);
if (!$brute) {
    flash('Crée d\'abord ta brute pour candidater.', 'warning');
    header('Location: creer_brute.php');
    exit;
}
if (!$defiId || mb_strlen($message) > 255) {
    flash('Candidature invalide.', 'danger');
    header('Location: ' . $retour);
    exit;
}

$pdo->beginTransaction();
try {
    // On verrouille le défi : impossible de candidater pendant qu'il est en cours de clôture
    $stmt = $pdo->prepare('SELECT id, brute_id, etat FROM defi WHERE id = ? FOR UPDATE');
    $stmt->execute([$defiId]);
    $defi = $stmt->fetch();

    if (!$defi) {
        $pdo->rollBack();
        flash('Ce défi n\'existe pas.', 'danger');
        header('Location: defis.php');
        exit;
    }
    if ((int)$defi['brute_id'] === (int)$brute['id']) {
        $pdo->rollBack();
        flash('Tu ne peux pas répondre à ton propre défi.', 'danger');
        header('Location: ' . $retour);
        exit;
    }
    if ($defi['etat'] !== 'ouvert') {
        $pdo->rollBack();
        flash('Ce défi n\'accepte plus de candidatures.', 'warning');
        header('Location: ' . $retour);
        exit;
    }

    $stmt = $pdo->prepare('INSERT INTO candidature (defi_id, brute_id, message) VALUES (?, ?, ?)');
    $stmt->execute([$defiId, $brute['id'], $message]);
    $pdo->commit();
    flash('Ta candidature est envoyée.');
} catch (PDOException $ex) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    // Code 23000 : la contrainte UNIQUE (defi_id, brute_id) bloque le doublon
    if ($ex->getCode() === '23000') {
        flash('Tu as déjà candidaté à ce défi.', 'warning');
    } else {
        flash('Une erreur est survenue, veuillez réessayer.', 'danger');
    }
}
header('Location: ' . $retour);
exit;
