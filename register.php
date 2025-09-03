<?php
// File: register.php

include 'templates/header.php';

$error_message = '';
$success_message = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nama_lengkap = mysqli_real_escape_string($conn, $_POST['nama_lengkap']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $nisn = mysqli_real_escape_string($conn, $_POST['nisn']);
    $password = $_POST['password'];
    $password_confirm = $_POST['password_confirm'];

    if ($password !== $password_confirm) {
        $error_message = "Konfirmasi password tidak cocok!";
    } else {
        // Cek jika email sudah ada
        $check_email_query = "SELECT * FROM users WHERE email = ?";
        $stmt = mysqli_prepare($conn, $check_email_query);
        mysqli_stmt_bind_param($stmt, "s", $email);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        if (mysqli_num_rows($result) > 0) {
            $error_message = "Email sudah terdaftar, silakan gunakan email lain.";
        } else {
            // Hash password
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            // Insert user baru
            $insert_query = "INSERT INTO users (nama_lengkap, email, nisn, password) VALUES (?, ?, ?, ?)";
            $stmt = mysqli_prepare($conn, $insert_query);
            mysqli_stmt_bind_param($stmt, "ssss", $nama_lengkap, $email, $nisn, $hashed_password);

            if (mysqli_stmt_execute($stmt)) {
                $success_message = "Registrasi berhasil! Silakan <a href='login.php'>login</a>.";
            } else {
                $error_message = "Registrasi gagal, silakan coba lagi.";
            }
        }
    }
}
?>

<div class="card">
    <div class="card-header">
        <h3 class="text-center">Registrasi Akun</h3>
    </div>
    <div class="card-body">
        <?php if(!empty($error_message)): ?>
            <div class="alert alert-danger"><?php echo $error_message; ?></div>
        <?php endif; ?>
        <?php if(!empty($success_message)): ?>
            <div class="alert alert-success"><?php echo $success_message; ?></div>
        <?php else: ?>
            <form action="register.php" method="POST">
                <div class="mb-3">
                    <label for="nama_lengkap" class="form-label">Nama Lengkap</label>
                    <input type="text" class="form-control" id="nama_lengkap" name="nama_lengkap" required>
                </div>
                <div class="mb-3">
                    <label for="email" class="form-label">Alamat Email</label>
                    <input type="email" class="form-control" id="email" name="email" required>
                </div>
                <div class="mb-3">
                    <label for="nisn" class="form-label">NISN (Nomor Induk Siswa Nasional)</label>
                    <input type="text" class="form-control" id="nisn" name="nisn" required>
                </div>
                <div class="mb-3">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" class="form-control" id="password" name="password" required>
                </div>
                <div class="mb-3">
                    <label for="password_confirm" class="form-label">Konfirmasi Password</label>
                    <input type="password" class="form-control" id="password_confirm" name="password_confirm" required>
                </div>
                <button type="submit" class="btn btn-primary w-100">Register</button>
            </form>
        <?php endif; ?>
    </div>
    <div class="card-footer text-center">
        Sudah punya akun? <a href="login.php">Login di sini</a>
    </div>
</div>

<?php include 'templates/footer.php'; ?>
