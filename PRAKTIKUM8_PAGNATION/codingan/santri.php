<?php
include 'koneksi.php';

// jumlah data per halaman
$batas = 5;

// halaman aktif
$halaman = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$halaman_awal = ($halaman - 1) * $batas;

// ambil data santri sesuai halaman
$query = mysqli_query(
    $conn,
    "SELECT * FROM santri LIMIT $halaman_awal, $batas"
);

// hitung total data
$total_data = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM santri"));
$total_halaman = ceil($total_data / $batas);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Data Santri</title>
    <style>
        body {
            font-family: Arial;
        }
        table {
            width: 80%;
            border-collapse: collapse;
        }
        th, td {
            border: 1px solid #555;
            padding: 8px;
            text-align: center;
        }
        .pagination a {
            padding: 6px 10px;
            margin: 2px;
            border: 1px solid #333;
            text-decoration: none;
            color: black;
        }
        .pagination .active {
            background: #333;
            color: white;
        }
    </style>
</head>
<body>

<h2>Data Santri</h2>

<table>
    <tr>
        <th>No</th>
        <th>Nama</th>
        <th>Alamat</th>
        <th>Kelas</th>
    </tr>

    <?php
    $no = $halaman_awal + 1;
    while ($data = mysqli_fetch_assoc($query)) {
    ?>
    <tr>
        <td><?= $no++; ?></td>
        <td><?= $data['nama']; ?></td>
        <td><?= $data['alamat']; ?></td>
        <td><?= $data['kelas']; ?></td>
    </tr>
    <?php } ?>
</table>

<br>

<div class="pagination">
<?php
for ($i = 1; $i <= $total_halaman; $i++) {
    if ($i == $halaman) {
        echo "<a class='active' href='?page=$i'>$i</a>";
    } else {
        echo "<a href='?page=$i'>$i</a>";
    }
}
?>
</div>

</body>
</html>
