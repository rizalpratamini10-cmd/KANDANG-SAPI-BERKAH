<?php 
require_once '../includes/config.php';
require_once '../includes/koneksi.php';
require_once '../includes/session.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

redirectIfNotCustomer();

// =====================================================
// PROSES TAMBAH KE KERANJANG
// =====================================================
if(isset($_GET['tambah'])) {
    $id = (int)$_GET['tambah'];
    
    $cek = mysqli_query($conn, "SELECT stok FROM produk WHERE id = $id");
    $produk = mysqli_fetch_assoc($cek);
    
    if($produk && $produk['stok'] == 1) {
        if(!isset($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }
        
        if(isset($_SESSION['cart'][$id])) {
            $_SESSION['cart'][$id] += 1;
        } else {
            $_SESSION['cart'][$id] = 1;
        }
        
        header("Location: keranjang.php");
        exit;
    } else {
        echo "<script>alert('Maaf, stok produk habis!'); window.location='produk.php';</script>";
        exit;
    }
}

// =====================================================
// PROSES HAPUS DARI KERANJANG
// =====================================================
if(isset($_GET['hapus'])) {
    $id = (int)$_GET['hapus'];
    unset($_SESSION['cart'][$id]);
    header("Location: keranjang.php");
    exit;
}

// =====================================================
// AMBIL DATA KERANJANG
// =====================================================
$cart_items = [];
$total = 0;

if(isset($_SESSION['cart']) && !empty($_SESSION['cart'])) {
    $ids = implode(',', array_keys($_SESSION['cart']));
    $query = "SELECT p.*, sk.nama_sub as kategori 
              FROM produk p
              JOIN sub_kategori sk ON p.id_sub_kategori = sk.id
              WHERE p.id IN ($ids) AND p.stok = 1";
    $result = mysqli_query($conn, $query);
    while($row = mysqli_fetch_assoc($result)) {
        $row['jumlah'] = $_SESSION['cart'][$row['id']];
        $row['subtotal'] = $row['harga'] * $row['jumlah'];
        $cart_items[] = $row;
        $total += $row['subtotal'];
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Keranjang Belanja - Kandang Berkah Jaya</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        body {
            background: #f8f9fa;
            padding-top: 80px;
        }
        .cart-container {
            max-width: 1100px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .page-title {
            text-align: center;
            margin-bottom: 30px;
        }
        .page-title h1 {
            color: #2c5f2d;
            font-size: 32px;
        }
        .page-title p {
            color: #666;
        }
        
        .cart-card {
            background: white;
            border-radius: 16px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            overflow: hidden;
        }
        .cart-header {
            background: linear-gradient(135deg, #1a472a, #2c5f2d);
            color: white;
            padding: 20px 25px;
        }
        .cart-header h4 {
            margin: 0;
            font-weight: 600;
        }
        
        .cart-item {
            padding: 20px 25px;
            border-bottom: 1px solid #eee;
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 15px;
        }
        .cart-item:last-child {
            border-bottom: none;
        }
        .cart-item .item-image {
            width: 80px;
            height: 80px;
            border-radius: 12px;
            overflow: hidden;
            flex-shrink: 0;
            background: #f5f5f5;
        }
        .cart-item .item-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .cart-item .item-info {
            flex: 1;
            min-width: 150px;
        }
        .cart-item .item-info h5 {
            margin: 0 0 4px 0;
            font-weight: 600;
            color: #333;
        }
        .cart-item .item-info .kode {
            font-size: 12px;
            color: #999;
            margin-bottom: 4px;
        }
        .cart-item .item-info .harga {
            font-weight: bold;
            color: #2c5f2d;
            font-size: 16px;
        }
        .cart-item .item-qty {
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .cart-item .item-qty input {
            width: 60px;
            text-align: center;
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 6px;
            font-size: 14px;
        }
        .cart-item .item-qty button {
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 6px 10px;
            background: white;
            cursor: pointer;
            transition: all 0.3s;
        }
        .cart-item .item-qty button:hover {
            background: #2c5f2d;
            color: white;
            border-color: #2c5f2d;
        }
        .cart-item .item-subtotal {
            font-weight: bold;
            font-size: 16px;
            color: #2c5f2d;
            min-width: 120px;
            text-align: right;
        }
        .cart-item .item-remove {
            color: #dc3545;
            background: none;
            border: none;
            font-size: 18px;
            cursor: pointer;
            padding: 5px;
        }
        .cart-item .item-remove:hover {
            color: #b02a37;
        }
        
        .cart-summary {
            background: #f8f9fa;
            padding: 25px;
            border-radius: 16px;
            position: sticky;
            top: 100px;
        }
        .cart-summary h5 {
            font-weight: 700;
            color: #333;
            margin-bottom: 20px;
        }
        .cart-summary .summary-row {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid #eee;
        }
        .cart-summary .summary-row.total {
            border-top: 2px solid #2c5f2d;
            border-bottom: none;
            font-weight: 700;
            font-size: 18px;
            padding-top: 15px;
            margin-top: 10px;
        }
        .cart-summary .summary-row .label {
            color: #666;
        }
        .cart-summary .summary-row .value {
            font-weight: 600;
            color: #333;
        }
        .cart-summary .summary-row.total .value {
            color: #2c5f2d;
        }
        
        .btn-checkout {
            background: linear-gradient(135deg, #1a472a, #2c5f2d);
            border: none;
            padding: 15px;
            font-weight: 600;
            color: white;
            width: 100%;
            border-radius: 12px;
            font-size: 16px;
            transition: all 0.3s;
            text-decoration: none;
            display: block;
            text-align: center;
            margin-top: 20px;
        }
        .btn-checkout:hover {
            color: white;
            opacity: 0.9;
            transform: translateY(-2px);
        }
        .btn-checkout:disabled {
            background: #ccc;
            cursor: not-allowed;
            transform: none;
        }
        
        .empty-cart {
            text-align: center;
            padding: 60px 20px;
        }
        .empty-cart .icon {
            font-size: 64px;
            color: #ccc;
            margin-bottom: 20px;
        }
        .empty-cart h4 {
            color: #333;
            margin-bottom: 10px;
        }
        .empty-cart p {
            color: #999;
            margin-bottom: 20px;
        }
        .empty-cart .btn-shop {
            background: #2c5f2d;
            color: white;
            padding: 12px 30px;
            border-radius: 30px;
            text-decoration: none;
            display: inline-block;
        }
        .empty-cart .btn-shop:hover {
            background: #1a472a;
        }
        
        /* ===== RESPONSIVE ===== */
        @media (max-width: 768px) {
            .cart-item {
                flex-wrap: wrap;
                gap: 10px;
                padding: 15px;
            }
            .cart-item .item-image {
                width: 60px;
                height: 60px;
            }
            .cart-item .item-info {
                flex: 1;
                min-width: 120px;
            }
            .cart-item .item-info h5 {
                font-size: 14px;
            }
            .cart-item .item-subtotal {
                min-width: 80px;
                font-size: 14px;
                text-align: left;
                width: 100%;
                padding-left: 70px;
            }
            .cart-summary {
                padding: 20px;
            }
            .page-title h1 {
                font-size: 24px;
            }
        }
        
        @media (max-width: 480px) {
            .cart-item .item-image {
                width: 50px;
                height: 50px;
            }
            .cart-item .item-info h5 {
                font-size: 13px;
            }
            .cart-item .item-qty input {
                width: 45px;
                padding: 4px;
                font-size: 12px;
            }
            .cart-item .item-subtotal {
                padding-left: 0;
                text-align: right;
                width: auto;
            }
        }
    </style>
</head>
<body>

<?php include '../includes/navbar.php'; ?>

<div class="cart-container">
    
    <div class="page-title">
        <h1>🛒 Keranjang Belanja</h1>
        <p>Tinjau pesanan Anda sebelum checkout</p>
    </div>
    
    <?php if(count($cart_items) > 0): ?>
    
    <div class="row g-4">
        <div class="col-lg-8">
            <div class="cart-card">
                <div class="cart-header">
                    <h4><i class="fas fa-shopping-cart"></i> Daftar Produk</h4>
                </div>
                
                <?php foreach($cart_items as $item): ?>
                <div class="cart-item" id="item-<?php echo $item['id']; ?>">
                    <div class="item-image">
                        <img src="../uploads/produk/produk_<?php echo $item['id']; ?>/1.jpg" 
                             alt="<?php echo $item['nama_produk']; ?>"
                             onerror="this.src='../uploads/produk/default.jpg'">
                    </div>
                    <div class="item-info">
                        <h5><?php echo htmlspecialchars($item['nama_produk']); ?></h5>
                        <div class="kode">Kode: <?php echo $item['kode_produk']; ?></div>
                        <div class="harga">Rp <?php echo number_format($item['harga'], 0, ',', '.'); ?></div>
                    </div>
                    <div class="item-qty">
                        <button onclick="updateQuantity(<?php echo $item['id']; ?>, 'decrement')">
                            <i class="fas fa-minus"></i>
                        </button>
                        <input type="number" id="qty-<?php echo $item['id']; ?>" 
                               value="<?php echo $item['jumlah']; ?>" min="1" 
                               onchange="updateQuantity(<?php echo $item['id']; ?>, 'set', this.value)">
                        <button onclick="updateQuantity(<?php echo $item['id']; ?>, 'increment')">
                            <i class="fas fa-plus"></i>
                        </button>
                    </div>
                    <div class="item-subtotal">
                        Rp <?php echo number_format($item['subtotal'], 0, ',', '.'); ?>
                    </div>
                    <button class="item-remove" onclick="removeItem(<?php echo $item['id']; ?>)">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        
        <div class="col-lg-4">
            <div class="cart-summary">
                <h5>Ringkasan Belanja</h5>
                
                <div class="summary-row">
                    <span class="label">Total Harga</span>
                    <span class="value">Rp <?php echo number_format($total, 0, ',', '.'); ?></span>
                </div>
                
                <div class="summary-row total">
                    <span class="label">Total</span>
                    <span class="value">Rp <?php echo number_format($total, 0, ',', '.'); ?></span>
                </div>
                
                <a href="checkout.php" class="btn-checkout">
                    <i class="fas fa-credit-card"></i> Checkout Sekarang
                </a>
            </div>
        </div>
    </div>
    
    <?php else: ?>
    
    <div class="cart-card">
        <div class="empty-cart">
            <div class="icon"><i class="fas fa-shopping-cart"></i></div>
            <h4>Keranjang Kosong</h4>
            <p>Yuk, pesan produk terbaik dari Kandang Berkah Jaya!</p>
            <a href="produk.php" class="btn-shop"><i class="fas fa-store"></i> Lihat Produk</a>
        </div>
    </div>
    
    <?php endif; ?>
    
</div>

<?php include '../includes/footer.php'; ?>

<script>
// =====================================================
// FUNGSI UPDATE QUANTITY
// =====================================================
function updateQuantity(productId, action, value = null) {
    let qtyInput = document.getElementById('qty-' + productId);
    let currentQty = parseInt(qtyInput.value);
    let newQty = currentQty;
    
    if(action === 'increment') {
        newQty = currentQty + 1;
    } else if(action === 'decrement' && currentQty > 1) {
        newQty = currentQty - 1;
    } else if(action === 'set' && value >= 1) {
        newQty = parseInt(value);
    }
    
    if(newQty !== currentQty && newQty >= 1) {
        fetch('../proses/update_keranjang.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'produk_id=' + productId + '&jumlah=' + newQty
        })
        .then(response => response.json())
        .then(data => {
            if(data.success) {
                location.reload();
            } else {
                alert('Gagal mengupdate keranjang');
            }
        });
    }
}

// =====================================================
// FUNGSI REMOVE ITEM
// =====================================================
function removeItem(productId) {
    if(confirm('Hapus item dari keranjang?')) {
        fetch('../proses/hapus_keranjang.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'produk_id=' + productId
        })
        .then(response => response.json())
        .then(data => {
            if(data.success) {
                location.reload();
            } else {
                alert('Gagal menghapus item');
            }
        });
    }
}
</script>

</body>
</html>