<!DOCTYPE html>
<html>
<head>
  <title>Tambah Santri - Ujian Pondok</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="container">
  <h2>Tambah Data Santri</h2>
  <form action="simpan.php" method="POST">
    <div class="mb-3">
      <label class="form-label">Nama Santri</label>
      <input type="text" name="nama_santri" class="form-control" required>
    </div>
    <div class="mb-3">
      <label class="form-label">Tempat Lahir</label>
      <input type="text" name="tempat_lahir" class="form-control">
    </div>
    <div class="mb-3">
      <label class="form-label">Tanggal Lahir</label>
      <input type="date" name="tanggal_lahir" class="form-control">
    </div>
    <div class="mb-3">
      <label class="form-label">Kelas</label>
      <input type="text" name="kelas" class="form-control">
    </div>
    <div class="mb-3">
      <label class="form-label">Nilai Ujian</label>
      <input type="number" step="0.01" name="nilai_ujian" class="form-control">
    </div>
    <button type="submit" class="btn btn-primary w-100">Simpan Data</button>
  </form>
  <div class="text-center mt-3">
    <a href="tampil.php" class="btn btn-secondary">Kembali</a>
  </div>
</div>
</body>
</html>
