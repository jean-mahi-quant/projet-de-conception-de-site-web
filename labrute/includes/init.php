<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/db.php';

function connecte() {
    return isset($_SESSION['joueur_id']);
}

function est_admin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}