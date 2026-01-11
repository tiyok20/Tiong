<?php
session_start();
require 'db.php';
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!$username || !$email || !$password) {
        $message = 'Semua field wajib diisi.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = 'Email tidak valid.';
    } elseif (strlen($password) < 8 || !preg_match('/[^A-Za-z0-9]/', $password)) {
        $message = 'Password minimal 8 karakter dan mengandung karakter spesial.';
    } else {
        $stmt = $pdo->prepare('SELECT id FROM users WHERE username = :u OR email = :e');
        $stmt->execute([':u'=>$username, ':e'=>$email]);
        if ($stmt->fetch()) {
            $message = 'Username atau email sudah terpakai.';
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $ins = $pdo->prepare('INSERT INTO users (username, email, password) VALUES (:u,:e,:p)');
            $ins->execute([':u'=>$username, ':e'=>$email, ':p'=>$hash]);
            $_SESSION['user_id'] = $pdo->lastInsertId();
            $_SESSION['username'] = $username;
            $_SESSION['is_admin'] = 0;
            header('Location: dashboard_user.php');
            exit;
        }
    }
}
?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Daftar Akun - Rekomendasi Film</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
  <style>
    body {
      font-family: 'Poppins', sans-serif;
      background: linear-gradient(135deg, #dfe9f3, #ffffff);
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      color: #333;
      overflow: hidden;
    }

    .card {
      background: rgba(255, 255, 255, 0.75);
      backdrop-filter: blur(15px);
      border: none;
      border-radius: 20px;
      box-shadow: 0 8px 25px rgba(0,0,0,0.08);
      padding: 2.5rem;
      width: 100%;
      max-width: 420px;
      animation: fadeIn 0.7s ease;
    }

    @keyframes fadeIn {
      from { opacity: 0; transform: translateY(20px); }
      to { opacity: 1; transform: translateY(0); }
    }

    .card h4 {
      text-align: center;
      font-weight: 700;
      margin-bottom: 1.5rem;
      color: #4a4a8a;
    }

    .form-control {
      background: rgba(255,255,255,0.6);
      border: 1px solid rgba(200,200,200,0.4);
      border-radius: 10px;
      color: #333;
      transition: all 0.3s ease;
    }

    .form-control:focus {
      border-color: #6d5dfc;
      box-shadow: 0 0 0 0.15rem rgba(109,93,252,0.25);
      background: #fff;
    }

    .btn-primary {
      background: linear-gradient(135deg, #667eea, #764ba2);
      border: none;
      border-radius: 10px;
      width: 100%;
      padding: 10px;
      font-weight: 600;
      transition: all 0.3s ease;
    }
    .btn-primary:hover {
      background: linear-gradient(135deg, #5a6ee5, #6d3f97);
      transform: scale(1.03);
    }

    .alert {
      border-radius: 12px;
      font-weight: 500;
      animation: fadeIn 0.5s ease;
    }

    .btn-link {
      display: block;
      text-align: center;
      color: #4a4a8a;
      text-decoration: none;
      margin-top: 10px;
      font-weight: 500;
      transition: 0.3s;
    }
    .btn-link:hover {
      color: #6d5dfc;
      text-decoration: underline;
    }

    footer {
      position: absolute;
      bottom: 10px;
      font-size: 0.9rem;
      color: #888;
      width: 100%;
      text-align: center;
    }

    /* Style kecil untuk ikon toggle di dalam input */
    .input-group .btn-toggle {
      border-top-left-radius: 0;
      border-bottom-left-radius: 0;
      border-top-right-radius: 10px;
      border-bottom-right-radius: 10px;
      border: 1px solid rgba(200,200,200,0.4);
      background: rgba(255,255,255,0.9);
      padding: 0.45rem 0.6rem;
    }
    .input-group .btn-toggle:focus {
      box-shadow: none;
    }
  </style>
</head>
<body>
  <div class="card shadow-lg">
    <h4>🎬 Daftar Akun Baru</h4>
    <?php if ($message): ?>
      <div class="alert alert-danger text-center"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>

    <form method="post" novalidate>
      <div class="mb-3">
        <label class="form-label">Username</label>
        <input name="username" class="form-control" placeholder="Masukkan username..." value="<?= htmlspecialchars($_POST['username'] ?? '') ?>" required>
      </div>
      <div class="mb-3">
        <label class="form-label">Email</label>
        <input name="email" class="form-control" placeholder="contoh@email.com" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
      </div>
      <div class="mb-3">
        <label class="form-label">Password</label>
        <div class="input-group">
          <input id="passwordInput" type="password" name="password" class="form-control" placeholder="Minimal 8 karakter & spesial" aria-describedby="togglePassword" required>
          <button
            type="button"
            id="togglePassword"
            class="btn btn-toggle"
            aria-pressed="false"
            title="Tampilkan password">
            <!-- ikon mata (eye) default -->
            <svg id="eyeIcon" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
              <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
              <circle cx="12" cy="12" r="3"></circle>
            </svg>
          </button>
        </div>
      </div>
      <button class="btn btn-primary mt-3">Daftar Sekarang</button>
      <a href="login.php" class="btn-link">Sudah punya akun? Login</a>
    </form>
  </div>

  <footer>
    © <?= date('Y') ?> | Rekomendasi Film
  </footer>

  <script>
    (function() {
      const pwdInput = document.getElementById('passwordInput');
      const toggleBtn = document.getElementById('togglePassword');
      const eyeIcon = document.getElementById('eyeIcon');

      function setIcon(visible) {
        // eye (visible) and eye-off (hidden) simple SVG paths
        if (visible) {
          eyeIcon.innerHTML = '<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle>';
        } else {
          eyeIcon.innerHTML = '<path d="M17.94 17.94A10.94 10.94 0 0 1 12 20c-7 0-11-8-11-8a21.64 21.64 0 0 1 5.27-6.72"></path><path d="M1 1l22 22"></path>';
        }
      }

      toggleBtn.addEventListener('click', function() {
        const isPassword = pwdInput.type === 'password';
        pwdInput.type = isPassword ? 'text' : 'password';
        toggleBtn.setAttribute('aria-pressed', isPassword ? 'true' : 'false');
        toggleBtn.title = isPassword ? 'Sembunyikan password' : 'Tampilkan password';
        // ubah ikon sederhana (visible / hidden)
        if (isPassword) {
          // show eye-open icon
          eyeIcon.innerHTML = '<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle>';
        } else {
          // eye-off icon (crossed)
          eyeIcon.innerHTML = '<path d="M17.94 17.94A10.94 10.94 0 0 1 12 20c-7 0-11-8-11-8a21.64 21.64 0 0 1 5.27-6.72"></path><path d="M1 1l22 22"></path>';
        }
      });

      // Optional: allow toggle with press-and-hold (improves UX on desktop/mobile)
      let holdTimer = null;
      toggleBtn.addEventListener('mousedown', () => {
        // nothing special for now — left in case you want press-and-hold behavior
      });
      toggleBtn.addEventListener('mouseup', () => {
        // nothing
      });

      // Initialize icon state
      setIcon(true);
    })();
  </script>
</body>
</html>
