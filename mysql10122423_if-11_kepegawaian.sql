-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Waktu pembuatan: 14 Feb 2025 pada 15.39
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
-- Database: `mysql10122423_if-11_kepegawaian`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `gaji`
--

CREATE TABLE `gaji` (
  `id_gaji` int(10) UNSIGNED NOT NULL,
  `id_pegawai` int(10) UNSIGNED NOT NULL,
  `bulan` tinyint(3) UNSIGNED NOT NULL CHECK (`bulan` between 1 and 12),
  `tahun` year(4) NOT NULL,
  `gaji_pokok` decimal(15,2) NOT NULL DEFAULT 0.00,
  `tunjangan` decimal(15,2) NOT NULL DEFAULT 0.00,
  `potongan` decimal(15,2) NOT NULL DEFAULT 0.00,
  `total_gaji` decimal(15,2) NOT NULL DEFAULT 0.00,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `gaji`
--

INSERT INTO `gaji` (`id_gaji`, `id_pegawai`, `bulan`, `tahun`, `gaji_pokok`, `tunjangan`, `potongan`, `total_gaji`, `created_at`, `updated_at`) VALUES
(5, 9, 2, '2025', 5500000.00, 2200000.00, 200000.00, 7500000.00, '2025-02-12 07:12:25', '2025-02-12 13:20:53'),
(6, 12, 2, '2025', 5000000.00, 2000000.00, 400000.00, 6600000.00, '2025-02-12 07:13:17', '2025-02-12 13:20:53'),
(7, 4, 2, '2025', 7800000.00, 200000.00, 300000.00, 7700000.00, '2025-02-12 07:14:01', '2025-02-12 13:20:53'),
(9, 8, 2, '2025', 50000000.00, 20000000.00, 13000000.00, 57000000.00, '2025-02-12 14:10:39', '2025-02-12 14:10:39'),
(10, 13, 2, '2025', 4000000.00, 1500000.00, 200000.00, 5300000.00, '2025-02-13 02:30:42', '2025-02-13 02:30:42');

-- --------------------------------------------------------

--
-- Struktur dari tabel `jabatan`
--

CREATE TABLE `jabatan` (
  `id_jabatan` int(10) UNSIGNED NOT NULL,
  `nama_jabatan` varchar(100) NOT NULL,
  `tunjangan` decimal(15,2) NOT NULL DEFAULT 0.00,
  `gaji_awal` decimal(15,2) NOT NULL DEFAULT 0.00,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `jabatan`
--

INSERT INTO `jabatan` (`id_jabatan`, `nama_jabatan`, `tunjangan`, `gaji_awal`, `updated_at`) VALUES
(4, 'Data Analyst', 200000.00, 7800000.00, '2025-02-12 13:20:53'),
(5, 'CEO (Chief Executive Officer)', 20000000.00, 50000000.00, '2025-02-12 13:20:53'),
(6, 'Staff Administrasi', 1500000.00, 4000000.00, '2025-02-12 13:20:53'),
(7, 'Marketing', 2200000.00, 5500000.00, '2025-02-12 13:20:53'),
(8, 'Programmer', 3500000.00, 7500000.00, '2025-02-12 13:20:53'),
(9, 'Teknisi', 2000000.00, 5000000.00, '2025-02-12 13:20:53');

-- --------------------------------------------------------

--
-- Struktur dari tabel `kehadiran`
--

CREATE TABLE `kehadiran` (
  `id_kehadiran` int(10) UNSIGNED NOT NULL,
  `id_pegawai` int(10) UNSIGNED NOT NULL,
  `tanggal` date NOT NULL,
  `status` enum('Hadir','Izin','Sakit','Absen') NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `kehadiran`
--

INSERT INTO `kehadiran` (`id_kehadiran`, `id_pegawai`, `tanggal`, `status`, `created_at`, `updated_at`) VALUES
(4, 8, '2025-02-12', 'Hadir', '2025-02-12 15:20:53', '2025-02-12 15:20:53'),
(5, 14, '2025-02-12', 'Hadir', '2025-02-12 15:23:28', '2025-02-12 15:23:28');

-- --------------------------------------------------------

--
-- Struktur dari tabel `pegawai`
--

CREATE TABLE `pegawai` (
  `id_pegawai` int(10) UNSIGNED NOT NULL,
  `id_users` int(10) UNSIGNED NOT NULL,
  `nama` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `no_hp` varchar(15) NOT NULL,
  `alamat` text NOT NULL,
  `id_jabatan` int(10) UNSIGNED DEFAULT NULL,
  `tanggal_masuk` date NOT NULL,
  `gaji` decimal(15,2) NOT NULL DEFAULT 0.00,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `pegawai`
--

INSERT INTO `pegawai` (`id_pegawai`, `id_users`, `nama`, `email`, `no_hp`, `alamat`, `id_jabatan`, `tanggal_masuk`, `gaji`, `created_at`, `updated_at`) VALUES
(4, 6, 'Jajak', 'jk@gmail.com', '08459344039745', 'DUSUN PON RT 002 RW 003', 6, '2025-02-12', 4400000.00, '2025-02-11 23:18:10', '2025-02-13 02:29:38'),
(8, 10, 'Katyusa Vladimov', 'elhakim1961@gmail.com', '0834583232507', 'DUSUN PON RT 002 RW 003', 5, '2025-02-12', 50000000.00, '2025-02-12 00:48:25', '2025-02-12 13:20:53'),
(9, 11, 'Dimas Dragon', 'dm@gmail.com', '0935603457231', 'Jalan Uranus tengah 2 Blok B3 No. 54 RT 002 RW 006', 7, '2025-02-12', 5500000.00, '2025-02-12 01:46:59', '2025-02-12 13:20:53'),
(11, 13, 'Bilal Aziz', 'aziz@gmail.com', '08348953623', 'DUSUN PON RT 002 RW 003', 4, '2025-02-12', 7800000.00, '2025-02-12 06:41:34', '2025-02-12 13:49:04'),
(12, 14, 'Galih Sucipto', 'galih@gmail.com', '0842942958963', 'DUSUN PON RT 002 RW 003', 9, '2025-02-12', 5000000.00, '2025-02-12 06:44:55', '2025-02-12 13:20:53'),
(13, 15, 'Rizal Fahmi', 'rz@gmail.com', '0843723832426', 'Jalan Uranus tengah 2 Blok B3 No. 54 RT 002 RW 006', 6, '2025-02-12', 4000000.00, '2025-02-12 14:02:02', '2025-02-12 14:02:02'),
(14, 16, 'Abel', 'ab@gmail.com', '08325252389732', 'Jalan Uranus tengah 2 Blok B3 No. 54 RT 002 RW 006', 8, '2025-02-12', 7500000.00, '2025-02-12 14:56:32', '2025-02-12 14:56:32');

-- --------------------------------------------------------

--
-- Struktur dari tabel `users`
--

CREATE TABLE `users` (
  `id_users` int(10) UNSIGNED NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','pegawai') NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `users`
--

INSERT INTO `users` (`id_users`, `username`, `password`, `role`, `created_at`, `updated_at`) VALUES
(2, 'Budi', '$2y$10$kT1eBM2/cYJhW2zmXNHIQebjYYpUgjpXQ7EhwimobIxy8osbNEB/m', 'pegawai', '2025-02-11 08:38:33', '2025-02-12 13:20:53'),
(3, 'admin', 'admin12345', 'admin', '2025-02-11 14:42:50', '2025-02-12 13:20:53'),
(4, 'Hadi', '$2y$10$ar2NFK0XF4j7/Gr.2Mk6yeRdd5PGyBg/Vf42fEF83Svfr19LpncBG', 'pegawai', '2025-02-11 09:00:48', '2025-02-12 13:20:53'),
(6, 'jk', '$2y$10$7VhNc3nj6.lKpgzZXm76nOE/oSseZHg.5XUbJHR.ov3bwqT64wJ/W', 'pegawai', '2025-02-11 23:18:10', '2025-02-12 13:20:53'),
(10, 'Katyusha', '$2y$10$qAhiJtVoLoSJ35avFPstCOqSEWlKRhzfQT9sw8QwgANFB7u2XVE0G', 'pegawai', '2025-02-12 00:48:25', '2025-02-12 13:20:53'),
(11, 'Dimas', '$2y$10$isratxez/zDuUBUWJVE5AOy6Lg4CZbxrlpuFvAWocudVF1KRR3kBC', 'pegawai', '2025-02-12 01:46:59', '2025-02-12 13:20:53'),
(13, 'Bilal', '$2y$10$BKUhgRwT8o5j1vLwNafNUu0Z2Om5ZoHSh9gi8xAwf/9EE9Mfo18Ry', 'pegawai', '2025-02-12 06:41:34', '2025-02-12 13:20:53'),
(14, 'Galih', '$2y$10$D56U8WPQ8Dna6mvUCth34OxS7QAkLnrCRmHYr6ETLrZOtZFvhjW.2', 'pegawai', '2025-02-12 06:44:55', '2025-02-12 13:20:53'),
(15, 'Rizal', '$2y$10$iTBssMaLyg9T13XHt4gSUuevj0xqwgeRRdd0FaV4G9dnhEqPviSdm', 'pegawai', '2025-02-12 14:02:02', '2025-02-12 14:02:02'),
(16, 'Abel', '$2y$10$9nBLSA6hUQMXgT/G6FfmE..ppzPVi44Z2waTUBDkIL9hhZSQ.V6cy', 'pegawai', '2025-02-12 14:56:32', '2025-02-12 14:56:32');

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `gaji`
--
ALTER TABLE `gaji`
  ADD PRIMARY KEY (`id_gaji`),
  ADD KEY `fk_gaji_pegawai` (`id_pegawai`);

--
-- Indeks untuk tabel `jabatan`
--
ALTER TABLE `jabatan`
  ADD PRIMARY KEY (`id_jabatan`);

--
-- Indeks untuk tabel `kehadiran`
--
ALTER TABLE `kehadiran`
  ADD PRIMARY KEY (`id_kehadiran`),
  ADD KEY `fk_kehadiran_pegawai` (`id_pegawai`);

--
-- Indeks untuk tabel `pegawai`
--
ALTER TABLE `pegawai`
  ADD PRIMARY KEY (`id_pegawai`),
  ADD UNIQUE KEY `id_users` (`id_users`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `fk_pegawai_jabatan` (`id_jabatan`);

--
-- Indeks untuk tabel `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id_users`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `gaji`
--
ALTER TABLE `gaji`
  MODIFY `id_gaji` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT untuk tabel `jabatan`
--
ALTER TABLE `jabatan`
  MODIFY `id_jabatan` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT untuk tabel `kehadiran`
--
ALTER TABLE `kehadiran`
  MODIFY `id_kehadiran` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT untuk tabel `pegawai`
--
ALTER TABLE `pegawai`
  MODIFY `id_pegawai` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT untuk tabel `users`
--
ALTER TABLE `users`
  MODIFY `id_users` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `gaji`
--
ALTER TABLE `gaji`
  ADD CONSTRAINT `fk_gaji_pegawai` FOREIGN KEY (`id_pegawai`) REFERENCES `pegawai` (`id_pegawai`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `kehadiran`
--
ALTER TABLE `kehadiran`
  ADD CONSTRAINT `fk_kehadiran_pegawai` FOREIGN KEY (`id_pegawai`) REFERENCES `pegawai` (`id_pegawai`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `pegawai`
--
ALTER TABLE `pegawai`
  ADD CONSTRAINT `fk_pegawai_jabatan` FOREIGN KEY (`id_jabatan`) REFERENCES `jabatan` (`id_jabatan`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_pegawai_users` FOREIGN KEY (`id_users`) REFERENCES `users` (`id_users`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
