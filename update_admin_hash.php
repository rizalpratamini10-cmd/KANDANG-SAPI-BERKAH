<?php
require_once 'includes/config.php';
require_once 'includes/koneksi.php';
require_once 'includes/functions.php';

// Update password admin ke hash
$new_password = 'admin123';
$hashed = hashPassword($new_password);

$query = "UPDATE admin SET password = '$hashed' WHERE username = 'admin'";
if(mysqli_query($conn, $query)) {
    echo "✅ Password admin berhasil diupdate ke hash!<br>";
    echo "Username: admin<br>";
    echo "Password: admin123<br>";
    echo "Hash: " . $hashed;
} else {
    echo "❌ Gagal: " . mysqli_error($conn);
}
?>