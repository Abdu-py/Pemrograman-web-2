<!DOCTYPE html>
<html lang="en">
<head>
    <title>Demo Loop</title>
</head>
<body>

<?php
// WHILE
$i = 0;
echo "<b>While Loop:</b><br>";
while ($i <= 5) {
    echo $i . " ";
    $i++;
}

// DO-WHILE
$j = 0;
echo "<br><br><b>Do While Loop:</b><br>";
do {
    echo $j . " ";
    $j++;
} while ($j <= 5);

// FOR
echo "<br><br><b>For Loop:</b><br>";
for ($k = 0; $k <= 5; $k++) {
    echo $k . " ";
}

// FOREACH
echo "<br><br><b>Foreach Loop:</b><br>";
$angka = [10, 20, 30, 40];
foreach ($angka as $value) {
    echo $value . " ";
}
?>

</body>
</html>
