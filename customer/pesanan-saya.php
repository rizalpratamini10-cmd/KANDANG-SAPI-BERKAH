<?php 
require_once '../includes/config.php';
require_once '../includes/koneksi.php';
require_once '../includes/session.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

// Cek apakah customer sudah login
redirectIfNotCustomer();

$customer_id = $_SESSION['customer_id'];

// Ambil semua pesanan customer
$query = "SELECT p.*, pr.nama_produk, pr.kode_produk, pr.gambar 
          FROM pesanan p
          JOIN produk pr ON p.id_produk = pr.id
          WHERE p.id_customer = $customer_id
          ORDER BY p.created_at DESC";
$result = mysqli_query($conn, $query);

// Ambil statistik
$total = mysqli_num_rows($result);
$menunggu = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM pesanan WHERE id_customer = $customer_id AND status = 'waiting_dp'"))['total'];
$proses = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM pesanan WHERE id_customer = $customer_id AND status = 'process'"))['total'];
$selesai = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM pesanan WHERE id_customer = $customer_id AND status = 'paid'"))['total'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pesanan Saya - Kandang Berkah Jaya</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #f5f5f5; }
        .pesanan-section { max-width: 1000px; margin: 0 auto; padding: 30px 20px; }
        h1 { text-align: center; color: #2c5f2d; margin-bottom: 10px; }
        .subtitle { text-align: center; color: #666; margin-bottom: 30px; }
        .stats { display: flex; justify-content: center; gap: 30px; margin-bottom: 30px; flex-wrap: wrap; }
        .stat-item { text-align: center; background: white; padding: 15px 25px; border-radius: 15px; min-width: 100px; box-shadow: 0 2px 5px rgba(0,0,0,0.05); }
        .stat-number { font-size: 24px; font-weight: bold; color: #2c5f2d; }
        .stat-label { font-size: 12px; color: #666; }
        .pesanan-card { background: white; border-radius: 15px; margin-bottom: 20px; overflow: hidden; box-shadow: 0 2px 10px rgba(0,0,0,0.05); }
        .pesanan-header { background: #f9f9f9; padding: 15px 20px; display: flex; justify-content: space-between; flex-wrap: wrap; border-bottom: 1px solid #eee; }
        .invoice { font-weight: bold; color: #2c5f2d; }
        .tanggal { font-size: 12px; color: #666; }
        .pesanan-body { display: flex; padding: 20px; gap: 20px; flex-wrap: wrap; }
        .produk-image { width: 100px; height: 100px; background: #f5f5f5; border-radius: 10px; display: flex; align-items: center; justify-content: center; }
        .produk-image img { max-width: 80px; max-height: 80px; object-fit: cover; }
        .produk-info { flex: 1; }
        .produk-nama { font-size: 18px; font-weight: bold; margin-bottom: 5px; }
        .produk-kode { font-size: 12px; color: #666; margin-bottom: 10px; }
        .produk-harga { font-weight: bold; color: #2c5f2d; }
        .pesanan-footer { background: #f9f9f9; padding: 15px 20px; display: flex; justify-content: space-between; flex-wrap: wrap; align-items: center; border-top: 1px solid #eee; }
        .status { padding: 5px 12px; border-radius: 20px; font-size: 12px; font-weight: bold; }
        .status-waiting { background: #fff3cd; color: #856404; }
        .status-process { background: #d1ecf1; color: #0c5460; }
        .status-paid { background: #d4edda; color: #155724; }
        .status-cancelled { background: #f8d7da; color: #721c24; }
        .btn-upload { background: #ffc107; color: #333; padding: 8px 20px; border-radius: 20px; text-decoration: none; font-size: 13px; font-weight: bold; }
        .btn-detail { background: #2c5f2d; color: white; padding: 8px 20px; border-radius: 20px; text-decoration: none; font-size: 13px; }
        .empty-state { text-align: center; padding: 60px; background: white; border-radius: 15px; }
        .empty-state p { margin-bottom: 20px; color: #666; }
        .btn-belanja { background: #2c5f2d; color: white; padding: 12px 30px; border-radius: 30px; text-decoration: none; }
        @media (max-width: 768px) {
            .pesanan-body { flex-direction: column; align-items: center; text-align: center; }
            .stats { gap: 15px; }
            .stat-item { padding: 10px 15px; min-width: 80px; }
        }
    </style>
</head>
<body>

<?php include '../includes/navbar.php'; ?>

<section class="pesanan-section">
    <h1>📋 Pesanan Saya</h1>
    <p class="subtitle">Lihat status dan riwayat pesanan Anda</p>
    
    <div class="stats">
        <div class="stat-item">
            <div class="stat-number"><?php echo $total; ?></div>
            <div class="stat-label">Total Pesanan</div>
        </div>
        <div class="stat-item">
            <div class="stat-number"><?php echo $menunggu; ?></div>
            <div class="stat-label">Menunggu Konfirmasi</div>
        </div>
        <div class="stat-item">
            <div class="stat-number"><?php echo $proses; ?></div>
            <div class="stat-label">Diproses</div>
        </div>
        <div class="stat-item">
            <div class="stat-number"><?php echo $selesai; ?></div>
            <div class="stat-label">Selesai</div>
        </div>
    </div>
    
    <?php if(mysqli_num_rows($result) == 0): ?>
        <div class="empty-state">
            <p>😞 Belum ada pesanan. Yuk pesan sekarang!</p>
            <a href="produk.php" class="btn-belanja">🛍️ Belanja Sekarang</a>
        </div>
    <?php else: ?>
        <?php while($row = mysqli_fetch_assoc($result)): ?>
            <div class="pesanan-card">
                <div class="pesanan-header">
                    <div>
                        <span class="invoice">🧾 <?php echo $row['invoice']; ?></span>
                    </div>
                    <div class="tanggal">
                        📅 <?php echo date('d/m/Y H:i', strtotime($row['created_at'])); ?>
                    </div>
                </div>
                
                <div class="pesanan-body">
                    <div class="produk-image">
                        <img src="../uploads/produk/<?php echo $row['gambar'] ?: 'default-product.jpg'; ?>" alt="">
                    </div>
                    <div class="produk-info">
                        <div class="produk-nama"><?php echo $row['nama_produk']; ?></div>
                        <div class="produk-kode">Kode: <?php echo $row['kode_produk']; ?></div>
                        <div class="produk-harga">Rp <?php echo number_format($row['total_harga'], 0, ',', '.'); ?></div>
                    </div>
                </div>
                
                <div class="pesanan-footer">
                    <div>
                        <?php 
                        $status_class = '';
                        $status_text = '';
                        if($row['status'] == 'waiting_dp') {
                            $status_class = 'status-waiting';
                            $status_text = '⏳ Menunggu Konfirmasi';
                        } elseif($row['status'] == 'process') {
                            $status_class = 'status-process';
                            $status_text = '🔄 Sedang Diproses';
                        } elseif($row['status'] == 'paid') {
                            $status_class = 'status-paid';
                            $status_text = '✅ Selesai / Lunas';
                        } else {
                            $status_class = 'status-cancelled';
                            $status_text = '❌ Dibatalkan';
                        }
                        ?>
                        <span class="status <?php echo $status_class; ?>"><?php echo $status_text; ?></span>
                    </div>
                    <div>
                        <?php if($row['status'] == 'waiting_dp' && !$row['bukti_transfer']): ?>
                            <a href="upload-bukti.php" class="btn-upload">📤 Upload Bukti</a>
                        <?php endif; ?>
                        <a href="detail-produk.php?id=<?php echo $row['id_produk']; ?>" class="btn-detail">Detail Produk</a>
                    </div>
                </div>
            </div>
        <?php endwhile; ?>
    <?php endif; ?>
</section>

<?php include '../includes/footer.php'; ?>
</body>
</html>