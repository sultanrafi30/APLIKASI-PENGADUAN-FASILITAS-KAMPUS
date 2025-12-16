// assets/js/chart.js
document.addEventListener('DOMContentLoaded', function() {
    // 🔹 Grafik Pengaduan per Bulan (untuk admin)
    const pengaduanChartCtx = document.getElementById('pengaduanChart');
    if (pengaduanChartCtx) {
        // Data dummy — nanti diganti dari PHP
        const labels = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun'];
        const data = {
            labels: labels,
            datasets: [{
                label: 'Pengaduan Masuk',
                data: [12, 19, 15, 22, 18, 25],
                backgroundColor: 'rgba(30, 58, 138, 0.2)',
                borderColor: '#1e3a8a',
                borderWidth: 2,
                fill: true,
                tension: 0.3
            }]
        };

        new Chart(pengaduanChartCtx, {
            type: 'line',
            data: data,
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: true,
                        position: 'top',
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 5
                        }
                    }
                }
            }
        });
    }

    // 🔹 Grafik Status Pengaduan (Pie Chart)
    const statusChartCtx = document.getElementById('statusChart');
    if (statusChartCtx) {
        const data = {
            labels: ['Selesai', 'Diproses', 'Menunggu', 'Ditolak'],
            datasets: [{
                data: [65, 20, 10, 5],
                backgroundColor: [
                    '#28a745', // selesai
                    '#17a2b8', // diproses
                    '#ffc107', // menunggu
                    '#dc3545'  // ditolak
                ],
                borderWidth: 2
            }]
        };

        new Chart(statusChartCtx, {
            type: 'pie',
            data: data,
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'right',
                    }
                }
            }
        });
    }
});