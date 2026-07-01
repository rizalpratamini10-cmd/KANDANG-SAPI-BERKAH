<?php
require_once '../includes/config.php';
require_once '../includes/koneksi.php';
require_once '../includes/session.php';
require_once '../includes/auth.php';

redirectIfNotAdmin();

$tipe = isset($_GET['tipe']) ? $_GET['tipe'] : 'penjualan';

// Sementara redirect ke Excel
header("Location: export-excel.php?tipe=$tipe");
exit;
?>