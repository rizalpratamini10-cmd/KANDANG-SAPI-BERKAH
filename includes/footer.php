<!-- ================================================
     FOOTER - Kandang Berkah Jaya (Logo Sama Besar)
     ================================================ -->

<footer class="footer">
    <div class="footer-container">
        <div class="footer-grid">
            <div class="footer-about">
                <!-- ===== DUA LOGO SAMA BESAR ===== -->
                <div class="footer-logos">
                    <div class="footer-logo-item">
                        <img src="<?php echo BASE_URL; ?>assets/img/logo/logoberkah.jpeg" alt="Kandang Berkah Jaya">
                    </div>
                    <div class="footer-logo-item">
                        <img src="<?php echo BASE_URL; ?>assets/img/logo/logobalqis.jpeg" alt="Balqys Aqiqah">
                    </div>
                </div>
                <h3>KANDANG BERKAH JAYA</h3>
                <h4>BALQYS AQIQAH</h4>
                <p class="footer-tagline">@kambingsapibatam</p>
                <p>Peternakan Kambing dan Sapi, Penyediaan Layanan Qurban dan Aqiqah</p>
            </div>
            
            <div class="footer-links">
                <h4>Menu Cepat</h4>
                <ul>
                    <li><a href="<?php echo BASE_URL; ?>index.php">Beranda</a></li>
                    <li><a href="<?php echo BASE_URL; ?>customer/produk.php">Produk</a></li>
                    <li><a href="<?php echo BASE_URL; ?>customer/jasa.php">Jasa</a></li>
                    <li><a href="<?php echo BASE_URL; ?>customer/keranjang.php">Keranjang</a></li>
                    <li><a href="<?php echo BASE_URL; ?>customer/pesanan-saya.php">Pesanan Saya</a></li>
                </ul>
            </div>
            
            <div class="footer-contact">
                <h4>Kontak Kami</h4>
                <ul>
                    <li>📞 <a href="tel:+6281234567890">+62 812-3456-7890</a></li>
                    <li>📧 <a href="mailto:info@kambingsapibatam.com">info@kambingsapibatam.com</a></li>
                    <li>📍 Batam, Kepulauan Riau</li>
                </ul>
            </div>
            
            <div class="footer-social">
                <h4>Ikuti Kami</h4>
                <div class="social-icons">
                    <a href="https://instagram.com/kambingsapibatam" target="_blank" class="social-link">
                        📷 Instagram
                    </a>
                    <a href="https://wa.me/6281234567890" target="_blank" class="social-link">
                        💬 WhatsApp
                    </a>
                </div>
            </div>
        </div>
        
        <div class="footer-bottom">
            <p>&copy; <?php echo date('Y'); ?> Kandang Berkah Jaya | Balqys Aqiqah.</p>
            <p>dibuat dengan Kandang Berkah Jaya</p>
        </div>
    </div>
</footer>

<style>
.footer {
    background: #1a472a;
    color: white;
    padding: 50px 0 20px;
    margin-top: 50px;
}
.footer-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 20px;
}
.footer-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 30px;
    margin-bottom: 30px;
}

/* ===== LOGO SAMA BESAR ===== */
.footer-logos {
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 15px;
    margin-bottom: 15px;
    flex-wrap: wrap;
}

.footer-logo-item {
    background: white;
    padding: 8px 12px;
    border-radius: 10px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.2);
    display: flex;
    align-items: center;
    justify-content: center;
    width: 90px;
    height: 60px;
}

.footer-logo-item img {
    max-width: 100%;
    max-height: 100%;
    width: auto;
    height: auto;
    object-fit: contain;
    border-radius: 4px;
}

.footer-about h3 {
    font-size: 18px;
    margin-bottom: 5px;
    text-align: center;
}
.footer-about h4 {
    font-size: 14px;
    font-weight: normal;
    opacity: 0.9;
    margin-bottom: 10px;
    text-align: center;
}
.footer-tagline {
    color: #ffd700;
    margin-bottom: 10px;
    text-align: center;
}
.footer-about p {
    text-align: center;
}

.footer h4 {
    font-size: 16px;
    margin-bottom: 15px;
    border-left: 3px solid #ffd700;
    padding-left: 10px;
}
.footer ul {
    list-style: none;
    padding: 0;
}
.footer ul li {
    margin-bottom: 10px;
}
.footer ul li a {
    color: white;
    text-decoration: none;
    opacity: 0.8;
    transition: opacity 0.3s;
}
.footer ul li a:hover {
    opacity: 1;
}
.social-link {
    display: inline-block;
    margin-right: 15px;
    color: white;
    text-decoration: none;
    padding: 5px 10px;
    background: rgba(255,255,255,0.1);
    border-radius: 20px;
    font-size: 13px;
    transition: background 0.3s;
}
.social-link:hover {
    background: #ffd700;
    color: #1a472a;
}
.footer-bottom {
    text-align: center;
    padding-top: 20px;
    border-top: 1px solid rgba(255,255,255,0.2);
    font-size: 12px;
    opacity: 0.7;
}
.footer-bottom p {
    margin: 5px 0;
}

@media (max-width: 768px) {
    .footer-grid {
        grid-template-columns: 1fr;
        text-align: center;
    }
    .footer h4 {
        border-left: none;
        text-align: center;
    }
    .footer-logo-item {
        width: 75px;
        height: 50px;
        padding: 6px 10px;
    }
    .footer-logos {
        gap: 12px;
    }
}

@media (max-width: 480px) {
    .footer-logo-item {
        width: 65px;
        height: 45px;
        padding: 4px 8px;
    }
    .footer-logos {
        gap: 10px;
    }
}
</style>