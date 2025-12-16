    <?php
    // dashboard/petugas/pengaduan.php
    require_once '../../config/database.php';
    require_once '../../includes/auth_check.php';
    auth_check('petugas'); // hanya petugas

    // Filter dari form (GET)
    $status_filter = $_GET['status'] ?? 'semua';
    $fasilitas_filter = $_GET['fasilitas'] ?? '';
    $tgl_awal = $_GET['tgl_awal'] ?? '';
    $tgl_akhir = $_GET['tgl_akhir'] ?? '';

    // Daftar status & fasilitas untuk dropdown
    $status_list = ['semua', 'menunggu', 'diproses', 'selesai', 'ditolak'];
    $fasilitas_result = $koneksi->query("SELECT id, nama FROM fasilitas ORDER BY nama");

    // Query dasar — hanya tampilkan pengaduan yang relevan untuk petugas
    $sql = "
        SELECT p.id, p.no_tiket, p.judul, p.status, p.created_at, p.updated_at,
            u.nama as pelapor, f.nama as fasilitas
        FROM pengaduan p
        JOIN users u ON p.user_id = u.id
        JOIN fasilitas f ON p.fasilitas_id = f.id
        WHERE 1=1
    ";

    $params = [];
    $types = "";

    // Filter: petugas biasanya fokus ke 'menunggu' & 'diproses'
    if ($status_filter !== 'semua') {
        $sql .= " AND p.status = ?";
        $params[] = $status_filter;
        $types .= "s";
    } else {
        // Default: hanya tampilkan menunggu + diproses (kecuali dipaksa lihat semua)
        $sql .= " AND p.status IN ('menunggu', 'diproses')";
    }

    if ($fasilitas_filter) {
        $sql .= " AND f.id = ?";
        $params[] = $fasilitas_filter;
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
    ?>

    <?php include '../../includes/header.php'; ?>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="fas fa-tools me-2"></i> Kelola Pengaduan</h2>
        <a href="index.php" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i> Dashboard
        </a>
    </div>

    <!-- 🔍 Filter -->
    <div class="card mb-4">
        <div class="card-header bg-info text-white">
            <i class="fas fa-filter me-2"></i> Filter Pengaduan
        </div>
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Status</label>
                    <select class="form-select" name="status">
                        <?php foreach ($status_list as $s): 
                            // Default: sembunyikan 'semua' kecuali dipilih
                            if ($s === 'semua' && !$status_filter) continue;
                        ?>
                            <option value="<?= $s ?>" <?= $s === $status_filter ? 'selected' : '' ?>>
                                <?= $s == 'semua' ? 'Semua Status' : ucfirst($s) ?>
                            </option>
                        <?php endforeach; ?>
                        <?php if (!$status_filter): ?>
                            <option value="semua">→ Tampilkan Semua</option>
                        <?php endif; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Fasilitas</label>
                    <select class="form-select" name="fasilitas">
                        <option value="">Semua Fasilitas</option>
                        <?php while ($f = $fasilitas_result->fetch_assoc()): ?>
                            <option value="<?= $f['id'] ?>" <?= $f['id'] == $fasilitas_filter ? 'selected' : '' ?>>
                                <?= htmlspecialchars($f['nama']) ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Dari Tanggal</label>
                    <input type="date" class="form-control" name="tgl_awal" value="<?= htmlspecialchars($tgl_awal) ?>">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Sampai Tanggal</label>
                    <input type="date" class="form-control" name="tgl_akhir" value="<?= htmlspecialchars($tgl_akhir) ?>">
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-info w-100">
                        <i class="fas fa-search me-1"></i> Filter
                    </button>
                </div>
            </form>
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
                                <th>Pelapor</th>
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
                                    <td><?= htmlspecialchars($row['pelapor']) ?></td>
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
                                        <div class="btn-group" role="group">
                                            <?php if ($row['status'] === 'menunggu'): ?>
                                                <button type="button" 
                                                        class="btn btn-sm btn-info"
                                                        data-bs-toggle="modal" 
                                                        data-bs-target="#ubahStatusModal<?= $row['id'] ?>">
                                                    <i class="fas fa-tools"></i> Proses
                                                </button>
                                            <?php elseif ($row['status'] === 'diproses'): ?>
                                                <button type="button" 
                                                        class="btn btn-sm btn-success"
                                                        data-bs-toggle="modal" 
                                                        data-bs-target="#ubahStatusModal<?= $row['id'] ?>">
                                                    <i class="fas fa-check"></i> Selesai
                                                </button>
                                                <button type="button" 
                                                        class="btn btn-sm btn-danger"
                                                        data-bs-toggle="modal" 
                                                        data-bs-target="#tolakModal<?= $row['id'] ?>">
                                                    <i class="fas fa-times"></i> Tolak
                                                </button>
                                            <?php endif; ?>
                                            <a href="#" 
                                            class="btn btn-sm btn-outline-primary"
                                            data-bs-toggle="modal" 
                                            data-bs-target="#detailModal<?= $row['id'] ?>">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>

                                <!-- Modal: sama seperti di admin/pengaduan.php -->
                                <!-- Modal Ubah Status -->
                                <div class="modal fade" id="ubahStatusModal<?= $row['id'] ?>" tabindex="-1">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <form method="POST" action="../admin/proses_status.php">
                                                <input type="hidden" name="pengaduan_id" value="<?= $row['id'] ?>">
                                                <input type="hidden" name="aksi" value="<?= $row['status'] === 'menunggu' ? 'proses' : 'selesai' ?>">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">
                                                        <i class="fas fa-sync-alt me-2"></i>
                                                        Ubah Status: <?= htmlspecialchars($row['no_tiket']) ?>
                                                    </h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <div class="mb-3">
                                                        <label class="form-label">Status Baru</label>
                                                        <input type="text" 
                                                            class="form-control-plaintext" 
                                                            value="<?= $row['status'] === 'menunggu' ? 'Diproses' : 'Selesai' ?>" 
                                                            readonly>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label">Catatan (Opsional)</label>
                                                        <textarea class="form-control" 
                                                                name="catatan" 
                                                                rows="3" 
                                                                placeholder="Contoh: Perbaikan selesai."></textarea>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                                        Batal
                                                    </button>
                                                    <button type="submit" class="btn btn-<?= $row['status'] === 'menunggu' ? 'info' : 'success' ?>">
                                                        Simpan
                                                    </button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>

                                <!-- Modal Tolak -->
                                <div class="modal fade" id="tolakModal<?= $row['id'] ?>" tabindex="-1">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <form method="POST" action="../admin/proses_status.php">
                                                <input type="hidden" name="pengaduan_id" value="<?= $row['id'] ?>">
                                                <input type="hidden" name="aksi" value="tolak">
                                                <div class="modal-header bg-danger text-white">
                                                    <h5 class="modal-title">
                                                        <i class="fas fa-ban me-2"></i>
                                                        Tolak Pengaduan
                                                    </h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <div class="alert alert-warning">
                                                        <i class="fas fa-exclamation-triangle me-1"></i>
                                                        Ini akan mengubah status menjadi <strong>Ditolak</strong>.
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label text-danger">Alasan *</label>
                                                        <textarea class="form-control" 
                                                                name="catatan" 
                                                                rows="3" required></textarea>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                                        Batal
                                                    </button>
                                                    <button type="submit" class="btn btn-danger">
                                                        Tolak
                                                    </button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>

                                <!-- Modal Detail (sederhana) -->
                                <div class="modal fade" id="detailModal<?= $row['id'] ?>" tabindex="-1">
                                    <div class="modal-dialog modal-lg">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title">
                                                    <i class="fas fa-info-circle me-2"></i>
                                                    Detail: <?= htmlspecialchars($row['no_tiket']) ?>
                                                </h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body">
                                                <p class="text-center text-muted">Fitur detail lengkap akan dikembangkan.</p>
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
                <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
                <h4>Tidak ada pengaduan menunggu/diproses.</h4>
                <a href="index.php" class="btn btn-outline-info">
                    <i class="fas fa-arrow-left me-1"></i> Kembali ke Dashboard
                </a>
            </div>
        </div>
    <?php endif; ?>

    <?php include '../../includes/footer.php'; ?>