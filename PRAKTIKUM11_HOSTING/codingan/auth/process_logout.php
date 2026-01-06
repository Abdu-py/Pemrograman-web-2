<?php
/**
 * Process Logout Backend
 * File: process_logout.php
 * Location: root folder (republik_computer/)
 */

// Start session
session_start();

// Set header untuk JSON response
header('Content-Type: application/json');

try {
    // Simpan informasi user sebelum logout (untuk logging)
    $user_id = $_SESSION['user_id'] ?? null;
    $username = $_SESSION['username'] ?? null;
    
    // Log logout activity (opsional)
    if ($user_id) {
        try {
            require_once 'config.php';
            $conn = getConnection();
            
            // Bisa ditambahkan log logout ke database jika diperlukan
            $stmt = $conn->prepare("
                INSERT INTO login_log (id_user, waktu_login, ip_address) 
                VALUES (?, NOW(), ?)
            ");
            // Note: Untuk logout bisa dibuat tabel terpisah atau kolom status
            
        } catch (PDOException $e) {
            // Ignore error, tetap lanjutkan logout
        }
    }
    
    // Unset all session variables
    $_SESSION = array();
    
    // Destroy the session cookie
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(
            session_name(), 
            '', 
            time() - 42000,
            $params["path"], 
            $params["domain"],
            $params["secure"], 
            $params["httponly"]
        );
    }
    
    // Destroy the session
    session_destroy();
    
    // Clear any additional cookies (jika ada)
    if (isset($_COOKIE['remember_token'])) {
        setcookie('remember_token', '', time() - 3600, '/');
    }
    
    // Return success response
    echo json_encode([
        'success' => true,
        'message' => 'Logout berhasil',
        'logged_out' => true,
        'timestamp' => date('Y-m-d H:i:s')
    ]);
    
} catch (Exception $e) {
    // Jika ada error, tetap response success agar user bisa logout
    echo json_encode([
        'success' => true,
        'message' => 'Logout berhasil',
        'logged_out' => true
    ]);
}

exit;
?>