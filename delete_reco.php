<?php
require 'db.php';
session_start();

// Cegah akses tanpa login admin
if (empty($_SESSION['user_id']) || empty($_SESSION['is_admin'])) {
    header('Location: login.php');
    exit;
}

// Hapus data rekomendasi jika permintaan POST valid
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['id'])) {
    $id = (int) $_POST['id'];
    $stmt = $pdo->prepare('DELETE FROM recommendations WHERE id = ?');
    $stmt->execute([$id]);
}

// Kembali ke dashboard admin
header('Location: dashboard_admin.php');
exit;
?>
