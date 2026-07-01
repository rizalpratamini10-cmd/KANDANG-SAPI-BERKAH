<?php
require_once '../../includes/config.php';
require_once '../../includes/koneksi.php';
require_once '../../includes/session.php';
require_once '../../includes/auth.php';

redirectIfNotAdmin();

// Cek apakah super admin
if($_SESSION['admin_level'] != 'super_admin') {
    header("Location: ../dashboard.php");
    exit;
}

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Cek jangan hapus diri sendiri
if($id == $_SESSION['admin_id']) {
    header("Location: admin.php?error=self");
    exit;
}

if($id > 0) {
    mysqli_query($conn, "DELETE FROM admin WHERE id = $id");
}

header("Location: admin.php");
exit;
?>