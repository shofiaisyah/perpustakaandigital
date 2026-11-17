<?php
require_once 'koneksi.php';
cek_login();

// TAMBAH ANGGOTA
if (isset($_POST['tambah'])) {
    $kode      = mysqli_real_escape_string($conn, $_POST['kode_anggota']);
    $nama      = mysqli_real_escape_string($conn, $_POST['nama_anggota']);
    $identitas = mysqli_real_escape_string($conn, $_POST['identitas_id']);
    $instansi  = mysqli_real_escape_string($conn, $_POST['instansi_prodi']);
    $hp        = mysqli_real_escape_string($conn, $_POST['no_hp']);
    $alamat    = mysqli_real_escape_string($conn, $_POST['alamat']);
    $tgl       = date('Y-m-d');

    mysqli_query($conn, "INSERT INTO anggota (kode_anggota, nama_anggota, identitas_id, instansi_prodi, no_hp, alamat, tgl_daftar) 
                         VALUES ('$kode', '$nama', '$identitas', '$instansi', '$hp', '$alamat', '$tgl')");
    header("Location: anggota.php");
    exit;
}

// UPDATE ANGGOTA
if (isset($_POST['update'])) {
    $id        = (int)$_POST['id_anggota'];
    $kode      = mysqli_real_escape_string($conn, $_POST['kode_anggota']);
    $nama      = mysqli_real_escape_string($conn, $_POST['nama_anggota']);
    $identitas = mysqli_real_escape_string($conn, $_POST['identitas_id']);
    $instansi  = mysqli_real_escape_string($conn, $_POST['instansi_prodi']);
    $hp        = mysqli_real_escape_string($conn, $_POST['no_hp']);
    $alamat    = mysqli_real_escape_string($conn, $_POST['alamat']);

    mysqli_query($conn, "UPDATE anggota SET kode_anggota='$kode', nama_anggota='$nama', identitas_id='$identitas', 
                         instansi_prodi='$instansi', no_hp='$hp', alamat='$alamat' WHERE id_anggota=$id");
    header("Location: anggota.php");
    exit;
}

// HAPUS ANGGOTA
if (isset($_GET['hapus'])) {
    $id = (int)$_GET['hapus'];
    mysqli_query($conn, "DELETE FROM anggota WHERE id_anggota=$id");
    header("Location: anggota.php");
    exit;
}

$edit = null;
if (isset($_GET['edit'])) {
    $id = (int)$_GET['edit'];
    $res = mysqli_query($conn, "SELECT * FROM anggota WHERE id_anggota=$id");
    $edit = mysqli_fetch_assoc($res);
}

tampilkan_header("Manajemen Keanggotaan");
?>

<div class="row g-4">
    <!-- FORM REGISTRASI / EDIT ANGGOTA -->
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header-clean d-flex align-items-center gap-2">
                <i class="bi <?= $edit ? 'bi-person-gear text-warning' : 'bi-person-plus text-primary' ?> fs-5"></i>
                <h6 class="fw-bold mb-0"><?= $edit ? 'Edit Data Anggota' : 'Registrasi Anggota Baru' ?></h6>
            </div>
            <div class="card-body p-4">
                <form method="POST">
                    <?php if ($edit): ?>
                        <input type="hidden" name="id_anggota" value="<?= $edit['id_anggota'] ?>">
                    <?php endif; ?>
                    <div class="mb-2">
                        <label class="form-label small">Kode Anggota</label>
                        <input type="text" name="kode_anggota" class="form-control form-control-sm font-mono" value="<?= isset($edit['kode_anggota']) ? htmlspecialchars($edit['kode_anggota']) : 'ANG-00' . rand(3,99) ?>" required>
                    </div>
                    <div class="mb-2">
                        <label class="form-label small">Nama Lengkap</label>
                        <input type="text" name="nama_anggota" class="form-control form-control-sm" value="<?= isset($edit['nama_anggota']) ? htmlspecialchars($edit['nama_anggota']) : '' ?>" required>
                    </div>
                    <div class="mb-2">
                        <label class="form-label small">NIM / NIK</label>
                        <input type="text" name="identitas_id" class="form-control form-control-sm font-mono" value="<?= isset($edit['identitas_id']) ? htmlspecialchars($edit['identitas_id']) : '' ?>" required>
                    </div>
                    <div class="mb-2">
                        <label class="form-label small">Program Studi / Instansi</label>
                        <input type="text" name="instansi_prodi" class="form-control form-control-sm" value="<?= isset($edit['instansi_prodi']) ? htmlspecialchars($edit['instansi_prodi']) : '' ?>" required>
                    </div>
                    <div class="mb-2">
                        <label class="form-label small">No. HP / WhatsApp</label>
                        <input type="text" name="no_hp" class="form-control form-control-sm" value="<?= isset($edit['no_hp']) ? htmlspecialchars($edit['no_hp']) : '' ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small">Alamat</label>
                        <textarea name="alamat" class="form-control form-control-sm" rows="2" required><?= isset($edit['alamat']) ? htmlspecialchars($edit['alamat']) : '' ?></textarea>
                    </div>
                    <?php if ($edit): ?>
                        <button type="submit" name="update" class="btn btn-warning text-dark btn-sm w-100 py-2">
                            <i class="bi bi-check-lg me-1"></i> Update Data Anggota
                        </button>
                        <a href="anggota.php" class="btn btn-light border btn-sm w-100 mt-2">Batal</a>
                    <?php else: ?>
                        <button type="submit" name="tambah" class="btn btn-primary btn-sm w-100 py-2">
                            <i class="bi bi-plus-lg me-1"></i> Daftarkan Anggota
                        </button>
                    <?php endif; ?>
                </form>
            </div>
        </div>
    </div>

    <!-- DAFTAR ANGGOTA -->
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header-clean">
                <h6 class="fw-bold mb-0">Daftar Anggota Perpustakaan</h6>
                <small class="text-muted">Kelola keanggotaan aktif dan cetak kartu anggota resmi</small>
            </div>
            <div class="table-responsive">
                <table class="table table-modern small">
                    <thead>
                        <tr>
                            <th>Kode</th>
                            <th>Anggota</th>
                            <th>Prodi / Instansi</th>
                            <th>Kontak</th>
                            <th width="185" class="text-end">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $q = mysqli_query($conn, "SELECT * FROM anggota ORDER BY id_anggota DESC");
                        while ($a = mysqli_fetch_assoc($q)):
                        ?>
                        <tr>
                            <td><span class="badge-pill badge-soft-primary font-mono"><?= htmlspecialchars($a['kode_anggota']) ?></span></td>
                            <td>
                                <div class="fw-bold text-dark"><?= htmlspecialchars($a['nama_anggota']) ?></div>
                                <div class="text-muted font-mono" style="font-size:0.75rem;">ID: <?= htmlspecialchars($a['identitas_id']) ?></div>
                            </td>
                            <td><?= htmlspecialchars($a['instansi_prodi']) ?></td>
                            <td><i class="bi bi-whatsapp text-success me-1"></i><?= htmlspecialchars($a['no_hp']) ?></td>
                            <td class="text-end">
                                <div class="btn-group btn-group-sm">
                                    <a href="cetak_kartu.php?id=<?= $a['id_anggota'] ?>" target="_blank" class="btn btn-outline-success" title="Cetak Kartu">
                                        <i class="bi bi-person-badge me-1"></i>Kartu
                                    </a>
                                    <a href="anggota.php?edit=<?= $a['id_anggota'] ?>" class="btn btn-outline-warning" title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <a href="anggota.php?hapus=<?= $a['id_anggota'] ?>" class="btn btn-outline-danger" title="Hapus" onclick="return confirm('Hapus anggota ini?')">
                                        <i class="bi bi-trash"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php tampilkan_footer(); ?>