<?php
include 'koneksi.php';

$nama   = $_POST['nama_santri'];
$tempat = $_POST['tempat_lahir'];
$tgl    = $_POST['tanggal_lahir'];
$kelas  = $_POST['kelas'];
$nilai  = $_POST['nilai_ujian'];

$query = "INSERT INTO santri (nama_santri, tempat_lahir, tanggal_lahir, kelas, nilai_ujian)
          VALUES ('$nama','$tempat','$tgl','$kelas','$nilai')";
mysqli_query($koneksi, $query);

header("Location: tampil_santri.php");
?>
