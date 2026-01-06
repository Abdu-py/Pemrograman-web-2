<?php
session_start();
require_once '../config/database.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Tidak terautentikasi']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Method tidak valid']);
    exit;
}

$order_id = isset($_POST['order_id']) ? intval($_POST['order_id']) : 0;
$user_id = $_SESSION['user_id'];

if ($order_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'ID pesanan tidak valid']);
    exit;
}

try {
    $conn = getConnection();
    
    // Verify order belongs to user
    $stmt = $conn->prepare("SELECT id_transaksi FROM transaksi WHERE id_transaksi = ? AND id_user = ?");
    $stmt->bind_param("ii", $order_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'Pesanan tidak ditemukan']);
        exit;
    }
    
    // Get order items
    $stmt = $conn->prepare("
        SELECT 
            dt.id_produk,
            dt.jumlah,
            dt.harga_satuan,
            p.stok,
            p.harga as harga_sekarang,
            p.status
        FROM detail_transaksi dt
        JOIN produk p ON dt.id_produk = p.id_produk
        WHERE dt.id_transaksi = ?
    ");
    
    $stmt->bind_param("i", $order_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $items = [];
    while ($row = $result->fetch_assoc()) {
        $items[] = $row;
    }
    
    if (empty($items)) {
        echo json_encode(['success' => false, 'message' => 'Tidak ada produk dalam pesanan']);
        exit;
    }
    
    $added = 0;
    $errors = [];
    
    foreach ($items as $item) {
        // Check if product is still available
        if ($item['status'] !== 'tersedia') {
            $errors[] = "Produk tidak tersedia lagi";
            continue;
        }
        
        // Check stock
        $qty_to_add = min($item['jumlah'], $item['stok']);
        
        if ($qty_to_add <= 0) {
            $errors[] = "Stok habis";
            continue;
        }
        
        // Check if already in cart
        $stmt = $conn->prepare("SELECT id_keranjang, jumlah FROM keranjang WHERE id_user = ? AND id_produk = ?");
        $stmt->bind_param("ii", $user_id, $item['id_produk']);
        $stmt->execute();
        $cart_result = $stmt->get_result();
        $existing = $cart_result->fetch_assoc();
        
        if ($existing) {
            // Update existing cart item
            $new_qty = min($existing['jumlah'] + $qty_to_add, $item['stok']);
            $stmt = $conn->prepare("UPDATE keranjang SET jumlah = ? WHERE id_keranjang = ?");
            $stmt->bind_param("ii", $new_qty, $existing['id_keranjang']);
            $stmt->execute();
        } else {
            // Add new cart item
            $stmt = $conn->prepare("INSERT INTO keranjang (id_user, id_produk, jumlah) VALUES (?, ?, ?)");
            $stmt->bind_param("iii", $user_id, $item['id_produk'], $qty_to_add);
            $stmt->execute();
        }
        
        $added++;
    }
    
    if ($added > 0) {
        $message = "$added produk berhasil ditambahkan ke keranjang";
        if (!empty($errors)) {
            $message .= ". Beberapa produk tidak dapat ditambahkan.";
        }
        
        echo json_encode([
            'success' => true,
            'message' => $message
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Tidak ada produk yang dapat ditambahkan ke keranjang'
        ]);
    }
    
    $stmt->close();
    $conn->close();
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Terjadi kesalahan sistem: ' . $e->getMessage()
    ]);
}
?>
```

**Ringkasan Perbaikan:**

1. ✅ **pesanan.php** - Mengubah semua reference ke `config.php` menjadi `config/database.php`
2. ✅ **get_orders.php** - File baru dengan koneksi database yang benar
3. ✅ **get_order_details.php** - File baru untuk detail pesanan dengan JOIN ke tabel produk
4. ✅ **reorder.php** - File baru untuk fitur pesan ulang dengan validasi stok
5. ✅ Semua file menggunakan **mysqli** konsisten dengan struktur yang ada
6. ✅ Proper **error handling** dan **session checking**
7. ✅ **JSON response** untuk komunikasi AJAX

**Struktur folder yang benar:**
```
republik-computer/
├── config/
│   └── database.php
├── user/
│   ├── beranda.php
│   ├── keranjang.php
│   ├── profil.php
│   ├── pesanan.php          ← Fixed
│   ├── get_user_data.php
│   ├── update_profil.php
│   ├── change_password.php
│   ├── get_orders.php        ← New
│   ├── get_order_details.php ← New
│   └── reorder.php           ← New