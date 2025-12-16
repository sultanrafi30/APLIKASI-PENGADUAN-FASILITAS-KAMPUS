<?php
// mahasiswa/buat_pengaduan.php
require_once '../../config/database.php';
require_once '../../includes/auth_check.php';
auth_check('mahasiswa');
$user_id = $_SESSION['user_id'];

$success = '';
$error = '';

// Proses form saat POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Ambil & sanitasi input
    $fasilitas_id = intval($_POST['fasilitas_id'] ?? 0);
    $judul = trim($_POST['judul'] ?? '');
    $deskripsi = trim($_POST['deskripsi'] ?? '');

    // Validasi wajib
    if (empty($fasilitas_id) || empty($judul) || empty($deskripsi)) {
        $error = "Semua field wajib diisi.";
    } else {
        // Generate nomor tiket: PM-2025-00001
        $tahun = date('Y');
        $next_id = 1;
        // Cari ID terakhir tahun ini
        $last_query = "SELECT id FROM pengaduan WHERE no_tiket LIKE 'PM-$tahun-%' ORDER BY id DESC LIMIT 1";
        $last_result = $koneksi->query($last_query);
        if ($last_result->num_rows > 0) {
            $last = $last_result->fetch_assoc();
            $next_id = $last['id'] + 1;
        }
        $no_tiket = "PM-$tahun-" . str_pad($next_id, 5, '0', STR_PAD_LEFT);

        // Handle upload foto (opsional)
        $foto_nama = null;
        if (!empty($_FILES['foto']['name'])) {
            $foto = $_FILES['foto'];
            $ekstensi = strtolower(pathinfo($foto['name'], PATHINFO_EXTENSION));
            $ukuran = $foto['size'];
            $nama_tmp = $foto['tmp_name'];

            // Validasi: ekstensi & ukuran
            if (!in_array($ekstensi, ['jpg', 'jpeg', 'png'])) {
                $error = "Foto hanya boleh format JPG/PNG.";
            } elseif ($ukuran > 2 * 1024 * 1024) { // 2MB
                $error = "Ukuran foto maksimal 2 MB.";
            } else {
                // Generate nama unik: timestamp + random
                $foto_nama = uniqid('pengaduan_') . '.' . $ekstensi;
                $target = '../../uploads/' . $foto_nama;

                if (!move_uploaded_file($nama_tmp, $target)) {
                    $error = "Gagal mengupload foto. Pastikan folder uploads/ ada dan writable.";
                }
            }
        }

        // Jika tidak ada error → simpan ke database
        if (empty($error)) {
            $stmt = $koneksi->prepare("
                INSERT INTO pengaduan 
                (user_id, fasilitas_id, no_tiket, judul, deskripsi, foto, status, created_at) 
                VALUES (?, ?, ?, ?, ?, ?, 'menunggu', NOW())
            ");
            $stmt->bind_param("iissss", $user_id, $fasilitas_id, $no_tiket, $judul, $deskripsi, $foto_nama);
            
            if ($stmt->execute()) {
                $_SESSION['swal_success'] = "Pengaduan berhasil dibuat! Nomor tiket: $no_tiket";
                header("Location: riwayat.php");
                exit();
            } else {
                $error = "Gagal menyimpan pengaduan. Silakan coba lagi.";
            }
            $stmt->close();
        }
    }
}

// Ambil daftar fasilitas untuk dropdown
$fasilitas_result = $koneksi->query("SELECT id, nama, lokasi FROM fasilitas ORDER BY nama");
?>

<?php include '../../includes/header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="fas fa-plus-circle me-2"></i> Buat Pengaduan Baru</h2>
    <a href="index.php" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left me-1"></i> Kembali
    </a>
</div>

<?php if ($error): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fas fa-exclamation-circle me-2"></i>
        <?= htmlspecialchars($error) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<div class="card">
    <div class="card-body">
        <form method="POST" enctype="multipart/form-data" id="form-pengaduan">
            <!-- Pilih Fasilitas -->
            <div class="mb-3">
                <label class="form-label"><i class="fas fa-building me-2"></i> Fasilitas *</label>
                <select class="form-select" name="fasilitas_id" required>
                    <option value="">-- Pilih Fasilitas --</option>
                    <?php while ($fas = $fasilitas_result->fetch_assoc()): ?>
                        <option value="<?= $fas['id'] ?>">
                            <?= htmlspecialchars($fas['nama']) ?> 
                            <?= $fas['lokasi'] ? " — " . htmlspecialchars($fas['lokasi']) : '' ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>

            <!-- Judul -->
            <div class="mb-3">
                <label class="form-label"><i class="fas fa-heading me-2"></i> Judul Pengaduan *</label>
                <input type="text" 
                       class="form-control" 
                       name="judul" 
                       placeholder="Contoh: AC ruang 201 mati" 
                       value="<?= htmlspecialchars($_POST['judul'] ?? '') ?>" 
                       required>
            </div>

            <!-- Deskripsi -->
            <div class="mb-3">
                <label class="form-label"><i class="fas fa-align-left me-2"></i> Deskripsi *</label>
                <textarea class="form-control" 
                          name="deskripsi" 
                          rows="4" 
                          placeholder="Jelaskan lokasi, kondisi, dan detail lainnya..." 
                          required><?= htmlspecialchars($_POST['deskripsi'] ?? '') ?></textarea>
            </div>

            <!-- Upload Foto -->
            <div class="mb-4">
                <label class="form-label">
                    <i class="fas fa-image me-2"></i> Foto (Opsional)
                    <small class="text-muted">(Max 2MB, JPG/PNG)</small>
                </label>
                <input class="form-control" type="file" name="foto" accept="image/jpeg,image/png">
                <div class="form-text">
                    <i class="fas fa-info-circle me-1"></i>
                    Foto membantu petugas memahami kondisi sebenarnya.
                </div>

                <!-- Preview Foto (akan diisi via JS) -->
                <div id="preview-container" class="mt-2" style="display:none;">
                    <img id="foto-preview" src="#" alt="Preview" class="img-thumbnail" style="max-height:200px;">
                </div>
            </div>

            <!-- Submit -->
            <div class="d-grid">
                <button type="submit" class="btn btn-success btn-lg w-100 mb-3">
                    <i class="fas fa-paper-plane me-2"></i> Kirim Pengaduan
                </button>
            </div>

            <div class="mt-3 text-muted">
                <i class="fas fa-info-circle me-1"></i>
                Setelah dikirim, Anda akan mendapat <strong>nomor tiket</strong> untuk melacak status.
            </div>
        </form>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>