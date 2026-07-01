<?php 
require_once '../includes/config.php';
require_once '../includes/koneksi.php';
require_once '../includes/session.php';
require_once '../includes/auth.php';

redirectIfNotCustomer();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$query = "SELECT p.*, c.nama as customer_nama, c.no_hp, c.alamat, 
          pr.nama_produk, pr.kode_produk, pr.harga as harga_produk
          FROM pesanan p
          JOIN customers c ON p.id_customer = c.id
          JOIN produk pr ON p.id_produk = pr.id
          WHERE p.id = $id AND p.id_customer = " . $_SESSION['customer_id'];
$result = mysqli_query($conn, $query);

if(mysqli_num_rows($result) == 0) {
    header("Location: pesanan-saya.php");
    exit;
}

$invoice = mysqli_fetch_assoc($result);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice - Kandang Berkah Jaya</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f5f5f5;
            padding: 40px 20px;
        }
        .invoice-container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        .invoice-header {
            background: #2c5f2d;
            color: white;
            padding: 30px;
            text-align: center;
        }
        .invoice-header h1 {
            font-size: 28px;
            margin-bottom: 5px;
        }
        .invoice-header p {
            opacity: 0.9;
        }
        .invoice-body {
            padding: 30px;
        }
        .info-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 1px solid #eee;
        }
        .info-label {
            font-weight: 600;
            color: #666;
        }
        .info-value {
            font-weight: 500;
        }
        .product-table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        .product-table th, .product-table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }
        .product-table th {
            background: #f5f5f5;
        }
        .total-row {
            display: flex;
            justify-content: space-between;
            padding: 15px;
            background: #f5f5f5;
            border-radius: 10px;
            margin-top: 20px;
            font-weight: bold;
            font-size: 18px;
        }
        .status-badge {
            display: inline-block;
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 12px;
        }
        .status-paid { background: #d4edda; color: #155724; }
        .status-process { background: #d1ecf1; color: #0c5460; }
        .status-waiting { background: #fff3cd; color: #856404; }
        .btn-print {
            background: #2c5f2d;
            color: white;
            border: none;
            padding: 12px 25px;
            border-radius: 8px;
            cursor: pointer;
            margin-top: 20px;
            width: 100%;
            font-size: 16px;
        }
        .btn-back {
            display: block;
            text-align: center;
            margin-top: 15px;
            color: #666;
            text-decoration: none;
        }
        @media print {
            .btn-print, .btn-back { display: none; }
            body { background: white; padding: 0; }
            .invoice-container { box-shadow: none; }
        }
    </style>
</head>
<body>
    <div class="invoice-container">
        <div class="invoice-header">
            <h1>KANDANG BERKAH JAYA</h1>
            <p>Balqys Aqiqah</p>
            <p>@kambingsapibatam</p>
        </div>
        
        <div class="invoice-body">
            <div class="info-row">
                <span class="info-label">Invoice:</span>
                <span class="info-value"><?php echo $invoice['invoice']; ?></span>
            </div>
            <div class="info-row">
                <span class="info-label">Tanggal Pesan:</span>
                <span class="info-value"><?php echo date('d/m/Y H:i', strtotime($invoice['created_at'])); ?></span>
            </div>
            <div class="info-row">
                <span class="info-label">Status:</span>
                <span class="info-value">
                    <?php 
                    if($invoice['status'] == 'paid') echo '<span class="status-badge status-paid">✅ Lunas</span>';
                    elseif($invoice['status'] == 'process') echo '<span class="status-badge status-process">🔄 Proses</span>';
                    else echo '<span class="status-badge status-waiting">⏳ Menunggu DP</span>';
                    ?>
                </span>
            </div>
            
            <h3 style="margin: 20px 0 10px;">Detail Customer</h3>
            <div class="info-row">
                <span class="info-label">Nama:</span>
                <span class="info-value"><?php echo $invoice['customer_nama']; ?></span>
            </div>
            <div class="info-row">
                <span class="info-label">No HP:</span>
                <span class="info-value"><?php echo $invoice['no_hp']; ?></span>
            </div>
            <div class="info-row">
                <span class="info-label">Alamat:</span>
                <span class="info-value"><?php echo $invoice['alamat'] ?: '-'; ?></span>
            </div>
            
            <h3 style="margin: 20px 0 10px;">Detail Produk</h3>
            <table class="product-table">
                <thead>
                    <tr><th>Produk</th><th>Kode</th><th>Harga</th><th>Total</th></tr>
                </thead>
                <tbody>
                    <tr>
                        <td><?php echo $invoice['nama_produk']; ?></td>
                        <td><?php echo $invoice['kode_produk']; ?></td>
                        <td>Rp <?php echo number_format($invoice['harga_produk'], 0, ',', '.'); ?></td>
                        <td>Rp <?php echo number_format($invoice['total_harga'], 0, ',', '.'); ?></td>
                    </tr>
                </tbody>
            </table>
            
            <div class="total-row">
                <span>TOTAL:</span>
                <span>Rp <?php echo number_format($invoice['total_harga'], 0, ',', '.'); ?></span>
            </div>
            
            <?php if($invoice['tipe_pembayaran'] == 'dp'): ?>
            <div class="total-row" style="background: #fff3cd; margin-top: 10px;">
                <span>DP Dibayar:</span>
                <span>Rp <?php echo number_format($invoice['dp_amount'], 0, ',', '.'); ?></span>
            </div>
            <div class="total-row" style="background: #fff3cd; margin-top: 5px;">
                <span>Sisa Pembayaran:</span>
                <span>Rp <?php echo number_format($invoice['sisa_pembayaran'], 0, ',', '.'); ?></span>
            </div>
            <?php endif; ?>
            
            <button class="btn-print" onclick="window.print()">🖨️ Cetak Invoice</button>
            <a href="pesanan-saya.php" class="btn-back">← Kembali ke Pesanan Saya</a>
        </div>
    </div>
</body>
</html>