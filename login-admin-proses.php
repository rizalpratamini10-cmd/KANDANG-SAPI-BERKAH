<?php
require_once 'includes/config.php';
require_once 'includes/koneksi.php';
require_once 'includes/session.php';
require_once 'includes/functions.php';

$username = isset($_POST['username']) ? mysqli_real_escape_string($conn, $_POST['username']) : '';
$password = isset($_POST['password']) ? $_POST['password'] : '';

// ================================================
// 1. CEK DI TABEL admin (ADMIN BIASA)
// ================================================
$query_admin = "SELECT * FROM admin WHERE username = '$username'";
$result_admin = mysqli_query($conn, $query_admin);

if (mysqli_num_rows($result_admin) == 1) {
    $admin = mysqli_fetch_assoc($result_admin);
    
    // Verifikasi password (pakai fungsi verifyPassword dari functions.php)
    if (verifyPassword($password, $admin['password'])) {
        $_SESSION['admin_id'] = $admin['id'];
        $_SESSION['admin_username'] = $admin['username'];
        $_SESSION['admin_nama'] = $admin['nama_lengkap'];
        $_SESSION['role'] = 'admin';
        
        // Update last login
        $update = "UPDATE admin SET last_login = NOW() WHERE id = " . $admin['id'];
        mysqli_query($conn, $update);
        
        header("Location: admin/dashboard.php");
        exit;
    } else {
        header("Location: login-admin.php?error=true");
        exit;
    }
}

// ================================================
// 2. CEK DI TABEL users (SUPERADMIN)
// ================================================
$query_user = "SELECT * FROM users WHERE username = '$username' AND role = 'superadmin' AND is_active = 1";
$result_user = mysqli_query($conn, $query_user);

if (mysqli_num_rows($result_user) == 1) {
    $user = mysqli_fetch_assoc($result_user);
    
    // Verifikasi password (pakai fungsi verifyPassword yang sama)
    if (verifyPassword($password, $user['password'])) {
        $_SESSION['admin_id'] = $user['id'];
        $_SESSION['admin_username'] = $user['username'];
        $_SESSION['admin_nama'] = $user['nama_lengkap'] ?? $user['username'];
        $_SESSION['role'] = 'superadmin'; // bedakan role
        
        // Update last login (jika ada kolom last_login di users)
        // Jika tidak ada kolom last_login, abaikan baris ini
        // $update = "UPDATE users SET last_login = NOW() WHERE id = " . $user['id'];
        // mysqli_query($conn, $update);
        
        header("Location: admin/dashboard.php");
        exit;
    } else {
        header("Location: login-admin.php?error=true");
        exit;
    }
}

// ================================================
// 3. JIKA TIDAK DITEMUKAN DI KEDUA TABEL
// ================================================
header("Location: login-admin.php?error=true");
exit;
?>