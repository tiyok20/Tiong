<?php
session_start();
$_SESSION = [];
if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params['path'], $params['domain'],
        $params['secure'], $params['httponly']
    );
}
session_destroy();
?>
<!doctype html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Logout...</title>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
<style>
  body {
    margin: 0;
    height: 100vh;
    display: flex;
    justify-content: center;
    align-items: center;
    font-family: 'Poppins', sans-serif;
    background: linear-gradient(135deg, #dfe9f3, #ffffff);
    color: #333;
  }

  .logout-box {
    background: rgba(255,255,255,0.75);
    backdrop-filter: blur(10px);
    padding: 40px 50px;
    border-radius: 20px;
    text-align: center;
    box-shadow: 0 10px 25px rgba(0,0,0,0.1);
    max-width: 400px;
    width: 90%;
    animation: fadeIn 0.8s ease;
  }

  h1 {
    font-size: 2rem;
    margin-bottom: 15px;
    color: #4a4a8a;
  }

  p {
    font-size: 1.1rem;
    margin-bottom: 20px;
  }

  .btn-home {
    display: inline-block;
    padding: 10px 25px;
    background: linear-gradient(135deg, #667eea, #764ba2);
    color: #fff;
    font-weight: 600;
    text-decoration: none;
    border-radius: 10px;
    transition: all 0.3s ease;
  }
  .btn-home:hover {
    background: linear-gradient(135deg, #5a6ee5, #6d3f97);
    transform: scale(1.05);
  }

  @keyframes fadeIn {
    from { opacity: 0; transform: scale(0.9); }
    to { opacity: 1; transform: scale(1); }
  }

  @keyframes fadeOut {
    to { opacity: 0; transform: scale(1.05); }
  }

  .fade-out {
    animation: fadeOut 0.8s ease forwards;
  }
</style>
</head>
<body>

<div class="logout-box" id="logoutBox">
  <h1>👋 Sampai Jumpa Lagi!</h1>
  <p>Anda telah keluar dari <strong>TIYOK 🎬</strong></p>
  <a href="index.php" class="btn-home">Kembali ke Halaman Utama</a>
</div>

<script>
  // Auto redirect setelah 3 detik dengan efek fade
  setTimeout(() => {
    const box = document.getElementById('logoutBox');
    box.classList.add('fade-out');
  }, 2800);

  setTimeout(() => {
    window.location.href = 'index.php';
  }, 4000);
</script>

</body>
</html>
