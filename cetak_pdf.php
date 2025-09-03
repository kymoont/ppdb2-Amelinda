<?php
// File: cetak_pdf.php

session_start();
include_once __DIR__ . '/config/database.php';

// Cek jika user belum login
if (!isset($_SESSION['user_id'])) {
    die("Akses ditolak. Silakan login terlebih dahulu.");
}

// Include libraries
require(__DIR__ . '/libs/fpdf.php');
require(__DIR__ . '/libs/phpqrcode/qrlib.php');

$user_id = $_SESSION['user_id'];

// Ambil data pendaftaran lengkap
$query = "SELECT p.*, j.nama_jurusan FROM pendaftar p JOIN jurusan j ON p.jurusan_id = j.id WHERE p.user_id = ?";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$pendaftar = mysqli_fetch_assoc($result);

if (!$pendaftar) {
    die("Data pendaftaran tidak ditemukan.");
}

// --- GENERATE QR CODE ---
$qr_code_data = "No: " . $pendaftar['nomor_pendaftaran'] . "\nNama: " . $pendaftar['nama_lengkap'] . "\nStatus: " . $pendaftar['status_pendaftaran'];
$qr_code_temp_dir = __DIR__ . '/uploads/temp_qr/';
if (!file_exists($qr_code_temp_dir)) {
    mkdir($qr_code_temp_dir, 0777, true);
}
$qr_code_file = $qr_code_temp_dir . $pendaftar['nomor_pendaftaran'] . '.png';
QRcode::png($qr_code_data, $qr_code_file, QR_ECLEVEL_L, 4);

// --- GENERATE PDF ---
class PDF extends FPDF
{
    // Page header
    function Header()
    {
        $this->SetFont('Arial', 'B', 14);
        $this->Cell(0, 7, 'BUKTI PENDAFTARAN SISWA BARU', 0, 1, 'C');
        $this->SetFont('Arial', 'B', 12);
        $this->Cell(0, 7, 'TAHUN AJARAN 2025/2026', 0, 1, 'C');
        $this->Ln(5);
        $this->Line($this->GetX(), $this->GetY(), $this->GetX() + 190, $this->GetY());
        $this->Ln(5);
    }

    // Page footer
    function Footer()
    {
        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 8);
        $this->Cell(0, 10, 'Halaman ' . $this->PageNo() . '/{nb}', 0, 0, 'C');
    }

    function DataRow($label, $data)
    {
        $this->SetFont('Arial', 'B', 10);
        $this->Cell(50, 7, $label, 0, 0);
        $this->SetFont('Arial', '', 10);
        $this->Cell(5, 7, ':', 0, 0, 'C');
        $this->MultiCell(0, 7, $data, 0, 'L');
    }
}

$pdf = new PDF('P', 'mm', 'A4');
$pdf->AliasNbPages();
$pdf->AddPage();
$pdf->SetFont('Arial', '', 10);

// Data Pendaftar & QR Code
$pdf->Image($qr_code_file, 150, 35, 40, 40, 'PNG');

$pdf->DataRow('Nomor Pendaftaran', $pendaftar['nomor_pendaftaran']);
$pdf->DataRow('Nama Lengkap', $pendaftar['nama_lengkap']);
$pdf->DataRow('Tempat, Tanggal Lahir', $pendaftar['tempat_lahir'] . ', ' . date('d F Y', strtotime($pendaftar['tanggal_lahir'])));
$pdf->DataRow('Jenis Kelamin', $pendaftar['jenis_kelamin']);
$pdf->DataRow('Agama', $pendaftar['agama']);
$pdf->DataRow('Alamat', $pendaftar['alamat']);
$pdf->DataRow('Pilihan Jurusan', $pendaftar['nama_jurusan']);
$pdf->DataRow('Status Pendaftaran', $pendaftar['status_pendaftaran']);

$pdf->Ln(10);

// Data Orang Tua
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(0, 7, 'Data Orang Tua / Wali', 'B', 1);
$pdf->Ln(2);
$pdf->DataRow('Nama Ayah', $pendaftar['nama_ayah']);
$pdf->DataRow('Pekerjaan Ayah', $pendaftar['pekerjaan_ayah']);
$pdf->DataRow('Nama Ibu', $pendaftar['nama_ibu']);
$pdf->DataRow('Pekerjaan Ibu', $pendaftar['pekerjaan_ibu']);

$pdf->Ln(10);

// Catatan
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(0, 7, 'Catatan:', 0, 1);
$pdf->SetFont('Arial', 'I', 9);
$pdf->MultiCell(0, 5, "- Ini adalah bukti pendaftaran yang sah.\n- Simpan bukti ini untuk keperluan verifikasi dan daftar ulang.\n- Pengumuman hasil seleksi akan diinformasikan lebih lanjut melalui website.", 0, 'L');

// Hapus file QR code sementara
unlink($qr_code_file);

$pdf->Output('D', 'bukti_pendaftaran_' . $pendaftar['nomor_pendaftaran'] . '.pdf');

?>