<?php
// mahasiswa/profil.php
require_once '../../config/database.php';
require_once '../../includes/auth_check.php';
auth_check('mahasiswa');

$user_id = $_SESSION['user_id'];
$error = '';
$success = '';

// Ambil data user
$user = $koneksi->query("SELECT nama, nim_nip, email FROM users WHERE id = $user_id")->fetch_assoc();

// Proses update profil
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['aksi']) && $_POST['aksi'] === 'update_profil') {
    $nama = trim($_POST['nama'] ?? '');
    $email = trim($_POST['email'] ?? '');

    if (empty($nama)) {
        $error = "Nama wajib diisi.";
    } else {
        $stmt = $koneksi->prepare("UPDATE users SET nama = ?, email = ? WHERE id = ?");
        $stmt->bind_param("ssi", $nama, $email, $user_id);
        if ($stmt->execute()) {
            // Update session juga
            $_SESSION['nama'] = $nama;
            $_SESSION['swal_success'] = "Profil berhasil diperbarui.";
            header("Location: profil.php");
            exit();
        } else {
            $error = "Gagal memperbarui profil.";
        }
        $stmt->close();
    }
}
?>

<?php include '../../includes/header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="fas fa-user-circle me-2"></i> Profil Saya</h2>
    <a href="index.php" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left me-1"></i> Dashboard
    </a>
</div>

<?php if ($error): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fas fa-exclamation-circle me-2"></i>
        <?= htmlspecialchars($error) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<div class="row">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <i class="fas fa-id-card me-2"></i> Data Profil
            </div>
            <div class="card-body">
                <form method="POST">
                    <input type="hidden" name="aksi" value="update_profil">
                    
                    <div class="mb-3">
                        <label class="form-label">Nama Lengkap *</label>
                        <input type="text" class="form-control" name="nama" 
                               value="<?= htmlspecialchars($user['nama']) ?>" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">NIM</label>
                        <input type="text" class="form-control-plaintext" 
                               value="<?= htmlspecialchars($user['nim_nip']) ?>" readonly>
                        <div class="form-text">NIM tidak bisa diubah.</div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" class="form-control" name="email" 
                               value="<?= htmlspecialchars($user['email'] ?? '') ?>">
                    </div>

                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i> Simpan Perubahan
                    </button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card">
            <div class="card-header bg-success text-white">
                <i class="fas fa-key me-2"></i> Keamanan Akun
            </div>
            <div class="card-body">
                <p><strong>Password Anda:</strong></p>
                <ul>
                    <li>Disimpan dalam format terenkripsi</li>
                    <li>Tidak bisa dilihat oleh siapa pun (termasuk admin)</li>
                </ul>
                <a href="ubah_password.php" class="btn btn-success">
                    <i class="fas fa-lock me-1"></i> Ubah Password
                </a>
            </div>
        </div>

        <div class="card mt-3">
            <div class="card-header bg-info text-white">
                <i class="fas fa-info-circle me-2"></i> Informasi
            </div>
            <div class="card-body">
                <p class="mb-1"><i class="fas fa-check-circle text-success me-1"></i> Akun terverifikasi</p>
                <p class="mb-1"><i class="fas fa-shield-alt text-primary me-1"></i> Session aktif</p>
                <small class="text-muted">
                    Terakhir login: <?= date('d M Y H:i') ?>
                </small>
            </div>
        </div>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>