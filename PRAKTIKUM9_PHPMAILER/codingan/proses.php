<?php

include "koneksi.php";

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Include PHPMailer manual, tanpa vendor
require 'PHPMailer/Exception.php';
require 'PHPMailer/PHPMailer.php';
require 'PHPMailer/SMTP.php';

$nama        = $_POST['nama'];
$nim         = $_POST['nim'];
$kelas       = $_POST['kelas'];
$prodi       = $_POST['prodi'];
$universitas = $_POST['universitas'];
$email       = $_POST['email'];
$pesan       = $_POST['pesan'];

$body = "
<b>Notifikasi Alert PHPMailer</b><br><br>
Nama: $nama<br>
NIM: $nim<br>
Kelas: $kelas<br>
Prodi: $prodi<br>
Universitas: $universitas<br>
Pesan:<br>$pesan
";

$mail = new PHPMailer(true);

try {
    $mail->isSMTP();
    $mail->Host       = 'smtp.gmail.com';
    $mail->SMTPAuth   = true;
    $mail->Username   = 'abdudisini@gmail.com';   // ganti
    $mail->Password   = 'vvef oldx xjde ilwo';    // ganti
    $mail->SMTPSecure = 'tls';
    $mail->Port       = 587;

    $mail->setFrom('abdudisini@gmail.com', 'Alert PHPMailer');
    $mail->addAddress($email);

    $mail->isHTML(true);
    $mail->Subject = 'Notifikasi Alert Mahasiswa';
    $mail->Body    = $body;

    $mail->send();

} catch (Exception $e) {
    echo "<script>alert('Gagal mengirim email: {$mail->ErrorInfo}');window.location='index.php';</script>";
    exit;
}

// Simpan ke database
$query = mysqli_query($koneksi, "INSERT INTO user_log VALUES('', '$nama', NOW())");

if ($query) {
    echo "<script>alert('Data tersimpan & email terkirim!');window.location='index.php';</script>";
} else {
    echo "<script>alert('Gagal menyimpan data');window.location='index.php';</script>";
}
?>
