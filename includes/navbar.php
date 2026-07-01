<!-- ================================================
     NAVBAR - Kandang Berkah Jaya (DENGAN MOBILE MENU)
     ================================================ -->

<nav class="navbar">
    <div class="nav-container">
        <div class="nav-logo">
            <a href="<?php echo BASE_URL; ?>customer/index.php">
                <img src="<?php echo BASE_URL; ?>assets/img/logo/logoberkah.jpeg" alt="Logo">
                <img src="<?php echo BASE_URL; ?>assets/img/logo/logobalqis.jpeg" alt="Logo">
                <div class="logo-text">
                    <span>KANDANG BERKAH JAYA</span>
                    <small>BALQYS AQIQAH</small>
                </div>
            </a>
        </div>
        
        <!-- ===== TOMBOL MENU MOBILE (HAMBURGER) ===== -->
        <div class="nav-toggle" id="nav-toggle">
            <span></span>
            <span></span>
            <span></span>
        </div>
        
        <div class="nav-menu" id="nav-menu">
            <ul>
                <!-- BERANDA -->
                <li><a href="<?php echo BASE_URL; ?>customer/index.php">🏠 Beranda</a></li>
                
                <!-- TENTANG KAMI -->
                <li><a href="<?php echo BASE_URL; ?>customer/tentangkami.php">📖 Tentang Kami</a></li>
                
                <!-- PRODUK (DROPDOWN) -->
                <li class="dropdown">
                    <a href="#">🛍️ Produk ▼</a>
                    <ul class="dropdown-menu">
                        <li><a href="<?php echo BASE_URL; ?>customer/produk.php?jenis=Kambing">🐐 Kambing</a></li>
                        <li><a href="<?php echo BASE_URL; ?>customer/produk.php?jenis=Sapi">🐄 Sapi</a></li>
                        <li><a href="<?php echo BASE_URL; ?>customer/produk.php?jenis=Kambing%20guling">🍖 Kambing Guling</a></li>
                        <li><a href="<?php echo BASE_URL; ?>customer/produk.php?jenis=Daging%20segar">🥩 Daging Segar</a></li>
                        <li><a href="<?php echo BASE_URL; ?>customer/produk.php?jenis=Kaki%20%26%20kepala%20kambing">🍗 Kaki & Kepala</a></li>
                    </ul>
                </li>
                
                <!-- JASA (DROPDOWN) -->
                <li class="dropdown">
                    <a href="#">🙏 Jasa ▼</a>
                    <ul class="dropdown-menu">
                        <li><a href="<?php echo BASE_URL; ?>customer/jasa.php?jenis=qurban">🕌 Qurban</a></li>
                        <li><a href="<?php echo BASE_URL; ?>customer/jasa.php?jenis=aqiqah">👶 Aqiqah</a></li>
                        <li><a href="<?php echo BASE_URL; ?>customer/jasa.php?jenis=catering">🍽️ Catering Service</a></li>
                    </ul>
                </li>
                
                <!-- GALERI -->
                <li><a href="<?php echo BASE_URL; ?>customer/galeri.php">📸 Galeri</a></li>
                
                <!-- KERANJANG -->
                <li><a href="<?php echo BASE_URL; ?>customer/keranjang.php">🛒 Keranjang</a></li>
                
                <!-- USER MENU -->
                <?php if(isset($_SESSION['customer_id'])): ?>
                    <li class="user-menu">
                        <a href="#">👤 <?php echo htmlspecialchars($_SESSION['customer_nama']); ?> ▼</a>
                        <ul class="dropdown-menu">
                            <li><a href="<?php echo BASE_URL; ?>customer/pesanan-saya.php">📋 Pesanan Saya</a></li>
                            <li><a href="<?php echo BASE_URL; ?>customer/profil.php">⚙️ Profil</a></li>
                            <li><a href="<?php echo BASE_URL; ?>logout.php">🚪 Logout</a></li>
                        </ul>
                    </li>
                <?php else: ?>
                    <li><a href="<?php echo BASE_URL; ?>login-customer.php" class="btn-login">🔑 Login</a></li>
                    <li><a href="<?php echo BASE_URL; ?>register.php" class="btn-register">📝 Daftar</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>

<style>
/* ===== NAVBAR STYLES ===== */
.navbar {
    background: #1a472a;
    color: white;
    padding: 12px 0;
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    z-index: 9999;
    box-shadow: 0 2px 15px rgba(0,0,0,0.15);
}

.nav-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 20px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
}

.nav-logo a {
    display: flex;
    align-items: center;
    gap: 12px;
    text-decoration: none;
    color: white;
}

.nav-logo img {
    width: 45px;
    height: 45px;
    border-radius: 50%;
    object-fit: cover;
}

.logo-text span {
    font-weight: bold;
    font-size: 16px;
    display: block;
}

.logo-text small {
    font-size: 10px;
    opacity: 0.8;
}

/* ===== MOBILE TOGGLE BUTTON ===== */
.nav-toggle {
    display: none;
    cursor: pointer;
    flex-direction: column;
    gap: 5px;
    padding: 5px 10px;
    border-radius: 8px;
    transition: background 0.3s;
    order: 2;
}

.nav-toggle:hover {
    background: rgba(255,255,255,0.1);
}

.nav-toggle span {
    display: block;
    width: 28px;
    height: 3px;
    background: white;
    border-radius: 3px;
    transition: 0.3s;
}

/* ===== NAV MENU ===== */
.nav-menu {
    order: 3;
    flex: 1 1 100%;
}

.nav-menu ul {
    display: flex;
    list-style: none;
    gap: 5px;
    margin: 0;
    padding: 0;
}

.nav-menu ul li {
    position: relative;
}

.nav-menu ul li a {
    color: white;
    text-decoration: none;
    padding: 10px 16px;
    border-radius: 25px;
    transition: all 0.3s;
    font-size: 14px;
    display: block;
    white-space: nowrap;
}

.nav-menu ul li a:hover {
    background: rgba(255,255,255,0.15);
}

/* ===== DROPDOWN ===== */
.dropdown,
.user-menu {
    position: relative;
}

ul.dropdown-menu {
    position: absolute;
    top: 100%;
    left: 0;
    min-width: 220px;
    background: #fff;
    border-radius: 12px;
    box-shadow: 0 10px 30px rgba(0,0,0,.15);
    padding: 8px 0;
    list-style: none;
    margin-top: 5px;
    z-index: 99999;
    display: none !important;
}

.dropdown:hover > ul.dropdown-menu,
.user-menu:hover > ul.dropdown-menu {
    display: block !important;
}

.dropdown-menu li a {
    color: #333 !important;
    padding: 10px 20px;
    border-radius: 0;
    font-size: 14px;
    white-space: nowrap;
    background: transparent !important;
    text-decoration: none;
}

.dropdown-menu li a:hover {
    background: #f0f7f0 !important;
    color: #2c5f2d !important;
}

.user-menu .dropdown-menu {
    right: 0;
    left: auto;
}

/* ===== BUTTON LOGIN/REGISTER ===== */
.btn-login {
    background: transparent;
    border: 1px solid rgba(255,255,255,0.3);
}

.btn-register {
    background: #ffd700;
    color: #333 !important;
}

.btn-register:hover {
    background: #e6c200;
}

/* ================================================
   RESPONSIVE - MOBILE MENU
   ================================================ */
@media (max-width: 992px) {
    .nav-toggle {
        display: flex;
    }
    
    .nav-menu {
        display: none;
        width: 100%;
        background: #1a472a;
        padding: 15px 0;
        border-top: 1px solid rgba(255,255,255,0.1);
        margin-top: 15px;
        position: absolute;
        top: 100%;
        left: 0;
        right: 0;
        z-index: 1000;
        box-shadow: 0 10px 30px rgba(0,0,0,0.2);
    }
    
    .nav-menu.active {
        display: block;
    }
    
    .nav-menu ul {
        flex-direction: column;
        padding: 0 20px;
        gap: 5px;
    }
    
    .nav-menu ul li a {
        padding: 12px 16px;
        border-radius: 10px;
        white-space: normal;
    }
    
    /* Dropdown di mobile - tampil semua */
    ul.dropdown-menu {
        position: static;
        background: rgba(255,255,255,0.08);
        box-shadow: none;
        border-radius: 10px;
        margin: 5px 0 5px 20px;
        padding: 5px 0;
        display: block !important;
    }
    
    .dropdown-menu li a {
        color: white !important;
        padding: 10px 16px;
    }
    
    .dropdown-menu li a:hover {
        background: rgba(255,255,255,0.1) !important;
        color: white !important;
    }
    
    .user-menu .dropdown-menu {
        right: auto;
        left: 0;
    }
}

@media (max-width: 480px) {
    .nav-logo img {
        width: 35px;
        height: 35px;
    }
    
    .logo-text span {
        font-size: 13px;
    }
    
    .logo-text small {
        font-size: 9px;
    }
    
    .nav-toggle span {
        width: 24px;
        height: 2px;
    }
    
    .nav-menu ul li a {
        font-size: 13px;
        padding: 10px 14px;
    }
}
</style>

<script>
// ================================================
// TOGGLE MOBILE MENU
// ================================================
document.addEventListener('DOMContentLoaded', function() {
    const navToggle = document.getElementById('nav-toggle');
    const navMenu = document.getElementById('nav-menu');
    
    if(navToggle) {
        navToggle.addEventListener('click', function() {
            navMenu.classList.toggle('active');
        });
    }
    
    // Tutup menu saat klik di luar
    document.addEventListener('click', function(event) {
        const isClickInside = navToggle.contains(event.target) || navMenu.contains(event.target);
        if (!isClickInside && navMenu.classList.contains('active')) {
            navMenu.classList.remove('active');
        }
    });
});
</script>