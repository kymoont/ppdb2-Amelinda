<?php
// File: admin/index.php

include 'templates/header.php';

// --- AMBIL DATA STATISTIK ---
// Total Pendaftar
$total_pendaftar = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(id) as total FROM pendaftar"))['total'];

// Diterima
$total_diterima = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(id) as total FROM pendaftar WHERE status_pendaftaran = 'Diterima'"))['total'];

// Ditolak
$total_ditolak = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(id) as total FROM pendaftar WHERE status_pendaftaran = 'Ditolak'"))['total'];

// Pending
$total_pending = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(id) as total FROM pendaftar WHERE status_pendaftaran = 'Pending'"))['total'];

// Pendaftar per jurusan
$pendaftar_per_jurusan = mysqli_query($conn, "SELECT j.nama_jurusan, COUNT(p.id) as jumlah FROM jurusan j LEFT JOIN pendaftar p ON j.id = p.jurusan_id GROUP BY j.id ORDER BY j.nama_jurusan");

?>

<div class="content-header">
    <h1>Dashboard</h1>
    <p>Selamat datang, <?php echo htmlspecialchars($_SESSION['admin_nama']); ?>!</p>
</div>

<h4>Statistik Pendaftar</h4>
<div class="row">
    <div class="col-md-3 mb-3">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title text-muted">Total Pendaftar</h5>
                <p class="card-text fs-2 fw-bold text-primary"><?php echo $total_pendaftar; ?></p>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title text-muted">Diterima</h5>
                <p class="card-text fs-2 fw-bold text-success"><?php echo $total_diterima; ?></p>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title text-muted">Ditolak</h5>
                <p class="card-text fs-2 fw-bold text-danger"><?php echo $total_ditolak; ?></p>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title text-muted">Pending</h5>
                <p class="card-text fs-2 fw-bold text-warning"><?php echo $total_pending; ?></p>
            </div>
        </div>
    </div>
</div>

<div class="card mt-4">
    <div class="card-header">
        <h4>Pendaftar per Jurusan</h4>
    </div>
    <div class="card-body">
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Nama Jurusan</th>
                    <th>Jumlah Pendaftar</th>
                </tr>
            </thead>
            <tbody>
                <?php while($jurusan = mysqli_fetch_assoc($pendaftar_per_jurusan)): ?>
                <tr>
                    <td><?php echo htmlspecialchars($jurusan['nama_jurusan']); ?></td>
                    <td><?php echo $jurusan['jumlah']; ?></td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include 'templates/footer.php'; ?>
