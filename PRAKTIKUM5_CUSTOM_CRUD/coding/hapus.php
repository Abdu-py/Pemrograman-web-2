<?php
include 'koneksi.php';
$id = $_GET['id'];

$sql = "DELETE FROM data_santri WHERE santri_id=$id";

if (mysqli_query($conn, $sql)) {
    header("Location: tampil.php");
    exit();
} else {
    echo "Gagal menghapus data: " . mysqli_error($conn);
}
mysqli_close($conn);
?>
