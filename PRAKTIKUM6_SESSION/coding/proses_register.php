<?php
include "db.php";

$username = $_POST['username'];
$password = $_POST['password'];
$gender   = $_POST['gender'];

$cek = mysqli_query($conn, "SELECT * FROM login WHERE username='$username'");
if (mysqli_num_rows($cek) > 0) {
    echo "<script>alert('Username sudah terdaftar!'); window.location='register.php';</script>";
} else {
    $sql = "INSERT INTO login (username, password, gender) VALUES ('$username', '$password', '$gender')";
    if (mysqli_query($conn, $sql)) {
        echo "<script>alert('Pendaftaran berhasil! Silakan login.'); window.location='login.php';</script>";
    } else {
        echo "Terjadi kesalahan: " . mysqli_error($conn);
    }
}
?>
