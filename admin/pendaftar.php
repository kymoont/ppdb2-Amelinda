<?php
// File: admin/pendaftar.php

include 'templates/header.php';

// --- LOGIKA FILTER DAN PENCARIAN ---
$where_clauses = [];
$params = [];
$types = '';

// Filter by search term (nama atau nomor pendaftaran)
$search = $_GET['search'] ?? '';
if (!empty($search)) {
    $where_clauses[] = "(p.nama_lengkap LIKE ? OR p.nomor_pendaftaran LIKE ?)";
    $search_param = "%$search%";
    $params[] = &$search_param;
    $params[] = &$search_param;
    $types .= 'ss';
}

// Filter by status
$status = $_GET['status'] ?? '';
if (!empty($status)) {
    $where_clauses[] = "p.status_pendaftaran = ?";
    $params[] = &$status;
    $types .= 's';
}

// Filter by jurusan
$jurusan_id_filter = $_GET['jurusan'] ?? '';
if (!empty($jurusan_id_filter)) {
    $where_clauses[] = "p.jurusan_id = ?";
    $params[] = &$jurusan_id_filter;
    $types .= 'i';
}

$sql = "SELECT p.id, p.nomor_pendaftaran, p.nama_lengkap, p.status_pendaftaran, j.nama_jurusan 
        FROM pendaftar p 
        JOIN jurusan j ON p.jurusan_id = j.id";

if (!empty($where_clauses)) {
    $sql .= " WHERE " . implode(' AND ', $where_clauses);
}

$sql .= " ORDER BY p.tanggal_daftar DESC";

$stmt = mysqli_prepare($conn, $sql);
if (!empty($params)) {
    mysqli_stmt_bind_param($stmt, $types, ...$params);
}

mysqli_stmt_execute($stmt);
$result_pendaftar = mysqli_stmt_get_result($stmt);

// Ambil data jurusan untuk filter
$hasil_jurusan = mysqli_query($conn, "SELECT * FROM jurusan ORDER BY nama_jurusan");

?>

<div class="content-header">
    <h1>Manajemen Pendaftar</h1>
    <p>Kelola semua pendaftar yang masuk melalui halaman ini.</p>
</div>

<!-- Filter Form -->
<div class="card mb-4">
    <div class="card-header">Filter & Cari Pendaftar</div>
    <div class="card-body">
        <form action="pendaftar.php" method="GET" class="row g-3">
            <div class="col-md-4">
                <input type="text" name="search" class="form-control" placeholder="Cari Nama/No. Pendaftaran..." value="<?php echo htmlspecialchars($search); ?>">
            </div>
            <div class="col-md-3">
                <select name="status" class="form-select">
                    <option value="">Semua Status</option>
                    <option value="Pending" <?php if($status == 'Pending') echo 'selected'; ?>>Pending</option>
                    <option value="Diterima" <?php if($status == 'Diterima') echo 'selected'; ?>>Diterima</option>
                    <option value="Ditolak" <?php if($status == 'Ditolak') echo 'selected'; ?>>Ditolak</option>
                </select>
            </div>
            <div class="col-md-3">
                <select name="jurusan" class="form-select">
                    <option value="">Semua Jurusan</option>
                    <?php while($j = mysqli_fetch_assoc($hasil_jurusan)): ?>
                        <option value="<?php echo $j['id']; ?>" <?php if($jurusan_id_filter == $j['id']) echo 'selected'; ?>><?php echo htmlspecialchars($j['nama_jurusan']); ?></option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100">Filter</button>
            </div>
        </form>
    </div>
</div>

<!-- Pendaftar Table -->
<div class="d-flex justify-content-end mb-3">
    <a href="export.php?<?php echo http_build_query($_GET); ?>" class="btn btn-success">Export ke CSV</a>
</div>
<div class="card">
    <div class="card-body">
        <table class="table table-striped table-hover">
            <thead>
                <tr>
                    <th>No. Pendaftaran</th>
                    <th>Nama Lengkap</th>
                    <th>Pilihan Jurusan</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if(mysqli_num_rows($result_pendaftar) > 0): ?>
                    <?php while($p = mysqli_fetch_assoc($result_pendaftar)): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($p['nomor_pendaftaran']); ?></td>
                            <td><?php echo htmlspecialchars($p['nama_lengkap']); ?></td>
                            <td><?php echo htmlspecialchars($p['nama_jurusan']); ?></td>
                            <td>
                                <?php
                                    $s = $p['status_pendaftaran'];
                                    $badge_class = 'bg-secondary';
                                    if ($s == 'Diterima') $badge_class = 'bg-success';
                                    if ($s == 'Ditolak') $badge_class = 'bg-danger';
                                    if ($s == 'Pending') $badge_class = 'bg-warning';
                                ?>
                                <span class="badge <?php echo $badge_class; ?>"><?php echo htmlspecialchars($s); ?></span>
                            </td>
                            <td>
                                <a href="detail.php?id=<?php echo $p['id']; ?>" class="btn btn-sm btn-info">Detail</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" class="text-center">Tidak ada data pendaftar yang cocok dengan kriteria.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include 'templates/footer.php'; ?>
