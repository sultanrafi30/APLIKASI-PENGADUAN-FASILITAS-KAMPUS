<?php
// dashboard/admin/laporan.php
require_once '../../config/database.php';
require_once '../../includes/auth_check.php';
auth_check('admin');

// 📤 Ekspor ke Excel (jika diminta)
if (isset($_GET['export']) && $_GET['export'] === 'excel') {
    // Query untuk ekspor — sama seperti tampilan, tapi tanpa limit
    $sql = "
        SELECT p.no_tiket, p.judul, p.status, p.created_at, p.updated_at,
               f.nama as fasilitas, u.nama as pelapor, pt.nama as petugas
        FROM pengaduan p
        JOIN users u ON p.user_id = u.id
        JOIN fasilitas f ON p.fasilitas_id = f.id
        LEFT JOIN (
            SELECT rp.pengaduan_id, us.nama 
            FROM riwayat_pengaduan rp
            JOIN users us ON rp.updated_by = us.id
            WHERE rp.status_baru IN ('selesai', 'ditolak')
            GROUP BY rp.pengaduan_id
            ORDER BY rp.created_at DESC
        ) pt ON p.id = pt.pengaduan_id
        WHERE 1=1
    ";

    $params = [];
    $types = "";

    // Gunakan filter yang sama seperti tampilan
    $tgl_awal = $_GET['tgl_awal'] ?? '';
    $tgl_akhir = $_GET['tgl_akhir'] ?? '';
    $status = $_GET['status'] ?? 'semua';
    $fasilitas_id = $_GET['fasilitas'] ?? '';

    if ($status !== 'semua') {
        $sql .= " AND p.status = ?";
        $params[] = $status;
        $types .= "s";
    }
    if ($fasilitas_id) {
        $sql .= " AND f.id = ?";
        $params[] = $fasilitas_id;
        $types .= "i";
    }
    if ($tgl_awal) {
        $sql .= " AND DATE(p.created_at) >= ?";
        $params[] = $tgl_awal;
        $types .= "s";
    }
    if ($tgl_akhir) {
        $sql .= " AND DATE(p.created_at) <= ?";
        $params[] = $tgl_akhir;
        $types .= "s";
    }

    $sql .= " ORDER BY p.created_at DESC";

    // Eksekusi
    $stmt = $koneksi->prepare($sql);
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $result = $stmt->get_result();

    // 📥 Set header untuk download CSV
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="laporan_pengaduan_' . date('Y-m-d') . '.csv"');

    $output = fopen('php://output', 'w');
    
    // Header kolom
    fputcsv($output, [
        'No', 'No. Tiket', 'Fasilitas', 'Judul Pengaduan', 
        'Pelapor', 'Status', 'Tgl Lapor', 'Tgl Selesai/Ditolak', 
        'Petugas Penangan', 'Durasi (Hari)'
    ]);

    $no = 1;
    while ($row = $result->fetch_assoc()) {
        $durasi = '-';
        if ($row['status'] === 'selesai' || $row['status'] === 'ditolak') {
            $start = new DateTime($row['created_at']);
            $end = new DateTime($row['updated_at']);
            $durasi = $start->diff($end)->days;
        }

        fputcsv($output, [
            $no++,
            $row['no_tiket'],
            $row['fasilitas'],
            $row['judul'],
            $row['pelapor'],
            ucfirst($row['status']),
            date('d-m-Y H:i', strtotime($row['created_at'])),
            $row['updated_at'] ? date('d-m-Y H:i', strtotime($row['updated_at'])) : '-',
            $row['petugas'] ?: '-',
            $durasi
        ]);
    }

    fclose($output);
    exit();
}

// 🔍 Filter dari form
$tgl_awal = $_GET['tgl_awal'] ?? date('Y-m-d', strtotime('-30 days')); // default 30 hari terakhir
$tgl_akhir = $_GET['tgl_akhir'] ?? date('Y-m-d');
$status = $_GET['status'] ?? 'semua';
$fasilitas_id = $_GET['fasilitas'] ?? '';

// Query data (limit 1000 untuk tampilan web)
$sql = "
    SELECT p.id, p.no_tiket, p.judul, p.status, p.created_at, p.updated_at,
           f.nama as fasilitas, u.nama as pelapor
    FROM pengaduan p
    JOIN users u ON p.user_id = u.id
    JOIN fasilitas f ON p.fasilitas_id = f.id
    WHERE DATE(p.created_at) BETWEEN ? AND ?
";

$params = [$tgl_awal, $tgl_akhir];
$types = "ss";

if ($status !== 'semua') {
    $sql .= " AND p.status = ?";
    $params[] = $status;
    $types .= "s";
}
if ($fasilitas_id) {
    $sql .= " AND f.id = ?";
    $params[] = $fasilitas_id;
    $types .= "i";
}

$sql .= " ORDER BY p.created_at DESC LIMIT 1000";

$stmt = $koneksi->prepare($sql);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();

// Ambil daftar fasilitas untuk dropdown
$fasilitas_list = $koneksi->query("SELECT id, nama FROM fasilitas ORDER BY nama");
?>

<?php include '../../includes/header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="fas fa-file-alt me-2"></i> Rekap Laporan Pengaduan</h2>
    <a href="index.php" class="btn btn-outline-secondary no-print">
        <i class="fas fa-arrow-left me-1"></i> Dashboard
    </a>
</div>

<!-- 🔍 Filter -->
<div class="card mb-4 no-print">
    <div class="card-header bg-secondary text-white">
        <i class="fas fa-filter me-2"></i> Filter Laporan
    </div>
    <div class="card-body">
        <form method="GET" class="row g-3">
            <div class="col-md-3">
                <label class="form-label">Dari Tanggal *</label>
                <input type="date" class="form-control" name="tgl_awal" value="<?= htmlspecialchars($tgl_awal) ?>" required>
            </div>
            <div class="col-md-3">
                <label class="form-label">Sampai Tanggal *</label>
                <input type="date" class="form-control" name="tgl_akhir" value="<?= htmlspecialchars($tgl_akhir) ?>" required>
            </div>
            <div class="col-md-2">
                <label class="form-label">Status</label>
                <select class="form-select" name="status">
                    <option value="semua" <?= $status === 'semua' ? 'selected' : '' ?>>Semua Status</option>
                    <option value="menunggu" <?= $status === 'menunggu' ? 'selected' : '' ?>>Menunggu</option>
                    <option value="diproses" <?= $status === 'diproses' ? 'selected' : '' ?>>Diproses</option>
                    <option value="selesai" <?= $status === 'selesai' ? 'selected' : '' ?>>Selesai</option>
                    <option value="ditolak" <?= $status === 'ditolak' ? 'selected' : '' ?>>Ditolak</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Fasilitas</label>
                <select class="form-select" name="fasilitas">
                    <option value="">Semua Fasilitas</option>
                    <?php while ($f = $fasilitas_list->fetch_assoc()): ?>
                        <option value="<?= $f['id'] ?>" <?= $f['id'] == $fasilitas_id ? 'selected' : '' ?>>
                            <?= htmlspecialchars($f['nama']) ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="col-md-1 d-flex align-items-end">
                <button type="submit" class="btn btn-secondary w-100">
                    <i class="fas fa-search"></i>
                </button>
            </div>
        </form>
    </div>
</div>

<!-- 📋 Hasil Laporan -->
<?php if ($result->num_rows > 0): ?>
    <!-- 🖨️ Header Print -->
    <div class="print-header no-print">
        <h2>LAPORAN PENGADUAN FASILITAS</h2>
        <p>
            Politeknik Negeri Medan<br>
            Periode: <?= date('d M Y', strtotime($tgl_awal)) ?> – <?= date('d M Y', strtotime($tgl_akhir)) ?>
        </p>
    </div>
    <div class="print-meta no-print text-end mb-3">
        <small>Diunduh: <?= date('d M Y H:i') ?></small>
    </div>

    <div class="card no-print">
        <div class="card-body">
            <div class="d-flex justify-content-between mb-3">
                <h5 class="mb-0"><?= $result->num_rows ?> pengaduan ditemukan</h5>
                <div>
                    <a href="?<?= http_build_query(array_merge($_GET, ['export' => 'excel'])) ?>" 
                       class="btn btn-success btn-sm">
                        <i class="fas fa-file-excel me-1"></i> Excel
                    </a>
                    <a href="laporan_pdf.php?<?= http_build_query($_GET) ?>" 
                    class="btn btn-danger btn-sm me-2" target="_blank">
                        <i class="fas fa-file-pdf me-1"></i> PDF (Lihat)
                    </a>
                    <a href="laporan_pdf.php?<?= http_build_query(array_merge($_GET, ['download' => 1])) ?>" 
                        class="btn btn-outline-danger btn-sm">
                            <i class="fas fa-download me-1"></i> PDF (Download)
                        </a>
                    <button onclick="window.print()" class="btn btn-outline-secondary btn-sm">
                        <i class="fas fa-print me-1"></i> Cetak
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-sm">
                    <thead class="table-light">
                        <tr>
                            <th>No</th>
                            <th>No. Tiket</th>
                            <th>Fasilitas</th>
                            <th>Judul</th>
                            <th>Pelapor</th>
                            <th>Status</th>
                            <th>Tgl Lapor</th>
                            <th>Tindakan</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $no = 1; while ($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td><?= $no++ ?></td>
                                <td><?= htmlspecialchars($row['no_tiket']) ?></td>
                                <td><?= htmlspecialchars($row['fasilitas']) ?></td>
                                <td><?= htmlspecialchars($row['judul']) ?></td>
                                <td><?= htmlspecialchars($row['pelapor']) ?></td>
                                <td>
                                    <span class="badge bg-<?= 
                                        $row['status'] == 'selesai' ? 'success' : 
                                        ($row['status'] == 'diproses' ? 'info' : 
                                        ($row['status'] == 'ditolak' ? 'danger' : 'warning')) 
                                    ?>">
                                        <?= ucfirst($row['status']) ?>
                                    </span>
                                </td>
                                <td><?= date('d-m-Y', strtotime($row['created_at'])) ?></td>
                                <td class="no-print">
                                    <a href="pengaduan.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- 🖨️ Footer Print -->
    <footer class="print-footer no-print">
        <small>Laporan ini dihasilkan oleh Sistem Pengaduan Fasilitas Polmed</small>
    </footer>
<?php else: ?>
    <div class="card">
        <div class="card-body text-center py-5">
            <i class="fas fa-file-alt fa-3x text-muted mb-3"></i>
            <h4>Tidak ada data sesuai filter.</h4>
        </div>
    </div>
<?php endif; ?>

<?php include '../../includes/footer.php'; ?> 