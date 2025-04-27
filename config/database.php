<?php
require_once __DIR__ . '/../vendor/autoload.php';

if (!defined('UPLOADS_PATH')) {
    define('UPLOADS_PATH', __DIR__ . '/../public/uploads/');
}
if (!defined('PHOTOS_PATH')) {
    define('PHOTOS_PATH', __DIR__ . '/../public/uploads/photos/');
}
if (!defined('VIDEOS_PATH')) {
    define('VIDEOS_PATH', __DIR__ . '/../public/uploads/videos/');
}
if (!defined('UPLOADS_URL')) {
    define('UPLOADS_URL', '/uploads/');
}

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

if (!defined('DB_DSN')) {
    define('DB_DSN', 'mysql:host=' . $_ENV['DB_HOST'] . ';dbname=' . $_ENV['DB_NAME'] . ';charset=utf8');
    define('DB_USER', $_ENV['DB_USER']);
    define('DB_PASSWORD', $_ENV['DB_PASSWORD']);
}

try {
    $pdo = new PDO(DB_DSN, DB_USER, DB_PASSWORD);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erreur de connexion à la base de données : " . $e->getMessage());
}
