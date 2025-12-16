<?php
// dashboard/admin/proses_status.php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once '../../config/database.php';
require_once '../../includes/auth_check.php';
auth_check(['admin', 'petugas']);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: pengaduan.php");
    exit();
}

$pengaduan_id = intval($_POST['pengaduan_id'] ?? 0);
$aksi = $_POST['aksi'] ?? '';
$catatan = trim($_POST['catatan'] ?? '');

$user_id = $_SESSION['user_id'];
$user_role = $_SESSION['role'];

// Validasi input
if (!$pengaduan_id || !in_array($aksi, ['proses', 'selesai', 'tolak'])) {
    $_SESSION['error'] = "Data tidak valid.";
    header("Location: pengaduan.php");
    exit();
}

// Ambil data pengaduan & pelapor (untuk notifikasi)
$stmt = $koneksi->prepare("
    SELECT p.id, p.no_tiket, p.status, p.judul, 
           u.id as pelapor_id, u.nama as pelapor_nama, u.email as pelapor_email
    FROM pengaduan p
    JOIN users u ON p.user_id = u.id
    WHERE p.id = ?
");
$stmt->bind_param("i", $pengaduan_id);
$stmt->execute();
$detail = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$detail) {
    $_SESSION['error'] = "Pengaduan tidak ditemukan.";
    header("Location: pengaduan.php");
    exit();
}

// Tentukan status baru
$status_baru = '';
switch ($aksi) {
    case 'proses':
        if ($detail['status'] !== 'menunggu') {
            $_SESSION['error'] = "Hanya pengaduan 'menunggu' yang bisa diproses.";
            header("Location: pengaduan.php");
            exit();
        }
        $status_baru = 'diproses';
        break;
    case 'selesai':
        if ($detail['status'] !== 'diproses') {
            $_SESSION['error'] = "Hanya pengaduan 'diproses' yang bisa diselesaikan.";
            header("Location: pengaduan.php");
            exit();
        }
        $status_baru = 'selesai';
        break;
    case 'tolak':
        if ($detail['status'] !== 'menunggu' && $detail['status'] !== 'diproses') {
            $_SESSION['error'] = "Pengaduan hanya bisa ditolak saat menunggu/diproses.";
            header("Location: pengaduan.php");
            exit();
        }
        $status_baru = 'ditolak';
        break;
}

// 🛠️ Update status pengaduan
$update_stmt = $koneksi->prepare("
    UPDATE pengaduan 
    SET status = ?, updated_at = NOW() 
    WHERE id = ?
");
$update_stmt->bind_param("si", $status_baru, $pengaduan_id);
$update_success = $update_stmt->execute();
$update_stmt->close();

if (!$update_success) {
    $_SESSION['error'] = "Gagal memperbarui status.";
    header("Location: pengaduan.php");
    exit();
}

// 📝 Catat ke riwayat_pengaduan
$riwayat_stmt = $koneksi->prepare("
    INSERT INTO riwayat_pengaduan (pengaduan_id, status_baru, catatan, updated_by, created_at) 
    VALUES (?, ?, ?, ?, NOW())
");
$riwayat_stmt->bind_param("isss", $pengaduan_id, $status_baru, $catatan, $user_id);
$riwayat_stmt->execute();
$riwayat_stmt->close();

// 🔔 Kirim notifikasi ke pelapor — DIPERBAIKI (lebih informatif)
$notif_judul = "Pengaduan Diperbarui";
$notif_isi = "Pengaduan dengan nomor tiket **" . $detail['no_tiket'] . "** statusnya berubah menjadi: **" . ucfirst($status_baru) . "**.";

// Tambahkan judul & catatan jika relevan
if ($catatan) {
    $notif_isi .= "\n\n📝 Catatan: " . $catatan;
} else {
    // Beri catatan default berdasarkan aksi
    $default_catatan = [
        'proses' => 'Teknisi sedang menangani laporan Anda.',
        'selesai' => 'Laporan Anda telah selesai ditangani. Terima kasih!',
        'tolak' => 'Mohon maaf, laporan Anda ditolak. Silakan buat pengaduan baru dengan data lebih lengkap.'
    ];
    $notif_isi .= "\n\n📝 Catatan: " . $default_catatan[$aksi];
}

// Simpan ke database (tanpa markdown, hanya teks biasa)
$notif_isi_clean = str_replace(['**', '📝'], '', $notif_isi); // hapus bold & emoji untuk DB

$notif_stmt = $koneksi->prepare("
    INSERT INTO notifikasi (user_id, judul, isi, is_read, created_at) 
    VALUES (?, ?, ?, 0, NOW())
");
$notif_stmt->bind_param("iss", $detail['pelapor_id'], $notif_judul, $notif_isi_clean);
$notif_stmt->execute();
// 🔔 KIRIM EMAIL (jika email pelapor ada)
if (!empty($detail['pelapor_email'])) {
    require_once '../../includes/email_helper.php';
    $email_body = "
        <h3>Pemberitahuan Pengaduan Fasilitas Polmed</h3>
        <p><strong>Nomor Tiket:</strong> " . htmlspecialchars($detail['no_tiket']) . "</p>
        <p><strong>Status Baru:</strong> <span style='color: #28a745; font-weight: bold;'>" . ucfirst($status_baru) . "</span></p>
        <p><strong>Catatan:</strong> " . nl2br(htmlspecialchars($notif_isi_clean)) . "</p>
        <hr>
        <p><small>Login ke dashboard untuk melihat detail: <a href='http://localhost/polmed-pengaduan/'>polmed-pengaduan</a></small></p>
    ";
    kirim_email($detail['pelapor_email'], $detail['pelapor_nama'], $notif_judul, $email_body);
}

$notif_stmt->close();

// ✅ Sukses — Redirect ke path absolut
$_SESSION['swal_success'] = "Status pengaduan <strong>" . $detail['no_tiket'] . "</strong> berhasil diubah menjadi: <strong>" . ucfirst($status_baru) . "</strong>";

// Redirect sesuai role pengguna yang melakukan perubahan
if ($user_role === 'admin') {
    header("Location: ../admin/pengaduan.php");
} elseif ($user_role === 'petugas') {
    header("Location: ../petugas/pengaduan.php");
} else {
    header("Location: ../mahasiswa/riwayat.php");
}
exit();
?>