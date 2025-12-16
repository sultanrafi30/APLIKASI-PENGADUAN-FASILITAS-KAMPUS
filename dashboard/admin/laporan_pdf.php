<?php
// dashboard/admin/laporan_pdf.php
require_once '../../config/database.php';
require_once '../../includes/auth_check.php';
auth_check('admin');

// ✅ LOAD AUTOLOAD COMPOSER (1x saja)
require_once '../../vendor/autoload.php';

// Ambil filter
$tgl_awal = $_GET['tgl_awal'] ?? date('Y-m-d', strtotime('-30 days'));
$tgl_akhir = $_GET['tgl_akhir'] ?? date('Y-m-d');
$status = $_GET['status'] ?? 'semua';
$fasilitas_id = $_GET['fasilitas'] ?? '';

// Query data
$sql = "
    SELECT p.no_tiket, p.judul, p.status, p.created_at, p.updated_at,
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

$sql .= " ORDER BY p.created_at DESC";

$stmt = $koneksi->prepare($sql);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();

// ✅ INISIALISASI MPDF v8+ (HANYA 1X, TANPA DUPlikat!)
$mpdf = new \Mpdf\Mpdf([
    'mode' => 'utf-8',
    'format' => 'A4',
    'margin_left' => 15,
    'margin_right' => 15,
    'margin_top' => 25,
    'margin_bottom' => 20
]);

// ✅ Header & Footer Halaman PDF
$mpdf->SetHTMLHeader('
<div style="border-bottom:1px solid #ccc; padding-bottom:5px;">
    <table width="100%">
        <tr>
            <td width="20%"><img src="Location: uploads/img/index.php"></td>
            <td width="60%" style="text-align:center; font-size:10pt; color:#333;">
                Laporan Pengaduan Fasilitas<br>
                Politeknik Negeri Medan
            </td>
            <td width="20%" style="text-align:right; font-size:9pt;">Hal. {PAGENO}</td>
        </tr>
    </table>
</div>
');

$mpdf->SetHTMLFooter('
<div style="border-top:1px solid #ccc; padding-top:5px; font-size:8pt; color:#666; text-align:center;">
    Dicetak tanggal ' . date('d-m-Y') . ' | Sistem Pengaduan Polmed v1.0
</div>
');

// Siapkan data
$periode = date('d M Y', strtotime($tgl_awal)) . ' – ' . date('d M Y', strtotime($tgl_akhir));
$jumlah_data = $result->num_rows;

// Konten HTML
$html = '
<h2 style="text-align:center; margin-bottom:5px;">POLITEKNIK NEGERI MEDAN</h2>
<h4 style="text-align:center; margin-bottom:20px; border-bottom:2px solid #000; padding-bottom:10px;">
LAPORAN PENGADUAN FASILITAS KAMPUS
</h4>
<div style="margin-bottom:20px;">
    <table width="100%" style="border:none;">
        <tr>
            <td width="70%">Periode: <strong>' . $periode . '</strong></td>
            <td width="30%" style="text-align:right;">Dicetak: ' . date('d F Y H:i') . '</td>
        </tr>
    </table>
</div>
';

if ($jumlah_data > 0) {
    $html .= '
    <table>
        <thead style="background-color:#f2f2f2;">
            <tr>
                <th width="5%">No</th>
                <th width="15%">No. Tiket</th>
                <th width="20%">Fasilitas</th>
                <th width="25%">Judul</th>
                <th width="15%">Status</th>
                <th width="20%">Tanggal</th>
            </tr>
        </thead>
        <tbody>';

    $no = 1;
    while ($row = $result->fetch_assoc()) {
        $status_color = match($row['status']) {
            'selesai' => '#28a745',
            'ditolak' => '#dc3545',
            'diproses' => '#17a2b8',
            default => '#ffc107'
        };

        $html .= '
        <tr>
            <td align="center">' . $no++ . '</td>
            <td>' . htmlspecialchars($row['no_tiket']) . '</td>
            <td>' . htmlspecialchars($row['fasilitas']) . '</td>
            <td>' . htmlspecialchars($row['judul']) . '</td>
            <td style="color:' . $status_color . '; font-weight:bold;">' . ucfirst($row['status']) . '</td>
            <td>' . date('d-m-Y', strtotime($row['created_at'])) . '</td>
        </tr>';
    }

    $html .= '
        </tbody>
    </table>
    <div style="margin-top:30px;">
        <table width="100%">
            <tr>
                <td width="50%"></td>
                <td width="50%" style="text-align:center;">
                    Medan, ' . date('d F Y') . '<br><br><br>
                    <div style="border-top:1px solid #000; padding-top:5px;">
                        <strong>Kepala Bagian Umum</strong>
                    </div>
                </td>
            </tr>
        </table>
    </div>';
} else {
    $html .= '<p style="text-align:center; color:red; font-weight:bold;">Tidak ada data sesuai filter.</p>';
}

$html .= '
<div style="margin-top:40px; font-size:10pt; color:#666; border-top:1px solid #ddd; padding-top:10px;">
    <p style="text-align:center; margin:0;">
        Laporan ini dihasilkan oleh Sistem Pengaduan Fasilitas Polmed<br>
        &copy; ' . date('Y') . ' Politeknik Negeri Medan
    </p>
</div>';

// Render PDF
$mpdf->WriteHTML($html);

// Output
if (isset($_GET['download']) && $_GET['download'] == 1) {
    $mpdf->Output('laporan_pengaduan_' . date('Y-m-d') . '.pdf', 'D');
} else {
    $mpdf->Output('laporan.pdf', 'I'); // 'I' = inline (tampil di browser)
}


?>