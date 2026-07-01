<?php
// =====================================================
// AUTENTIKASI LOGIN (Customer & Admin)
// =====================================================

require_once 'session.php';

// Cek apakah sudah login
function isLoggedIn() {
    return isset($_SESSION['customer_id']) || isset($_SESSION['admin_id']);
}

// Cek apakah customer
function isCustomer() {
    return isset($_SESSION['customer_id']) && $_SESSION['role'] == 'customer';
}

// Cek apakah admin (termasuk super admin)
function isAdmin() {
    return isset($_SESSION['admin_id']) && ($_SESSION['role'] == 'admin' || $_SESSION['role'] == 'super_admin');
}

// Cek apakah super admin
function isSuperAdmin() {
    return isset($_SESSION['admin_id']) && $_SESSION['role'] == 'super_admin';
}

// Cek apakah admin biasa (bukan super admin)
function isRegularAdmin() {
    return isset($_SESSION['admin_id']) && $_SESSION['role'] == 'admin';
}

// Redirect jika belum login
function redirectIfNotLoggedIn() {
    if(!isLoggedIn()) {
        header("Location: " . BASE_URL . "index.php");
        exit;
    }
}

// Redirect jika bukan customer
function redirectIfNotCustomer() {
    if(!isCustomer()) {
        header("Location: " . BASE_URL . "index.php");
        exit;
    }
}

// Redirect jika bukan admin (termasuk super admin)
function redirectIfNotAdmin() {
    if(!isAdmin()) {
        header("Location: " . BASE_URL . "index.php");
        exit;
    }
}

// Redirect jika bukan super admin (untuk halaman khusus super admin)
function redirectIfNotSuperAdmin() {
    if(!isSuperAdmin()) {
        header("Location: " . BASE_URL . "admin/dashboard.php");
        exit;
    }
}

// Ambil data customer yang login
function getCurrentCustomer($conn) {
    if(isCustomer()) {
        $id = (int)$_SESSION['customer_id'];
        $query = "SELECT * FROM customers WHERE id = '$id'";
        $result = mysqli_query($conn, $query);
        return mysqli_fetch_assoc($result);
    }
    return null;
}

// Ambil data admin yang login
function getCurrentAdmin($conn) {
    if(isAdmin()) {
        $id = (int)$_SESSION['admin_id'];
        $query = "SELECT * FROM admin WHERE id = '$id'";
        $result = mysqli_query($conn, $query);
        return mysqli_fetch_assoc($result);
    }
    return null;
}

// Cek akses halaman (untuk admin biasa)
function checkAdminAccess($allowed_pages = []) {
    if(isSuperAdmin()) {
        return true;
    }
    if(isRegularAdmin()) {
        $current_page = basename($_SERVER['PHP_SELF']);
        foreach($allowed_pages as $page) {
            if(strpos($current_page, $page) !== false) {
                return true;
            }
        }
        // Jika tidak diizinkan, redirect ke dashboard
        header("Location: " . BASE_URL . "admin/dashboard.php?error=access_denied");
        exit;
    }
    return false;
}
?>