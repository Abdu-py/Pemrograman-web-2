<?php
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type");

include '../koneksi.php';

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {

    // ================= GET =================
    case 'GET':
        if (isset($_GET['id'])) {
            $id = $_GET['id'];
            $query = mysqli_query($koneksi, "SELECT * FROM santri WHERE id=$id");
            $data = mysqli_fetch_assoc($query);

            if ($data) {
                echo json_encode($data);
            } else {
                http_response_code(404);
                echo json_encode(["message" => "Data tidak ditemukan"]);
            }
        } else {
            $result = mysqli_query($koneksi, "SELECT * FROM santri");
            $data = [];
            while ($row = mysqli_fetch_assoc($result)) {
                $data[] = $row;
            }
            echo json_encode($data);
        }
        break;

    // ================= POST =================
    case 'POST':
        $input = json_decode(file_get_contents("php://input"), true);

        $nama   = $input['nama_santri'];
        $tempat = $input['tempat_lahir'];
        $tgl    = $input['tanggal_lahir'];
        $kelas  = $input['kelas'];
        $nilai  = $input['nilai_ujian'];

        $query = mysqli_query($koneksi, "INSERT INTO santri 
            (nama_santri, tempat_lahir, tanggal_lahir, kelas, nilai_ujian)
            VALUES ('$nama','$tempat','$tgl','$kelas','$nilai')");

        if ($query) {
            http_response_code(201);
            echo json_encode(["message" => "Data berhasil ditambahkan"]);
        } else {
            http_response_code(500);
            echo json_encode(["message" => "Gagal menambahkan data"]);
        }
        break;

    // ================= PUT =================
    case 'PUT':
        $input = json_decode(file_get_contents("php://input"), true);
        $id = $_GET['id'];

        $query = mysqli_query($koneksi, "UPDATE santri SET
            nama_santri='{$input['nama_santri']}',
            tempat_lahir='{$input['tempat_lahir']}',
            tanggal_lahir='{$input['tanggal_lahir']}',
            kelas='{$input['kelas']}',
            nilai_ujian='{$input['nilai_ujian']}'
            WHERE id=$id");

        if ($query) {
            echo json_encode(["message" => "Data berhasil diupdate"]);
        } else {
            http_response_code(500);
            echo json_encode(["message" => "Gagal update data"]);
        }
        break;

    // ================= DELETE =================
    case 'DELETE':
        $id = $_GET['id'];
        $query = mysqli_query($koneksi, "DELETE FROM santri WHERE id=$id");

        if ($query) {
            echo json_encode(["message" => "Data berhasil dihapus"]);
        } else {
            http_response_code(500);
            echo json_encode(["message" => "Gagal menghapus data"]);
        }
        break;

    default:
        http_response_code(405);
        echo json_encode(["message" => "Method tidak diizinkan"]);
}
