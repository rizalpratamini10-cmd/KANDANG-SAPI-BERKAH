<?php
require_once '../includes/config.php';
require_once '../includes/koneksi.php';
require_once '../includes/session.php';
require_once '../includes/auth.php';

redirectIfNotAdmin();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if($id) {
    // Hapus customer (pesanan akan otomatis terhapus karena foreign key CASCADE)
    $query = "DELETE FROM customers WHERE id = $id";
    mysqli_query($conn, $query);
}

header("Location: customer.php");
exit;
?>