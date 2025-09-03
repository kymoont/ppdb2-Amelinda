<?php
// File: formulir.php

include 'templates/header.php';

// Cek jika user belum login, redirect ke halaman login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$error_message = '';
$success_message = '';

// Cek apakah pendaftar sudah mengisi formulir
$query_pendaftar = "SELECT id FROM pendaftar WHERE user_id = ?";
$stmt_check = mysqli_prepare($conn, $query_pendaftar);
mysqli_stmt_bind_param($stmt_check, "i", $user_id);
mysqli_stmt_execute($stmt_check);
$result_pendaftar = mysqli_stmt_get_result($stmt_check);
$sudah_mendaftar = mysqli_num_rows($result_pendaftar) > 0;

// --- AUTO-SEED JURUSAN (HANYA JIKA KOSONG) ---
$result_jurusan = mysqli_query($conn, "SELECT id FROM jurusan");
if (mysqli_num_rows($result_jurusan) == 0) {
    mysqli_query($conn, "INSERT INTO jurusan (nama_jurusan, keterangan) VALUES ('Teknik Komputer dan Jaringan', 'Fokus pada infrastruktur IT dan jaringan.'), ('Rekayasa Perangkat Lunak', 'Fokus pada pengembangan aplikasi dan software.'), ('Multimedia', 'Fokus pada desain grafis, video, dan animasi.')");
}
// --- END OF SEED ---

if ($_SERVER["REQUEST_METHOD"] == "POST" && !$sudah_mendaftar) {
    // Ambil data dari form
    $nik = mysqli_real_escape_string($conn, $_POST['nik']);
    $tempat_lahir = mysqli_real_escape_string($conn, $_POST['tempat_lahir']);
    $tanggal_lahir = mysqli_real_escape_string($conn, $_POST['tanggal_lahir']);
    $jenis_kelamin = mysqli_real_escape_string($conn, $_POST['jenis_kelamin']);
    $alamat = mysqli_real_escape_string($conn, $_POST['alamat']);
    $agama = mysqli_real_escape_string($conn, $_POST['agama']);
    $nama_ayah = mysqli_real_escape_string($conn, $_POST['nama_ayah']);
    $nama_ibu = mysqli_real_escape_string($conn, $_POST['nama_ibu']);
    $pekerjaan_ayah = mysqli_real_escape_string($conn, $_POST['pekerjaan_ayah']);
    $pekerjaan_ibu = mysqli_real_escape_string($conn, $_POST['pekerjaan_ibu']);
    $nilai_nem = mysqli_real_escape_string($conn, $_POST['nilai_nem']);
    $jurusan_id = mysqli_real_escape_string($conn, $_POST['jurusan_id']);

    // Generate nomor pendaftaran unik
    $nomor_pendaftaran = 'PPDB' . date('Y') . strtoupper(uniqid());

    // Mulai transaksi database
    mysqli_begin_transaction($conn);

    try {
        // Insert ke tabel pendaftar
        $query_insert_pendaftar = "INSERT INTO pendaftar (user_id, nomor_pendaftaran, nama_lengkap, nik, tempat_lahir, tanggal_lahir, jenis_kelamin, alamat, agama, nama_ayah, nama_ibu, pekerjaan_ayah, pekerjaan_ibu, nilai_nem, jurusan_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt_pendaftar = mysqli_prepare($conn, $query_insert_pendaftar);
        mysqli_stmt_bind_param($stmt_pendaftar, "issssssssssssdi", $user_id, $nomor_pendaftaran, $_SESSION['nama_lengkap'], $nik, $tempat_lahir, $tanggal_lahir, $jenis_kelamin, $alamat, $agama, $nama_ayah, $nama_ibu, $pekerjaan_ayah, $pekerjaan_ibu, $nilai_nem, $jurusan_id);
        mysqli_stmt_execute($stmt_pendaftar);
        $pendaftar_id = mysqli_insert_id($conn);

        // Proses upload berkas
        $upload_dir = "uploads/";
        $allowed_types = ['image/jpeg', 'image/png', 'application/pdf'];
        $berkas_list = ['berkas_kk' => 'KK', 'berkas_akta' => 'Akta Lahir', 'berkas_foto' => 'Foto', 'berkas_ijazah' => 'Ijazah'];

        foreach ($berkas_list as $file_input_name => $jenis_berkas) {
            if (isset($_FILES[$file_input_name]) && $_FILES[$file_input_name]['error'] == 0) {
                $file = $_FILES[$file_input_name];
                if ($file['size'] > 2097152) { // 2MB limit
                    throw new Exception("Ukuran file $jenis_berkas terlalu besar (maks 2MB).");
                }
                if (!in_array($file['type'], $allowed_types)) {
                    throw new Exception("Tipe file $jenis_berkas tidak valid (hanya JPG, PNG, PDF).");
                }

                $file_extension = pathinfo($file['name'], PATHINFO_EXTENSION);
                $new_filename = $pendaftar_id . '_' . $jenis_berkas . '_' . time() . '.' . $file_extension;
                $target_path = $upload_dir . $new_filename;

                if (move_uploaded_file($file['tmp_name'], $target_path)) {
                    // Insert ke tabel berkas
                    $query_insert_berkas = "INSERT INTO berkas (pendaftar_id, jenis_berkas, nama_file) VALUES (?, ?, ?)";
                    $stmt_berkas = mysqli_prepare($conn, $query_insert_berkas);
                    mysqli_stmt_bind_param($stmt_berkas, "iss", $pendaftar_id, $jenis_berkas, $new_filename);
                    mysqli_stmt_execute($stmt_berkas);
                } else {
                    throw new Exception("Gagal mengupload file $jenis_berkas.");
                }
            }
        }

        // Jika semua berhasil, commit transaksi
        mysqli_commit($conn);
        $success_message = "Pendaftaran Anda berhasil dikirim! Nomor pendaftaran Anda adalah: <strong>$nomor_pendaftaran</strong>. Silakan cek status pendaftaran secara berkala.";
        $sudah_mendaftar = true; // Update status untuk me-refresh halaman

    } catch (Exception $e) {
        // Jika ada error, rollback transaksi
        mysqli_rollback($conn);
        $error_message = "Terjadi kesalahan: " . $e->getMessage();
    }
}

// Ambil data jurusan untuk dropdown
$query_jurusan = "SELECT * FROM jurusan ORDER BY nama_jurusan";
$hasil_jurusan = mysqli_query($conn, $query_jurusan);

?>

<div class="card">
    <div class="card-header">
        <h3 class="text-center">Formulir Pendaftaran Siswa Baru</h3>
    </div>
    <div class="card-body">
        <?php if ($sudah_mendaftar): ?>
            <div class="alert alert-success">
                <h4 class="alert-heading">Pendaftaran Selesai!</h4>
                <p>Anda sudah mengirimkan formulir pendaftaran. Anda tidak dapat mengubahnya lagi.</p>
                <hr>
                <p class="mb-0">Silakan menuju ke halaman <a href="cek_status.php" class="alert-link">Cek Status</a> untuk melihat perkembangan pendaftaran Anda.</p>
            </div>
             <?php if (!empty($success_message)) echo "<div class='alert alert-info'>$success_message</div>"; ?>
        <?php else: ?>
            <?php if(!empty($error_message)) echo "<div class='alert alert-danger'>$error_message</div>"; ?>
            
            <form action="formulir.php" method="POST" enctype="multipart/form-data">
                <!-- Data Pribadi -->
                <h5>1. Data Pribadi</h5>
                <hr>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="nama_lengkap" class="form-label">Nama Lengkap (sesuai akun)</label>
                        <input type="text" class="form-control" id="nama_lengkap" value="<?php echo htmlspecialchars($_SESSION['nama_lengkap']); ?>" disabled>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="nik" class="form-label">NIK</label>
                        <input type="text" class="form-control" id="nik" name="nik" required>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="tempat_lahir" class="form-label">Tempat Lahir</label>
                        <input type="text" class="form-control" id="tempat_lahir" name="tempat_lahir" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="tanggal_lahir" class="form-label">Tanggal Lahir</label>
                        <input type="date" class="form-control" id="tanggal_lahir" name="tanggal_lahir" required>
                    </div>
                </div>
                <div class="row">
                     <div class="col-md-6 mb-3">
                        <label for="jenis_kelamin" class="form-label">Jenis Kelamin</label>
                        <select class="form-select" id="jenis_kelamin" name="jenis_kelamin" required>
                            <option value="">-- Pilih --</option>
                            <option value="Laki-laki">Laki-laki</option>
                            <option value="Perempuan">Perempuan</option>
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="agama" class="form-label">Agama</label>
                        <select class="form-select" id="agama" name="agama" required>
                            <option value="">-- Pilih --</option>
                            <option value="Islam">Islam</option>
                            <option value="Kristen Protestan">Kristen Protestan</option>
                            <option value="Kristen Katolik">Kristen Katolik</option>
                            <option value="Hindu">Hindu</option>
                            <option value="Buddha">Buddha</option>
                            <option value="Konghucu">Konghucu</option>
                        </select>
                    </div>
                </div>
                <div class="mb-3">
                    <label for="alamat" class="form-label">Alamat Lengkap</label>
                    <textarea class="form-control" id="alamat" name="alamat" rows="3" required></textarea>
                </div>

                <!-- Data Orang Tua -->
                <h5 class="mt-4">2. Data Orang Tua/Wali</h5>
                <hr>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="nama_ayah" class="form-label">Nama Ayah</label>
                        <input type="text" class="form-control" id="nama_ayah" name="nama_ayah" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="pekerjaan_ayah" class="form-label">Pekerjaan Ayah</label>
                        <input type="text" class="form-control" id="pekerjaan_ayah" name="pekerjaan_ayah" required>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="nama_ibu" class="form-label">Nama Ibu</label>
                        <input type="text" class="form-control" id="nama_ibu" name="nama_ibu" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="pekerjaan_ibu" class="form-label">Pekerjaan Ibu</label>
                        <input type="text" class="form-control" id="pekerjaan_ibu" name="pekerjaan_ibu" required>
                    </div>
                </div>

                <!-- Data Akademik -->
                <h5 class="mt-4">3. Data Akademik & Pilihan Jurusan</h5>
                <hr>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="nilai_nem" class="form-label">Nilai Ujian Akhir (NEM)</label>
                        <input type="number" step="0.01" class="form-control" id="nilai_nem" name="nilai_nem" placeholder="Contoh: 35.50" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="jurusan_id" class="form-label">Pilihan Jurusan</label>
                        <select class="form-select" id="jurusan_id" name="jurusan_id" required>
                            <option value="">-- Pilih Jurusan --</option>
                            <?php while($jurusan = mysqli_fetch_assoc($hasil_jurusan)): ?>
                                <option value="<?php echo $jurusan['id']; ?>"><?php echo htmlspecialchars($jurusan['nama_jurusan']); ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                </div>

                <!-- Upload Dokumen -->
                <h5 class="mt-4">4. Upload Dokumen (Tipe: JPG, PNG, PDF | Maks: 2MB)</h5>
                <hr>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="berkas_kk" class="form-label">Scan Kartu Keluarga (KK)</label>
                        <input class="form-control" type="file" id="berkas_kk" name="berkas_kk" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="berkas_akta" class="form-label">Scan Akta Lahir</label>
                        <input class="form-control" type="file" id="berkas_akta" name="berkas_akta" required>
                    </div>
                </div>
                 <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="berkas_foto" class="form-label">Pas Foto 3x4</label>
                        <input class="form-control" type="file" id="berkas_foto" name="berkas_foto" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="berkas_ijazah" class="form-label">Scan Ijazah/SKL</label>
                        <input class="form-control" type="file" id="berkas_ijazah" name="berkas_ijazah" required>
                    </div>
                </div>

                <div class="d-grid gap-2 mt-4">
                    <button type="submit" class="btn btn-primary btn-lg">Kirim Pendaftaran</button>
                </div>
            </form>
        <?php endif; ?>
    </div>
</div>

<?php include 'templates/footer.php'; ?>
