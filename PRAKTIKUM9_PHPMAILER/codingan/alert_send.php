<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// PENTING: path-nya disesuaikan dengan folder kamu
require 'PHPMailer-master/src/PHPMailer.php';
require 'PHPMailer-master/src/SMTP.php';
require 'PHPMailer-master/src/Exception.php';

$mail = new PHPMailer(true);

try {
    // Konfigurasi SMTP
    $mail->isSMTP();
    $mail->Host       = 'smtp.gmail.com';
    $mail->SMTPAuth   = true;
    $mail->Username   = '*******@gmail.com';
    $mail->Password   = '**** **** **** ****'; 
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port       = 587;

    $mail->setFrom('********i@gmail.com', 'Alert System');
    $mail->addAddress('***********@gmail.com');

    // Konten Email
    $mail->isHTML(true);
    $mail->Subject = "ALERT NOTIFICATION";
    $mail->Body = "
        Nama: $nama<br>
        Waktu: " . date("d-m-Y H:i:s") . "
    ";

    $mail->send();
} catch (Exception $e) {
    echo "Error: {$mail->ErrorInfo}";
}
?>
