<?php
// Fonctions liées aux brutes

// Enregistre l'avatar envoyé. Retourne [nom_de_fichier, null] ou [null, message_d_erreur]
function enregistrer_avatar($fichier) {
    if ($fichier['error'] !== UPLOAD_ERR_OK) {
        return [null, "L'envoi de l'avatar a échoué."];
    }
    if ($fichier['size'] > 2 * 1024 * 1024) {
        return [null, "L'avatar ne doit pas dépasser 2 Mo."];
    }
    $types = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/gif' => 'gif', 'image/webp' => 'webp'];
    $info = @getimagesize($fichier['tmp_name']);
    $mime = $info['mime'] ?? '';
    if (!isset($types[$mime])) {
        return [null, "Format d'image non accepté (JPG, PNG, GIF ou WebP)."];
    }
    $nom = bin2hex(random_bytes(8)) . '.' . $types[$mime];
    $dossier = __DIR__ . '/../uploads/avatars/';
    if (!move_uploaded_file($fichier['tmp_name'], $dossier . $nom)) {
        return [null, "Impossible d'enregistrer l'avatar."];
    }
    return [$nom, null];
}

function supprimer_avatar($nom) {
    if ($nom && $nom !== 'default.png') {
        $chemin = __DIR__ . '/../uploads/avatars/' . basename($nom);
        if (is_file($chemin)) {
            unlink($chemin);
        }
    }
}

// Combats, victoires et défaites calculés à partir des résultats enregistrés
function stats_brute($pdo, $brute_id) {
    $stmt = $pdo->prepare('SELECT COUNT(*) FROM combat WHERE brute1_id = ? OR brute2_id = ?');
    $stmt->execute([$brute_id, $brute_id]);
    $combats = (int)$stmt->fetchColumn();

    $stmt = $pdo->prepare('SELECT COUNT(*) FROM combat WHERE vainqueur_id = ?');
    $stmt->execute([$brute_id]);
    $victoires = (int)$stmt->fetchColumn();

    return ['combats' => $combats, 'victoires' => $victoires, 'defaites' => $combats - $victoires];
}

// Brute d'un joueur (ou false s'il n'en a pas encore)
function brute_du_joueur($pdo, $joueur_id) {
    $stmt = $pdo->prepare('SELECT * FROM brute WHERE joueur_id = ?');
    $stmt->execute([$joueur_id]);
    return $stmt->fetch();
}

// Un joueur a-t-il déjà un défi actif (ouvert) ?
function defi_actif($pdo, $brute_id) {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM defi WHERE brute_id = ? AND etat = 'ouvert'");
    $stmt->execute([$brute_id]);
    return $stmt->fetchColumn() > 0;
}

// Combats auxquels une brute a participé
function combats_brute($pdo, $brute_id) {
    $stmt = $pdo->prepare(
        'SELECT c.id, c.date_combat, c.vainqueur_id, c.brute1_id, d.titre,
                b1.nom AS nom1, b2.nom AS nom2
         FROM combat c
         JOIN defi d ON d.id = c.defi_id
         JOIN brute b1 ON b1.id = c.brute1_id
         JOIN brute b2 ON b2.id = c.brute2_id
         WHERE c.brute1_id = ? OR c.brute2_id = ?
         ORDER BY c.date_combat DESC, c.id DESC'
    );
    $stmt->execute([$brute_id, $brute_id]);
    return $stmt->fetchAll();
}
