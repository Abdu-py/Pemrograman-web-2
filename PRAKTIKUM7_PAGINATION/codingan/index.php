<?php include "koneksi.php";
echo '<h2 style="text-align: center;margin-top: 30px;">DAFTAR PRODUK</h2>';
?>
<link rel="stylesheet" href="style.css">

<a href="tambah.php" class="btn-add-product">Tambah Produk</a>
<br><br>

<table border="1" cellpadding="10" cellspacing="0">
    <tr>
        <th>ID</th>
        <th>Nama Produk</th>
        <th>Harga</th>
        <th>Stok</th>
        <th>Foto</th>
        <th>Aksi</th>
    </tr>

    <?php
    $data = mysqli_query($koneksi, "SELECT * FROM produk");
    while($row = mysqli_fetch_array($data)) {
    ?>
    <tr>
        <td><?= $row['id_produk']; ?></td>
        <td><?= $row['nama_produk']; ?></td>
        <td><?= number_format($row['harga']); ?></td>
        <td><?= $row['stok']; ?></td>
        <td><img src="upload/<?= $row['foto']; ?>" width="70"></td>
        <td>
            <a href="edit.php?id=<?= $row['id_produk']; ?>">Edit</a> |
            <a href="hapus.php?id=<?= $row['id_produk']; ?>" onclick="return confirm('Hapus produk?');">Hapus</a>
        </td>
    </tr>
    <?php } ?>
</table>
