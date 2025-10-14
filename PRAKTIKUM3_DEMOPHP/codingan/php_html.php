<!DOCTYPE html>
<html lang="en">
<head>
    <title>Demo PHP + HTML</title>
</head>
<body>

<h2>Contoh Kombinasi HTML dan PHP</h2>

<?php
echo "Halo, ini teks dari PHP!<br>";
echo "Tanggal hari ini: " . date("d-m-Y") . "<br>";
?>

<p>Ini paragraf HTML biasa.</p>

<?php
$nama = "Abdu";
echo "Nama saya adalah $nama.";
?>

</body>
</html>
