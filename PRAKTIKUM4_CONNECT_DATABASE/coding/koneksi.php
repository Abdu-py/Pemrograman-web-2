<?php
$servername = "localhost";
$username = "root";
$password = "";
$database = "ujian_tpq";

$conn = mysqli_connect($servername, $username, $password, $database);

if (!$conn) {
    die("Koneksi gagal: " . mysqli_connect_error());
} else {
    echo "Koneksi ke database ujian_tpq berhasil!";
}
?>
