<html> 
<head> 
<title>Form untuk input nama file</title> 
</head> 
<body> 
<h1>Input nama file untuk Upload</h1> 
<br>Klik Browse untuk memilih ! 
<form enctype="multipart/form-data" method="post" 
action="do_upload.php"> 
<input type="hidden" name="MAX_FILE_SIZE" value="100000"> 
Nama File : <input type="file" name="file1" size="300000"> 
<br> 
<input type="submit" value="Upload"> 
</form> 
</body> 
</html> 