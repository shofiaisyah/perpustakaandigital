<?php
require_once 'koneksi.php';
cek_login();

// PROSES SIMPAN BARU
if (isset($_POST['tambah'])) {
    $kode      = mysqli_real_escape_string($conn, $_POST['kode_buku']);
    $judul     = mysqli_real_escape_string($conn, $_POST['judul']);
    $pengarang = mysqli_real_escape_string($conn, $_POST['pengarang']);
    $penerbit  = mysqli_real_escape_string($conn, $_POST['penerbit']);
    $tahun     = (int)$_POST['tahun_terbit'];
    $isbn      = mysqli_real_escape_string($conn, $_POST['isbn']);
    $kategori  = mysqli_real_escape_string($conn, $_POST['kategori']);
    $rak       = mysqli_real_escape_string($conn, $_POST['lokasi_rak']);
    $stok      = (int)$_POST['stok'];

    mysqli_query($conn, "INSERT INTO bibliografi (kode_buku, judul, pengarang, penerbit, tahun_terbit, isbn, kategori, lokasi_rak, stok) 
                         VALUES ('$kode', '$judul', '$pengarang', '$penerbit', '$tahun', '$isbn', '$kategori', '$rak', '$stok')");
    header("Location: bibliografi.php");
    exit;
}

// PROSES UPDATE
if (isset($_POST['update'])) {
    $id        = (int)$_POST['id_buku'];
    $kode      = mysqli_real_escape_string($conn, $_POST['kode_buku']);
    $judul     = mysqli_real_escape_string($conn, $_POST['judul']);
    $pengarang = mysqli_real_escape_string($conn, $_POST['pengarang']);
    $penerbit  = mysqli_real_escape_string($conn, $_POST['penerbit']);
    $tahun     = (int)$_POST['tahun_terbit'];
    $isbn      = mysqli_real_escape_string($conn, $_POST['isbn']);
    $kategori  = mysqli_real_escape_string($conn, $_POST['kategori']);
    $rak       = mysqli_real_escape_string($conn, $_POST['lokasi_rak']);
    $stok      = (int)$_POST['stok'];

    mysqli_query($conn, "UPDATE bibliografi SET kode_buku='$kode', judul='$judul', pengarang='$pengarang', penerbit='$penerbit', 
                         tahun_terbit='$tahun', isbn='$isbn', kategori='$kategori', lokasi_rak='$rak', stok='$stok' WHERE id_buku=$id");
    header("Location: bibliografi.php");
    exit;
}

// PROSES HAPUS
if (isset($_GET['hapus'])) {
    $id = (int)$_GET['hapus'];
    mysqli_query($conn, "DELETE FROM bibliografi WHERE id_buku=$id");
    header("Location: bibliografi.php");
    exit;
}

// AMBIL DATA UNTUK EDIT
$edit_data = null;
if (isset($_GET['edit'])) {
    $id = (int)$_GET['edit'];
    $res = mysqli_query($conn, "SELECT * FROM bibliografi WHERE id_buku=$id");
    $edit_data = mysqli_fetch_assoc($res);
}

tampilkan_header("Manajemen Bibliografi");
?>

<div class="row g-4">
    <!-- FORM INPUT / EDIT BIBLIOGRAFI -->
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header-clean d-flex align-items-center gap-2">
                <i class="bi <?= $edit_data ? 'bi-pencil-square text-warning' : 'bi-plus-square text-primary' ?> fs-5"></i>
                <h6 class="fw-bold mb-0"><?= $edit_data ? 'Edit Data Bibliografi' : 'Tambah Bibliografi Baru' ?></h6>
            </div>
            <div class="card-body p-4">
                <form method="POST">
                    <?php if ($edit_data): ?>
                        <input type="hidden" name="id_buku" value="<?= $edit_data['id_buku'] ?>">
                    <?php endif; ?>
                    <div class="mb-2">
                        <label class="form-label small">Kode Buku / Panggil</label>
                        <input type="text" name="kode_buku" class="form-control form-control-sm font-mono" value="<?= isset($edit_data['kode_buku']) ? htmlspecialchars($edit_data['kode_buku']) : '' ?>" placeholder="Contoh: BK-004" required>
                    </div>
                    <div class="mb-2">
                        <label class="form-label small">Judul Buku</label>
                        <input type="text" name="judul" class="form-control form-control-sm" value="<?= isset($edit_data['judul']) ? htmlspecialchars($edit_data['judul']) : '' ?>" required>
                    </div>
                    <div class="mb-2">
                        <label class="form-label small">Pengarang</label>
                        <input type="text" name="pengarang" class="form-control form-control-sm" value="<?= isset($edit_data['pengarang']) ? htmlspecialchars($edit_data['pengarang']) : '' ?>" required>
                    </div>
                    <div class="row g-2 mb-2">
                        <div class="col-8">
                            <label class="form-label small">Penerbit</label>
                            <input type="text" name="penerbit" class="form-control form-control-sm" value="<?= isset($edit_data['penerbit']) ? htmlspecialchars($edit_data['penerbit']) : '' ?>" required>
                        </div>
                        <div class="col-4">
                            <label class="form-label small">Tahun</label>
                            <input type="number" name="tahun_terbit" class="form-control form-control-sm" value="<?= isset($edit_data['tahun_terbit']) ? $edit_data['tahun_terbit'] : date('Y') ?>" required>
                        </div>
                    </div>
                    <div class="mb-2">
                        <label class="form-label small">Nomor ISBN</label>
                        <input type="text" name="isbn" class="form-control form-control-sm font-mono" value="<?= isset($edit_data['isbn']) ? htmlspecialchars($edit_data['isbn']) : '' ?>" required>
                    </div>
                    <div class="mb-2">
                        <label class="form-label small">Kategori / Subjek</label>
                        <input type="text" name="kategori" class="form-control form-control-sm" value="<?= isset($edit_data['kategori']) ? htmlspecialchars($edit_data['kategori']) : '' ?>" placeholder="Contoh: Ilmu Komputer" required>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-8">
                            <label class="form-label small">Lokasi Rak</label>
                            <input type="text" name="lokasi_rak" class="form-control form-control-sm" value="<?= isset($edit_data['lokasi_rak']) ? htmlspecialchars($edit_data['lokasi_rak']) : '' ?>" placeholder="Rak A1" required>
                        </div>
                        <div class="col-4">
                            <label class="form-label small">Stok</label>
                            <input type="number" name="stok" class="form-control form-control-sm" value="<?= isset($edit_data['stok']) ? $edit_data['stok'] : 1 ?>" min="0" required>
                        </div>
                    </div>
                    <?php if ($edit_data): ?>
                        <button type="submit" name="update" class="btn btn-warning text-dark btn-sm w-100 py-2">
                            <i class="bi bi-check-lg me-1"></i> Simpan Perubahan
                        </button>
                        <a href="bibliografi.php" class="btn btn-light border btn-sm w-100 mt-2">Batal</a>
                    <?php else: ?>
                        <button type="submit" name="tambah" class="btn btn-primary btn-sm w-100 py-2">
                            <i class="bi bi-plus-lg me-1"></i> Simpan Bibliografi
                        </button>
                    <?php endif; ?>
                </form>
            </div>
        </div>
    </div>

    <!-- DAFTAR KOLEKSI BIBLIOGRAFI -->
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header-clean d-flex flex-wrap justify-content-between align-items-center gap-2">
                <div>
                    <h6 class="fw-bold mb-0">Daftar Koleksi Bibliografi</h6>
                    <small class="text-muted">Kelola katalog buku dan cetak kartu katalog standar</small>
                </div>
                <a href="cetak_katalog.php" target="_blank" class="btn btn-sm btn-success">
                    <i class="bi bi-printer me-1"></i> Print Semua Katalog
                </a>
            </div>
            <div class="table-responsive">
                <table class="table table-modern small">
                    <thead>
                        <tr>
                            <th>Kode</th>
                            <th>Judul & Pengarang</th>
                            <th>ISBN / Tahun</th>
                            <th>Rak</th>
                            <th class="text-center">Stok</th>
                            <th width="145" class="text-end">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $q = mysqli_query($conn, "SELECT * FROM bibliografi ORDER BY id_buku DESC");
                        while ($row = mysqli_fetch_assoc($q)):
                        ?>
                        <tr>
                            <td><span class="badge-pill badge-soft-slate font-mono"><?= htmlspecialchars($row['kode_buku']) ?></span></td>
                            <td>
                                <div class="fw-bold text-dark"><?= htmlspecialchars($row['judul']) ?></div>
                                <div class="text-muted" style="font-size:0.78rem;"><?= htmlspecialchars($row['pengarang']) ?> &bull; <?= htmlspecialchars($row['penerbit']) ?></div>
                            </td>
                            <td>
                                <div class="font-mono" style="font-size:0.76rem;"><?= htmlspecialchars($row['isbn']) ?></div>
                                <span class="badge-pill badge-soft-primary mt-1"><?= $row['tahun_terbit'] ?></span>
                            </td>
                            <td><span class="text-secondary fw-semibold"><?= htmlspecialchars($row['lokasi_rak']) ?></span></td>
                            <td class="text-center">
                                <span class="badge-pill <?= $row['stok'] > 0 ? 'badge-soft-success' : 'badge-soft-danger' ?>">
                                    <?= $row['stok'] ?>
                                </span>
                            </td>
                            <td class="text-end">
                                <div class="btn-group btn-group-sm">
                                    <a href="cetak_katalog.php?id=<?= $row['id_buku'] ?>" target="_blank" class="btn btn-outline-secondary" title="Cetak Katalog">
                                        <i class="bi bi-printer"></i>
                                    </a>
                                    <a href="bibliografi.php?edit=<?= $row['id_buku'] ?>" class="btn btn-outline-warning" title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <a href="bibliografi.php?hapus=<?= $row['id_buku'] ?>" class="btn btn-outline-danger" title="Hapus" onclick="return confirm('Hapus buku ini?')">
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