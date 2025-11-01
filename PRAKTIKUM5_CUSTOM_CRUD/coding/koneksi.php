<?php
$servername = "localhost";
$username = "root";
$password = ""; // kosongkan jika root tidak punya password
$database = "ujian_pondok";

$conn = mysqli_connect($servername, $username, $password, $database);

if (!$conn) {
    die("Koneksi gagal: " . mysqli_connect_error());
} else {
    echo "Koneksi ke database ujian_pondok berhasil!";
}
?>
