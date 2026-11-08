-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 08, 2026 at 04:36 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `perpustakaan`
--

-- --------------------------------------------------------

--
-- Table structure for table `anggota`
--

CREATE TABLE `anggota` (
  `id` int(11) NOT NULL,
  `nim` varchar(20) NOT NULL,
  `nama` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `anggota`
--

INSERT INTO `anggota` (`id`, `nim`, `nama`) VALUES
(1, '20256835', 'Alya Namiratuz Zahra'),
(2, '26585912', 'Novia Melinda Putri'),
(3, '252623489', 'Dinda Febiola'),
(4, '25267789', 'Dinda Ayu');

-- --------------------------------------------------------

--
-- Table structure for table `buku`
--

CREATE TABLE `buku` (
  `id` int(11) NOT NULL,
  `kode_buku` varchar(20) NOT NULL,
  `judul` varchar(150) NOT NULL,
  `penulis` varchar(100) NOT NULL,
  `penerbit` varchar(100) NOT NULL,
  `kategori` varchar(50) NOT NULL DEFAULT 'Umum',
  `tahun_terbit` int(11) NOT NULL,
  `stok` int(11) NOT NULL DEFAULT 0,
  `status` enum('tersedia','habis') NOT NULL DEFAULT 'tersedia',
  `deleted_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `buku`
--

INSERT INTO `buku` (`id`, `kode_buku`, `judul`, `penulis`, `penerbit`, `kategori`, `tahun_terbit`, `stok`, `status`, `deleted_at`) VALUES
(1, 'LD6279', 'Literasi Digital', 'Siti Aminah', 'Andi Offset', 'Umum', 2021, 20, 'tersedia', '2026-10-09 14:40:46'),
(3, 'BK003', 'Digital Ethic', 'Salma Rosyida', 'Gramedia', 'Umum', 2023, 20, 'tersedia', NULL),
(4, 'BK010', 'Sistem Informasi Publik', 'Dinda Ayu', 'Gramedia', 'Umum', 2020, 1, 'tersedia', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `peminjaman`
--

CREATE TABLE `peminjaman` (
  `id` int(11) NOT NULL,
  `kode_peminjaman` varchar(20) NOT NULL,
  `nim` varchar(20) NOT NULL,
  `id_buku` int(11) NOT NULL,
  `tanggal_pinjam` date NOT NULL,
  `tanggal_kembali` date NOT NULL,
  `tanggal_kembali_aktual` date DEFAULT NULL,
  `status` enum('dipinjam','kembali') NOT NULL DEFAULT 'dipinjam',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `peminjaman`
--

INSERT INTO `peminjaman` (`id`, `kode_peminjaman`, `nim`, `id_buku`, `tanggal_pinjam`, `tanggal_kembali`, `tanggal_kembali_aktual`, `status`, `created_at`) VALUES
(1, 'PJ001', '20256835', 1, '2026-10-05', '2026-10-12', '2026-10-08', 'kembali', '2026-10-05 16:07:34'),
(2, 'PJ002', '26585912', 4, '2026-10-08', '2026-10-15', '2026-10-09', 'kembali', '2026-10-08 04:29:36'),
(3, 'PJ002', '26585912', 3, '2026-10-08', '2026-10-15', '2026-10-09', 'kembali', '2026-10-08 04:29:36'),
(4, 'PJ002', '26585912', 1, '2026-10-08', '2026-10-15', '2026-10-08', 'kembali', '2026-10-08 04:29:36'),
(5, 'PJ005', '20256835', 4, '2026-10-08', '2026-10-15', '2026-10-09', 'kembali', '2026-10-08 05:07:55'),
(6, 'PJ005', '20256835', 3, '2026-10-08', '2026-10-15', '2026-10-09', 'kembali', '2026-10-08 05:07:55'),
(7, 'PJ005', '20256835', 1, '2026-10-08', '2026-10-15', '2026-10-09', 'kembali', '2026-10-08 05:07:55'),
(8, 'PJ008', '26585912', 1, '2026-10-08', '2026-10-15', '2026-10-09', 'kembali', '2026-10-08 05:08:14'),
(9, 'PJ009', '20256835', 4, '2026-10-09', '2026-10-16', '2026-10-09', 'kembali', '2026-10-09 07:20:43'),
(10, 'PJ010', '26585912', 3, '2026-10-09', '2026-10-16', '2026-10-09', 'kembali', '2026-10-09 07:20:49');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `nim` varchar(20) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','user') NOT NULL DEFAULT 'user',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `nama`, `username`, `email`, `nim`, `password`, `role`, `created_at`) VALUES
(1, 'admin perpustakaan', 'Admin', 'adminperpustakaan@gmail.com', '', '$2y$10$SEu0IeJMJhg7.0cpccDHYOISr1KdWD/5KFKPyiU4XiQjlLNGU3E5u', 'admin', '2026-10-05 16:03:40'),
(2, 'Alya Namiratuz Zahra', 'Alya', 'alyanameera@gmail.com', '20256835', '$2y$10$et7e/oi4SmrwkEUP9n3aauLI.XeTkBwLQ6Ktq3LfJg2c.loRLHKPC', 'user', '2026-10-05 16:09:15'),
(3, 'Novia Melinda Putri', 'Noviamelinda', 'novia@gmail.com', '26585912', '$2y$10$HoegeTcNdyjs.OWh34JJv.YlZRjscgdiuoCr3mAjLb1njOwTXXKPu', 'user', '2026-10-08 04:24:19'),
(4, 'Dinda Ayu Wulan Romadhoni', 'Dindaayu', 'dindaayu@gmail.com', '25267789', '$2y$10$lySvSfKkEMxb5swZT2uSieYhyiayI.U2jn0p/bVUpnEATPS3eu5ti', 'user', '2026-10-31 12:22:00');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `anggota`
--
ALTER TABLE `anggota`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nim` (`nim`);

--
-- Indexes for table `buku`
--
ALTER TABLE `buku`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `kode_buku` (`kode_buku`);

--
-- Indexes for table `peminjaman`
--
ALTER TABLE `peminjaman`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `nim` (`nim`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `anggota`
--
ALTER TABLE `anggota`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `buku`
--
ALTER TABLE `buku`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `peminjaman`
--
ALTER TABLE `peminjaman`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
