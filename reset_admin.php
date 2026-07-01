<?php
require_once 'includes/config.php';
require_once 'includes/koneksi.php';
require_once 'includes/functions.php';

// Ganti password admin menjadi 'admin123'
$new_password = 'admin123';
$hashed = hashPassword($new_password);

// Update password admin
$query = "UPDATE admin SET password = '$hashed' WHERE username = 'admin'";

if(mysqli_query($conn, $query)) {
    echo "<h2>✅ Password Admin Berhasil Direset!</h2>";
    echo "<p>Username: <strong>admin</strong></p>";
    echo "<p>Password: <strong>admin123</strong></p>";
    echo "<hr>";
    echo "<a href='login-admin.php'>Klik di sini untuk login</a>";
} else {
    echo "❌ Gagal: " . mysqli_error($conn);
}

// Juga tampilkan hash untuk referensi
echo "<p style='margin-top:20px; font-size:11px; color:#666;'>Hash: $hashed</p>";
?>