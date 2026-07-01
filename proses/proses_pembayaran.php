<?php
session_start();
header('Content-Type: application/json');

require_once '../includes/config.php';
require_once '../includes/koneksi.php';
require_once '../includes/functions.php';

// Cek login
if(!isset($_SESSION['customer_id'])) {
    echo json_encode(['success' => false, 'message' => 'Silakan login terlebih dahulu']);
    exit;
}

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $customer_id = $_SESSION['customer_id'];
    
    // Upload file bukti pembayaran
    $bukti_pembayaran = '';
    if(isset($_FILES['bukti_pembayaran']) && $_FILES['bukti_pembayaran']['error'] == 0) {
        $target_dir = '../uploads/bukti-transfer/';
        
        if(!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        
        $file_extension = pathinfo($_FILES['bukti_pembayaran']['name'], PATHINFO_EXTENSION);
        $bukti_pembayaran = 'bukti_' . time() . '_' . $customer_id . '.' . $file_extension;
        $target_file = $target_dir . $bukti_pembayaran;
        
        // Validasi file
        $allowed_types = ['jpg', 'jpeg', 'png'];
        if(!in_array(strtolower($file_extension), $allowed_types)) {
            echo json_encode(['success' => false, 'message' => 'Format file harus JPG atau PNG']);
            exit;
        }
        
        if($_FILES['bukti_pembayaran']['size'] > 2 * 1024 * 1024) {
            echo json_encode(['success' => false, 'message' => 'Ukuran file maksimal 2MB']);
            exit;
        }
        
        if(!move_uploaded_file($_FILES['bukti_pembayaran']['tmp_name'], $target_file)) {
            echo json_encode(['success' => false, 'message' => 'Gagal upload bukti pembayaran']);
            exit;
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Bukti pembayaran wajib diupload']);
        exit;
    }
    
    // Ambil data dari form (tanpa email)
    $nama_penerima = isset($_POST['nama_penerima']) ? mysqli_real_escape_string($conn, $_POST['nama_penerima']) : '';
    $no_hp = isset($_POST['no_hp']) ? mysqli_real_escape_string($conn, $_POST['no_hp']) : '';
    $alamat = isset($_POST['alamat']) ? mysqli_real_escape_string($conn, $_POST['alamat']) : '';
    $catatan = isset($_POST['catatan']) ? mysqli_real_escape_string($conn, $_POST['catatan']) : '';
    $metode_pembayaran = isset($_POST['metode_pembayaran']) ? (int)$_POST['metode_pembayaran'] : 0;
    $tipe_pembayaran = isset($_POST['tipe_pembayaran']) ? $_POST['tipe_pembayaran'] : 'lunas';
    $total_harga = isset($_POST['total_harga']) ? (int)$_POST['total_harga'] : 0;
    $produk_ids = isset($_POST['produk_id']) ? $_POST['produk_id'] : [];

    // Validasi
    if(empty($nama_penerima) || empty($no_hp) || empty($alamat) || empty($produk_ids) || $total_harga <= 0) {
        echo json_encode(['success' => false, 'message' => 'Data tidak lengkap']);
        exit;
    }
    
    if($metode_pembayaran <= 0) {
        echo json_encode(['success' => false, 'message' => 'Pilih metode pembayaran']);
        exit;
    }
    
    // Update data customer
    $update_customer = "UPDATE customers SET no_hp='$no_hp', alamat='$alamat' WHERE id=$customer_id";
    mysqli_query($conn, $update_customer);
    
    // Buat invoice
    $invoice = generateInvoice();
    
    // Hitung DP
    $dp_amount = 0;
    $sisa = 0;
    if($tipe_pembayaran == 'dp') {
        $dp_amount = $total_harga * 0.5;
        $sisa = $total_harga - $dp_amount;
    }
    
    // Mulai transaction
    mysqli_begin_transaction($conn);
    
    try {
        $success_count = 0;
        $errors = [];
        
        foreach($produk_ids as $produk_id) {
            $produk_id = (int)$produk_id;
            
            // Cek stok
            $cek = mysqli_query($conn, "SELECT stok, nama_produk FROM produk WHERE id = $produk_id");
            $stok_data = mysqli_fetch_assoc($cek);
            
            if(!$stok_data) {
                $errors[] = "Produk tidak ditemukan";
                continue;
            }
            
            if($stok_data['stok'] != 1) {
                $errors[] = "Produk " . $stok_data['nama_produk'] . " sudah habis!";
                continue;
            }
            
            // Insert pesanan (TANPA metode_bayar_id)
            $query = "INSERT INTO pesanan (
                id_customer, 
                id_produk, 
                invoice, 
                tipe_pembayaran, 
                total_harga, 
                dp_amount, 
                sisa_pembayaran, 
                status, 
                bukti_transfer, 
                catatan
            ) VALUES (
                $customer_id, 
                $produk_id, 
                '$invoice', 
                '$tipe_pembayaran', 
                $total_harga, 
                $dp_amount, 
                $sisa, 
                'waiting_dp', 
                '$bukti_pembayaran', 
                'Nama: $nama_penerima, HP: $no_hp, Alamat: $alamat, Catatan: $catatan'
            )";
            
            if(mysqli_query($conn, $query)) {
                // Kurangi stok
                mysqli_query($conn, "UPDATE produk SET stok = 0, status = 'habis', updated_at = NOW() WHERE id = $produk_id");
                $success_count++;
            } else {
                $errors[] = "Gagal menyimpan pesanan: " . mysqli_error($conn);
            }
        }
        
        if($success_count > 0) {
            // Kosongkan keranjang (session)
            unset($_SESSION['cart']);
            
            mysqli_commit($conn);
            
            echo json_encode([
                'success' => true, 
                'order_id' => $invoice,
                'message' => 'Pesanan berhasil dibuat'
            ]);
        } else {
            mysqli_rollback($conn);
            echo json_encode(['success' => false, 'message' => implode(', ', $errors)]);
        }
        
    } catch(Exception $e) {
        mysqli_rollback($conn);
        echo json_encode(['success' => false, 'message' => 'Gagal memproses pesanan: ' . $e->getMessage()]);
    }
}
?>