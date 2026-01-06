<?php
/**
 * FILE: admin/produk.php
 * DESKRIPSI: Kelola Produk Lengkap (CRUD, Kelola Stok, Kelola Harga)
 * LOKASI: republik-computer/admin/produk.php
 */

session_start();
require_once '../config/database.php';

// Cek login dan role
if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.php');
    exit;
}

if ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'kepala_toko') {
    header('Location: ../user/beranda.php');
    exit;
}

$user_name = $_SESSION['nama'];
$user_role = $_SESSION['role'];

// Handle Actions (Create, Update, Delete, Update Stok, Update Harga)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $conn = getConnection();
        
        if (isset($_POST['action'])) {
            switch ($_POST['action']) {
                case 'create':
                    $nama_produk = cleanInput($_POST['nama_produk']);
                    $id_kategori = intval($_POST['id_kategori']);
                    $deskripsi = cleanInput($_POST['deskripsi']);
                    $harga = floatval($_POST['harga']);
                    $stok = intval($_POST['stok']);
                    $status = cleanInput($_POST['status']);
                    
                    $stmt = $conn->prepare("
                        INSERT INTO produk (id_kategori, nama_produk, deskripsi, harga, stok, status) 
                        VALUES (?, ?, ?, ?, ?, ?)
                    ");
                    $stmt->bind_param("issdis", $id_kategori, $nama_produk, $deskripsi, $harga, $stok, $status);
                    $stmt->execute();
                    
                    $_SESSION['success'] = "Produk berhasil ditambahkan!";
                    break;
                
                case 'update':
                    $id_produk = intval($_POST['id_produk']);
                    $nama_produk = cleanInput($_POST['nama_produk']);
                    $id_kategori = intval($_POST['id_kategori']);
                    $deskripsi = cleanInput($_POST['deskripsi']);
                    $harga = floatval($_POST['harga']);
                    $stok = intval($_POST['stok']);
                    $status = cleanInput($_POST['status']);
                    
                    $stmt = $conn->prepare("
                        UPDATE produk 
                        SET nama_produk = ?, id_kategori = ?, deskripsi = ?, harga = ?, stok = ?, status = ?
                        WHERE id_produk = ?
                    ");
                    $stmt->bind_param("sisdiis", $nama_produk, $id_kategori, $deskripsi, $harga, $stok, $status, $id_produk);
                    $stmt->execute();
                    
                    $_SESSION['success'] = "Produk berhasil diupdate!";
                    break;
                
                case 'delete':
                    $id_produk = intval($_POST['id_produk']);
                    $stmt = $conn->prepare("DELETE FROM produk WHERE id_produk = ?");
                    $stmt->bind_param("i", $id_produk);
                    $stmt->execute();
                    
                    $_SESSION['success'] = "Produk berhasil dihapus!";
                    break;
                
                case 'update_stok':
                    $id_produk = intval($_POST['id_produk']);
                    $stok = intval($_POST['stok']);
                    
                    $status = $stok > 0 ? 'tersedia' : 'habis';
                    
                    $stmt = $conn->prepare("UPDATE produk SET stok = ?, status = ? WHERE id_produk = ?");
                    $stmt->bind_param("isi", $stok, $status, $id_produk);
                    $stmt->execute();
                    
                    $_SESSION['success'] = "Stok berhasil diupdate!";
                    break;
                
                case 'update_harga':
                    $id_produk = intval($_POST['id_produk']);
                    $harga = floatval($_POST['harga']);
                    
                    $stmt = $conn->prepare("UPDATE produk SET harga = ? WHERE id_produk = ?");
                    $stmt->bind_param("di", $harga, $id_produk);
                    $stmt->execute();
                    
                    $_SESSION['success'] = "Harga berhasil diupdate!";
                    break;
                
                case 'bulk_update_status':
                    $status = cleanInput($_POST['status']);
                    $selected = $_POST['selected'] ?? [];
                    
                    if (!empty($selected)) {
                        $placeholders = str_repeat('?,', count($selected) - 1) . '?';
                        $stmt = $conn->prepare("UPDATE produk SET status = ? WHERE id_produk IN ($placeholders)");
                        $types = str_repeat('i', count($selected));
                        $stmt->bind_param("s$types", $status, ...$selected);
                        $stmt->execute();
                        
                        $_SESSION['success'] = count($selected) . " produk berhasil diupdate!";
                    }
                    break;
            }
        }
        
        if (isset($stmt)) $stmt->close();
        if (isset($conn)) $conn->close();
        
        header('Location: produk.php');
        exit;
        
    } catch (Exception $e) {
        $_SESSION['error'] = "Error: " . $e->getMessage();
        header('Location: produk.php');
        exit;
    }
}

// Ambil data produk dengan filter
try {
    $conn = getConnection();
    
    // Filter
    $filter_kategori = isset($_GET['kategori']) ? intval($_GET['kategori']) : 0;
    $filter_status = isset($_GET['status']) ? cleanInput($_GET['status']) : '';
    $search = isset($_GET['search']) ? cleanInput($_GET['search']) : '';
    
    $query = "
        SELECT p.*, k.nama_kategori 
        FROM produk p
        LEFT JOIN kategori k ON p.id_kategori = k.id_kategori
        WHERE 1=1
    ";
    
    $params = [];
    $types = '';
    
    if ($filter_kategori > 0) {
        $query .= " AND p.id_kategori = ?";
        $params[] = $filter_kategori;
        $types .= 'i';
    }
    
    if (!empty($filter_status)) {
        $query .= " AND p.status = ?";
        $params[] = $filter_status;
        $types .= 's';
    }
    
    if (!empty($search)) {
        $query .= " AND (p.nama_produk LIKE ? OR p.deskripsi LIKE ?)";
        $search_param = "%$search%";
        $params[] = $search_param;
        $params[] = $search_param;
        $types .= 'ss';
    }
    
    $query .= " ORDER BY p.created_at DESC";
    
    $stmt = $conn->prepare($query);
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $produk_list = $stmt->get_result();
    
    // Ambil kategori untuk dropdown
    $kategori_list = $conn->query("SELECT * FROM kategori ORDER BY nama_kategori");
    
    // Statistik
    $stats = $conn->query("
        SELECT 
            COUNT(*) as total,
            SUM(CASE WHEN status = 'tersedia' THEN 1 ELSE 0 END) as tersedia,
            SUM(CASE WHEN status = 'habis' THEN 1 ELSE 0 END) as habis,
            SUM(CASE WHEN stok < 5 AND status = 'tersedia' THEN 1 ELSE 0 END) as stok_menipis
        FROM produk
    ")->fetch_assoc();
    
} catch (Exception $e) {
    $error_message = $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Produk - Republik Computer</title>
    
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        body {
            background: #f3f4f6;
            display: flex;
            min-height: 100vh;
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
        .stat-icon.red { background: #fee2e2; color: #ef4444; }
        .stat-icon.orange { background: #fed7aa; color: #f59e0b; }
        
        /* Content Section */
        .content-section {
            background: white;
            padding: 2rem;
            border-radius: 0.75rem;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }
        
        /* Filter Bar */
        .filter-bar {
            display: flex;
            gap: 1rem;
            margin-bottom: 2rem;
            flex-wrap: wrap;
            align-items: center;
        }
        
        .filter-bar .form-input,
        .filter-bar .form-select {
            padding: 0.75rem 1rem;
            border: 2px solid #e5e7eb;
            border-radius: 0.5rem;
        }
        
        .filter-bar .search-box {
            flex: 1;
            min-width: 250px;
        }
        
        /* Action Bar */
        .action-bar {
            display: flex;
            justify-content: space-between;
            margin-bottom: 1.5rem;
            flex-wrap: wrap;
            gap: 1rem;
        }
        
        /* Table */
        .data-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .data-table th {
            background: #f9fafb;
            padding: 1rem;
            text-align: left;
            font-weight: 600;
            color: #374151;
            border-bottom: 2px solid #e5e7eb;
            white-space: nowrap;
        }
        
        .data-table td {
            padding: 1rem;
            border-bottom: 1px solid #e5e7eb;
            vertical-align: middle;
        }
        
        .data-table tr:hover {
            background: #f9fafb;
        }
        
        .data-table .checkbox-col {
            width: 40px;
            text-align: center;
        }
        
        .product-name {
            font-weight: 600;
            color: #1f2937;
            margin-bottom: 0.25rem;
        }
        
        .product-category {
            font-size: 0.875rem;
            color: #6b7280;
        }
        
        .price-value {
            font-size: 1.125rem;
            font-weight: 700;
            color: #2563eb;
        }
        
        .stock-value {
            font-size: 1.125rem;
            font-weight: 700;
        }
        
        .stock-value.low {
            color: #ef4444;
        }
        
        .stock-value.medium {
            color: #f59e0b;
        }
        
        .stock-value.high {
            color: #10b981;
        }
        
        .status-badge {
            padding: 0.375rem 0.875rem;
            border-radius: 9999px;
            font-size: 0.875rem;
            font-weight: 600;
            text-transform: uppercase;
        }
        
        .status-tersedia { background: #d1fae5; color: #065f46; }
        .status-habis { background: #fee2e2; color: #991b1b; }
        .status-nonaktif { background: #e5e7eb; color: #374151; }
        
        /* Action Buttons */
        .action-buttons {
            display: flex;
            gap: 0.5rem;
        }
        
        .btn-icon {
            width: 36px;
            height: 36px;
            padding: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 0.375rem;
            border: none;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .btn-edit { background: #dbeafe; color: #2563eb; }
        .btn-edit:hover { background: #2563eb; color: white; }
        
        .btn-stock { background: #fef3c7; color: #f59e0b; }
        .btn-stock:hover { background: #f59e0b; color: white; }
        
        .btn-price { background: #d1fae5; color: #10b981; }
        .btn-price:hover { background: #10b981; color: white; }
        
        .btn-delete { background: #fee2e2; color: #ef4444; }
        .btn-delete:hover { background: #ef4444; color: white; }
        
        /* Modal */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            animation: fadeIn 0.3s;
        }
        
        .modal.show {
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .modal-content {
            background: white;
            border-radius: 0.75rem;
            padding: 2rem;
            max-width: 600px;
            width: 90%;
            max-height: 90vh;
            overflow-y: auto;
            animation: slideUp 0.3s;
        }
        
        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid #e5e7eb;
        }
        
        .modal-header h2 {
            font-size: 1.5rem;
            color: #1f2937;
        }
        
        .close-modal {
            background: none;
            border: none;
            font-size: 1.5rem;
            cursor: pointer;
            color: #6b7280;
            padding: 0;
            width: 32px;
            height: 32px;
        }
        
        .close-modal:hover {
            color: #ef4444;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        
        @keyframes slideUp {
            from { transform: translateY(50px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }
        
        @media (max-width: 768px) {
            .sidebar {
                width: 0;
                padding: 0;
            }
            
            .main-content {
                margin-left: 0;
            }
            
            .data-table {
                font-size: 0.875rem;
            }
            
            .action-buttons {
                flex-direction: column;
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
                <a href="dashboard.php">
                    <i class="fas fa-home"></i>
                    <span>Dashboard</span>
                </a>
            </li>
            <li>
                <a href="produk.php" class="active">
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
                <a href="transaksi.php">
                    <i class="fas fa-shopping-cart"></i>
                    <span>Transaksi</span>
                </a>
            </li>
            <?php if ($user_role === 'admin'): ?>
            <li>
                <a href="pengguna.php">
                    <i class="fas fa-users"></i>
                    <span>Kelola Pengguna</span>
                </a>
            </li>
            <?php endif; ?>
            <li>
                <a href="laporan.php">
                    <i class="fas fa-chart-bar"></i>
                    <span>Laporan</span>
                </a>
            </li>
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
                <h1>Kelola Produk</h1>
                <p style="color: #6b7280;">Manajemen produk, stok, dan harga</p>
            </div>
            <div style="text-align: right;">
                <p style="font-weight: 600;"><?php echo htmlspecialchars($user_name); ?></p>
                <p style="font-size: 0.875rem; color: #6b7280;">
                    <?php echo $user_role === 'admin' ? 'Administrator' : 'Kepala Toko'; ?>
                </p>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-info">
                    <h3>Total Produk</h3>
                    <p><?php echo $stats['total']; ?></p>
                </div>
                <div class="stat-icon blue">
                    <i class="fas fa-box"></i>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-info">
                    <h3>Produk Tersedia</h3>
                    <p><?php echo $stats['tersedia']; ?></p>
                </div>
                <div class="stat-icon green">
                    <i class="fas fa-check-circle"></i>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-info">
                    <h3>Stok Habis</h3>
                    <p><?php echo $stats['habis']; ?></p>
                </div>
                <div class="stat-icon red">
                    <i class="fas fa-times-circle"></i>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-info">
                    <h3>Stok Menipis</h3>
                    <p><?php echo $stats['stok_menipis']; ?></p>
                </div>
                <div class="stat-icon orange">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
            </div>
        </div>

        <!-- Alerts -->
        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i>
                <span><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></span>
            </div>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle"></i>
                <span><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></span>
            </div>
        <?php endif; ?>

        <!-- Content Section -->
        <div class="content-section">
            <!-- Filter Bar -->
            <form action="produk.php" method="GET" class="filter-bar">
                <div class="search-box">
                    <input type="text" name="search" class="form-input" placeholder="Cari produk..." 
                           value="<?php echo htmlspecialchars($search); ?>">
                </div>
                
                <select name="kategori" class="form-select">
                    <option value="">Semua Kategori</option>
                    <?php 
                    $kategori_list->data_seek(0);
                    while ($kat = $kategori_list->fetch_assoc()): 
                    ?>
                        <option value="<?php echo $kat['id_kategori']; ?>" 
                                <?php echo $filter_kategori == $kat['id_kategori'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($kat['nama_kategori']); ?>
                        </option>
                    <?php endwhile; ?>
                </select>
                
                <select name="status" class="form-select">
                    <option value="">Semua Status</option>
                    <option value="tersedia" <?php echo $filter_status === 'tersedia' ? 'selected' : ''; ?>>Tersedia</option>
                    <option value="habis" <?php echo $filter_status === 'habis' ? 'selected' : ''; ?>>Habis</option>
                    <option value="nonaktif" <?php echo $filter_status === 'nonaktif' ? 'selected' : ''; ?>>Nonaktif</option>
                </select>
                
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-filter"></i> Filter
                </button>
                
                <a href="produk.php" class="btn btn-secondary">
                    <i class="fas fa-redo"></i> Reset
                </a>
            </form>

            <!-- Action Bar -->
            <div class="action-bar">
                <button class="btn btn-success" onclick="openModal('modalCreate')">
                    <i class="fas fa-plus"></i> Tambah Produk Baru
                </button>
                
                <div style="display: flex; gap: 0.5rem;">
                    <button class="btn btn-primary" onclick="bulkAction('tersedia')">
                        <i class="fas fa-check"></i> Aktifkan Terpilih
                    </button>
                    <button class="btn btn-danger" onclick="bulkAction('nonaktif')">
                        <i class="fas fa-ban"></i> Nonaktifkan Terpilih
                    </button>
                </div>
            </div>

            <!-- Table -->
            <div style="overflow-x: auto;">
                <form id="bulkForm" method="POST">
                    <input type="hidden" name="action" value="bulk_update_status">
                    <input type="hidden" name="status" id="bulkStatus">
                    
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th class="checkbox-col">
                                    <input type="checkbox" id="selectAll" onchange="toggleSelectAll()">
                                </th>
                                <th>Produk</th>
                                <th>Harga</th>
                                <th>Stok</th>
                                <th>Status</th>
                                <th style="text-align: center;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($produk_list && $produk_list->num_rows > 0): ?>
                                <?php while ($produk = $produk_list->fetch_assoc()): ?>
                                    <tr>
                                        <td class="checkbox-col">
                                            <input type="checkbox" name="selected[]" value="<?php echo $produk['id_produk']; ?>" class="select-item">
                                        </td>
                                        <td>
                                            <div class="product-name"><?php echo htmlspecialchars($produk['nama_produk']); ?></div>
                                            <div class="product-category">
                                                <i class="fas fa-tag"></i> <?php echo htmlspecialchars($produk['nama_kategori']); ?>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="price-value">Rp <?php echo number_format($produk['harga'], 0, ',', '.'); ?></div>
                                        </td>
                                        <td>
                                            <div class="stock-value <?php 
                                                if ($produk['stok'] == 0) echo 'low';
                                                elseif ($produk['stok'] < 5) echo 'medium';
                                                else echo 'high';
                                            ?>">
                                                <?php echo $produk['stok']; ?> pcs
                                            </div>
                                        </td>
                                        <td>
                                            <span class="status-badge status-<?php echo $produk['status']; ?>">
                                                <?php echo ucfirst($produk['status']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="action-buttons" style="justify-content: center;">
                                                <button type="button" class="btn-icon btn-edit" 
                                                        onclick="openEditModal(<?php echo htmlspecialchars(json_encode($produk)); ?>)"
                                                        title="Edit Produk">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button type="button" class="btn-icon btn-stock" 
                                                        onclick="openStockModal(<?php echo $produk['id_produk']; ?>, '<?php echo htmlspecialchars($produk['nama_produk']); ?>', <?php echo $produk['stok']; ?>)"
                                                        title="Update Stok">
                                                    <i class="fas fa-cubes"></i>
                                                </button>
                                                <button type="button" class="btn-icon btn-price" 
                                                        onclick="openPriceModal(<?php echo $produk['id_produk']; ?>, '<?php echo htmlspecialchars($produk['nama_produk']); ?>', <?php echo $produk['harga']; ?>)"
                                                        title="Update Harga">
                                                    <i class="fas fa-dollar-sign"></i>
                                                </button>
                                                <button type="button" class="btn-icon btn-delete" 
                                                        onclick="deleteProduk(<?php echo $produk['id_produk']; ?>, '<?php echo htmlspecialchars($produk['nama_produk']); ?>')"
                                                        title="Hapus Produk">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" style="text-align: center; padding: 2rem; color: #6b7280;">
                                        <i class="fas fa-box-open" style="font-size: 3rem; margin-bottom: 1rem;"></i>
                                        <p>Tidak ada produk ditemukan</p>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </form>
            </div>
        </div>
    </main>

    <!-- Modal Tambah Produk -->
    <div id="modalCreate" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2><i class="fas fa-plus-circle"></i> Tambah Produk Baru</h2>
                <button class="close-modal" onclick="closeModal('modalCreate')">&times;</button>
            </div>
            <form method="POST">
                <input type="hidden" name="action" value="create">
                
                <div class="form-group">
                    <label class="form-label">Nama Produk <span class="required">*</span></label>
                    <input type="text" name="nama_produk" class="form-input" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Kategori <span class="required">*</span></label>
                    <select name="id_kategori" class="form-select" required>
                        <option value="">Pilih Kategori</option>
                        <?php 
                        $kategori_list->data_seek(0);
                        while ($kat = $kategori_list->fetch_assoc()): 
                        ?>
                            <option value="<?php echo $kat['id_kategori']; ?>">
                                <?php echo htmlspecialchars($kat['nama_kategori']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Deskripsi</label>
                    <textarea name="deskripsi" class="form-textarea" rows="3"></textarea>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Harga <span class="required">*</span></label>
                        <input type="number" name="harga" class="form-input" min="0" step="0.01" required>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Stok <span class="required">*</span></label>
                        <input type="number" name="stok" class="form-input" min="0" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Status <span class="required">*</span></label>
                    <select name="status" class="form-select" required>
                        <option value="tersedia">Tersedia</option>
                        <option value="habis">Habis</option>
                        <option value="nonaktif">Nonaktif</option>
                    </select>
                </div>
                
                <div style="display: flex; gap: 1rem; margin-top: 2rem;">
                    <button type="submit" class="btn btn-success btn-block">
                        <i class="fas fa-save"></i> Simpan Produk
                    </button>
                    <button type="button" class="btn btn-secondary btn-block" onclick="closeModal('modalCreate')">
                        <i class="fas fa-times"></i> Batal
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Edit Produk -->
    <div id="modalEdit" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2><i class="fas fa-edit"></i> Edit Produk</h2>
                <button class="close-modal" onclick="closeModal('modalEdit')">&times;</button>
            </div>
            <form method="POST" id="formEdit">
                <input type="hidden" name="action" value="update">
                <input type="hidden" name="id_produk" id="edit_id_produk">
                
                <div class="form-group">
                    <label class="form-label">Nama Produk <span class="required">*</span></label>
                    <input type="text" name="nama_produk" id="edit_nama_produk" class="form-input" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Kategori <span class="required">*</span></label>
                    <select name="id_kategori" id="edit_id_kategori" class="form-select" required>
                        <?php 
                        $kategori_list->data_seek(0);
                        while ($kat = $kategori_list->fetch_assoc()): 
                        ?>
                            <option value="<?php echo $kat['id_kategori']; ?>">
                                <?php echo htmlspecialchars($kat['nama_kategori']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Deskripsi</label>
                    <textarea name="deskripsi" id="edit_deskripsi" class="form-textarea" rows="3"></textarea>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Harga <span class="required">*</span></label>
                        <input type="number" name="harga" id="edit_harga" class="form-input" min="0" step="0.01" required>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Stok <span class="required">*</span></label>
                        <input type="number" name="stok" id="edit_stok" class="form-input" min="0" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Status <span class="required">*</span></label>
                    <select name="status" id="edit_status" class="form-select" required>
                        <option value="tersedia">Tersedia</option>
                        <option value="habis">Habis</option>
                        <option value="nonaktif">Nonaktif</option>
                    </select>
                </div>
                
                <div style="display: flex; gap: 1rem; margin-top: 2rem;">
                    <button type="submit" class="btn btn-primary btn-block">
                        <i class="fas fa-save"></i> Update Produk
                    </button>
                    <button type="button" class="btn btn-secondary btn-block" onclick="closeModal('modalEdit')">
                        <i class="fas fa-times"></i> Batal
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Update Stok -->
    <div id="modalStok" class="modal">
        <div class="modal-content" style="max-width: 400px;">
            <div class="modal-header">
                <h2><i class="fas fa-cubes"></i> Update Stok</h2>
                <button class="close-modal" onclick="closeModal('modalStok')">&times;</button>
            </div>
            <form method="POST">
                <input type="hidden" name="action" value="update_stok">
                <input type="hidden" name="id_produk" id="stok_id_produk">
                
                <div class="form-group">
                    <label class="form-label">Produk</label>
                    <input type="text" id="stok_nama_produk" class="form-input" readonly style="background: #f3f4f6;">
                </div>
                
                <div class="form-group">
                    <label class="form-label">Stok Saat Ini</label>
                    <input type="text" id="stok_current" class="form-input" readonly style="background: #f3f4f6;">
                </div>
                
                <div class="form-group">
                    <label class="form-label">Stok Baru <span class="required">*</span></label>
                    <input type="number" name="stok" id="stok_new" class="form-input" min="0" required autofocus>
                    <span class="form-helper">Masukkan jumlah stok baru</span>
                </div>
                
                <div style="display: flex; gap: 1rem; margin-top: 2rem;">
                    <button type="submit" class="btn btn-warning btn-block">
                        <i class="fas fa-save"></i> Update Stok
                    </button>
                    <button type="button" class="btn btn-secondary btn-block" onclick="closeModal('modalStok')">
                        <i class="fas fa-times"></i> Batal
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Update Harga -->
    <div id="modalHarga" class="modal">
        <div class="modal-content" style="max-width: 400px;">
            <div class="modal-header">
                <h2><i class="fas fa-dollar-sign"></i> Update Harga</h2>
                <button class="close-modal" onclick="closeModal('modalHarga')">&times;</button>
            </div>
            <form method="POST">
                <input type="hidden" name="action" value="update_harga">
                <input type="hidden" name="id_produk" id="harga_id_produk">
                
                <div class="form-group">
                    <label class="form-label">Produk</label>
                    <input type="text" id="harga_nama_produk" class="form-input" readonly style="background: #f3f4f6;">
                </div>
                
                <div class="form-group">
                    <label class="form-label">Harga Saat Ini</label>
                    <input type="text" id="harga_current" class="form-input" readonly style="background: #f3f4f6;">
                </div>
                
                <div class="form-group">
                    <label class="form-label">Harga Baru <span class="required">*</span></label>
                    <input type="number" name="harga" id="harga_new" class="form-input" min="0" step="0.01" required autofocus>
                    <span class="form-helper">Masukkan harga baru (tanpa titik/koma)</span>
                </div>
                
                <div style="display: flex; gap: 1rem; margin-top: 2rem;">
                    <button type="submit" class="btn btn-success btn-block">
                        <i class="fas fa-save"></i> Update Harga
                    </button>
                    <button type="button" class="btn btn-secondary btn-block" onclick="closeModal('modalHarga')">
                        <i class="fas fa-times"></i> Batal
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Delete Form (Hidden) -->
    <form id="deleteForm" method="POST" style="display: none;">
        <input type="hidden" name="action" value="delete">
        <input type="hidden" name="id_produk" id="delete_id_produk">
    </form>

    <script>
        // Modal Functions
        function openModal(modalId) {
            document.getElementById(modalId).classList.add('show');
        }

        function closeModal(modalId) {
            document.getElementById(modalId).classList.remove('show');
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            if (event.target.classList.contains('modal')) {
                event.target.classList.remove('show');
            }
        }

        // Open Edit Modal
        function openEditModal(produk) {
            document.getElementById('edit_id_produk').value = produk.id_produk;
            document.getElementById('edit_nama_produk').value = produk.nama_produk;
            document.getElementById('edit_id_kategori').value = produk.id_kategori;
            document.getElementById('edit_deskripsi').value = produk.deskripsi || '';
            document.getElementById('edit_harga').value = produk.harga;
            document.getElementById('edit_stok').value = produk.stok;
            document.getElementById('edit_status').value = produk.status;
            openModal('modalEdit');
        }

        // Open Stock Modal
        function openStockModal(id, nama, stok) {
            document.getElementById('stok_id_produk').value = id;
            document.getElementById('stok_nama_produk').value = nama;
            document.getElementById('stok_current').value = stok + ' pcs';
            document.getElementById('stok_new').value = stok;
            openModal('modalStok');
        }

        // Open Price Modal
        function openPriceModal(id, nama, harga) {
            document.getElementById('harga_id_produk').value = id;
            document.getElementById('harga_nama_produk').value = nama;
            document.getElementById('harga_current').value = 'Rp ' + new Intl.NumberFormat('id-ID').format(harga);
            document.getElementById('harga_new').value = harga;
            openModal('modalHarga');
        }

        // Delete Product
        function deleteProduk(id, nama) {
            if (confirm('Hapus produk "' + nama + '"?\n\nTindakan ini tidak dapat dibatalkan!')) {
                document.getElementById('delete_id_produk').value = id;
                document.getElementById('deleteForm').submit();
            }
        }

        // Select All Checkbox
        function toggleSelectAll() {
            const selectAll = document.getElementById('selectAll');
            const checkboxes = document.querySelectorAll('.select-item');
            checkboxes.forEach(cb => cb.checked = selectAll.checked);
        }

        // Bulk Action
        function bulkAction(status) {
            const selected = document.querySelectorAll('.select-item:checked');
            
            if (selected.length === 0) {
                alert('Pilih minimal 1 produk!');
                return;
            }
            
            const statusText = status === 'tersedia' ? 'mengaktifkan' : 'menonaktifkan';
            if (confirm('Yakin ingin ' + statusText + ' ' + selected.length + ' produk?')) {
                document.getElementById('bulkStatus').value = status;
                document.getElementById('bulkForm').submit();
            }
        }

        // Auto hide alerts
        setTimeout(function() {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(function(alert) {
                alert.style.transition = 'opacity 0.5s';
                alert.style.opacity = '0';
                setTimeout(() => alert.remove(), 500);
            });
        }, 5000);
    </script>

    <?php
    if (isset($stmt)) $stmt->close();
    if (isset($conn)) $conn->close();
    ?>
</body>
</html>