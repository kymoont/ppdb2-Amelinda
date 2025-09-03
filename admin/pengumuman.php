<?php
// File: admin/pengumuman.php

include 'templates/header.php';

$admin_id = $_SESSION['admin_id'];
$message = '';

// --- LOGIKA CRUD ---

// Hapus Pengumuman
if (isset($_GET['hapus'])) {
    $id_hapus = $_GET['hapus'];
    $stmt = mysqli_prepare($conn, "DELETE FROM pengumuman WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "i", $id_hapus);
    if (mysqli_stmt_execute($stmt)) {
        $message = '<div class="alert alert-success">Pengumuman berhasil dihapus.</div>';
    } else {
        $message = '<div class="alert alert-danger">Gagal menghapus pengumuman.</div>';
    }
}

// Tambah atau Edit Pengumuman
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = $_POST['id'] ?? null;
    $judul = mysqli_real_escape_string($conn, $_POST['judul']);
    $isi = mysqli_real_escape_string($conn, $_POST['isi']);

    if ($id) { // Edit
        $stmt = mysqli_prepare($conn, "UPDATE pengumuman SET judul = ?, isi = ? WHERE id = ?");
        mysqli_stmt_bind_param($stmt, "ssi", $judul, $isi, $id);
        $pesan_sukses = "diperbarui";
    } else { // Tambah
        $stmt = mysqli_prepare($conn, "INSERT INTO pengumuman (judul, isi, admin_id) VALUES (?, ?, ?)");
        mysqli_stmt_bind_param($stmt, "ssi", $judul, $isi, $admin_id);
        $pesan_sukses = "ditambahkan";
    }

    if (mysqli_stmt_execute($stmt)) {
        $message = "<div class='alert alert-success'>Pengumuman berhasil $pesan_sukses.</div>";
    } else {
        $message = "<div class='alert alert-danger'>Gagal memproses pengumuman.</div>";
    }
}

// Ambil data untuk form edit
$edit_data = null;
if (isset($_GET['edit'])) {
    $id_edit = $_GET['edit'];
    $stmt = mysqli_prepare($conn, "SELECT * FROM pengumuman WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "i", $id_edit);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $edit_data = mysqli_fetch_assoc($result);
}

// Ambil semua pengumuman
$list_pengumuman = mysqli_query($conn, "SELECT p.*, a.nama_lengkap FROM pengumuman p JOIN admin a ON p.admin_id = a.id ORDER BY p.tanggal_publish DESC");

?>

<div class="content-header">
    <h1>Manajemen Pengumuman</h1>
    <p>Buat, edit, atau hapus pengumuman untuk ditampilkan di halaman pendaftar.</p>
</div>

<?php echo $message; ?>

<!-- Form Tambah/Edit -->
<div class="card mb-4">
    <div class="card-header"><h4><?php echo $edit_data ? 'Edit' : 'Tambah'; ?> Pengumuman</h4></div>
    <div class="card-body">
        <form action="pengumuman.php" method="POST">
            <input type="hidden" name="id" value="<?php echo $edit_data['id'] ?? ''; ?>">
            <div class="mb-3">
                <label for="judul" class="form-label">Judul</label>
                <input type="text" name="judul" id="judul" class="form-control" value="<?php echo htmlspecialchars($edit_data['judul'] ?? ''); ?>" required>
            </div>
            <div class="mb-3">
                <label for="isi" class="form-label">Isi Pengumuman</label>
                <textarea name="isi" id="isi" class="form-control" rows="5" required><?php echo htmlspecialchars($edit_data['isi'] ?? ''); ?></textarea>
            </div>
            <button type="submit" class="btn btn-primary">Simpan</button>
            <?php if ($edit_data): ?>
                <a href="pengumuman.php" class="btn btn-secondary">Batal Edit</a>
            <?php endif; ?>
        </form>
    </div>
</div>

<!-- Daftar Pengumuman -->
<div class="card">
    <div class="card-header"><h4>Daftar Pengumuman</h4></div>
    <div class="card-body">
        <div class="list-group">
            <?php while($p = mysqli_fetch_assoc($list_pengumuman)): ?>
                <div class="list-group-item list-group-item-action flex-column align-items-start">
                    <div class="d-flex w-100 justify-content-between">
                        <h5 class="mb-1"><?php echo htmlspecialchars($p['judul']); ?></h5>
                        <small>Dipublish: <?php echo date('d M Y', strtotime($p['tanggal_publish'])); ?></small>
                    </div>
                    <p class="mb-1"><?php echo nl2br(htmlspecialchars($p['isi'])); ?></p>
                    <small>Oleh: <?php echo htmlspecialchars($p['nama_lengkap']); ?></small>
                    <div class="mt-2">
                        <a href="pengumuman.php?edit=<?php echo $p['id']; ?>" class="btn btn-sm btn-warning">Edit</a>
                        <a href="pengumuman.php?hapus=<?php echo $p['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Yakin ingin menghapus pengumuman ini?')">Hapus</a>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    </div>
</div>

<?php include 'templates/footer.php'; ?>
