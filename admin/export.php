<?php
// File: admin/export.php

// Bootstrap aplikasi
session_start();
include_once __DIR__ . '/../config/database.php';

// Hanya admin yang bisa akses
if (!isset($_SESSION['admin_id'])) {
    die("Akses ditolak.");
}

// --- LOGIKA FILTER DAN PENCARIAN (diambil dari pendaftar.php) ---
$where_clauses = [];
$params = [];
$types = '';

$search = $_GET['search'] ?? '';
if (!empty($search)) {
    $where_clauses[] = "(p.nama_lengkap LIKE ? OR p.nomor_pendaftaran LIKE ?)";
    $search_param = "%$search%";
    $params[] = &$search_param;
    $params[] = &$search_param;
    $types .= 'ss';
}

$status = $_GET['status'] ?? '';
if (!empty($status)) {
    $where_clauses[] = "p.status_pendaftaran = ?";
    $params[] = &$status;
    $types .= 's';
}

$jurusan_id_filter = $_GET['jurusan'] ?? '';
if (!empty($jurusan_id_filter)) {
    $where_clauses[] = "p.jurusan_id = ?";
    $params[] = &$jurusan_id_filter;
    $types .= 'i';
}

// Query untuk mengambil SEMUA data pendaftar sesuai filter
$sql = "SELECT p.*, j.nama_jurusan, u.email 
        FROM pendaftar p 
        JOIN jurusan j ON p.jurusan_id = j.id
        JOIN users u ON p.user_id = u.id";

if (!empty($where_clauses)) {
    $sql .= " WHERE " . implode(' AND ', $where_clauses);
}

$sql .= " ORDER BY p.tanggal_daftar DESC";

$stmt = mysqli_prepare($conn, $sql);
if (!empty($params)) {
    mysqli_stmt_bind_param($stmt, $types, ...$params);
}

mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

// --- GENERATE CSV ---
$filename = "laporan_pendaftar_" . date('Y-m-d') . ".csv";

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=' . $filename);

$output = fopen('php://output', 'w');

// Header kolom
fputcsv($output, [
    'Nomor Pendaftaran', 'Tanggal Daftar', 'Nama Lengkap', 'Email', 'NIK', 'Tempat Lahir', 'Tanggal Lahir', 'Jenis Kelamin', 'Alamat', 'Agama', 
    'Nama Ayah', 'Pekerjaan Ayah', 'Nama Ibu', 'Pekerjaan Ibu', 
    'Nilai NEM', 'Pilihan Jurusan', 'Status Pendaftaran', 'Catatan Admin'
]);

// Data baris
if (mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        fputcsv($output, [
            $row['nomor_pendaftaran'],
            $row['tanggal_daftar'],
            $row['nama_lengkap'],
            $row['email'],
            $row['nik'],
            $row['tempat_lahir'],
            $row['tanggal_lahir'],
            $row['jenis_kelamin'],
            $row['alamat'],
            $row['agama'],
            $row['nama_ayah'],
            $row['pekerjaan_ayah'],
            $row['nama_ibu'],
            $row['pekerjaan_ibu'],
            $row['nilai_nem'],
            $row['nama_jurusan'],
            $row['status_pendaftaran'],
            $row['catatan_admin']
        ]);
    }
}

fclose($output);
exit();
?>
