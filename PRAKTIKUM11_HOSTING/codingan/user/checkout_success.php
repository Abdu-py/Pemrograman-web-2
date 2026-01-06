<?php
/**
 * FILE: user/checkout_success.php
 * DESKRIPSI: Halaman konfirmasi setelah checkout berhasil
 * LOKASI: republik-computer/user/checkout_success.php
 */

session_start();
require_once '../config/database.php';

// Cek apakah sudah login
if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$order_id = isset($_GET['order']) ? intval($_GET['order']) : 0;

if ($order_id <= 0) {
    header('Location: beranda.php');
    exit;
}

// Ambil data transaksi
try {
    $conn = getConnection();
    
    $stmt = $conn->prepare("
        SELECT * FROM transaksi 
        WHERE id_transaksi = ? AND id_user = ?
    ");
    
    $stmt->bind_param("ii", $order_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        $_SESSION['error'] = "Pesanan tidak ditemukan";
        header('Location: beranda.php');
        exit;
    }
    
    $order = $result->fetch_assoc();
    
} catch (Exception $e) {
    $_SESSION['error'] = "Terjadi kesalahan";
    header('Location: beranda.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pesanan Berhasil - Republik Computer</title>
    
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        body {
            background: #f3f4f6;
        }
        
        .success-container {
            max-width: 600px;
            margin: 4rem auto;
            padding: 0 20px;
        }
        
        .success-card {
            background: white;
            border-radius: 1rem;
            padding: 3rem 2rem;
            text-align: center;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        
        .success-icon {
            width: 100px;
            height: 100px;
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 2rem;
            font-size: 3rem;
            color: white;
        }
        
        .success-card h1 {
            font-size: 2rem;
            color: #1f2937;
            margin-bottom: 1rem;
        }
        
        .success-card p {
            color: #6b7280;
            margin-bottom: 2rem;
            font-size: 1.125rem;
        }
        
        .order-info {
            background: #f9fafb;
            padding: 1.5rem;
            border-radius: 0.5rem;
            margin-bottom: 2rem;
            text-align: left;
        }
        
        .info-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 0.75rem;
            padding-bottom: 0.75rem;
            border-bottom: 1px solid #e5e7eb;
        }
        
        .info-row:last-child {
            border-bottom: none;
            margin-bottom: 0;
            padding-bottom: 0;
        }
        
        .info-label {
            color: #6b7280;
        }
        
        .info-value {
            font-weight: 600;
            color: #1f2937;
        }
        
        .btn-group {
            display: flex;
            gap: 1rem;
            justify-content: center;
        }
    </style>
</head>
<body>
    <div class="success-container">
        <div class="success-card">
            <div class="success-icon">
                <i class="fas fa-check"></i>
            </div>
            
            <h1>Pesanan Berhasil Dibuat!</h1>
            <p>Terima kasih telah berbelanja di Republik Computer</p>
            
            <div class="order-info">
                <div class="info-row">
                    <span class="info-label">Kode Transaksi:</span>
                    <span class="info-value"><?php echo htmlspecialchars($order['kode_transaksi']); ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">Total Pembayaran:</span>
                    <span class="info-value" style="color: #2563eb;">
                        Rp <?php echo number_format($order['total_bayar'], 0, ',', '.'); ?>
                    </span>
                </div>
                <div class="info-row">
                    <span class="info-label">Metode Pembayaran:</span>
                    <span class="info-value"><?php echo ucwords(str_replace('_', ' ', $order['metode_pembayaran'])); ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">Status:</span>
                    <span class="info-value" style="color: #f59e0b;">Menunggu Pembayaran</span>
                </div>
            </div>
            
            <div class="btn-group">
                <a href="pesanan.php" class="btn btn-primary">
                    <i class="fas fa-box"></i> Lihat Pesanan
                </a>
                <a href="beranda.php" class="btn btn-secondary">
                    <i class="fas fa-home"></i> Kembali ke Beranda
                </a>
            </div>
        </div>
    </div>
    
    <?php
    if (isset($stmt)) $stmt->close();
    if (isset($conn)) $conn->close();
    ?>
</body>
</html>