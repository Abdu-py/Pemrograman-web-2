<?php
session_start();
include "db.php";

$username = $_POST['username'];
$password = $_POST['password'];

$query = mysqli_query($conn, "SELECT * FROM login WHERE username='$username' AND password='$password'");
$data  = mysqli_fetch_array($query);

if ($data) {
    $_SESSION['username'] = $data['username'];
    $_SESSION['gender']   = $data['gender'];
    header("Location: home.php");
} else {
    echo "<script>alert('Username atau password salah!'); window.location='login.php';</script>";
}
?>
