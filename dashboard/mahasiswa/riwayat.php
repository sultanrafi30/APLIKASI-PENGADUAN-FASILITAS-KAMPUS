<?php
// mahasiswa/riwayat.php
require_once '../../config/database.php';
require_once '../../includes/auth_check.php';
auth_check('mahasiswa');
$user_id = $_SESSION['user_id'];

// Filter status (opsional)
$status_filter = $_GET['status'] ?? 'semua';
$valid_status = ['semua', 'menunggu', 'diproses', 'selesai', 'ditolak'];
if (!in_array($status_filter, $valid_status)) {
    $status_filter = 'semua';
}

// Query dasar — tambahkan nama pelapor (untuk kebutuhan timeline)
$sql = "
    SELECT p.*, f.nama as fasilitas, u.nama as nama_pelapor
    FROM pengaduan p
    JOIN fasilitas f ON p.fasilitas_id = f.id
    JOIN users u ON p.user_id = u.id
    WHERE p.user_id = ?
";

// Tambahkan filter jika bukan 'semua'
if ($status_filter !== 'semua') {
    $sql .= " AND p.status = ?";
}

$sql .= " ORDER BY p.created_at DESC";

// Eksekusi query
if ($status_filter === 'semua') {
    $stmt = $koneksi->prepare($sql);
    $stmt->bind_param("i", $user_id);
} else {
    $stmt = $koneksi->prepare($sql);
    $stmt->bind_param("is", $user_id, $status_filter);
}

$stmt->execute();
$result = $stmt->get_result();
?>

<?php include '../../includes/header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="fas fa-history me-2"></i> Riwayat Pengaduan Saya</h2>
    <a href="index.php" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left me-1"></i> Dashboard
    </a>
</div>

<!-- 🔍 Filter Status -->
<div class="card mb-4">
    <div class="card-body">
        <h5><i class="fas fa-filter me-2"></i> Filter Status</h5>
        <div class="btn-group" role="group">
            <?php foreach ($valid_status as $status): 
                $label = $status == 'semua' ? 'Semua' : ucfirst($status);
                $color = $status == $status_filter ? 'primary' : 'outline-secondary';
                $url = $status == 'semua' ? 'riwayat.php' : "riwayat.php?status=$status";
            ?>
                <a href="<?= $url ?>" class="btn btn-<?= $color ?> btn-sm">
                    <?= $label ?>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<!-- 📋 Tabel Pengaduan -->
<?php if ($result->num_rows > 0): ?>
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>No. Tiket</th>
                            <th>Judul</th>
                            <th>Fasilitas</th>
                            <th>Tanggal</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td><code><?= htmlspecialchars($row['no_tiket']) ?></code></td>
                                <td><?= htmlspecialchars($row['judul']) ?></td>
                                <td><?= htmlspecialchars($row['fasilitas']) ?></td>
                                <td><?= date('d M Y', strtotime($row['created_at'])) ?></td>
                                <td>
                                    <span class="badge bg-<?= 
                                        $row['status'] == 'selesai' ? 'success' : 
                                        ($row['status'] == 'diproses' ? 'info' : 
                                        ($row['status'] == 'ditolak' ? 'danger' : 'warning')) 
                                    ?>">
                                        <?= ucfirst($row['status']) ?>
                                    </span>
                                </td>
                                <td>
                                    <button type="button" 
                                            class="btn btn-sm btn-outline-primary"
                                            data-bs-toggle="modal" 
                                            data-bs-target="#detailModal<?= $row['id'] ?>">
                                        <i class="fas fa-eye"></i> Detail
                                    </button>
                                </td>
                            </tr>

                            <!-- Modal Detail (LENGKAP) -->
                            <div class="modal fade" id="detailModal<?= $row['id'] ?>" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog modal-xl">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">
                                                <i class="fas fa-info-circle me-2"></i>
                                                Detail Pengaduan: <?= htmlspecialchars($row['no_tiket']) ?>
                                            </h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">
                                            <!-- Data pengaduan untuk partial -->
                                            <?php 
                                            // Siapkan data untuk partial
                                            $pengaduan = $row;
                                            // Include partial detail
                                            include '../../includes/detail_pengaduan.php';
                                            ?>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                                Tutup
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
<?php else: ?>
    <div class="card">
        <div class="card-body text-center py-5">
            <i class="fas fa-info-circle fa-3x text-muted mb-3"></i>
            <h4>Tidak ada pengaduan.</h4>
            <p class="text-muted">Anda belum membuat laporan kerusakan fasilitas.</p>
            <a href="buat_pengaduan.php" class="btn btn-primary">
                <i class="fas fa-plus-circle me-1"></i> Buat Pengaduan Baru
            </a>
        </div>
    </div>
<?php endif; ?>

<?php include '../../includes/footer.php'; ?>