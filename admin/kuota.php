<?php
// File: admin/kuota.php

include 'templates/header.php';

$message = '';

// Tentukan tahun ajaran aktif
$tahun_ajaran_aktif = $_GET['tahun_ajaran'] ?? date('Y') . '/' . (date('Y') + 1);

// --- LOGIKA UPDATE KUOTA ---
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $kuota_data = $_POST['kuota'];
    $tahun_ajaran = $_POST['tahun_ajaran'];

    mysqli_begin_transaction($conn);
    try {
        foreach ($kuota_data as $jurusan_id => $jumlah_kuota) {
            $jumlah_kuota = (int)$jumlah_kuota;

            // Cek apakah sudah ada kuota untuk jurusan dan tahun ini
            $stmt_check = mysqli_prepare($conn, "SELECT id FROM kuota WHERE jurusan_id = ? AND tahun_ajaran = ?");
            mysqli_stmt_bind_param($stmt_check, "is", $jurusan_id, $tahun_ajaran);
            mysqli_stmt_execute($stmt_check);
            $result_check = mysqli_stmt_get_result($stmt_check);

            if (mysqli_num_rows($result_check) > 0) { // Update
                $kuota_id = mysqli_fetch_assoc($result_check)['id'];
                $stmt_update = mysqli_prepare($conn, "UPDATE kuota SET jumlah_kuota = ? WHERE id = ?");
                mysqli_stmt_bind_param($stmt_update, "ii", $jumlah_kuota, $kuota_id);
                mysqli_stmt_execute($stmt_update);
            } else { // Insert
                $stmt_insert = mysqli_prepare($conn, "INSERT INTO kuota (jurusan_id, tahun_ajaran, jumlah_kuota) VALUES (?, ?, ?)");
                mysqli_stmt_bind_param($stmt_insert, "isi", $jurusan_id, $tahun_ajaran, $jumlah_kuota);
                mysqli_stmt_execute($stmt_insert);
            }
        }
        mysqli_commit($conn);
        $message = '<div class="alert alert-success">Kuota berhasil diperbarui untuk tahun ajaran ' . htmlspecialchars($tahun_ajaran) . '.</div>';
        $tahun_ajaran_aktif = $tahun_ajaran; // Refresh data untuk tahun ajaran yang baru diupdate
    } catch (Exception $e) {
        mysqli_rollback($conn);
        $message = '<div class="alert alert-danger">Gagal memperbarui kuota: ' . $e->getMessage() . '</div>';
    }
}

// --- AMBIL DATA UNTUK TABEL ---
$query = "
    SELECT 
        j.id as jurusan_id, 
        j.nama_jurusan, 
        k.jumlah_kuota, 
        (SELECT COUNT(*) FROM pendaftar p WHERE p.jurusan_id = j.id AND p.status_pendaftaran = 'Diterima') as jumlah_diterima
    FROM 
        jurusan j
    LEFT JOIN 
        kuota k ON j.id = k.jurusan_id AND k.tahun_ajaran = ?
    ORDER BY j.nama_jurusan
";

$stmt_data = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt_data, "s", $tahun_ajaran_aktif);
mysqli_stmt_execute($stmt_data);
$result_data = mysqli_stmt_get_result($stmt_data);

?>

<div class="content-header">
    <h1>Pengaturan Kuota Penerimaan</h1>
    <p>Atur jumlah maksimal siswa yang dapat diterima untuk setiap jurusan per tahun ajaran.</p>
</div>

<?php echo $message; ?>

<!-- Filter Tahun Ajaran -->
<div class="card mb-4">
    <div class="card-body">
        <form action="kuota.php" method="GET" class="row align-items-end">
            <div class="col-md-4">
                <label for="tahun_ajaran_filter" class="form-label">Pilih Tahun Ajaran:</label>
                <input type="text" name="tahun_ajaran" id="tahun_ajaran_filter" class="form-control" value="<?php echo htmlspecialchars($tahun_ajaran_aktif); ?>" placeholder="Contoh: 2025/2026">
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-secondary">Tampilkan</button>
            </div>
        </form>
    </div>
</div>

<!-- Tabel Kuota -->
<div class="card">
    <div class="card-header"><h4>Kuota Jurusan untuk Tahun Ajaran <?php echo htmlspecialchars($tahun_ajaran_aktif); ?></h4></div>
    <div class="card-body">
        <form action="kuota.php?tahun_ajaran=<?php echo htmlspecialchars($tahun_ajaran_aktif); ?>" method="POST">
            <input type="hidden" name="tahun_ajaran" value="<?php echo htmlspecialchars($tahun_ajaran_aktif); ?>">
            <table class="table table-bordered table-hover">
                <thead class="table-light">
                    <tr>
                        <th>Nama Jurusan</th>
                        <th style="width: 150px;">Kuota Ditetapkan</th>
                        <th style="width: 150px;">Sudah Diterima</th>
                        <th style="width: 150px;">Sisa Kuota</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($row = mysqli_fetch_assoc($result_data)): ?>
                        <?php 
                            $kuota = (int)($row['jumlah_kuota'] ?? 0); 
                            $diterima = (int)$row['jumlah_diterima'];
                            $sisa = $kuota - $diterima;
                        ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['nama_jurusan']); ?></td>
                            <td>
                                <input type="number" name="kuota[<?php echo $row['jurusan_id']; ?>]" class="form-control" value="<?php echo $kuota; ?>">
                            </td>
                            <td class="text-center"><?php echo $diterima; ?></td>
                            <td class="text-center <?php echo ($sisa < 0) ? 'text-danger fw-bold' : ''; ?>">
                                <?php echo $sisa; ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
            <div class="d-flex justify-content-end">
                <button type="submit" class="btn btn-primary">Simpan Semua Perubahan Kuota</button>
            </div>
        </form>
    </div>
</div>

<?php include 'templates/footer.php'; ?>
