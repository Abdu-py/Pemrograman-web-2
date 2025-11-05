<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Daftar - Shopee Style</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="container">
    <div class="logo">
        <h1>Shopaa Register</h1>
    </div>

    <h2>Buat Akun Baru</h2>
    <form method="post" action="proses_register.php">
        <label>Username</label>
        <input type="text" name="username" required>

        <label>Password</label>
        <input type="password" name="password" required>

        <label>Gender</label>
        <select name="gender" required>
            <option value="">-- Pilih Gender --</option>
            <option value="Laki-laki">Laki-laki</option>
            <option value="Perempuan">Perempuan</option>
        </select>

        <input type="submit" value="Daftar">
    </form>

    <p>Sudah punya akun? <a href="login.php">Masuk di sini</a></p>
</div>
</body>
</html>
