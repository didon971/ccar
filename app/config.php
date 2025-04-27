<?php
require_once __DIR__ . '/../config/database.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Vérifier si une session est déjà active avant de la démarrer
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}


