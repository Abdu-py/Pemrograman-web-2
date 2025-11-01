<?php
include 'koneksi.php';

$id = $_POST['santri_id'];
$nama = $_POST['nama_santri'];
$tempat = $_POST['tempat_lahir'];
$tanggal = $_POST['tanggal_lahir'];
$kelas = $_POST['kelas'];
$nilai = $_POST['nilai_ujian'];

$sql = "UPDATE data_santri 
        SET nama_santri='$nama', tempat_lahir='$tempat', tanggal_lahir='$tanggal', kelas='$kelas', nilai_ujian='$nilai' 
        WHERE santri_id=$id";

if (mysqli_query($conn, $sql)) {
    echo "Data berhasil diupdate!<br>";
    echo "<a href='tampil.php'>Kembali ke Daftar</a>";
} else {
    echo "Error: " . $sql . "<br>" . mysqli_error($conn);
}

mysqli_close($conn);
?>
