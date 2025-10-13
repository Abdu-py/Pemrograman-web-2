<html> 
<head> 
<title>Buku Tamu</title> 
</head> 
<body> 
<?php 
$nama=$_POST["nama"]; 
$email=$_POST["email"]; 
$komentar=$_POST["komentar"]; 
?> 
<h1>Data Buku Tamu </h1> 
<hr> 
Nama anda : <?php echo $nama?> 
<br> 
Email address : <?php echo $email?> 
<br> 
Komentar : 
<textarea name="komentar" cols="40" 
rows="5"><?php echo $komentar?> </textarea> 
<br> 
</body> 
</html>