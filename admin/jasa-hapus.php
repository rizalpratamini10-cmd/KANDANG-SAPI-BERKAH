<?php
require_once '../includes/config.php';
require_once '../includes/koneksi.php';
require_once '../includes/session.php';
require_once '../includes/auth.php';

redirectIfNotAdmin();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if($id) {
    $query = "DELETE FROM produk WHERE id = $id";
    mysqli_query($conn, $query);
}

header("Location: jasa.php");
exit;
?>
