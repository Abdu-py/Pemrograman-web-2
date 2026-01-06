<?php
/**
 * FILE: auth/process_register.php
 * DESKRIPSI: Backend untuk memproses registrasi pengguna baru
 * 
 * FLOW:
 * 1. Validasi input dari form
 * 2. Cek apakah email/username sudah terdaftar
 * 3. Hash password
 * 4. Insert data ke database
 * 5. Redirect ke halaman login dengan pesan sukses
 */

session_start();
require_once '../config/database.php';

// Cek apakah request method adalah POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: register.php');
    exit;
}

// Ambil dan bersihkan input dari form
$nama = cleanInput($_POST['nama']);
$email = cleanInput($_POST['email']);
$username = cleanInput($_POST['username']);
$password = $_POST['password'];
$confirm_password = $_POST['confirm_password'];
$no_hp = cleanInput($_POST['no_hp']);
$alamat = cleanInput($_POST['alamat']);

// Array untuk menyimpan error
$errors = [];

// ============================================
// VALIDASI INPUT
// ============================================

// Validasi Nama
if (empty($nama)) {
    $errors[] = "Nama lengkap harus diisi";
} elseif (strlen($nama) < 3) {
    $errors[] = "Nama minimal 3 karakter";
}

// Validasi Email
if (empty($email)) {
    $errors[] = "Email harus diisi";
} elseif (!isValidEmail($email)) {
    $errors[] = "Format email tidak valid";
}

// Validasi Username
if (empty($username)) {
    $errors[] = "Username harus diisi";
} elseif (strlen($username) < 4 || strlen($username) > 20) {
    $errors[] = "Username harus 4-20 karakter";
} elseif (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
    $errors[] = "Username hanya boleh huruf, angka, dan underscore";
}

// Validasi Password
if (empty($password)) {
    $errors[] = "Password harus diisi";
} elseif (strlen($password) < 6) {
    $errors[] = "Password minimal 6 karakter";
}

// Validasi Konfirmasi Password
if ($password !== $confirm_password) {
    $errors[] = "Password dan konfirmasi password tidak sama";
}

// Validasi No HP
if (empty($no_hp)) {
    $errors[] = "Nomor HP harus diisi";
} elseif (!preg_match('/^[0-9]{10,15}$/', $no_hp)) {
    $errors[] = "Nomor HP harus 10-15 digit angka";
}

// Validasi Alamat
if (empty($alamat)) {
    $errors[] = "Alamat harus diisi";
} elseif (strlen($alamat) < 10) {
    $errors[] = "Alamat minimal 10 karakter";
}

// Jika ada error, redirect kembali ke form
if (!empty($errors)) {
    $_SESSION['error'] = implode(', ', $errors);
    
    // Simpan input lama untuk diisi ulang
    $_SESSION['old_nama'] = $nama;
    $_SESSION['old_email'] = $email;
    $_SESSION['old_username'] = $username;
    $_SESSION['old_no_hp'] = $no_hp;
    $_SESSION['old_alamat'] = $alamat;
    
    header('Location: register.php');
    exit;
}

// ============================================
// CEK EMAIL & USERNAME SUDAH TERDAFTAR
// ============================================

try {
    $conn = getConnection();
    
    // Cek email sudah terdaftar atau belum
    $stmt = $conn->prepare("SELECT id_user FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $_SESSION['error'] = "Email sudah terdaftar, gunakan email lain";
        $_SESSION['old_nama'] = $nama;
        $_SESSION['old_username'] = $username;
        $_SESSION['old_no_hp'] = $no_hp;
        $_SESSION['old_alamat'] = $alamat;
        
        header('Location: register.php');
        exit;
    }
    
    // Cek username sudah terdaftar atau belum
    $stmt = $conn->prepare("SELECT id_user FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $_SESSION['error'] = "Username sudah digunakan, pilih username lain";
        $_SESSION['old_nama'] = $nama;
        $_SESSION['old_email'] = $email;
        $_SESSION['old_no_hp'] = $no_hp;
        $_SESSION['old_alamat'] = $alamat;
        
        header('Location: register.php');
        exit;
    }
    
    // ============================================
    // INSERT DATA KE DATABASE
    // ============================================
    
    // Hash password
    $hashed_password = hashPassword($password);
    
    // Prepare INSERT statement
    $stmt = $conn->prepare("
        INSERT INTO users (nama, email, username, password, role, alamat, no_hp, status, created_at) 
        VALUES (?, ?, ?, ?, 'pelanggan', ?, ?, 'aktif', NOW())
    ");
    
    $stmt->bind_param("ssssss", $nama, $email, $username, $hashed_password, $alamat, $no_hp);
    
    if ($stmt->execute()) {
        // Registrasi berhasil
        $_SESSION['success'] = "Registrasi berhasil! Silakan login dengan akun Anda";
        
        // Redirect ke halaman login
        header('Location: login.php');
        exit;
    } else {
        // Gagal insert
        throw new Exception("Gagal menyimpan data: " . $stmt->error);
    }
    
} catch (Exception $e) {
    $_SESSION['error'] = "Terjadi kesalahan sistem: " . $e->getMessage();
    
    // Simpan input lama
    $_SESSION['old_nama'] = $nama;
    $_SESSION['old_email'] = $email;
    $_SESSION['old_username'] = $username;
    $_SESSION['old_no_hp'] = $no_hp;
    $_SESSION['old_alamat'] = $alamat;
    
    header('Location: register.php');
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