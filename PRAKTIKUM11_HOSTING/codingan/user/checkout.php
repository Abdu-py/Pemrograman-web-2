<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.php');
    exit;
}

if ($_SESSION['role'] !== 'pelanggan') {
    header('Location: ../admin/dashboard.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['nama'];

$cart_items_array = array();
$total_items = 0;
$total_qty = 0;
$total_harga = 0;
$user_data = array('alamat' => '');
$error_message = '';

try {
    $conn = getConnection();
    
    // Cek keranjang
    $stmt = $conn->prepare("SELECT COUNT(*) as total FROM keranjang WHERE id_user = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $cart_check = $stmt->get_result()->fetch_assoc();
    
    if ($cart_check['total'] == 0) {
        $_SESSION['error'] = "Keranjang Anda kosong!";
        header('Location: keranjang.php');
        exit;
    }
    
    // Ambil keranjang - TANPA ALIAS untuk menghindari konflik
    $stmt = $conn->prepare("
        SELECT 
            keranjang.id_keranjang,
            keranjang.id_produk,
            keranjang.jumlah,
            keranjang.subtotal,
            produk.nama_produk,
            produk.harga,
            produk.stok
        FROM keranjang
        JOIN produk ON keranjang.id_produk = produk.id_produk
        WHERE keranjang.id_user = ?
    ");
    
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        $cart_items_array[] = $row;
        $total_items++;
        $total_qty += $row['jumlah'];
        $total_harga += $row['subtotal'];
    }
    
    // Ambil data user
    $stmt = $conn->prepare("SELECT alamat FROM users WHERE id_user = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $user_data = $stmt->get_result()->fetch_assoc();
    
} catch (Exception $e) {
    $error_message = $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - Republik Computer</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { background: #f3f4f6; font-family: system-ui, -apple-system, sans-serif; }
        .navbar { background: white; box-shadow: 0 2px 4px rgba(0,0,0,0.1); padding: 1rem 0; }
        .container { max-width: 1200px; margin: 0 auto; padding: 0 20px; }
        .navbar-brand { font-size: 1.5rem; font-weight: 700; color: #2563eb; text-decoration: none; }
        .page-header { background: white; padding: 2rem 0; margin-bottom: 2rem; box-shadow: 0 2px 4px rgba(0,0,0,0.05); }
        .page-header h1 { font-size: 2rem; color: #1f2937; }
        .checkout-container { display: grid; grid-template-columns: 1fr 400px; gap: 2rem; margin-bottom: 2rem; }
        .checkout-form, .order-summary { background: white; border-radius: 0.75rem; padding: 2rem; box-shadow: 0 2px 4px rgba(0,0,0,0.05); }
        .section-title { font-size: 1.25rem; font-weight: 600; margin-bottom: 1rem; padding-bottom: 0.75rem; border-bottom: 2px solid #e5e7eb; }
        .form-group { margin-bottom: 1rem; }
        .form-group label { display: block; margin-bottom: 0.5rem; font-weight: 500; }
        .form-group textarea { width: 100%; padding: 0.75rem; border: 1px solid #d1d5db; border-radius: 0.5rem; font-family: inherit; min-height: 100px; }
        .form-group textarea:focus { outline: none; border-color: #2563eb; box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1); }
        .payment-option { border: 2px solid #e5e7eb; border-radius: 0.5rem; padding: 1rem; margin-bottom: 1rem; cursor: pointer; display: block; transition: all 0.3s; }
        .payment-option:hover, .payment-option.selected { border-color: #2563eb; background: #eff6ff; }
        .payment-option input { margin-right: 0.5rem; }
        .summary-item { display: flex; justify-content: space-between; margin-bottom: 1rem; padding-bottom: 1rem; border-bottom: 1px solid #e5e7eb; }
        .item-name { font-weight: 500; margin-bottom: 0.25rem; }
        .item-qty { font-size: 0.875rem; color: #6b7280; }
        .item-price { font-weight: 600; color: #2563eb; }
        .summary-row { display: flex; justify-content: space-between; margin-bottom: 0.75rem; }
        .summary-total { display: flex; justify-content: space-between; font-size: 1.5rem; font-weight: 700; padding-top: 1rem; border-top: 2px solid #e5e7eb; margin-top: 1rem; }
        .summary-total .amount { color: #2563eb; }
        .btn { padding: 0.75rem 1.5rem; border: none; border-radius: 0.5rem; font-weight: 600; cursor: pointer; width: 100%; background: #2563eb; color: white; font-size: 1rem; transition: background 0.3s; }
        .btn:hover { background: #1e40af; }
        .alert { background: #fee2e2; color: #991b1b; padding: 1rem; border-radius: 0.5rem; margin-bottom: 1rem; }
        @media (max-width: 1024px) { .checkout-container { grid-template-columns: 1fr; } }
    </style>
</head>
<body>
    <nav class="navbar">
        <div class="container">
            <a href="beranda.php" class="navbar-brand"><i class="fas fa-laptop"></i> Republik Computer</a>
        </div>
    </nav>

    <div class="page-header">
        <div class="container">
            <h1><i class="fas fa-credit-card"></i> Checkout</h1>
        </div>
    </div>

    <div class="container">
        <?php if ($error_message): ?>
            <div class="alert"><i class="fas fa-exclamation-circle"></i> Error: <?php echo htmlspecialchars($error_message); ?></div>
        <?php endif; ?>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert"><i class="fas fa-exclamation-circle"></i> <?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
        <?php endif; ?>

        <div class="checkout-container">
            <div class="checkout-form">
                <form id="checkoutForm" method="POST" action="process_checkout.php">
                    <div style="margin-bottom: 2rem;">
                        <h2 class="section-title"><i class="fas fa-shipping-fast"></i> Informasi Pengiriman</h2>
                        <div class="form-group">
                            <label>Alamat Lengkap *</label>
                            <textarea name="alamat_pengiriman" required><?php echo htmlspecialchars($user_data['alamat'] ?? ''); ?></textarea>
                        </div>
                        <div class="form-group">
                            <label>Catatan Pesanan (Opsional)</label>
                            <textarea name="catatan" placeholder="Contoh: Kirim sebelum jam 5 sore"></textarea>
                        </div>
                    </div>

                    <div>
                        <h2 class="section-title"><i class="fas fa-money-bill-wave"></i> Metode Pembayaran</h2>
                        <label class="payment-option">
                            <input type="radio" name="metode_pembayaran" value="transfer_bank" required>
                            <strong>Transfer Bank</strong><br>
                            <small style="margin-left: 1.5rem; color: #6b7280;">BCA / Mandiri / BNI / BRI</small>
                        </label>
                        <label class="payment-option">
                            <input type="radio" name="metode_pembayaran" value="cod" required>
                            <strong>COD (Cash on Delivery)</strong><br>
                            <small style="margin-left: 1.5rem; color: #6b7280;">Bayar saat barang diterima</small>
                        </label>
                        <label class="payment-option">
                            <input type="radio" name="metode_pembayaran" value="e-wallet" required>
                            <strong>E-Wallet</strong><br>
                            <small style="margin-left: 1.5rem; color: #6b7280;">GoPay / OVO / Dana / ShopeePay</small>
                        </label>
                    </div>
                </form>
            </div>

            <div class="order-summary">
                <h3 style="font-size: 1.5rem; font-weight: 700; margin-bottom: 1.5rem; padding-bottom: 1rem; border-bottom: 2px solid #e5e7eb;">Ringkasan Pesanan</h3>
                
                <div style="max-height: 300px; overflow-y: auto; margin-bottom: 1rem;">
                    <?php foreach ($cart_items_array as $item): ?>
                        <div class="summary-item">
                            <div style="flex: 1;">
                                <div class="item-name"><?php echo htmlspecialchars($item['nama_produk']); ?></div>
                                <div class="item-qty"><?php echo $item['jumlah']; ?> x Rp <?php echo number_format($item['harga'], 0, ',', '.'); ?></div>
                            </div>
                            <div class="item-price">Rp <?php echo number_format($item['subtotal'], 0, ',', '.'); ?></div>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <div class="summary-row">
                    <span>Subtotal (<?php echo $total_items; ?> produk):</span>
                    <strong>Rp <?php echo number_format($total_harga, 0, ',', '.'); ?></strong>
                </div>
                <div class="summary-row">
                    <span>Ongkir:</span>
                    <strong style="color: #10b981;">GRATIS</strong>
                </div>
                <div class="summary-total">
                    <span>Total:</span>
                    <span class="amount">Rp <?php echo number_format($total_harga, 0, ',', '.'); ?></span>
                </div>
                
                <button type="submit" form="checkoutForm" class="btn" style="margin-top: 1.5rem;">
                    <i class="fas fa-check-circle"></i> Buat Pesanan
                </button>
            </div>
        </div>
    </div>

    <script>
        document.querySelectorAll('.payment-option').forEach(opt => {
            opt.addEventListener('click', function() {
                document.querySelectorAll('.payment-option').forEach(o => o.classList.remove('selected'));
                this.classList.add('selected');
                this.querySelector('input').checked = true;
            });
        });

        document.getElementById('checkoutForm').addEventListener('submit', function(e) {
            if (!document.querySelector('input[name="metode_pembayaran"]:checked')) {
                e.preventDefault();
                alert('Silakan pilih metode pembayaran!');
                return false;
            }
            return confirm('Lanjutkan membuat pesanan?');
        });
    </script>

    <?php
    if (isset($stmt)) $stmt->close();
    if (isset($conn)) $conn->close();
    ?>
</body>
</html>