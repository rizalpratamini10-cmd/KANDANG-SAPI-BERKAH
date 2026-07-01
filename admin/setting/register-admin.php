<?php
require_once '../../includes/config.php';
require_once '../../includes/koneksi.php';
require_once '../../includes/session.php';

// Cek apakah sudah ada admin dengan role superadmin di tabel admin
$check = $conn->query("SELECT id FROM admin WHERE role = 'superadmin' LIMIT 1");
if ($check && $check->num_rows > 0) {
    die("<h2 style='color:orange;'>⚠️ Superadmin sudah terdaftar di tabel admin! Jika ingin membuat ulang, hapus dulu akun superadmin lama.</h2>");
}

$message = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $confirm = $_POST['confirm_password'];
    $nama = trim($_POST['nama_lengkap']);
    $email = trim($_POST['email']);

    if (empty($username) || empty($password) || empty($nama) || empty($email)) {
        $message = "Semua field wajib diisi!";
        $message_type = 'error';
    } elseif ($password !== $confirm) {
        $message = "Password dan konfirmasi tidak cocok!";
        $message_type = 'error';
    } elseif (strlen($password) < 6) {
        $message = "Password minimal 6 karakter!";
        $message_type = 'error';
    } else {
        // Cek username/email sudah ada di tabel admin
        $cek = $conn->query("SELECT id FROM admin WHERE username = '$username' OR email = '$email' LIMIT 1");
        if ($cek && $cek->num_rows > 0) {
            $message = "Username atau email sudah terdaftar!";
            $message_type = 'error';
        } else {
            // Hash password
            $hashed = password_hash($password, PASSWORD_DEFAULT);

            // Cek struktur tabel admin untuk tahu kolom role/level
            $columns = [];
            $col_result = $conn->query("SHOW COLUMNS FROM admin");
            while ($row = $col_result->fetch_assoc()) {
                $columns[] = $row['Field'];
            }

            $role_column = '';
            $role_value = '';
            if (in_array('role', $columns)) {
                $role_column = 'role';
                $role_value = 'superadmin';
            } elseif (in_array('level', $columns)) {
                $role_column = 'level';
                $role_value = '1';
            } elseif (in_array('hak_akses', $columns)) {
                $role_column = 'hak_akses';
                $role_value = 'superadmin';
            }

            $sql = "INSERT INTO admin (username, password, nama_lengkap, email";
            $values = "'$username', '$hashed', '$nama', '$email'";
            if ($role_column) {
                $sql .= ", $role_column";
                $values .= ", '$role_value'";
            }
            if (in_array('created_at', $columns)) {
                $sql .= ", created_at";
                $values .= ", NOW()";
            }
            $sql .= ") VALUES ($values)";

            if ($conn->query($sql) === TRUE) {
                $message = "✅ Akun Superadmin BERHASIL dibuat di tabel admin! Silakan login ke <a href='../loginadmin.php'>Login Admin</a>.";
                $message_type = 'success';
                $_POST = array();
            } else {
                $message = "❌ Gagal: " . $conn->error;
                $message_type = 'error';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Register Superadmin</title>
    <style>
        * { margin:0; padding:0; box-sizing:border-box; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #f0f2f5; display: flex; justify-content: center; align-items: center; min-height: 100vh; padding:20px; }
        .container { background: white; padding: 40px; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.1); width:100%; max-width:500px; }
        h1 { text-align:center; color:#1a3c34; margin-bottom:10px; }
        .subtitle { text-align:center; color:#666; margin-bottom:30px; font-size:14px; }
        .form-group { margin-bottom:18px; }
        label { display:block; font-weight:600; margin-bottom:5px; color:#333; font-size:14px; }
        input[type="text"], input[type="email"], input[type="password"] { width:100%; padding:12px 15px; border:1px solid #ddd; border-radius:8px; font-size:16px; }
        input:focus { border-color:#1a3c34; outline:none; box-shadow:0 0 0 3px rgba(26,60,52,0.1); }
        button { width:100%; padding:14px; background:#1a3c34; color:white; border:none; border-radius:8px; font-size:18px; font-weight:bold; cursor:pointer; }
        button:hover { background:#0f2a24; }
        .message { padding:12px 15px; border-radius:8px; margin-bottom:20px; font-weight:500; }
        .message.success { background:#d4edda; color:#155724; border:1px solid #c3e6cb; }
        .message.error { background:#f8d7da; color:#721c24; border:1px solid #f5c6cb; }
        .warning { background:#fff3cd; color:#856404; padding:10px; border-radius:6px; font-size:13px; margin-top:20px; border:1px solid #ffc107; text-align:center; }
        .login-link { text-align:center; margin-top:20px; font-size:14px; }
        .login-link a { color:#1a3c34; font-weight:bold; text-decoration:none; }
        .login-link a:hover { text-decoration:underline; }
        @media (max-width:600px){ .container { padding:25px 20px; } }
    </style>
</head>
<body>
<div class="container">
    <h1>🔐 Register Superadmin</h1>
    <p class="subtitle">Buat akun superadmin di tabel admin</p>
    <?php if ($message): ?>
        <div class="message <?= $message_type ?>"><?= $message ?></div>
    <?php endif; ?>
    <form method="POST">
        <div class="form-group">
            <label>Username</label>
            <input type="text" name="username" required value="<?= isset($_POST['username']) ? htmlspecialchars($_POST['username']) : '' ?>">
        </div>
        <div class="form-group">
            <label>Nama Lengkap</label>
            <input type="text" name="nama_lengkap" required value="<?= isset($_POST['nama_lengkap']) ? htmlspecialchars($_POST['nama_lengkap']) : '' ?>">
        </div>
        <div class="form-group">
            <label>Email</label>
            <input type="email" name="email" required value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '' ?>">
        </div>
        <div class="form-group">
            <label>Password (min. 6)</label>
            <input type="password" name="password" required>
        </div>
        <div class="form-group">
            <label>Konfirmasi Password</label>
            <input type="password" name="confirm_password" required>
        </div>
        <button type="submit">Daftar Superadmin</button>
    </form>
    <div class="login-link">
        Sudah punya akun? <a href="../loginadmin.php">Login Admin</a>
    </div>
    <div class="warning">
        ⚠️ <strong>Penting:</strong> Setelah berhasil, segera <strong>hapus file ini</strong> dari server!
    </div>
</div>
</body>
</html>