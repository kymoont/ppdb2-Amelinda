<?php
// File: index.php

include 'templates/header.php';

// Cek jika user belum login, redirect ke halaman login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$nama_lengkap = $_SESSION['nama_lengkap'];

// Cek apakah pendaftar sudah mengisi formulir atau belum
$query_pendaftar = "SELECT id FROM pendaftar WHERE user_id = ?";
$stmt = mysqli_prepare($conn, $query_pendaftar);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$result_pendaftar = mysqli_stmt_get_result($stmt);
$sudah_mendaftar = mysqli_num_rows($result_pendaftar) > 0;

// Ambil pengumuman terbaru
$query_pengumuman = "SELECT * FROM pengumuman ORDER BY tanggal_publish DESC LIMIT 1";
$hasil_pengumuman = mysqli_query($conn, $query_pengumuman);
$pengumuman = mysqli_fetch_assoc($hasil_pengumuman);

?>

<?php if ($pengumuman): ?>
<div class="alert alert-info alert-dismissible fade show" role="alert">
  <h4 class="alert-heading">Pengumuman!</h4>
  <strong><?php echo htmlspecialchars($pengumuman['judul']); ?></strong>
  <p><?php echo nl2br(htmlspecialchars($pengumuman['isi'])); ?></p>
  <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
<?php endif; ?>

<div class="card">
    <div class="card-header">
        <h3>Dashboard Pendaftar</h3>
    </div>
    <div class="card-body">
        <h4>Selamat Datang, <?php echo htmlspecialchars($nama_lengkap); ?>!</h4>
        <p>Ini adalah halaman dashboard Anda. Silakan kelola pendaftaran Anda melalui menu di bawah ini.</p>
        
        <?php if ($sudah_mendaftar): ?>
            <div class="alert alert-success">
                Anda sudah melakukan pendaftaran. Anda dapat memeriksa status pendaftaran Anda.
            </div>
        <?php else: ?>
            <div class="alert alert-info">
                Anda belum mengisi formulir pendaftaran. Silakan isi formulir untuk melanjutkan.
            </div>
        <?php endif; ?>

        <div class="list-group">
            <a href="formulir.php" class="list-group-item list-group-item-action <?php if ($sudah_mendaftar) echo 'disabled'; ?>">Isi Formulir Pendaftaran</a>
            <a href="cek_status.php" class="list-group-item list-group-item-action">Cek Status Pendaftaran</a>
            <a href="cetak_bukti.php" class="list-group-item list-group-item-action <?php if (!$sudah_mendaftar) echo 'disabled'; ?>">Cetak Bukti Pendaftaran</a>
        </div>
    </div>
</div>

<?php include 'templates/footer.php'; ?>
