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

try {
    $conn = getConnection();
    
    // Produk Terlaris
    $stmt = $conn->prepare("
        SELECT 
            p.id_produk,
            p.nama_produk,
            p.harga,
            p.stok,
            COALESCE(SUM(dt.qty), 0) as total_terjual,
            COALESCE(SUM(dt.subtotal), 0) as total_pendapatan,
            COUNT(DISTINCT dt.id_transaksi) as jumlah_transaksi
        FROM produk p
        LEFT JOIN detail_transaksi dt ON p.id_produk = dt.id_produk
        LEFT JOIN transaksi t ON dt.id_transaksi = t.id_transaksi
        WHERE t.tanggal_transaksi BETWEEN ? AND ?
        OR t.tanggal_transaksi IS NULL
        GROUP BY p.id_produk
        ORDER BY total_terjual DESC
    ");
    
    $stmt->bind_param("ss", $start_date, $end_date);
    $stmt->execute();
    $produk_terlaris = $stmt->get_result();
    
    // Statistik
    $stmt = $conn->prepare("
        SELECT 
            COUNT(DISTINCT p.id_produk) as total_produk,
            COALESCE(SUM(dt.qty), 0) as total_item_terjual,
            COALESCE(SUM(dt.subtotal), 0) as total_revenue
        FROM produk p
        LEFT JOIN detail_transaksi dt ON p.id_produk = dt.id_produk
        LEFT JOIN transaksi t ON dt.id_transaksi = t.id_transaksi
        WHERE t.tanggal_transaksi BETWEEN ? AND ?
        AND t.status = 'selesai'
    ");
    
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
    <title>Laporan Produk - Republik Computer</title>
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
        
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1rem; margin-bottom: 2rem; }
        .stat-card { background: white; padding: 1.5rem; border-radius: 0.75rem; box-shadow: 0 2px 4px rgba(0,0,0,0.05); }
        .stat-card h3 { color: #6b7280; font-size: 0.875rem; margin-bottom: 0.5rem; }
        .stat-card p { font-size: 1.5rem; font-weight: 700; color: #2563eb; }
        
        .filter-section { background: white; padding: 1.5rem; border-radius: 0.75rem; box-shadow: 0 2px 4px rgba(0,0,0,0.05); margin-bottom: 2rem; }
        .filter-form { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; }
        .form-group label { display: block; margin-bottom: 0.5rem; font-weight: 500; }
        .form-group input { width: 100%; padding: 0.75rem; border: 1px solid #d1d5db; border-radius: 0.5rem; }
        
        .content-section { background: white; padding: 1.5rem; border-radius: 0.75rem; box-shadow: 0 2px 4px rgba(0,0,0,0.05); }
        .section-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; }
        .data-table { width: 100%; border-collapse: collapse; }
        .data-table th { background: #f9fafb; padding: 0.75rem; text-align: left; font-weight: 600; border-bottom: 2px solid #e5e7eb; }
        .data-table td { padding: 0.75rem; border-bottom: 1px solid #e5e7eb; }
        .data-table tr:hover { background: #f9fafb; }
        
        .rank-badge { width: 30px; height: 30px; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; font-weight: 700; color: white; }
        .rank-1 { background: #f59e0b; }
        .rank-2 { background: #9ca3af; }
        .rank-3 { background: #cd7f32; }
        .rank-other { background: #6b7280; }
        
        .btn { padding: 0.5rem 1rem; border: none; border-radius: 0.5rem; font-weight: 600; cursor: pointer; text-decoration: none; display: inline-block; }
        .btn-primary { background: #2563eb; color: white; }
        .btn-success { background: #10b981; color: white; }
    </style>
</head>
<body>
    <aside class="sidebar">
        <a href="dashboard.php" class="sidebar-brand"><i class="fas fa-laptop"></i> Republik Computer</a>
        <ul class="sidebar-menu">
            <li><a href="dashboard.php"><i class="fas fa-home"></i> Dashboard</a></li>
            <li><a href="laporan_transaksi.php"><i class="fas fa-file-invoice-dollar"></i> Laporan Transaksi</a></li>
            <li><a href="laporan_produk.php" class="active"><i class="fas fa-chart-line"></i> Laporan Produk</a></li>
            <li><a href="laporan_stok.php"><i class="fas fa-boxes"></i> Laporan Stok</a></li>
            <li><a href="../auth/logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
        </ul>
    </aside>

    <main class="main-content">
        <div class="top-bar">
            <h1><i class="fas fa-chart-line"></i> Laporan Produk Terlaris</h1>
            <p style="color: #6b7280;">Periode: <?php echo date('d M Y', strtotime($start_date)); ?> - <?php echo date('d M Y', strtotime($end_date)); ?></p>
        </div>

        <div class="stats-grid">
            <div class="stat-card">
                <h3>Total Produk</h3>
                <p><?php echo number_format($stats['total_produk']); ?></p>
            </div>
            <div class="stat-card">
                <h3>Total Item Terjual</h3>
                <p><?php echo number_format($stats['total_item_terjual']); ?></p>
            </div>
            <div class="stat-card">
                <h3>Total Revenue Produk</h3>
                <p style="font-size: 1.25rem;">Rp <?php echo number_format($stats['total_revenue'], 0, ',', '.'); ?></p>
            </div>
        </div>

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
                <div class="form-group" style="display: flex; align-items: flex-end;">
                    <button type="submit" class="btn btn-primary" style="width: 100%;">
                        <i class="fas fa-filter"></i> Filter
                    </button>
                </div>
            </form>
        </div>

        <div class="content-section">
            <div class="section-header">
                <h2>Produk Terlaris</h2>
                <button onclick="window.print()" class="btn btn-success">
                    <i class="fas fa-print"></i> Cetak
                </button>
            </div>

            <table class="data-table">
                <thead>
                    <tr>
                        <th>Rank</th>
                        <th>Nama Produk</th>
                        <th>Harga</th>
                        <th>Stok Tersisa</th>
                        <th>Qty Terjual</th>
                        <th>Total Pendapatan</th>
                        <th>Jumlah Transaksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    if ($produk_terlaris && $produk_terlaris->num_rows > 0):
                        $rank = 1;
                        while ($p = $produk_terlaris->fetch_assoc()): 
                            $rank_class = $rank <= 3 ? "rank-$rank" : "rank-other";
                    ?>
                    <tr>
                        <td>
                            <span class="rank-badge <?php echo $rank_class; ?>"><?php echo $rank; ?></span>
                        </td>
                        <td><strong><?php echo htmlspecialchars($p['nama_produk']); ?></strong></td>
                        <td>Rp <?php echo number_format($p['harga'], 0, ',', '.'); ?></td>
                        <td><?php echo $p['stok']; ?> pcs</td>
                        <td><strong style="color: #2563eb;"><?php echo $p['total_terjual']; ?> pcs</strong></td>
                        <td><strong style="color: #10b981;">Rp <?php echo number_format($p['total_pendapatan'], 0, ',', '.'); ?></strong></td>
                        <td><?php echo $p['jumlah_transaksi']; ?> transaksi</td>
                    </tr>
                    <?php 
                            $rank++;
                        endwhile;
                    else: 
                    ?>
                    <tr><td colspan="7" style="text-align: center; padding: 2rem;">Tidak ada data produk</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>

    <?php
    if (isset($stmt)) $stmt->close();
    if (isset($conn)) $conn->close();
    ?>
</body>
</html>