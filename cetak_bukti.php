<?php
// File: cetak_bukti.php

include 'templates/header.php';

// Cek jika user belum login, redirect ke halaman login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Ambil data pendaftaran lengkap
$query = "SELECT p.*, j.nama_jurusan FROM pendaftar p JOIN jurusan j ON p.jurusan_id = j.id WHERE p.user_id = ?";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$pendaftar = mysqli_fetch_assoc($result);

if (!$pendaftar) {
    // Jika belum mendaftar, redirect atau tampilkan pesan
    header("Location: index.php"); // atau tampilkan pesan error
    exit();
}

// Hasilkan QR Code jika belum ada
require(__DIR__ . '/libs/phpqrcode/qrlib.php');
$qr_code_temp_dir = __DIR__ . '/uploads/temp_qr/';
if (!file_exists($qr_code_temp_dir)) {
    mkdir($qr_code_temp_dir, 0777, true);
}
$qr_code_file_path = $qr_code_temp_dir . $pendaftar['nomor_pendaftaran'] . '.png';
$qr_code_url = 'uploads/temp_qr/' . $pendaftar['nomor_pendaftaran'] . '.png';

if (!file_exists($qr_code_file_path)) {
    $qr_code_data = "No: " . $pendaftar['nomor_pendaftaran'] . "\nNama: " . $pendaftar['nama_lengkap'] . "\nStatus: " . $pendaftar['status_pendaftaran'];
    QRcode::png($qr_code_data, $qr_code_file_path, QR_ECLEVEL_L, 4);
}

?>

<style>
    .print-area {
        border: 1px solid #ccc;
        padding: 20px;
    }
    .print-header {
        text-align: center;
        margin-bottom: 20px;
    }
    .data-table th, .data-table td {
        padding: 5px;
        vertical-align: top;
    }
    .data-table th {
        width: 30%;
    }
    @media print {
        body * {
            visibility: hidden;
        }
        .print-area, .print-area * {
            visibility: visible;
        }
        .print-area {
            position: absolute;
            left: 0;
            top: 0;
            width: 100%;
        }
        .no-print {
            display: none;
        }
    }
</style>

<div class="card">
    <div class="card-header">
        <h3 class="text-center">Bukti Pendaftaran</h3>
    </div>
    <div class="card-body">
        <div class="print-area" id="printArea">
            <div class="print-header">
                <h4>BUKTI PENDAFTARAN SISWA BARU</h4>
                <h5>TAHUN AJARAN 2025/2026</h5>
                <hr>
            </div>
            <div class="row">
                <div class="col-md-8">
                    <table class="table table-borderless data-table">
                        <tr><th>Nomor Pendaftaran</th><td>: <?php echo htmlspecialchars($pendaftar['nomor_pendaftaran']); ?></td></tr>
                        <tr><th>Nama Lengkap</th><td>: <?php echo htmlspecialchars($pendaftar['nama_lengkap']); ?></td></tr>
                        <tr><th>Tempat, Tanggal Lahir</th><td>: <?php echo htmlspecialchars($pendaftar['tempat_lahir'] . ', ' . date('d F Y', strtotime($pendaftar['tanggal_lahir']))); ?></td></tr>
                        <tr><th>Jenis Kelamin</th><td>: <?php echo htmlspecialchars($pendaftar['jenis_kelamin']); ?></td></tr>
                        <tr><th>Agama</th><td>: <?php echo htmlspecialchars($pendaftar['agama']); ?></td></tr>
                        <tr><th>Alamat</th><td>: <?php echo htmlspecialchars($pendaftar['alamat']); ?></td></tr>
                        <tr><th>Pilihan Jurusan</th><td>: <?php echo htmlspecialchars($pendaftar['nama_jurusan']); ?></td></tr>
                    </table>
                </div>
                <div class="col-md-4 text-center">
                    <img src="<?php echo $qr_code_url; ?>" alt="QR Code Pendaftaran" style="width: 150px; height: 150px;">
                    <p class="mt-2">Status: <strong><?php echo htmlspecialchars($pendaftar['status_pendaftaran']); ?></strong></p>
                </div>
            </div>
            <div class="mt-4">
                <p><strong>Data Orang Tua/Wali:</strong></p>
                <table class="table table-sm table-bordered">
                    <thead><tr><th></th><th>Ayah</th><th>Ibu</th></tr></thead>
                    <tbody>
                        <tr><th>Nama</th><td><?php echo htmlspecialchars($pendaftar['nama_ayah']); ?></td><td><?php echo htmlspecialchars($pendaftar['nama_ibu']); ?></td></tr>
                        <tr><th>Pekerjaan</th><td><?php echo htmlspecialchars($pendaftar['pekerjaan_ayah']); ?></td><td><?php echo htmlspecialchars($pendaftar['pekerjaan_ibu']); ?></td></tr>
                    </tbody>
                </table>
            </div>
            <div class="mt-4">
                <p><strong>Catatan:</strong></p>
                <ul>
                    <li>Ini adalah bukti pendaftaran yang sah.</li>
                    <li>Simpan bukti ini untuk keperluan verifikasi dan daftar ulang.</li>
                    <li>Pengumuman hasil seleksi akan diinformasikan lebih lanjut.</li>
                </ul>
            </div>
        </div>
    </div>
    <div class="card-footer text-center no-print">
        <button class="btn btn-success" onclick="window.print();">Cetak Halaman Ini</button>
        <a href="cetak_pdf.php" class="btn btn-danger">Unduh sebagai PDF</a>
        <a href="index.php" class="btn btn-secondary">Kembali ke Dashboard</a>
    </div>
</div>

<?php include 'templates/footer.php'; ?>
