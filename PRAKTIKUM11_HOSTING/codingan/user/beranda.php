<?php
/**
 * FILE: user/beranda.php
 * DESKRIPSI: Halaman beranda untuk pelanggan yang sudah login
 * LOKASI: republik-computer/user/beranda.php
 */

session_start();
require_once '../config/database.php';

// Cek apakah sudah login
if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.php');
    exit;
}

// Cek role (hanya pelanggan yang bisa akses)
if ($_SESSION['role'] !== 'pelanggan') {
    header('Location: ../admin/dashboard.php');
    exit;
}

// Ambil data user
$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['nama'];

// Ambil jumlah item di keranjang
try {
    $conn = getConnection();
    
    // Hitung total item di keranjang (gunakan SUM untuk total qty)
    $stmt = $conn->prepare("SELECT COALESCE(SUM(jumlah), 0) as total FROM keranjang WHERE id_user = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $cart_count = $result->fetch_assoc()['total'];
    
    // Ambil kategori untuk filter
    $kategori_list = $conn->query("SELECT * FROM kategori ORDER BY nama_kategori");
    
    // Filter kategori dari GET parameter
    $filter_kategori = isset($_GET['kategori']) ? intval($_GET['kategori']) : 0;
    $search = isset($_GET['search']) ? cleanInput($_GET['search']) : '';
    
    // Build query dengan filter
    $query = "
        SELECT p.*, k.nama_kategori 
        FROM produk p
        LEFT JOIN kategori k ON p.id_kategori = k.id_kategori
        WHERE p.status = 'tersedia'
    ";
    
    $params = [];
    $types = '';
    
    if ($filter_kategori > 0) {
        $query .= " AND p.id_kategori = ?";
        $params[] = $filter_kategori;
        $types .= 'i';
    }
    
    if (!empty($search)) {
        $query .= " AND (p.nama_produk LIKE ? OR p.deskripsi LIKE ?)";
        $search_param = "%$search%";
        $params[] = $search_param;
        $params[] = $search_param;
        $types .= 'ss';
    }
    
    $query .= " ORDER BY p.created_at DESC";
    
    $stmt = $conn->prepare($query);
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $produk_list = $stmt->get_result();
    
} catch (Exception $e) {
    $error_message = $e->getMessage();
    error_log("Error in beranda.php: " . $error_message);
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Beranda - Republik Computer</title>
    
    <!-- CSS -->
    <link rel="stylesheet" href="../assets/css/style.css">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        body {
            background: #f3f4f6;
        }
        
        /* Navbar Styles */
        .main-navbar {
            background: white;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            padding: 1rem 0;
            position: sticky;
            top: 0;
            z-index: 100;
        }
        
        .navbar-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 2rem;
        }
        
        .navbar-brand {
            font-size: 1.5rem;
            font-weight: 700;
            color: #2563eb;
            text-decoration: none;
        }
        
        .navbar-search {
            flex: 1;
            max-width: 500px;
        }
        
        .search-box {
            position: relative;
        }
        
        .search-box input {
            width: 100%;
            padding: 0.75rem 3rem 0.75rem 1rem;
            border: 2px solid #e5e7eb;
            border-radius: 0.5rem;
        }
        
        .search-box button {
            position: absolute;
            right: 0.5rem;
            top: 50%;
            transform: translateY(-50%);
            background: #2563eb;
            color: white;
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 0.375rem;
            cursor: pointer;
        }
        
        .navbar-actions {
            display: flex;
            gap: 1.5rem;
            align-items: center;
        }
        
        .cart-btn {
            position: relative;
            background: #2563eb;
            color: white;
            padding: 0.75rem 1.5rem;
            border-radius: 0.5rem;
            text-decoration: none;
            transition: background 0.3s;
        }
        
        .cart-btn:hover {
            background: #1e40af;
        }
        
        .cart-badge {
            position: absolute;
            top: -8px;
            right: -8px;
            background: #ef4444;
            color: white;
            border-radius: 50%;
            min-width: 24px;
            height: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.75rem;
            font-weight: 700;
            padding: 0 6px;
        }
        
        .user-menu {
            position: relative;
        }
        
        .user-btn {
            background: #f3f4f6;
            padding: 0.75rem 1rem;
            border-radius: 0.5rem;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            border: none;
        }
        
        .user-dropdown {
            display: none;
            position: absolute;
            top: 100%;
            right: 0;
            margin-top: 0.5rem;
            background: white;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            border-radius: 0.5rem;
            min-width: 200px;
            z-index: 1000;
        }
        
        .user-dropdown.show {
            display: block;
        }
        
        .user-dropdown a {
            display: block;
            padding: 0.75rem 1rem;
            color: #374151;
            text-decoration: none;
            transition: background 0.3s;
        }
        
        .user-dropdown a:hover {
            background: #f3f4f6;
        }
        
        /* Welcome Banner */
        .welcome-banner {
            background: linear-gradient(135deg, #2563eb 0%, #1e40af 100%);
            color: white;
            padding: 2rem 0;
            margin-bottom: 2rem;
        }
        
        /* Category Filter */
        .category-filter {
            background: white;
            padding: 1.5rem;
            border-radius: 0.75rem;
            margin-bottom: 2rem;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }
        
        .category-buttons {
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
            margin-top: 1rem;
        }
        
        .category-btn {
            padding: 0.5rem 1rem;
            border: 2px solid #e5e7eb;
            background: white;
            border-radius: 0.5rem;
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
            color: #374151;
            font-size: 14px;
        }
        
        .category-btn:hover, .category-btn.active {
            background: #2563eb;
            color: white;
            border-color: #2563eb;
        }
        
        /* Products Grid */
        .products-section {
            background: white;
            padding: 2rem;
            border-radius: 0.75rem;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }
        
        .products-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-top: 1.5rem;
        }
        
        .product-card {
            border: 1px solid #e5e7eb;
            border-radius: 0.75rem;
            overflow: hidden;
            transition: transform 0.3s, box-shadow 0.3s;
        }
        
        .product-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        
        .product-image {
            width: 100%;
            height: 200px;
            background: #f3f4f6;
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
            font-size: 1rem;
            font-weight: 600;
            color: #1f2937;
            margin-bottom: 0.5rem;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
            min-height: 48px;
        }
        
        .product-price {
            font-size: 1.25rem;
            font-weight: 700;
            color: #2563eb;
            margin-bottom: 1rem;
        }
        
        .product-stock {
            font-size: 0.875rem;
            color: #10b981;
            margin-bottom: 1rem;
        }
        
        .product-stock.low {
            color: #ef4444;
        }
        
        @media (max-width: 768px) {
            .navbar-search {
                display: none;
            }
            
            .navbar-container {
                flex-wrap: wrap;
            }
        }
    </style>
</head>
<body>
    <!-- Main Navbar -->
    <nav class="main-navbar">
        <div class="container navbar-container">
            <a href="beranda.php" class="navbar-brand">
                <i class="fas fa-laptop"></i> Republik Computer
            </a>
            
            <div class="navbar-search">
                <form action="beranda.php" method="GET" class="search-box">
                    <input type="text" name="search" placeholder="Cari produk..." 
                           value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
                    <button type="submit">
                        <i class="fas fa-search"></i>
                    </button>
                </form>
            </div>
            
            <div class="navbar-actions">
                <a href="keranjang.php" class="cart-btn">
                    <i class="fas fa-shopping-cart"></i> Keranjang
                    <?php if ($cart_count > 0): ?>
                        <span class="cart-badge"><?php echo $cart_count; ?></span>
                    <?php endif; ?>
                </a>
                
                <div class="user-menu">
                    <button class="user-btn" onclick="toggleUserMenu()">
                        <i class="fas fa-user-circle"></i>
                        <span><?php echo htmlspecialchars($user_name); ?></span>
                        <i class="fas fa-chevron-down"></i>
                    </button>
                    <div class="user-dropdown" id="userDropdown">
                        <a href="profil.php"><i class="fas fa-user"></i> Profil Saya</a>
                        <a href="pesanan.php"><i class="fas fa-box"></i> Pesanan Saya</a>
                        <a href="../auth/logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <!-- Welcome Banner -->
    <div class="welcome-banner">
        <div class="container">
            <h1><i class="fas fa-hand-sparkles"></i> Selamat Datang, <?php echo htmlspecialchars($user_name); ?>!</h1>
            <p>Temukan produk komputer, laptop, dan aksesoris terbaik di sini</p>
        </div>
    </div>

    <!-- Main Content -->
    <div class="container">
        <!-- Alert Messages -->
        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i>
                <span><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></span>
            </div>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle"></i>
                <span><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></span>
            </div>
        <?php endif; ?>

        <!-- Category Filter -->
        <div class="category-filter">
            <h3><i class="fas fa-filter"></i> Filter Kategori</h3>
            <div class="category-buttons">
                <a href="beranda.php" class="category-btn <?php echo $filter_kategori == 0 ? 'active' : ''; ?>">
                    <i class="fas fa-th"></i> Semua
                </a>
                <?php if ($kategori_list && $kategori_list->num_rows > 0): ?>
                    <?php while ($kategori = $kategori_list->fetch_assoc()): ?>
                        <a href="beranda.php?kategori=<?php echo $kategori['id_kategori']; ?>" 
                           class="category-btn <?php echo $filter_kategori == $kategori['id_kategori'] ? 'active' : ''; ?>">
                            <i class="fas <?php echo $kategori['icon']; ?>"></i> 
                            <?php echo htmlspecialchars($kategori['nama_kategori']); ?>
                        </a>
                    <?php endwhile; ?>
                <?php endif; ?>
            </div>
        </div>

        <!-- Products Section -->
        <div class="products-section">
            <h2><i class="fas fa-box-open"></i> Produk Tersedia</h2>
            
            <div class="products-grid">
                <?php if ($produk_list && $produk_list->num_rows > 0): ?>
                    <?php while ($produk = $produk_list->fetch_assoc()): ?>
                        <div class="product-card">
                            <div class="product-image">
                                <i class="fas fa-laptop"></i>
                            </div>
                            <div class="product-info">
                                <div class="product-category">
                                    <i class="fas fa-tag"></i> <?php echo htmlspecialchars($produk['nama_kategori']); ?>
                                </div>
                                <div class="product-name" title="<?php echo htmlspecialchars($produk['nama_produk']); ?>">
                                    <?php echo htmlspecialchars($produk['nama_produk']); ?>
                                </div>
                                <div class="product-price">
                                    Rp <?php echo number_format($produk['harga'], 0, ',', '.'); ?>
                                </div>
                                <div class="product-stock <?php echo $produk['stok'] < 5 ? 'low' : ''; ?>">
                                    <i class="fas fa-box"></i> Stok: <?php echo $produk['stok']; ?>
                                    <?php if ($produk['stok'] < 5): ?>
                                        <strong>(Terbatas!)</strong>
                                    <?php endif; ?>
                                </div>
                                
                                <?php if ($produk['stok'] > 0): ?>
                                    <a href="add_to_cart.php?id=<?php echo $produk['id_produk']; ?>" 
                                       class="btn btn-primary btn-block"
                                       onclick="return confirm('Tambahkan <?php echo htmlspecialchars($produk['nama_produk']); ?> ke keranjang?')">
                                        <i class="fas fa-cart-plus"></i> Tambah ke Keranjang
                                    </a>
                                <?php else: ?>
                                    <button class="btn btn-secondary btn-block" disabled>
                                        <i class="fas fa-ban"></i> Stok Habis
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p style="grid-column: 1/-1; text-align: center; padding: 2rem; color: #666;">
                        <i class="fas fa-search"></i><br><br>
                        <?php if (!empty($search)): ?>
                            Tidak ada produk yang cocok dengan pencarian "<?php echo htmlspecialchars($search); ?>"
                        <?php else: ?>
                            Belum ada produk tersedia
                        <?php endif; ?>
                    </p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- JavaScript -->
    <script>
        // Toggle User Menu
        function toggleUserMenu() {
            const dropdown = document.getElementById('userDropdown');
            dropdown.classList.toggle('show');
        }

        // Close dropdown when clicking outside
        document.addEventListener('click', function(event) {
            const userMenu = document.querySelector('.user-menu');
            if (!userMenu.contains(event.target)) {
                document.getElementById('userDropdown').classList.remove('show');
            }
        });

        // Auto hide alerts
        setTimeout(function() {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(function(alert) {
                alert.style.transition = 'opacity 0.5s';
                alert.style.opacity = '0';
                setTimeout(() => alert.remove(), 500);
            });
        }, 5000);
    </script>

    <?php
    // Close connections
    if (isset($stmt)) $stmt->close();
    if (isset($conn)) $conn->close();
    ?>
</body>
</html>