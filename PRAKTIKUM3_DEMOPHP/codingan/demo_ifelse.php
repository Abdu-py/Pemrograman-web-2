<!DOCTYPE html>
<html lang="en">
<head>
    <title>Demo If Else</title>
</head>
<body>

<?php
$nilai = 75;

if ($nilai >= 90) {
    echo "Nilai A (Sangat Baik)";
} elseif ($nilai >= 75) {
    echo "Nilai B (Baik)";
} elseif ($nilai >= 60) {
    echo "Nilai C (Cukup)";
} else {
    echo "Nilai D (Kurang)";
}
?>

<hr>

<?php
// Contoh sama dengan switch
$hari = "Rabu";

switch ($hari) {
    case "Senin": echo "Hari ini Senin"; break;
    case "Selasa": echo "Hari ini Selasa"; break;
    case "Rabu": echo "Hari ini Rabu"; break;
    default: echo "Hari tidak diketahui";
}
?>

</body>
</html>
