<?php
// includes/auth_check.php
// 🔐 WAJIB dipanggil di AWAL setiap halaman terproteksi (setelah session_start())

// Mulai session (pastikan belum ada output sebelum ini!)
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Cek apakah user sudah login
if (!isset($_SESSION['user_id'])) {
    $_SESSION['error'] = "Anda harus login terlebih dahulu.";
    header("Location: ../index.php"); // redirect ke login
    exit();
}

// Ambil data user dari session (untuk cek role)
$user_id = $_SESSION['user_id'];
$user_role = $_SESSION['role'] ?? 'mahasiswa'; // default: mahasiswa

// 🔒 Fungsi untuk proteksi berdasarkan role
// Contoh pemakaian:
// - auth_check('admin'); → hanya admin boleh akses
// - auth_check(['admin', 'petugas']); → admin atau petugas
// - auth_check(); → hanya pastikan login (semua role boleh)

function auth_check($allowed_roles = null) {
    global $user_role;

    // Jika tidak ada batasan role → cukup pastikan login
    if ($allowed_roles === null) {
        return true;
    }

    // Normalisasi: jadikan array jika string tunggal
    if (!is_array($allowed_roles)) {
        $allowed_roles = [$allowed_roles];
    }

    // Cek apakah role user ada di daftar yang diizinkan
    if (!in_array($user_role, $allowed_roles)) {
        $_SESSION['error'] = "Akses ditolak. Anda tidak memiliki hak untuk membuka halaman ini.";
        // Redirect sesuai role
        if ($user_role === 'mahasiswa') {
            header("Location: ../mahasiswa/index.php");
        } elseif ($user_role === 'petugas') {
            header("Location: ../dashboard/petugas/index.php");
        } else {
            header("Location: ../index.php");
        }
        exit();
    }
}

// ✅ Panggil auth_check() di halaman yang butuh proteksi
// Contoh di dashboard admin:
// require_once '../config/database.php';
// require_once '../includes/auth_check.php';
// auth_check('admin'); // hanya admin
?>