<?php
include 'koneksi.php';

$nama = $_POST['nama_santri'];
$tempat = $_POST['tempat_lahir'];
$tanggal = $_POST['tanggal_lahir'];
$kelas = $_POST['kelas'];
$nilai = $_POST['nilai_ujian'];

$sql = "INSERT INTO data_santri (nama_santri, tempat_lahir, tanggal_lahir, kelas, nilai_ujian)
        VALUES ('$nama', '$tempat', '$tanggal', '$kelas', '$nilai')";

if (mysqli_query($conn, $sql)) {
    echo "Data berhasil disimpan!<br>";
    echo "<a href='tampil.php'>Lihat Data</a>";
} else {
    echo "Error: " . $sql . "<br>" . mysqli_error($conn);
}
mysqli_close($conn);
?>
