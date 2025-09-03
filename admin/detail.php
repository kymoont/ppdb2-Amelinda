<?php
// File: admin/detail.php

include 'templates/header.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo "<div class='alert alert-danger'>ID Pendaftar tidak valid.</div>";
    include 'templates/footer.php';
    exit();
}
$pendaftar_id = $_GET['id'];

// --- LOGIKA UPDATE STATUS ---
$success_message = '';
$error_message = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $status_baru = mysqli_real_escape_string($conn, $_POST['status_pendaftaran']);
    $catatan_admin = mysqli_real_escape_string($conn, $_POST['catatan_admin']);
    $admin_id = $_SESSION['admin_id'];

    mysqli_begin_transaction($conn);
    try {
        // Update status di tabel pendaftar
        $query_update = "UPDATE pendaftar SET status_pendaftaran = ?, catatan_admin = ? WHERE id = ?";
        $stmt_update = mysqli_prepare($conn, $query_update);
        mysqli_stmt_bind_param($stmt_update, "ssi", $status_baru, $catatan_admin, $pendaftar_id);
        mysqli_stmt_execute($stmt_update);

        // Catat ke log verifikasi
        $aksi = "Mengubah status menjadi '" . $status_baru . "' dengan catatan: " . $catatan_admin;
        $query_log = "INSERT INTO log_verifikasi (pendaftar_id, admin_id, aksi) VALUES (?, ?, ?)";
        $stmt_log = mysqli_prepare($conn, $query_log);
        mysqli_stmt_bind_param($stmt_log, "iis", $pendaftar_id, $admin_id, $aksi);
        mysqli_stmt_execute($stmt_log);

        mysqli_commit($conn);
        $success_message = "Status pendaftaran berhasil diperbarui!";

    } catch (Exception $e) {
        mysqli_rollback($conn);
        $error_message = "Gagal memperbarui status: " . $e->getMessage();
    }
}

// --- AMBIL DATA LENGKAP PENDAFTAR ---
$query_detail = "SELECT p.*, j.nama_jurusan FROM pendaftar p JOIN jurusan j ON p.jurusan_id = j.id WHERE p.id = ?";
$stmt_detail = mysqli_prepare($conn, $query_detail);
mysqli_stmt_bind_param($stmt_detail, "i", $pendaftar_id);
mysqli_stmt_execute($stmt_detail);
$result_detail = mysqli_stmt_get_result($stmt_detail);
$pendaftar = mysqli_fetch_assoc($result_detail);

if (!$pendaftar) {
    echo "<div class='alert alert-danger'>Data pendaftar tidak ditemukan.</div>";
    include 'templates/footer.php';
    exit();
}

// Ambil data berkas
$query_berkas = "SELECT * FROM berkas WHERE pendaftar_id = ?";
$stmt_berkas = mysqli_prepare($conn, $query_berkas);
mysqli_stmt_bind_param($stmt_berkas, "i", $pendaftar_id);
mysqli_stmt_execute($stmt_berkas);
$result_berkas = mysqli_stmt_get_result($stmt_berkas);

?>

<a href="pendaftar.php" class="btn btn-secondary mb-3"><< Kembali ke Daftar</a>

<?php if($success_message) echo "<div class='alert alert-success'>$success_message</div>"; ?>
<?php if($error_message) echo "<div class='alert alert-danger'>$error_message</div>"; ?>

<div class="row">
    <!-- Kolom Kiri: Detail Pendaftar -->
    <div class="col-md-8">
        <div class="card">
            <div class="card-header"><h4>Detail Pendaftar</h4></div>
            <div class="card-body">
                <table class="table table-striped">
                    <tr><th>No. Pendaftaran</th><td><?php echo htmlspecialchars($pendaftar['nomor_pendaftaran']); ?></td></tr>
                    <tr><th>Nama Lengkap</th><td><?php echo htmlspecialchars($pendaftar['nama_lengkap']); ?></td></tr>
                    <tr><th>NIK</th><td><?php echo htmlspecialchars($pendaftar['nik']); ?></td></tr>
                    <tr><th>TTL</th><td><?php echo htmlspecialchars($pendaftar['tempat_lahir'] . ', ' . date('d-m-Y', strtotime($pendaftar['tanggal_lahir']))); ?></td></tr>
                    <tr><th>Jenis Kelamin</th><td><?php echo htmlspecialchars($pendaftar['jenis_kelamin']); ?></td></tr>
                    <tr><th>Alamat</th><td><?php echo htmlspecialchars($pendaftar['alamat']); ?></td></tr>
                    <tr><th>Agama</th><td><?php echo htmlspecialchars($pendaftar['agama']); ?></td></tr>
                    <tr><th>Pilihan Jurusan</th><td><?php echo htmlspecialchars($pendaftar['nama_jurusan']); ?></td></tr>
                    <tr><th>Nilai NEM</th><td><?php echo htmlspecialchars($pendaftar['nilai_nem']); ?></td></tr>
                    <tr><th>Nama Ayah</th><td><?php echo htmlspecialchars($pendaftar['nama_ayah']); ?></td></tr>
                    <tr><th>Pekerjaan Ayah</th><td><?php echo htmlspecialchars($pendaftar['pekerjaan_ayah']); ?></td></tr>
                    <tr><th>Nama Ibu</th><td><?php echo htmlspecialchars($pendaftar['nama_ibu']); ?></td></tr>
                    <tr><th>Pekerjaan Ibu</th><td><?php echo htmlspecialchars($pendaftar['pekerjaan_ibu']); ?></td></tr>
                </table>
            </div>
        </div>
    </div>

    <!-- Kolom Kanan: Verifikasi & Berkas -->
    <div class="col-md-4">
        <div class="card mb-4">
            <div class="card-header"><h4>Verifikasi Pendaftaran</h4></div>
            <div class="card-body">
                <form action="detail.php?id=<?php echo $pendaftar_id; ?>" method="POST">
                    <div class="mb-3">
                        <label for="status_pendaftaran" class="form-label">Ubah Status</label>
                        <select name="status_pendaftaran" id="status_pendaftaran" class="form-select">
                            <option value="Pending" <?php if($pendaftar['status_pendaftaran'] == 'Pending') echo 'selected'; ?>>Pending</option>
                            <option value="Diterima" <?php if($pendaftar['status_pendaftaran'] == 'Diterima') echo 'selected'; ?>>Diterima</option>
                            <option value="Ditolak" <?php if($pendaftar['status_pendaftaran'] == 'Ditolak') echo 'selected'; ?>>Ditolak</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="catatan_admin" class="form-label">Catatan untuk Siswa (Opsional)</label>
                        <textarea name="catatan_admin" id="catatan_admin" class="form-control" rows="4"><?php echo htmlspecialchars($pendaftar['catatan_admin']); ?></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Simpan Perubahan</button>
                </form>
            </div>
        </div>

        <div class="card">
            <div class="card-header"><h4>Berkas Terlampir</h4></div>
            <div class="card-body">
                <ul class="list-group list-group-flush">
                    <?php while($berkas = mysqli_fetch_assoc($result_berkas)): ?>
                        <li class="list-group-item">
                            <a href="../uploads/<?php echo htmlspecialchars($berkas['nama_file']); ?>" target="_blank">
                                <?php echo htmlspecialchars($berkas['jenis_berkas']); ?>
                            </a>
                        </li>
                    <?php endwhile; ?>
                </ul>
            </div>
        </div>
    </div>
</div>

<?php include 'templates/footer.php'; ?>
