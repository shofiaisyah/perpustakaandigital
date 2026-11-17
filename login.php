<?php
require_once 'koneksi.php';
$pesan = "";

// 1. PROSES LOGIN
if (isset($_POST['aksi_login'])) {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = md5($_POST['password']);

    $q = mysqli_query($conn, "SELECT * FROM users WHERE username='$username' AND password='$password'");
    if (mysqli_num_rows($q) > 0) {
        $data = mysqli_fetch_assoc($q);
        $_SESSION['login_user']   = $data['username'];
        $_SESSION['id_user']      = $data['id_user'];
        $_SESSION['nama_petugas'] = $data['nama_petugas'];
        header("Location: fitur_tambahan.php");
        exit;
    } else {
        $pesan = "<div class='alert alert-danger border-0 shadow-sm d-flex align-items-center gap-2'><i class='bi bi-exclamation-triangle-fill'></i> Username atau Password salah!</div>";
    }
}

// 2. PROSES GANTI PASSWORD
if (isset($_POST['aksi_ganti_password'])) {
    cek_login();
    $id_user   = $_SESSION['id_user'];
    $pass_lama = md5($_POST['pass_lama']);
    $pass_baru = md5($_POST['pass_baru']);

    $cek = mysqli_query($conn, "SELECT * FROM users WHERE id_user='$id_user' AND password='$pass_lama'");
    if (mysqli_num_rows($cek) > 0) {
        mysqli_query($conn, "UPDATE users SET password='$pass_baru' WHERE id_user='$id_user'");
        $pesan = "<div class='alert alert-success border-0 shadow-sm d-flex align-items-center gap-2'><i class='bi bi-check-circle-fill'></i> Password berhasil diperbarui!</div>";
    } else {
        $pesan = "<div class='alert alert-danger border-0 shadow-sm d-flex align-items-center gap-2'><i class='bi bi-x-circle-fill'></i> Password lama tidak sesuai!</div>";
    }
}

// 3. PROSES TAMBAH USERNAME / PETUGAS BARU
if (isset($_POST['aksi_tambah_user'])) {
    cek_login();
    $user_baru = mysqli_real_escape_string($conn, $_POST['user_baru']);
    $nama_baru = mysqli_real_escape_string($conn, $_POST['nama_baru']);
    $pass_baru = md5($_POST['pass_user_baru']);

    $cek_duplikat = mysqli_query($conn, "SELECT * FROM users WHERE username='$user_baru'");
    if (mysqli_num_rows($cek_duplikat) > 0) {
        $pesan = "<div class='alert alert-warning border-0 shadow-sm d-flex align-items-center gap-2'><i class='bi bi-exclamation-circle-fill'></i> Username sudah digunakan, pilih username lain!</div>";
    } else {
        mysqli_query($conn, "INSERT INTO users (username, password, nama_petugas) VALUES ('$user_baru', '$pass_baru', '$nama_baru')");
        $pesan = "<div class='alert alert-success border-0 shadow-sm d-flex align-items-center gap-2'><i class='bi bi-check-circle-fill'></i> Username baru (<b>$user_baru</b>) berhasil ditambahkan!</div>";
    }
}

tampilkan_header("Manajemen Login & Akun");
echo $pesan;
?>

<?php if (!isset($_SESSION['login_user'])): ?>
    <!-- TAMPILAN FORM LOGIN MODERN -->
    <div class="row justify-content-center align-items-center" style="min-height: 75vh;">
        <div class="col-sm-10 col-md-6 col-lg-4">
            <div class="card p-4 p-md-5 shadow-sm">
                <div class="text-center mb-4">
                    <div class="brand-icon mx-auto mb-3" style="width:52px;height:52px;font-size:1.5rem;">
                        <i class="bi bi-book-half"></i>
                    </div>
                    <h4 class="fw-bold mb-1">Selamat Datang</h4>
                    <p class="text-muted small mb-0">Masuk ke panel petugas LookBook</p>
                </div>

                <form method="POST">
                    <div class="mb-3">
                        <label class="form-label small">Username</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light"><i class="bi bi-person text-muted"></i></span>
                            <input type="text" name="username" class="form-control" placeholder="Masukkan username" required>
                        </div>
                    </div>
                    <div class="mb-4">
                        <label class="form-label small">Password</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light"><i class="bi bi-lock text-muted"></i></span>
                            <input type="password" name="password" class="form-control" placeholder="Masukkan password" required>
                        </div>
                    </div>
                    <button type="submit" name="aksi_login" class="btn btn-primary w-100 py-2">
                        <i class="bi bi-box-arrow-in-right me-1"></i> Masuk ke Sistem
                    </button>
                </form>

                <div class="mt-4 pt-3 border-top text-center">
                    <span class="badge-pill badge-soft-slate">
                        Default: <b>admin</b> &bull; Pass: <b>admin123</b>
                    </span>
                </div>
            </div>
        </div>
    </div>

<?php else: ?>
    <!-- TAMPILAN KELOLA AKUN (GANTI PASSWORD & TAMBAH USERNAME) -->
    <div class="row g-4">
        <div class="col-lg-6">
            <div class="card h-100">
                <div class="card-header-clean d-flex align-items-center gap-2">
                    <i class="bi bi-key text-warning fs-5"></i>
                    <h6 class="fw-bold mb-0">Ganti Password Akun Aktif</h6>
                </div>
                <div class="card-body p-4">
                    <form method="POST">
                        <div class="mb-3">
                            <label class="form-label small">Username Aktif</label>
                            <input type="text" class="form-control bg-light font-mono" value="<?= htmlspecialchars($_SESSION['login_user']) ?>" disabled>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small">Password Lama</label>
                            <input type="password" name="pass_lama" class="form-control" placeholder="Masukkan password saat ini" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small">Password Baru</label>
                            <input type="password" name="pass_baru" class="form-control" placeholder="Masukkan password baru" required>
                        </div>
                        <button type="submit" name="aksi_ganti_password" class="btn btn-warning text-dark">
                            <i class="bi bi-check2-circle me-1"></i> Simpan Password Baru
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card h-100">
                <div class="card-header-clean d-flex align-items-center gap-2">
                    <i class="bi bi-person-plus text-success fs-5"></i>
                    <h6 class="fw-bold mb-0">Tambah Username Petugas Baru</h6>
                </div>
                <div class="card-body p-4">
                    <form method="POST">
                        <div class="mb-3">
                            <label class="form-label small">Nama Lengkap Petugas</label>
                            <input type="text" name="nama_baru" class="form-control" placeholder="Contoh: Pustakawan Layanan" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small">Username Baru</label>
                            <input type="text" name="user_baru" class="form-control" placeholder="Tanpa spasi, misal: petugas02" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small">Password</label>
                            <input type="password" name="pass_user_baru" class="form-control" placeholder="Password akun baru" required>
                        </div>
                        <button type="submit" name="aksi_tambah_user" class="btn btn-success">
                            <i class="bi bi-plus-lg me-1"></i> Tambah Akun Petugas
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-12">
            <div class="card">
                <div class="card-header-clean">
                    <h6 class="fw-bold mb-0">Daftar Akun Petugas Terdaftar</h6>
                </div>
                <div class="table-responsive">
                    <table class="table table-modern">
                        <thead>
                            <tr>
                                <th width="70">No</th>
                                <th>Username</th>
                                <th>Nama Petugas</th>
                                <th>Terdaftar Pada</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $no = 1;
                            $qu = mysqli_query($conn, "SELECT * FROM users ORDER BY id_user ASC");
                            while ($u = mysqli_fetch_assoc($qu)) {
                                echo "<tr>
                                    <td class='text-muted'>{$no}</td>
                                    <td><span class='badge-pill badge-soft-primary font-mono'>" . htmlspecialchars($u['username']) . "</span></td>
                                    <td class='fw-semibold'>" . htmlspecialchars($u['nama_petugas']) . "</td>
                                    <td class='text-muted small'><i class='bi bi-clock me-1'></i>{$u['created_at']}</td>
                                </tr>";
                                $no++;
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>

<?php tampilkan_footer(); ?>