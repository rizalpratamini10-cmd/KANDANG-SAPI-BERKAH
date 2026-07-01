<?php 
require_once '../includes/config.php';
require_once '../includes/koneksi.php';
require_once '../includes/session.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

redirectIfNotAdmin();

$error = '';
$success = '';

// Ambil daftar sub kategori untuk jasa (id_kategori = 1)
$sub_kategori = mysqli_query($conn, "SELECT * FROM sub_kategori WHERE id_kategori = 1 ORDER BY nama_sub");

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id_sub_kategori = (int)$_POST['id_sub_kategori'];
    $nama_produk = mysqli_real_escape_string($conn, $_POST['nama_produk']);
    $kode_produk = strtoupper(mysqli_real_escape_string($conn, $_POST['kode_produk']));
    $deskripsi = mysqli_real_escape_string($conn, $_POST['deskripsi']);
    $harga = (int)$_POST['harga'];
    $stok = 1;
    
    // Cek kode unik
    $cek = "SELECT * FROM produk WHERE kode_produk = '$kode_produk'";
    $cek_result = mysqli_query($conn, $cek);
    
    if(mysqli_num_rows($cek_result) > 0) {
        $error = "Kode '$kode_produk' sudah digunakan!";
    } else {
        $query = "INSERT INTO produk (id_sub_kategori, nama_produk, kode_produk, deskripsi, harga, stok) 
                  VALUES ('$id_sub_kategori', '$nama_produk', '$kode_produk', '$deskripsi', '$harga', '$stok')";
        
        if(mysqli_query($conn, $query)) {
            $success = "Jasa berhasil ditambahkan dengan kode: $kode_produk";
        } else {
            $error = "Gagal menambah jasa: " . mysqli_error($conn);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Jasa - Admin</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
    <style>
        .admin-main { margin-left: 260px; padding: 20px; }
        .form-card { background: white; border-radius: 12px; padding: 25px; max-width: 500px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; margin-bottom: 8px; font-weight: 600; }
        .form-group input, .form-group select, .form-group textarea { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 8px; }
        .alert-error { background: #fee; color: #c00; padding: 12px; border-radius: 8px; margin-bottom: 20px; }
        .alert-success { background: #efe; color: #2c5f2d; padding: 12px; border-radius: 8px; margin-bottom: 20px; }
        .btn-save { background: #2c5f2d; color: white; padding: 12px 25px; border: none; border-radius: 8px; cursor: pointer; }
        .kode-hint { font-size: 12px; color: #666; margin-top: 5px; }
        @media (max-width: 768px) { .admin-main { margin-left: 0; } }
    </style>
</head>
<body>
<div class="admin-wrapper">
    <?php include '../includes/sidebar.php'; ?>
    
    <div class="admin-main">
        <h1>Tambah Jasa Baru</h1>
        
        <div class="form-card">
            <?php if($error): ?>
                <div class="alert-error">⚠️ <?php echo $error; ?></div>
            <?php endif; ?>
            <?php if($success): ?>
                <div class="alert-success">✅ <?php echo $success; ?> <a href="jasa.php">Lihat daftar jasa</a></div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <div class="form-group">
                    <label>Kategori Jasa *</label>
                    <select name="id_sub_kategori" required>
                        <option value="">-- Pilih Kategori --</option>
                        <?php while($row = mysqli_fetch_assoc($sub_kategori)): ?>
                            <option value="<?php echo $row['id']; ?>"><?php echo $row['nama_sub']; ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>Nama Jasa *</label>
                    <input type="text" name="nama_produk" placeholder="Contoh: Paket Qurban Hemat" required>
                </div>
                
                <div class="form-group">
                    <label>Kode Jasa * (Unik)</label>
                    <input type="text" name="kode_produk" placeholder="Contoh: QR-001, AQ-001" required>
                    <div class="kode-hint">💡 Saran: QR-xxxx untuk Qurban, AQ-xxxx untuk Aqiqah, CT-xxxx untuk Catering</div>
                </div>
                
                <div class="form-group">
                    <label>Deskripsi</label>
                    <textarea name="deskripsi" rows="4" placeholder="Detail layanan jasa..."></textarea>
                </div>
                
                <div class="form-group">
                    <label>Harga (Rp) *</label>
                    <input type="number" name="harga" placeholder="Contoh: 2500000" required>
                </div>
                
                <button type="submit" class="btn-save">Simpan Jasa</button>
                <a href="jasa.php" style="margin-left: 10px; color: #666;">Batal</a>
            </form>
        </div>
    </div>
</div>
</body>
</html>