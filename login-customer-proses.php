<?php
require_once 'includes/config.php';
require_once 'includes/koneksi.php';
require_once 'includes/session.php';
require_once 'includes/functions.php'; // <-- TAMBAHKAN INI (WAJIB)

$no_hp = isset($_POST['no_hp']) ? mysqli_real_escape_string($conn, $_POST['no_hp']) : '';
$password = isset($_POST['password']) ? $_POST['password'] : '';

// Cek customer berdasarkan no_hp
$query = "SELECT * FROM customers WHERE no_hp = '$no_hp'";
$result = mysqli_query($conn, $query);

if(mysqli_num_rows($result) == 1) {
    $customer = mysqli_fetch_assoc($result);
    
    // VERIFIKASI PASSWORD (GANTI md5 DENGAN verifyPassword)
    if(verifyPassword($password, $customer['password'])) {
        $_SESSION['customer_id'] = $customer['id'];
        $_SESSION['customer_nama'] = $customer['nama'];
        $_SESSION['customer_no_hp'] = $customer['no_hp'];
        $_SESSION['role'] = 'customer';
        
        header("Location: customer/index.php");
        exit;
    } else {
        header("Location: login-customer.php?error=true");
        exit;
    }
} else {
    header("Location: login-customer.php?error=true");
    exit;
}
?>