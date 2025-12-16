-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Waktu pembuatan: 06 Des 2025 pada 20.21
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
-- Database: `polmed_pengaduan`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `fasilitas`
--

CREATE TABLE `fasilitas` (
  `id` int(11) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `lokasi` varchar(100) DEFAULT NULL,
  `deskripsi` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `fasilitas`
--

INSERT INTO `fasilitas` (`id`, `nama`, `lokasi`, `deskripsi`) VALUES
(1, 'Lab Komputer A', 'Gedung Utama Lt.2', 'Digunakan untuk praktikum pemrograman.'),
(2, 'Toilet Gedung Utama Lt.2', 'Gedung Utama Lt.2', 'Toilet umum untuk mahasiswa & dosen.'),
(3, 'AC Ruang Auditorium', 'Gedung Serba Guna', 'AC central untuk ruang kapasitas 200 orang.'),
(4, 'Kursi Ruang Kelas 301', 'Gedung B Lt.3', 'Kursi kuliah rusak kaki kanan.'),
(5, 'Printer Lab Jaringan', 'Lab Jaringan', 'Printer HP LaserJet tidak bisa mencetak.');

-- --------------------------------------------------------

--
-- Struktur dari tabel `notifikasi`
--

CREATE TABLE `notifikasi` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `judul` varchar(100) NOT NULL,
  `isi` text NOT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `notifikasi`
--

INSERT INTO `notifikasi` (`id`, `user_id`, `judul`, `isi`, `is_read`, `created_at`) VALUES
(1, 5, 'Pengaduan Anda Diperbarui', 'Pengaduan dengan nomor tiket PM-??? statusnya berubah menjadi: Diproses', 0, '2025-12-05 13:09:35'),
(2, 5, 'Pengaduan Diperbarui', 'Pengaduan dengan nomor tiket PM-2025-00008 statusnya berubah menjadi: Ditolak.\n\n Catatan: malas', 0, '2025-12-05 17:31:36'),
(3, 5, 'Pengaduan Diperbarui', 'Pengaduan dengan nomor tiket PM-2025-00005 statusnya berubah menjadi: Ditolak.\n\n Catatan: s', 0, '2025-12-05 17:32:19'),
(4, 4, 'Pengaduan Diperbarui', 'Pengaduan dengan nomor tiket PM-2025-00009 statusnya berubah menjadi: Diproses.\n\n Catatan: Teknisi sedang menangani laporan Anda.', 0, '2025-12-06 09:16:19'),
(5, 4, 'Pengaduan Diperbarui', 'Pengaduan dengan nomor tiket PM-2025-00007 statusnya berubah menjadi: Diproses.\n\n Catatan: Teknisi sedang menangani laporan Anda.', 0, '2025-12-06 09:41:31'),
(6, 6, 'Pengaduan Diperbarui', 'Pengaduan dengan nomor tiket PM-2025-00006 statusnya berubah menjadi: Diproses.\n\n Catatan: Teknisi sedang menangani laporan Anda.', 0, '2025-12-06 09:52:41'),
(7, 4, 'Pengaduan Diperbarui', 'Pengaduan dengan nomor tiket PM-2025-00009 statusnya berubah menjadi: Selesai.\n\n Catatan: Laporan Anda telah selesai ditangani. Terima kasih!', 0, '2025-12-06 09:58:02'),
(8, 4, 'Pengaduan Diperbarui', 'Pengaduan dengan nomor tiket PM-2025-00007 statusnya berubah menjadi: Selesai.\n\n Catatan: Laporan Anda telah selesai ditangani. Terima kasih!', 0, '2025-12-06 09:58:29'),
(9, 6, 'Pengaduan Diperbarui', 'Pengaduan dengan nomor tiket PM-2025-00006 statusnya berubah menjadi: Selesai.\n\n Catatan: Laporan Anda telah selesai ditangani. Terima kasih!', 0, '2025-12-06 10:04:35'),
(10, 6, 'Pengaduan Diperbarui', 'Pengaduan dengan nomor tiket PM-2025-00003 statusnya berubah menjadi: Diproses.\n\n Catatan: Teknisi sedang menangani laporan Anda.', 0, '2025-12-06 10:05:06'),
(11, 6, 'Pengaduan Diperbarui', 'Pengaduan dengan nomor tiket PM-2025-00003 statusnya berubah menjadi: Selesai.\n\n Catatan: Laporan Anda telah selesai ditangani. Terima kasih!', 0, '2025-12-06 10:14:25');

-- --------------------------------------------------------

--
-- Struktur dari tabel `pengaduan`
--

CREATE TABLE `pengaduan` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `fasilitas_id` int(11) NOT NULL,
  `no_tiket` varchar(20) NOT NULL,
  `judul` varchar(150) NOT NULL,
  `deskripsi` text NOT NULL,
  `foto` varchar(255) DEFAULT NULL,
  `status` enum('menunggu','diproses','selesai','ditolak') NOT NULL DEFAULT 'menunggu',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `pengaduan`
--

INSERT INTO `pengaduan` (`id`, `user_id`, `fasilitas_id`, `no_tiket`, `judul`, `deskripsi`, `foto`, `status`, `created_at`, `updated_at`) VALUES
(1, 4, 1, 'PM-2025-00001', 'Komputer 5 mati total', 'Komputer nomor 5 di Lab Komputer A tidak bisa dinyalakan sama sekali. Sudah dicoba restart dan cek kabel.', NULL, 'selesai', '2025-12-01 08:30:00', '2025-12-02 10:15:00'),
(2, 5, 2, 'PM-2025-00002', 'Toilet wanita mampet', 'Toilet nomor 3 di lantai 2 sering mampet setelah dipakai. Sudah 3 hari belum diperbaiki.', 'pengaduan_674f1a2b3c4d5.jpg', 'diproses', '2025-12-02 09:45:00', '2025-12-04 14:20:00'),
(3, 6, 3, 'PM-2025-00003', 'AC auditorium tidak dingin', 'AC di ruang auditorium hanya mengeluarkan angin biasa, tidak dingin. Digunakan untuk acara besok.', NULL, 'selesai', '2025-12-04 13:20:00', '2025-12-06 10:14:25'),
(4, 4, 4, 'PM-2025-00004', 'Kursi kuliah patah', 'Kursi nomor 12 di ruang 301 patah bagian kaki kanan, berbahaya jika dipakai.', NULL, 'ditolak', '2025-12-03 10:10:00', '2025-12-03 16:45:00'),
(5, 5, 5, 'PM-2025-00005', 'Printer tidak bisa scan', 'Printer di Lab Jaringan bisa print tapi tidak bisa scan dokumen.', NULL, 'ditolak', '2025-12-04 11:30:00', '2025-12-05 17:32:19'),
(6, 6, 1, 'PM-2025-00006', 'Proyektor tidak konek HDMI', 'Proyektor di Lab Komputer A tidak mendeteksi sinyal HDMI dari laptop.', NULL, 'selesai', '2025-12-05 08:15:00', '2025-12-06 10:04:35'),
(7, 4, 2, 'PM-2025-00007', 'Lampu toilet redup', 'Lampu di toilet laki-laki lantai 2 sangat redup, hampir tidak terlihat.', NULL, 'selesai', '2025-12-05 10:40:00', '2025-12-06 09:58:29'),
(8, 5, 3, 'PM-2025-00008', 'Remote AC hilang', 'Remote AC di ruang auditorium tidak ditemukan.', NULL, 'ditolak', '2025-12-05 14:25:00', '2025-12-05 17:31:36'),
(9, 4, 3, 'PM-2025-00009', 'ac 1', 'sadasdasd', 'pengaduan_6933005f476b2.png', 'selesai', '2025-12-05 15:55:11', '2025-12-06 09:58:02');

-- --------------------------------------------------------

--
-- Struktur dari tabel `riwayat_pengaduan`
--

CREATE TABLE `riwayat_pengaduan` (
  `id` int(11) NOT NULL,
  `pengaduan_id` int(11) NOT NULL,
  `status_baru` varchar(20) NOT NULL,
  `catatan` text DEFAULT NULL,
  `updated_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `riwayat_pengaduan`
--

INSERT INTO `riwayat_pengaduan` (`id`, `pengaduan_id`, `status_baru`, `catatan`, `updated_by`, `created_at`) VALUES
(1, 1, 'diproses', 'Teknisi sudah dikirim ke lokasi.', 2, '2025-12-01 15:30:00'),
(2, 1, 'selesai', 'Ganti power supply komputer. Sudah diuji dan berfungsi normal.', 2, '2025-12-02 10:15:00'),
(3, 2, 'diproses', 'Pipa sedang dibersihkan.', 3, '2025-12-04 14:20:00'),
(4, 4, 'ditolak', 'Foto tidak dilampirkan, lokasi tidak jelas. Mohon perjelas.', 1, '2025-12-03 16:45:00'),
(5, 5, 'diproses', 'Printer dalam antrian servis.', 2, '2025-12-05 09:00:00'),
(6, 8, 'diproses', '', 1, '2025-12-05 13:09:35'),
(7, 8, 'ditolak', 'malas', 2, '2025-12-05 17:31:36'),
(8, 5, 'ditolak', 's', 2, '2025-12-05 17:32:19'),
(9, 9, 'diproses', '', 1, '2025-12-06 09:16:19'),
(10, 7, 'diproses', '', 1, '2025-12-06 09:41:31'),
(11, 6, 'diproses', '', 1, '2025-12-06 09:52:41'),
(12, 9, 'selesai', '', 1, '2025-12-06 09:58:02'),
(13, 7, 'selesai', '', 1, '2025-12-06 09:58:29'),
(14, 6, 'selesai', '', 1, '2025-12-06 10:04:35'),
(15, 3, 'diproses', '', 1, '2025-12-06 10:05:06'),
(16, 3, 'selesai', '', 1, '2025-12-06 10:14:25');

-- --------------------------------------------------------

--
-- Struktur dari tabel `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `nim_nip` varchar(20) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('mahasiswa','petugas','admin') NOT NULL DEFAULT 'mahasiswa',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `users`
--

INSERT INTO `users` (`id`, `nama`, `nim_nip`, `email`, `password`, `role`, `created_at`) VALUES
(1, 'Admin Polmed', 'ADM001', 'admin@polmed.ac.id', '$2y$10$03Iz81wzkAmcuVi4Wj9Youpp8I4nu0lPpZck3sYyEpxmZjraekbuW', 'admin', '2025-12-05 07:00:00'),
(2, 'Budi Santoso', 'PTG001', 'budi@polmed.ac.id', '$2y$10$03Iz81wzkAmcuVi4Wj9Youpp8I4nu0lPpZck3sYyEpxmZjraekbuW', 'petugas', '2025-12-05 07:01:00'),
(3, 'Siti Rahayu', 'PTG002', 'siti@polmed.ac.id', '$2y$10$03Iz81wzkAmcuVi4Wj9Youpp8I4nu0lPpZck3sYyEpxmZjraekbuW', 'petugas', '2025-12-05 07:02:00'),
(4, 'Ahmad Fauzi', '23.11.0001', 'ahmad@mail.com', '$2y$10$03Iz81wzkAmcuVi4Wj9Youpp8I4nu0lPpZck3sYyEpxmZjraekbuW', 'mahasiswa', '2025-12-05 07:03:00'),
(5, 'Dewi Lestari', '23.11.0002', 'dewi@mail.com', '$2y$10$03Iz81wzkAmcuVi4Wj9Youpp8I4nu0lPpZck3sYyEpxmZjraekbuW', 'mahasiswa', '2025-12-05 07:04:00'),
(6, 'Rudi Hartono', '23.11.0003', 'rudi@mail.com', '$2y$10$03Iz81wzkAmcuVi4Wj9Youpp8I4nu0lPpZck3sYyEpxmZjraekbuW', 'mahasiswa', '2025-12-05 07:05:00'),
(7, 'jamal', '23.11.0005', 'jamal@polmed.com', '$2y$10$ADeE4QnKqfMNzMuixjSpaul6tFtNmhuI.49Pht0rn3etdssAJmTf.', 'mahasiswa', '2025-12-05 16:25:10');

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `fasilitas`
--
ALTER TABLE `fasilitas`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `notifikasi`
--
ALTER TABLE `notifikasi`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indeks untuk tabel `pengaduan`
--
ALTER TABLE `pengaduan`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `fasilitas_id` (`fasilitas_id`);

--
-- Indeks untuk tabel `riwayat_pengaduan`
--
ALTER TABLE `riwayat_pengaduan`
  ADD PRIMARY KEY (`id`),
  ADD KEY `pengaduan_id` (`pengaduan_id`),
  ADD KEY `updated_by` (`updated_by`);

--
-- Indeks untuk tabel `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nim_nip` (`nim_nip`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `fasilitas`
--
ALTER TABLE `fasilitas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT untuk tabel `notifikasi`
--
ALTER TABLE `notifikasi`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT untuk tabel `pengaduan`
--
ALTER TABLE `pengaduan`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT untuk tabel `riwayat_pengaduan`
--
ALTER TABLE `riwayat_pengaduan`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT untuk tabel `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `notifikasi`
--
ALTER TABLE `notifikasi`
  ADD CONSTRAINT `notifikasi_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `pengaduan`
--
ALTER TABLE `pengaduan`
  ADD CONSTRAINT `pengaduan_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `pengaduan_ibfk_2` FOREIGN KEY (`fasilitas_id`) REFERENCES `fasilitas` (`id`);

--
-- Ketidakleluasaan untuk tabel `riwayat_pengaduan`
--
ALTER TABLE `riwayat_pengaduan`
  ADD CONSTRAINT `riwayat_pengaduan_ibfk_1` FOREIGN KEY (`pengaduan_id`) REFERENCES `pengaduan` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `riwayat_pengaduan_ibfk_2` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;


