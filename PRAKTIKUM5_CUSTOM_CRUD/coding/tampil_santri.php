<?php
include 'koneksi.php';
$query = mysqli_query($koneksi, "SELECT * FROM santri ORDER BY id DESC");
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Data Santri</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
  <style>
    body {
      font-family: 'Poppins', sans-serif;
      background: #f4f6f8;
      padding: 40px;
    }
    h2 {
      text-align: center;
      color: #333;
    }
    table {
      width: 90%;
      margin: 20px auto;
      border-collapse: collapse;
      background: white;
      border-radius: 10px;
      overflow: hidden;
      box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    }
    th, td {
      padding: 12px 15px;
      text-align: center;
    }
    th {
      background: #0066ff;
      color: white;
    }
    tr:nth-child(even) { background: #f9f9f9; }
    a.btn {
      padding: 6px 10px;
      border-radius: 6px;
      text-decoration: none;
      color: white;
      font-size: 13px;
      font-weight: 600;
    }
    .edit { background: #ffc107; }
    .hapus { background: #dc3545; }
    .tambah {
      display: block;
      width: 150px;
      margin: 20px auto;
      text-align: center;
      background: #28a745;
      padding: 10px;
      border-radius: 8px;
      color: white;
      text-decoration: none;
      font-weight: 600;
    }
  </style>
</head>
<body>
  <h2>Data Santri</h2>
  <a href="form_santri.html" class="tambah">+ Tambah Santri</a>
  <table>
    <tr>
      <th>No</th>
      <th>Nama</th>
      <th>Tempat Lahir</th>
      <th>Tanggal Lahir</th>
      <th>Kelas</th>
      <th>Nilai</th>
      <th>Aksi</th>
    </tr>
    <?php
    $no = 1;
    while($data = mysqli_fetch_array($query)) {
      echo "
      <tr>
        <td>$no</td>
        <td>{$data['nama_santri']}</td>
        <td>{$data['tempat_lahir']}</td>
        <td>{$data['tanggal_lahir']}</td>
        <td>{$data['kelas']}</td>
        <td>{$data['nilai_ujian']}</td>
        <td>
          <a href='edit.php?id={$data['id']}' class='btn edit'>Edit</a>
          <a href='hapus.php?id={$data['id']}' class='btn hapus' onclick='return confirm(\"Yakin hapus data ini?\")'>Hapus</a>
        </td>
      </tr>";
      $no++;
    }
    ?>
  </table>
</body>
</html>
