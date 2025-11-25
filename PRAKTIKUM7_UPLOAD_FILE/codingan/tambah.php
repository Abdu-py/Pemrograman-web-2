<?php include "koneksi.php"; ?>
<link rel="stylesheet" href="style.css">

<h2>Tambah Produk</h2>

<form action="" method="POST" enctype="multipart/form-data">
    Nama Produk:<br>
    <input type="text" name="nama_produk" required><br><br>

    Harga:<br>
    <input type="number" name="harga" required><br><br>

    Stok:<br>
    <input type="number" name="stok" required><br><br>

    Foto Produk:<br>
    <input type="file" name="foto"><br><br>

    <button type="submit" name="simpan">Simpan</button>
</form>

<?php
if(isset($_POST['simpan'])){
    $nama = $_POST['nama_produk'];
    $harga = $_POST['harga'];
    $stok = $_POST['stok'];

    // Upload Foto
    $foto = $_FILES['foto']['name'];
    $tmp = $_FILES['foto']['tmp_name'];

    if($foto != ""){
        move_uploaded_file($tmp, "upload/".$foto);
    } else {
        $foto = "default.png";
    }

    mysqli_query($koneksi, "INSERT INTO produk (nama_produk, harga, stok, foto)
    VALUES ('$nama', '$harga', '$stok', '$foto')");

    header("location:index.php");
}
?>
