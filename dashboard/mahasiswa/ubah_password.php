<?php
// mahasiswa/ubah_password.php
require_once '../../config/database.php';
require_once '../../includes/auth_check.php';
auth_check('mahasiswa');

$user_id = $_SESSION['user_id'];
$error = '';
$success = '';

// Ambil hash password lama dari database
$current_hash = $koneksi->query("SELECT password FROM users WHERE id = $user_id")->fetch_assoc()['password'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password_lama = $_POST['password_lama'] ?? '';
    $password_baru = $_POST['password_baru'] ?? '';
    $password_ulang = $_POST['password_ulang'] ?? '';

    // Validasi
    if (empty($password_lama) || empty($password_baru) || empty($password_ulang)) {
        $error = "Semua field wajib diisi.";
    } elseif (!password_verify($password_lama, $current_hash)) {
        $error = "Password lama salah.";
    } elseif (strlen($password_baru) < 8) {
        $error = "Password baru minimal 8 karakter.";
    } elseif ($password_baru !== $password_ulang) {
        $error = "Password baru dan konfirmasi tidak sama.";
    } elseif (password_verify($password_baru, $current_hash)) {
        $error = "Password baru tidak boleh sama dengan password lama.";
    } else {
        // Hash password baru
        $new_hash = password_hash($password_baru, PASSWORD_BCRYPT);
        $stmt = $koneksi->prepare("UPDATE users SET password = ? WHERE id = ?");
        $stmt->bind_param("si", $new_hash, $user_id);
        
        if ($stmt->execute()) {
            $_SESSION['swal_success'] = "Password berhasil diubah. Silakan login ulang.";
            // Logout otomatis setelah ganti password (lebih aman)
            session_destroy();
            header("Location: ../../index.php");
            exit();
        } else {
            $error = "Gagal mengubah password.";
        }
        $stmt->close();
    }
}
?>

<?php include '../../includes/header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="fas fa-lock me-2"></i> Ubah Password</h2>
    <a href="profil.php" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left me-1"></i> Kembali ke Profil
    </a>
</div>

<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header bg-warning text-dark">
                <i class="fas fa-shield-alt me-2"></i> Keamanan Akun
            </div>
            <div class="card-body">
                <?php if ($error): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-circle me-2"></i>
                        <?= htmlspecialchars($error) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <form method="POST">
                    <div class="mb-3">
                        <label class="form-label">Password Lama *</label>
                        <input type="password" class="form-control" name="password_lama" required>
                        <div class="form-text">Masukkan password saat ini.</div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Password Baru *</label>
                        <input type="password" class="form-control" name="password_baru" 
                               minlength="8" required>
                        <div class="form-text">
                            Minimal 8 karakter. Gunakan kombinasi huruf & angka.
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Konfirmasi Password Baru *</label>
                        <input type="password" class="form-control" name="password_ulang" 
                               minlength="8" required>
                    </div>

                    <div class="d-grid">
                        <button type="submit" class="btn btn-warning">
                            <i class="fas fa-sync-alt me-1"></i> Ubah Password
                        </button>
                    </div>
                </form>

                <div class="mt-3 p-3 bg-light rounded">
                    <h6><i class="fas fa-info-circle me-1"></i> Tips Keamanan</h6>
                    <ul class="mb-0 small">
                        <li>Jangan gunakan password yang mudah ditebak (NIM, tanggal lahir)</li>
                        <li>Jangan bagikan password ke siapa pun</li>
                        <li>Ganti password secara berkala</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>