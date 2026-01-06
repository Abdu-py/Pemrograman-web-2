<?php
/**
 * FILE: auth/login.php
 * DESKRIPSI: Halaman login untuk semua role (Pelanggan, Admin, Kepala Toko)
 * 
 * CARA PAKAI:
 * 1. Akses: http://localhost/republik-computer/auth/login.php
 * 2. Login dengan akun yang sudah didaftarkan
 * 
 * AKUN DEFAULT (dari database):
 * - Admin: username: admin, password: admin123
 * - Kepala Toko: username: kepala_toko, password: admin123
 */

session_start();

// Redirect jika sudah login
if (isset($_SESSION['user_id'])) {
    // Redirect sesuai role
    if ($_SESSION['role'] === 'admin' || $_SESSION['role'] === 'kepala_toko') {
        header('Location: ../admin/dashboard.php');
    } else {
        header('Location: ../user/beranda.php');
    }
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Republik Computer</title>
    
    <!-- CSS -->
    <link rel="stylesheet" href="../assets/css/style.css">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="auth-wrapper">
        <div class="auth-container">
            <!-- Header -->
            <div class="auth-header">
                <h1><i class="fas fa-laptop"></i> Republik Computer</h1>
                <p>Masuk ke akun Anda</p>
            </div>

            <!-- Body -->
            <div class="auth-body">
                <!-- Alert Messages -->
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

                <!-- Login Form -->
                <form action="process_login.php" method="POST" id="loginForm">
                    
                    <!-- Username / Email -->
                    <div class="form-group">
                        <label for="login_id" class="form-label">
                            Username atau Email <span class="required">*</span>
                        </label>
                        <input 
                            type="text" 
                            id="login_id" 
                            name="login_id" 
                            class="form-input" 
                            placeholder="Masukkan username atau email"
                            required
                            autofocus
                        >
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
                                placeholder="Masukkan password"
                                required
                            >
                            <button type="button" class="password-toggle" onclick="togglePassword()">
                                <i class="fas fa-eye" id="password-icon"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Remember Me -->
                    <div class="form-group" style="display: flex; align-items: center; gap: 0.5rem;">
                        <input 
                            type="checkbox" 
                            id="remember" 
                            name="remember"
                            style="width: auto;"
                        >
                        <label for="remember" style="margin: 0; font-weight: normal;">
                            Ingat saya
                        </label>
                    </div>

                    <!-- Submit Button -->
                    <button type="submit" class="btn btn-primary btn-block">
                        <i class="fas fa-sign-in-alt"></i> Masuk
                    </button>

                </form>

                <!-- Links -->
                <div class="auth-link">
                    <p>Belum punya akun? <a href="register.php">Daftar di sini</a></p>
                </div>

                <!-- Demo Accounts Info -->
                <div style="margin-top: 1.5rem; padding: 1rem; background: #f9fafb; border-radius: 0.5rem; font-size: 0.875rem;">
                    <p style="font-weight: 600; margin-bottom: 0.5rem; color: #374151;">
                        <i class="fas fa-info-circle"></i> Akun Demo:
                    </p>
                    <p style="margin: 0.25rem 0; color: #6b7280;">
                        <strong>Admin:</strong> username: <code>admin</code>, password: <code>admin123</code>
                    </p>
                    <p style="margin: 0.25rem 0; color: #6b7280;">
                        <strong>Kepala Toko:</strong> username: <code>kepala_toko</code>, password: <code>admin123</code>
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- JavaScript -->
    <script>
        // Toggle Password Visibility
        function togglePassword() {
            const field = document.getElementById('password');
            const icon = document.getElementById('password-icon');
            
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

        // Handle Enter key on form
        document.getElementById('loginForm').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                this.submit();
            }
        });
    </script>
</body>
</html>