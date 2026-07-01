<?php 
require_once '../includes/config.php';
require_once '../includes/koneksi.php';
require_once '../includes/session.php';
require_once '../includes/auth.php';

redirectIfNotAdmin();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$query = "SELECT p.*, 
          c.nama as customer_nama, 
          c.no_hp as customer_no_hp, 
          c.alamat as customer_alamat,
          pr.nama_produk, 
          pr.kode_produk, 
          pr.harga as harga_produk,
          -- Ambil data penerima dari catatan
          SUBSTRING_INDEX(SUBSTRING_INDEX(p.catatan, 'Nama: ', -1), ',', 1) as penerima_nama,
          SUBSTRING_INDEX(SUBSTRING_INDEX(p.catatan, 'HP: ', -1), ',', 1) as penerima_hp,
          SUBSTRING_INDEX(SUBSTRING_INDEX(p.catatan, 'Alamat: ', -1), ',', 1) as penerima_alamat
          FROM pesanan p
          JOIN customers c ON p.id_customer = c.id
          JOIN produk pr ON p.id_produk = pr.id
          WHERE p.id = $id";
$result = mysqli_query($conn, $query);

if(mysqli_num_rows($result) == 0) {
    header("Location: pesanan.php");
    exit;
}

$pesanan = mysqli_fetch_assoc($result);

// Ambil follow up pesanan ini
$followup = mysqli_query($conn, "SELECT * FROM follow_up WHERE tipe_followup = 'pesanan' AND id_target = $id ORDER BY created_at DESC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Pesanan - Admin</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .admin-main { margin-left: 260px; padding: 20px; }
        .btn-back { background: #6c757d; color: white; padding: 8px 20px; border-radius: 8px; text-decoration: none; display: inline-block; margin-bottom: 20px; }
        .detail-container { display: flex; gap: 25px; flex-wrap: wrap; }
        .detail-card { background: white; border-radius: 12px; padding: 20px; flex: 1; min-width: 300px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
        .detail-card h3 { margin-bottom: 15px; padding-bottom: 10px; border-bottom: 2px solid #2c5f2d; color: #2c5f2d; }
        .info-row { display: flex; margin-bottom: 10px; }
        .info-label { width: 130px; font-weight: 600; color: #666; flex-shrink: 0; }
        .info-value { flex: 1; }
        .status-badge { padding: 5px 15px; border-radius: 20px; display: inline-block; font-size: 13px; }
        .badge-warning { background: #fff3cd; color: #856404; }
        .badge-info { background: #d1ecf1; color: #0c5460; }
        .badge-success { background: #d4edda; color: #155724; }
        .followup-item { padding: 10px; border-bottom: 1px solid #eee; margin-bottom: 10px; }
        .followup-catatan { background: #f5f5f5; padding: 10px; border-radius: 8px; margin-top: 5px; }
        .followup-tanggal { font-size: 11px; color: #999; }
        .btn-wa { background: #25D366; color: white; padding: 8px 20px; border: none; border-radius: 8px; cursor: pointer; }
        .btn-wa:hover { background: #128C7E; }
        @media (max-width: 768px) { .admin-main { margin-left: 0; } }
    </style>
</head>
<body>
<div class="admin-wrapper">
    <?php include '../includes/sidebar.php'; ?>
    
    <div class="admin-main">
        <a href="pesanan.php" class="btn-back">← Kembali ke Pesanan</a>
        <h1>Detail Pesanan</h1>
        
        <div class="detail-container">
            
            <!-- INFORMASI PESANAN -->
            <div class="detail-card">
                <h3>📋 Informasi Pesanan</h3>
                <div class="info-row">
                    <div class="info-label">Invoice:</div>
                    <div class="info-value"><strong><?php echo $pesanan['invoice']; ?></strong></div>
                </div>
                <div class="info-row">
                    <div class="info-label">Tanggal Pesan:</div>
                    <div class="info-value"><?php echo date('d/m/Y H:i', strtotime($pesanan['created_at'])); ?></div>
                </div>
                <div class="info-row">
                    <div class="info-label">Status:</div>
                    <div class="info-value">
                        <?php 
                        if($pesanan['status'] == 'waiting_dp') echo '<span class="status-badge badge-warning">⏳ Menunggu DP</span>';
                        elseif($pesanan['status'] == 'process') echo '<span class="status-badge badge-info">🔄 Proses</span>';
                        elseif($pesanan['status'] == 'paid') echo '<span class="status-badge badge-success">✅ Lunas</span>';
                        else echo '<span class="status-badge badge-danger">❌ Dibatalkan</span>';
                        ?>
                    </div>
                </div>
                <div class="info-row">
                    <div class="info-label">Tipe Bayar:</div>
                    <div class="info-value"><?php echo $pesanan['tipe_pembayaran'] == 'dp' ? 'DP + Pelunasan' : 'Lunas'; ?></div>
                </div>
                <div class="info-row">
                    <div class="info-label">Total Harga:</div>
                    <div class="info-value">Rp <?php echo number_format($pesanan['total_harga'], 0, ',', '.'); ?></div>
                </div>
                <div class="info-row">
                    <div class="info-label">DP Dibayar:</div>
                    <div class="info-value">Rp <?php echo number_format($pesanan['dp_amount'], 0, ',', '.'); ?></div>
                </div>
                <div class="info-row">
                    <div class="info-label">Sisa:</div>
                    <div class="info-value">Rp <?php echo number_format($pesanan['sisa_pembayaran'], 0, ',', '.'); ?></div>
                </div>
                <?php if($pesanan['bukti_transfer']): ?>
                <div class="info-row">
                    <div class="info-label">Bukti Transfer:</div>
                    <div class="info-value">
                        <a href="../uploads/bukti-transfer/<?php echo $pesanan['bukti_transfer']; ?>" target="_blank">📎 Lihat Bukti</a>
                    </div>
                </div>
                <?php endif; ?>
            </div>
            
            <!-- INFORMASI CUSTOMER & PENERIMA -->
            <div class="detail-card">
                <h3>👤 Customer & Penerima</h3>
                
                <div style="background: #e8f5e9; padding: 12px; border-radius: 8px; margin-bottom: 15px;">
                    <strong>Customer (Pemesan):</strong>
                    <div class="info-row" style="margin-top: 5px;">
                        <div class="info-label">Nama:</div>
                        <div class="info-value"><?php echo $pesanan['customer_nama']; ?></div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">No HP:</div>
                        <div class="info-value"><?php echo $pesanan['customer_no_hp']; ?></div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Alamat:</div>
                        <div class="info-value"><?php echo $pesanan['customer_alamat'] ?: '-'; ?></div>
                    </div>
                </div>
                
                <div style="background: #fff3cd; padding: 12px; border-radius: 8px;">
                    <strong>📦 Penerima (Pengiriman):</strong>
                    <?php 
                    $penerima_nama = trim($pesanan['penerima_nama'] ?? '');
                    $penerima_hp = trim($pesanan['penerima_hp'] ?? '');
                    $penerima_alamat = trim($pesanan['penerima_alamat'] ?? '');
                    ?>
                    <?php if(!empty($penerima_nama) || !empty($penerima_hp)): ?>
                        <div class="info-row" style="margin-top: 5px;">
                            <div class="info-label">Nama:</div>
                            <div class="info-value"><strong><?php echo htmlspecialchars($penerima_nama); ?></strong></div>
                        </div>
                        <div class="info-row">
                            <div class="info-label">No HP:</div>
                            <div class="info-value"><?php echo htmlspecialchars($penerima_hp); ?></div>
                        </div>
                        <?php if(!empty($penerima_alamat)): ?>
                        <div class="info-row">
                            <div class="info-label">Alamat:</div>
                            <div class="info-value"><?php echo htmlspecialchars($penerima_alamat); ?></div>
                        </div>
                        <?php endif; ?>
                        <?php if(!empty($penerima_hp)): ?>
                        <div style="margin-top: 10px;">
                            <button class="btn-wa" onclick="followUpWA('<?php echo $pesanan['id']; ?>', '<?php echo $penerima_hp; ?>', '<?php echo htmlspecialchars($penerima_nama); ?>', '<?php echo $pesanan['invoice']; ?>', '<?php echo htmlspecialchars($pesanan['nama_produk']); ?>')">
                                <i class="fab fa-whatsapp"></i> WA Penerima
                            </button>
                        </div>
                        <?php endif; ?>
                    <?php else: ?>
                        <p style="color:#999; margin-top: 5px;">- Data penerima tidak tersedia (mungkin belum diisi)</p>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- INFORMASI PRODUK -->
            <div class="detail-card">
                <h3>🛍️ Produk</h3>
                <div class="info-row">
                    <div class="info-label">Nama:</div>
                    <div class="info-value"><?php echo $pesanan['nama_produk']; ?></div>
                </div>
                <div class="info-row">
                    <div class="info-label">Kode:</div>
                    <div class="info-value"><strong><?php echo $pesanan['kode_produk']; ?></strong></div>
                </div>
                <div class="info-row">
                    <div class="info-label">Harga:</div>
                    <div class="info-value">Rp <?php echo number_format($pesanan['harga_produk'], 0, ',', '.'); ?></div>
                </div>
            </div>
            
            <!-- HISTORY FOLLOW UP -->
            <div class="detail-card" style="flex: 1 1 100%;">
                <h3>📝 History Follow Up</h3>
                <?php if(mysqli_num_rows($followup) > 0): ?>
                    <?php while($fu = mysqli_fetch_assoc($followup)): ?>
                        <div class="followup-item">
                            <div class="followup-tanggal">📅 <?php echo date('d/m/Y H:i', strtotime($fu['created_at'])); ?></div>
                            <div class="followup-catatan"><?php echo nl2br($fu['catatan']); ?></div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p style="color: #999;">Belum ada follow up</p>
                <?php endif; ?>
                
                <button onclick="tambahFollowUp(<?php echo $pesanan['id']; ?>)" style="margin-top: 15px; background: #2c5f2d; color: white; padding: 8px 20px; border: none; border-radius: 8px; cursor: pointer;">
                    + Tambah Follow Up
                </button>
            </div>
            
        </div>
    </div>
</div>

<script>
function tambahFollowUp(pesananId) {
    let catatan = prompt('Masukkan catatan follow up:');
    if(catatan && catatan.trim()) {
        let formData = new FormData();
        formData.append('tipe', 'pesanan');
        formData.append('id_target', pesananId);
        formData.append('catatan', catatan);
        
        fetch('followup-tambah.php', {
            method: 'POST',
            body: formData
        })
        .then(() => {
            location.reload();
        });
    }
}

function followUpWA(pesananId, noHp, penerimaNama, invoice, productName) {
    let phone = noHp.replace(/\s/g, '').replace(/-/g, '').replace(/\+/g, '');
    if(phone.startsWith('0')) {
        phone = '62' + phone.substring(1);
    }
    if(!phone.startsWith('62')) {
        phone = '62' + phone;
    }
    
    let message = `Halo *${penerimaNama}*,

Saya dari *Kandang Berkah Jaya | Balqys Aqiqah*.

Kami ingin menginformasikan pesanan Anda:

📋 *Invoice:* ${invoice}
🛍️ *Produk:* ${productName}

Apakah ada yang bisa kami bantu? 😊

Terima kasih 🙏

_Salam, Admin Kandang Berkah Jaya_`;

    let encodedMessage = encodeURIComponent(message);
    let waUrl = `https://wa.me/${phone}?text=${encodedMessage}`;
    window.open(waUrl, '_blank');
}
</script>

</body>
</html>