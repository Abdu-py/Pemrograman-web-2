<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Login - Shopee Style</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="container">
    <div class="logo">
        <h1>Shopaa Login</h1>
    </div>

    <h2>Masuk ke Akun Anda</h2>
    <form method="post" action="proses_login.php">
        <label>Username</label>
        <input type="text" name="username" required>

        <label>Password</label>
        <input type="password" name="password" required>

        <input type="submit" value="Login">
    </form>

    <p>Belum punya akun? <a href="register.php">Daftar di sini</a></p>
</div>
</body>
</html>
