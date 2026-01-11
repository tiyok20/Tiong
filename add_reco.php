<?php
require 'db.php';
if (empty($_SESSION['user_id']) || empty($_SESSION['is_admin'])) { header('Location: login.php'); exit; }
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $genre = trim($_POST['genre'] ?? '');
    $desc = trim($_POST['description'] ?? '');
    if ($title) {
        $ins = $pdo->prepare('INSERT INTO recommendations (title, description, genre, created_by) VALUES (:t,:d,:g,:cb)');
        $ins->execute([':t'=>$title, ':d'=>$desc, ':g'=>$genre, ':cb'=>$_SESSION['user_id']]);
    }
}
header('Location: dashboard_admin.php');
exit;
?>