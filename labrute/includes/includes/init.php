<?php
// Initialisation commune : session, base de données, fonctions d'accès
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/db.php';

function connecte() {
    return isset($_SESSION['joueur_id']);
}

function est_admin() {
    return connecte() && ($_SESSION['role'] ?? '') === 'admin';
}

// À appeler en première ligne des pages réservées aux joueurs
function exiger_connexion() {
    if (!connecte()) {
        header('Location: connexion.php');
        exit;
    }
}

// À appeler en première ligne des pages d'administration
function exiger_admin() {
    if (!est_admin()) {
        header('Location: index.php');
        exit;
    }
}

// Protection CSRF : un jeton par session, vérifié à chaque formulaire POST
function csrf_champ() {
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return '<input type="hidden" name="csrf" value="' . e($_SESSION['csrf']) . '">';
}

function csrf_valide() {
    return isset($_SESSION['csrf'], $_POST['csrf'])
        && hash_equals($_SESSION['csrf'], $_POST['csrf']);
}

// Message affiché une seule fois à la page suivante
function flash($message, $type = 'success') {
    $_SESSION['flash'] = $message;
    $_SESSION['flash_type'] = $type;
}

// Pastille d'état d'un défi ou d'une candidature
function badge_etat($etat) {
    $map = [
        'ouvert' => ['success', 'Ouvert'],
        'termine' => ['secondary', 'Terminé'],
        'attente' => ['warning', 'En attente'],
        'acceptee' => ['success', 'Acceptée'],
        'non_retenue' => ['secondary', 'Non retenue'],
    ];
    [$classe, $libelle] = $map[$etat] ?? ['light', $etat];
    return '<span class="badge text-bg-' . $classe . '">' . e($libelle) . '</span>';
}