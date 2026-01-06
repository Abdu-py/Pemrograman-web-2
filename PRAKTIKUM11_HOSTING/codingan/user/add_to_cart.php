<?php
/**
 * FILE: user/add_to_cart.php
 * DESKRIPSI: Menambahkan produk ke keranjang
 * LOKASI: republik-computer/user/add_to_cart.php
 */

session_start();
require_once '../config/database.php';

// DEBUGGING: Uncomment baris di bawah untuk debugging
// error_reporting(E_ALL);
// ini_set('display_errors', 1);

// Cek apakah user sudah login
if (!isset($_SESSION['user_id'])) {
    $_SESSION['error'] = "Anda harus login terlebih dahulu";
    header('Location: ../auth/login.php');
    exit;
}

// Cek role (hanya pelanggan yang bisa add to cart)
if ($_SESSION['role'] !== 'pelanggan') {
    $_SESSION['error'] = "Hanya pelanggan yang bisa menambahkan ke keranjang";
    header('Location: beranda.php');
    exit;
}

// Ambil data dari request
$id_produk = isset($_GET['id']) ? intval($_GET['id']) : 0;
$jumlah = isset($_GET['qty']) ? intval($_GET['qty']) : 1;
$user_id = $_SESSION['user_id'];

// Validasi input
if ($id_produk <= 0) {
    $_SESSION['error'] = "ID produk tidak valid";
    header('Location: beranda.php');
    exit;
}

if ($jumlah <= 0) {
    $jumlah = 1;
}

try {
    $conn = getConnection();
    
    // ============================================
    // CEK PRODUK ADA DAN STOK CUKUP
    // ============================================
    
    $stmt = $conn->prepare("SELECT * FROM produk WHERE id_produk = ? AND status = 'tersedia'");
    $stmt->bind_param("i", $id_produk);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        $_SESSION['error'] = "Produk tidak ditemukan atau tidak tersedia";
        header('Location: beranda.php');
        exit;
    }
    
    $produk = $result->fetch_assoc();
    
    // Cek stok
    if ($produk['stok'] < $jumlah) {
        $_SESSION['error'] = "Stok tidak mencukupi! Stok tersedia: " . $produk['stok'];
        header('Location: beranda.php');
        exit;
    }
    
    // ============================================
    // CEK APAKAH PRODUK SUDAH ADA DI KERANJANG
    // ============================================
    
    $stmt = $conn->prepare("SELECT * FROM keranjang WHERE id_user = ? AND id_produk = ?");
    $stmt->bind_param("ii", $user_id, $id_produk);
    $stmt->execute();
    $cart_result = $stmt->get_result();
    
    if ($cart_result->num_rows > 0) {
        // ============================================
        // UPDATE JUMLAH JIKA SUDAH ADA
        // ============================================
        
        $cart_item = $cart_result->fetch_assoc();
        $new_qty = $cart_item['jumlah'] + $jumlah;
        
        // Cek apakah qty baru tidak melebihi stok
        if ($new_qty > $produk['stok']) {
            $_SESSION['error'] = "Tidak bisa menambah! Total melebihi stok tersedia (" . $produk['stok'] . ")";
            header('Location: beranda.php');
            exit;
        }
        
        // Update quantity
        $update_stmt = $conn->prepare("
            UPDATE keranjang 
            SET jumlah = ?
            WHERE id_keranjang = ?
        ");
        $update_stmt->bind_param("ii", $new_qty, $cart_item['id_keranjang']);
        
        if ($update_stmt->execute()) {
            $_SESSION['success'] = "Jumlah produk di keranjang berhasil diperbarui!";
        } else {
            throw new Exception("Gagal update keranjang: " . $update_stmt->error);
        }
        
        $update_stmt->close();
        
    } else {
        // ============================================
        // INSERT BARU JIKA BELUM ADA
        // ============================================
        
        $insert_stmt = $conn->prepare("
            INSERT INTO keranjang (id_user, id_produk, jumlah) 
            VALUES (?, ?, ?)
        ");
        $insert_stmt->bind_param("iii", $user_id, $id_produk, $jumlah);
        
        if ($insert_stmt->execute()) {
            $_SESSION['success'] = "Produk berhasil ditambahkan ke keranjang!";
        } else {
            throw new Exception("Gagal menambahkan ke keranjang: " . $insert_stmt->error);
        }
        
        $insert_stmt->close();
    }
    
    // Redirect kembali ke halaman sebelumnya
    header('Location: beranda.php');
    exit;
    
} catch (Exception $e) {
    // Log error (optional)
    error_log("Error add to cart: " . $e->getMessage());
    
    $_SESSION['error'] = "Terjadi kesalahan: " . $e->getMessage();
    header('Location: beranda.php');
    exit;
    
} finally {
    // Tutup koneksi
    if (isset($stmt)) {
        $stmt->close();
    }
    if (isset($conn)) {
        $conn->close();
    }
}
?>