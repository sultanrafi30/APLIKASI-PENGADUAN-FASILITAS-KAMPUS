<?php
// dashboard/petugas/index.php
require_once '../../config/database.php';
require_once '../../includes/auth_check.php';
auth_check('petugas'); // hanya petugas boleh akses
?>

<?php include '../../includes/header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="fas fa-user-cog me-2"></i> Dashboard Petugas</h2>
    <span class="badge bg-info">
        <i class="fas fa-user me-1"></i> <?= htmlspecialchars($_SESSION['nama']) ?>
    </span>
</div>

<!-- 📊 Statistik Ringkas -->
<div class="row g-4 mb-4">
    <?php
    // Jumlah menunggu
    $result = $koneksi->query("SELECT COUNT(*) as total FROM pengaduan WHERE status = 'menunggu'");
    $menunggu = $result->fetch_assoc()['total'];
    
    // Jumlah diproses
    $result = $koneksi->query("SELECT COUNT(*) as total FROM pengaduan WHERE status = 'diproses'");
    $diproses = $result->fetch_assoc()['total'];
    
    // Rata-rata usia pengaduan menunggu (dalam jam)
    $result = $koneksi->query("
        SELECT AVG(TIMESTAMPDIFF(HOUR, created_at, NOW())) as avg_hours
        FROM pengaduan 
        WHERE status = 'menunggu'
    ");
    $avg_hours = $result->fetch_assoc()['avg_hours'] ?? 0;
    $avg_hours = round($avg_hours, 1);
    ?>

    <div class="col-md-4">
        <div class="card text-bg-warning border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <small class="text-white-50">MENUNGGU</small>
                        <h3 class="mb-0"><?= $menunggu ?></h3>
                        <small class="text-white-75">rata-rata: <?= $avg_hours ?> jam</small>
                    </div>
                    <i class="fas fa-hourglass-half fa-2x opacity-75"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card text-bg-info border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <small class="text-white-50">DIPROSES</small>
                        <h3 class="mb-0"><?= $diproses ?></h3>
                    </div>
                    <i class="fas fa-tools fa-2x opacity-75"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card text-bg-success border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <small class="text-white-50">TOTAL DITANGANI</small>
                        <h3 class="mb-0"><?= $menunggu + $diproses ?></h3>
                    </div>
                    <i class="fas fa-tasks fa-2x opacity-75"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- 📋 Aksi Cepat -->
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
                </div>
            </div>
        </div>
    </div>

    <!-- 🕒 Pengaduan Menunggu Terlama -->
    <div class="col-md-6">
        <div class="card">
            <div class="card-header bg-warning text-dark">
                <i class="fas fa-clock me-2"></i> Menunggu Terlama (>24 jam)
            </div>
            <div class="card-body">
                <?php
                $lama_query = "
                    SELECT id, no_tiket, judul, 
                           TIMESTAMPDIFF(HOUR, created_at, NOW()) as hours_old,
                           created_at
                    FROM pengaduan 
                    WHERE status = 'menunggu' 
                      AND created_at < NOW() - INTERVAL 24 HOUR
                    ORDER BY created_at ASC
                    LIMIT 3
                ";
                $lama_result = $koneksi->query($lama_query);
                ?>
                <?php if ($lama_result->num_rows > 0): ?>
                    <ul class="list-group list-group-flush">
                        <?php while ($row = $lama_result->fetch_assoc()): ?>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <div>
                                    <strong><?= htmlspecialchars($row['no_tiket']) ?></strong><br>
                                    <small><?= htmlspecialchars($row['judul']) ?></small>
                                </div>
                                <span class="badge bg-danger"><?= $row['hours_old'] ?> jam</span>
                            </li>
                        <?php endwhile; ?>
                    </ul>
                <?php else: ?>
                    <div class="text-center text-muted py-2">
                        <i class="fas fa-check-circle text-success me-2"></i>
                        Tidak ada pengaduan tertunda >24 jam
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- 📋 Pengaduan Sedang Diproses -->
<div class="card">
    <div class="card-header bg-info text-white">
        <i class="fas fa-tools me-2"></i> Pengaduan Sedang Diproses
    </div>
    <div class="card-body">
        <?php
        $proses_query = "
            SELECT p.id, p.no_tiket, p.judul, p.created_at, 
                   f.nama as fasilitas, u.nama as pelapor
            FROM pengaduan p
            JOIN fasilitas f ON p.fasilitas_id = f.id
            JOIN users u ON p.user_id = u.id
            WHERE p.status = 'diproses'
            ORDER BY p.updated_at DESC
        ";
        $proses_result = $koneksi->query($proses_query);
        ?>
        <?php if ($proses_result->num_rows > 0): ?>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>No. Tiket</th>
                            <th>Judul</th>
                            <th>Fasilitas</th>
                            <th>Pelapor</th>
                            <th>Tgl Lapor</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $proses_result->fetch_assoc()): ?>
                            <tr>
                                <td><?= htmlspecialchars($row['no_tiket']) ?></td>
                                <td><?= htmlspecialchars($row['judul']) ?></td>
                                <td><?= htmlspecialchars($row['fasilitas']) ?></td>
                                <td><?= htmlspecialchars($row['pelapor']) ?></td>
                                <td><?= date('d M', strtotime($row['created_at'])) ?></td>
                                <td>
                                    <a href="pengaduan.php?id=<?= $row['id'] ?>" 
                                       class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-edit"></i> Update
                                    </a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="text-center text-muted py-3">
                <i class="fas fa-check-circle text-success me-2"></i>
                Tidak ada pengaduan sedang diproses.
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>