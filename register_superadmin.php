<?php
// ================================================================
//  REGISTER SUPERADMIN - HANYA UNTUK PENDAFTARAN AWAL
//  SETELAH BERHASIL, SEGERA HAPUS FILE INI !!!
// ================================================================

require_once 'includes/config.php';
require_once 'includes/koneksi.php';
require_once 'includes/session.php';

// Cek apakah sudah ada superadmin
$check = $conn->query("SELECT id FROM users WHERE role = 'superadmin' LIMIT 1");
if ($check->num_rows > 0) {
    die("<h2 style='color:orange;'>⚠️ Superadmin sudah terdaftar!</h2>
         <p>Jika ingin membuat ulang, hapus dulu akun superadmin yang lama di database.</p>");
}

// Proses form
$message = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $email    = trim($_POST['email']);
    $nama     = trim($_POST['nama_lengkap']);
    $no_hp    = trim($_POST['no_hp']);

    // Validasi
    if (empty($username) || empty($password) || empty($email) || empty($nama)) {
        $message = "Semua field harus diisi!";
        $message_type = 'error';
    } elseif ($password !== $confirm_password) {
        $message = "Password dan konfirmasi tidak cocok!";
        $message_type = 'error';
    } elseif (strlen($password) < 6) {
        $message = "Password minimal 6 karakter!";
        $message_type = 'error';
    } else {
        // Cek username/email sudah ada
        $check_user = $conn->query("SELECT id FROM users WHERE username = '$username' OR email = '$email' LIMIT 1");
        if ($check_user->num_rows > 0) {
            $message = "Username atau email sudah terdaftar!";
            $message_type = 'error';
        } else {
            // Hash password menggunakan password_hash (compatible dengan verifyPassword)
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            // Insert ke tabel users (sesuaikan kolom)
            $sql = "INSERT INTO users (username, password, email, nama_lengkap, no_hp, role, is_active, created_at) 
                    VALUES ('$username', '$hashed_password', '$email', '$nama', '$no_hp', 'superadmin', 1, NOW())";

            if ($conn->query($sql) === TRUE) {
                $message = "✅ Akun Superadmin BERHASIL dibuat! Silakan login.";
                $message_type = 'success';
                // Kosongkan form
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
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register Superadmin</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f0f2f5;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 20px;
        }
        .container {
            background: white;
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
            width: 100%;
            max-width: 500px;
        }
        h1 {
            text-align: center;
            color: #1a3c34;
            margin-bottom: 10px;
        }
        .subtitle {
            text-align: center;
            color: #666;
            margin-bottom: 30px;
            font-size: 14px;
        }
        .form-group {
            margin-bottom: 18px;
        }
        label {
            display: block;
            font-weight: 600;
            margin-bottom: 5px;
            color: #333;
            font-size: 14px;
        }
        input[type="text"],
        input[type="email"],
        input[type="password"] {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 16px;
            transition: border 0.3s;
        }
        input:focus {
            border-color: #1a3c34;
            outline: none;
            box-shadow: 0 0 0 3px rgba(26,60,52,0.1);
        }
        button {
            width: 100%;
            padding: 14px;
            background: #1a3c34;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 18px;
            font-weight: bold;
            cursor: pointer;
            transition: background 0.3s;
        }
        button:hover {
            background: #0f2a24;
        }
        .message {
            padding: 12px 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-weight: 500;
        }
        .message.success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .message.error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .warning {
            background: #fff3cd;
            color: #856404;
            padding: 10px;
            border-radius: 6px;
            font-size: 13px;
            margin-top: 20px;
            border: 1px solid #ffc107;
            text-align: center;
        }
        .login-link {
            text-align: center;
            margin-top: 20px;
            font-size: 14px;
        }
        .login-link a {
            color: #1a3c34;
            font-weight: bold;
            text-decoration: none;
        }
        .login-link a:hover {
            text-decoration: underline;
        }
        @media (max-width: 600px) {
            .container { padding: 25px 20px; }
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔐 Register Superadmin</h1>
        <p class="subtitle">Buat akun superadmin untuk pertama kali</p>

        <?php if (!empty($message)): ?>
            <div class="message <?= $message_type ?>"><?= $message ?></div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="form-group">
                <label>Username</label>
                <input type="text" name="username" placeholder="Masukkan username" required
                       value="<?= isset($_POST['username']) ? htmlspecialchars($_POST['username']) : '' ?>">
            </div>
            <div class="form-group">
                <label>Nama Lengkap</label>
                <input type="text" name="nama_lengkap" placeholder="Masukkan nama lengkap" required
                       value="<?= isset($_POST['nama_lengkap']) ? htmlspecialchars($_POST['nama_lengkap']) : '' ?>">
            </div>
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" placeholder="Masukkan email" required
                       value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '' ?>">
            </div>
            <div class="form-group">
                <label>No. HP</label>
                <input type="text" name="no_hp" placeholder="Masukkan no. HP (opsional)"
                       value="<?= isset($_POST['no_hp']) ? htmlspecialchars($_POST['no_hp']) : '' ?>">
            </div>
            <div class="form-group">
                <label>Password (min. 6 karakter)</label>
                <input type="password" name="password" placeholder="Masukkan password" required>
            </div>
            <div class="form-group">
                <label>Konfirmasi Password</label>
                <input type="password" name="confirm_password" placeholder="Ulangi password" required>
            </div>
            <button type="submit">Daftar Superadmin</button>
        </form>

        <div class="login-link">
            Sudah punya akun? <a href="loginadmin.php">Login Admin</a>
        </div>

        <div class="warning">
            ⚠️ <strong>Penting:</strong> Setelah berhasil, segera <strong>hapus file ini</strong> dari server!
        </div>
    </div>
</body>
</html>