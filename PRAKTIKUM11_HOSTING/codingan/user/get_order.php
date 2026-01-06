<?php
require_once '../config.php';
header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Tidak terautentikasi']);
    exit;
}

try {
    $conn = getConnection();
    $user_id = $_SESSION['user_id'];
    
    $stmt = $conn->prepare("
        SELECT * FROM transaksi 
        WHERE id_user = ? 
        ORDER BY tanggal_transaksi DESC
    ");
    
    $stmt->execute([$user_id]);
    $orders = $stmt->fetchAll();
    
    echo json_encode([
        'success' => true,
        'orders' => $orders
    ]);
    
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Terjadi kesalahan',
        'orders' => []
    ]);
}
?>