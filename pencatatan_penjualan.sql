-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Waktu pembuatan: 23 Des 2025 pada 15.45
-- Versi server: 10.4.32-MariaDB
-- Versi PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `pencatatan_penjualan`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `customer`
--

CREATE TABLE `customer` (
  `customer_id` int(11) NOT NULL,
  `nama_toko` varchar(100) NOT NULL,
  `alamat` text DEFAULT NULL,
  `telepon` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `customer`
--

INSERT INTO `customer` (`customer_id`, `nama_toko`, `alamat`, `telepon`) VALUES
(1, 'Pelita', 'Jl. Mawar', '000000'),
(3, 'Tjandra', 'Jl. Melati', '12345'),
(4, 'Bule', 'jembatan Lima', '2222222');

-- --------------------------------------------------------

--
-- Struktur dari tabel `detail_penjualan`
--

CREATE TABLE `detail_penjualan` (
  `detail_id` int(11) NOT NULL,
  `penjualan_id` int(11) DEFAULT NULL,
  `produk_id` int(11) DEFAULT NULL,
  `jumlah` int(11) NOT NULL,
  `harga_satuan` decimal(12,2) NOT NULL,
  `subtotal` decimal(12,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `detail_penjualan`
--

INSERT INTO `detail_penjualan` (`detail_id`, `penjualan_id`, `produk_id`, `jumlah`, `harga_satuan`, `subtotal`) VALUES
(1, 1, 3, 70, 5000.00, 350000.00),
(2, 2, 3, 1, 5000.00, 5000.00),
(3, 3, 3, 9, 5000.00, 45000.00),
(4, 4, 1, 50, 15000.00, 750000.00),
(5, 5, 3, 50, 5000.00, 250000.00),
(6, 6, 5, 200, 28000.00, 5600000.00),
(7, 6, 4, 200, 820000.00, 164000000.00),
(8, 7, 1, 30, 15000.00, 450000.00),
(9, 8, 1, 30, 15000.00, 450000.00),
(10, 9, 2, 50, 32000.00, 1600000.00),
(11, 10, 4, 150, 820000.00, 123000000.00),
(12, 11, 1, 1000, 15000.00, 15000000.00),
(13, 11, 4, 50, 820000.00, 41000000.00),
(14, 12, 4, 70, 820000.00, 57400000.00),
(15, 13, 1, 50, 15000.00, 750000.00);

-- --------------------------------------------------------

--
-- Struktur dari tabel `kategori_produk`
--

CREATE TABLE `kategori_produk` (
  `kategori_id` int(11) NOT NULL,
  `nama_kategori` varchar(100) NOT NULL,
  `deskripsi` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `kategori_produk`
--

INSERT INTO `kategori_produk` (`kategori_id`, `nama_kategori`, `deskripsi`) VALUES
(1, 'Gula', 'Produk gula berbagai merek seperti GMP, Rose, dsb.'),
(2, 'Kacang', 'Kacang tanah berbagai ukuran 50/60, 80/90, dsb.'),
(3, 'Garam', 'Aneka garam kemasan.'),
(5, 'Bawang', 'Bawang Putih Kating Atau utuh'),
(6, 'Kemiri', 'Berbagai jenis kemiri bulat dan pecah');

-- --------------------------------------------------------

--
-- Struktur dari tabel `log_aktivitas`
--

CREATE TABLE `log_aktivitas` (
  `log_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `waktu` datetime DEFAULT current_timestamp(),
  `aktivitas` varchar(100) DEFAULT NULL,
  `keterangan` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `log_aktivitas`
--

INSERT INTO `log_aktivitas` (`log_id`, `user_id`, `waktu`, `aktivitas`, `keterangan`) VALUES
(1, 1, '2025-11-14 15:18:20', 'Login', 'User login'),
(2, 1, '2025-11-14 15:19:27', 'Tambah Pelanggan', 'Pelita'),
(3, 1, '2025-11-15 16:45:34', 'Login', 'User login'),
(4, 1, '2025-11-15 16:59:37', 'Transaksi Penjualan', 'Nota #1 (Belum Lunas)'),
(5, 1, '2025-11-15 17:00:10', 'Transaksi Penjualan', 'Nota #2 (Belum Lunas)'),
(6, 1, '2025-11-16 17:29:44', 'Login', 'User login'),
(7, 1, '2025-11-16 19:16:50', 'Transaksi Penjualan', 'Nota #3 (Belum Lunas)'),
(8, 1, '2025-11-16 19:18:58', 'Tambah Pelanggan', 'Tjandra'),
(9, 1, '2025-11-16 19:19:06', 'Tambah Pelanggan', 'Tjandra'),
(10, 1, '2025-11-16 19:19:10', 'Hapus Pelanggan', 'ID 2'),
(11, 1, '2025-11-16 19:20:35', 'Tambah Kategori', 'Bawang'),
(12, 1, '2025-11-16 19:20:42', 'Tambah Kategori', 'Bawang'),
(13, 1, '2025-11-16 19:20:47', 'Hapus Kategori', 'ID 4'),
(14, 1, '2025-11-16 19:21:20', 'Retur Penjualan', 'Nota #3 dengan nilai retur Rp 20.000'),
(15, 1, '2025-11-16 19:22:25', 'Tambah Produk', 'Gula Rose'),
(16, 1, '2025-11-16 19:24:59', 'Transaksi Penjualan', 'Nota #4 (Belum Lunas)'),
(17, 1, '2025-11-16 19:25:21', 'Pembayaran', 'Bayar nota ID 4 sebesar 750000 (Giro)'),
(18, 1, '2025-11-16 19:25:59', 'Retur Penjualan', 'Nota #4 dengan nilai retur Rp 150.000'),
(19, 1, '2025-11-16 19:26:49', 'Stok Masuk', 'Tambah stok 56 untuk produk ID 3'),
(20, 1, '2025-11-16 19:27:08', 'Edit Produk', 'Ubah: Gula GMP 1kg → Gula GMP 1kgg'),
(21, 1, '2025-11-16 19:28:54', 'Tambah Kategori', 'Kemiri'),
(22, 1, '2025-11-16 19:29:19', 'Tambah Produk', 'Kemiri Bulat'),
(23, 1, '2025-11-16 19:29:43', 'Edit Pelanggan', 'Ubah: Pelita → Pelita 1'),
(24, 1, '2025-11-16 19:30:53', 'Tambah Pelanggan', 'Bule'),
(25, 1, '2025-11-16 19:31:06', 'Edit Pelanggan', 'Ubah: Pelita 1 → Pelita'),
(26, 1, '2025-11-16 19:34:14', 'Transaksi Penjualan', 'Nota #5 (Belum Lunas)'),
(27, 1, '2025-11-16 19:35:57', 'Retur Penjualan', 'Nota #5 dengan nilai retur Rp 50.000'),
(28, 1, '2025-11-16 21:54:01', 'Transaksi Penjualan', 'Nota #6 (Belum Lunas)'),
(29, 1, '2025-11-16 21:55:55', 'Retur Penjualan', 'Nota #6 dengan nilai retur Rp 82.000.000'),
(30, 1, '2025-11-16 22:21:04', 'Pembayaran', 'Bayar nota ID 1 sebesar 350000 (Cash)'),
(31, 1, '2025-11-16 22:21:10', 'Pembayaran', 'Bayar nota ID 1 sebesar 350000 (Cash)'),
(32, 1, '2025-11-16 22:23:35', 'Pembayaran', 'Bayar nota ID 2 sebesar 5000 (Cash)'),
(33, 1, '2025-11-16 22:23:53', 'Pembayaran', 'Bayar nota ID 3 sebesar 25000 (Transfer)'),
(34, 1, '2025-11-16 22:24:15', 'Pembayaran', 'Bayar nota ID 5 sebesar 200000 (Transfer)'),
(35, 1, '2025-11-17 00:13:15', 'Logout', 'Keluar sistem'),
(36, 3, '2025-11-17 00:13:25', 'Login', 'User login'),
(37, 3, '2025-11-17 00:16:47', 'Logout', 'Keluar sistem'),
(38, 2, '2025-11-17 00:18:14', 'Login', 'User login'),
(39, 2, '2025-11-17 00:19:57', 'Logout', 'Keluar sistem'),
(40, 3, '2025-11-17 00:20:03', 'Login', 'User login'),
(41, 3, '2025-11-17 01:00:31', 'Logout', 'Keluar sistem'),
(42, 2, '2025-11-17 01:00:40', 'Login', 'User login'),
(43, 2, '2025-11-17 01:00:55', 'Logout', 'Keluar sistem'),
(44, 1, '2025-11-17 11:33:34', 'Login', 'User login'),
(45, 1, '2025-11-17 20:51:11', 'Login', 'User login'),
(46, 3, '2025-11-18 07:46:02', 'Login', 'User login'),
(47, 1, '2025-11-18 23:12:37', 'Login', 'User login'),
(48, 1, '2025-11-20 10:35:05', 'Login', 'User login'),
(49, 1, '2025-11-20 10:42:20', 'Transaksi Penjualan', 'Nota #7 (Belum Lunas)'),
(50, 1, '2025-11-20 11:26:33', 'Pembayaran', 'Bayar nota ID 7 sebesar 450000 (Transfer)'),
(51, 1, '2025-11-20 11:26:46', 'Pembayaran', 'Bayar nota ID 7 sebesar 450000 (Transfer)'),
(52, 1, '2025-11-20 11:27:17', 'Pembayaran', 'Bayar nota ID 6 sebesar 8700000 (Transfer)'),
(53, 1, '2025-11-20 11:27:21', 'Pembayaran', 'Bayar nota ID 6 sebesar 8700000 (Transfer)'),
(54, 1, '2025-11-20 11:28:06', 'Pembayaran', 'Bayar nota ID 6 sebesar 900000000 (Transfer)'),
(55, 1, '2025-11-20 11:28:18', 'Pembayaran', 'Bayar nota ID 6 sebesar 90000000 (Cash)'),
(56, 1, '2025-11-20 11:30:56', 'Transaksi Penjualan', 'Nota #8 (Belum Lunas)'),
(57, 1, '2025-11-20 11:34:21', 'Retur Penjualan', 'Nota #8 dengan nilai retur Rp 150.000'),
(58, 1, '2025-11-20 14:18:49', 'Login', 'User login'),
(59, 1, '2025-11-20 14:39:45', 'Logout', 'Keluar sistem'),
(60, 2, '2025-11-20 14:39:54', 'Login', 'User login'),
(61, 2, '2025-11-20 14:59:18', 'Logout', 'Keluar sistem'),
(62, 1, '2025-11-20 15:16:14', 'Login', 'User login'),
(63, 1, '2025-11-20 15:17:43', 'Transaksi Penjualan', 'Nota #9 (Belum Lunas)'),
(64, 1, '2025-11-20 15:18:33', 'Pembayaran', 'Bayar nota ID 9 sebesar 16000000 (Transfer)'),
(65, 1, '2025-11-20 15:18:39', 'Pembayaran', 'Bayar nota ID 9 sebesar 16000000 (Transfer)'),
(66, 1, '2025-11-20 15:19:02', 'Logout', 'Keluar sistem'),
(67, 1, '2025-11-20 15:20:00', 'Login', 'User login'),
(68, 1, '2025-11-20 15:21:21', 'Transaksi Penjualan', 'Nota #10 (Belum Lunas)'),
(69, 1, '2025-11-20 15:22:03', 'Pembayaran', 'Bayar nota ID 8 sebesar 300000 (Cash)'),
(70, 1, '2025-11-20 15:22:08', 'Pembayaran', 'Bayar nota ID 8 sebesar 300000 (Cash)'),
(71, 1, '2025-11-20 15:23:35', 'Stok Masuk', 'Tambah stok 1000 untuk produk ID 1'),
(72, 1, '2025-11-20 15:24:46', 'Edit Produk', 'Ubah: Gula GMP 1kgg → Gula GMP 1kg'),
(73, 1, '2025-11-20 15:27:05', 'Logout', 'Keluar sistem'),
(74, 2, '2025-11-20 15:27:13', 'Login', 'User login'),
(75, 2, '2025-11-20 15:27:52', 'Logout', 'Keluar sistem'),
(76, 3, '2025-11-20 15:28:05', 'Login', 'User login'),
(77, 3, '2025-11-20 19:27:40', 'Logout', 'Keluar sistem'),
(78, 1, '2025-11-20 19:27:46', 'Login', 'User login'),
(79, 1, '2025-11-20 19:41:31', 'Logout', 'Keluar sistem'),
(80, 2, '2025-11-20 19:41:58', 'Login', 'User login'),
(81, 2, '2025-11-20 19:45:41', 'Logout', 'Keluar sistem'),
(82, 1, '2025-11-20 19:45:52', 'Login', 'User login'),
(83, 1, '2025-11-20 20:13:15', 'Logout', 'Keluar sistem'),
(84, 2, '2025-11-20 20:13:24', 'Login', 'User login'),
(85, 2, '2025-11-20 20:14:54', 'Logout', 'Keluar sistem'),
(86, 1, '2025-11-20 20:15:05', 'Login', 'User login'),
(87, 1, '2025-11-20 22:15:51', 'Logout', 'Keluar sistem'),
(88, 2, '2025-11-20 22:16:00', 'Login', 'User login'),
(89, 1, '2025-12-05 15:50:49', 'Login', 'User login'),
(90, 1, '2025-12-07 10:56:41', 'Logout', 'Keluar sistem'),
(91, 2, '2025-12-07 10:56:47', 'Login', 'User login'),
(92, 2, '2025-12-07 11:00:33', 'Logout', 'Keluar sistem'),
(93, 1, '2025-12-07 11:00:40', 'Login', 'User login'),
(94, 1, '2025-12-07 11:03:05', 'Tambah User', 'Buat user: Admin_Viona, role: admin'),
(95, 1, '2025-12-07 11:04:23', 'Logout', 'Keluar sistem'),
(96, 5, '2025-12-07 11:04:36', 'Login', 'User login'),
(97, 5, '2025-12-07 19:29:28', 'Transaksi Penjualan', 'Nota #11 (Belum Lunas)'),
(98, 5, '2025-12-07 20:01:57', 'Retur Penjualan', 'Nota #11 dengan nilai retur Rp 15.700.000'),
(99, 5, '2025-12-09 00:13:55', 'Logout', 'Keluar sistem'),
(100, 1, '2025-12-09 00:15:46', 'Login', 'User login'),
(101, 1, '2025-12-09 12:38:04', 'Login', 'User login'),
(102, 1, '2025-12-09 12:47:01', 'Transaksi Penjualan', 'Nota #12 (Belum Lunas)'),
(103, 1, '2025-12-09 12:48:05', 'Pembayaran', 'Bayar nota ID 12 sebesar 57400000 (Transfer)'),
(104, 1, '2025-12-09 12:48:08', 'Pembayaran', 'Bayar nota ID 12 sebesar 57400000 (Transfer)'),
(105, 1, '2025-12-09 12:52:27', 'Logout', 'Keluar sistem'),
(106, 1, '2025-12-17 23:37:02', 'Login', 'User login'),
(107, 1, '2025-12-17 23:40:01', 'Logout', 'Keluar sistem'),
(108, 2, '2025-12-17 23:40:11', 'Login', 'User login'),
(109, 2, '2025-12-17 23:42:05', 'Logout', 'Keluar sistem'),
(110, 3, '2025-12-17 23:42:32', 'Login', 'User login'),
(111, 3, '2025-12-17 23:45:31', 'Logout', 'Keluar sistem'),
(112, 1, '2025-12-17 23:47:57', 'Login', 'User login'),
(113, 1, '2025-12-18 00:47:55', 'Logout', 'Keluar sistem'),
(114, 2, '2025-12-18 00:48:05', 'Login', 'User login'),
(115, 2, '2025-12-18 00:57:31', 'Logout', 'Keluar sistem'),
(116, 3, '2025-12-18 00:57:38', 'Login', 'User login'),
(117, 1, '2025-12-19 21:33:38', 'Login', 'User login'),
(118, 1, '2025-12-19 21:33:50', 'Transaksi Penjualan', 'Nota #13 (Belum Lunas)');

-- --------------------------------------------------------

--
-- Struktur dari tabel `pembayaran`
--

CREATE TABLE `pembayaran` (
  `pembayaran_id` int(11) NOT NULL,
  `penjualan_id` int(11) DEFAULT NULL,
  `tanggal_bayar` datetime DEFAULT current_timestamp(),
  `metode` varchar(50) DEFAULT NULL,
  `jumlah_bayar` decimal(12,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `pembayaran`
--

INSERT INTO `pembayaran` (`pembayaran_id`, `penjualan_id`, `tanggal_bayar`, `metode`, `jumlah_bayar`) VALUES
(1, 4, '2025-11-16 19:25:21', 'Giro', 750000.00),
(2, 1, '2025-11-16 22:21:04', 'Cash', 350000.00),
(3, 1, '2025-11-16 22:21:10', 'Cash', 350000.00),
(4, 2, '2025-11-16 22:23:35', 'Cash', 5000.00),
(5, 3, '2025-11-16 22:23:53', 'Transfer', 25000.00),
(6, 5, '2025-11-16 22:24:15', 'Transfer', 200000.00),
(7, 7, '2025-11-20 11:26:33', 'Transfer', 450000.00),
(8, 7, '2025-11-20 11:26:46', 'Transfer', 450000.00),
(9, 6, '2025-11-20 11:27:17', 'Transfer', 8700000.00),
(10, 6, '2025-11-20 11:27:21', 'Transfer', 8700000.00),
(11, 6, '2025-11-20 11:28:06', 'Transfer', 900000000.00),
(12, 6, '2025-11-20 11:28:18', 'Cash', 90000000.00),
(13, 9, '2025-11-20 15:18:33', 'Transfer', 16000000.00),
(14, 9, '2025-11-20 15:18:39', 'Transfer', 16000000.00),
(15, 8, '2025-11-20 15:22:03', 'Cash', 300000.00),
(16, 8, '2025-11-20 15:22:08', 'Cash', 300000.00),
(17, 12, '2025-12-09 12:48:05', 'Transfer', 57400000.00),
(18, 12, '2025-12-09 12:48:08', 'Transfer', 57400000.00);

-- --------------------------------------------------------

--
-- Struktur dari tabel `penjualan`
--

CREATE TABLE `penjualan` (
  `penjualan_id` int(11) NOT NULL,
  `no_nota` int(11) NOT NULL,
  `tanggal` datetime DEFAULT current_timestamp(),
  `total` decimal(12,2) DEFAULT 0.00,
  `customer_id` int(11) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `status_pembayaran` enum('Lunas','Belum Lunas') DEFAULT 'Belum Lunas'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `penjualan`
--

INSERT INTO `penjualan` (`penjualan_id`, `no_nota`, `tanggal`, `total`, `customer_id`, `user_id`, `status_pembayaran`) VALUES
(1, 1, '2025-11-15 16:59:37', 350000.00, 1, 1, 'Lunas'),
(2, 2, '2025-09-01 17:00:10', 5000.00, 1, 1, 'Lunas'),
(3, 3, '2025-11-16 19:16:50', 25000.00, 1, 1, 'Lunas'),
(4, 4, '2025-11-16 19:24:59', 600000.00, 3, 1, 'Lunas'),
(5, 5, '2025-11-16 19:34:14', 200000.00, 4, 1, 'Lunas'),
(6, 6, '2025-11-16 21:54:01', 87600000.00, 1, 1, 'Lunas'),
(7, 7, '2025-11-20 10:42:20', 450000.00, 1, 1, 'Lunas'),
(8, 8, '2025-11-20 11:30:56', 300000.00, 4, 1, 'Lunas'),
(9, 9, '2025-11-20 15:17:42', 1600000.00, 4, 1, 'Lunas'),
(10, 10, '2025-11-20 15:21:21', 123000000.00, 1, 1, 'Belum Lunas'),
(11, 11, '2025-12-07 19:29:28', 40300000.00, 4, 5, 'Belum Lunas'),
(12, 12, '2025-12-09 12:47:01', 57400000.00, 1, 1, 'Lunas'),
(13, 13, '2025-12-19 21:33:50', 750000.00, 1, 1, 'Belum Lunas');

-- --------------------------------------------------------

--
-- Struktur dari tabel `produk`
--

CREATE TABLE `produk` (
  `produk_id` int(11) NOT NULL,
  `nama_produk` varchar(100) NOT NULL,
  `harga` decimal(12,2) NOT NULL,
  `stok` int(11) DEFAULT 0,
  `stok_minimum` int(11) DEFAULT 50,
  `kategori_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `produk`
--

INSERT INTO `produk` (`produk_id`, `nama_produk`, `harga`, `stok`, `stok_minimum`, `kategori_id`) VALUES
(1, 'Gula GMP 1kg', 15000.00, 460, 50, 1),
(2, 'Kacang Tanah 80/90', 32000.00, 25, 50, 2),
(3, 'Garam Dapur 500gr', 5000.00, 60, 50, 3),
(4, 'Gula Rose', 820000.00, 40, 50, 1),
(5, 'Kemiri Bulat', 28000.00, 50, 50, 6);

-- --------------------------------------------------------

--
-- Struktur dari tabel `retur`
--

CREATE TABLE `retur` (
  `retur_id` int(11) NOT NULL,
  `penjualan_id` int(11) DEFAULT NULL,
  `alasan` text DEFAULT NULL,
  `tanggal_retur` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `retur`
--

INSERT INTO `retur` (`retur_id`, `penjualan_id`, `alasan`, `tanggal_retur`) VALUES
(1, 3, 'Barang tidak bagus', '2025-11-16 19:21:20'),
(2, 4, 'tidak sesuai', '2025-11-16 19:25:59'),
(3, 5, 'tidak sesuai', '2025-11-16 19:35:57'),
(4, 6, 'Kebanyakan', '2025-11-16 21:55:55'),
(5, 8, 'kualitas kurang bagus', '2025-11-20 11:34:21'),
(6, 11, 'kebanyakan', '2025-12-07 20:01:57');

-- --------------------------------------------------------

--
-- Struktur dari tabel `retur_detail`
--

CREATE TABLE `retur_detail` (
  `retur_detail_id` int(11) NOT NULL,
  `retur_id` int(11) DEFAULT NULL,
  `produk_id` int(11) DEFAULT NULL,
  `jumlah_retur` int(11) DEFAULT NULL,
  `nilai_retur` decimal(12,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `retur_detail`
--

INSERT INTO `retur_detail` (`retur_detail_id`, `retur_id`, `produk_id`, `jumlah_retur`, `nilai_retur`) VALUES
(1, 1, 3, 4, 20000.00),
(2, 2, 1, 10, 150000.00),
(3, 3, 3, 10, 50000.00),
(4, 4, 4, 100, 82000000.00),
(5, 5, 1, 10, 150000.00),
(6, 6, 1, 500, 7500000.00),
(7, 6, 4, 10, 8200000.00);

-- --------------------------------------------------------

--
-- Struktur dari tabel `stok_masuk`
--

CREATE TABLE `stok_masuk` (
  `stok_id` int(11) NOT NULL,
  `produk_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `tanggal` datetime DEFAULT current_timestamp(),
  `jumlah` int(11) NOT NULL,
  `keterangan` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `stok_masuk`
--

INSERT INTO `stok_masuk` (`stok_id`, `produk_id`, `user_id`, `tanggal`, `jumlah`, `keterangan`) VALUES
(1, 3, 1, '2025-11-16 19:26:49', 56, 'stok garam masuk sebanyak 56'),
(2, 1, 1, '2025-11-20 15:23:35', 1000, 'stok baru');

-- --------------------------------------------------------

--
-- Struktur dari tabel `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','kasir','owner') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `users`
--

INSERT INTO `users` (`user_id`, `username`, `password`, `role`) VALUES
(1, 'admin', '$2y$10$jW.F//MtUj1TH4OcfQOsweoDHeNxIjzz73NsdNyXHfaLJZHBGdDvS', 'admin'),
(2, 'kasir', '$2y$10$v/04I1Db2g9JANguIRsFmeElmrgfASbt52Mkspgq9w46WoUoC1YL2', 'kasir'),
(3, 'owner', '$2y$10$4xZDQ8NGcC6RVCXI4rHGs.cVpqdKCI40JN.PbApL5rI3Q3XrOLslm', 'owner'),
(5, 'Admin_Viona', '$2y$10$GgayElKOjcuj/KOINul1UOyHXVRjRMpSXpqEJM0ba9xOZKVyun98.', 'admin');

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `customer`
--
ALTER TABLE `customer`
  ADD PRIMARY KEY (`customer_id`);

--
-- Indeks untuk tabel `detail_penjualan`
--
ALTER TABLE `detail_penjualan`
  ADD PRIMARY KEY (`detail_id`),
  ADD KEY `penjualan_id` (`penjualan_id`),
  ADD KEY `produk_id` (`produk_id`);

--
-- Indeks untuk tabel `kategori_produk`
--
ALTER TABLE `kategori_produk`
  ADD PRIMARY KEY (`kategori_id`);

--
-- Indeks untuk tabel `log_aktivitas`
--
ALTER TABLE `log_aktivitas`
  ADD PRIMARY KEY (`log_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indeks untuk tabel `pembayaran`
--
ALTER TABLE `pembayaran`
  ADD PRIMARY KEY (`pembayaran_id`),
  ADD KEY `penjualan_id` (`penjualan_id`);

--
-- Indeks untuk tabel `penjualan`
--
ALTER TABLE `penjualan`
  ADD PRIMARY KEY (`penjualan_id`),
  ADD KEY `customer_id` (`customer_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indeks untuk tabel `produk`
--
ALTER TABLE `produk`
  ADD PRIMARY KEY (`produk_id`),
  ADD KEY `kategori_id` (`kategori_id`);

--
-- Indeks untuk tabel `retur`
--
ALTER TABLE `retur`
  ADD PRIMARY KEY (`retur_id`),
  ADD KEY `penjualan_id` (`penjualan_id`);

--
-- Indeks untuk tabel `retur_detail`
--
ALTER TABLE `retur_detail`
  ADD PRIMARY KEY (`retur_detail_id`),
  ADD KEY `retur_id` (`retur_id`),
  ADD KEY `produk_id` (`produk_id`);

--
-- Indeks untuk tabel `stok_masuk`
--
ALTER TABLE `stok_masuk`
  ADD PRIMARY KEY (`stok_id`),
  ADD KEY `produk_id` (`produk_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indeks untuk tabel `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `customer`
--
ALTER TABLE `customer`
  MODIFY `customer_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT untuk tabel `detail_penjualan`
--
ALTER TABLE `detail_penjualan`
  MODIFY `detail_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT untuk tabel `kategori_produk`
--
ALTER TABLE `kategori_produk`
  MODIFY `kategori_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT untuk tabel `log_aktivitas`
--
ALTER TABLE `log_aktivitas`
  MODIFY `log_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=119;

--
-- AUTO_INCREMENT untuk tabel `pembayaran`
--
ALTER TABLE `pembayaran`
  MODIFY `pembayaran_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT untuk tabel `penjualan`
--
ALTER TABLE `penjualan`
  MODIFY `penjualan_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT untuk tabel `produk`
--
ALTER TABLE `produk`
  MODIFY `produk_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT untuk tabel `retur`
--
ALTER TABLE `retur`
  MODIFY `retur_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT untuk tabel `retur_detail`
--
ALTER TABLE `retur_detail`
  MODIFY `retur_detail_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT untuk tabel `stok_masuk`
--
ALTER TABLE `stok_masuk`
  MODIFY `stok_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT untuk tabel `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `detail_penjualan`
--
ALTER TABLE `detail_penjualan`
  ADD CONSTRAINT `detail_penjualan_ibfk_1` FOREIGN KEY (`penjualan_id`) REFERENCES `penjualan` (`penjualan_id`),
  ADD CONSTRAINT `detail_penjualan_ibfk_2` FOREIGN KEY (`produk_id`) REFERENCES `produk` (`produk_id`);

--
-- Ketidakleluasaan untuk tabel `log_aktivitas`
--
ALTER TABLE `log_aktivitas`
  ADD CONSTRAINT `log_aktivitas_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`);

--
-- Ketidakleluasaan untuk tabel `pembayaran`
--
ALTER TABLE `pembayaran`
  ADD CONSTRAINT `pembayaran_ibfk_1` FOREIGN KEY (`penjualan_id`) REFERENCES `penjualan` (`penjualan_id`);

--
-- Ketidakleluasaan untuk tabel `penjualan`
--
ALTER TABLE `penjualan`
  ADD CONSTRAINT `penjualan_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `customer` (`customer_id`),
  ADD CONSTRAINT `penjualan_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`);

--
-- Ketidakleluasaan untuk tabel `produk`
--
ALTER TABLE `produk`
  ADD CONSTRAINT `produk_ibfk_1` FOREIGN KEY (`kategori_id`) REFERENCES `kategori_produk` (`kategori_id`);

--
-- Ketidakleluasaan untuk tabel `retur`
--
ALTER TABLE `retur`
  ADD CONSTRAINT `retur_ibfk_1` FOREIGN KEY (`penjualan_id`) REFERENCES `penjualan` (`penjualan_id`);

--
-- Ketidakleluasaan untuk tabel `retur_detail`
--
ALTER TABLE `retur_detail`
  ADD CONSTRAINT `retur_detail_ibfk_1` FOREIGN KEY (`retur_id`) REFERENCES `retur` (`retur_id`),
  ADD CONSTRAINT `retur_detail_ibfk_2` FOREIGN KEY (`produk_id`) REFERENCES `produk` (`produk_id`);

--
-- Ketidakleluasaan untuk tabel `stok_masuk`
--
ALTER TABLE `stok_masuk`
  ADD CONSTRAINT `stok_masuk_ibfk_1` FOREIGN KEY (`produk_id`) REFERENCES `produk` (`produk_id`),
  ADD CONSTRAINT `stok_masuk_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
