<!DOCTYPE html>
<html lang="en">
<head>
    <title>Demo Casting</title>
</head>
<body>

<?php
$teks = "123abc";  // String berisi angka dan huruf
$angka = (int)$teks;  // Hanya angka 123 yang diambil

echo "Tipe sebelum casting: " . gettype($teks) . "<br>";
echo "Tipe sesudah casting: " . gettype($angka) . "<br>";
echo "Nilainya: $angka";
?>

</body>
</html>
