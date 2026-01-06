<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../auth/login.php');
    exit;
}

$user_name = $_SESSION['nama'];

// Handle update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_produk = intval($_POST['id_produk']);
    $stok = intval($_POST['stok']);
    $harga = floatval($_POST['harga']);
    
    try {
        $conn = getConnection();
        $stmt = $conn->prepare("UPDATE produk SET stok = ?, harga = ? WHERE id_produk = ?");
        $stmt->bind_param("idi", $stok, $harga, $id_produk);
        
        if ($stmt->execute()) {
            $_SESSION['success'] = "Produk berhasil diupdate!";
        } else {
            $_SESSION['error'] = "Gagal update produk!";
        }
        
        $stmt->close();
        $conn->close();
    } catch (Exception $e) {
        $_SESSION['error'] = "Error: " . $e->getMessage();
    }
    
    header('Location: edit_stok.php');
    exit;
}

// Ambil semua produk
try {
    $conn = getConnection();
    $search = isset($_GET['search']) ? cleanInput($_GET['search']) : '';
    
    if ($search) {
        $search_param = "%$search%";
        $stmt = $conn->prepare("SELECT * FROM produk WHERE nama_produk LIKE ? ORDER BY nama_produk");
        $stmt->bind_param("s", $search_param);
    } else {
        $stmt = $conn->prepare("SELECT * FROM produk ORDER BY nama_produk");
    }
    
    $stmt->execute();
    $produk_list = $stmt->get_result();
} catch (Exception $e) {
    $error_message = $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Stok & Harga - Republik Computer</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { background: #f3f4f6; font-family: system-ui, sans-serif; display: flex; min-height: 100vh; }
        .sidebar { width: 260px; background: #1f2937; color: white; padding: 2rem 0; position: fixed; height: 100vh; overflow-y: auto; }
        .sidebar-brand { padding: 0 1.5rem; margin-bottom: 2rem; font-size: 1.5rem; font-weight: 700; color: white; text-decoration: none; display: block; }
        .sidebar-menu { list-style: none; padding: 0; }
        .sidebar-menu a { display: flex; align-items: center; gap: 1rem; padding: 0.75rem 1.5rem; color: #d1d5db; text-decoration: none; transition: all 0.3s; }
        .sidebar-menu a:hover { background: #374151; color: white; }
        .main-content { flex: 1; margin-left: 260px; padding: 2rem; }
        .top-bar { background: white; padding: 1.5rem; border-radius: 0.75rem; box-shadow: 0 2px 4px rgba(0,0,0,0.05); margin-bottom: 2rem; }
        .top-bar h1 { font-size: 1.875rem; color: #1f2937; margin-bottom: 0.5rem; }
        .search-box { margin-bottom: 2rem; }
        .search-box input { width: 100%; max-width: 500px; padding: 0.75rem 1rem; border: 1px solid #d1d5db; border-radius: 0.5rem; font-size: 1rem; }
        .content-section { background: white; padding: 1.5rem; border-radius: 0.75rem; box-shadow: 0 2px 4px rgba(0,0,0,0.05); }
        .data-table { width: 100%; border-collapse: collapse; }
        .data-table th { background: #f9fafb; padding: 0.75rem; text-align: left; font-weight: 600; color: #374151; border-bottom: 2px solid #e5e7eb; }
        .data-table td { padding: 0.75rem; border-bottom: 1px solid #e5e7eb; }
        .data-table tr:hover { background: #f9fafb; }
        .form-inline { display: flex; gap: 0.5rem; align-items: center; }
        .form-inline input { padding: 0.5rem; border: 1px solid #d1d5db; border-radius: 0.375rem; width: 100px; }
        .btn { padding: 0.5rem 1rem; border: none; border-radius: 0.5rem; font-weight: 600; cursor: pointer; transition: all 0.3s; }
        .btn-primary { background: #2563eb; color: white; }
        .btn-primary:hover { background: #1e40af; }
        .alert { padding: 1rem; border-radius: 0.5rem; margin-bottom: 1rem; }
        .alert-success { background: #d1fae5; color: #065f46; border: 1px solid #6ee7b7; }
        .alert-danger { background: #fee2e2; color: #991b1b; border: 1px solid #fecaca; }
        .stok-low { color: #ef4444; font-weight: 700; }
        .stok-ok { color: #10b981; font-weight: 700; }
    </style>
</head>
<body>
    <aside class="sidebar">
        <a href="dashboard.php" class="sidebar-brand"><i class="fas fa-laptop"></i> Republik Computer</a>
        <ul class="sidebar-menu">
            <li><a href="dashboard.php"><i class="fas fa-home"></i> Dashboard</a></li>
            <li><a href="produk.php"><i class="fas fa-box"></i> Kelola Produk</a></li>
            <li><a href="edit_stok.php" style="background: #374151; color: white;"><i class="fas fa-warehouse"></i> Edit Stok Produk</a></li>
            <li><a href="transaksi.php"><i class="fas fa-shopping-cart"></i> Transaksi</a></li>
            <li><a href="../auth/logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
        </ul>
    </aside>

    <main class="main-content">
        <div class="top-bar">
            <h1><i class="fas fa-warehouse"></i> Edit Stok & Harga Produk</h1>
            <p style="color: #6b7280;">Kelola stok dan harga produk</p>
        </div>

        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i> <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle"></i> <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
            </div>
        <?php endif; ?>

        <div class="search-box">
            <form method="GET">
                <input type="text" name="search" placeholder="Cari produk..." value="<?php echo htmlspecialchars($search ?? ''); ?>">
            </form>
        </div>

        <div class="content-section">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Nama Produk</th>
                        <th>Stok Saat Ini</th>
                        <th>Harga Saat Ini</th>
                        <th>Update</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($produk_list && $produk_list->num_rows > 0): ?>
                        <?php while ($produk = $produk_list->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($produk['nama_produk']); ?></td>
                            <td class="<?php echo $produk['stok'] < 5 ? 'stok-low' : 'stok-ok'; ?>">
                                <?php echo $produk['stok']; ?> pcs
                            </td>
                            <td>Rp <?php echo number_format($produk['harga'], 0, ',', '.'); ?></td>
                            <td>
                                <form method="POST" class="form-inline">
                                    <input type="hidden" name="id_produk" value="<?php echo $produk['id_produk']; ?>">
                                    <input type="number" name="stok" value="<?php echo $produk['stok']; ?>" min="0" required>
                                    <input type="number" name="harga" value="<?php echo $produk['harga']; ?>" min="0" step="1000" required>
                                    <button type="submit" class="btn btn-primary" onclick="return confirm('Update produk ini?')">
                                        <i class="fas fa-save"></i> Update
                                    </button>
                                </form>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="4" style="text-align: center; padding: 2rem;">Tidak ada produk</td></tr>
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