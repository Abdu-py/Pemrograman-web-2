<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'kepala_toko') {
    header('Location: ../auth/login.php');
    exit;
}

$user_name = $_SESSION['nama'];

// Filter
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-01');
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-d');
$status_filter = isset($_GET['status']) ? $_GET['status'] : 'all';

try {
    $conn = getConnection();
    
    // Query transaksi dengan filter
    $query = "
        SELECT 
            t.*,
            u.nama as nama_pelanggan,
            u.email as email_pelanggan
        FROM transaksi t
        JOIN users u ON t.id_user = u.id_user
        WHERE DATE(t.tanggal_transaksi) BETWEEN ? AND ?
    ";
    
    $params = [$start_date, $end_date];
    $types = "ss";
    
    if ($status_filter !== 'all') {
        $query .= " AND t.status = ?";
        $params[] = $status_filter;
        $types .= "s";
    }
    
    $query .= " ORDER BY t.tanggal_transaksi DESC";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $transaksi_list = $stmt->get_result();
    
    // Hitung statistik
    $stats_query = "
        SELECT 
            COUNT(*) as total_transaksi,
            SUM(total_bayar) as total_pendapatan,
            SUM(CASE WHEN status = 'selesai' THEN total_bayar ELSE 0 END) as pendapatan_selesai,
            SUM(CASE WHEN status = 'menunggu' THEN 1 ELSE 0 END) as transaksi_menunggu,
            SUM(CASE WHEN status = 'diproses' THEN 1 ELSE 0 END) as transaksi_diproses,
            SUM(CASE WHEN status = 'dikirim' THEN 1 ELSE 0 END) as transaksi_dikirim,
            SUM(CASE WHEN status = 'selesai' THEN 1 ELSE 0 END) as transaksi_selesai,
            SUM(CASE WHEN status = 'batal' THEN 1 ELSE 0 END) as transaksi_batal
        FROM transaksi
        WHERE DATE(tanggal_transaksi) BETWEEN ? AND ?
    ";
    
    $stmt = $conn->prepare($stats_query);
    $stmt->bind_param("ss", $start_date, $end_date);
    $stmt->execute();
    $stats = $stmt->get_result()->fetch_assoc();
    
} catch (Exception $e) {
    $error_message = $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Transaksi - Republik Computer</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { background: #f3f4f6; font-family: system-ui, sans-serif; display: flex; min-height: 100vh; }
        .sidebar { width: 260px; background: #1f2937; color: white; padding: 2rem 0; position: fixed; height: 100vh; overflow-y: auto; }
        .sidebar-brand { padding: 0 1.5rem; margin-bottom: 2rem; font-size: 1.5rem; font-weight: 700; color: white; text-decoration: none; display: block; }
        .sidebar-menu { list-style: none; padding: 0; }
        .sidebar-menu a { display: flex; align-items: center; gap: 1rem; padding: 0.75rem 1.5rem; color: #d1d5db; text-decoration: none; transition: all 0.3s; }
        .sidebar-menu a:hover, .sidebar-menu a.active { background: #374151; color: white; }
        .main-content { flex: 1; margin-left: 260px; padding: 2rem; }
        .top-bar { background: white; padding: 1.5rem; border-radius: 0.75rem; box-shadow: 0 2px 4px rgba(0,0,0,0.05); margin-bottom: 2rem; }
        .top-bar h1 { font-size: 1.875rem; color: #1f2937; margin-bottom: 0.5rem; }
        
        /* Stats Cards */
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; margin-bottom: 2rem; }
        .stat-card { background: white; padding: 1.5rem; border-radius: 0.75rem; box-shadow: 0 2px 4px rgba(0,0,0,0.05); }
        .stat-card h3 { color: #6b7280; font-size: 0.875rem; margin-bottom: 0.5rem; }
        .stat-card p { font-size: 1.5rem; font-weight: 700; color: #1f2937; }
        .stat-card.blue p { color: #2563eb; }
        .stat-card.green p { color: #10b981; }
        .stat-card.orange p { color: #f59e0b; }
        .stat-card.red p { color: #ef4444; }
        
        /* Filter Section */
        .filter-section { background: white; padding: 1.5rem; border-radius: 0.75rem; box-shadow: 0 2px 4px rgba(0,0,0,0.05); margin-bottom: 2rem; }
        .filter-form { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; }
        .form-group label { display: block; margin-bottom: 0.5rem; font-weight: 500; color: #374151; }
        .form-group input, .form-group select { width: 100%; padding: 0.75rem; border: 1px solid #d1d5db; border-radius: 0.5rem; }
        
        /* Content Section */
        .content-section { background: white; padding: 1.5rem; border-radius: 0.75rem; box-shadow: 0 2px 4px rgba(0,0,0,0.05); }
        .section-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; }
        .data-table { width: 100%; border-collapse: collapse; }
        .data-table th { background: #f9fafb; padding: 0.75rem; text-align: left; font-weight: 600; color: #374151; border-bottom: 2px solid #e5e7eb; }
        .data-table td { padding: 0.75rem; border-bottom: 1px solid #e5e7eb; }
        .data-table tr:hover { background: #f9fafb; }
        
        .status-badge { padding: 0.25rem 0.75rem; border-radius: 9999px; font-size: 0.875rem; font-weight: 500; }
        .status-menunggu { background: #fef3c7; color: #92400e; }
        .status-diproses { background: #dbeafe; color: #1e40af; }
        .status-dikirim { background: #bfdbfe; color: #1e3a8a; }
        .status-selesai { background: #d1fae5; color: #065f46; }
        .status-batal { background: #fee2e2; color: #991b1b; }
        
        .btn { padding: 0.5rem 1rem; border: none; border-radius: 0.5rem; font-weight: 600; cursor: pointer; text-decoration: none; display: inline-block; transition: all 0.3s; }
        .btn-primary { background: #2563eb; color: white; }
        .btn-primary:hover { background: #1e40af; }
        .btn-success { background: #10b981; color: white; }
        .btn-success:hover { background: #059669; }
    </style>
</head>
<body>
    <aside class="sidebar">
        <a href="dashboard.php" class="sidebar-brand"><i class="fas fa-laptop"></i> Republik Computer</a>
        <ul class="sidebar-menu">
            <li><a href="dashboard.php"><i class="fas fa-home"></i> Dashboard</a></li>
            <li><a href="laporan_transaksi.php" class="active"><i class="fas fa-file-invoice-dollar"></i> Laporan Transaksi</a></li>
            <li><a href="laporan_produk.php"><i class="fas fa-chart-line"></i> Laporan Produk</a></li>
            <li><a href="laporan_stok.php"><i class="fas fa-boxes"></i> Laporan Stok</a></li>
            <li><a href="../auth/logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
        </ul>
    </aside>

    <main class="main-content">
        <div class="top-bar">
            <h1><i class="fas fa-file-invoice-dollar"></i> Laporan Transaksi</h1>
            <p style="color: #6b7280;">Periode: <?php echo date('d M Y', strtotime($start_date)); ?> - <?php echo date('d M Y', strtotime($end_date)); ?></p>
        </div>

        <!-- Statistics Cards -->
        <div class="stats-grid">
            <div class="stat-card blue">
                <h3>Total Transaksi</h3>
                <p><?php echo number_format($stats['total_transaksi']); ?></p>
            </div>
            <div class="stat-card green">
                <h3>Total Pendapatan</h3>
                <p style="font-size: 1.25rem;">Rp <?php echo number_format($stats['total_pendapatan'], 0, ',', '.'); ?></p>
            </div>
            <div class="stat-card green">
                <h3>Pendapatan Selesai</h3>
                <p style="font-size: 1.25rem;">Rp <?php echo number_format($stats['pendapatan_selesai'], 0, ',', '.'); ?></p>
            </div>
            <div class="stat-card orange">
                <h3>Menunggu Pembayaran</h3>
                <p><?php echo $stats['transaksi_menunggu']; ?></p>
            </div>
            <div class="stat-card blue">
                <h3>Diproses</h3>
                <p><?php echo $stats['transaksi_diproses']; ?></p>
            </div>
            <div class="stat-card blue">
                <h3>Dikirim</h3>
                <p><?php echo $stats['transaksi_dikirim']; ?></p>
            </div>
            <div class="stat-card green">
                <h3>Selesai</h3>
                <p><?php echo $stats['transaksi_selesai']; ?></p>
            </div>
            <div class="stat-card red">
                <h3>Dibatalkan</h3>
                <p><?php echo $stats['transaksi_batal']; ?></p>
            </div>
        </div>

        <!-- Filter Section -->
        <div class="filter-section">
            <form method="GET" class="filter-form">
                <div class="form-group">
                    <label>Tanggal Mulai</label>
                    <input type="date" name="start_date" value="<?php echo $start_date; ?>">
                </div>
                <div class="form-group">
                    <label>Tanggal Akhir</label>
                    <input type="date" name="end_date" value="<?php echo $end_date; ?>">
                </div>
                <div class="form-group">
                    <label>Status</label>
                    <select name="status">
                        <option value="all" <?php echo $status_filter === 'all' ? 'selected' : ''; ?>>Semua Status</option>
                        <option value="menunggu" <?php echo $status_filter === 'menunggu' ? 'selected' : ''; ?>>Menunggu</option>
                        <option value="diproses" <?php echo $status_filter === 'diproses' ? 'selected' : ''; ?>>Diproses</option>
                        <option value="dikirim" <?php echo $status_filter === 'dikirim' ? 'selected' : ''; ?>>Dikirim</option>
                        <option value="selesai" <?php echo $status_filter === 'selesai' ? 'selected' : ''; ?>>Selesai</option>
                        <option value="batal" <?php echo $status_filter === 'batal' ? 'selected' : ''; ?>>Batal</option>
                    </select>
                </div>
                <div class="form-group" style="display: flex; align-items: flex-end;">
                    <button type="submit" class="btn btn-primary" style="width: 100%;">
                        <i class="fas fa-filter"></i> Filter
                    </button>
                </div>
            </form>
        </div>

        <!-- Transactions Table -->
        <div class="content-section">
            <div class="section-header">
                <h2>Daftar Transaksi</h2>
                <button onclick="printReport()" class="btn btn-success">
                    <i class="fas fa-print"></i> Cetak Laporan
                </button>
            </div>

            <div id="printable-area">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Kode</th>
                            <th>Tanggal</th>
                            <th>Pelanggan</th>
                            <th>Metode Bayar</th>
                            <th>Total</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($transaksi_list && $transaksi_list->num_rows > 0): ?>
                            <?php while ($t = $transaksi_list->fetch_assoc()): ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($t['kode_transaksi']); ?></strong></td>
                                <td><?php echo date('d/m/Y H:i', strtotime($t['tanggal_transaksi'])); ?></td>
                                <td><?php echo htmlspecialchars($t['nama_pelanggan']); ?></td>
                                <td><?php echo ucwords(str_replace('_', ' ', $t['metode_pembayaran'])); ?></td>
                                <td><strong>Rp <?php echo number_format($t['total_bayar'], 0, ',', '.'); ?></strong></td>
                                <td><span class="status-badge status-<?php echo $t['status']; ?>"><?php echo ucfirst($t['status']); ?></span></td>
                            </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr><td colspan="6" style="text-align: center; padding: 2rem;">Tidak ada data transaksi</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <script>
        function printReport() {
            window.print();
        }
    </script>

    <?php
    if (isset($stmt)) $stmt->close();
    if (isset($conn)) $conn->close();
    ?>
</body>
</html>