<?php 
require_once '../includes/config.php';
require_once '../includes/koneksi.php';
require_once '../includes/session.php';
require_once '../includes/auth.php';

redirectIfNotAdmin();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$query = "SELECT * FROM produk WHERE id = $id";
$result = mysqli_query($conn, $query);

if(mysqli_num_rows($result) == 0) {
    header("Location: jasa.php");
    exit;
}

$jasa = mysqli_fetch_assoc($result);
$sub_kategori = mysqli_query($conn, "SELECT * FROM sub_kategori WHERE id_kategori = 1 ORDER BY nama_sub");

$error = '';
$success = '';

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id_sub_kategori = (int)$_POST['id_sub_kategori'];
    $nama_produk = mysqli_real_escape_string($conn, $_POST['nama_produk']);
    $deskripsi = mysqli_real_escape_string($conn, $_POST['deskripsi']);
    $harga = (int)$_POST['harga'];
    
    $update = "UPDATE produk SET 
               id_sub_kategori = '$id_sub_kategori',
               nama_produk = '$nama_produk',
               deskripsi = '$deskripsi',
               harga = '$harga',
               updated_at = NOW()
               WHERE id = $id";
    
    if(mysqli_query($conn, $update)) {
        $success = "Jasa berhasil diupdate!";
        // Refresh data
        $result = mysqli_query($conn, "SELECT * FROM produk WHERE id = $id");
        $jasa = mysqli_fetch_assoc($result);
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
    <title>Edit Jasa - Admin</title>
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
        .kode-info { background: #f0f0f0; padding: 10px; border-radius: 8px; margin-bottom: 20px; font-family: monospace; }
        @media (max-width: 768px) { .admin-main { margin-left: 0; } }
    </style>
</head>
<body>
<div class="admin-wrapper">
    <?php include '../includes/sidebar.php'; ?>
    
    <div class="admin-main">
        <h1>Edit Jasa</h1>
        
        <div class="form-card">
            <div class="kode-info">
                🔑 Kode Jasa: <strong><?php echo $jasa['kode_produk']; ?></strong> (Tidak bisa diubah)
            </div>
            
            <?php if($error): ?>
                <div class="alert-error">⚠️ <?php echo $error; ?></div>
            <?php endif; ?>
            <?php if($success): ?>
                <div class="alert-success">✅ <?php echo $success; ?></div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <div class="form-group">
                    <label>Kategori Jasa *</label>
                    <select name="id_sub_kategori" required>
                        <?php while($row = mysqli_fetch_assoc($sub_kategori)): ?>
                            <option value="<?php echo $row['id']; ?>" <?php echo ($row['id'] == $jasa['id_sub_kategori']) ? 'selected' : ''; ?>>
                                <?php echo $row['nama_sub']; ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>Nama Jasa *</label>
                    <input type="text" name="nama_produk" value="<?php echo htmlspecialchars($jasa['nama_produk']); ?>" required>
                </div>
                
                <div class="form-group">
                    <label>Deskripsi</label>
                    <textarea name="deskripsi" rows="4"><?php echo htmlspecialchars($jasa['deskripsi']); ?></textarea>
                </div>
                
                <div class="form-group">
                    <label>Harga (Rp) *</label>
                    <input type="number" name="harga" value="<?php echo $jasa['harga']; ?>" required>
                </div>
                
                <button type="submit" class="btn-save">Update Jasa</button>
                <a href="jasa.php" style="margin-left: 10px; color: #666;">Kembali</a>
            </form>
        </div>
    </div>
</div>
</body>
</html>