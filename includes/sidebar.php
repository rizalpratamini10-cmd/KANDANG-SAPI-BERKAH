<?php
// Asumsikan BASE_URL sudah didefinisikan di config
?>
<div class="admin-sidebar">
    <div class="sidebar-logo">
        <div class="logo-container">
            <img src="<?php echo BASE_URL; ?>assets/img/logo/logoberkah.jpeg" alt="Kandang Berkah">
            <img src="<?php echo BASE_URL; ?>assets/img/logo/logobalqis.jpeg" alt="Balqys Aqiqah">
        </div>
        <h3>KANDANG BERKAH</h3>
        <small>Admin Panel</small>
    </div>
    
    <ul class="sidebar-menu">
        <li><a href="<?php echo BASE_URL; ?>admin/dashboard.php" class="sidebar-link">
            <span class="icon">📊</span> Dashboard
        </a></li>
        <li><a href="<?php echo BASE_URL; ?>admin/pesanan.php" class="sidebar-link">
            <span class="icon">📋</span> Pesanan
        </a></li>
        <li><a href="<?php echo BASE_URL; ?>admin/customer.php" class="sidebar-link">
            <span class="icon">👥</span> Customer
        </a></li>
        <li><a href="<?php echo BASE_URL; ?>admin/followup.php" class="sidebar-link">
            <span class="icon">💬</span> Follow Up
        </a></li>
        <li><a href="<?php echo BASE_URL; ?>admin/produk.php" class="sidebar-link">
            <span class="icon">🛍️</span> Produk (Stok 1)
        </a></li>
        <li><a href="<?php echo BASE_URL; ?>admin/jasa.php" class="sidebar-link">
            <span class="icon">🙏</span> Jasa
        </a></li>
        <li><a href="<?php echo BASE_URL; ?>admin/metode-pembayaran.php" class="sidebar-link">
            <span class="icon">🏦</span> Metode Pembayaran
        </a></li>
        <li><a href="<?php echo BASE_URL; ?>admin/galeri.php" class="sidebar-link">
            <span class="icon">📸</span> Galeri
        </a></li>

        <!-- ===== SETTING DROPDOWN ===== -->
        <li>
            <a href="javascript:void(0)" class="sidebar-link" onclick="toggleSetting()">
                <span class="icon">⚙️</span> Setting ▼
            </a>
            <ul class="dropdown-menu-admin" id="menuSetting">
                <li><a href="<?php echo BASE_URL; ?>admin/setting/register-admin.php">📋 Daftar Admin</a></li>
                <li><a href="<?php echo BASE_URL; ?>admin/setting/profile.php">👤 Profile</a></li>
            </ul>
        </li>
        
        <!-- ===== LAPORAN DROPDOWN ===== -->
        <li>
            <a href="javascript:void(0)" class="sidebar-link" onclick="toggleLaporan()">
                <span class="icon">📊</span> Laporan ▼
            </a>
            <ul class="dropdown-menu-admin" id="menuLaporan">
                <li><a href="<?php echo BASE_URL; ?>admin/laporan-penjualan.php">📈 Penjualan</a></li>
                <li><a href="<?php echo BASE_URL; ?>admin/laporan-customer.php">👥 Customer</a></li>
                <li><a href="<?php echo BASE_URL; ?>admin/laporan-keuangan.php">💰 Keuangan</a></li>
                <li><a href="<?php echo BASE_URL; ?>admin/laporan-stok.php">📦 Stok Produk</a></li>
            </ul>
        </li>
        
        <li><a href="<?php echo BASE_URL; ?>logout.php" class="sidebar-link logout">
            <span class="icon">🚪</span> Logout
        </a></li>
    </ul>
</div>

<!-- ======================================== -->
<!-- CSS (dapat diletakkan di file terpisah)  -->
<!-- ======================================== -->
<style>
/* ===== SIDEBAR WRAPPER ===== */
.admin-sidebar {
    width: 260px;
    height: 100vh;
    background: #1a3c34;
    color: #fff;
    position: fixed;
    top: 0;
    left: 0;
    overflow-y: auto;
    box-shadow: 2px 0 10px rgba(0,0,0,0.2);
    transition: all 0.3s;
    z-index: 999;
}

.admin-sidebar::-webkit-scrollbar {
    width: 4px;
}
.admin-sidebar::-webkit-scrollbar-thumb {
    background: #ffd700;
    border-radius: 4px;
}

/* ===== LOGO AREA ===== */
.admin-sidebar .sidebar-logo {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 20px 15px 15px;
    border-bottom: 1px solid rgba(255,255,255,0.1);
    margin-bottom: 10px;
}

.admin-sidebar .sidebar-logo .logo-container {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 12px;
    margin-bottom: 10px;
    width: 100%;
}

.admin-sidebar .sidebar-logo .logo-container img {
    width: 55px;
    height: 55px;
    object-fit: contain;
    border-radius: 10px;
    background: white;
    padding: 4px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.2);
    transition: transform 0.3s;
}

.admin-sidebar .sidebar-logo .logo-container img:hover {
    transform: scale(1.05);
}

.admin-sidebar .sidebar-logo h3 {
    color: #ffd700;
    font-size: 18px;
    font-weight: 700;
    margin: 0;
    letter-spacing: 1px;
    text-align: center;
    line-height: 1.2;
}

.admin-sidebar .sidebar-logo small {
    color: rgba(255,255,255,0.6);
    font-size: 11px;
    text-transform: uppercase;
    letter-spacing: 2px;
    text-align: center;
}

/* ===== MENU ===== */
.admin-sidebar .sidebar-menu {
    list-style: none;
    padding: 0;
    margin: 0;
}

.admin-sidebar .sidebar-menu li {
    margin: 2px 0;
}

.admin-sidebar .sidebar-menu .sidebar-link {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 12px 20px;
    color: rgba(255,255,255,0.7);
    text-decoration: none;
    font-size: 14px;
    font-weight: 500;
    transition: all 0.3s;
    border-left: 3px solid transparent;
}

.admin-sidebar .sidebar-menu .sidebar-link:hover {
    background: rgba(255,255,255,0.05);
    color: #fff;
    border-left-color: #ffd700;
}

.admin-sidebar .sidebar-menu .sidebar-link .icon {
    font-size: 18px;
    width: 24px;
    text-align: center;
}

.admin-sidebar .sidebar-menu .sidebar-link.logout {
    border-top: 1px solid rgba(255,255,255,0.1);
    margin-top: 10px;
    color: #ff6b6b;
}

.admin-sidebar .sidebar-menu .sidebar-link.logout:hover {
    background: rgba(255,0,0,0.1);
    border-left-color: #ff6b6b;
}

/* ===== DROPDOWN ===== */
.admin-sidebar .dropdown-menu-admin {
    display: none;
    list-style: none;
    padding-left: 15px;
    margin: 0;
    background: rgba(0,0,0,0.15);
}

.admin-sidebar .dropdown-menu-admin.show {
    display: block;
}

.admin-sidebar .dropdown-menu-admin li a {
    display: block;
    padding: 8px 20px 8px 35px;
    color: rgba(255,255,255,0.6);
    text-decoration: none;
    font-size: 13px;
    transition: all 0.3s;
}

.admin-sidebar .dropdown-menu-admin li a:hover {
    color: #fff;
    background: rgba(255,215,0,0.1);
}

/* ===== RESPONSIVE ===== */
@media (max-width: 768px) {
    .admin-sidebar {
        width: 220px;
    }
    .admin-sidebar .sidebar-logo .logo-container img {
        width: 45px;
        height: 45px;
    }
    .admin-sidebar .sidebar-logo h3 {
        font-size: 16px;
    }
}
</style>

<!-- ======================================== -->
<!-- JAVASCRIPT UNTUK DROPDOWN                -->
<!-- ======================================== -->
<script>
// ================================================
// TOGGLE SETTING DROPDOWN
// ================================================
function toggleSetting() {
    var menu = document.getElementById('menuSetting');
    menu.classList.toggle('show');
}

// ================================================
// TOGGLE LAPORAN DROPDOWN
// ================================================
function toggleLaporan() {
    var menu = document.getElementById('menuLaporan');
    menu.classList.toggle('show');
}

// ================================================
// TUTUP DROPDOWN SAAT KLIK DI LUAR
// ================================================
document.addEventListener('click', function(event) {
    // Untuk Setting
    var menuSetting = document.getElementById('menuSetting');
    var linkSetting = document.querySelector('.sidebar-link[onclick="toggleSetting()"]');
    if (menuSetting && linkSetting) {
        if (!linkSetting.contains(event.target) && !menuSetting.contains(event.target)) {
            menuSetting.classList.remove('show');
        }
    }

    // Untuk Laporan
    var menuLaporan = document.getElementById('menuLaporan');
    var linkLaporan = document.querySelector('.sidebar-link[onclick="toggleLaporan()"]');
    if (menuLaporan && linkLaporan) {
        if (!linkLaporan.contains(event.target) && !menuLaporan.contains(event.target)) {
            menuLaporan.classList.remove('show');
        }
    }
});
</script>