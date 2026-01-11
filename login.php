<?php
session_start();
require 'db.php';
$message = '';

// === PROSES LOGIN ===
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!$email || !$password) {
        $message = 'Masukkan email dan password.';
    } else {
        $stmt = $pdo->prepare('SELECT id, username, password, is_admin FROM users WHERE email = :e');
        $stmt->execute([':e' => $email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            session_regenerate_id(true);
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['is_admin'] = (int)$user['is_admin'];

            $redirect = $_SESSION['is_admin'] ? 'dashboard_admin.php' : 'dashboard_user.php';

            echo <<<HTML
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Login Berhasil</title>
<style>
body {
    margin: 0;
    height: 100vh;
    display: flex;
    justify-content: center;
    align-items: center;
    background: linear-gradient(135deg, #eef2f3, #ffffff);
    font-family: 'Poppins', sans-serif;
}
.box {
    background: #fff;
    padding: 50px 60px;
    border-radius: 20px;
    box-shadow: 0 10px 35px rgba(0,0,0,0.08);
    text-align: center;
    animation: fadeIn 0.8s ease;
}
@keyframes fadeIn {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
}
.loader {
    width: 70px;
    height: 70px;
    border: 5px solid rgba(0,0,0,0.1);
    border-top: 5px solid #667eea;
    border-radius: 50%;
    animation: spin 1s linear infinite;
    margin: 0 auto 25px;
}
@keyframes spin { to { transform: rotate(360deg); } }
h3 { color: #4a4a8a; font-size: 1.3rem; margin-bottom: 8px; }
p { color: #666; font-size: 0.95rem; }
</style>
</head>
<body>
<div class="box">
    <div class="loader"></div>
    <h3>Login Berhasil 🎉</h3>
    <p>Mengarahkan ke dashboard...</p>
</div>
<script>
    setTimeout(() => { window.location.href = '$redirect'; }, 2000);
</script>
</body>
</html>
HTML;
            exit;
        } else {
            $message = 'Email atau password salah.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Masuk | TIYOK 🎬</title>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
<style>
body {
    font-family: 'Poppins', sans-serif;
    background: linear-gradient(135deg, #eef2f3, #ffffff);
    display: flex;
    justify-content: center;
    align-items: center;
    min-height: 100vh;
    margin: 0;
}
.login-card {
    width: 100%;
    max-width: 420px;
    background: #ffffff;
    border-radius: 20px;
    padding: 45px 40px 35px;
    box-shadow: 0 8px 25px rgba(0,0,0,0.08);
    animation: fadeUp 0.8s ease;
}
@keyframes fadeUp {
    from { opacity: 0; transform: translateY(40px); }
    to { opacity: 1; transform: translateY(0); }
}
.logo {
    text-align: center;
    font-size: 2rem;
    font-weight: 700;
    color: #4a4a8a;
    margin-bottom: 8px;
}
.tagline {
    text-align: center;
    font-size: 0.9rem;
    color: #666;
    margin-bottom: 30px;
}
form { display: flex; flex-direction: column; align-items: center; gap: 18px; }
.form-control {
    width: 90%;
    height: 44px;
    border-radius: 10px;
    border: 1px solid rgba(0,0,0,0.1);
    background: rgba(255,255,255,0.95);
    color: #333;
    padding: 0 14px;
    font-size: 0.95rem;
    outline: none;
    transition: all 0.3s;
}
.form-control:focus {
    border-color: #667eea;
    box-shadow: 0 0 0 0.15rem rgba(102,126,234,0.25);
}
.pw-wrapper { position: relative; width: 90%; }
.pw-toggle {
    position: absolute;
    right: 12px;
    top: 50%;
    transform: translateY(-50%);
    background: transparent;
    border: none;
    cursor: pointer;
    transition: all 0.2s;
    padding: 0;
}
.btn-login {
    width: 90%;
    height: 44px;
    border: none;
    border-radius: 10px;
    background: linear-gradient(135deg, #667eea, #764ba2);
    color: #fff;
    font-weight: 600;
    letter-spacing: 0.3px;
    box-shadow: 0 8px 20px rgba(118,75,162,0.25);
    cursor: pointer;
    transition: 0.3s;
}
.btn-login:hover { transform: translateY(-2px); box-shadow: 0 12px 25px rgba(118,75,162,0.35); }
.message {
    background: rgba(255, 0, 0, 0.1);
    color: #d9534f;
    border-radius: 10px;
    padding: 0.8rem;
    text-align: center;
    font-size: 0.9rem;
    margin-bottom: 10px;
    width: 90%;
}
.register { text-align: center; margin-top: 25px; font-size: 0.9rem; color: #555; }
.register a { color: #667eea; text-decoration: none; font-weight: 600; transition: color 0.2s; }
.register a:hover { color: #764ba2; }
footer { position: absolute; bottom: 12px; width: 100%; text-align: center; color: #999; font-size: 0.8rem; }
@media (max-width: 480px) {
    .login-card { padding: 35px 25px; }
    .form-control, .btn-login, .pw-wrapper { width: 100%; }
}
</style>
</head>

<body>
<div class="login-card">
    <div class="logo">🎬 TIYOK</div>
    <div class="tagline">Masuk untuk mengelola rekomendasi film</div>

    <?php if ($message): ?>
        <div class="message"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>

    <form method="post">
        <input type="email" name="email" class="form-control" placeholder="Alamat email" required>

        <div class="pw-wrapper">
            <input type="password" name="password" id="password" class="form-control" placeholder="Kata sandi" required>
            <button type="button" class="pw-toggle" id="pwToggle" title="Lihat kata sandi">
                <svg id="eyeIcon" xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                    <circle cx="12" cy="12" r="3"/>
                </svg>
            </button>
        </div>

        <button class="btn-login" type="submit">Masuk</button>

        <div class="register">
            Belum punya akun? <a href="register.php">Daftar Sekarang</a>
        </div>
    </form>
</div>

<footer>© 2025 TIYOK — Rekomendasi Film 🎥</footer>

<script>
const pwInput = document.getElementById('password');
const pwToggle = document.getElementById('pwToggle');
const eyeIcon = document.getElementById('eyeIcon');

pwToggle.addEventListener('click', () => {
    const show = pwInput.type === 'password';
    pwInput.type = show ? 'text' : 'password';
    if(show){
        eyeIcon.innerHTML = '<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>';
    } else {
        eyeIcon.innerHTML = '<path d="M17.94 17.94A10.94 10.94 0 0 1 12 20c-7 0-11-8-11-8a21.64 21.64 0 0 1 5.27-6.72"/><path d="M1 1l22 22"/>';
    }
});
</script>
</body>
</html>
