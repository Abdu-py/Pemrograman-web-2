<?php
/**
 * FILE: user/keranjang.php
 * DESKRIPSI: Halaman keranjang belanja
 * LOKASI: republik-computer/user/keranjang.php
 */

session_start();
require_once '../config/database.php';

// Cek apakah sudah login
if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.php');
    exit;
}

// Cek role
if ($_SESSION['role'] !== 'pelanggan') {
    header('Location: ../admin/dashboard.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['nama'];

// Handle actions (update, delete, clear)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $conn = getConnection();
        
        if (isset($_POST['action'])) {
            switch ($_POST['action']) {
                case 'update':
                    $id_keranjang = intval($_POST['id_keranjang']);
                    $qty = intval($_POST['qty']);
                    
                    if ($qty > 0) {
                        $stmt = $conn->prepare("UPDATE keranjang SET jumlah = ? WHERE id_keranjang = ? AND id_user = ?");
                        $stmt->bind_param("iii", $qty, $id_keranjang, $user_id);
                        $stmt->execute();
                        $_SESSION['success'] = "Jumlah berhasil diupdate";
                    }
                    break;
                    
                case 'delete':
                    $id_keranjang = intval($_POST['id_keranjang']);
                    $stmt = $conn->prepare("DELETE FROM keranjang WHERE id_keranjang = ? AND id_user = ?");
                    $stmt->bind_param("ii", $id_keranjang, $user_id);
                    $stmt->execute();
                    $_SESSION['success'] = "Produk berhasil dihapus dari keranjang";
                    break;
                    
                case 'clear':
                    $stmt = $conn->prepare("DELETE FROM keranjang WHERE id_user = ?");
                    $stmt->bind_param("i", $user_id);
                    $stmt->execute();
                    $_SESSION['success'] = "Keranjang berhasil dikosongkan";
                    break;
            }
        }
        
        if (isset($stmt)) $stmt->close();
        if (isset($conn)) $conn->close();
        
        header('Location: keranjang.php');
        exit;
        
    } catch (Exception $e) {
        $_SESSION['error'] = "Terjadi kesalahan: " . $e->getMessage();
        header('Location: keranjang.php');
        exit;
    }
}

// Ambil data keranjang
try {
    $conn = getConnection();
    
    $stmt = $conn->prepare("
        SELECT 
            k.id_keranjang,
            k.jumlah,
            k.subtotal,
            p.id_produk,
            p.nama_produk,
            p.harga,
            p.stok,
            p.foto,
            kat.nama_kategori
        FROM keranjang k
        JOIN produk p ON k.id_produk = p.id_produk
        LEFT JOIN kategori kat ON p.id_kategori = kat.id_kategori
        WHERE k.id_user = ?
        ORDER BY k.created_at DESC
    ");
    
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $cart_items = $stmt->get_result();
    
    // Hitung total
    $stmt = $conn->prepare("
        SELECT 
            COUNT(*) as total_items,
            SUM(k.jumlah) as total_qty,
            SUM(k.jumlah * p.harga) as total_harga
        FROM keranjang k
        JOIN produk p ON k.id_produk = p.id_produk
        WHERE k.id_user = ?
    ");
    
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $totals = $stmt->get_result()->fetch_assoc();
    
} catch (Exception $e) {
    $error_message = $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Keranjang Belanja - Republik Computer</title>
    
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        body {
            background: #f3f4f6;
        }
        
        .navbar {
            background: white;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            padding: 1rem 0;
        }
        
        .navbar-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .navbar-brand {
            font-size: 1.5rem;
            font-weight: 700;
            color: #2563eb;
            text-decoration: none;
        }
        
        .navbar-menu {
            display: flex;
            gap: 2rem;
        }
        
        .navbar-menu a {
            color: #374151;
            text-decoration: none;
            font-weight: 500;
        }
        
        .page-header {
            background: white;
            padding: 2rem 0;
            margin-bottom: 2rem;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }
        
        .page-header h1 {
            font-size: 2rem;
            color: #1f2937;
        }
        
        .cart-container {
            display: grid;
            grid-template-columns: 1fr 400px;
            gap: 2rem;
            margin-bottom: 2rem;
        }
        
        .cart-items {
            background: white;
            border-radius: 0.75rem;
            padding: 2rem;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }
        
        .cart-item {
            display: grid;
            grid-template-columns: 100px 1fr auto;
            gap: 1.5rem;
            padding: 1.5rem;
            border: 1px solid #e5e7eb;
            border-radius: 0.5rem;
            margin-bottom: 1rem;
        }
        
        .item-image {
            width: 100px;
            height: 100px;
            background: #f3f4f6;
            border-radius: 0.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2.5rem;
            color: #9ca3af;
        }
        
        .item-details {
            flex: 1;
        }
        
        .item-category {
            font-size: 0.875rem;
            color: #6b7280;
            margin-bottom: 0.25rem;
        }
        
        .item-name {
            font-size: 1.125rem;
            font-weight: 600;
            color: #1f2937;
            margin-bottom: 0.5rem;
        }
        
        .item-price {
            font-size: 1.25rem;
            font-weight: 700;
            color: #2563eb;
            margin-bottom: 0.5rem;
        }
        
        .item-stock {
            font-size: 0.875rem;
            color: #6b7280;
        }
        
        .item-actions {
            display: flex;
            flex-direction: column;
            gap: 1rem;
            align-items: flex-end;
        }
        
        .qty-control {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            background: #f3f4f6;
            padding: 0.5rem;
            border-radius: 0.5rem;
        }
        
        .qty-btn {
            width: 32px;
            height: 32px;
            background: #2563eb;
            color: white;
            border: none;
            border-radius: 0.375rem;
            cursor: pointer;
            font-weight: 700;
        }
        
        .qty-btn:hover {
            background: #1e40af;
        }
        
        .qty-input {
            width: 60px;
            text-align: center;
            border: 1px solid #e5e7eb;
            border-radius: 0.375rem;
            padding: 0.5rem;
            font-weight: 600;
        }
        
        .delete-btn {
            background: #ef4444;
            color: white;
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 0.375rem;
            cursor: pointer;
            font-size: 0.875rem;
        }
        
        .delete-btn:hover {
            background: #dc2626;
        }
        
        .cart-summary {
            background: white;
            border-radius: 0.75rem;
            padding: 2rem;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
            height: fit-content;
            position: sticky;
            top: 1rem;
        }
        
        .summary-title {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid #e5e7eb;
        }
        
        .summary-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 1rem;
            font-size: 1rem;
        }
        
        .summary-total {
            display: flex;
            justify-content: space-between;
            font-size: 1.5rem;
            font-weight: 700;
            padding-top: 1rem;
            border-top: 2px solid #e5e7eb;
            margin-top: 1rem;
        }
        
        .summary-total .amount {
            color: #2563eb;
        }
        
        .empty-cart {
            text-align: center;
            padding: 4rem 2rem;
            background: white;
            border-radius: 0.75rem;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }
        
        .empty-cart i {
            font-size: 5rem;
            color: #9ca3af;
            margin-bottom: 1rem;
        }
        
        .empty-cart h2 {
            font-size: 1.5rem;
            color: #1f2937;
            margin-bottom: 0.5rem;
        }
        
        .empty-cart p {
            color: #6b7280;
            margin-bottom: 2rem;
        }
        
        @media (max-width: 1024px) {
            .cart-container {
                grid-template-columns: 1fr;
            }
            
            .cart-summary {
                position: relative;
            }
        }
        
        @media (max-width: 768px) {
            .cart-item {
                grid-template-columns: 80px 1fr;
            }
            
            .item-actions {
                grid-column: 1 / -1;
                flex-direction: row;
                justify-content: space-between;
            }
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar">
        <div class="container navbar-container">
            <a href="beranda.php" class="navbar-brand">
                <i class="fas fa-laptop"></i> Republik Computer
            </a>
            <div class="navbar-menu">
                <a href="beranda.php"><i class="fas fa-home"></i> Beranda</a>
                <a href="keranjang.php"><i class="fas fa-shopping-cart"></i> Keranjang</a>
                <a href="profil.php"><i class="fas fa-user"></i> Profil</a>
            </div>
        </div>
    </nav>

    <!-- Page Header -->
    <div class="page-header">
        <div class="container">
            <h1><i class="fas fa-shopping-cart"></i> Keranjang Belanja</h1>
        </div>
    </div>

    <!-- Main Content -->
    <div class="container">
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

        <?php if ($cart_items && $cart_items->num_rows > 0): ?>
            <div class="cart-container">
                <!-- Cart Items -->
                <div class="cart-items">
                    <h2 style="margin-bottom: 1.5rem;">Item di Keranjang (<?php echo $totals['total_items']; ?>)</h2>
                    
                    <?php while ($item = $cart_items->fetch_assoc()): ?>
                        <div class="cart-item">
                            <div class="item-image">
                                <i class="fas fa-laptop"></i>
                            </div>
                            
                            <div class="item-details">
                                <div class="item-category">
                                    <i class="fas fa-tag"></i> <?php echo htmlspecialchars($item['nama_kategori']); ?>
                                </div>
                                <div class="item-name"><?php echo htmlspecialchars($item['nama_produk']); ?></div>
                                <div class="item-price">Rp <?php echo number_format($item['harga'], 0, ',', '.'); ?></div>
                                <div class="item-stock">Stok tersedia: <?php echo $item['stok']; ?></div>
                                <div style="margin-top: 0.5rem; color: #6b7280; font-size: 0.875rem;">
                                    Subtotal: <strong style="color: #2563eb;">Rp <?php echo number_format($item['harga'] * $item['jumlah'], 0, ',', '.'); ?></strong>
                                </div>
                            </div>
                            
                            <div class="item-actions">
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="action" value="update">
                                    <input type="hidden" name="id_keranjang" value="<?php echo $item['id_keranjang']; ?>">
                                    <div class="qty-control">
                                        <button type="submit" name="qty" value="<?php echo max(1, $item['jumlah'] - 1); ?>" class="qty-btn">-</button>
                                        <input type="number" class="qty-input" value="<?php echo $item['jumlah']; ?>" 
                                               min="1" max="<?php echo $item['stok']; ?>" readonly>
                                        <button type="submit" name="qty" value="<?php echo min($item['stok'], $item['jumlah'] + 1); ?>" 
                                                class="qty-btn" <?php echo $item['jumlah'] >= $item['stok'] ? 'disabled' : ''; ?>>+</button>
                                    </div>
                                </form>
                                
                                <form method="POST" style="display: inline;" 
                                      onsubmit="return confirm('Hapus produk ini dari keranjang?')">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="id_keranjang" value="<?php echo $item['id_keranjang']; ?>">
                                    <button type="submit" class="delete-btn">
                                        <i class="fas fa-trash"></i> Hapus
                                    </button>
                                </form>
                            </div>
                        </div>
                    <?php endwhile; ?>
                    
                    <form method="POST" style="margin-top: 1.5rem;" 
                          onsubmit="return confirm('Kosongkan semua item dari keranjang?')">
                        <input type="hidden" name="action" value="clear">
                        <button type="submit" class="btn btn-danger">
                            <i class="fas fa-trash-alt"></i> Kosongkan Keranjang
                        </button>
                    </form>
                </div>

                <!-- Cart Summary -->
                <div class="cart-summary">
                    <h3 class="summary-title">Ringkasan Belanja</h3>
                    
                    <div class="summary-row">
                        <span>Total Item:</span>
                        <strong><?php echo $totals['total_items']; ?> produk</strong>
                    </div>
                    
                    <div class="summary-row">
                        <span>Total Qty:</span>
                        <strong><?php echo $totals['total_qty']; ?> pcs</strong>
                    </div>
                    
                    <div class="summary-total">
                        <span>Total:</span>
                        <span class="amount">Rp <?php echo number_format($totals['total_harga'], 0, ',', '.'); ?></span>
                    </div>
                    
                    <a href="checkout.php" class="btn btn-primary btn-block" style="margin-top: 1.5rem;">
                        <i class="fas fa-credit-card"></i> Lanjut ke Pembayaran
                    </a>
                    
                    <a href="beranda.php" class="btn btn-secondary btn-block" style="margin-top: 1rem;">
                        <i class="fas fa-arrow-left"></i> Lanjut Belanja
                    </a>
                </div>
            </div>
        <?php else: ?>
            <!-- Empty Cart -->
            <div class="empty-cart">
                <i class="fas fa-shopping-cart"></i>
                <h2>Keranjang Belanja Kosong</h2>
                <p>Belum ada produk dalam keranjang Anda</p>
                <a href="beranda.php" class="btn btn-primary">
                    <i class="fas fa-shopping-bag"></i> Mulai Belanja
                </a>
            </div>
        <?php endif; ?>
    </div>

    <script>
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