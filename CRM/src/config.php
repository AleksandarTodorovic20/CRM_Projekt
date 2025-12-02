<?php
// config.php
session_start();

$DB_HOST = 'localhost';
$DB_NAME = 'u243206db1';
$DB_USER = 'u243206db1';
$DB_PASS = 'Aleks-58'; // anpassen

try {
    $pdo = new PDO(
        "mysql:host=$DB_HOST;dbname=$DB_NAME;charset=utf8mb4",
        $DB_USER,
        $DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]
    );
} catch (PDOException $e) {
    die("Datenbankverbindung fehlgeschlagen: " . htmlspecialchars($e->getMessage()));
}

require_once 'functions.php';
