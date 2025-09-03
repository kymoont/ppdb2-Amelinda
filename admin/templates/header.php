<?php
session_start();
include_once __DIR__ . '/../../config/database.php';

// Protect all admin pages
if (!isset($_SESSION['admin_id']) && basename($_SERVER['PHP_SELF']) != 'login.php') {
    header("Location: login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - PPDB Online</title>
    <!-- Bootstrap & Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body>

<?php if (isset($_SESSION['admin_id'])): ?>
<div class="sidebar">
    <h4 class="text-center">Admin PPDB</h4>
    <hr style="background-color: white;">
    <a href="index.php" class="<?php echo (basename($_SERVER['PHP_SELF']) == 'index.php') ? 'active' : ''; ?>">Dashboard</a>
    <a href="pendaftar.php" class="<?php echo (basename($_SERVER['PHP_SELF']) == 'pendaftar.php') ? 'active' : ''; ?>">Manajemen Pendaftar</a>
    <a href="pengumuman.php" class="<?php echo (basename($_SERVER['PHP_SELF']) == 'pengumuman.php') ? 'active' : ''; ?>">Pengumuman</a>
    <a href="kuota.php" class="<?php echo (basename($_SERVER['PHP_SELF']) == 'kuota.php') ? 'active' : ''; ?>">Pengaturan Kuota</a>
    <?php if ($_SESSION['admin_id'] == 1): // Super admin only ?>
    <a href="admin.php" class="<?php echo (basename($_SERVER['PHP_SELF']) == 'admin.php') ? 'active' : ''; ?>">Manajemen Admin</a>
    <?php endif; ?>
    <a href="logout.php">Logout</a>
</div>
<div class="main-content">
<?php endif; ?>
