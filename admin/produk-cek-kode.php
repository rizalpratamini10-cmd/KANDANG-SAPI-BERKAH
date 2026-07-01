<?php
require_once '../includes/config.php';
require_once '../includes/koneksi.php';

$kode = isset($_GET['kode']) ? mysqli_real_escape_string($conn, $_GET['kode']) : '';

if($kode) {
    $query = "SELECT * FROM produk WHERE kode_produk = '$kode'";
    $result = mysqli_query($conn, $query);
    
    if(mysqli_num_rows($result) > 0) {
        echo json_encode(['exists' => true]);
    } else {
        echo json_encode(['exists' => false]);
    }
} else {
    echo json_encode(['exists' => false]);
}
?>