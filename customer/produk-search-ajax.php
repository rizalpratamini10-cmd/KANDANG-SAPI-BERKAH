<?php
require_once '../includes/config.php';
require_once '../includes/koneksi.php';

$keyword = isset($_GET['keyword']) ? mysqli_real_escape_string($conn, $_GET['keyword']) : '';

$products = [];

if(strlen($keyword) >= 2) {
    $query = "SELECT p.id, p.nama_produk, p.kode_produk, p.harga, p.gambar 
              FROM produk p
              WHERE p.stok = 1 
              AND (p.nama_produk LIKE '%$keyword%' OR p.kode_produk LIKE '%$keyword%')
              LIMIT 10";
    
    $result = mysqli_query($conn, $query);
    
    while($row = mysqli_fetch_assoc($result)) {
        $products[] = [
            'id' => $row['id'],
            'nama_produk' => $row['nama_produk'],
            'kode_produk' => $row['kode_produk'],
            'harga' => $row['harga'],
            'gambar' => $row['gambar']
        ];
    }
}

header('Content-Type: application/json');
echo json_encode($products);
?>