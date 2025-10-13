<html> 
<head> 
<title>Proses Input</title> 
</head> 
<body> 
<?php
$bil1=$_POST["bil1"]; 
$bil2=$_POST["bil2"]; 
?> 
<h1>Perbandingan Bilangan </h1> 
<hr> 
Bil I : <?php echo $bil1?> 
<br> 
Bil II: <?php echo $bil2?> 
<br> 
<?php
if ($bil1<$bil2){ 
echo "$bil1 lebih kecil dari $bil2"; 
} 
elseif($bil1>$bil2){ 
echo "$bil1 lebih besar dari $bil2"; 
} 
else{ 
echo "$bil1 sama dengan $bil2"; 
} 
?> 
</body> 
</html>