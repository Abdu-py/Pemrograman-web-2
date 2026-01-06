<?php
/**
 * FILE: auth/process_login.php
 * DESKRIPSI: Backend untuk memproses login pengguna
 * 
 * FLOW:
 * 1. Validasi input
 * 2. Cari user berdasarkan username atau email
 * 3. Verifikasi password
 * 4. Buat session
 * 5. Log login activity
 * 6. Redirect sesuai role
 */

session_start();
require_once '../config/database.php';

// Cek apakah request method adalah POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: login.php');
    exit;
}

// Ambil dan bersihkan input
$login_id = cleanInput($_POST['login_id']); // bisa username atau email
$password = $_POST['password'];
$remember = isset($_POST['remember']) ? true : false;

// Validasi input kosong
if (empty($login_id) || empty($password)) {
    $_SESSION['error'] = "Username/Email dan Password harus diisi";
    header('Location: login.php');
    exit;
}

try {
    $conn = getConnection();
    
    // ============================================
    // CARI USER BERDASARKAN USERNAME ATAU EMAIL
    // ============================================
    
    $stmt = $conn->prepare("
        SELECT id_user, nama, email, username, password, role, status, foto_profil
        FROM users 
        WHERE (username = ? OR email = ?)
        LIMIT 1
    ");
    
    $stmt->bind_param("ss", $login_id, $login_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    // Cek apakah user ditemukan
    if ($result->num_rows === 0) {
        $_SESSION['error'] = "Username/Email atau Password salah";
        header('Location: login.php');
        exit;
    }
    
    $user = $result->fetch_assoc();
    
    // ============================================
    // VERIFIKASI PASSWORD
    // ============================================
    
    if (!verifyPassword($password, $user['password'])) {
        $_SESSION['error'] = "Username/Email atau Password salah";
        header('Location: login.php');
        exit;
    }
    
    // ============================================
    // CEK STATUS AKUN
    // ============================================
    
    if ($user['status'] !== 'aktif') {
        $_SESSION['error'] = "Akun Anda tidak aktif. Hubungi administrator";
        header('Location: login.php');
        exit;
    }
    
    // ============================================
    // BUAT SESSION
    // ============================================
    
    $_SESSION['user_id'] = $user['id_user'];
    $_SESSION['nama'] = $user['nama'];
    $_SESSION['email'] = $user['email'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['role'] = $user['role'];
    $_SESSION['foto_profil'] = $user['foto_profil'];
    $_SESSION['login_time'] = time();
    
    // Set cookie jika remember me dicentang
    if ($remember) {
        // Cookie akan bertahan 30 hari
        $cookie_value = base64_encode($user['id_user'] . '|' . $user['username']);
        setcookie('remember_user', $cookie_value, time() + (30 * 24 * 60 * 60), '/');
    }
    
    // ============================================
    // LOG LOGIN ACTIVITY
    // ============================================
    
    $ip_address = $_SERVER['REMOTE_ADDR'];
    $user_agent = $_SERVER['HTTP_USER_AGENT'];
    
    $log_stmt = $conn->prepare("
        INSERT INTO login_log (id_user, waktu_login, ip_address, user_agent) 
        VALUES (?, NOW(), ?, ?)
    ");
    
    $log_stmt->bind_param("iss", $user['id_user'], $ip_address, $user_agent);
    $log_stmt->execute();
    
    // ============================================
    // REDIRECT SESUAI ROLE
    // ============================================
    
    switch ($user['role']) {
        case 'admin':
            $_SESSION['success'] = "Selamat datang, Administrator!";
            header('Location: ../admin/dashboard.php');
            break;
            
        case 'kepala_toko':
            $_SESSION['success'] = "Selamat datang, Kepala Toko!";
            header('Location: ../admin/dashboard.php');
            break;
            
        case 'pelanggan':
        default:
            $_SESSION['success'] = "Selamat datang, " . $user['nama'] . "!";
            header('Location: ../user/beranda.php');
            break;
    }
    
    exit;
    
} catch (Exception $e) {
    $_SESSION['error'] = "Terjadi kesalahan sistem: " . $e->getMessage();
    header('Location: login.php');
    exit;
    
} finally {
    // Tutup koneksi
    if (isset($stmt)) {
        $stmt->close();
    }
    if (isset($log_stmt)) {
        $log_stmt->close();
    }
    if (isset($conn)) {
        $conn->close();
    }
}
?>