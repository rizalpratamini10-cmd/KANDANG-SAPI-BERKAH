-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Waktu pembuatan: 23 Jun 2026 pada 10.35
-- Versi server: 10.4.32-MariaDB
-- Versi PHP: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `db_qurban2026`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `admin`
--

CREATE TABLE `admin` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `nama_lengkap` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `foto` varchar(255) DEFAULT NULL,
  `last_login` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `level` enum('super_admin','admin') DEFAULT 'admin',
  `role` enum('super_admin','admin') DEFAULT 'admin',
  `is_active` tinyint(1) DEFAULT 1,
  `created_by` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `admin`
--

INSERT INTO `admin` (`id`, `username`, `password`, `nama_lengkap`, `email`, `foto`, `last_login`, `created_at`, `level`, `role`, `is_active`, `created_by`) VALUES
(1, 'admin', '$2y$10$vKQZkyKCS1yE30akRDbLwuP9QK6OYCkErz.LGv0ShOxyaiY5EgTKq', 'Administrator', 'admin@kandangberkahjaya.com', NULL, '2026-06-23 15:24:35', '2026-06-14 11:38:55', 'super_admin', 'super_admin', 1, NULL),
(2, 'MuhammadRizal', '$2y$10$d.54P.SvEs8ayi5DMzS3ye1uTXypKP9v/44dYNkuGqM5bm9QOskOq', 'Muhammad Rizal', 'MuhammadRizal@kandangberkah.com', NULL, '2026-06-23 15:29:44', '2026-06-23 08:27:01', 'admin', '', 1, NULL);

-- --------------------------------------------------------

--
-- Struktur dari tabel `cart`
--

CREATE TABLE `cart` (
  `id` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `customers`
--

CREATE TABLE `customers` (
  `id` int(11) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `no_hp` varchar(20) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `alamat` text DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `customers`
--

INSERT INTO `customers` (`id`, `nama`, `no_hp`, `email`, `alamat`, `password`, `created_at`) VALUES
(1, 'Rizal', '085669049294', NULL, NULL, '$2y$10$.q52lOqWpx9ej1fF3cvMHeo3/yZKUR6RD4S8wbfxq3pkKvfSpFJy2', '2026-06-14 13:30:59');

-- --------------------------------------------------------

--
-- Struktur dari tabel `customer_addresses`
--

CREATE TABLE `customer_addresses` (
  `id` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `label` varchar(50) NOT NULL,
  `alamat` text NOT NULL,
  `is_primary` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `follow_up`
--

CREATE TABLE `follow_up` (
  `id` int(11) NOT NULL,
  `tipe_followup` enum('pesanan','customer') NOT NULL,
  `id_target` int(11) NOT NULL,
  `catatan` text NOT NULL,
  `followup_date` date DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `follow_up`
--

INSERT INTO `follow_up` (`id`, `tipe_followup`, `id_target`, `catatan`, `followup_date`, `created_by`, `created_at`) VALUES
(1, 'pesanan', 3, 'catat lagi', '2026-06-17', 1, '2026-06-17 13:56:23'),
(2, 'pesanan', 3, '085669049294', '2026-06-17', 1, '2026-06-17 14:05:45'),
(3, 'pesanan', 3, 'giamana', '2026-06-17', 1, '2026-06-17 14:08:35'),
(4, 'pesanan', 3, 'hallo', '2026-06-17', 1, '2026-06-17 14:13:26'),
(5, 'pesanan', 3, 'masih dp ', '2026-06-17', 1, '2026-06-17 14:17:15'),
(6, 'pesanan', 4, 'di wa', '2026-06-22', 1, '2026-06-22 15:24:41');

-- --------------------------------------------------------

--
-- Struktur dari tabel `galeri`
--

CREATE TABLE `galeri` (
  `id` int(11) NOT NULL,
  `judul` varchar(100) NOT NULL,
  `deskripsi` text DEFAULT NULL,
  `gambar` varchar(255) NOT NULL,
  `kategori` enum('kandang','kegiatan','produk','lainnya') DEFAULT 'lainnya',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `galeri`
--

INSERT INTO `galeri` (`id`, `judul`, `deskripsi`, `gambar`, `kategori`, `created_at`) VALUES
(1, 'kandang balqis aqiqah', '', '1782189164_kandang1.jpeg', 'kandang', '2026-06-23 04:32:44'),
(2, 'kandang berkah jaya ', '', '1782189648_Screenshot 2026-06-22 185859.png', 'kandang', '2026-06-23 04:40:48');

-- --------------------------------------------------------

--
-- Struktur dari tabel `gallery`
--

CREATE TABLE `gallery` (
  `id` int(11) NOT NULL,
  `judul` varchar(100) NOT NULL,
  `gambar` varchar(255) NOT NULL,
  `deskripsi` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `kategori`
--

CREATE TABLE `kategori` (
  `id` int(11) NOT NULL,
  `nama_kategori` varchar(50) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `kategori`
--

INSERT INTO `kategori` (`id`, `nama_kategori`, `created_at`) VALUES
(1, 'Jasa', '2026-06-14 11:38:55'),
(2, 'Produk', '2026-06-14 11:38:55');

-- --------------------------------------------------------

--
-- Struktur dari tabel `metode_pembayaran`
--

CREATE TABLE `metode_pembayaran` (
  `id` int(11) NOT NULL,
  `bank_name` varchar(50) NOT NULL,
  `account_number` varchar(50) NOT NULL,
  `account_holder` varchar(100) NOT NULL,
  `logo` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `metode_pembayaran`
--

INSERT INTO `metode_pembayaran` (`id`, `bank_name`, `account_number`, `account_holder`, `logo`, `is_active`, `created_at`) VALUES
(1, 'Bank BCA', '1234567890', 'Kandang Berkah Jaya', NULL, 1, '2026-06-14 11:38:55'),
(2, 'Bank Mandiri', '9876543210', 'Kandang Berkah Jaya', NULL, 1, '2026-06-14 11:38:55'),
(3, 'Bank BRI', '5551234567', 'Kandang Berkah Jaya', NULL, 1, '2026-06-14 11:38:55');

-- --------------------------------------------------------

--
-- Struktur dari tabel `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `title` varchar(100) NOT NULL,
  `message` text NOT NULL,
  `type` enum('order','payment','delivery','system') DEFAULT 'system',
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `order_number` varchar(20) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `kurir_id` int(11) DEFAULT NULL,
  `total_harga` decimal(12,0) NOT NULL,
  `biaya_pengiriman` decimal(12,0) DEFAULT 0,
  `status` enum('pending','diproses','dikirim','selesai','dibatalkan') DEFAULT 'pending',
  `payment_method` enum('cash','transfer','qris') DEFAULT 'cash',
  `payment_status` enum('belum_bayar','sudah_bayar') DEFAULT 'belum_bayar',
  `catatan` text DEFAULT NULL,
  `alamat_pengiriman` text NOT NULL,
  `latitude` decimal(10,8) DEFAULT NULL,
  `longitude` decimal(11,8) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `order_details`
--

CREATE TABLE `order_details` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `harga_per_item` decimal(12,0) NOT NULL,
  `subtotal` decimal(12,0) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `password_reset`
--

CREATE TABLE `password_reset` (
  `id` int(11) NOT NULL,
  `no_hp` varchar(20) NOT NULL,
  `token` varchar(255) NOT NULL,
  `expired_at` datetime NOT NULL,
  `is_used` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `password_reset`
--

INSERT INTO `password_reset` (`id`, `no_hp`, `token`, `expired_at`, `is_used`, `created_at`) VALUES
(1, '085669049294', '2d4bf93e68fc13370a392773e5c0be74', '2026-06-14 21:33:30', 0, '2026-06-14 13:33:30');

-- --------------------------------------------------------

--
-- Struktur dari tabel `pesanan`
--

CREATE TABLE `pesanan` (
  `id` int(11) NOT NULL,
  `id_customer` int(11) NOT NULL,
  `id_produk` int(11) NOT NULL,
  `invoice` varchar(50) DEFAULT NULL,
  `tipe_pembayaran` enum('dp','lunas') NOT NULL,
  `total_harga` decimal(15,2) NOT NULL,
  `dp_amount` decimal(15,2) DEFAULT 0.00,
  `sisa_pembayaran` decimal(15,2) DEFAULT 0.00,
  `status` enum('waiting_dp','process','paid','cancelled') DEFAULT 'waiting_dp',
  `bukti_transfer` varchar(255) DEFAULT NULL,
  `catatan` text DEFAULT NULL,
  `metode_bayar_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `pesanan`
--

INSERT INTO `pesanan` (`id`, `id_customer`, `id_produk`, `invoice`, `tipe_pembayaran`, `total_harga`, `dp_amount`, `sisa_pembayaran`, `status`, `bukti_transfer`, `catatan`, `metode_bayar_id`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 'INV/20260614/46C58B', 'lunas', 2500000.00, 0.00, 0.00, 'paid', '1781445460_logo_warungrizal.png', 'Menunggu konfirmasi admin', NULL, '2026-06-14 13:57:40', '2026-06-14 20:59:07'),
(2, 1, 4, 'INV/20260615/FA4FE1', 'lunas', 1500000.00, 0.00, 0.00, 'paid', '1781528479_logo_warungrizal.png', 'Menunggu konfirmasi admin', NULL, '2026-06-15 13:01:19', '2026-06-15 20:06:10'),
(3, 1, 5, 'INV/20260617/C291A0', 'dp', 4500000.00, 2250000.00, 2250000.00, 'paid', 'bukti_1781669916_1.png', 'Nama: yanto, HP: 08564234232323, Alamat: masjid, Catatan: malam', NULL, '2026-06-17 04:18:36', '2026-06-22 22:23:49'),
(4, 1, 3, 'INV/20260622/C4A31D', 'dp', 18000000.00, 9000000.00, 9000000.00, 'process', 'bukti_1782141788_1.png', 'Nama: ridwan, HP: 085667899064, Alamat: masjid, Catatan: ', NULL, '2026-06-22 15:23:08', '2026-06-22 22:24:24');

-- --------------------------------------------------------

--
-- Struktur dari tabel `produk`
--

CREATE TABLE `produk` (
  `id` int(11) NOT NULL,
  `id_sub_kategori` int(11) NOT NULL,
  `nama_produk` varchar(100) NOT NULL,
  `kode_produk` varchar(50) NOT NULL,
  `deskripsi` text DEFAULT NULL,
  `harga` decimal(15,2) NOT NULL,
  `stok` int(11) DEFAULT 1,
  `gambar` varchar(255) DEFAULT NULL,
  `status` enum('tersedia','habis') DEFAULT 'tersedia',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `produk`
--

INSERT INTO `produk` (`id`, `id_sub_kategori`, `nama_produk`, `kode_produk`, `deskripsi`, `harga`, `stok`, `gambar`, `status`, `created_at`, `updated_at`) VALUES
(1, 4, 'Kambing', 'KMB-3212', 'Kambing sehat berat 30kg, siap qurban', 2500000.00, 0, NULL, 'habis', '2026-06-14 11:38:55', '2026-06-14 20:59:07'),
(2, 4, 'Kambing', 'KMB-4523', 'Kambing sehat berat 35kg', 2700000.00, 1, NULL, 'tersedia', '2026-06-14 11:38:55', NULL),
(3, 5, 'Sapi', 'SP-7890', 'Sapi limosin berat 200kg', 18000000.00, 0, NULL, 'habis', '2026-06-14 11:38:55', '2026-06-22 22:24:24'),
(4, 6, 'Kambing guling', 'KMG-001', 'Kambing guling siap saji untuk 10 orang', 1500000.00, 0, NULL, 'habis', '2026-06-14 11:38:55', '2026-06-15 20:06:01'),
(5, 4, 'kambing jantan', 'KMB121', 'sehat kuat gemuk', 4500000.00, 0, '1781446335_ChatGPTImage10Jun202615.02.45.png', 'habis', '2026-06-14 14:12:15', '2026-06-17 11:19:00');

-- --------------------------------------------------------

--
-- Struktur dari tabel `setting_website`
--

CREATE TABLE `setting_website` (
  `id` int(11) NOT NULL,
  `setting_key` varchar(50) NOT NULL,
  `setting_value` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `setting_website`
--

INSERT INTO `setting_website` (`id`, `setting_key`, `setting_value`, `created_at`) VALUES
(1, 'nama_perusahaan', 'Kandang Berkah Jaya | Balqys Aqiqah', '2026-06-14 11:38:55'),
(2, 'tagline', '@kambingsapibatam', '2026-06-14 11:38:55'),
(3, 'deskripsi', 'Peternakan Kambing dan Sapi, Penyediaan Layanan Qurban dan Aqiqah', '2026-06-14 11:38:55'),
(4, 'no_telp', '081234567890', '2026-06-14 11:38:55'),
(5, 'email', 'info@kandangberkahjaya.com', '2026-06-14 11:38:55'),
(6, 'alamat', 'Batam, Kepulauan Riau', '2026-06-14 11:38:55'),
(7, 'instagram', 'https://instagram.com/kambingsapibatam', '2026-06-14 11:38:55');

-- --------------------------------------------------------

--
-- Struktur dari tabel `sub_kategori`
--

CREATE TABLE `sub_kategori` (
  `id` int(11) NOT NULL,
  `id_kategori` int(11) NOT NULL,
  `nama_sub` varchar(50) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `sub_kategori`
--

INSERT INTO `sub_kategori` (`id`, `id_kategori`, `nama_sub`, `created_at`) VALUES
(1, 1, 'Qurban', '2026-06-14 11:38:55'),
(2, 1, 'Aqiqah', '2026-06-14 11:38:55'),
(3, 1, 'Catering service', '2026-06-14 11:38:55'),
(4, 2, 'Kambing', '2026-06-14 11:38:55'),
(5, 2, 'Sapi', '2026-06-14 11:38:55'),
(6, 2, 'Kambing guling', '2026-06-14 11:38:55'),
(7, 2, 'Daging segar', '2026-06-14 11:38:55'),
(8, 2, 'Kaki & kepala kambing', '2026-06-14 11:38:55');

-- --------------------------------------------------------

--
-- Struktur dari tabel `testimonials`
--

CREATE TABLE `testimonials` (
  `id` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `rating` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `nama_lengkap` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `no_hp` varchar(15) NOT NULL,
  `alamat` text DEFAULT NULL,
  `role` enum('super_admin','admin','customer','kurir') NOT NULL DEFAULT 'customer',
  `foto` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `latitude` decimal(10,8) DEFAULT NULL,
  `longitude` decimal(11,8) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `nama_lengkap`, `email`, `no_hp`, `alamat`, `role`, `foto`, `is_active`, `created_at`, `updated_at`, `latitude`, `longitude`) VALUES
(30, 'muhammadrizal', '$2y$10$vN3dCnKKnizFl.TQ.ZA8Ueq3c/Ii9tUKRE2rBKW9ZRDJllCV6Ft9i', 'Muhammad Rizal', 'MuhammadRizal@kandangberkah.com', '082172346980', NULL, '', NULL, 1, '2026-06-23 08:00:45', '2026-06-23 08:00:45', NULL, NULL),
(31, 'Rizal1922', '$2y$10$PTsn/acwfwJn31wf9htBO.O3.cOxGS2LGLfET5pOgMNxeqUyMD.xm', 'Rizal', 'rizalpratamini10@gmail.com', '085669049294', NULL, '', NULL, 1, '2026-06-23 08:07:44', '2026-06-23 08:07:44', NULL, NULL);

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indeks untuk tabel `cart`
--
ALTER TABLE `cart`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_cart` (`customer_id`,`product_id`),
  ADD KEY `product_id` (`product_id`),
  ADD KEY `idx_customer` (`customer_id`);

--
-- Indeks untuk tabel `customers`
--
ALTER TABLE `customers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `no_hp` (`no_hp`);

--
-- Indeks untuk tabel `customer_addresses`
--
ALTER TABLE `customer_addresses`
  ADD PRIMARY KEY (`id`),
  ADD KEY `customer_id` (`customer_id`);

--
-- Indeks untuk tabel `follow_up`
--
ALTER TABLE `follow_up`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `idx_tipe_target` (`tipe_followup`,`id_target`);

--
-- Indeks untuk tabel `galeri`
--
ALTER TABLE `galeri`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `gallery`
--
ALTER TABLE `gallery`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `kategori`
--
ALTER TABLE `kategori`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `metode_pembayaran`
--
ALTER TABLE `metode_pembayaran`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user` (`user_id`),
  ADD KEY `idx_unread` (`user_id`,`is_read`),
  ADD KEY `idx_created` (`created_at`);

--
-- Indeks untuk tabel `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `order_number` (`order_number`),
  ADD KEY `idx_customer` (`customer_id`),
  ADD KEY `idx_kurir` (`kurir_id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_created` (`created_at`),
  ADD KEY `idx_order_number` (`order_number`);

--
-- Indeks untuk tabel `order_details`
--
ALTER TABLE `order_details`
  ADD PRIMARY KEY (`id`),
  ADD KEY `product_id` (`product_id`),
  ADD KEY `idx_order` (`order_id`);

--
-- Indeks untuk tabel `password_reset`
--
ALTER TABLE `password_reset`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_token` (`token`),
  ADD KEY `idx_no_hp` (`no_hp`);

--
-- Indeks untuk tabel `pesanan`
--
ALTER TABLE `pesanan`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `invoice` (`invoice`),
  ADD KEY `id_customer` (`id_customer`),
  ADD KEY `id_produk` (`id_produk`);

--
-- Indeks untuk tabel `produk`
--
ALTER TABLE `produk`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `kode_produk` (`kode_produk`),
  ADD KEY `id_sub_kategori` (`id_sub_kategori`),
  ADD KEY `idx_kode_produk` (`kode_produk`),
  ADD KEY `idx_nama_produk` (`nama_produk`),
  ADD KEY `idx_stok` (`stok`);

--
-- Indeks untuk tabel `setting_website`
--
ALTER TABLE `setting_website`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `setting_key` (`setting_key`);

--
-- Indeks untuk tabel `sub_kategori`
--
ALTER TABLE `sub_kategori`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_kategori` (`id_kategori`);

--
-- Indeks untuk tabel `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_role` (`role`),
  ADD KEY `idx_email` (`email`),
  ADD KEY `idx_is_active` (`is_active`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `admin`
--
ALTER TABLE `admin`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT untuk tabel `cart`
--
ALTER TABLE `cart`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT untuk tabel `customers`
--
ALTER TABLE `customers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT untuk tabel `customer_addresses`
--
ALTER TABLE `customer_addresses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `follow_up`
--
ALTER TABLE `follow_up`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT untuk tabel `galeri`
--
ALTER TABLE `galeri`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT untuk tabel `gallery`
--
ALTER TABLE `gallery`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `kategori`
--
ALTER TABLE `kategori`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT untuk tabel `metode_pembayaran`
--
ALTER TABLE `metode_pembayaran`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT untuk tabel `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT untuk tabel `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT untuk tabel `order_details`
--
ALTER TABLE `order_details`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT untuk tabel `password_reset`
--
ALTER TABLE `password_reset`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT untuk tabel `pesanan`
--
ALTER TABLE `pesanan`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT untuk tabel `produk`
--
ALTER TABLE `produk`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT untuk tabel `setting_website`
--
ALTER TABLE `setting_website`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT untuk tabel `sub_kategori`
--
ALTER TABLE `sub_kategori`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT untuk tabel `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `follow_up`
--
ALTER TABLE `follow_up`
  ADD CONSTRAINT `follow_up_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `admin` (`id`) ON DELETE SET NULL;

--
-- Ketidakleluasaan untuk tabel `password_reset`
--
ALTER TABLE `password_reset`
  ADD CONSTRAINT `password_reset_ibfk_1` FOREIGN KEY (`no_hp`) REFERENCES `customers` (`no_hp`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `pesanan`
--
ALTER TABLE `pesanan`
  ADD CONSTRAINT `pesanan_ibfk_1` FOREIGN KEY (`id_customer`) REFERENCES `customers` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `pesanan_ibfk_2` FOREIGN KEY (`id_produk`) REFERENCES `produk` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `produk`
--
ALTER TABLE `produk`
  ADD CONSTRAINT `produk_ibfk_1` FOREIGN KEY (`id_sub_kategori`) REFERENCES `sub_kategori` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `sub_kategori`
--
ALTER TABLE `sub_kategori`
  ADD CONSTRAINT `sub_kategori_ibfk_1` FOREIGN KEY (`id_kategori`) REFERENCES `kategori` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
