<?php
include 'koneksi.php';
$id = $_GET['id'];
mysqli_query($koneksi, "DELETE FROM santri WHERE id='$id'");
header("Location: tampil_santri.php");
?>
