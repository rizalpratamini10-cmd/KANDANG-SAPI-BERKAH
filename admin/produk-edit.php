<?php 
require_once '../includes/config.php';
require_once '../includes/koneksi.php';
require_once '../includes/session.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

redirectIfNotAdmin();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Ambil data produk
$query = "SELECT * FROM produk WHERE id = $id";
$result = mysqli_query($conn, $query);

if(mysqli_num_rows($result) == 0) {
    header("Location: produk.php");
    exit;
}

$produk = mysqli_fetch_assoc($result);

// Ambil daftar sub kategori
$sub_kategori = mysqli_query($conn, "SELECT * FROM sub_kategori ORDER BY id_kategori, nama_sub");

$error = '';
$success = '';

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id_sub_kategori = (int)$_POST['id_sub_kategori'];
    $nama_produk = mysqli_real_escape_string($conn, $_POST['nama_produk']);
    $deskripsi = mysqli_real_escape_string($conn, $_POST['deskripsi']);
    $harga = (int)$_POST['harga'];
    
    // Upload gambar baru jika ada
    $gambar = $produk['gambar'];
    if(isset($_FILES['gambar']) && $_FILES['gambar']['error'] == 0) {
        $target_dir = "../uploads/produk/";
        $new_gambar = uploadFile($_FILES['gambar'], $target_dir, ['jpg', 'jpeg', 'png']);
        if($new_gambar) {
            // Hapus gambar lama
            if($gambar && file_exists($target_dir . $gambar)) {
                unlink($target_dir . $gambar);
            }
            $gambar = $new_gambar;
        }
    }
    
    $query = "UPDATE produk SET 
              id_sub_kategori = '$id_sub_kategori',
              nama_produk = '$nama_produk',
              deskripsi = '$deskripsi',
              harga = '$harga',
              gambar = '$gambar',
              updated_at = NOW()
              WHERE id = $id";
    
    if(mysqli_query($conn, $query)) {
        $success = "Produk berhasil diupdate!";
        // Refresh data
        $result = mysqli_query($conn, "SELECT * FROM produk WHERE id = $id");
        $produk = mysqli_fetch_assoc($result);
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
    <title>Edit Produk - Admin</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
    <style>
        .admin-main { margin-left: 260px; padding: 20px; }
        .form-card { background: white; border-radius: 12px; padding: 25px; max-width: 700px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; margin-bottom: 8px; font-weight: 600; }
        .form-group input, .form-group select, .form-group textarea { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 8px; }
        .current-image { margin: 10px 0; }
        .current-image img { max-width: 150px; border-radius: 8px; }
        .alert-error { background: #fee; color: #c00; padding: 12px; border-radius: 8px; margin-bottom: 20px; }
        .alert-success { background: #efe; color: #2c5f2d; padding: 12px; border-radius: 8px; margin-bottom: 20px; }
        .btn-save { background: #2c5f2d; color: white; padding: 12px 25px; border: none; border-radius: 8px; cursor: pointer; }
        .kode-info { background: #f0f0f0; padding: 10px; border-radius: 8px; margin-bottom: 20px; font-family: monospace; }
    </style>
</head>
<body>
<div class="admin-wrapper">
    <?php include '../includes/sidebar.php'; ?>
    
    <div class="admin-main">
        <h1>Edit Produk</h1>
        
        <div class="form-card">
            <?php if($error): ?>
                <div class="alert-error">⚠️ <?php echo $error; ?></div>
            <?php endif; ?>
            <?php if($success): ?>
                <div class="alert-success">✅ <?php echo $success; ?></div>
            <?php endif; ?>
            
            <div class="kode-info">
                <strong>🔑 Kode Produk (Tidak bisa diubah):</strong> <?php echo $produk['kode_produk']; ?>
            </div>
            
            <form method="POST" action="" enctype="multipart/form-data">
                <div class="form-group">
                    <label>Kategori *</label>
                    <select name="id_sub_kategori" required>
                        <?php while($row = mysqli_fetch_assoc($sub_kategori)): ?>
                            <option value="<?php echo $row['id']; ?>" <?php echo ($row['id'] == $produk['id_sub_kategori']) ? 'selected' : ''; ?>>
                                <?php 
                                $kategori = ($row['id_kategori'] == 1) ? 'Jasa' : 'Produk';
                                echo "[$kategori] " . $row['nama_sub']; 
                                ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>Nama Produk *</label>
                    <input type="text" name="nama_produk" value="<?php echo htmlspecialchars($produk['nama_produk']); ?>" required>
                </div>
                
                <div class="form-group">
                    <label>Deskripsi</label>
                    <textarea name="deskripsi" rows="4"><?php echo htmlspecialchars($produk['deskripsi']); ?></textarea>
                </div>
                
                <div class="form-group">
                    <label>Harga (Rp) *</label>
                    <input type="number" name="harga" value="<?php echo $produk['harga']; ?>" required>
                </div>
                
                <div class="form-group">
                    <label>Gambar Saat Ini</label>
                    <div class="current-image">
                        <?php if($produk['gambar']): ?>
                            <img src="../uploads/produk/<?php echo $produk['gambar']; ?>" alt="">
                            <br><small style="color: #666;"><?php echo $produk['gambar']; ?></small>
                        <?php else: ?>
                            <p>Tidak ada gambar</p>
                        <?php endif; ?>
                    </div>
                    <input type="file" name="gambar" accept="image/*">
                    <small>Kosongkan jika tidak ingin mengubah gambar</small>
                </div>
                
                <button type="submit" class="btn-save">Update Produk</button>
                <a href="produk.php" style="margin-left: 10px; color: #666;">Kembali</a>
            </form>
        </div>
    </div>
</div>
</body>
</html>