<?php
session_start();
require 'db.php';

// 🔒 Pastikan user sudah login
if (empty($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// 🧠 Ambil semua film
$stmt = $pdo->query("
    SELECT r.*, u.username,
    (SELECT IFNULL(ROUND(AVG(rt.rating),1),0) FROM ratings rt WHERE rt.reco_id = r.id) AS avg_rating,
    (SELECT COUNT(*) FROM ratings rt WHERE rt.reco_id = r.id) AS ratings_count
    FROM recommendations r
    LEFT JOIN users u ON r.created_by = u.id
    ORDER BY r.created_at DESC
");
$recs = $stmt->fetchAll(PDO::FETCH_ASSOC);

// ⭐ Ambil rating user
$rstmt = $pdo->prepare("SELECT reco_id, rating, review FROM ratings WHERE user_id = :uid");
$rstmt->execute([':uid' => $_SESSION['user_id']]);
$user_ratings = [];
foreach ($rstmt->fetchAll(PDO::FETCH_ASSOC) as $r) {
    $user_ratings[(int)$r['reco_id']] = $r;
}
?>
<!doctype html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Dashboard User | <?=htmlspecialchars($_SESSION['username'])?></title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

<style>
  @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap');

  body {
    background: #f8fafc;
    font-family: 'Poppins', sans-serif;
    color: #111827;
    padding: 2rem;
  }

  .topbar {
    background: #ffffff;
    border: 1px solid #e5e7eb;
    border-radius: 12px;
    padding: 1rem 1.5rem;
    margin-bottom: 2rem;
    box-shadow: 0 2px 8px rgba(0,0,0,0.05);
    display: flex;
    justify-content: space-between;
    align-items: center;
  }

  .topbar h4 {
    font-size: 1.2rem;
    font-weight: 600;
    color: #2563eb;
    margin: 0;
  }

  .btn {
    border-radius: 10px !important;
  }

  .card {
    background: #ffffff;
    border: 1px solid #e5e7eb;
    border-radius: 12px;
    box-shadow: 0 3px 12px rgba(0,0,0,0.05);
    transition: transform 0.2s ease, box-shadow 0.2s ease;
  }
  .card:hover {
    transform: translateY(-3px);
    box-shadow: 0 6px 20px rgba(0,0,0,0.08);
  }

  .movie-img {
    width: 100%;
    height: 250px;
    object-fit: cover;
    border-radius: 10px;
  }

  .movie-info h5 {
    color: #111827;
    font-weight: 600;
    margin-top: 0.8rem;
  }

  .text-muted {
    color: #6b7280 !important;
  }

  .form-select, .form-control {
    border-radius: 8px;
    border: 1px solid #d1d5db;
  }

  footer {
    margin-top: 3rem;
    text-align: center;
    color: #6b7280;
    font-size: 0.9rem;
  }
</style>
</head>

<body>
  <div class="container">
    <!-- 🧭 Topbar -->
    <div class="topbar">
      <h4>👋 Hai, <?=htmlspecialchars($_SESSION['username'])?></h4>
      <div>
        <a href="index.php" class="btn btn-outline-secondary me-2">Beranda</a>
        <a href="logout.php" class="btn btn-danger">Logout</a>
      </div>
    </div>

    <!-- 🎬 Daftar Film -->
    <div class="row g-4">
      <?php if (!$recs): ?>
        <div class="col-12">
          <div class="card p-4 text-center">Belum ada rekomendasi film.</div>
        </div>
      <?php else: ?>
        <?php foreach ($recs as $r): 
          $rid = (int)$r['id'];
          $userRating = $user_ratings[$rid]['rating'] ?? '';
          $userReview = $user_ratings[$rid]['review'] ?? '';
        ?>
        <div class="col-md-4 col-sm-6">
          <div class="card p-3 h-100" id="reco-<?=$rid?>">
            <!-- 🖼️ Gambar Film -->
            <?php if (!empty($r['image'])): ?>
              <img src="uploads/<?=htmlspecialchars($r['image'])?>" 
                   alt="<?=htmlspecialchars($r['title'])?>" 
                   class="movie-img mb-3">
            <?php else: ?>
              <div class="movie-img mb-3 d-flex align-items-center justify-content-center bg-light text-muted">
                <span>Tidak ada gambar</span>
              </div>
            <?php endif; ?>

            <!-- 🎞️ Info Film -->
            <div class="movie-info">
              <h5><?=htmlspecialchars($r['title'])?></h5>
              <p class="text-muted mb-1"><?=htmlspecialchars($r['genre'])?> — oleh <?=htmlspecialchars($r['username'] ?? 'Admin')?></p>
              <p><?=nl2br(htmlspecialchars($r['description']))?></p>
              <p><strong>⭐ Rata-rata:</strong> <?=htmlspecialchars($r['avg_rating'])?> / 5 (<?=htmlspecialchars($r['ratings_count'])?> rating)</p>
            </div>

            <!-- 💬 Form Rating -->
            <form method="post" action="rate.php" class="row g-2 align-items-end mt-2">
              <input type="hidden" name="reco_id" value="<?=$rid?>">
              <div class="col-4">
                <label class="form-label mb-1">Rating</label>
                <select name="rating" class="form-select form-select-sm">
                  <?php for ($i=1; $i<=5; $i++): ?>
                    <option value="<?=$i?>" <?=$userRating==$i?'selected':''?>><?=$i?></option>
                  <?php endfor; ?>
                </select>
              </div>
              <div class="col-8">
                <label class="form-label mb-1">Review</label>
                <input name="review" class="form-control form-control-sm" 
                       value="<?=htmlspecialchars($userReview)?>" 
                       placeholder="Tulis pendapatmu...">
              </div>
              <div class="col-12 mt-2">
                <button class="btn btn-primary w-100 btn-sm">Kirim</button>
              </div>
            </form>
          </div>
        </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>

    <footer class="mt-5">
      © <?=date('Y')?> — <span style="color:#2563eb;">TIYOK 🎬</span>
    </footer>
  </div>
</body>
</html>
