<?php
/**
 * FILE: admin/dashboard.php
 * DESKRIPSI: Dashboard untuk Admin dan Kepala Toko dengan Role-Based Access
 * LOKASI: republik-computer/admin/dashboard.php
 */

session_start();
require_once '../config/database.php';

// Cek apakah sudah login
if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.php');
    exit;
}

// Cek role (hanya admin dan kepala toko)
if ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'kepala_toko') {
    header('Location: ../user/beranda.php');
    exit;
}

$user_name = $_SESSION['nama'];
$user_role = $_SESSION['role'];

// Ambil statistik
try {
    $conn = getConnection();
    
    // Total Produk
    $stmt = $conn->prepare("SELECT COUNT(*) as total FROM produk");
    $stmt->execute();
    $total_produk = $stmt->get_result()->fetch_assoc()['total'];
    
    // Total Pelanggan
    $stmt = $conn->prepare("SELECT COUNT(*) as total FROM users WHERE role = 'pelanggan'");
    $stmt->execute();
    $total_pelanggan = $stmt->get_result()->fetch_assoc()['total'];
    
    // Total Transaksi Hari Ini
    $stmt = $conn->prepare("SELECT COUNT(*) as total FROM transaksi WHERE DATE(tanggal_transaksi) = CURDATE()");
    $stmt->execute();
    $transaksi_hari_ini = $stmt->get_result()->fetch_assoc()['total'];
    
    // Total Pendapatan Bulan Ini
    $stmt = $conn->prepare("
        SELECT COALESCE(SUM(total_bayar), 0) as total 
        FROM transaksi 
        WHERE MONTH(tanggal_transaksi) = MONTH(CURDATE()) 
        AND YEAR(tanggal_transaksi) = YEAR(CURDATE())
        AND status = 'selesai'
    ");
    $stmt->execute();
    $pendapatan_bulan_ini = $stmt->get_result()->fetch_assoc()['total'];
    
    // Transaksi Terbaru
    $stmt = $conn->prepare("
        SELECT t.*, u.nama as nama_pelanggan
        FROM transaksi t
        JOIN users u ON t.id_user = u.id_user
        ORDER BY t.tanggal_transaksi DESC
        LIMIT 10
    ");
    $stmt->execute();
    $transaksi_terbaru = $stmt->get_result();
    
    // Produk Stok Menipis (< 5)
    $stmt = $conn->prepare("
        SELECT * FROM produk 
        WHERE stok < 5 
        ORDER BY stok ASC
        LIMIT 10
    ");
    $stmt->execute();
    $produk_stok_menipis = $stmt->get_result();
    
} catch (Exception $e) {
    $error_message = $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Republik Computer</title>
    
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        body {
            background: #f3f4f6;
            display: flex;
            min-height: 100vh;
            margin: 0;
            font-family: system-ui, -apple-system, sans-serif;
        }
        
        /* Sidebar */
        .sidebar {
            width: 260px;
            background: #1f2937;
            color: white;
            padding: 2rem 0;
            position: fixed;
            height: 100vh;
            overflow-y: auto;
        }
        
        .sidebar-brand {
            padding: 0 1.5rem;
            margin-bottom: 2rem;
            font-size: 1.5rem;
            font-weight: 700;
            color: white;
            text-decoration: none;
            display: block;
        }
        
        .sidebar-menu {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        
        .sidebar-menu li {
            margin-bottom: 0.5rem;
        }
        
        .sidebar-menu a {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 0.75rem 1.5rem;
            color: #d1d5db;
            text-decoration: none;
            transition: all 0.3s;
        }
        
        .sidebar-menu a:hover,
        .sidebar-menu a.active {
            background: #374151;
            color: white;
        }
        
        .sidebar-menu i {
            width: 20px;
            text-align: center;
        }
        
        /* Main Content */
        .main-content {
            flex: 1;
            margin-left: 260px;
            padding: 2rem;
        }
        
        /* Top Bar */
        .top-bar {
            background: white;
            padding: 1.5rem;
            border-radius: 0.75rem;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
            margin-bottom: 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .user-info {
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        
        .role-badge {
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 600;
        }
        
        .role-admin {
            background: #dbeafe;
            color: #1e40af;
        }
        
        .role-kepala {
            background: #dcfce7;
            color: #166534;
        }
        
        /* Stats Cards */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        
        .stat-card {
            background: white;
            padding: 1.5rem;
            border-radius: 0.75rem;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .stat-info h3 {
            color: #6b7280;
            font-size: 0.875rem;
            font-weight: 500;
            margin-bottom: 0.5rem;
        }
        
        .stat-info p {
            font-size: 2rem;
            font-weight: 700;
            color: #1f2937;
            margin: 0;
        }
        
        .stat-icon {
            width: 60px;
            height: 60px;
            border-radius: 0.75rem;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
        }
        
        .stat-icon.blue { background: #dbeafe; color: #2563eb; }
        .stat-icon.green { background: #d1fae5; color: #10b981; }
        .stat-icon.orange { background: #fed7aa; color: #f59e0b; }
        .stat-icon.purple { background: #e9d5ff; color: #8b5cf6; }
        
        /* Content Sections */
        .content-section {
            background: white;
            padding: 1.5rem;
            border-radius: 0.75rem;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
            margin-bottom: 2rem;
        }
        
        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid #f3f4f6;
        }
        
        .section-header h2 {
            font-size: 1.25rem;
            color: #1f2937;
            margin: 0;
        }
        
        /* Table */
        .data-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .data-table th {
            background: #f9fafb;
            padding: 0.75rem;
            text-align: left;
            font-weight: 600;
            color: #374151;
            border-bottom: 2px solid #e5e7eb;
        }
        
        .data-table td {
            padding: 0.75rem;
            border-bottom: 1px solid #e5e7eb;
        }
        
        .data-table tr:hover {
            background: #f9fafb;
        }
        
        .status-badge {
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            font-size: 0.875rem;
            font-weight: 500;
        }
        
        .status-menunggu { background: #fef3c7; color: #92400e; }
        .status-diproses { background: #dbeafe; color: #1e40af; }
        .status-dikirim { background: #bfdbfe; color: #1e3a8a; }
        .status-selesai { background: #d1fae5; color: #065f46; }
        .status-batal { background: #fee2e2; color: #991b1b; }
        
        .alert-warning {
            background: #fef3c7;
            color: #92400e;
            padding: 1rem;
            border-radius: 0.5rem;
            border-left: 4px solid #f59e0b;
            margin-bottom: 1rem;
        }
        
        .btn {
            padding: 0.5rem 1rem;
            border-radius: 0.5rem;
            text-decoration: none;
            display: inline-block;
            font-weight: 600;
            transition: all 0.3s;
        }
        
        .btn-primary {
            background: #2563eb;
            color: white;
        }
        
        .btn-primary:hover {
            background: #1e40af;
        }
        
        @media (max-width: 768px) {
            .sidebar {
                width: 0;
                padding: 0;
            }
            
            .main-content {
                margin-left: 0;
            }
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <aside class="sidebar">
        <a href="dashboard.php" class="sidebar-brand">
            <i class="fas fa-laptop"></i> Republik Computer
        </a>
        
        <ul class="sidebar-menu">
            <li>
                <a href="dashboard.php" class="active">
                    <i class="fas fa-home"></i>
                    <span>Dashboard</span>
                </a>
            </li>
            
            <?php if ($user_role === 'admin'): ?>
            <!-- Menu khusus Admin -->
            <li>
                <a href="produk.php">
                    <i class="fas fa-box"></i>
                    <span>Kelola Produk</span>
                </a>
            </li>
            <li>
                <a href="kategori.php">
                    <i class="fas fa-tags"></i>
                    <span>Kelola Kategori</span>
                </a>
            </li>
            <li>
                <a href="edit_stok.php">
                    <i class="fas fa-warehouse"></i>
                    <span>Edit Stok Produk</span>
                </a>
            </li>
            <li>
                <a href="transaksi.php">
                    <i class="fas fa-shopping-cart"></i>
                    <span>Transaksi</span>
                </a>
            </li>
            <li>
                <a href="pengguna.php">
                    <i class="fas fa-users"></i>
                    <span>Kelola Pengguna</span>
                </a>
            </li>
            <?php endif; ?>
            
            <?php if ($user_role === 'kepala_toko'): ?>
            <!-- Menu khusus Kepala Toko -->
            <li>
                <a href="laporan_transaksi.php">
                    <i class="fas fa-file-invoice-dollar"></i>
                    <span>Laporan Transaksi</span>
                </a>
            </li>
            <li>
                <a href="laporan_produk.php">
                    <i class="fas fa-chart-line"></i>
                    <span>Laporan Produk</span>
                </a>
            </li>
            <li>
                <a href="laporan_stok.php">
                    <i class="fas fa-boxes"></i>
                    <span>Laporan Stok</span>
                </a>
            </li>
            <?php endif; ?>
            
            <li>
                <a href="../auth/logout.php">
                    <i class="fas fa-sign-out-alt"></i>
                    <span>Logout</span>
                </a>
            </li>
        </ul>
    </aside>

    <!-- Main Content -->
    <main class="main-content">
        <!-- Top Bar -->
        <div class="top-bar">
            <div>
                <h1>Dashboard</h1>
                <p style="color: #6b7280; margin: 0;">Selamat datang kembali, <?php echo htmlspecialchars($user_name); ?></p>
            </div>
            <div class="user-info">
                <div style="text-align: right;">
                    <p style="font-weight: 600; margin: 0 0 0.25rem 0;"><?php echo htmlspecialchars($user_name); ?></p>
                    <span class="role-badge <?php echo $user_role === 'admin' ? 'role-admin' : 'role-kepala'; ?>">
                        <?php echo $user_role === 'admin' ? 'Administrator' : 'Kepala Toko'; ?>
                    </span>
                </div>
                <i class="fas fa-user-circle" style="font-size: 2.5rem; color: #2563eb;"></i>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-info">
                    <h3>Total Produk</h3>
                    <p><?php echo $total_produk; ?></p>
                </div>
                <div class="stat-icon blue">
                    <i class="fas fa-box"></i>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-info">
                    <h3>Total Pelanggan</h3>
                    <p><?php echo $total_pelanggan; ?></p>
                </div>
                <div class="stat-icon green">
                    <i class="fas fa-users"></i>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-info">
                    <h3>Transaksi Hari Ini</h3>
                    <p><?php echo $transaksi_hari_ini; ?></p>
                </div>
                <div class="stat-icon orange">
                    <i class="fas fa-shopping-cart"></i>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-info">
                    <h3>Pendapatan Bulan Ini</h3>
                    <p style="font-size: 1.25rem;">Rp <?php echo number_format($pendapatan_bulan_ini, 0, ',', '.'); ?></p>
                </div>
                <div class="stat-icon purple">
                    <i class="fas fa-dollar-sign"></i>
                </div>
            </div>
        </div>

        <!-- Produk Stok Menipis -->
        <?php if ($produk_stok_menipis && $produk_stok_menipis->num_rows > 0): ?>
        <div class="content-section">
            <div class="alert-warning">
                <i class="fas fa-exclamation-triangle"></i>
                <strong>Peringatan!</strong> Ada <?php echo $produk_stok_menipis->num_rows; ?> produk dengan stok menipis (< 5)
            </div>
            
            <div class="section-header">
                <h2><i class="fas fa-exclamation-circle"></i> Produk Stok Menipis</h2>
                <?php if ($user_role === 'admin'): ?>
                <a href="edit_stok.php" class="btn btn-primary">Edit Stok</a>
                <?php endif; ?>
            </div>
            
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Nama Produk</th>
                        <th>Stok</th>
                        <th>Harga</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($produk = $produk_stok_menipis->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($produk['nama_produk']); ?></td>
                        <td><strong style="color: #ef4444;"><?php echo $produk['stok']; ?></strong></td>
                        <td>Rp <?php echo number_format($produk['harga'], 0, ',', '.'); ?></td>
                        <td>
                            <span class="status-badge status-menunggu">
                                Stok Menipis
                            </span>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>

        <!-- Transaksi Terbaru -->
        <div class="content-section">
            <div class="section-header">
                <h2><i class="fas fa-clock"></i> Transaksi Terbaru</h2>
                <?php if ($user_role === 'kepala_toko'): ?>
                <a href="laporan_transaksi.php" class="btn btn-primary">Lihat Laporan</a>
                <?php else: ?>
                <a href="transaksi.php" class="btn btn-primary">Lihat Semua</a>
                <?php endif; ?>
            </div>
            
            <?php if ($transaksi_terbaru && $transaksi_terbaru->num_rows > 0): ?>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Kode Transaksi</th>
                        <th>Pelanggan</th>
                        <th>Tanggal</th>
                        <th>Total</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($transaksi = $transaksi_terbaru->fetch_assoc()): ?>
                    <tr>
                        <td><strong><?php echo htmlspecialchars($transaksi['kode_transaksi']); ?></strong></td>
                        <td><?php echo htmlspecialchars($transaksi['nama_pelanggan']); ?></td>
                        <td><?php echo date('d/m/Y H:i', strtotime($transaksi['tanggal_transaksi'])); ?></td>
                        <td><strong>Rp <?php echo number_format($transaksi['total_bayar'], 0, ',', '.'); ?></strong></td>
                        <td>
                            <span class="status-badge status-<?php echo $transaksi['status']; ?>">
                                <?php echo ucfirst($transaksi['status']); ?>
                            </span>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
            <?php else: ?>
            <p style="text-align: center; padding: 2rem; color: #6b7280;">Belum ada transaksi</p>
            <?php endif; ?>
        </div>
    </main>

    <?php
    if (isset($stmt)) $stmt->close();
    if (isset($conn)) $conn->close();
    ?>
</body>
</html>