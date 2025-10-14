<!DOCTYPE html>
<html lang="en">
<head>
    <title>Demo Fungsi</title>
</head>
<body>

<?php
// Prosedur
function tampilWaktu() {
    echo "Waktu sekarang: " . date("H:i:s") . "<br>";
}

// Fungsi dengan return
function jumlah($a, $b) {
    return $a + $b;
}

// Fungsi dengan parameter opsional
function cetakTeks($teks, $tebal = true) {
    echo $tebal ? "<b>$teks</b>" : $teks;
}

tampilWaktu();
echo "Hasil penjumlahan: " . jumlah(4, 6) . "<br>";
cetakTeks("PHP itu menyenangkan!"); 
echo "<br>";
cetakTeks("Tanpa huruf tebal", false);
?>

</body>
</html>
