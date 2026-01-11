<?php
require 'db.php';
session_start();

if (empty($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];
    $reco_id = (int)($_POST['reco_id'] ?? 0);
    $rating  = (int)($_POST['rating'] ?? 0);
    $review  = trim($_POST['review'] ?? '');

    if ($reco_id && $rating >= 1 && $rating <= 5) {
        // cek apakah sudah pernah memberi rating
        $exists = $pdo->prepare('SELECT 1 FROM ratings WHERE user_id = :uid AND reco_id = :rid');
        $exists->execute([':uid' => $user_id, ':rid' => $reco_id]);

        $query = $exists->fetch()
            ? 'UPDATE ratings SET rating=:r, review=:rv, created_at=CURRENT_TIMESTAMP WHERE user_id=:uid AND reco_id=:rid'
            : 'INSERT INTO ratings (user_id, reco_id, rating, review) VALUES (:uid, :rid, :r, :rv)';

        $stmt = $pdo->prepare($query);
        $stmt->execute([':uid' => $user_id, ':rid' => $reco_id, ':r' => $rating, ':rv' => $review]);
    }

    header("Location: dashboard_user.php#reco-$reco_id");
    exit;
}
?>
