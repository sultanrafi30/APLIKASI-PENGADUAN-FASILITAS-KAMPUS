<?php
// dashboard/admin/fasilitas.php
require_once '../../config/database.php';
require_once '../../includes/auth_check.php';
auth_check('admin');

$success = '';
$error = '';

// 📥 Proses form (tambah/edit)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $aksi = $_POST['aksi'] ?? '';
    $id = intval($_POST['id'] ?? 0);
    $nama = trim($_POST['nama'] ?? '');
    $lokasi = trim($_POST['lokasi'] ?? '');
    $deskripsi = trim($_POST['deskripsi'] ?? '');

    if (empty($nama)) {
        $error = "Nama fasilitas wajib diisi.";
    } else {
        if ($aksi === 'tambah') {
            // Tambah baru
            $stmt = $koneksi->prepare("INSERT INTO fasilitas (nama, lokasi, deskripsi) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $nama, $lokasi, $deskripsi);
            if ($stmt->execute()) {
                $_SESSION['swal_success'] = "Fasilitas <strong>$nama</strong> berhasil ditambahkan.";
            } else {
                $error = "Gagal menambah fasilitas.";
            }
        } elseif ($aksi === 'edit' && $id > 0) {
            // Edit
            $stmt = $koneksi->prepare("UPDATE fasilitas SET nama = ?, lokasi = ?, deskripsi = ? WHERE id = ?");
            $stmt->bind_param("sssi", $nama, $lokasi, $deskripsi, $id);
            if ($stmt->execute()) {
                $_SESSION['swal_success'] = "Fasilitas <strong>$nama</strong> berhasil diperbarui.";
            } else {
                $error = "Gagal memperbarui fasilitas.";
            }
        }
        $stmt->close();
        header("Location: fasilitas.php");
        exit();
    }
}

// 🗑️ Proses hapus
if (isset($_GET['hapus'])) {
    $id = intval($_GET['hapus']);
    // Cek dulu: apakah ada pengaduan yang terkait?
    $cek = $koneksi->query("SELECT COUNT(*) as total FROM pengaduan WHERE fasilitas_id = $id");
    $terpakai = $cek->fetch_assoc()['total'] > 0;

    if ($terpakai) {
        $_SESSION['error'] = "Gagal menghapus: fasilitas ini masih digunakan dalam pengaduan.";
    } else {
        $stmt = $koneksi->prepare("DELETE FROM fasilitas WHERE id = ?");
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            $_SESSION['swal_success'] = "Fasilitas berhasil dihapus.";
        } else {
            $_SESSION['error'] = "Gagal menghapus fasilitas.";
        }
        $stmt->close();
    }
    header("Location: fasilitas.php");
    exit();
}

// 📋 Ambil data untuk edit (jika ada)
$edit_data = null;
if (isset($_GET['edit'])) {
    $id = intval($_GET['edit']);
    $result = $koneksi->query("SELECT * FROM fasilitas WHERE id = $id");
    $edit_data = $result->fetch_assoc();
}

// 📋 Daftar semua fasilitas
$fasilitas_result = $koneksi->query("SELECT * FROM fasilitas ORDER BY nama");
?>

<?php include '../../includes/header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="fas fa-building me-2"></i> Kelola Fasilitas</h2>
    <a href="index.php" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left me-1"></i> Dashboard
    </a>
</div>

<!-- ✏️ Form Tambah/Edit -->
<div class="card mb-4">
    <div class="card-header bg-<?= $edit_data ? 'warning' : 'success' ?> text-white">
        <i class="fas fa-<?= $edit_data ? 'edit' : 'plus' ?> me-2"></i>
        <?= $edit_data ? 'Edit Fasilitas' : 'Tambah Fasilitas Baru' ?>
    </div>
    <div class="card-body">
        <form method="POST">
            <input type="hidden" name="aksi" value="<?= $edit_data ? 'edit' : 'tambah' ?>">
            <?php if ($edit_data): ?>
                <input type="hidden" name="id" value="<?= $edit_data['id'] ?>">
            <?php endif; ?>

            <div class="mb-3">
                <label class="form-label">Nama Fasilitas *</label>
                <input type="text" 
                       class="form-control" 
                       name="nama" 
                       value="<?= htmlspecialchars($edit_data['nama'] ?? '') ?>" 
                       placeholder="Contoh: Lab Komputer A, Toilet Gedung Utama" 
                       required>
            </div>

            <div class="mb-3">
                <label class="form-label">Lokasi (Opsional)</label>
                <input type="text" 
                       class="form-control" 
                       name="lokasi" 
                       value="<?= htmlspecialchars($edit_data['lokasi'] ?? '') ?>" 
                       placeholder="Contoh: Gedung B Lt.2">
            </div>

            <div class="mb-3">
                <label class="form-label">Deskripsi (Opsional)</label>
                <textarea class="form-control" 
                          name="deskripsi" 
                          rows="3" 
                          placeholder="Contoh: Digunakan untuk praktikum jaringan."><?= htmlspecialchars($edit_data['deskripsi'] ?? '') ?></textarea>
            </div>

            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                <?php if ($edit_data): ?>
                    <a href="fasilitas.php" class="btn btn-secondary">
                        <i class="fas fa-times me-1"></i> Batal Edit
                    </a>
                <?php endif; ?>
                <button type="submit" class="btn btn-<?= $edit_data ? 'warning' : 'success' ?> w-100 mb-2">
                    <i class="fas fa-save me-1"></i>
                    <?= $edit_data ? 'Simpan Perubahan' : 'Tambah Fasilitas' ?>
                </button>
            </div>
        </form>
    </div>
</div>

<!-- 📋 Daftar Fasilitas -->
<?php if ($fasilitas_result->num_rows > 0): ?>
    <div class="card">
        <div class="card-header bg-primary text-white">
            <i class="fas fa-list me-2"></i> Daftar Fasilitas (<?= $fasilitas_result->num_rows ?>)
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Nama</th>
                            <th>Lokasi</th>
                            <th>Deskripsi</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $no = 1; while ($f = $fasilitas_result->fetch_assoc()): ?>
                            <tr>
                                <td><?= $no++ ?></td>
                                <td><strong><?= htmlspecialchars($f['nama']) ?></strong></td>
                                <td><?= htmlspecialchars($f['lokasi']) ?: '—' ?></td>
                                <td>
                                    <?php 
                                    $desc = htmlspecialchars($f['deskripsi']);
                                    echo strlen($desc) > 50 ? substr($desc, 0, 50) . '...' : $desc;
                                    ?>
                                </td>
                                <td>
                                    <a href="?edit=<?= $f['id'] ?>" 
                                       class="btn btn-sm btn-outline-warning"
                                       title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="?hapus=<?= $f['id'] ?>" 
                                       class="btn btn-sm btn-outline-danger"
                                       title="Hapus"
                                       onclick="return confirm('Yakin hapus fasilitas: <?= addslashes($f['nama']) ?>?')">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
<?php else: ?>
    <div class="card">
        <div class="card-body text-center py-5">
            <i class="fas fa-building fa-3x text-muted mb-3"></i>
            <h4>Belum ada fasilitas.</h4>
            <p class="text-muted">Tambahkan fasilitas pertama Anda.</p>
        </div>
    </div>
<?php endif; ?>

<?php include '../../includes/footer.php'; ?>