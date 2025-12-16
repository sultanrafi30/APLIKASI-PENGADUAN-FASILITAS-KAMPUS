<?php
// dashboard/admin/user.php
require_once '../../config/database.php';
require_once '../../includes/auth_check.php';
auth_check('admin');

$success = '';
$error = '';

// 📥 Proses tambah user
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['aksi']) && $_POST['aksi'] === 'tambah') {
    $nama = trim($_POST['nama'] ?? '');
    $nim_nip = trim($_POST['nim_nip'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $role = $_POST['role'] ?? 'mahasiswa';

    // Validasi
    if (empty($nama) || empty($nim_nip)) {
        $error = "Nama dan NIM/NIP wajib diisi.";
    } elseif (!in_array($role, ['admin', 'petugas', 'mahasiswa'])) {
        $error = "Role tidak valid.";
    } else {
        // Validasi format NIM untuk mahasiswa
        if ($role === 'mahasiswa' && !preg_match('/^\d{2}\.11\.\d{4}$/', $nim_nip)) {
            $error = "Format NIM mahasiswa salah. Contoh: 23.11.0001";
        } else {
            // Generate password default: nim_nip (tanpa titik) + "123"
            $pass_default = preg_replace('/[^0-9]/', '', $nim_nip) . '123';
            $password_hash = password_hash($pass_default, PASSWORD_BCRYPT);

            $stmt = $koneksi->prepare("INSERT INTO users (nama, nim_nip, email, password, role) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("sssss", $nama, $nim_nip, $email, $password_hash, $role);
            
            if ($stmt->execute()) {
                $pesan = "User <strong>$nama</strong> berhasil ditambahkan.<br>Password default: <code>$pass_default</code>";
                $_SESSION['swal_success'] = $pesan;
            } else {
                $error = "Gagal menambah user. NIM/NIP mungkin sudah terdaftar.";
            }
            $stmt->close();
        }
    }
}

// 🔁 Reset password
if (isset($_GET['reset'])) {
    $id = intval($_GET['reset']);
    $user_id = $_SESSION['user_id'];

    if ($id == $user_id) {
        $_SESSION['error'] = "Anda tidak bisa mereset password sendiri di sini.";
    } else {
        // Ambil NIM/NIP user
        $result = $koneksi->query("SELECT nim_nip FROM users WHERE id = $id");
        if ($result->num_rows > 0) {
            $nim = $result->fetch_assoc()['nim_nip'];
            $pass_baru = preg_replace('/[^0-9]/', '', $nim) . '123';
            $hash_baru = password_hash($pass_baru, PASSWORD_BCRYPT);

            $stmt = $koneksi->prepare("UPDATE users SET password = ? WHERE id = ?");
            $stmt->bind_param("si", $hash_baru, $id);
            if ($stmt->execute()) {
                $_SESSION['swal_success'] = "Password user berhasil direset.<br>Password baru: <code>$pass_baru</code>";
            } else {
                $_SESSION['error'] = "Gagal mereset password.";
            }
            $stmt->close();
        }
    }
    header("Location: user.php");
    exit();
}

// 🗑️ Hapus user
if (isset($_GET['hapus'])) {
    $id = intval($_GET['hapus']);
    $user_id = $_SESSION['user_id'];

    if ($id == $user_id) {
        $_SESSION['error'] = "Anda tidak bisa menghapus diri sendiri.";
    } else {
        $stmt = $koneksi->prepare("DELETE FROM users WHERE id = ?");
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            $_SESSION['swal_success'] = "User berhasil dihapus.";
        } else {
            $_SESSION['error'] = "Gagal menghapus user.";
        }
        $stmt->close();
    }
    header("Location: user.php");
    exit();
}

// 📋 Ambil semua user
$user_result = $koneksi->query("
    SELECT id, nama, nim_nip, email, role, created_at 
    FROM users 
    ORDER BY role, nama
");
?>

<?php include '../../includes/header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="fas fa-users me-2"></i> Kelola User</h2>
    <a href="index.php" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left me-1"></i> Dashboard
    </a>
</div>

<!-- ✏️ Form Tambah User -->
<div class="card mb-4">
    <div class="card-header bg-success text-white">
        <i class="fas fa-plus me-2"></i> Tambah User Baru
    </div>
    <div class="card-body">
        <form method="POST">
            <input type="hidden" name="aksi" value="tambah">
            
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Nama Lengkap *</label>
                    <input type="text" class="form-control" name="nama" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">
                        NIM / NIP *
                        <small class="text-muted">(Mahasiswa: 23.11.0001)</small>
                    </label>
                    <input type="text" class="form-control" name="nim_nip" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Email (Opsional)</label>
                    <input type="email" class="form-control" name="email">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Role *</label>
                    <select class="form-select" name="role" required>
                        <option value="mahasiswa">Mahasiswa</option>
                        <option value="petugas">Petugas</option>
                        <option value="admin">Admin</option>
                    </select>
                </div>
            </div>

            <div class="mt-3 alert alert-info">
                <i class="fas fa-key me-1"></i>
                <strong>Password default:</strong> angka NIM/NIP (tanpa titik) + <code>123</code><br>
                Contoh: NIM <code>23.11.0001</code> → password <code>23110001123</code>
            </div>

            <div class="d-grid">
                <button type="submit" class="btn btn-success">
                    <i class="fas fa-user-plus me-1"></i> Tambah User
                </button>
            </div>
        </form>
    </div>
</div>

<!-- 📋 Daftar User -->
<?php if ($user_result->num_rows > 0): ?>
    <div class="card">
        <div class="card-header bg-primary text-white">
            <i class="fas fa-list me-2"></i> Daftar User (<?= $user_result->num_rows ?>)
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Nama</th>
                            <th>NIM/NIP</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Tgl Daftar</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $no = 1; while ($u = $user_result->fetch_assoc()): ?>
                            <tr>
                                <td><?= $no++ ?></td>
                                <td><?= htmlspecialchars($u['nama']) ?></td>
                                <td><code><?= htmlspecialchars($u['nim_nip']) ?></code></td>
                                <td><?= htmlspecialchars($u['email']) ?: '—' ?></td>
                                <td>
                                    <span class="badge bg-<?= 
                                        $u['role'] == 'admin' ? 'danger' : 
                                        ($u['role'] == 'petugas' ? 'info' : 'primary') 
                                    ?>">
                                        <?= ucfirst($u['role']) ?>
                                    </span>
                                </td>
                                <td><?= date('d M Y', strtotime($u['created_at'])) ?></td>
                                <td>
                                    <?php if ($u['id'] != $_SESSION['user_id']): ?>
                                        <a href="?reset=<?= $u['id'] ?>" 
                                           class="btn btn-sm btn-outline-secondary"
                                           title="Reset Password"
                                           onclick="return confirm('Reset password user <?= addslashes($u['nama']) ?>?')">
                                            <i class="fas fa-key"></i>
                                        </a>
                                        <a href="?hapus=<?= $u['id'] ?>" 
                                           class="btn btn-sm btn-outline-danger"
                                           title="Hapus"
                                           onclick="return confirm('Yakin hapus user: <?= addslashes($u['nama']) ?>?')">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    <?php else: ?>
                                        <span class="text-muted">—</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
<?php else: ?>
    <div class="card">
        <div class="card-body text-center py-5">
            <i class="fas fa-users fa-3x text-muted mb-3"></i>
            <h4>Belum ada user selain Anda.</h4>
        </div>
    </div>
<?php endif; ?>

<?php include '../../includes/footer.php'; ?>