<?php
session_start();
require 'db.php';

// Cek admin login
if (empty($_SESSION['user_id']) || empty($_SESSION['is_admin'])) {
    header('Location: login.php');
    exit;
}

// Tambah film
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_movie'])) {
    $title = trim($_POST['title']);
    $genre = trim($_POST['genre']);
    $desc = trim($_POST['description']);
    $image = null;

    if (!empty($_FILES['image']['name'])) {
        $targetDir = "uploads/";
        if (!is_dir($targetDir)) mkdir($targetDir, 0777, true);
        $fileName = time() . "_" . basename($_FILES['image']['name']);
        $targetFilePath = $targetDir . $fileName;
        $allowed = ['jpg','jpeg','png','gif','webp'];

        if (in_array(strtolower(pathinfo($fileName, PATHINFO_EXTENSION)), $allowed)) {
            if (move_uploaded_file($_FILES['image']['tmp_name'], $targetFilePath)) {
                $image = $fileName;
            }
        }
    }

    if ($title && $genre && $desc) {
        $stmt = $pdo->prepare("INSERT INTO recommendations (title, genre, description, image, created_by, created_at) VALUES (:t, :g, :d, :img, :uid, NOW())");
        $stmt->execute([
            ':t' => $title,
            ':g' => $genre,
            ':d' => $desc,
            ':img' => $image,
            ':uid' => $_SESSION['user_id']
        ]);
        $msg = "✅ Film berhasil ditambahkan!";
    } else {
        $msg = "⚠️ Semua kolom wajib diisi.";
    }
}

// Hapus film
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $stmt = $pdo->prepare("SELECT image FROM recommendations WHERE id=:id");
    $stmt->execute([':id' => $id]);
    $data = $stmt->fetch();

    if ($data && $data['image'] && file_exists("uploads/" . $data['image'])) {
        unlink("uploads/" . $data['image']);
    }

    $stmt = $pdo->prepare("DELETE FROM recommendations WHERE id=:id");
    $stmt->execute([':id' => $id]);
    $msg = "🗑️ Film berhasil dihapus!";
}

// Ambil daftar film
$stmt = $pdo->query("SELECT r.*, u.username,
    (SELECT COUNT(*) FROM ratings rat WHERE rat.reco_id = r.id) AS total_ratings
    FROM recommendations r
    LEFT JOIN users u ON r.created_by = u.id
    ORDER BY r.created_at DESC");
$films = $stmt->fetchAll();
?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Dashboard Admin</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
  <style>
    body {
      font-family: 'Poppins', sans-serif;
      background: linear-gradient(135deg, #dfe9f3, #ffffff);
      min-height: 100vh;
      color: #333;
      overflow-x: hidden;
    }

    .navbar {
      background: rgba(255, 255, 255, 0.5);
      backdrop-filter: blur(15px);
      box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
      border-bottom: 1px solid rgba(255,255,255,0.3);
    }

    .navbar h4 {
      color: #4a4a8a;
    }

    .card {
      background: rgba(255, 255, 255, 0.7);
      border: none;
      border-radius: 20px;
      backdrop-filter: blur(10px);
      box-shadow: 0 8px 25px rgba(0,0,0,0.08);
      transition: all 0.3s ease;
      animation: fadeIn 0.6s ease;
    }
    .card:hover {
      transform: translateY(-5px);
      box-shadow: 0 10px 30px rgba(0,0,0,0.1);
    }

    @keyframes fadeIn {
      from { opacity: 0; transform: translateY(15px); }
      to { opacity: 1; transform: translateY(0); }
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

    .form-control, .form-select {
      background: rgba(255,255,255,0.6);
      border: 1px solid rgba(200,200,200,0.4);
      border-radius: 10px;
      color: #333;
    }
    .form-control:focus {
      border-color: #6d5dfc;
      box-shadow: 0 0 0 0.15rem rgba(109,93,252,0.25);
    }

    img.preview {
      width: 100%;
      height: 240px;
      border-radius: 15px;
      object-fit: cover;
      box-shadow: 0 4px 20px rgba(0,0,0,0.1);
    }

    footer {
      font-size: 0.9rem;
      color: #666;
      padding: 20px 0;
    }

    #preview {
      display: none;
      width: 100%;
      border-radius: 10px;
      margin-top: 10px;
      box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }

    .alert {
      border-radius: 12px;
      font-weight: 500;
      animation: fadeIn 0.5s ease;
    }
  </style>
</head>
<body>
  <nav class="navbar p-3 d-flex justify-content-between align-items-center">
    <h4 class="fw-bold mb-0">🎬 Admin Dashboard</h4>
    <div class="d-flex align-items-center">
      <a href="index.php" class="btn btn-outline-secondary btn-sm me-2">Lihat Situs</a>
      <a href="logout.php" class="btn btn-danger btn-sm">Logout</a>
    </div>
  </nav>

  <div class="container my-5">
    <?php if (!empty($msg)): ?>
      <div class="alert alert-info text-center"><?= htmlspecialchars($msg) ?></div>
    <?php endif; ?>

    <div class="card p-4 mb-4">
      <h5 class="mb-3 fw-semibold">Tambah Rekomendasi Film</h5>
      <form method="post" enctype="multipart/form-data" class="row g-3">
        <div class="col-md-4">
          <label class="form-label">Judul</label>
          <input type="text" name="title" class="form-control" placeholder="Contoh: Interstellar">
        </div>
        <div class="col-md-4">
          <label class="form-label">Genre</label>
          <input type="text" name="genre" class="form-control" placeholder="Contoh: Sci-Fi">
        </div>
        <div class="col-md-4">
          <label class="form-label">Gambar</label>
          <input type="file" name="image" accept="image/*" class="form-control" id="imageInput">
          <img id="preview" alt="Preview">
        </div>
        <div class="col-md-12">
          <label class="form-label">Deskripsi</label>
          <textarea name="description" rows="3" class="form-control" placeholder="Tuliskan sinopsis singkat..."></textarea>
        </div>
        <div class="col-md-12 text-end">
          <button class="btn btn-primary" name="add_movie">+ Tambah Film</button>
        </div>
      </form>
    </div>

    <h5 class="mb-3 fw-semibold">🎞️ Daftar Rekomendasi Film</h5>
    <div class="row">
      <?php foreach ($films as $f): ?>
      <div class="col-md-6 mb-4">
        <div class="card p-3 h-100">
          <?php if (!empty($f['image'])): ?>
            <img src="uploads/<?= htmlspecialchars($f['image']) ?>" class="preview mb-3" alt="<?= htmlspecialchars($f['title']) ?>">
          <?php endif; ?>
          <h5 class="mb-1"><?= htmlspecialchars($f['title']) ?></h5>
          <small class="text-muted"><?= htmlspecialchars($f['genre']) ?> — oleh <?= htmlspecialchars($f['username'] ?? 'System') ?></small>
          <p class="mt-2"><?= nl2br(htmlspecialchars($f['description'])) ?></p>
          <div class="d-flex justify-content-between align-items-center">
            <small>⭐ <?= htmlspecialchars($f['total_ratings']) ?> rating</small>
            <a href="?delete=<?= htmlspecialchars($f['id']) ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Hapus film ini?')">Hapus</a>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
      <?php if (!count($films)): ?>
        <p class="text-center text-muted">Belum ada film ditambahkan.</p>
      <?php endif; ?>
    </div>
  </div>

  <footer class="text-center">
    © <?= date('Y') ?> | Admin Panel - TIYOK 🎥
  </footer>

  <script>
    // Preview gambar upload
    document.getElementById('imageInput').addEventListener('change', function(event) {
      const file = event.target.files[0];
      const preview = document.getElementById('preview');
      if (file) {
        const reader = new FileReader();
        reader.onload = e => {
          preview.src = e.target.result;
          preview.style.display = 'block';
        };
        reader.readAsDataURL(file);
      } else {
        preview.style.display = 'none';
      }
    });
  </script>
</body>
</html>
