<?php
/**
 * FILE: user/pesanan.php
 * DESKRIPSI: Halaman pesanan pengguna
 * LOKASI: republik-computer/user/pesanan.php
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
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pesanan Saya - Republik Computer</title>
    
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
            align-items: center;
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
        
        .page-title {
            font-size: 32px;
            color: #333;
            margin-bottom: 30px;
            padding: 40px 0 0;
        }
        
        .filter-section {
            background: white;
            padding: 20px;
            border-radius: 15px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            margin-bottom: 30px;
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
        }
        
        .filter-btn {
            padding: 10px 20px;
            border: 2px solid #e0e0e0;
            background: white;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s;
            font-weight: 600;
            font-size: 14px;
        }
        
        .filter-btn:hover {
            border-color: #667eea;
        }
        
        .filter-btn.active {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-color: #667eea;
        }
        
        .orders-container {
            display: grid;
            gap: 20px;
        }
        
        .order-card {
            background: white;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            transition: all 0.3s;
        }
        
        .order-card:hover {
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
            transform: translateY(-2px);
        }
        
        .order-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid #f0f0f0;
        }
        
        .order-info h3 {
            font-size: 18px;
            color: #333;
            margin-bottom: 8px;
        }
        
        .order-date {
            color: #666;
            font-size: 14px;
        }
        
        .order-status {
            padding: 8px 20px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: 700;
            text-transform: uppercase;
        }
        
        .status-menunggu {
            background: #fef3c7;
            color: #92400e;
        }
        
        .status-diproses {
            background: #dbeafe;
            color: #1e40af;
        }
        
        .status-dikirim {
            background: #e0e7ff;
            color: #4338ca;
        }
        
        .status-selesai {
            background: #d1fae5;
            color: #065f46;
        }
        
        .status-batal {
            background: #fee2e2;
            color: #991b1b;
        }
        
        .order-items {
            margin-bottom: 20px;
        }
        
        .order-item {
            display: flex;
            gap: 15px;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 10px;
            margin-bottom: 10px;
        }
        
        .item-image {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 32px;
            flex-shrink: 0;
        }
        
        .item-details {
            flex: 1;
        }
        
        .item-name {
            font-weight: 600;
            color: #333;
            margin-bottom: 5px;
        }
        
        .item-qty {
            color: #666;
            font-size: 14px;
            margin-bottom: 5px;
        }
        
        .item-price {
            color: #667eea;
            font-weight: 700;
        }
        
        .order-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-top: 15px;
            border-top: 2px solid #f0f0f0;
        }
        
        .order-total {
            font-size: 20px;
            font-weight: 700;
            color: #333;
        }
        
        .order-total span {
            color: #667eea;
        }
        
        .order-actions {
            display: flex;
            gap: 10px;
        }
        
        .btn-detail {
            background: white;
            color: #667eea;
            border: 2px solid #667eea;
        }
        
        .btn-detail:hover {
            background: #667eea;
            color: white;
        }
        
        .btn-reorder {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        
        .btn-reorder:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.3);
        }
        
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            background: white;
            border-radius: 15px;
        }
        
        .empty-icon {
            font-size: 80px;
            margin-bottom: 20px;
            color: #9ca3af;
        }
        
        .empty-state h2 {
            font-size: 24px;
            color: #333;
            margin-bottom: 10px;
        }
        
        .empty-state p {
            color: #666;
            margin-bottom: 30px;
        }
        
        .loading {
            text-align: center;
            padding: 40px;
            color: #666;
        }
        
        @media (max-width: 768px) {
            .order-header {
                flex-direction: column;
                gap: 15px;
            }
            
            .order-footer {
                flex-direction: column;
                gap: 15px;
                align-items: stretch;
            }
            
            .order-actions {
                flex-direction: column;
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
    
    <div class="container">
        <h1 class="page-title"><i class="fas fa-box"></i> Pesanan Saya</h1>
        
        <div class="filter-section">
            <button class="filter-btn active" onclick="filterOrders('all')">Semua Pesanan</button>
            <button class="filter-btn" onclick="filterOrders('menunggu')">Menunggu Pembayaran</button>
            <button class="filter-btn" onclick="filterOrders('diproses')">Diproses</button>
            <button class="filter-btn" onclick="filterOrders('dikirim')">Dikirim</button>
            <button class="filter-btn" onclick="filterOrders('selesai')">Selesai</button>
            <button class="filter-btn" onclick="filterOrders('batal')">Dibatalkan</button>
        </div>
        
        <div class="orders-container" id="ordersContainer">
            <div class="loading">Memuat pesanan...</div>
        </div>
    </div>

    <script>
        let allOrders = [];
        let currentFilter = 'all';
        
        document.addEventListener('DOMContentLoaded', () => {
            loadOrders();
        });
        
        async function loadOrders() {
            try {
                const response = await fetch('get_orders.php');
                const data = await response.json();
                
                if (!data.success) {
                    showEmptyState();
                    return;
                }
                
                allOrders = data.orders || [];
                
                if (allOrders.length === 0) {
                    showEmptyState();
                } else {
                    displayOrders(allOrders);
                }
            } catch (error) {
                console.error('Error loading orders:', error);
                showEmptyState();
            }
        }
        
        function showEmptyState() {
            document.getElementById('ordersContainer').innerHTML = `
                <div class="empty-state">
                    <div class="empty-icon"><i class="fas fa-box-open"></i></div>
                    <h2>Belum Ada Pesanan</h2>
                    <p>Anda belum memiliki riwayat pesanan</p>
                    <a href="beranda.php" class="btn btn-primary">Mulai Belanja</a>
                </div>
            `;
        }
        
        function displayOrders(orders) {
            const container = document.getElementById('ordersContainer');
            
            if (orders.length === 0) {
                container.innerHTML = `
                    <div class="empty-state">
                        <div class="empty-icon"><i class="fas fa-search"></i></div>
                        <h2>Tidak Ada Pesanan</h2>
                        <p>Tidak ditemukan pesanan dengan filter ini</p>
                    </div>
                `;
                return;
            }
            
            container.innerHTML = orders.map(order => `
                <div class="order-card">
                    <div class="order-header">
                        <div class="order-info">
                            <h3>Pesanan #${order.kode_transaksi || order.id_transaksi}</h3>
                            <div class="order-date">${formatDate(order.tanggal_transaksi)}</div>
                        </div>
                        <div class="order-status status-${order.status}">
                            ${getStatusText(order.status)}
                        </div>
                    </div>
                    
                    <div class="order-items" id="items-${order.id_transaksi}">
                        <div style="color: #666; font-size: 14px;">Memuat detail...</div>
                    </div>
                    
                    <div class="order-footer">
                        <div class="order-total">
                            Total: <span>Rp ${Number(order.total_bayar).toLocaleString('id-ID')}</span>
                        </div>
                        <div class="order-actions">
                            <button class="btn btn-detail" onclick="viewDetail(${order.id_transaksi})">
                                Lihat Detail
                            </button>
                            ${order.status === 'selesai' ? `
                                <button class="btn btn-reorder" onclick="reorder(${order.id_transaksi})">
                                    Pesan Lagi
                                </button>
                            ` : ''}
                        </div>
                    </div>
                </div>
            `).join('');
            
            // Load details for each order
            orders.forEach(order => {
                loadOrderDetails(order.id_transaksi);
            });
        }
        
        async function loadOrderDetails(orderId) {
            try {
                const response = await fetch(`get_order_details.php?id=${orderId}`);
                const data = await response.json();
                
                if (data.success && data.items) {
                    const container = document.getElementById(`items-${orderId}`);
                    container.innerHTML = data.items.slice(0, 2).map(item => `
                        <div class="order-item">
                            <div class="item-image"><i class="fas fa-laptop"></i></div>
                            <div class="item-details">
                                <div class="item-name">${item.nama_produk || 'Produk'}</div>
                                <div class="item-qty">${item.jumlah} x Rp ${Number(item.harga_satuan).toLocaleString('id-ID')}</div>
                                <div class="item-price">Rp ${Number(item.subtotal).toLocaleString('id-ID')}</div>
                            </div>
                        </div>
                    `).join('');
                    
                    if (data.items.length > 2) {
                        container.innerHTML += `<div style="color: #666; font-size: 14px; padding: 10px;">+${data.items.length - 2} produk lainnya</div>`;
                    }
                }
            } catch (error) {
                console.error('Error loading order details:', error);
            }
        }
        
        function filterOrders(status) {
            currentFilter = status;
            
            // Update active button
            document.querySelectorAll('.filter-btn').forEach(btn => {
                btn.classList.remove('active');
            });
            event.target.classList.add('active');
            
            // Filter orders
            const filtered = status === 'all' 
                ? allOrders 
                : allOrders.filter(order => order.status === status);
            
            displayOrders(filtered);
        }
        
        function formatDate(dateString) {
            const date = new Date(dateString);
            const options = { 
                year: 'numeric', 
                month: 'long', 
                day: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            };
            return date.toLocaleDateString('id-ID', options);
        }
        
        function getStatusText(status) {
            const statusMap = {
                'menunggu': 'Menunggu Pembayaran',
                'diproses': 'Sedang Diproses',
                'dikirim': 'Sedang Dikirim',
                'selesai': 'Selesai',
                'batal': 'Dibatalkan'
            };
            return statusMap[status] || status;
        }
        
        function viewDetail(orderId) {
            alert('Detail pesanan #' + orderId + '\n\nFitur ini akan segera tersedia!');
        }
        
        async function reorder(orderId) {
            if (!confirm('Apakah Anda ingin memesan produk yang sama?')) return;
            
            try {
                const response = await fetch('reorder.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `order_id=${orderId}`
                });
                
                const result = await response.json();
                
                if (result.success) {
                    alert('Produk berhasil ditambahkan ke keranjang!');
                    window.location.href = 'keranjang.php';
                } else {
                    alert(result.message || 'Gagal menambahkan ke keranjang');
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Terjadi kesalahan');
            }
        }
    </script>
</body>
</html>