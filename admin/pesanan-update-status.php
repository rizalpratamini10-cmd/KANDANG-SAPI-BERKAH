<?php 
require_once '../includes/config.php';
require_once '../includes/koneksi.php';
require_once '../includes/session.php';
require_once '../includes/auth.php';

redirectIfNotAdmin();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Ambil data pesanan
$query = "SELECT p.*, pr.nama_produk, pr.kode_produk, c.nama as customer_nama, c.no_hp
          FROM pesanan p
          JOIN produk pr ON p.id_produk = pr.id
          JOIN customers c ON p.id_customer = c.id
          WHERE p.id = $id";
$result = mysqli_query($conn, $query);

if(mysqli_num_rows($result) == 0) {
    header("Location: pesanan.php");
    exit;
}

$pesanan = mysqli_fetch_assoc($result);
$error = '';
$success = '';

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $status = mysqli_real_escape_string($conn, $_POST['status']);
    $catatan = mysqli_real_escape_string($conn, $_POST['catatan']);
    
    // Jika status diubah menjadi process atau paid, update stok produk
    if(($status == 'process' || $status == 'paid') && $pesanan['status'] == 'waiting_dp') {
        $update_stok = "UPDATE produk SET stok = 0, status = 'habis', updated_at = NOW() WHERE id = " . $pesanan['id_produk'];
        mysqli_query($conn, $update_stok);
    }
    
    // Update status pesanan
    $update = "UPDATE pesanan SET status = '$status', updated_at = NOW() WHERE id = $id";
    
    if(mysqli_query($conn, $update)) {
        // Simpan catatan ke follow up jika ada
        if(!empty($catatan)) {
            $admin_id = $_SESSION['admin_id'];
            $insert_fu = "INSERT INTO follow_up (tipe_followup, id_target, catatan, followup_date, created_by) 
                          VALUES ('pesanan', '$id', '$catatan', CURDATE(), '$admin_id')";
            mysqli_query($conn, $insert_fu);
        }
        
        $success = "Status pesanan berhasil diupdate!";
        // Refresh data
        $result = mysqli_query($conn, "SELECT p.*, pr.nama_produk, pr.kode_produk, c.nama as customer_nama, c.no_hp
                                       FROM pesanan p
                                       JOIN produk pr ON p.id_produk = pr.id
                                       JOIN customers c ON p.id_customer = c.id
                                       WHERE p.id = $id");
        $pesanan = mysqli_fetch_assoc($result);
    } else {
        $error = "Gagal update: " . mysqli_error($conn);
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Status Pesanan - Admin</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
    <style>
        .admin-main { margin-left: 260px; padding: 20px; }
        .form-card { background: white; border-radius: 12px; padding: 25px; max-width: 550px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; margin-bottom: 8px; font-weight: 600; }
        .form-group select, .form-group textarea { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 8px; }
        .alert-error { background: #fee; color: #c00; padding: 12px; border-radius: 8px; margin-bottom: 20px; }
        .alert-success { background: #efe; color: #2c5f2d; padding: 12px; border-radius: 8px; margin-bottom: 20px; }
        .btn-save { background: #2c5f2d; color: white; padding: 12px 25px; border: none; border-radius: 8px; cursor: pointer; }
        .info-pesanan { background: #f5f5f5; padding: 15px; border-radius: 8px; margin-bottom: 20px; }
        .info-pesanan p { margin: 5px 0; }
        .current-status { margin-bottom: 20px; padding: 10px; background: #e3f2fd; border-radius: 8px; border-left: 4px solid #2196f3; }
        @media (max-width: 768px) { .admin-main { margin-left: 0; } }
    </style>
</head>
<body>
<div class="admin-wrapper">
    <?php include '../includes/sidebar.php'; ?>
    
    <div class="admin-main">
        <h1>Update Status Pesanan</h1>
        
        <div class="form-card">
            <div class="info-pesanan">
                <p><strong>Invoice:</strong> <?php echo $pesanan['invoice']; ?></p>
                <p><strong>Customer:</strong> <?php echo $pesanan['customer_nama']; ?> (<?php echo $pesanan['no_hp']; ?>)</p>
                <p><strong>Produk:</strong> <?php echo $pesanan['nama_produk']; ?> (Kode: <?php echo $pesanan['kode_produk']; ?>)</p>
                <p><strong>Total:</strong> Rp <?php echo number_format($pesanan['total_harga'], 0, ',', '.'); ?></p>
            </div>
            
            <div class="current-status">
                <strong>Status Saat Ini:</strong> 
                <?php 
                if($pesanan['status'] == 'waiting_dp') echo '⏳ Menunggu DP';
                elseif($pesanan['status'] == 'process') echo '🔄 Proses';
                elseif($pesanan['status'] == 'paid') echo '✅ Lunas / Selesai';
                else echo '❌ Dibatalkan';
                ?>
            </div>
            
            <?php if($error): ?>
                <div class="alert-error">⚠️ <?php echo $error; ?></div>
            <?php endif; ?>
            <?php if($success): ?>
                <div class="alert-success">✅ <?php echo $success; ?></div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <div class="form-group">
                    <label>Update Status ke:</label>
                    <select name="status" required>
                        <option value="waiting_dp" <?php echo $pesanan['status'] == 'waiting_dp' ? 'selected' : ''; ?>>⏳ Menunggu DP</option>
                        <option value="process" <?php echo $pesanan['status'] == 'process' ? 'selected' : ''; ?>>🔄 Proses</option>
                        <option value="paid" <?php echo $pesanan['status'] == 'paid' ? 'selected' : ''; ?>>✅ Lunas / Selesai</option>
                        <option value="cancelled" <?php echo $pesanan['status'] == 'cancelled' ? 'selected' : ''; ?>>❌ Dibatalkan</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>Catatan (opsional)</label>
                    <textarea name="catatan" rows="3" placeholder="Tambahkan catatan untuk pesanan ini..."></textarea>
                </div>
                
                <button type="submit" class="btn-save">Update Status</button>
                <a href="pesanan-detail.php?id=<?php echo $id; ?>" style="margin-left: 10px; color: #666;">Kembali ke Detail</a>
            </form>
        </div>
    </div>
</div>
</body>
</html>