<?php
// File: get_hash.php
// Jalankan file ini di browser untuk mendapatkan hash password

$password = 'admin123'; // Ganti dengan password yang diinginkan

$hash = password_hash($password, PASSWORD_DEFAULT);

echo "<h2>Hash untuk password: <strong>$password</strong></h2>";
echo "<h3>Hash: <code>$hash</code></h3>";
echo "<hr>";
echo "<p>Copy hash di atas, lalu ganti di file SQL atau langsung update di database:</p>";
echo "<pre>UPDATE admin SET password = '$hash' WHERE username = 'admin';</pre>";
?>