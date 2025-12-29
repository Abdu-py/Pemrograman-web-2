<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dashboard Pondok</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
  <style>
    * {
      margin: 0; padding: 0; box-sizing: border-box;
    }
    body {
      font-family: 'Poppins', sans-serif;
      background: #f5f7fa;
      color: #333;
      display: flex;
      height: 100vh;
    }
    /* Sidebar */
    .sidebar {
      width: 240px;
      background: #0066ff;
      color: white;
      display: flex;
      flex-direction: column;
      justify-content: space-between;
      padding: 20px;
    }
    .sidebar h2 {
      font-size: 22px;
      text-align: center;
      margin-bottom: 30px;
    }
    .menu a {
      display: block;
      color: white;
      text-decoration: none;
      padding: 12px 15px;
      margin: 5px 0;
      border-radius: 8px;
      transition: 0.3s;
      font-weight: 500;
    }
    .menu a:hover {
      background: rgba(255,255,255,0.2);
    }
    .logout {
      text-align: center;
      font-size: 13px;
      opacity: 0.8;
    }

    /* Konten utama */
    .content {
      flex: 1;
      padding: 40px;
    }
    header {
      background: white;
      padding: 20px 30px;
      border-radius: 12px;
      box-shadow: 0 3px 10px rgba(0,0,0,0.1);
      display: flex;
      justify-content: space-between;
      align-items: center;
    }
    header h1 {
      font-size: 22px;
      color: #333;
    }
    .card-container {
      margin-top: 40px;
      display: flex;
      flex-wrap: wrap;
      gap: 20px;
    }
    .card {
      background: white;
      flex: 1 1 250px;
      padding: 30px;
      border-radius: 15px;
      box-shadow: 0 5px 15px rgba(0,0,0,0.08);
      transition: 0.3s;
      text-align: center;
    }
    .card:hover {
      transform: translateY(-5px);
    }
    .card h3 {
      margin-bottom: 10px;
      color: #0066ff;
    }
    .card p {
      color: #666;
      font-size: 14px;
    }
    .btn {
      display: inline-block;
      background: #0066ff;
      color: white;
      padding: 10px 18px;
      border-radius: 8px;
      text-decoration: none;
      margin-top: 15px;
      transition: 0.3s;
      font-weight: 500;
    }
    .btn:hover {
      background: #004bcc;
    }
  </style>
</head>
<body>
  <div class="sidebar">
    <div>
      <h2>🕌 Pondok Admin</h2>
      <div class="menu">
        <a href="index.php">🏠 Dashboard</a>
        <a href="tampil_santri.php">👨‍🎓 Data Santri</a>
      </div>
    </div>
    <div class="logout">
      &copy; <?= date('Y'); ?> Pondok Dashboard
    </div>
  </div>

  <div class="content">
    <header>
      <h1>Selamat Datang di Dashboard Pondok</h1>
      <span>Admin Panel</span>
    </header>

    <div class="card-container">
      <div class="card">
        <h3>Data Santri</h3>
        <p>Lihat, tambah, ubah, atau hapus data santri pondok.</p>
        <a href="tampil_santri.php" class="btn">Buka</a>
      </div>

      <div class="card">
        <h3>Profil Pondok</h3>
        <p>Informasi singkat tentang lembaga dan kegiatan pondok.</p>
        <a href="#" class="btn">Lihat</a>
      </div>

      <div class="card">
        <h3>Laporan</h3>
        <p>Unduh laporan data dan hasil evaluasi santri.</p>
        <a href="#" class="btn">Lihat</a>
      </div>
    </div>
  </div>
</body>
</html>
