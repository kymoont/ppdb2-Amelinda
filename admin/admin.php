<?php
// File: admin/admin.php

include 'templates/header.php';

// Hanya super admin (ID 1) yang bisa mengakses halaman ini
if ($_SESSION['admin_id'] != 1) {
    echo "<div class='alert alert-danger'>Anda tidak memiliki hak akses untuk halaman ini.</div>";
    include 'templates/footer.php';
    exit();
}

$message = '';

// --- LOGIKA CRUD ---
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'] ?? '';

    // Tambah Admin
    if ($action == 'add') {
        $username = mysqli_real_escape_string($conn, $_POST['username']);
        $nama_lengkap = mysqli_real_escape_string($conn, $_POST['nama_lengkap']);
        $password = $_POST['password'];
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        $stmt_check = mysqli_prepare($conn, "SELECT id FROM admin WHERE username = ?");
        mysqli_stmt_bind_param($stmt_check, "s", $username);
        mysqli_stmt_execute($stmt_check);
        if (mysqli_stmt_get_result($stmt_check)->num_rows > 0) {
            $message = '<div class="alert alert-danger">Username sudah ada. Silakan pilih username lain.</div>';
        } else {
            $stmt_add = mysqli_prepare($conn, "INSERT INTO admin (username, nama_lengkap, password) VALUES (?, ?, ?)");
            mysqli_stmt_bind_param($stmt_add, "sss", $username, $nama_lengkap, $hashed_password);
            if (mysqli_stmt_execute($stmt_add)) {
                $message = '<div class="alert alert-success">Admin baru berhasil ditambahkan.</div>';
            } else {
                $message = '<div class="alert alert-danger">Gagal menambahkan admin.</div>';
            }
        }
    }

    // Hapus Admin
    if ($action == 'delete') {
        $id_hapus = (int)$_POST['id'];
        if ($id_hapus == 1) {
            $message = '<div class="alert alert-danger">Super Admin tidak dapat dihapus.</div>';
        } else {
            $stmt_delete = mysqli_prepare($conn, "DELETE FROM admin WHERE id = ?");
            mysqli_stmt_bind_param($stmt_delete, "i", $id_hapus);
            if (mysqli_stmt_execute($stmt_delete)) {
                $message = '<div class="alert alert-success">Admin berhasil dihapus.</div>';
            } else {
                $message = '<div class="alert alert-danger">Gagal menghapus admin.</div>';
            }
        }
    }
}

// Ambil daftar admin
$list_admin = mysqli_query($conn, "SELECT id, username, nama_lengkap FROM admin");

?>

<div class="content-header">
    <h1>Manajemen Admin</h1>
    <p>Tambah atau hapus akun admin lain. Hanya Super Admin yang dapat mengakses halaman ini.</p>
</div>

<?php echo $message; ?>

<!-- Form Tambah Admin -->
<div class="card mb-4">
    <div class="card-header"><h4>Tambah Admin Baru</h4></div>
    <div class="card-body">
        <form action="admin.php" method="POST">
            <input type="hidden" name="action" value="add">
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label for="nama_lengkap" class="form-label">Nama Lengkap</label>
                    <input type="text" name="nama_lengkap" id="nama_lengkap" class="form-control" required>
                </div>
                <div class="col-md-4 mb-3">
                    <label for="username" class="form-label">Username</label>
                    <input type="text" name="username" id="username" class="form-control" required>
                </div>
                <div class="col-md-4 mb-3">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" name="password" id="password" class="form-control" required>
                </div>
            </div>
            <button type="submit" class="btn btn-primary">Tambah Admin</button>
        </form>
    </div>
</div>

<!-- Daftar Admin -->
<div class="card">
    <div class="card-header"><h4>Daftar Admin</h4></div>
    <div class="card-body">
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nama Lengkap</th>
                    <th>Username</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php while($admin = mysqli_fetch_assoc($list_admin)): ?>
                <tr>
                    <td><?php echo $admin['id']; ?></td>
                    <td><?php echo htmlspecialchars($admin['nama_lengkap']); ?></td>
                    <td><?php echo htmlspecialchars($admin['username']); ?></td>
                    <td>
                        <?php if ($admin['id'] == 1): ?>
                            <span class="text-muted">Super Admin</span>
                        <?php else: ?>
                            <form action="admin.php" method="POST" onsubmit="return confirm('Yakin ingin menghapus admin ini?');" style="display: inline;">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id" value="<?php echo $admin['id']; ?>">
                                <button type="submit" class="btn btn-sm btn-danger">Hapus</button>
                            </form>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include 'templates/footer.php'; ?>
