<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Beranda - Shopee Style</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="home-container">
    <div class="logo">
        <h1>Shopaa</h1>
    </div>
    <h2>Selamat Datang, <?php echo $_SESSION['username']; ?>!</h2>
    <p>Gender Anda: <?php echo $_SESSION['gender']; ?></p>
    <p>Anda berhasil login ke sistem dengan autentikasi session.</p>
    <a href="logout.php" class="logout-btn">Logout</a>
</div>
</body>
</html>
