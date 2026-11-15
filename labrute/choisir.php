<?php
require_once 'includes/init.php';
require_once 'includes/brute.php';
require_once 'includes/combat.php';
exiger_connexion();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: mes_defis.php');
    exit;
}

$defiId = filter_input(INPUT_POST, 'defi_id', FILTER_VALIDATE_INT);
$candId = filter_input(INPUT_POST, 'candidature_id', FILTER_VALIDATE_INT);
$retour = $defiId ? 'defi.php?id=' . $defiId : 'mes_defis.php';

if (!csrf_valide() || !$defiId || !$candId) {
    flash('Requête invalide.', 'danger');
    header('Location: ' . $retour);
    exit;
}

$pdo->beginTransaction();
try {
    // Verrou sur le défi : deux envois simultanés ne peuvent pas lancer deux combats
    $stmt = $pdo->prepare(
        'SELECT d.id, d.etat, d.brute_id, b.joueur_id
         FROM defi d JOIN brute b ON b.id = d.brute_id
         WHERE d.id = ? FOR UPDATE'
    );
    $stmt->execute([$defiId]);
    $defi = $stmt->fetch();

    // Seul l'auteur du défi peut choisir l'adversaire
    if (!$defi || (int)$defi['joueur_id'] !== (int)$_SESSION['joueur_id']) {
        $pdo->rollBack();
        flash('Action non autorisée.', 'danger');
        header('Location: defis.php');
        exit;
    }
    // Un défi terminé ne peut plus donner lieu à un combat (même après actualisation)
    if ($defi['etat'] !== 'ouvert') {
        $pdo->rollBack();
        flash('Ce défi est déjà terminé.', 'warning');
        header('Location: ' . $retour);
        exit;
    }

    $stmt = $pdo->prepare("SELECT brute_id FROM candidature WHERE id = ? AND defi_id = ? AND etat = 'attente'");
    $stmt->execute([$candId, $defiId]);
    $cand = $stmt->fetch();
    if (!$cand) {
        $pdo->rollBack();
        flash('Candidature introuvable.', 'danger');
        header('Location: ' . $retour);
        exit;
    }

    $stmt = $pdo->prepare('SELECT * FROM brute WHERE id = ?');
    $stmt->execute([$defi['brute_id']]);
    $brute1 = $stmt->fetch();
    $stmt->execute([$cand['brute_id']]);
    $brute2 = $stmt->fetch();

    // Simulation du combat côté serveur
    $resultat = simuler_combat($brute1, $brute2);

    $stmt = $pdo->prepare(
        'INSERT INTO combat (defi_id, brute1_id, brute2_id, vainqueur_id, compte_rendu)
         VALUES (?, ?, ?, ?, ?)'
    );
    $stmt->execute([$defiId, $brute1['id'], $brute2['id'], $resultat['vainqueur_id'], $resultat['compte_rendu']]);
    $combatId = (int)$pdo->lastInsertId();

    $pdo->prepare("UPDATE candidature SET etat = 'acceptee' WHERE id = ?")->execute([$candId]);
    $pdo->prepare("UPDATE candidature SET etat = 'non_retenue' WHERE defi_id = ? AND id <> ?")->execute([$defiId, $candId]);
    $pdo->prepare("UPDATE defi SET etat = 'termine' WHERE id = ?")->execute([$defiId]);

    $pdo->commit();
    flash('Le combat a eu lieu !');
    header('Location: combat.php?id=' . $combatId);
    exit;
} catch (Throwable $ex) {
    echo '<pre>' . $ex . '</pre>'; exit;
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
        flash('Une erreur est survenue, le combat n\'a pas été lancé.', 'danger');
    header('Location: ' . $retour);
    exit;
}
