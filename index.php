<?php
session_start();
require 'db.php';

// Ambil filter genre (jika ada)
$genre = $_GET['genre'] ?? '';
if ($genre) {
    $stmt = $pdo->prepare('SELECT r.*, u.username,
        (SELECT IFNULL(ROUND(AVG(rat.rating),1),0) FROM ratings rat WHERE rat.reco_id = r.id) AS avg_rating,
        (SELECT COUNT(*) FROM ratings rat WHERE rat.reco_id = r.id) AS ratings_count
        FROM recommendations r
        LEFT JOIN users u ON r.created_by = u.id
        WHERE r.genre = :genre
        ORDER BY r.created_at DESC');
    $stmt->execute([':genre' => $genre]);
} else {
    $stmt = $pdo->query('SELECT r.*, u.username,
        (SELECT IFNULL(ROUND(AVG(rat.rating),1),0) FROM ratings rat WHERE rat.reco_id = r.id) AS avg_rating,
        (SELECT COUNT(*) FROM ratings rat WHERE rat.reco_id = r.id) AS ratings_count
        FROM recommendations r
        LEFT JOIN users u ON r.created_by = u.id
        ORDER BY r.created_at DESC');
}
$recs = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!doctype html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Beranda - Rekomendasi Film</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
<style>
  body {
    font-family: 'Poppins', sans-serif;
    background: linear-gradient(135deg, #dfe9f3, #ffffff);
    color: #333;
    min-height: 100vh;
    overflow-x: hidden;
  }

  .navbar {
    background: rgba(255, 255, 255, 0.5);
    backdrop-filter: blur(15px);
    border-radius: 16px;
    border: 1px solid rgba(255,255,255,0.4);
    box-shadow: 0 6px 25px rgba(0,0,0,0.05);
    padding: 1rem 1.5rem;
    margin-bottom: 2rem;
    transition: all 0.3s ease;
  }

  .navbar h4 {
    color: #4a4a8a;
    font-weight: 600;
  }

  .navbar a {
    text-decoration: none;
    color: #4a4a8a;
    font-weight: 500;
    margin-left: 15px;
    transition: color 0.3s ease;
  }

  .navbar a:hover {
    color: #6d5dfc;
  }

  .card {
    background: rgba(255,255,255,0.75);
    border: none;
    border-radius: 20px;
    backdrop-filter: blur(10px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.08);
    padding: 1.5rem;
    margin-bottom: 1.5rem;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
  }

  .card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 30px rgba(0,0,0,0.1);
  }

  .card h5 {
    color: #4a4a8a;
    font-weight: 600;
  }

  .card img {
    border-radius: 15px;
    height: 250px;
    object-fit: cover;
    width: 100%;
    margin-bottom: 1rem;
    box-shadow: 0 4px 20px rgba(0,0,0,0.08);
  }

  .text-muted {
    color: #666 !important;
  }

  .form-select {
    border-radius: 10px;
    border: 1px solid rgba(200,200,200,0.4);
    background: rgba(255,255,255,0.7);
    color: #333;
  }

  .form-select:focus {
    border-color: #6d5dfc;
    box-shadow: 0 0 0 0.15rem rgba(109,93,252,0.25);
  }

  .btn-primary {
    background: linear-gradient(135deg, #667eea, #764ba2);
    border: none;
    border-radius: 10px;
    padding: 10px 20px;
    transition: all 0.3s ease;
  }

  .btn-primary:hover {
    background: linear-gradient(135deg, #5a6ee5, #6d3f97);
    transform: scale(1.03);
  }

  footer {
    margin-top: 3rem;
    text-align: center;
    color: #666;
    font-size: 0.9rem;
  }

  @keyframes fadeIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
  }

  .fade-in {
    animation: fadeIn 0.6s ease;
  }
</style>
</head>
<body>
<div class="container py-4">

  <!-- Navbar -->
  <div class="navbar d-flex justify-content-between align-items-center fade-in">
    <h4>🎬 Rekomendasi Film</h4>
    <div>
      <?php if (!empty($_SESSION['user_id'])): ?>
        <a href="dashboard_user.php">Dashboard</a>
        <?php if (!empty($_SESSION['is_admin'])): ?>
          <a href="dashboard_admin.php">Admin</a>
        <?php endif; ?>
        <a href="logout.php">Logout</a>
      <?php else: ?>
        <a href="login.php">Login</a>
        <a href="register.php">Register</a>
      <?php endif; ?>
    </div>
  </div>

  <!-- Filter Genre -->
  <form method="get" class="mb-4 fade-in">
    <div class="row g-2 align-items-center">
      <div class="col-md-3">
        <select name="genre" class="form-select" onchange="this.form.submit()">
          <option value="">Semua Genre</option>
          <option value="Action" <?=($genre=='Action')?'selected':''?>>Action</option>
          <option value="Comedy" <?=($genre=='Comedy')?'selected':''?>>Comedy</option>
          <option value="Drama" <?=($genre=='Drama')?'selected':''?>>Drama</option>
          <option value="Horror" <?=($genre=='Horror')?'selected':''?>>Horror</option>
          <option value="Romance" <?=($genre=='Romance')?'selected':''?>>Romance</option>
        </select>
      </div>
    </div>
  </form>

  <!-- Daftar Rekomendasi -->
  <?php if (!$recs): ?>
    <div class="card fade-in text-center"><p class="mb-0">Belum ada rekomendasi film.</p></div>
  <?php else: ?>
    <div class="row">
    <?php foreach ($recs as $r): ?>
      <div class="col-md-6 mb-4 fade-in">
        <div class="card h-100">
          <h5><?=htmlspecialchars($r['title'])?></h5>
          <small class="text-muted"><?=htmlspecialchars($r['genre'])?> — oleh <?=htmlspecialchars($r['username'] ?? 'System')?></small>
          
          <?php if (!empty($r['image']) && file_exists("uploads/".$r['image'])): ?>
            <img src="uploads/<?=htmlspecialchars($r['image'])?>" alt="Poster Film">
          <?php endif; ?>
          
          <p class="mt-2"><?=nl2br(htmlspecialchars($r['description']))?></p>
          <p class="mb-1"><strong>Rata-rata:</strong> <?=htmlspecialchars($r['avg_rating'])?> / 5</p>
          <small class="text-muted"><?=htmlspecialchars($r['ratings_count'])?> rating</small>
        </div>
      </div>
    <?php endforeach; ?>
    </div>
  <?php endif; ?>

  <footer>© <?=date('Y')?> | <span style="color:#4a4a8a;">TIYOK</span> 🎥</footer>
</div>
</body>
</html>
