<?php
/**
 * FILE: auth/register.php
 * DESKRIPSI: Halaman registrasi untuk pelanggan baru
 * 
 * CARA PAKAI:
 * 1. Pastikan file config/database.php sudah ada
 * 2. Pastikan database sudah dibuat dan diimport
 * 3. Akses: http://localhost/republik-computer/auth/register.php
 */

session_start();

// Redirect jika sudah login
if (isset($_SESSION['user_id'])) {
    header('Location: ../user/beranda.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Akun - Republik Computer</title>
    
    <!-- CSS -->
    <link rel="stylesheet" href="../assets/css/style.css">
    
    <!-- Font Awesome for Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="auth-wrapper">
        <div class="auth-container">
            <!-- Header -->
            <div class="auth-header">
                <h1><i class="fas fa-laptop"></i> Republik Computer</h1>
                <p>Daftar akun untuk mulai berbelanja</p>
            </div>

            <!-- Body -->
            <div class="auth-body">
                <!-- Alert Message -->
                <?php if (isset($_SESSION['error'])): ?>
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-circle"></i>
                        <span><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></span>
                    </div>
                <?php endif; ?>

                <?php if (isset($_SESSION['success'])): ?>
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i>
                        <span><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></span>
                    </div>
                <?php endif; ?>

                <!-- Registration Form -->
                <form action="process_register.php" method="POST" id="registerForm">
                    
                    <!-- Nama Lengkap -->
                    <div class="form-group">
                        <label for="nama" class="form-label">
                            Nama Lengkap <span class="required">*</span>
                        </label>
                        <input 
                            type="text" 
                            id="nama" 
                            name="nama" 
                            class="form-input" 
                            placeholder="Masukkan nama lengkap"
                            required
                            value="<?php echo isset($_SESSION['old_nama']) ? $_SESSION['old_nama'] : ''; ?>"
                        >
                        <span class="form-helper">Nama akan ditampilkan pada profil Anda</span>
                    </div>

                    <!-- Email -->
                    <div class="form-group">
                        <label for="email" class="form-label">
                            Email <span class="required">*</span>
                        </label>
                        <input 
                            type="email" 
                            id="email" 
                            name="email" 
                            class="form-input" 
                            placeholder="contoh@email.com"
                            required
                            value="<?php echo isset($_SESSION['old_email']) ? $_SESSION['old_email'] : ''; ?>"
                        >
                        <span class="form-helper">Gunakan email aktif untuk verifikasi</span>
                    </div>

                    <!-- Username -->
                    <div class="form-group">
                        <label for="username" class="form-label">
                            Username <span class="required">*</span>
                        </label>
                        <input 
                            type="text" 
                            id="username" 
                            name="username" 
                            class="form-input" 
                            placeholder="Pilih username unik"
                            required
                            pattern="[a-zA-Z0-9_]{4,20}"
                            title="Username 4-20 karakter, hanya huruf, angka, dan underscore"
                            value="<?php echo isset($_SESSION['old_username']) ? $_SESSION['old_username'] : ''; ?>"
                        >
                        <span class="form-helper">4-20 karakter, hanya huruf, angka, dan underscore</span>
                    </div>

                    <!-- Password -->
                    <div class="form-group">
                        <label for="password" class="form-label">
                            Password <span class="required">*</span>
                        </label>
                        <div class="password-wrapper">
                            <input 
                                type="password" 
                                id="password" 
                                name="password" 
                                class="form-input" 
                                placeholder="Minimal 6 karakter"
                                required
                                minlength="6"
                            >
                            <button type="button" class="password-toggle" onclick="togglePassword('password')">
                                <i class="fas fa-eye" id="password-icon"></i>
                            </button>
                        </div>
                        <span class="form-helper">Minimal 6 karakter untuk keamanan</span>
                    </div>

                    <!-- Confirm Password -->
                    <div class="form-group">
                        <label for="confirm_password" class="form-label">
                            Konfirmasi Password <span class="required">*</span>
                        </label>
                        <div class="password-wrapper">
                            <input 
                                type="password" 
                                id="confirm_password" 
                                name="confirm_password" 
                                class="form-input" 
                                placeholder="Ulangi password"
                                required
                            >
                            <button type="button" class="password-toggle" onclick="togglePassword('confirm_password')">
                                <i class="fas fa-eye" id="confirm_password-icon"></i>
                            </button>
                        </div>
                    </div>

                    <!-- No HP -->
                    <div class="form-group">
                        <label for="no_hp" class="form-label">
                            No. HP/WhatsApp <span class="required">*</span>
                        </label>
                        <input 
                            type="tel" 
                            id="no_hp" 
                            name="no_hp" 
                            class="form-input" 
                            placeholder="08xxxxxxxxxx"
                            required
                            pattern="[0-9]{10,15}"
                            title="Nomor HP 10-15 digit"
                            value="<?php echo isset($_SESSION['old_no_hp']) ? $_SESSION['old_no_hp'] : ''; ?>"
                        >
                        <span class="form-helper">Untuk konfirmasi pesanan</span>
                    </div>

                    <!-- Alamat -->
                    <div class="form-group">
                        <label for="alamat" class="form-label">
                            Alamat Lengkap <span class="required">*</span>
                        </label>
                        <textarea 
                            id="alamat" 
                            name="alamat" 
                            class="form-textarea" 
                            placeholder="Masukkan alamat lengkap untuk pengiriman"
                            required
                        ><?php echo isset($_SESSION['old_alamat']) ? $_SESSION['old_alamat'] : ''; ?></textarea>
                        <span class="form-helper">Alamat akan digunakan untuk pengiriman barang</span>
                    </div>

                    <!-- Submit Button -->
                    <button type="submit" class="btn btn-primary btn-block">
                        <i class="fas fa-user-plus"></i> Daftar Sekarang
                    </button>

                </form>

                <!-- Link to Login -->
                <div class="auth-link">
                    <p>Sudah punya akun? <a href="login.php">Login di sini</a></p>
                </div>
            </div>
        </div>
    </div>

    <!-- JavaScript -->
    <script>
        // Toggle Password Visibility
        function togglePassword(fieldId) {
            const field = document.getElementById(fieldId);
            const icon = document.getElementById(fieldId + '-icon');
            
            if (field.type === 'password') {
                field.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                field.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }

        // Form Validation
        document.getElementById('registerForm').addEventListener('submit', function(e) {
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            
            // Check if passwords match
            if (password !== confirmPassword) {
                e.preventDefault();
                alert('Password dan Konfirmasi Password tidak sama!');
                return false;
            }
            
            // Check password length
            if (password.length < 6) {
                e.preventDefault();
                alert('Password minimal 6 karakter!');
                return false;
            }
        });

        // Auto hide alerts after 5 seconds
        setTimeout(function() {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(function(alert) {
                alert.style.transition = 'opacity 0.5s';
                alert.style.opacity = '0';
                setTimeout(function() {
                    alert.remove();
                }, 500);
            });
        }, 5000);
    </script>

    <?php
    // Clear old input values after display
    unset($_SESSION['old_nama']);
    unset($_SESSION['old_email']);
    unset($_SESSION['old_username']);
    unset($_SESSION['old_no_hp']);
    unset($_SESSION['old_alamat']);
    ?>
</body>
</html>