<?php
// File: cek_status.php

include 'templates/header.php';

// Cek jika user belum login, redirect ke halaman login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Ambil data pendaftaran user dari database
$query = "SELECT p.*, j.nama_jurusan FROM pendaftar p JOIN jurusan j ON p.jurusan_id = j.id WHERE p.user_id = ?";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$pendaftar = mysqli_fetch_assoc($result);

?>

<div class="card">
    <div class="card-header">
        <h3 class="text-center">Status Pendaftaran Anda</h3>
    </div>
    <div class="card-body">
        <?php if ($pendaftar): ?>
            <div class="row mb-3">
                <label class="col-sm-4 col-form-label">Nomor Pendaftaran</label>
                <div class="col-sm-8">
                    <input type="text" readonly class="form-control-plaintext" value=": <?php echo htmlspecialchars($pendaftar['nomor_pendaftaran']); ?>">
                </div>
            </div>
            <div class="row mb-3">
                <label class="col-sm-4 col-form-label">Nama Lengkap</label>
                <div class="col-sm-8">
                    <input type="text" readonly class="form-control-plaintext" value=": <?php echo htmlspecialchars($pendaftar['nama_lengkap']); ?>">
                </div>
            </div>
            <div class="row mb-3">
                <label class="col-sm-4 col-form-label">Pilihan Jurusan</label>
                <div class="col-sm-8">
                    <input type="text" readonly class="form-control-plaintext" value=": <?php echo htmlspecialchars($pendaftar['nama_jurusan']); ?>">
                </div>
            </div>
            <hr>
            <div class="row mb-3">
                <label class="col-sm-4 col-form-label">Status Pendaftaran</label>
                <div class="col-sm-8">
                    <?php
                        $status = $pendaftar['status_pendaftaran'];
                        $badge_class = 'bg-secondary';
                        if ($status == 'Diterima') {
                            $badge_class = 'bg-success';
                        } elseif ($status == 'Ditolak') {
                            $badge_class = 'bg-danger';
                        } elseif ($status == 'Pending') {
                            $badge_class = 'bg-warning';
                        }
                    ?>
                    <h4><span class="badge <?php echo $badge_class; ?>"><?php echo htmlspecialchars($status); ?></span></h4>
                </div>
            </div>

            <?php if (!empty($pendaftar['catatan_admin'])): ?>
            <div class="row mb-3">
                <label class="col-sm-4 col-form-label">Komentar dari Admin</label>
                <div class="col-sm-8">
                    <div class="alert alert-info">
                        <?php echo nl2br(htmlspecialchars($pendaftar['catatan_admin'])); ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>

        <?php else: ?>
            <div class="alert alert-warning">
                Anda belum mengisi formulir pendaftaran. Silakan <a href="formulir.php" class="alert-link">isi formulir</a> terlebih dahulu.
            </div>
        <?php endif; ?>
    </div>
    <div class="card-footer text-center">
        <a href="index.php" class="btn btn-secondary">Kembali ke Dashboard</a>
        <?php if ($pendaftar): ?>
        <a href="cetak_bukti.php" class="btn btn-primary">Cetak Bukti Pendaftaran</a>
        <?php endif; ?>
    </div>
</div>

<?php include 'templates/footer.php'; ?>
