<?php
session_start();
include 'db.php';
if(!isset($_SESSION['username'])||$_SESSION['role']!='admin') header("Location: login.php");
$result = $conn->query("SELECT * FROM films");
?>
<!DOCTYPE html>
<html lang="id">
<head><meta charset="UTF-8"><title>Dashboard Admin</title>
<link rel="stylesheet" href="css/style.css">
<script src="js/script.js"></script></head>
<body>
<header>Dashboard Admin</header>
<div class="container">
<p>Halo Admin <?= $_SESSION['username']; ?> | <a href="logout.php">Logout</a></p>
<?php while($film=$result->fetch_assoc()){ ?>
<div class="card">
<h3><?= $film['title'] ?></h3>
<p>Genre: <?= $film['genre'] ?> | Tahun: <?= $film['release_year'] ?></p>
</div><?php } ?>
</div>
</body>
</html>