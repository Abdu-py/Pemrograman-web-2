<?php
include 'koneksi.php';

$id     = $_POST['id'];
$nama   = $_POST['nama_santri'];
$tempat = $_POST['tempat_lahir'];
$tgl    = $_POST['tanggal_lahir'];
$kelas  = $_POST['kelas'];
$nilai  = $_POST['nilai_ujian'];

$query = "UPDATE santri SET
            nama_santri='$nama',
            tempat_lahir='$tempat',
            tanggal_lahir='$tgl',
            kelas='$kelas',
            nilai_ujian='$nilai'
          WHERE id='$id'";
mysqli_query($koneksi, $query);

header("Location: tampil_santri.php");
?>
