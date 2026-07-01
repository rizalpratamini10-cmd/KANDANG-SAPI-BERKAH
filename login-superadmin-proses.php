<?php
require_once 'includes/config.php';
require_once 'includes/koneksi.php';
require_once 'includes/session.php';
require_once 'includes/functions.php'; // untuk verifyPassword

$username = isset($_POST['username']) ? mysqli_real_escape_string($conn, $_POST['username']) : '';
$password = isset($_POST['password']) ? $_POST['password'] : '';

if (empty($username) || empty($password)) {
    header("Location: login-superadmin.php?error=true");
    exit;
}

// Cek di tabel users dengan role superadmin dan is_active = 1
$query = "SELECT * FROM users WHERE username = '$username' AND role = 'superadmin' AND is_active = 1";
$result = mysqli_query($conn, $query);

if (mysqli_num_rows($result) == 1) {
    $user = mysqli_fetch_assoc($result);
    
    // Verifikasi password menggunakan fungsi verifyPassword (dari functions.php)
    if (verifyPassword($password, $user['password'])) {
        // Set session untuk admin (sesuai dengan yang digunakan di dashboard)
        $_SESSION['admin_id'] = $user['id'];
        $_SESSION['admin_username'] = $user['username'];
        $_SESSION['admin_nama'] = $user['nama_lengkap'];
        $_SESSION['role'] = 'superadmin'; // tanda khusus superadmin
        
        // Update last login jika ada kolomnya
        if (isset($user['last_login'])) {
            $update = "UPDATE users SET last_login = NOW() WHERE id = " . $user['id'];
            mysqli_query($conn, $update);
        }
        
        header("Location: admin/dashboard.php");
        exit;
    } else {
        header("Location: login-superadmin.php?error=true");
        exit;
    }
} else {
    header("Location: login-superadmin.php?error=true");
    exit;
}
?>