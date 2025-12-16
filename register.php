<?php
// register.php — Proses Daftar Mahasiswa
require_once 'config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: index.php");
    exit();
}

// Ambil & sanitasi input
$nama = trim($_POST['nama'] ?? '');
$nim = trim($_POST['nim'] ?? '');
$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';
$password2 = $_POST['password2'] ?? '';

$error = '';

// Validasi
if (empty($nama) || empty($nim) || empty($password)) {
    $error = "Nama, NIM, dan password wajib diisi.";
} elseif (!preg_match('/^\d{2}\.11\.\d{4}$/', $nim)) {
    $error = "Format NIM salah. Contoh: 23.11.0001";
} elseif (strlen($password) < 6) {
    $error = "Password minimal 6 karakter.";
} elseif ($password !== $password2) {
    $error = "Password dan konfirmasi tidak sama.";
} else {
    // Cek duplikat NIM
    $stmt = $koneksi->prepare("SELECT id FROM users WHERE nim_nip = ?");
    $stmt->bind_param("s", $nim);
    $stmt->execute();
    if ($stmt->get_result()->num_rows > 0) {
        $error = "NIM sudah terdaftar. Gunakan NIM yang berbeda.";
    }
    $stmt->close();
}

if (!empty($error)) {
    header("Location: index.php?error=" . urlencode($error) . "#login");
    exit();
}

// ✅ Semua valid — simpan ke database
$hash = password_hash($password, PASSWORD_BCRYPT); // 🔒 Auto-hash aman
$stmt = $koneksi->prepare("INSERT INTO users (nama, nim_nip, email, password, role) VALUES (?, ?, ?, ?, 'mahasiswa')");
$stmt->bind_param("ssss", $nama, $nim, $email, $hash);

if ($stmt->execute()) {
    $_SESSION['success'] = "Akun berhasil dibuat! Silakan login dengan NIM dan password Anda.";
    header("Location: index.php#login");
} else {
    header("Location: index.php?error=Gagal membuat akun. Coba lagi." . "#login");
}

$stmt->close();
$koneksi->close();
exit();
?>