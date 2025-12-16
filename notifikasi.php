<?php
// notifikasi.php
require_once 'config/database.php';
require_once 'includes/auth_check.php';
auth_check(); // semua role boleh

$user_id = $_SESSION['user_id'];
$filter = $_GET['filter'] ?? 'belum_dibaca';

// Update: tandai notifikasi sebagai dibaca (jika dikirim via GET)
if (isset($_GET['baca'])) {
    $notif_id = intval($_GET['baca']);
    $stmt = $koneksi->prepare("UPDATE notifikasi SET is_read = 1 WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $notif_id, $user_id);
    $stmt->execute();
    $stmt->close();
    
    // Redirect agar tidak berulang saat refresh
    header("Location: notifikasi.php?filter=" . $filter);
    exit();
}

// Query notifikasi
$sql = "SELECT * FROM notifikasi WHERE user_id = ? ";
if ($filter === 'belum_dibaca') {
    $sql .= "AND is_read = 0 ";
}
$sql .= "ORDER BY created_at DESC";

$stmt = $koneksi->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<?php include 'includes/header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="fas fa-bell me-2"></i> Notifikasi Saya</h2>
    <a href="<?= $_SESSION['role'] === 'mahasiswa' ? 'mahasiswa/index.php' : ($_SESSION['role'] === 'petugas' ? 'dashboard/petugas/index.php' : 'dashboard/admin/index.php') ?>" 
       class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left me-1"></i> Kembali
    </a>
</div>

<!-- 🔍 Filter -->
<div class="btn-group mb-3" role="group">
    <a href="?filter=belum_dibaca" 
       class="btn btn-<?= $filter === 'belum_dibaca' ? 'primary' : 'outline-primary' ?>">
        <i class="fas fa-envelope me-1"></i> Belum Dibaca 
        <?php 
        $unseen = $koneksi->query("SELECT COUNT(*) as total FROM notifikasi WHERE user_id = $user_id AND is_read = 0")->fetch_assoc()['total'];
        if ($unseen > 0) echo "<span class='badge bg-danger ms-1'>$unseen</span>";
        ?>
    </a>
    <a href="?filter=semua" 
       class="btn btn-<?= $filter === 'semua' ? 'primary' : 'outline-primary' ?>">
        <i class="fas fa-inbox me-1"></i> Semua
    </a>
</div>

<!-- 📋 Daftar Notifikasi -->
<?php if ($result->num_rows > 0): ?>
    <div class="list-group">
        <?php while ($n = $result->fetch_assoc()): ?>
            <a href="?baca=<?= $n['id'] ?>&filter=<?= $filter ?>" 
               class="list-group-item list-group-item-action <?= $n['is_read'] ? 'list-group-item-light' : '' ?>">
                <div class="d-flex justify-content-between">
                    <h6 class="mb-1"><?= htmlspecialchars($n['judul']) ?></h6>
                    <small class="text-muted"><?= date('d M Y H:i', strtotime($n['created_at'])) ?></small>
                </div>
                <p class="mb-1"><?= nl2br(htmlspecialchars($n['isi'])) ?></p>
                <?php if (!$n['is_read']): ?>
                    <span class="badge bg-primary">Baru</span>
                <?php endif; ?>
            </a>
        <?php endwhile; ?>
    </div>

    <?php if ($filter === 'belum_dibaca' && $result->num_rows > 0): ?>
        <div class="mt-3">
            <a href="#" 
               class="btn btn-sm btn-outline-secondary"
               onclick="if(confirm('Tandai semua notifikasi sebagai sudah dibaca?')) window.location='?tandai_semua=ya'; return false;">
                <i class="fas fa-check-double me-1"></i> Tandai Semua Sudah Dibaca
            </a>
        </div>
    <?php endif; ?>

<?php else: ?>
    <div class="card">
        <div class="card-body text-center py-5">
            <i class="fas fa-bell-slash fa-3x text-muted mb-3"></i>
            <h4>Tidak ada notifikasi.</h4>
            <p class="text-muted">
                <?php if ($filter === 'belum_dibaca'): ?>
                    Semua notifikasi sudah dibaca.
                    <br><a href="?filter=semua" class="btn btn-sm btn-outline-primary mt-2">
                        Lihat Semua Notifikasi
                    </a>
                <?php else: ?>
                    Belum ada notifikasi.
                <?php endif; ?>
            </p>
        </div>
    </div>
<?php endif; ?>

<!-- Proses tandai semua -->
<?php
if (isset($_GET['tandai_semua']) && $_GET['tandai_semua'] === 'ya') {
    $koneksi->query("UPDATE notifikasi SET is_read = 1 WHERE user_id = $user_id AND is_read = 0");
    header("Location: notifikasi.php");
    exit();
}
?>

<?php include 'includes/footer.php'; ?>