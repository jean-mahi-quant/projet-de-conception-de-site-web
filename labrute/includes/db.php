<?php
// Connexion PDO (à adapter avec les identifiants du serveur de l'école)
$pdo = new PDO(
    'mysql:host=localhost;dbname=labrute;charset=utf8mb4',
    'root',
    'root',
    [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]
);

// Raccourci d'échappement pour tous les affichages dynamiques
function e($texte) {
    return htmlspecialchars((string)$texte, ENT_QUOTES, 'UTF-8');
}
