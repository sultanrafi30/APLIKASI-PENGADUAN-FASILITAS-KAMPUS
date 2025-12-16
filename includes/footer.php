<?php
// includes/footer.php
?>

</div> <!-- penutup .container dari header.php -->

<!-- Bootstrap 5 JS (CDN) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<!-- SweetAlert2 (CDN) -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<!-- Custom JS -->
<script src="../assets/js/script.js"></script>

<script>
// ✅ Aktifkan toast otomatis saat halaman load
document.addEventListener('DOMContentLoaded', function() {
    const successMsg = document.getElementById('swal-success-msg');
    const errorMsg = document.getElementById('swal-error-msg');
    
    if (successMsg) {
        Swal.fire({
            toast: true,
            position: 'top-end',
            icon: 'success',
            title: successMsg.value,
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true
        });
    }
    if (errorMsg) {
        Swal.fire({
            toast: true,
            position: 'top-end',
            icon: 'error',
            title: errorMsg.value,
            showConfirmButton: false,
            timer: 3000
        });
    }
});
</script>

<!-- 🎯 Footer -->
<footer class="bg-light text-center py-3 mt-5 border-top">
    <small class="text-muted">
        &copy; <?= date('Y') ?> Pengaduan Fasilitas Kampus Polmed
    </small>
</footer>

<!-- 🔔 Hidden input untuk toast notifikasi -->
<?php if (isset($_SESSION['swal_success'])): ?>
    <input type="hidden" id="swal-success-msg" value="<?= htmlspecialchars($_SESSION['swal_success']) ?>">
    <?php unset($_SESSION['swal_success']); ?>
<?php endif; ?>
<?php if (isset($_SESSION['swal_error'])): ?>
    <input type="hidden" id="swal-error-msg" value="<?= htmlspecialchars($_SESSION['swal_error']) ?>">
    <?php unset($_SESSION['swal_error']); ?>
<?php endif; ?>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Data dari PHP ke JavaScript
    const bulanLabels = <?= json_encode($bulan_labels) ?>;
    const bulanData = <?= json_encode($bulan_data) ?>;
    const statusLabels = <?= json_encode(['Menunggu', 'Diproses', 'Selesai', 'Ditolak']) ?>;
    const statusData = <?= json_encode($status_data) ?>;
    
    // Inisialisasi grafik
    document.addEventListener('DOMContentLoaded', function() {
        // Grafik Pengaduan per Bulan
        const ctx1 = document.getElementById('pengaduanChart').getContext('2d');
    new Chart(ctx1, {
        type: 'line',
        data: {
            labels: bulanLabels,
            datasets: [{
                label: 'Jumlah Pengaduan',
                data: bulanData,
                borderColor: '#1e3a8a',
                backgroundColor: 'rgba(30, 58, 138, 0.1)',
                borderWidth: 2,
                fill: true,
                tension: 0.3
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: { stepSize: 1 }
                }
            }
        }
    });

    // Grafik Status
    const ctx2 = document.getElementById('statusChart').getContext('2d');
    new Chart(ctx2, {
        type: 'pie',
        data: {
            labels: statusLabels,
            datasets: [{
                data: statusData,
                backgroundColor: ['#ffc107', '#17a2b8', '#28a745', '#dc3545'],
                borderWidth: 2
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'right' }
            }
        }
    });
});
</script>


</body>

</html>