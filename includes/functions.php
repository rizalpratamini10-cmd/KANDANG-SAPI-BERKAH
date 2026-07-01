<?php
// =====================================================
// FUNGSI BANTUAN LENGKAP
// =====================================================

// Hash password menggunakan bcrypt
function hashPassword($password) {
    return password_hash($password, PASSWORD_DEFAULT);
}

// Verifikasi password
function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}

// Generate nomor invoice unik
function generateInvoice() {
    return 'INV/' . date('Ymd') . '/' . strtoupper(substr(uniqid(), -6));
}

// Format Rupiah
function formatRupiah($number) {
    return "Rp " . number_format($number, 0, ',', '.');
}

// Upload file
function uploadFile($file, $target_dir, $allowed_types = ['jpg', 'jpeg', 'png', 'pdf']) {
    if (!file_exists($target_dir)) {
        mkdir($target_dir, 0777, true);
    }
    
    $fileName = time() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '', basename($file['name']));
    $target_file = $target_dir . $fileName;
    $fileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
    
    if(!in_array($fileType, $allowed_types)) {
        return false;
    }
    
    if(move_uploaded_file($file['tmp_name'], $target_file)) {
        return $fileName;
    }
    return false;
}

// Get status badge
function getStatusBadge($status) {
    $badges = [
        'waiting_dp' => '<span class="badge-warning">⏳ Menunggu DP</span>',
        'process' => '<span class="badge-info">🔄 Proses</span>',
        'paid' => '<span class="badge-success">✅ Lunas</span>',
        'cancelled' => '<span class="badge-danger">❌ Dibatalkan</span>'
    ];
    return isset($badges[$status]) ? $badges[$status] : '<span class="badge-secondary">' . $status . '</span>';
}

// Clean input
function cleanInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}
?>