<?php include "koneksi.php"; ?>
<link rel="stylesheet" href="style.css">

<?php
$id = $_GET['id'];
$data = mysqli_query($koneksi, "SELECT * FROM produk WHERE id_produk='$id'");
$row = mysqli_fetch_assoc($data);
?>

<h2>Edit Produk</h2>

<form action="" method="POST" enctype="multipart/form-data">
    Nama Produk:<br>
    <input type="text" name="nama_produk" value="<?= $row['nama_produk']; ?>"><br><br>

    Harga:<br>
    <input type="number" name="harga" value="<?= $row['harga']; ?>"><br><br>

    Stok:<br>
    <input type="number" name="stok" value="<?= $row['stok']; ?>"><br><br>

    Foto Lama: <br>
    <img src="upload/<?= $row['foto']; ?>" width="100"><br><br>

    Ganti Foto (opsional):<br>
    <input type="file" name="foto"><br><br>

    <button type="submit" name="update">Update</button>
</form>

<?php
if(isset($_POST['update'])){
    $nama = $_POST['nama_produk'];
    $harga = $_POST['harga'];
    $stok = $_POST['stok'];

    $foto_baru = $_FILES['foto']['name'];
    $tmp = $_FILES['foto']['tmp_name'];

    if($foto_baru != ""){
        move_uploaded_file($tmp, "upload/".$foto_baru);
        $foto_final = $foto_baru;
    } else {
        $foto_final = $row['foto'];
    }

    mysqli_query($koneksi, "
        UPDATE produk SET 
        nama_produk='$nama',
        harga='$harga',
        stok='$stok',
        foto='$foto_final'
        WHERE id_produk='$id'
    ");

    header("location:index.php");
}
?>
