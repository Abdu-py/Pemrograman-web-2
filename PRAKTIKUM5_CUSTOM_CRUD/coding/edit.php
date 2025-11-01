<?php
include 'koneksi.php';
$id = $_GET['id'];
$data = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM data_santri WHERE santri_id=$id"));
?>
<!DOCTYPE html>
<html>
<head>
  <title>Edit Data Santri</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="container">
  <h2>Edit Data Santri</h2>
  <form action="update.php" method="POST">
    <input type="hidden" name="santri_id" value="<?= $data['santri_id'] ?>">
    <div class="mb-3">
      <label class="form-label">Nama Santri</label>
      <input type="text" name="nama_santri" class="form-control" value="<?= $data['nama_santri'] ?>" required>
    </div>
    <div class="mb-3">
      <label class="form-label">Tempat Lahir</label>
      <input type="text" name="tempat_lahir" class="form-control" value="<?= $data['tempat_lahir'] ?>">
    </div>
    <div class="mb-3">
      <label class="form-label">Tanggal Lahir</label>
      <input type="date" name="tanggal_lahir" class="form-control" value="<?= $data['tanggal_lahir'] ?>">
    </div>
    <div class="mb-3">
      <label class="form-label">Kelas</label>
      <input type="text" name="kelas" class="form-control" value="<?= $data['kelas'] ?>">
    </div>
    <div class="mb-3">
      <label class="form-label">Nilai Ujian</label>
      <input type="number" step="0.01" name="nilai_ujian" class="form-control" value="<?= $data['nilai_ujian'] ?>">
    </div>
    <button type="submit" class="btn btn-primary w-100">Update Data</button>
  </form>
  <div class="text-center mt-3">
    <a href="tampil.php" class="btn btn-secondary">Kembali</a>
  </div>
</div>
</body>
</html>
