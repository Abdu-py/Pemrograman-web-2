<html> 
<head> 
<title>Contoh Form dengan POST</title> 
</head> 
<body> 
<h1>Buku Tamu</h1> 
Selamat datang di acara kami >_<.
<hr>
Silahkan isi data diri dan juga komentar kalian yahh..
<hr> 
<form action="bukutamu_post.php" method="post"> 
<pre> 
Nama anda : <input type="text" name="nama" size="25" 
maxlength="50"> 
Email address : <input type="text" name="email" size="25" 
maxlength="50"> 
Komentar : <textarea name="komentar" cols="40" 
rows="5"> </textarea> 
<input type="submit" value="kirim"> 
<input type="reset" value="ulangi"> 
</pre> 
</form> 
</body> 
</html> 