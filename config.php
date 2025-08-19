<?php
// SQLite config - database file will be in data/gym.sqlite
$db_file = __DIR__ . '/data/gym.sqlite';
try {
    $pdo = new PDO('sqlite:' . $db_file);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    // enable foreign keys
    $pdo->exec('PRAGMA foreign_keys = ON;');
} catch (Exception $e) {
    die('Database connection error: ' . $e->getMessage());
}

session_start();
function is_logged() {
    return isset($_SESSION['agency_id']);
}
function require_login() {
    if (!is_logged()) {
        header('Location: login.php');
        exit;
    }
}
?>