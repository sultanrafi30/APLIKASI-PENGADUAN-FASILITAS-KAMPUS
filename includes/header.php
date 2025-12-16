<?php
// includes/header.php — NAVBAR MODERN (UPDATE)
require_once '../../config/database.php';

// Ambil jumlah notifikasi belum dibaca
$notif_count = 0;
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $notif_query = "SELECT COUNT(*) as total FROM notifikasi WHERE user_id = ? AND is_read = 0";
    $stmt = $koneksi->prepare($notif_query);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $notif_result = $stmt->get_result();
    $notif = $notif_result->fetch_assoc();
    $notif_count = $notif['total'];
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pengaduan Fasilitas Polmed</title>
    <!-- Google Fonts — Plus Jakarta Sans -->
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        body { font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, sans-serif; }
    </style>
</head>
<body>

<!-- Navbar Modern -->
<nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm">
    <div class="container">
        <!-- Logo -->
        <a class="navbar-brand fw-bold" href="#" style="color: #0f172a;">
            <i class="fas fa-university me-2" style="color: var(--primary);"></i>
            Polmed
        </a>

        <!-- Toggle (HP) -->
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>

        <!-- Menu Navigasi -->
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav mx-auto">
                <?php if ($_SESSION['role'] === 'mahasiswa'): ?>
                    <li class="nav-item">
                        <a class="nav-link <?= strpos($_SERVER['REQUEST_URI'], 'mahasiswa/index.php') !== false ? 'active text-primary fw-bold' : '' ?>" 
                           href="../mahasiswa/index.php">
                            <i class="fas fa-home me-1"></i> Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= strpos($_SERVER['REQUEST_URI'], 'buat_pengaduan.php') !== false ? 'active text-primary fw-bold' : '' ?>" 
                           href="../mahasiswa/buat_pengaduan.php">
                            <i class="fas fa-plus-circle me-1"></i> Buat Pengaduan
                        </a>
                    </li>

                <?php elseif ($_SESSION['role'] === 'petugas'): ?>
                    <li class="nav-item">
                        <a class="nav-link <?= strpos($_SERVER['REQUEST_URI'], 'petugas/index.php') !== false ? 'active text-primary fw-bold' : '' ?>" 
                           href="../petugas/index.php">
                            <i class="fas fa-home me-1"></i> Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= strpos($_SERVER['REQUEST_URI'], 'pengaduan.php') !== false ? 'active text-primary fw-bold' : '' ?>" 
                           href="../petugas/pengaduan.php">
                            <i class="fas fa-tasks me-1"></i> Kelola Pengaduan
                        </a>
                    </li>

                <?php elseif ($_SESSION['role'] === 'admin'): ?>
                    <li class="nav-item">
                        <a class="nav-link <?= strpos($_SERVER['REQUEST_URI'], 'admin/index.php') !== false ? 'active text-primary fw-bold' : '' ?>" 
                           href="../admin/index.php">
                            <i class="fas fa-home me-1"></i> Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= strpos($_SERVER['REQUEST_URI'], 'pengaduan.php') !== false ? 'active text-primary fw-bold' : '' ?>" 
                           href="../admin/pengaduan.php">
                            <i class="fas fa-exclamation-triangle me-1"></i> Pengaduan
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= strpos($_SERVER['REQUEST_URI'], 'fasilitas.php') !== false ? 'active text-primary fw-bold' : '' ?>" 
                           href="../admin/fasilitas.php">
                            <i class="fas fa-building me-1"></i> Fasilitas
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= strpos($_SERVER['REQUEST_URI'], 'user.php') !== false ? 'active text-primary fw-bold' : '' ?>" 
                           href="../admin/user.php">
                            <i class="fas fa-users me-1"></i> User
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= strpos($_SERVER['REQUEST_URI'], 'laporan.php') !== false ? 'active text-primary fw-bold' : '' ?>" 
                           href="../admin/laporan.php">
                            <i class="fas fa-file-alt me-1"></i> Laporan
                        </a>
                    </li>
                <?php endif; ?>
            </ul>

            <!-- Notifikasi & Profil -->
            <ul class="navbar-nav ms-auto">
                <!-- Notifikasi -->
                <li class="nav-item dropdown">
                    <a class="nav-link position-relative" href="#" id="notifDropdown" role="button" data-bs-toggle="dropdown">
                        <i class="fas fa-bell"></i>
                        <?php if ($notif_count > 0): ?>
                            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                                <?= $notif_count ?>
                            </span>
                        <?php endif; ?>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end" style="border-radius: 12px; box-shadow: var(--shadow);">
                        <li><h6 class="dropdown-header">Notifikasi</h6></li>
                        <?php if ($notif_count == 0): ?>
                            <li><a class="dropdown-item text-muted">Tidak ada notifikasi baru</a></li>
                        <?php else: ?>
                            <li><a class="dropdown-item" href="../notifikasi.php">Lihat semua notifikasi</a></li>
                        <?php endif; ?>
                    </ul>
                </li>

                <!-- Profil User -->
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown" style="border-radius: 12px;">
                        <i class="fas fa-user-circle"></i> <?= htmlspecialchars($_SESSION['nama']) ?>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end" style="border-radius: 12px; box-shadow: var(--shadow);">
                        <?php if ($_SESSION['role'] === 'mahasiswa'): ?>
                            <li>
                                <a class="dropdown-item" href="../mahasiswa/profil.php">
                                    <i class="fas fa-user-edit me-2"></i> Ubah Profil
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="../mahasiswa/ubah_password.php">
                                    <i class="fas fa-key me-2"></i> Ubah Password
                                </a>
                            </li>
                        <?php elseif ($_SESSION['role'] === 'petugas'): ?>
                            <li>
                                <a class="dropdown-item" href="../petugas/profil.php">
                                    <i class="fas fa-user-edit me-2"></i> Ubah Profil
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="../petugas/ubah_password.php">
                                    <i class="fas fa-key me-2"></i> Ubah Password
                                </a>
                            </li>
                        <?php elseif ($_SESSION['role'] === 'admin'): ?>
                            <li>
                                <a class="dropdown-item" href="../admin/profil.php">
                                    <i class="fas fa-user-edit me-2"></i> Ubah Profil
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="../admin/ubah_password.php">
                                    <i class="fas fa-key me-2"></i> Ubah Password
                                </a>
                            </li>
                        <?php endif; ?>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <a class="dropdown-item" href="/polmed-pengaduan/logout.php">
                                <i class="fas fa-sign-out-alt me-2"></i> Logout
                            </a>
                        </li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</nav>

<!-- Alert dari Session -->
<?php if (isset($_SESSION['error'])): ?>
    <div class="alert alert-danger alert-dismissible fade show m-3" role="alert">
        <i class="fas fa-exclamation-circle me-2"></i>
        <?= htmlspecialchars($_SESSION['error']) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php unset($_SESSION['error']); ?>
<?php endif; ?>

<?php if (isset($_SESSION['success'])): ?>
    <div class="alert alert-success alert-dismissible fade show m-3" role="alert">
        <i class="fas fa-check-circle me-2"></i>
        <?= htmlspecialchars($_SESSION['success']) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php unset($_SESSION['success']); ?>
<?php endif; ?>

<div class="container mt-4">
