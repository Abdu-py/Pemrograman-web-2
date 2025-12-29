<?php
include 'koneksi.php';
$id = $_GET['id'];
$data = mysqli_fetch_array(mysqli_query($koneksi, "SELECT * FROM santri WHERE id='$id'"));
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Edit Data Santri</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
  <style>
    body {
      font-family: 'Poppins', sans-serif;
      background: #f6f9fc;
      display: flex;
      justify-content: center;
      align-items: center;
      height: 100vh;
    }
    .container {
      background: white;
      padding: 30px;
      border-radius: 15px;
      box-shadow: 0 10px 20px rgba(0,0,0,0.1);
      width: 400px;
    }
    h2 { text-align: center; margin-bottom: 20px; color: #333; }
    label { font-weight: 600; margin-top: 10px; display: block; }
    input {
      width: 100%; padding: 10px; border: 1px solid #ddd;
      border-radius: 8px; margin-top: 5px; font-size: 14px;
    }
    button {
      width: 100%; padding: 12px; background: #0066ff; border: none;
      border-radius: 8px; color: white; font-weight: 600; margin-top: 20px;
      cursor: pointer; transition: 0.3s;
    }
    button:hover { background: #004bcc; }
  </style>
</head>
<body>
  <div class="container">
    <h2>Edit Data Santri</h2>
    <form action="simpan.php" method="POST">
      <input type="hidden" name="id" value="<?= $data['id'] ?>">

      <label>Nama Santri</label>
      <input type="text" name="nama_santri" value="<?= $data['nama_santri'] ?>" required>

      <label>Tempat Lahir</label>
      <input type="text" name="tempat_lahir" value="<?= $data['tempat_lahir'] ?>">

      <label>Tanggal Lahir</label>
      <input type="date" name="tanggal_lahir" value="<?= $data['tanggal_lahir'] ?>">

      <label>Kelas</label>
      <input type="text" name="kelas" value="<?= $data['kelas'] ?>">

      <label>Nilai Ujian</label>
      <input type="number" step="0.01" name="nilai_ujian" value="<?= $data['nilai_ujian'] ?>">

      <button type="submit">Simpan Perubahan</button>
    </form>
  </div>
</body>
</html>
