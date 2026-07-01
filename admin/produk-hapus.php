<?php
require_once '../includes/config.php';
require_once '../includes/koneksi.php';
require_once '../includes/session.php';
require_once '../includes/auth.php';

redirectIfNotAdmin();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Ambil info produk untuk hapus gambar
$query = "SELECT gambar FROM produk WHERE id = $id";
$result = mysqli_query($conn, $query);
$produk = mysqli_fetch_assoc($result);

if($produk) {
    // Hapus file gambar
    if($produk['gambar'] && file_exists("../uploads/produk/" . $produk['gambar'])) {
        unlink("../uploads/produk/" . $produk['gambar']);
    }
    
    // Hapus data produk
    $delete = "DELETE FROM produk WHERE id = $id";
    mysqli_query($conn, $delete);
}

header("Location: produk.php");
exit;
?>