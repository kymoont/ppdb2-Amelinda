<?php
// File: admin/login.php

include 'templates/header.php';

// Jika sudah login, redirect ke dashboard admin
if (isset($_SESSION['admin_id'])) {
    header("Location: index.php");
    exit();
}

// --- AUTO-SEED ADMIN (HANYA JIKA KOSONG) ---
$result_admin = mysqli_query($conn, "SELECT id FROM admin");
if (mysqli_num_rows($result_admin) == 0) {
    $username = 'admin';
    $password = password_hash('password', PASSWORD_DEFAULT);
    $nama_lengkap = 'Administrator';
    mysqli_query($conn, "INSERT INTO admin (username, password, nama_lengkap) VALUES ('$username', '$password', '$nama_lengkap')");
}
// --- END OF SEED ---

$error_message = '';
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = $_POST['password'];

    $query = "SELECT * FROM admin WHERE username = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "s", $username);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if ($admin = mysqli_fetch_assoc($result)) {
        if (password_verify($password, $admin['password'])) {
            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['admin_username'] = $admin['username'];
            $_SESSION['admin_nama'] = $admin['nama_lengkap'];
            header("Location: index.php");
            exit();
        } else {
            $error_message = "Username atau password salah.";
        }
    } else {
        $error_message = "Username atau password salah.";
    }
}
?>

<div class="container">
    <div class="row justify-content-center align-items-center" style="height: 100vh;">
        <div class="col-md-5">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h3 class="text-center">Admin Login</h3>
                </div>
                <div class="card-body p-4">
                    <p class="text-center text-muted">Gunakan <strong>admin</strong> / <strong>password</strong> untuk login pertama kali.</p>
                    <?php if(!empty($error_message)): ?>
                        <div class="alert alert-danger"><?php echo $error_message; ?></div>
                    <?php endif; ?>
                    <form action="login.php" method="POST">
                        <div class="mb-3">
                            <label for="username" class="form-label">Username</label>
                            <input type="text" class="form-control" id="username" name="username" required>
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label">Password</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                        </div>
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary">Login</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'templates/footer.php'; ?>
