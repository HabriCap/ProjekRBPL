-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Mar 18, 2026 at 09:30 AM
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
-- Database: `rpbl_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `arsip_laporan`
--

CREATE TABLE `arsip_laporan` (
  `id_arsiplaporan` int(11) NOT NULL,
  `id_laporan` int(11) NOT NULL,
  `tanggal_arsip` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `arsip_nota`
--

CREATE TABLE `arsip_nota` (
  `id_arsipnota` int(11) NOT NULL,
  `id_nota` int(11) NOT NULL,
  `tanggal_arsip` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `barang`
--

CREATE TABLE `barang` (
  `id_barang` int(11) NOT NULL,
  `nama_barang` varchar(100) NOT NULL,
  `jenis_barang` enum('Material Bangunan','Besi & Logam','Keramik & Lantai','Alat pertukangan','Kayu & Olahan','Listrik') NOT NULL,
  `jumlah_barang` int(100) NOT NULL,
  `status_barang` enum('Sesuai','Cacat') NOT NULL,
  `id_nota` int(11) NOT NULL,
  `foto_bukti` varchar(255) NOT NULL,
  `keterangan` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `barang`
--

INSERT INTO `barang` (`id_barang`, `nama_barang`, `jenis_barang`, `jumlah_barang`, `status_barang`, `id_nota`, `foto_bukti`, `keterangan`) VALUES
(1, 'Gergaji', 'Alat pertukangan', 120, 'Sesuai', 1, '', ''),
(2, 'Kayu jati', 'Kayu & Olahan', 125, 'Cacat', 1, '1773821974_Screenshot 2026-03-12 000455.png', 'ada yang berjamur, dan ada juga yang dah lapuk'),
(3, 'Kaca Powder', 'Keramik & Lantai', 145, 'Cacat', 1, '1773821974_Screenshot 2026-03-11 233846.png', 'Beberapa powder hilang/kosong');

-- --------------------------------------------------------

--
-- Table structure for table `laporan`
--

CREATE TABLE `laporan` (
  `id_laporan` int(11) NOT NULL,
  `tanggal` int(11) NOT NULL,
  `detail_laporan` text NOT NULL,
  `tanggal_laporan` date NOT NULL,
  `status` enum('diterima','ditolak') NOT NULL,
  `catatanrevisi` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `nota`
--

CREATE TABLE `nota` (
  `id_nota` int(11) NOT NULL,
  `nomor_nota` text NOT NULL,
  `tanggal_nota` date NOT NULL,
  `supplier` varchar(100) NOT NULL,
  `foto` varchar(255) NOT NULL,
  `status` varchar(20) NOT NULL DEFAULT '''Belum Dicek'''
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `nota`
--

INSERT INTO `nota` (`id_nota`, `nomor_nota`, `tanggal_nota`, `supplier`, `foto`, `status`) VALUES
(1, '1', '1948-03-22', 'PT Solo', 'Screenshot 2026-03-12 000455.png', 'Sudah Dicek');

-- --------------------------------------------------------

--
-- Table structure for table `retur`
--

CREATE TABLE `retur` (
  `id_retur` int(11) NOT NULL,
  `jenis_retur` enum('material bangunan','besi & logam','keramik & lantai','alat pertukangan','kayu & olahan','listrik') NOT NULL,
  `jumlah_cacat` int(11) NOT NULL,
  `alasan` text NOT NULL,
  `tanggal_retur` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `user`
--

CREATE TABLE `user` (
  `id_user` int(11) NOT NULL,
  `username` varchar(100) NOT NULL,
  `password` varchar(100) NOT NULL,
  `role` enum('admin','kasir','manager') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user`
--

INSERT INTO `user` (`id_user`, `username`, `password`, `role`) VALUES
(1, 'admin2222', 'admin1111', 'admin'),
(2, 'kasir2222', 'kasir1111', 'kasir'),
(3, 'manager2222', 'manager1111', 'manager');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `arsip_laporan`
--
ALTER TABLE `arsip_laporan`
  ADD PRIMARY KEY (`id_arsiplaporan`),
  ADD KEY `id_laporan` (`id_laporan`);

--
-- Indexes for table `arsip_nota`
--
ALTER TABLE `arsip_nota`
  ADD PRIMARY KEY (`id_arsipnota`);

--
-- Indexes for table `barang`
--
ALTER TABLE `barang`
  ADD PRIMARY KEY (`id_barang`),
  ADD KEY `id_nota` (`id_nota`);

--
-- Indexes for table `laporan`
--
ALTER TABLE `laporan`
  ADD PRIMARY KEY (`id_laporan`);

--
-- Indexes for table `nota`
--
ALTER TABLE `nota`
  ADD PRIMARY KEY (`id_nota`);

--
-- Indexes for table `retur`
--
ALTER TABLE `retur`
  ADD PRIMARY KEY (`id_retur`);

--
-- Indexes for table `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`id_user`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `arsip_laporan`
--
ALTER TABLE `arsip_laporan`
  MODIFY `id_arsiplaporan` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `arsip_nota`
--
ALTER TABLE `arsip_nota`
  MODIFY `id_arsipnota` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `barang`
--
ALTER TABLE `barang`
  MODIFY `id_barang` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `laporan`
--
ALTER TABLE `laporan`
  MODIFY `id_laporan` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `nota`
--
ALTER TABLE `nota`
  MODIFY `id_nota` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `retur`
--
ALTER TABLE `retur`
  MODIFY `id_retur` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `user`
--
ALTER TABLE `user`
  MODIFY `id_user` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `arsip_laporan`
--
ALTER TABLE `arsip_laporan`
  ADD CONSTRAINT `arsip_laporan_ibfk_1` FOREIGN KEY (`id_laporan`) REFERENCES `laporan` (`id_laporan`);

--
-- Constraints for table `barang`
--
ALTER TABLE `barang`
  ADD CONSTRAINT `barang_ibfk_1` FOREIGN KEY (`id_nota`) REFERENCES `nota` (`id_nota`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
