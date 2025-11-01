<?php include 'koneksi.php'; ?>
<!DOCTYPE html>
<html>
<head>
  <title>Data Santri - Ujian Pondok</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="container">
  <h2>Manajemen Data Santri</h2>
  <div class="text-end mb-3">
    <a href="form_tambah.php" class="btn btn-success">+ Tambah Santri</a>
  </div>

  <table class="table table-striped table-hover">
    <thead class="table-primary">
      <tr>
        <th>ID</th>
        <th>Nama</th>
        <th>Tempat Lahir</th>
        <th>Tanggal Lahir</th>
        <th>Kelas</th>
        <th>Nilai</th>
        <th>Aksi</th>
      </tr>
    </thead>
    <tbody>
      <?php
      $result = mysqli_query($conn, "SELECT * FROM data_santri ORDER BY santri_id DESC");
      while ($row = mysqli_fetch_assoc($result)) {
          echo "<tr>
                  <td>{$row['santri_id']}</td>
                  <td>{$row['nama_santri']}</td>
                  <td>{$row['tempat_lahir']}</td>
                  <td>{$row['tanggal_lahir']}</td>
                  <td>{$row['kelas']}</td>
                  <td>{$row['nilai_ujian']}</td>
                  <td>
                    <a href='edit.php?id={$row['santri_id']}' class='btn btn-sm btn-warning'>Edit</a>
                    <a href='hapus.php?id={$row['santri_id']}' class='btn btn-sm btn-danger' onclick='return confirm(\"Yakin mau hapus?\")'>Hapus</a>
                  </td>
                </tr>";
      }
      ?>
    </tbody>
  </table>
</div>
</body>
</html>
