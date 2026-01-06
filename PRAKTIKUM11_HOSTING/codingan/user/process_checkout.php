<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user_id'])) {
    $_SESSION['error'] = "Anda harus login terlebih dahulu";
    header('Location: ../auth/login.php');
    exit;
}

if ($_SESSION['role'] !== 'pelanggan') {
    $_SESSION['error'] = "Akses ditolak";
    header('Location: ../admin/dashboard.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: checkout.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$alamat_pengiriman = cleanInput($_POST['alamat_pengiriman']);
$catatan = isset($_POST['catatan']) ? cleanInput($_POST['catatan']) : '';
$metode_pembayaran = cleanInput($_POST['metode_pembayaran']);

if (empty($alamat_pengiriman) || empty($metode_pembayaran)) {
    $_SESSION['error'] = "Alamat pengiriman dan metode pembayaran wajib diisi!";
    header('Location: checkout.php');
    exit;
}

try {
    $conn = getConnection();
    $conn->begin_transaction();
    
    // ============================================
    // 1. CEK KERANJANG
    // ============================================
    
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
    $cart_items = $stmt->get_result();
    
    if ($cart_items->num_rows === 0) {
        throw new Exception("Keranjang kosong!");
    }
    
    // Validasi stok dan hitung total
    $total_bayar = 0;
    $items = [];
    
    while ($item = $cart_items->fetch_assoc()) {
        if ($item['stok'] < $item['jumlah']) {
            throw new Exception("Stok " . $item['nama_produk'] . " tidak mencukupi!");
        }
        
        $total_bayar += $item['subtotal'];
        $items[] = $item;
    }
    
    // ============================================
    // 2. BUAT KODE TRANSAKSI UNIK
    // ============================================
    
    $kode_transaksi = 'TRX' . date('Ymd') . rand(1000, 9999);
    
    $check_stmt = $conn->prepare("SELECT id_transaksi FROM transaksi WHERE kode_transaksi = ?");
    $check_stmt->bind_param("s", $kode_transaksi);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    
    while ($check_result->num_rows > 0) {
        $kode_transaksi = 'TRX' . date('Ymd') . rand(1000, 9999);
        $check_stmt->bind_param("s", $kode_transaksi);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();
    }
    $check_stmt->close();
    
    // ============================================
    // 3. INSERT TRANSAKSI
    // ============================================
    
    $status = 'menunggu';
    
    $stmt = $conn->prepare("
        INSERT INTO transaksi (
            kode_transaksi, 
            id_user, 
            tanggal_transaksi,
            total_bayar,
            metode_pembayaran,
            status,
            alamat_pengiriman,
            catatan
        ) VALUES (?, ?, NOW(), ?, ?, ?, ?, ?)
    ");
    
    $stmt->bind_param(
        "sidssss",
        $kode_transaksi,
        $user_id,
        $total_bayar,
        $metode_pembayaran,
        $status,
        $alamat_pengiriman,
        $catatan
    );
    
    if (!$stmt->execute()) {
        throw new Exception("Gagal membuat transaksi: " . $stmt->error);
    }
    
    $id_transaksi = $conn->insert_id;
    
    // ============================================
    // 4. INSERT DETAIL TRANSAKSI (GUNAKAN 'qty')
    // ============================================
    
    $stmt = $conn->prepare("
        INSERT INTO detail_transaksi (
            id_transaksi,
            id_produk,
            nama_produk,
            qty,
            harga,
            subtotal
        ) VALUES (?, ?, ?, ?, ?, ?)
    ");
    
    foreach ($items as $item) {
        $stmt->bind_param(
            "iisidd",
            $id_transaksi,
            $item['id_produk'],
            $item['nama_produk'],
            $item['jumlah'],
            $item['harga'],
            $item['subtotal']
        );
        
        if (!$stmt->execute()) {
            throw new Exception("Gagal menyimpan detail transaksi: " . $stmt->error);
        }
        
        // Update stok produk
        $update_stmt = $conn->prepare("
            UPDATE produk 
            SET stok = stok - ?
            WHERE id_produk = ?
        ");
        
        $update_stmt->bind_param("ii", $item['jumlah'], $item['id_produk']);
        
        if (!$update_stmt->execute()) {
            throw new Exception("Gagal update stok produk: " . $update_stmt->error);
        }
        
        $update_stmt->close();
    }
    
    // ============================================
    // 5. HAPUS KERANJANG
    // ============================================
    
    $stmt = $conn->prepare("DELETE FROM keranjang WHERE id_user = ?");
    $stmt->bind_param("i", $user_id);
    
    if (!$stmt->execute()) {
        throw new Exception("Gagal menghapus keranjang");
    }
    
    // ============================================
    // 6. COMMIT TRANSACTION
    // ============================================
    
    $conn->commit();
    
    $_SESSION['success'] = "Pesanan berhasil dibuat! Kode transaksi: " . $kode_transaksi;
    $_SESSION['last_order_id'] = $id_transaksi;
    
    header('Location: checkout_success.php?order=' . $id_transaksi);
    exit;
    
} catch (Exception $e) {
    if (isset($conn)) {
        $conn->rollback();
    }
    
    $_SESSION['error'] = "Terjadi kesalahan: " . $e->getMessage();
    header('Location: checkout.php');
    exit;
    
} finally {
    if (isset($stmt)) $stmt->close();
    if (isset($conn)) $conn->close();
}
?>