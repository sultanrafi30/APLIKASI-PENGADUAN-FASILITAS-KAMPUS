<?php
// dashboard/admin/index.php
require_once '../../config/database.php';
require_once '../../includes/auth_check.php';
auth_check('admin'); // hanya admin boleh akses
?>

<?php include '../../includes/header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="fas fa-tachometer-alt me-2"></i> Dashboard Admin</h2>
    <span class="badge bg-success">
        <i class="fas fa-user me-1"></i> <?= htmlspecialchars($_SESSION['nama']) ?>
    </span>
</div>

<!-- 📊 Statistik Cards -->
<div class="row g-4 mb-4">
    <?php
    // Query statistik
    $stats = [];
    
    // Total pengaduan
    $result = $koneksi->query("SELECT COUNT(*) as total FROM pengaduan");
    $stats['total'] = $result->fetch_assoc()['total'];
    
    // Jumlah selesai
    $result = $koneksi->query("SELECT COUNT(*) as selesai FROM pengaduan WHERE status = 'selesai'");
    $stats['selesai'] = $result->fetch_assoc()['selesai'];
    
    // Hitung persentase
    $stats['persen_selesai'] = $stats['total'] > 0 ? round(($stats['selesai'] / $stats['total']) * 100, 1) : 0;
    
    // Rata-rata waktu penyelesaian (dalam hari)
    $result = $koneksi->query("
        SELECT AVG(DATEDIFF(updated_at, created_at)) as avg_days 
        FROM pengaduan 
        WHERE status = 'selesai'
    ");
    $avg = $result->fetch_assoc()['avg_days'];
    $stats['rata_waktu'] = $avg ? round($avg, 1) : 0;
    ?>

    <!-- Card 1: Total Pengaduan -->
    <div class="col-md-3">
        <div class="card text-bg-primary border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <small class="text-white-50">TOTAL PENGADUAN</small>
                        <h3 class="mb-0"><?= $stats['total'] ?></h3>
                    </div>
                    <i class="fas fa-exclamation-triangle fa-2x opacity-75"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Card 2: Selesai -->
    <div class="col-md-3">
        <div class="card text-bg-success border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <small class="text-white-50">SELESAI</small>
                        <h3 class="mb-0"><?= $stats['selesai'] ?></h3>
                        <small class="text-white-75"><?= $stats['persen_selesai'] ?>%</small>
                    </div>
                    <i class="fas fa-check-circle fa-2x opacity-75"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Card 3: Rata-rata Waktu -->
    <div class="col-md-3">
        <div class="card text-bg-info border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <small class="text-white-50">RATA-RATA PENYELESAIAN</small>
                        <h3 class="mb-0"><?= $stats['rata_waktu'] ?></h3>
                        <small class="text-white-75">hari</small>
                    </div>
                    <i class="fas fa-clock fa-2x opacity-75"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Card 4: Menunggu -->
    <div class="col-md-3">
        <?php
        $result = $koneksi->query("SELECT COUNT(*) as menunggu FROM pengaduan WHERE status = 'menunggu'");
        $menunggu = $result->fetch_assoc()['menunggu'];
        ?>
        <div class="card text-bg-warning border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <small class="text-white-50">MENUNGGU</small>
                        <h3 class="mb-0"><?= $menunggu ?></h3>
                    </div>
                    <i class="fas fa-hourglass-half fa-2x opacity-75"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- 📋 Shortcut Aksi Cepat -->
<div class="row mb-4">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <i class="fas fa-bolt me-2"></i> Aksi Cepat
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="pengaduan.php" class="btn btn-outline-primary">
                        <i class="fas fa-exclamation-triangle me-2"></i> Kelola Pengaduan
                    </a>
                    <a href="fasilitas.php" class="btn btn-outline-success">
                        <i class="fas fa-building me-2"></i> Kelola Fasilitas
                    </a>
                    <a href="user.php" class="btn btn-outline-info">
                        <i class="fas fa-users me-2"></i> Kelola User
                    </a>
                    <a href="laporan.php" class="btn btn-outline-secondary">
                        <i class="fas fa-file-alt me-2"></i> Rekap Laporan
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- 📉 Top 5 Fasilitas Bermasalah -->
    <div class="col-md-6">
        <div class="card">
            <div class="card-header bg-danger text-white">
                <i class="fas fa-chart-bar me-2"></i> Top 5 Fasilitas Bermasalah
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Fasilitas</th>
                                <th class="text-end">Jumlah</th>
                                <th class="text-end">%</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $top_query = "
                                SELECT f.nama, COUNT(p.id) as total
                                FROM pengaduan p
                                JOIN fasilitas f ON p.fasilitas_id = f.id
                                GROUP BY f.id
                                ORDER BY total DESC
                                LIMIT 5
                            ";
                            $top_result = $koneksi->query($top_query);
                            $no = 1;
                            while ($row = $top_result->fetch_assoc()):
                                $persen = $stats['total'] > 0 ? round(($row['total'] / $stats['total']) * 100, 1) : 0;
                            ?>
                            <tr>
                                <td><?= $no++ ?>.</td>
                                <td><?= htmlspecialchars($row['nama']) ?></td>
                                <td class="text-end"><?= $row['total'] ?></td>
                                <td class="text-end"><?= $persen ?>%</td>
                            </tr>
                            <?php endwhile; ?>
                            <?php if ($top_result->num_rows === 0): ?>
                                <tr>
                                    <td colspan="4" class="text-center text-muted">Belum ada data</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- 📈 Grafik Interaktif (Chart.js) -->
<div class="row mb-4">
    <!-- Grafik Pengaduan per Bulan -->
    <div class="col-md-8">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <i class="fas fa-chart-line me-2"></i> Pengaduan per Bulan (6 Bulan Terakhir)
            </div>
            <div class="card-body">
                <div style="height:300px;">
                    <canvas id="pengaduanChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Grafik Status -->
    <div class="col-md-4">
        <div class="card">
            <div class="card-header bg-success text-white">
                <i class="fas fa-chart-pie me-2"></i> Distribusi Status
            </div>
            <div class="card-body">
                <div style="height:300px;">
                    <canvas id="statusChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
// 🔹 Query Data untuk Grafik
// 1. Pengaduan per bulan (6 bulan terakhir)
$bulan_labels = [];
$bulan_data = [];
for ($i = 5; $i >= 0; $i--) {
    $bulan = date('Y-m', strtotime("-$i months"));
    $label = date('M Y', strtotime("-$i months"));
    $bulan_labels[] = $label;
    
    $result = $koneksi->query("
        SELECT COUNT(*) as total 
        FROM pengaduan 
        WHERE DATE_FORMAT(created_at, '%Y-%m') = '$bulan'
    ");
    $bulan_data[] = $result->fetch_assoc()['total'];
}

// 2. Distribusi status
$status_labels = ['menunggu', 'diproses', 'selesai', 'ditolak'];
$status_data = [];
$status_colors = ["#ffc107", "#17a2b8", "#28a745", "#dc3545"];
foreach ($status_labels as $s) {
    $result = $koneksi->query("SELECT COUNT(*) as total FROM pengaduan WHERE status = '$s'");
    $status_data[] = $result->fetch_assoc()['total'];
}
?>

<!-- 📅 Pengaduan Terbaru -->
<div class="card">
    <div class="card-header bg-secondary text-white">
        <i class="fas fa-history me-2"></i> 5 Pengaduan Terbaru
    </div>
    <div class="card-body">
        <?php
        $recent_query = "
            SELECT p.id, p.no_tiket, p.judul, p.status, p.created_at, 
                   u.nama as pelapor, f.nama as fasilitas
            FROM pengaduan p
            JOIN users u ON p.user_id = u.id
            JOIN fasilitas f ON p.fasilitas_id = f.id
            ORDER BY p.created_at DESC
            LIMIT 5
        ";
        $recent_result = $koneksi->query($recent_query);
        ?>
        <div class="list-group">
            <?php while ($row = $recent_result->fetch_assoc()): ?>
                <a href="pengaduan.php" class="list-group-item list-group-item-action">
                    <div class="d-flex justify-content-between">
                        <strong><?= htmlspecialchars($row['no_tiket']) ?></strong>
                        <small class="text-muted"><?= date('d M Y H:i', strtotime($row['created_at'])) ?></small>
                    </div>
                    <div class="mb-1">
                        <span class="badge bg-<?= 
                            $row['status'] == 'selesai' ? 'success' : 
                            ($row['status'] == 'diproses' ? 'info' : 
                            ($row['status'] == 'ditolak' ? 'danger' : 'warning')) 
                        ?>"><?= ucfirst($row['status']) ?></span>
                        <?= htmlspecialchars($row['fasilitas']) ?>
                    </div>
                    <div><?= htmlspecialchars($row['judul']) ?></div>
                    <small class="text-muted">Oleh: <?= htmlspecialchars($row['pelapor']) ?></small>
                </a>
            <?php endwhile; ?>
            <?php if ($recent_result->num_rows === 0): ?>
                <div class="text-center text-muted py-3">Belum ada pengaduan.</div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>