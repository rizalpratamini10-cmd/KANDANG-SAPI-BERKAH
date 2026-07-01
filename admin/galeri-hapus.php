<?php
require_once '../includes/config.php';
require_once '../includes/koneksi.php';
require_once '../includes/session.php';
require_once '../includes/auth.php';

redirectIfNotAdmin();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if($id) {
    // Ambil nama file gambar
    $query = "SELECT gambar FROM galeri WHERE id = $id";
    $result = mysqli_query($conn, $query);
    $row = mysqli_fetch_assoc($result);
    
    if($row && file_exists("../uploads/galeri/" . $row['gambar'])) {
        unlink("../uploads/galeri/" . $row['gambar']);
    }
    
    mysqli_query($conn, "DELETE FROM galeri WHERE id = $id");
}

header("Location: galeri.php");
exit;
?>