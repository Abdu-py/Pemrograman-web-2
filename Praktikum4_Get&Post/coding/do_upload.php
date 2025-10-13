<html> 
<head> 
<title></title> 
</head> 
<body> 
<h1>Simpan file yang diupload</h1> 
<br> 
<?php
if ($files!="none") { 
copy("$files1","hasilupload.txt") or 
die ("No files"); 
} 
else { 
die("Tidak ada file yang diupload"); 
} 
?> 
</body> 
</html>  