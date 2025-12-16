<?php
// includes/detail_pengaduan.php
// 🔔 Harus dipanggil setelah koneksi database & data $pengaduan sudah tersedia
// Contoh pemakaian di halaman lain:
//   $pengaduan = $result->fetch_assoc();
//   include 'includes/detail_pengaduan.php';

if (!isset($pengaduan)) {
    die("<div class='alert alert-danger'>Error: Data pengaduan tidak tersedia.</div>");
}

// Ambil fasilitas
$fasilitas = $koneksi->query("SELECT nama, lokasi FROM fasilitas WHERE id = " . $pengaduan['fasilitas_id'])->fetch_assoc();

// Ambil riwayat status
$riwayat = [];
$riwayat_result = $koneksi->query("
    SELECT rp.status_baru, rp.catatan, rp.created_at, u.nama as updated_by
    FROM riwayat_pengaduan rp
    JOIN users u ON rp.updated_by = u.id
    WHERE rp.pengaduan_id = " . $pengaduan['id'] . "
    ORDER BY rp.created_at ASC
");
while ($r = $riwayat_result->fetch_assoc()) {
    $riwayat[] = $r;
}

// Status awal (saat dibuat)
$status_awal = [
    'status_baru' => 'menunggu',
    'catatan' => 'Pengaduan baru dibuat oleh ' . htmlspecialchars($pengaduan['nama_pelapor'] ?? ''),
    'created_at' => $pengaduan['created_at'],
    'updated_by' => $pengaduan['nama_pelapor'] ?? 'Mahasiswa'
];
// Gabungkan: status awal + riwayat
$timeline = array_merge([$status_awal], $riwayat);
?>

<!-- 📋 Detail Pengaduan -->
<div class="row mb-4">
    <div class="col-md-8">
        <h5><i class="fas fa-align-left me-1"></i> Deskripsi</h5>
        <div class="border rounded p-3 bg-light">
            <?= nl2br(htmlspecialchars($pengaduan['deskripsi'])) ?>
        </div>
    </div>
    <div class="col-md-4">
        <?php if ($pengaduan['foto']): ?>
            <h5><i class="fas fa-image me-1"></i> Foto</h5>
            <div class="border rounded overflow-hidden">
                <img src="../uploads/<?= htmlspecialchars($pengaduan['foto']) ?>" 
                     class="img-fluid" 
                     alt="Foto pengaduan"
                     style="max-height:300px; object-fit:contain;">
            </div>
        <?php else: ?>
            <div class="text-center py-4 text-muted">
                <i class="fas fa-image fa-2x mb-2"></i>
                <br>
                Tidak ada foto.
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- 📅 Timeline Riwayat Status -->
<h5><i class="fas fa-history me-1"></i> Riwayat Perubahan Status</h5>
<div class="timeline p-3 border rounded">
    <?php foreach ($timeline as $i => $t): 
        $is_last = ($i == count($timeline) - 1);
        $status_class = '';
        switch ($t['status_baru']) {
            case 'menunggu': $status_class = 'text-warning'; break;
            case 'diproses': $status_class = 'text-info'; break;
            case 'selesai': $status_class = 'text-success'; break;
            case 'ditolak': $status_class = 'text-danger'; break;
        }
    ?>
        <div class="d-flex">
            <div class="me-3">
                <div class="bg-light border rounded-circle d-flex align-items-center justify-content-center" 
                     style="width:36px; height:36px;">
                    <i class="fas <?= 
                        $t['status_baru'] == 'selesai' ? 'fa-check' : 
                        ($t['status_baru'] == 'ditolak' ? 'fa-times' : 'fa-clock') 
                    ?> <?= $status_class ?>"></i>
                </div>
                <?php if (!$is_last): ?>
                    <div class="border-start border-2 h-100 ms-4 mt-1"></div>
                <?php endif; ?>
            </div>
            <div class="flex-grow-1 mb-4">
                <div class="d-flex justify-content-between">
                    <strong class="<?= $status_class ?>">
                        <?= ucfirst($t['status_baru']) ?>
                    </strong>
                    <small class="text-muted"><?= date('d M Y H:i', strtotime($t['created_at'])) ?></small>
                </div>
                <div class="mt-1">
                    <small>
                        <i class="fas fa-user me-1"></i>
                        <?= htmlspecialchars($t['updated_by']) ?>
                    </small>
                </div>
                <?php if (!empty($t['catatan'])): ?>
                    <div class="mt-2 p-2 bg-white border rounded">
                        <i class="fas fa-comment me-1"></i>
                        <?= nl2br(htmlspecialchars($t['catatan'])) ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<style>
/* Timeline styling */
.timeline .border-start {
    border-color: #0d6efd !important;
}
.timeline i.fa-check { color: #28a745; }
.timeline i.fa-times { color: #dc3545; }
.timeline i.fa-clock { color: #ffc107; }
</style>