<?php
/**
 * FILE: index.php (Root folder)
 * DESKRIPSI: Landing page utama website Republik Computer
 * Halaman ini untuk pengunjung yang belum login
 * 
 * LOKASI: republik-computer/index.php
 */

session_start();
require_once 'config/database.php';

// Jika sudah login, redirect ke halaman sesuai role
if (isset($_SESSION['user_id'])) {
    if ($_SESSION['role'] === 'admin' || $_SESSION['role'] === 'kepala_toko') {
        header('Location: admin/dashboard.php');
    } else {
        header('Location: user/beranda.php');
    }
    exit;
}

// Ambil beberapa produk unggulan untuk ditampilkan
try {
    $conn = getConnection();
    $stmt = $conn->prepare("
        SELECT p.*, k.nama_kategori 
        FROM produk p
        LEFT JOIN kategori k ON p.id_kategori = k.id_kategori
        WHERE p.status = 'tersedia'
        ORDER BY p.created_at DESC
        LIMIT 8
    ");
    $stmt->execute();
    $produk_unggulan = $stmt->get_result();
} catch (Exception $e) {
    $produk_unggulan = null;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Republik Computer - Toko Komputer Terpercaya</title>
    
    <!-- CSS -->
    <link rel="stylesheet" href="assets/css/style.css">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        /* Landing Page Specific Styles */
        .hero-section {
            background: linear-gradient(135deg, #2563eb 0%, #1e40af 100%);
            color: white;
            padding: 4rem 0;
            text-align: center;
        }
        
        .hero-content h1 {
            font-size: 2.5rem;
            margin-bottom: 1rem;
        }
        
        .hero-content p {
            font-size: 1.2rem;
            margin-bottom: 2rem;
            opacity: 0.9;
        }
        
        .hero-buttons {
            display: flex;
            gap: 1rem;
            justify-content: center;
            flex-wrap: wrap;
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
            list-style: none;
        }
        
        .navbar-menu a {
            text-decoration: none;
            color: #374151;
            font-weight: 500;
            transition: color 0.3s;
        }
        
        .navbar-menu a:hover {
            color: #2563eb;
        }
        
        .features-section {
            padding: 4rem 0;
            background: #f9fafb;
        }
        
        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 2rem;
            margin-top: 3rem;
        }
        
        .feature-card {
            background: white;
            padding: 2rem;
            border-radius: 0.75rem;
            text-align: center;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            transition: transform 0.3s;
        }
        
        .feature-card:hover {
            transform: translateY(-5px);
        }
        
        .feature-icon {
            font-size: 3rem;
            color: #2563eb;
            margin-bottom: 1rem;
        }
        
        .products-section {
            padding: 4rem 0;
        }
        
        .products-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 2rem;
            margin-top: 2rem;
        }
        
        .product-card {
            background: white;
            border-radius: 0.75rem;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            transition: transform 0.3s;
        }
        
        .product-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }
        
        .product-image {
            width: 100%;
            height: 200px;
            background: #e5e7eb;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 3rem;
            color: #9ca3af;
        }
        
        .product-info {
            padding: 1.5rem;
        }
        
        .product-category {
            font-size: 0.875rem;
            color: #6b7280;
            margin-bottom: 0.5rem;
        }
        
        .product-name {
            font-size: 1.1rem;
            font-weight: 600;
            color: #1f2937;
            margin-bottom: 0.5rem;
        }
        
        .product-price {
            font-size: 1.25rem;
            font-weight: 700;
            color: #2563eb;
            margin-bottom: 1rem;
        }
        
        .footer {
            background: #1f2937;
            color: white;
            padding: 3rem 0 1rem;
        }
        
        .footer-content {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 2rem;
            margin-bottom: 2rem;
        }
        
        .footer-bottom {
            text-align: center;
            padding-top: 2rem;
            border-top: 1px solid #374151;
        }
        
        @media (max-width: 768px) {
            .hero-content h1 {
                font-size: 2rem;
            }
            
            .navbar-menu {
                display: none;
            }
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar">
        <div class="container navbar-container">
            <a href="index.php" class="navbar-brand">
                <i class="fas fa-laptop"></i> Republik Computer
            </a>
            <ul class="navbar-menu">
                <li><a href="index.php"><i class="fas fa-home"></i> Beranda</a></li>
                <li><a href="auth/login.php"><i class="fas fa-sign-in-alt"></i> Login</a></li>
                <li><a href="auth/register.php"><i class="fas fa-user-plus"></i> Daftar</a></li>
            </ul>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero-section">
        <div class="container">
            <div class="hero-content">
                <h1><i class="fas fa-laptop-code"></i> Republik Computer</h1>
                <p>Toko Komputer, Laptop, dan Aksesoris Terpercaya di Tegal</p>
                <div class="hero-buttons">
                    <a href="auth/register.php" class="btn btn-success" style="padding: 1rem 2rem; font-size: 1.1rem;">
                        <i class="fas fa-shopping-cart"></i> Belanja Sekarang
                    </a>
                    <a href="auth/login.php" class="btn btn-secondary" style="padding: 1rem 2rem; font-size: 1.1rem; background: white; color: #2563eb;">
                        <i class="fas fa-sign-in-alt"></i> Login
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="features-section">
        <div class="container">
            <h2 class="text-center" style="font-size: 2rem; margin-bottom: 1rem;">Mengapa Memilih Kami?</h2>
            <p class="text-center" style="color: #6b7280; margin-bottom: 2rem;">
                Kami memberikan pelayanan terbaik untuk kebutuhan komputer Anda
            </p>
            
            <div class="features-grid">
                <div class="feature-card">
                    <div class="feature-icon"><i class="fas fa-shield-alt"></i></div>
                    <h3>Produk Original</h3>
                    <p>Semua produk dijamin 100% original dan bergaransi resmi</p>
                </div>
                
                <div class="feature-card">
                    <div class="feature-icon"><i class="fas fa-truck"></i></div>
                    <h3>Pengiriman Cepat</h3>
                    <p>Pengiriman cepat ke seluruh Indonesia dengan packaging aman</p>
                </div>
                
                <div class="feature-card">
                    <div class="feature-icon"><i class="fas fa-headset"></i></div>
                    <h3>Layanan 24/7</h3>
                    <p>Customer service siap membantu Anda kapan saja</p>
                </div>
                
                <div class="feature-card">
                    <div class="feature-icon"><i class="fas fa-tag"></i></div>
                    <h3>Harga Terjangkau</h3>
                    <p>Harga kompetitif dengan berbagai promo menarik</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Products Section -->
    <section class="products-section">
        <div class="container">
            <h2 class="text-center" style="font-size: 2rem; margin-bottom: 1rem;">Produk Unggulan</h2>
            <p class="text-center" style="color: #6b7280; margin-bottom: 2rem;">
                Produk terbaru dan terlaris di toko kami
            </p>
            
            <div class="products-grid">
                <?php if ($produk_unggulan && $produk_unggulan->num_rows > 0): ?>
                    <?php while ($produk = $produk_unggulan->fetch_assoc()): ?>
                        <div class="product-card">
                            <div class="product-image">
                                <i class="fas fa-laptop"></i>
                            </div>
                            <div class="product-info">
                                <div class="product-category">
                                    <i class="fas fa-tag"></i> <?php echo htmlspecialchars($produk['nama_kategori']); ?>
                                </div>
                                <div class="product-name"><?php echo htmlspecialchars($produk['nama_produk']); ?></div>
                                <div class="product-price">Rp <?php echo number_format($produk['harga'], 0, ',', '.'); ?></div>
                                <a href="auth/login.php" class="btn btn-primary btn-block">
                                    <i class="fas fa-shopping-cart"></i> Beli Sekarang
                                </a>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p class="text-center" style="grid-column: 1/-1; padding: 2rem;">Belum ada produk tersedia</p>
                <?php endif; ?>
            </div>
            
            <div class="text-center mt-4">
                <a href="auth/login.php" class="btn btn-primary" style="padding: 0.75rem 2rem;">
                    <i class="fas fa-eye"></i> Lihat Semua Produk
                </a>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-content">
                <div>
                    <h3 style="margin-bottom: 1rem;"><i class="fas fa-laptop"></i> Republik Computer</h3>
                    <p style="color: #9ca3af;">Toko komputer, laptop, dan aksesoris terpercaya sejak 2015</p>
                </div>
                
                <div>
                    <h4 style="margin-bottom: 1rem;">Kontak</h4>
                    <p style="color: #9ca3af; margin: 0.5rem 0;">
                        <i class="fas fa-map-marker-alt"></i> Jl. Flores Baru, No.1, Slawi, Tegal
                    </p>
                    <p style="color: #9ca3af; margin: 0.5rem 0;">
                        <i class="fas fa-phone"></i> 085742447278
                    </p>
                    <p style="color: #9ca3af; margin: 0.5rem 0;">
                        <i class="fas fa-envelope"></i> republikcomp2slw@gmail.com
                    </p>
                </div>
                
                <div>
                    <h4 style="margin-bottom: 1rem;">Link Cepat</h4>
                    <p style="margin: 0.5rem 0;"><a href="auth/login.php" style="color: #9ca3af; text-decoration: none;">Login</a></p>
                    <p style="margin: 0.5rem 0;"><a href="auth/register.php" style="color: #9ca3af; text-decoration: none;">Daftar</a></p>
                </div>
            </div>
            
            <div class="footer-bottom">
                <p style="color: #9ca3af;">&copy; 2025 Republik Computer. All rights reserved. | Developed by OneTeam</p>
            </div>
        </div>
    </footer>

    <?php
    // Close connection
    if (isset($stmt)) $stmt->close();
    if (isset($conn)) $conn->close();
    ?>
</body>
</html>