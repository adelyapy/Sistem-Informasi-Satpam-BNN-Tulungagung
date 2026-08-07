-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Waktu pembuatan: 29 Jul 2026 pada 05.28
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
-- Database: `db_sistem_informasi_satpam`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `anggota_shift`
--

CREATE TABLE `anggota_shift` (
  `id_anggota` int(11) NOT NULL,
  `id_laporan` int(11) NOT NULL,
  `id_satpam` int(11) NOT NULL,
  `status_login` enum('belum_login','sudah_login') NOT NULL DEFAULT 'belum_login',
  `login_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `buku_saku`
--

CREATE TABLE `buku_saku` (
  `id_buku` int(11) NOT NULL,
  `judul` varchar(200) NOT NULL,
  `nama_file` varchar(255) NOT NULL,
  `path_file` varchar(255) NOT NULL,
  `ukuran_file` bigint(20) DEFAULT NULL,
  `uploaded_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `inventaris`
--

CREATE TABLE `inventaris` (
  `id_inventaris` int(11) NOT NULL,
  `id_laporan` int(11) NOT NULL,
  `urutan` int(11) NOT NULL,
  `nama_barang` varchar(150) NOT NULL,
  `jumlah` int(11) NOT NULL DEFAULT 1,
  `keterangan` text DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `jadwal_shift`
--

CREATE TABLE `jadwal_shift` (
  `id_jadwal` int(11) NOT NULL,
  `id_satpam` int(11) NOT NULL,
  `id_shift` int(11) NOT NULL,
  `tanggal` date NOT NULL,
  `status` enum('bertugas','libur') DEFAULT 'bertugas',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `jadwal_shift`
--

INSERT INTO `jadwal_shift` (`id_jadwal`, `id_satpam`, `id_shift`, `tanggal`, `status`, `created_at`) VALUES
(1, 3, 1, '2026-07-28', 'bertugas', '2026-07-28 02:38:48'),
(2, 3, 2, '2026-07-28', 'bertugas', '2026-07-28 02:38:48'),
(3, 4, 2, '2026-07-28', 'bertugas', '2026-07-28 02:38:48'),
(4, 5, 1, '2026-07-29', 'bertugas', '2026-07-28 02:38:48');

-- --------------------------------------------------------

--
-- Struktur dari tabel `laporan`
--

CREATE TABLE `laporan` (
  `id_laporan` int(11) NOT NULL,
  `id_jadwal` int(11) NOT NULL,
  `created_by` int(11) NOT NULL,
  `tanggal_laporan` date NOT NULL,
  `status` enum('draft','menunggu_validasi','tervalidasi') DEFAULT 'draft',
  `inventaris_selesai` tinyint(1) DEFAULT 0,
  `uraian_selesai` tinyint(1) DEFAULT 0,
  `validated_by` int(11) DEFAULT NULL,
  `validated_at` datetime DEFAULT NULL,
  `ttd_kepala` varchar(255) DEFAULT NULL,
  `ttd_satpam` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `nomor_penting`
--

CREATE TABLE `nomor_penting` (
  `id_nomor` int(11) NOT NULL,
  `instansi` varchar(150) NOT NULL,
  `nomor_telepon` varchar(30) NOT NULL,
  `keterangan` varchar(255) DEFAULT NULL,
  `urutan` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `nomor_penting`
--

INSERT INTO `nomor_penting` (`id_nomor`, `instansi`, `nomor_telepon`, `keterangan`, `urutan`, `created_at`, `updated_at`) VALUES
(1, 'Polres Tulungagung', '110', 'Kepolisian', 1, '2026-07-28 02:49:16', NULL),
(2, 'Pemadam Kebakaran', '113', 'Damkar', 2, '2026-07-28 02:49:16', NULL),
(3, 'Ambulans', '119', 'Layanan Medis', 3, '2026-07-28 02:49:16', NULL);

-- --------------------------------------------------------

--
-- Struktur dari tabel `shift`
--

CREATE TABLE `shift` (
  `id_shift` int(11) NOT NULL,
  `nama_shift` varchar(30) NOT NULL,
  `jam_mulai` time NOT NULL,
  `jam_selesai` time NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `shift`
--

INSERT INTO `shift` (`id_shift`, `nama_shift`, `jam_mulai`, `jam_selesai`) VALUES
(1, 'Shift 1', '07:00:00', '19:00:00'),
(2, 'Shift 2', '19:00:00', '07:00:00');

-- --------------------------------------------------------

--
-- Struktur dari tabel `uraian_kegiatan`
--

CREATE TABLE `uraian_kegiatan` (
  `id_uraian` int(11) NOT NULL,
  `id_laporan` int(11) NOT NULL,
  `urutan` int(11) NOT NULL,
  `jam` time NOT NULL,
  `uraian` text NOT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `users`
--

CREATE TABLE `users` (
  `id_user` int(11) NOT NULL,
  `kode_satpam` varchar(30) DEFAULT NULL,
  `nama` varchar(100) NOT NULL,
  `username` varchar(50) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `foto` varchar(255) DEFAULT NULL,
  `ttd` varchar(255) DEFAULT NULL,
  `role` enum('admin','kepala','satpam') NOT NULL,
  `status` enum('aktif','nonaktif') NOT NULL DEFAULT 'aktif',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `users`
--

INSERT INTO `users` (`id_user`, `kode_satpam`, `nama`, `username`, `password`, `foto`, `ttd`, `role`, `status`, `created_at`, `updated_at`) VALUES
(1, NULL, 'Administrator', 'admin', '$2y$10$2OXpVK/jyHNj65I/8hU.fu4BdbZL1tsayy8Bec6bt8itveNjUJK0e', NULL, NULL, 'admin', 'aktif', '2026-07-28 02:38:48', NULL),
(2, NULL, 'Kepala BNN', 'kepala', '$2y$10$4dfcgyACRHcM5ixjbu.8vuN3bXHy8GVTS1tp1WfAhxwFmetRtTPki', NULL, NULL, 'kepala', 'aktif', '2026-07-28 02:38:48', NULL),
(3, 'STP001', 'Angga', NULL, NULL, NULL, NULL, 'satpam', 'aktif', '2026-07-28 02:38:48', NULL),
(4, 'STP002', 'Budi', NULL, NULL, NULL, NULL, 'satpam', 'aktif', '2026-07-28 02:38:48', NULL),
(5, 'STP003', 'Rizky', NULL, NULL, NULL, NULL, 'satpam', 'aktif', '2026-07-28 02:38:48', NULL),
(6, 'STP004', 'Doni', NULL, NULL, NULL, NULL, 'satpam', 'aktif', '2026-07-28 02:38:48', NULL);

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `anggota_shift`
--
ALTER TABLE `anggota_shift`
  ADD PRIMARY KEY (`id_anggota`),
  ADD UNIQUE KEY `uq_anggota` (`id_laporan`,`id_satpam`),
  ADD KEY `idx_anggota_laporan` (`id_laporan`),
  ADD KEY `idx_anggota_satpam` (`id_satpam`);

--
-- Indeks untuk tabel `buku_saku`
--
ALTER TABLE `buku_saku`
  ADD PRIMARY KEY (`id_buku`),
  ADD KEY `fk_buku_user` (`uploaded_by`),
  ADD KEY `idx_buku_judul` (`judul`);

--
-- Indeks untuk tabel `inventaris`
--
ALTER TABLE `inventaris`
  ADD PRIMARY KEY (`id_inventaris`),
  ADD KEY `idx_inventaris_laporan` (`id_laporan`);

--
-- Indeks untuk tabel `jadwal_shift`
--
ALTER TABLE `jadwal_shift`
  ADD PRIMARY KEY (`id_jadwal`),
  ADD UNIQUE KEY `uq_jadwal` (`id_satpam`,`id_shift`,`tanggal`),
  ADD KEY `idx_jadwal_tanggal` (`tanggal`),
  ADD KEY `idx_jadwal_shift` (`id_shift`),
  ADD KEY `idx_jadwal_satpam` (`id_satpam`),
  ADD KEY `idx_jadwal_tanggal_shift` (`tanggal`,`id_shift`);

--
-- Indeks untuk tabel `laporan`
--
ALTER TABLE `laporan`
  ADD PRIMARY KEY (`id_laporan`),
  ADD UNIQUE KEY `uq_laporan_jadwal` (`id_jadwal`),
  ADD KEY `fk_laporan_created` (`created_by`),
  ADD KEY `fk_laporan_validator` (`validated_by`),
  ADD KEY `idx_laporan_status` (`status`),
  ADD KEY `idx_laporan_tanggal` (`tanggal_laporan`),
  ADD KEY `idx_laporan_jadwal` (`id_jadwal`);

--
-- Indeks untuk tabel `nomor_penting`
--
ALTER TABLE `nomor_penting`
  ADD PRIMARY KEY (`id_nomor`),
  ADD KEY `idx_nomor_instansi` (`instansi`);

--
-- Indeks untuk tabel `shift`
--
ALTER TABLE `shift`
  ADD PRIMARY KEY (`id_shift`);

--
-- Indeks untuk tabel `uraian_kegiatan`
--
ALTER TABLE `uraian_kegiatan`
  ADD PRIMARY KEY (`id_uraian`),
  ADD KEY `idx_uraian_laporan` (`id_laporan`);

--
-- Indeks untuk tabel `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id_user`),
  ADD UNIQUE KEY `kode_satpam` (`kode_satpam`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `anggota_shift`
--
ALTER TABLE `anggota_shift`
  MODIFY `id_anggota` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `buku_saku`
--
ALTER TABLE `buku_saku`
  MODIFY `id_buku` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `inventaris`
--
ALTER TABLE `inventaris`
  MODIFY `id_inventaris` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `jadwal_shift`
--
ALTER TABLE `jadwal_shift`
  MODIFY `id_jadwal` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT untuk tabel `laporan`
--
ALTER TABLE `laporan`
  MODIFY `id_laporan` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `nomor_penting`
--
ALTER TABLE `nomor_penting`
  MODIFY `id_nomor` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT untuk tabel `shift`
--
ALTER TABLE `shift`
  MODIFY `id_shift` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT untuk tabel `uraian_kegiatan`
--
ALTER TABLE `uraian_kegiatan`
  MODIFY `id_uraian` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `users`
--
ALTER TABLE `users`
  MODIFY `id_user` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `anggota_shift`
--
ALTER TABLE `anggota_shift`
  ADD CONSTRAINT `fk_anggota_laporan` FOREIGN KEY (`id_laporan`) REFERENCES `laporan` (`id_laporan`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_anggota_satpam` FOREIGN KEY (`id_satpam`) REFERENCES `users` (`id_user`) ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `buku_saku`
--
ALTER TABLE `buku_saku`
  ADD CONSTRAINT `fk_buku_user` FOREIGN KEY (`uploaded_by`) REFERENCES `users` (`id_user`) ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `inventaris`
--
ALTER TABLE `inventaris`
  ADD CONSTRAINT `fk_inventaris_laporan` FOREIGN KEY (`id_laporan`) REFERENCES `laporan` (`id_laporan`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `jadwal_shift`
--
ALTER TABLE `jadwal_shift`
  ADD CONSTRAINT `fk_jadwal_satpam` FOREIGN KEY (`id_satpam`) REFERENCES `users` (`id_user`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_jadwal_shift` FOREIGN KEY (`id_shift`) REFERENCES `shift` (`id_shift`) ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `laporan`
--
ALTER TABLE `laporan`
  ADD CONSTRAINT `fk_laporan_created` FOREIGN KEY (`created_by`) REFERENCES `users` (`id_user`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_laporan_jadwal` FOREIGN KEY (`id_jadwal`) REFERENCES `jadwal_shift` (`id_jadwal`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_laporan_validator` FOREIGN KEY (`validated_by`) REFERENCES `users` (`id_user`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `uraian_kegiatan`
--
ALTER TABLE `uraian_kegiatan`
  ADD CONSTRAINT `fk_uraian_laporan` FOREIGN KEY (`id_laporan`) REFERENCES `laporan` (`id_laporan`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
