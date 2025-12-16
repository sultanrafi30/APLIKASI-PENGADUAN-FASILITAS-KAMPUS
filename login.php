<?php
// login.php — Proses Login
session_start();

// Load koneksi database
require_once 'config/database.php';

// Cek: hanya boleh POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: index.php");
    exit();
}

// Ambil input & sanitasi
$username = trim($_POST['username'] ?? '');
$password = $_POST['password'] ?? '';

// Validasi input kosong
if (empty($username) || empty($password)) {
    $_SESSION['error'] = "Username dan password wajib diisi.";
    header("Location: index.php");
    exit();
}

// Cari user berdasarkan nim_nip (unik)
$stmt = $koneksi->prepare("SELECT id, nama, password, role FROM users WHERE nim_nip = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
    $user = $result->fetch_assoc();
    
    // Verifikasi password (hash bcrypt)
    if (password_verify($password, $user['password'])) {
        // ✅ Login sukses — set session
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['nama'] = $user['nama'];
        $_SESSION['role'] = $user['role'];
        
        // Redirect sesuai role
        switch ($user['role']) {
            case 'admin':
                header("Location: dashboard/admin/index.php");
                break;
            case 'petugas':
                header("Location: dashboard/petugas/index.php");
                break;
            case 'mahasiswa':
            default:
                header("Location: dashboard/mahasiswa/index.php");
                break;
        }
        exit();
    } else {
        // ❌ Password salah
        $_SESSION['error'] = "Password salah. Silakan coba lagi.";
    }
} else {
    // ❌ Username tidak ditemukan
    $_SESSION['error'] = "NIM/NIP tidak terdaftar.";
}

$stmt->close();
header("Location: index.php");
exit();
?>