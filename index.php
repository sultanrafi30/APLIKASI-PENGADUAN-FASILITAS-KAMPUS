<?php
// index.php — HOMEPAGE MODERN
session_start();

// Redirect otomatis jika sudah login
if (isset($_SESSION['user_id'])) {
    switch ($_SESSION['role']) {
        case 'admin': header("Location: dashboard/admin/index.php"); break;
        case 'petugas': header("Location: dashboard/petugas/index.php"); break;
        default: header("Location: mahasiswa/index.php"); break;
    }
    exit();
}

// Pesan logout
if (isset($_GET['logout']) && $_GET['logout'] === 'success') {
    $_SESSION['success'] = "Anda telah logout.";
}

// Statistik
require_once 'config/database.php';
$stat = [
    'total' => $koneksi->query("SELECT COUNT(*) as t FROM pengaduan")->fetch_assoc()['t'],
    'selesai' => $koneksi->query("SELECT COUNT(*) as t FROM pengaduan WHERE status = 'selesai'")->fetch_assoc()['t'],
    'hari_ini' => $koneksi->query("SELECT COUNT(*) as t FROM pengaduan WHERE DATE(created_at) = CURDATE()")->fetch_assoc()['t'],
];
$koneksi->close();

$error = $_GET['error'] ?? '';
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pengaduan Fasilitas Kampus — Politeknik Negeri Medan</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .hero {
            background: linear-gradient(135deg, #f0f7ff 0%, #e6f0ff 100%);
            padding: 5rem 0;
        }
        .stat-number {
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--primary);
        }
        .tab-btn {
            border: none;
            background: #f8fafc;
            padding: 12px 24px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
        }
        .tab-btn.active {
            background: var(--primary);
            color: white;
            border-radius: 8px;
        }
        .floating {
            animation: float 4s ease-in-out infinite;
        }
        @keyframes float {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-10px); }
        }
    </style>
</head>
<body>

<!-- 🟦 Navbar Modern -->
<nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm">
    <div class="container">
        <a class="navbar-brand fw-bold" href="#" style="color: #0f172a;">
            <i class="fas fa-university me-2" style="color: var(--primary);"></i>
            Polmed
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item">
                    <a class="nav-link fw-medium" href="#tentang">Tentang</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link fw-medium" href="#statistik">Statistik</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link fw-medium" href="#cara">Cara Pakai</a>
                </li>
            </ul>
        </div>
    </div>
</nav>

<!-- 🏆 Hero Section -->
<section class="hero">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6 mb-4 mb-lg-0">
                <h1 class="display-5 fw-bold" style="color: #0f172a;">
                    Pengaduan Fasilitas Kampus
                </h1>
                <p class="lead text-muted mb-4">
                    Laporkan kerusakan fasilitas Polmed secara cepat, transparan, dan terlacak.
                </p>
                <div class="d-flex flex-wrap gap-2">
                    <a href="#login" class="btn btn-primary btn-lg px-4">
                        <i class="fas fa-user-plus me-2"></i> Daftar Sekarang
                    </a>
                    <a href="#login" class="btn btn-outline-primary btn-lg px-4">
                        <i class="fas fa-sign-in-alt me-2"></i> Login
                    </a>
                </div>
            </div>
            <div class="col-lg-6 text-center">
                <img src="https://images.unsplash.com/photo-1551650975-87deedd944c3?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=80" 
                     alt="Ilustrasi Pengaduan" 
                     class="img-fluid rounded-3 shadow" 
                     width="400"
                     style="border: 8px solid white;">
            </div>
        </div>
    </div>
</section>

<!-- 📋 Tentang Sistem -->
<section id="tentang" class="py-5">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="fw-bold" style="color: #0f172a;">Apa Itu Sistem Pengaduan Fasilitas?</h2>
            <p class="text-muted">Solusi digital untuk meningkatkan kenyamanan & keamanan kampus</p>
        </div>
        <div class="row g-4">
            <div class="col-md-4">
                <div class="text-center p-4">
                    <div class="bg-primary bg-opacity-10 d-inline-flex p-3 rounded-circle mb-3">
                        <i class="fas fa-bolt fa-2x text-primary"></i>
                    </div>
                    <h5>Respons Cepat</h5>
                    <p class="text-muted">Laporan langsung diterima tim fasilitas dalam waktu 24 jam</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="text-center p-4">
                    <div class="bg-success bg-opacity-10 d-inline-flex p-3 rounded-circle mb-3">
                        <i class="fas fa-shield-alt fa-2x text-success"></i>
                    </div>
                    <h5>Aman & Terverifikasi</h5>
                    <p class="text-muted">Hanya untuk mahasiswa & pegawai Polmed</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="text-center p-4">
                    <div class="bg-info bg-opacity-10 d-inline-flex p-3 rounded-circle mb-3">
                        <i class="fas fa-history fa-2x text-info"></i>
                    </div>
                    <h5>Transparan</h5>
                    <p class="text-muted">Pantau progres pengaduan dari awal hingga selesai</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- 📊 Statistik Real-Time -->
<section id="statistik" class="py-5 bg-light">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="fw-bold" style="color: #0f172a;">Statistik Pengaduan</h2>
            <p class="text-muted">Data terkini sistem pengaduan Polmed</p>
        </div>
        <div class="row g-4">
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="stat-number"><?= $stat['total'] ?></div>
                    <p class="mb-0 text-muted">Total Pengaduan</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="stat-number"><?= $stat['selesai'] ?></div>
                    <p class="mb-0 text-muted">Selesai</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="stat-number"><?= $stat['hari_ini'] ?></div>
                    <p class="mb-0 text-muted">Hari Ini</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="stat-number"><?= $stat['total'] > 0 ? round(($stat['selesai'] / $stat['total']) * 100) : 0 ?>%</div>
                    <p class="mb-0 text-muted">Tingkat Penyelesaian</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- 🔐 Login & Register -->
<section id="login" class="py-5">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="fw-bold" style="color: #0f172a;">Akses Akun Anda</h2>
            <p class="text-muted">Masuk atau daftar sebagai mahasiswa/pegawai Polmed</p>
        </div>
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-body">
                        <!-- Tab Navigation -->
                        <div class="d-flex mb-3">
                            <button class="tab-btn active flex-fill" data-tab="login">
                                <i class="fas fa-sign-in-alt me-2"></i> Login
                            </button>
                            <button class="tab-btn flex-fill" data-tab="register">
                                <i class="fas fa-user-plus me-2"></i> Daftar
                            </button>
                        </div>

                        <!-- Tab Content -->
                        <div id="tab-login" class="tab-content active">
                            <?php if ($error): ?>
                                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                    <i class="fas fa-exclamation-circle me-2"></i>
                                    <?= htmlspecialchars($error) ?>
                                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                </div>
                            <?php endif; ?>
                            <form method="POST" action="login.php">
                                <div class="mb-3">
                                    <label class="form-label">NIM / NIP / Username</label>
                                    <input type="text" class="form-control" name="username" placeholder="Contoh: 23.11.0001" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Password</label>
                                    <input type="password" class="form-control" name="password" placeholder="******" required>
                                </div>
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="fas fa-sign-in-alt me-2"></i> Masuk ke Dashboard
                                </button>
                            </form>
                            <div class="text-center mt-3">
                                <small class="text-muted">
                                    🔑 Password default akun contoh: <code>polmed123</code>
                                </small>
                            </div>
                        </div>

                        <div id="tab-register" class="tab-content" style="display:none;">
                            <form method="POST" action="register.php">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label">Nama Lengkap *</label>
                                        <input type="text" class="form-control" name="nama" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">NIM (Mahasiswa) *</label>
                                        <input type="text" class="form-control" name="nim" 
                                               placeholder="23.11.0001" 
                                               pattern="\d{2}\.11\.\d{4}"
                                               title="Format: 23.11.0001"
                                               required>
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label">Email (Opsional)</label>
                                        <input type="email" class="form-control" name="email">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Password *</label>
                                        <input type="password" class="form-control" name="password" minlength="6" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Konfirmasi Password *</label>
                                        <input type="password" class="form-control" name="password2" minlength="6" required>
                                    </div>
                                </div>
                                <div class="mt-3 form-check">
                                    <input class="form-check-input" type="checkbox" id="agree" required>
                                    <label class="form-check-label" for="agree">
                                        Saya adalah mahasiswa/pegawai Politeknik Negeri Medan
                                    </label>
                                </div>
                                <button type="submit" class="btn btn-primary w-100 mt-3">
                                    <i class="fas fa-user-plus me-2"></i> Buat Akun
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- 📞 Footer -->
<footer class="bg-dark text-white py-4">
    <div class="container">
        <div class="text-center">
            <div class="mb-3">
                <span class="fw-bold">Politeknik Negeri Medan</span>
                <br>
                <small>Jl. Almamater No. 1, Gedung Utama Lt. 1</small>
            </div>
            <div class="text-muted small">
                &copy; <?= date('Y') ?> Pengaduan Fasilitas Kampus Polmed
            </div>
        </div>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Tab switching
document.querySelectorAll('.tab-btn').forEach(button => {
    button.addEventListener('click', () => {
        document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
        document.querySelectorAll('.tab-content').forEach(c => c.style.display = 'none');
        button.classList.add('active');
        const tab = button.getAttribute('data-tab');
        document.getElementById('tab-' + tab).style.display = 'block';
    });
});
</script>

</body>
</html>