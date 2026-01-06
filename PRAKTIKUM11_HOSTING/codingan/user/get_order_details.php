<?php
session_start();
require_once '../config/database.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Tidak terautentikasi']);
    exit;
}

$order_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($order_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'ID tidak valid']);
    exit;
}

try {
    $conn = getConnection();
    $user_id = $_SESSION['user_id'];
    
    // Verify order belongs to user
    $stmt = $conn->prepare("SELECT id_transaksi FROM transaksi WHERE id_transaksi = ? AND id_user = ?");
    $stmt->bind_param("ii", $order_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'Pesanan tidak ditemukan']);
        exit;
    }
    
    // Get order details with product info
    $stmt = $conn->prepare("
        SELECT 
            dt.*,
            p.nama_produk,
            p.foto,
            k.nama_kategori
        FROM detail_transaksi dt
        LEFT JOIN produk p ON dt.id_produk = p.id_produk
        LEFT JOIN kategori k ON p.id_kategori = k.id_kategori
        WHERE dt.id_transaksi = ?
    ");
    
    $stmt->bind_param("i", $order_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $items = [];
    while ($row = $result->fetch_assoc()) {
        $items[] = $row;
    }
    
    echo json_encode([
        'success' => true,
        'items' => $items
    ]);
    
    $stmt->close();
    $conn->close();
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Terjadi kesalahan sistem',
        'items' => []
    ]);
}
?>