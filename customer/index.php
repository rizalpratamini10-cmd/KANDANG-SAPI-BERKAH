<?php 
require_once '../includes/config.php';
require_once '../includes/koneksi.php';
require_once '../includes/session.php';
require_once '../includes/auth.php';

// Cek apakah customer sudah login
redirectIfNotCustomer();

$customer = getCurrentCustomer($conn);

// Ambil pesanan terbaru customer
$query = "SELECT p.*, pr.nama_produk, pr.kode_produk, pr.gambar 
          FROM pesanan p
          JOIN produk pr ON p.id_produk = pr.id
          WHERE p.id_customer = " . $_SESSION['customer_id'] . "
          ORDER BY p.created_at DESC LIMIT 5";
$pesanan = mysqli_query($conn, $query);

// Hitung statistik
$total_pesanan = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM pesanan WHERE id_customer = " . $_SESSION['customer_id']))['total'];
$pesanan_diproses = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM pesanan WHERE id_customer = " . $_SESSION['customer_id'] . " AND status = 'process'"))['total'];
$pesanan_selesai = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM pesanan WHERE id_customer = " . $_SESSION['customer_id'] . " AND status = 'paid'"))['total'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Customer - Kandang Berkah Jaya</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f0f2f5;
            padding-top: 80px;
        }
        
        .dashboard-section {
            max-width: 1100px;
            margin: 0 auto;
            padding: 20px;
        }
        
        /* ===== WELCOME CARD ===== */
        .welcome-card {
            background: linear-gradient(135deg, #2c5f2d, #1a472a);
            color: white;
            padding: 30px 35px;
            border-radius: 20px;
            margin-bottom: 30px;
            position: relative;
            overflow: hidden;
        }
        .welcome-card::after {
            content: '🐐🐄';
            position: absolute;
            right: 20px;
            bottom: 10px;
            font-size: 70px;
            opacity: 0.12;
        }
        .welcome-card h1 {
            font-size: 28px;
            margin-bottom: 8px;
        }
        .welcome-card p {
            opacity: 0.9;
            margin-bottom: 4px;
        }
        .welcome-card .hp {
            margin-top: 10px;
            font-size: 14px;
            background: rgba(255,255,255,0.15);
            display: inline-block;
            padding: 6px 18px;
            border-radius: 25px;
        }
        
        /* ===== STATISTIK ===== */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
            margin-bottom: 30px;
        }
        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 16px;
            text-align: center;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            transition: transform 0.3s;
        }
        .stat-card:hover {
            transform: translateY(-3px);
        }
        .stat-card .number {
            font-size: 32px;
            font-weight: bold;
            color: #2c5f2d;
        }
        .stat-card .label {
            font-size: 13px;
            color: #666;
            margin-top: 5px;
        }
        
        /* ===== RECENT ORDERS ===== */
        .recent-orders {
            background: white;
            border-radius: 16px;
            padding: 20px 25px;
            margin-bottom: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        .recent-orders h2 {
            font-size: 20px;
            color: #333;
            margin-bottom: 20px;
            border-left: 4px solid #2c5f2d;
            padding-left: 15px;
        }
        
        /* ===== FIX TABEL - RESPONSIVE ===== */
        .table-wrap {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
            margin: 0 -5px;
        }
        
        .order-table {
            width: 100%;
            min-width: 650px;
            border-collapse: collapse;
            font-size: 14px;
        }
        .order-table th {
            background: #f5f5f5;
            padding: 12px 15px;
            text-align: left;
            font-weight: 600;
            color: #555;
            white-space: nowrap;
        }
        .order-table td {
            padding: 12px 15px;
            border-bottom: 1px solid #eee;
            vertical-align: middle;
        }
        .order-table tr:hover {
            background: #fafafa;
        }
        
        .badge-status {
            padding: 4px 14px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            display: inline-block;
            white-space: nowrap;
        }
        .badge-status.process {
            background: #d1ecf1;
            color: #0c5460;
        }
        .badge-status.paid {
            background: #d4edda;
            color: #155724;
        }
        .badge-status.waiting {
            background: #fff3cd;
            color: #856404;
        }
        
        .btn-detail {
            background: #2c5f2d;
            color: white;
            padding: 5px 16px;
            border-radius: 20px;
            text-decoration: none;
            font-size: 12px;
            font-weight: 600;
            transition: background 0.3s;
            display: inline-block;
            white-space: nowrap;
        }
        .btn-detail:hover {
            background: #1a472a;
            color: white;
        }
        
        /* ===== MENU GRID ===== */
        .menu-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
        }
        .menu-card {
            background: white;
            padding: 25px;
            border-radius: 16px;
            text-align: center;
            text-decoration: none;
            color: #333;
            transition: all 0.3s;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            display: block;
        }
        .menu-card:hover {
            background: #2c5f2d;
            color: white;
            transform: translateY(-5px);
        }
        .menu-card .icon {
            font-size: 36px;
            display: block;
            margin-bottom: 10px;
        }
        .menu-card h3 {
            font-size: 16px;
            margin-bottom: 5px;
        }
        .menu-card p {
            font-size: 13px;
            opacity: 0.7;
        }
        .menu-card.logout:hover {
            background: #dc3545;
        }
        
        /* ===== RESPONSIVE ===== */
        @media (max-width: 768px) {
            .stats-grid {
                grid-template-columns: 1fr 1fr;
            }
            .menu-grid {
                grid-template-columns: 1fr 1fr;
            }
            .welcome-card {
                padding: 20px;
            }
            .welcome-card h1 {
                font-size: 22px;
            }
            .welcome-card::after {
                font-size: 40px;
            }
            .order-table {
                font-size: 12px;
                min-width: 550px;
            }
            .order-table th,
            .order-table td {
                padding: 8px 12px;
            }
        }
        
        @media (max-width: 480px) {
            .stats-grid {
                grid-template-columns: 1fr;
            }
            .menu-grid {
                grid-template-columns: 1fr;
            }
            .recent-orders {
                padding: 15px;
            }
            .order-table {
                font-size: 11px;
                min-width: 480px;
            }
            .order-table th,
            .order-table td {
                padding: 6px 10px;
            }
            .btn-detail {
                padding: 3px 12px;
                font-size: 11px;
            }
        }
    </style>
</head>
<body>

<?php include '../includes/navbar.php'; ?>

<section class="dashboard-section">
    
    <!-- ===== WELCOME CARD ===== -->
    <div class="welcome-card">
        <h1>Halo, <?php echo htmlspecialchars($customer['nama']); ?>! 😊</h1>
        <p>Selamat datang di dashboard customer Kandang Berkah Jaya</p>
        <div class="hp">📩 No HP: <?php echo $customer['no_hp']; ?></div>
    </div>
    
    <!-- ===== STATISTIK ===== -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="number"><?php echo $total_pesanan; ?></div>
            <div class="label">Total Pesanan</div>
        </div>
        <div class="stat-card">
            <div class="number"><?php echo $pesanan_diproses; ?></div>
            <div class="label">Dalam Proses</div>
        </div>
        <div class="stat-card">
            <div class="number"><?php echo $pesanan_selesai; ?></div>
            <div class="label">Selesai</div>
        </div>
    </div>
    
    <!-- ===== PESANAN TERBARU ===== -->
    <div class="recent-orders">
        <h2>📋 Pesanan Terbaru</h2>
        
        <?php if(mysqli_num_rows($pesanan) > 0): ?>
        <div class="table-wrap">
            <table class="order-table">
                <thead>
                    <tr>
                        <th>Invoice</th>
                        <th>Produk</th>
                        <th>Kode</th>
                        <th>Total</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($row = mysqli_fetch_assoc($pesanan)): ?>
                        <tr>
                            <td><strong><?php echo $row['invoice']; ?></strong></td>
                            <td><?php echo $row['nama_produk']; ?></td>
                            <td><small><?php echo $row['kode_produk']; ?></small></td>
                            <td>Rp <?php echo number_format($row['total_harga'], 0, ',', '.'); ?></td>
                            <td>
                                <?php 
                                if($row['status'] == 'waiting_dp') {
                                    echo '<span class="badge-status waiting">Menunggu DP</span>';
                                } elseif($row['status'] == 'process') {
                                    echo '<span class="badge-status process">Proses</span>';
                                } elseif($row['status'] == 'paid') {
                                    echo '<span class="badge-status paid">Lunas</span>';
                                } else {
                                    echo '<span class="badge-status">' . $row['status'] . '</span>';
                                }
                                ?>
                            </td>
                            <td>
                                <a href="pesanan-saya.php" class="btn-detail">Detail</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
        <?php else: ?>
            <p style="text-align: center; padding: 30px; color: #999;">Belum ada pesanan. Yuk pesan sekarang!</p>
        <?php endif; ?>
    </div>
    
    <!-- ===== MENU CEPAT ===== -->
    <div class="menu-grid">
        <a href="produk.php" class="menu-card">
            <span class="icon">🛍️</span>
            <h3>Pesan Sekarang</h3>
            <p>Lihat produk & jasa yang tersedia</p>
        </a>
        <a href="pesanan-saya.php" class="menu-card">
            <span class="icon">📋</span>
            <h3>Riwayat Pesanan</h3>
            <p>Lihat semua pesanan Anda</p>
        </a>
        <a href="../logout.php" class="menu-card logout">
            <span class="icon">🚪</span>
            <h3>Logout</h3>
            <p>Keluar dari akun Anda</p>
        </a>
    </div>
    
</section>

<?php include '../includes/footer.php'; ?>

</body>
</html>