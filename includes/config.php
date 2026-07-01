<?php
// =====================================================
// KONFIGURASI DATABASE & WEBSITE
// Kandang Berkah Jaya | Balqys Aqiqah
// =====================================================

// Konfigurasi Database
define('DB_HOST', 'localhost');      // Server database (biasanya localhost)
define('DB_USER', 'root');           // Username MySQL (default: root)
define('DB_PASS', '');               // Password MySQL (kosongkan jika default)
define('DB_NAME', 'db_qurban2026');  // Nama database yang akan digunakan

// URL Website (sesuaikan dengan folder Anda)
// Contoh: 
// - Jika di root: http://localhost/
// - Jika di folder: http://localhost/kandang.berkah.jaya/
define('BASE_URL', 'http://localhost/kandang.berkah.jaya/');

// Nama Website
define('SITE_NAME', 'Kandang Berkah Jaya | Balqys Aqiqah');
define('SITE_TAGLINE', '@kambingsapibatam');

// Timezone Indonesia
date_default_timezone_set('Asia/Jakarta');

// Error Reporting (matikan saat production)
error_reporting(E_ALL);
ini_set('display_errors', 1);
?>