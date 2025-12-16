<?php
// dashboard/mahasiswa/index.php
require_once '../../config/database.php';
require_once '../../includes/auth_check.php';
auth_check('mahasiswa'); // hanya mahasiswa boleh akses
$user_id = $_SESSION['user_id'];
?>

<?php include '../../includes/header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="fas fa-user-graduate me-2"></i> Dashboard Mahasiswa</h2>
    <span class="badge bg-primary">
        <i class="fas fa-user me-1"></i> <?= htmlspecialchars($_SESSION['nama']) ?>
    </span>
</div>

<!-- 📝 Shortcut Buat Pengaduan -->
<div class="row mb-4">
    <div class="col-md-6">
        <div class="card border-primary">
            <div class="card-body text-center">
                <h5 class="card-title">
                    <i class="fas fa-plus-circle text-primary me-2"></i>
                    Laporkan Kerusakan Fasilitas
                </h5>
                <p class="text-muted mb-3">
                    Temukan kerusakan di kampus? Laporkan sekarang!
                </p>
                <a href="buat_pengaduan.php" class="btn btn-primary btn-lg">
                    <i class="fas fa-exclamation-triangle me-2"></i> Buat Pengaduan
                </a>
            </div>
        </div>
    </div>

    <!-- 📊 Ringkasan Pengaduan Pribadi -->
    <div class="col-md-6">
        <?php
        // Hitung pengaduan pribadi
        $stats = [];
        $result = $koneksi->query("SELECT COUNT(*) as total FROM pengaduan WHERE user_id = $user_id");
        $stats['total'] = $result->fetch_assoc()['total'];
        
        $result = $koneksi->query("SELECT COUNT(*) as selesai FROM pengaduan WHERE user_id = $user_id AND status = 'selesai'");
        $stats['selesai'] = $result->fetch_assoc()['selesai'];
        
        $status_labels = [
            'menunggu' => 'Menunggu',
            'diproses' => 'Diproses',
            'selesai' => 'Selesai',
            'ditolak' => 'Ditolak'
        ];
        ?>
        <div class="card">
            <div class="card-header bg-success text-white">
                <i class="fas fa-chart-pie me-2"></i> Ringkasan Pengaduan Anda
            </div>
            <div class="card-body">
                <p><strong>Total:</strong> <?= $stats['total'] ?> pengaduan</p>
                <div class="row text-center">
                    <?php foreach (['menunggu', 'diproses', 'selesai', 'ditolak'] as $status): ?>
                        <?php
                        $result = $koneksi->query("SELECT COUNT(*) as total FROM pengaduan WHERE user_id = $user_id AND status = '$status'");
                        $count = $result->fetch_assoc()['total'];
                        $label = $status_labels[$status];
                        $color = $status == 'selesai' ? 'success' : ($status == 'ditolak' ? 'danger' : 'warning');
                        ?>
                        <div class="col-6 mb-2">
                            <div class="badge bg-<?= $color ?>"><?= $label ?></div><br>
                            <strong><?= $count ?></strong>
                        </div>
                    <?php endforeach; ?>
                </div>
                <div class="mt-3 text-center">
                    <a href="riwayat.php" class="btn btn-outline-secondary btn-sm">
                        <i class="fas fa-history me-1"></i> Lihat Semua Riwayat
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- 📜 3 Pengaduan Terakhir -->
<div class="card">
    <div class="card-header bg-secondary text-white">
        <i class="fas fa-history me-2"></i> 3 Pengaduan Terakhir Anda
    </div>
    <div class="card-body">
        <?php
        $recent_query = "
            SELECT id, no_tiket, judul, status, created_at, fasilitas_id
            FROM pengaduan 
            WHERE user_id = $user_id
            ORDER BY created_at DESC
            LIMIT 3
        ";
        $recent_result = $koneksi->query($recent_query);
        ?>
        <?php if ($recent_result->num_rows > 0): ?>
            <div class="list-group">
                <?php while ($row = $recent_result->fetch_assoc()): ?>
                    <?php
                    // Ambil nama fasilitas
                    $fas_query = "SELECT nama FROM fasilitas WHERE id = " . $row['fasilitas_id'];
                    $fas_result = $koneksi->query($fas_query);
                    $fasilitas = $fas_result->num_rows ? $fas_result->fetch_assoc()['nama'] : '—';
                    ?>
                    <div class="list-group-item">
                        <div class="d-flex justify-content-between">
                            <strong><?= htmlspecialchars($row['no_tiket']) ?></strong>
                            <span class="badge bg-<?= 
                                $row['status'] == 'selesai' ? 'success' : 
                                ($row['status'] == 'diproses' ? 'info' : 
                                ($row['status'] == 'ditolak' ? 'danger' : 'warning')) 
                            ?>"><?= ucfirst($row['status']) ?></span>
                        </div>
                        <div><?= htmlspecialchars($row['judul']) ?></div>
                        <small class="text-muted">
                            <i class="fas fa-building me-1"></i> <?= htmlspecialchars($fasilitas) ?> |
                            <i class="fas fa-clock me-1"></i> <?= date('d M Y', strtotime($row['created_at'])) ?>
                        </small>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <div class="text-center text-muted py-3">
                <i class="fas fa-info-circle me-2"></i>
                Anda belum membuat pengaduan.
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>