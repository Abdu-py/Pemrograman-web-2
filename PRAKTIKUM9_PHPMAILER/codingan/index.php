<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Alert Notification PHPMailer</title>

    <!-- Bootstrap CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body {
            background: #f0f2f5;
        }
        .form-container {
            max-width: 550px;
            margin: 40px auto;
            background: white;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
        }
        .title {
            font-weight: 700;
        }
    </style>
</head>
<body>

<div class="form-container">
    <h3 class="text-center title mb-3">FORM ALERT NOTIFICATION</h3>
    <p class="text-center text-muted mb-4">Mengirim notifikasi menggunakan PHPMailer</p>

    <form action="proses.php" method="POST">

        <div class="mb-3">
            <label class="form-label">Nama Lengkap</label>
            <input type="text" name="nama" class="form-control" placeholder="Masukkan nama lengkap" required>
        </div>

        <div class="mb-3">
            <label class="form-label">NIM</label>
            <input type="text" name="nim" class="form-control" placeholder="Masukkan NIM" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Kelas</label>
            <input type="text" name="kelas" class="form-control" placeholder="Masukkan kelas" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Program Studi</label>
            <input type="text" name="prodi" class="form-control" placeholder="Masukkan Program Studi" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Universitas</label>
            <input type="text" name="universitas" class="form-control" placeholder="Nama Universitas" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Email Tujuan</label>
            <input type="email" name="email" class="form-control" placeholder="Masukkan email yang menerima notifikasi" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Pesan (Opsional)</label>
            <textarea name="pesan" class="form-control" rows="4" placeholder="Tulis pesan jika ada..."></textarea>
        </div>

        <button type="submit" name="kirim" class="btn btn-primary w-100">
            Kirim Notifikasi
        </button>

    </form>
</div>

</body>
</html>
