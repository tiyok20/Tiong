<?php
// db.php - PDO connection
declare(strict_types=1);

define('DB_HOST', '127.0.0.1');
define('DB_NAME', 'rekomendasi_db');
define('DB_USER', 'root');
define('DB_PASS', '');

try {
    $pdo = new PDO(
        'mysql:host='.DB_HOST.';dbname='.DB_NAME.';charset=utf8mb4',
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]
    );
} catch (PDOException $e) {
    echo 'Database connection failed: ' . htmlspecialchars($e->getMessage());
    exit;
}

// Pastikan session hanya dimulai sekali
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
