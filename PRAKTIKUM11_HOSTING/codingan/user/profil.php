<?php
/**
 * FILE: user/profil.php
 * DESKRIPSI: Halaman profil pengguna
 * LOKASI: republik-computer/user/profil.php
 */

session_start();
require_once '../config/database.php';

// Cek apakah sudah login
if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.php');
    exit;
}

// Cek role (hanya pelanggan)
if ($_SESSION['role'] !== 'pelanggan') {
    header('Location: ../admin/dashboard.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['nama'];
$user_email = $_SESSION['email'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil Saya - Republik Computer</title>
    
    <!-- CSS -->
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        body {
            background: #f5f5f5;
        }
        
        .navbar {
            background: white;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            padding: 1rem 0;
        }
        
        .navbar-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .navbar-brand {
            font-size: 1.5rem;
            font-weight: 700;
            color: #2563eb;
            text-decoration: none;
        }
        
        .navbar-menu {
            display: flex;
            gap: 2rem;
        }
        
        .navbar-menu a {
            color: #374151;
            text-decoration: none;
            font-weight: 500;
            transition: color 0.3s;
        }
        
        .navbar-menu a:hover {
            color: #2563eb;
        }
        
        .profile-container {
            max-width: 1200px;
            margin: 40px auto;
            padding: 0 20px;
            display: grid;
            grid-template-columns: 300px 1fr;
            gap: 30px;
        }
        
        .sidebar {
            background: white;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            height: fit-content;
        }
        
        .profile-avatar {
            width: 120px;
            height: 120px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 48px;
            margin: 0 auto 20px;
        }
        
        .profile-name {
            text-align: center;
            font-size: 22px;
            color: #333;
            margin-bottom: 10px;
            font-weight: 600;
        }
        
        .profile-email {
            text-align: center;
            color: #666;
            font-size: 14px;
            margin-bottom: 30px;
        }
        
        .menu-list {
            list-style: none;
        }
        
        .menu-item {
            margin-bottom: 10px;
        }
        
        .menu-item a {
            display: block;
            padding: 15px 20px;
            color: #333;
            text-decoration: none;
            border-radius: 10px;
            transition: all 0.3s;
        }
        
        .menu-item a:hover,
        .menu-item a.active {
            background: rgba(102, 126, 234, 0.1);
            color: #667eea;
        }
        
        .main-content {
            background: white;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        
        .section-title {
            font-size: 24px;
            color: #333;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid #f0f0f0;
        }
        
        .tab-content {
            display: none;
        }
        
        .tab-content.active {
            display: block;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #333;
            font-weight: 600;
            font-size: 14px;
        }
        
        .form-group input,
        .form-group textarea {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            font-size: 14px;
            transition: all 0.3s;
            font-family: inherit;
        }
        
        .form-group textarea {
            resize: vertical;
            min-height: 100px;
        }
        
        .form-group input:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }
        
        .form-group input:disabled {
            background: #f5f5f5;
            cursor: not-allowed;
        }
        
        .btn-group {
            display: flex;
            gap: 10px;
        }
        
        .alert {
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
            font-size: 14px;
        }
        
        .alert-success {
            background: #d1fae5;
            color: #065f46;
            border: 1px solid #6ee7b7;
        }
        
        .alert-error {
            background: #fee2e2;
            color: #991b1b;
            border: 1px solid #fecaca;
        }
        
        .order-card {
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            transition: all 0.3s;
        }
        
        .order-card:hover {
            border-color: #667eea;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .order-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }
        
        .order-status {
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }
        
        .status-menunggu {
            background: #fef3c7;
            color: #92400e;
        }
        
        .status-diproses {
            background: #dbeafe;
            color: #1e40af;
        }
        
        .status-selesai {
            background: #d1fae5;
            color: #065f46;
        }
        
        .status-batal {
            background: #fee2e2;
            color: #991b1b;
        }
        
        @media (max-width: 768px) {
            .profile-container {
                grid-template-columns: 1fr;
            }
            
            .form-row {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar">
        <div class="container navbar-container">
            <a href="beranda.php" class="navbar-brand">
                <i class="fas fa-laptop"></i> Republik Computer
            </a>
            <div class="navbar-menu">
                <a href="beranda.php"><i class="fas fa-home"></i> Beranda</a>
                <a href="keranjang.php"><i class="fas fa-shopping-cart"></i> Keranjang</a>
                <a href="profil.php"><i class="fas fa-user"></i> Profil</a>
            </div>
        </div>
    </nav>
    
    <div class="profile-container">
        <!-- Sidebar -->
        <div class="sidebar">
            <div class="profile-avatar">
                <i class="fas fa-user"></i>
            </div>
            <div class="profile-name"><?php echo htmlspecialchars($user_name); ?></div>
            <div class="profile-email"><?php echo htmlspecialchars($user_email); ?></div>
            
            <ul class="menu-list">
                <li class="menu-item">
                    <a href="#" class="active" onclick="showTab('profil', event)">
                        <i class="fas fa-user-circle"></i> Profil Saya
                    </a>
                </li>
                <li class="menu-item">
                    <a href="pesanan.php">
                        <i class="fas fa-box"></i> Pesanan Saya
                    </a>
                </li>
                <li class="menu-item">
                    <a href="#" onclick="showTab('password', event)">
                        <i class="fas fa-lock"></i> Ganti Password
                    </a>
                </li>
                <li class="menu-item">
                    <a href="../auth/logout.php" style="color: #ef4444;">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </a>
                </li>
            </ul>
        </div>
        
        <!-- Main Content -->
        <div class="main-content">
            <!-- Tab Profil -->
            <div id="tab-profil" class="tab-content active">
                <h2 class="section-title">Informasi Profil</h2>
                
                <div id="alert-profil"></div>
                
                <form id="formProfil">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="nama">Nama Lengkap *</label>
                            <input type="text" id="nama" name="nama" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="email">Email *</label>
                            <input type="email" id="email" name="email" required>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="username">Username</label>
                            <input type="text" id="username" name="username" disabled>
                        </div>
                        
                        <div class="form-group">
                            <label for="no_hp">No. HP</label>
                            <input type="tel" id="no_hp" name="no_hp">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="alamat">Alamat Lengkap</label>
                        <textarea id="alamat" name="alamat"></textarea>
                    </div>
                    
                    <div class="btn-group">
                        <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                        <button type="button" class="btn btn-secondary" onclick="loadUserData()">Batal</button>
                    </div>
                </form>
            </div>
            
            <!-- Tab Password -->
            <div id="tab-password" class="tab-content">
                <h2 class="section-title">Ganti Password</h2>
                
                <div id="alert-password"></div>
                
                <form id="formPassword">
                    <div class="form-group">
                        <label for="current_password">Password Lama *</label>
                        <input type="password" id="current_password" name="current_password" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="new_password">Password Baru *</label>
                        <input type="password" id="new_password" name="new_password" required minlength="6">
                        <small style="color: #666; font-size: 12px;">Minimal 6 karakter</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="confirm_password">Konfirmasi Password Baru *</label>
                        <input type="password" id="confirm_password" required>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">Ganti Password</button>
                </form>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            loadUserData();
        });
        
        async function loadUserData() {
            try {
                const response = await fetch('get_user_data.php');
                const data = await response.json();
                
                if (data.success) {
                    document.getElementById('nama').value = data.user.nama || '';
                    document.getElementById('email').value = data.user.email || '';
                    document.getElementById('username').value = data.user.username || '';
                    document.getElementById('no_hp').value = data.user.no_hp || '';
                    document.getElementById('alamat').value = data.user.alamat || '';
                }
            } catch (error) {
                console.error('Error loading user data:', error);
            }
        }
        
        function showTab(tabName, event) {
            if (event) {
                event.preventDefault();
            }
            
            // Hide all tabs
            document.querySelectorAll('.tab-content').forEach(tab => {
                tab.classList.remove('active');
            });
            
            // Remove active from menu items
            document.querySelectorAll('.menu-item a').forEach(link => {
                link.classList.remove('active');
            });
            
            // Show selected tab
            document.getElementById('tab-' + tabName).classList.add('active');
            
            // Add active to clicked menu item
            if (event) {
                event.target.classList.add('active');
            }
        }
        
        // Handle profile form submit
        document.getElementById('formProfil').addEventListener('submit', async (e) => {
            e.preventDefault();
            
            const formData = new FormData(e.target);
            const alertDiv = document.getElementById('alert-profil');
            
            try {
                const response = await fetch('update_profil.php', {
                    method: 'POST',
                    body: formData
                });
                
                const result = await response.json();
                
                if (result.success) {
                    alertDiv.innerHTML = '<div class="alert alert-success">Profil berhasil diupdate!</div>';
                    setTimeout(() => {
                        alertDiv.innerHTML = '';
                        location.reload();
                    }, 2000);
                } else {
                    alertDiv.innerHTML = `<div class="alert alert-error">${result.message}</div>`;
                }
            } catch (error) {
                console.error('Error:', error);
                alertDiv.innerHTML = '<div class="alert alert-error">Terjadi kesalahan sistem</div>';
            }
        });
        
        // Handle password form submit
        document.getElementById('formPassword').addEventListener('submit', async (e) => {
            e.preventDefault();
            
            const formData = new FormData(e.target);
            const alertDiv = document.getElementById('alert-password');
            
            // Validasi password match
            const newPassword = document.getElementById('new_password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            
            if (newPassword !== confirmPassword) {
                alertDiv.innerHTML = '<div class="alert alert-error">Password baru tidak cocok!</div>';
                return;
            }
            
            try {
                const response = await fetch('change_password.php', {
                    method: 'POST',
                    body: formData
                });
                
                const result = await response.json();
                
                if (result.success) {
                    alertDiv.innerHTML = '<div class="alert alert-success">Password berhasil diubah!</div>';
                    e.target.reset();
                    setTimeout(() => {
                        alertDiv.innerHTML = '';
                    }, 3000);
                } else {
                    alertDiv.innerHTML = `<div class="alert alert-error">${result.message}</div>`;
                }
            } catch (error) {
                console.error('Error:', error);
                alertDiv.innerHTML = '<div class="alert alert-error">Terjadi kesalahan sistem</div>';
            }
        });
    </script>
</body>
</html>