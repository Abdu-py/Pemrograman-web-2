<?php
session_start();
require_once '../config/database.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Tidak terautentikasi']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];
    $nama = trim($_POST['nama'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $no_hp = trim($_POST['no_hp'] ?? '');
    $alamat = trim($_POST['alamat'] ?? '');
    
    if (empty($nama) || empty($email)) {
        echo json_encode(['success' => false, 'message' => 'Nama dan email harus diisi']);
        exit;
    }
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['success' => false, 'message' => 'Format email tidak valid']);
        exit;
    }
    
    try {
        $conn = getConnection();
        
        // Check if email already used by other user
        $stmt = $conn->prepare("SELECT id_user FROM users WHERE email = ? AND id_user != ?");
        $stmt->bind_param("si", $email, $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            echo json_encode(['success' => false, 'message' => 'Email sudah digunakan pengguna lain']);
            exit;
        }
        
        // Update profile
        $stmt = $conn->prepare("
            UPDATE users 
            SET nama = ?, email = ?, no_hp = ?, alamat = ?
            WHERE id_user = ?
        ");
        $stmt->bind_param("ssssi", $nama, $email, $no_hp, $alamat, $user_id);
        
        if ($stmt->execute()) {
            // Update session
            $_SESSION['nama'] = $nama;
            $_SESSION['email'] = $email;
            
            echo json_encode(['success' => true, 'message' => 'Profil berhasil diupdate']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Gagal mengupdate profil']);
        }
        
        $stmt->close();
        $conn->close();
        
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Terjadi kesalahan sistem']);
    }
}
?>