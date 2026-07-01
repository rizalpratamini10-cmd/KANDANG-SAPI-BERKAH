<?php
// =====================================================
// KONEKSI DATABASE
// =====================================================

// Panggil file konfigurasi
require_once 'config.php';

// Buat koneksi ke database
$conn = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Cek koneksi
if(!$conn) {
    die("Koneksi database gagal: " . mysqli_connect_error());
}

// Set charset ke UTF-8
mysqli_set_charset($conn, "utf8");

// Opsional: Simpan waktu koneksi untuk debugging
// $conn_time = date('Y-m-d H:i:s');
?>