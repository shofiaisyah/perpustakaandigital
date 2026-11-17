<?php
require_once 'koneksi.php';
cek_login();
$pesan = "";

// 1. PROSES PEMINJAMAN (STOK BERKURANG)
if (isset($_POST['pinjam'])) {
    $id_anggota = (int)$_POST['id_anggota'];
    $id_buku    = (int)$_POST['id_buku'];
    $tgl_pinjam = $_POST['tgl_pinjam'];
    $tgl_tempo  = $_POST['tgl_jatuh_tempo'];

    $cek_stok = mysqli_query($conn, "SELECT stok, judul FROM bibliografi WHERE id_buku = $id_buku");
    $buku     = mysqli_fetch_assoc($cek_stok);

    if ($buku && $buku['stok'] > 0) {
        mysqli_query($conn, "INSERT INTO sirkulasi (id_buku, id_anggota, tgl_pinjam, tgl_jatuh_tempo, status) 
                             VALUES ($id_buku, $id_anggota, '$tgl_pinjam', '$tgl_tempo', 'Pinjam')");
        mysqli_query($conn, "UPDATE bibliografi SET stok = stok - 1 WHERE id_buku = $id_buku");
        $pesan = "<div class='alert alert-success border-0 shadow-sm d-flex align-items-center gap-2'><i class='bi bi-check-circle-fill'></i> Peminjaman buku <b>" . htmlspecialchars($buku['judul']) . "</b> berhasil dicatat! Stok otomatis berkurang 1.</div>";
    } else {
        $pesan = "<div class='alert alert-danger border-0 shadow-sm d-flex align-items-center gap-2'><i class='bi bi-x-circle-fill'></i> Gagal! Stok buku habis.</div>";
    }
}

// 2. PROSES PENGEMBALIAN (STOK BERTAMBAH + HITUNG DENDA OTOMATIS)
if (isset($_POST['kembalikan'])) {
    $id_pinjam   = (int)$_POST['id_pinjam'];
    $tgl_kembali = $_POST['tgl_kembali_aktual'];

    $q_pinjam = mysqli_query($conn, "SELECT * FROM sirkulasi WHERE id_pinjam = $id_pinjam AND status = 'Pinjam'");
    if (mysqli_num_rows($q_pinjam) > 0) {
        $data_pinjam = mysqli_fetch_assoc($q_pinjam);
        $id_buku     = $data_pinjam['id_buku'];
        $jatuh_tempo = $data_pinjam['tgl_jatuh_tempo'];

        $timestamp_tempo   = strtotime($jatuh_tempo);
        $timestamp_kembali = strtotime($tgl_kembali);
        $selisih_detik     = $timestamp_kembali - $timestamp_tempo;
        $selisih_hari      = floor($selisih_detik / (60 * 60 * 24));

        $terlambat = ($selisih_hari > 0) ? $selisih_hari : 0;
        $denda     = $terlambat * $tarif_denda_per_hari;

        mysqli_query($conn, "UPDATE sirkulasi SET 
                             tgl_kembali = '$tgl_kembali', 
                             status = 'Kembali', 
                             terlambat_hari = $terlambat, 
                             denda = $denda 
                             WHERE id_pinjam = $id_pinjam");

        mysqli_query($conn, "UPDATE bibliografi SET stok = stok + 1 WHERE id_buku = $id_buku");

        $info_denda = ($denda > 0) ? "Terlambat <b>$terlambat hari</b>. Denda: <b>Rp " . number_format($denda, 0, ',', '.') . "</b>" : "Tepat waktu (Tanpa Denda).";
        $pesan = "<div class='alert alert-info border-0 shadow-sm d-flex align-items-center gap-2'><i class='bi bi-info-circle-fill'></i> Buku berhasil dikembalikan! Stok bertambah 1. Status: $info_denda</div>";
    }
}

// Ringkasan Cepat untuk Kartu KPI Sirkulasi
$kpi_stok    = mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(stok) as total FROM bibliografi"));
$kpi_aktif   = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM sirkulasi WHERE status='Pinjam'"));
$kpi_selesai = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM sirkulasi WHERE status='Kembali'"));
$kpi_denda   = mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(denda) as total FROM sirkulasi"));

tampilkan_header("Transaksi Sirkulasi");
echo $pesan;
?>

<!-- KARTU RINGKASAN SIRKULASI -->
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-xl-3">
        <div class="card p-3">
            <div class="d-flex align-items-center gap-3">
                <div class="kpi-icon badge-soft-primary"><i class="bi bi-journal-check"></i></div>
                <div>
                    <div class="text-muted small">Stok Buku di Rak</div>
                    <div class="h5 fw-bold mb-0"><?= (int)$kpi_stok['total'] ?> <span class="fw-normal small text-muted">Eks</span></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card p-3">
            <div class="d-flex align-items-center gap-3">
                <div class="kpi-icon badge-soft-warning"><i class="bi bi-hourglass-split"></i></div>
                <div>
                    <div class="text-muted small">Sedang Dipinjam</div>
                    <div class="h5 fw-bold mb-0"><?= (int)$kpi_aktif['total'] ?> <span class="fw-normal small text-muted">Buku</span></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card p-3">
            <div class="d-flex align-items-center gap-3">
                <div class="kpi-icon badge-soft-success"><i class="bi bi-check2-all"></i></div>
                <div>
                    <div class="text-muted small">Transaksi Selesai</div>
                    <div class="h5 fw-bold mb-0"><?= (int)$kpi_selesai['total'] ?> <span class="fw-normal small text-muted">Kembali</span></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card p-3">
            <div class="d-flex align-items-center gap-3">
                <div class="kpi-icon badge-soft-danger"><i class="bi bi-cash-coin"></i></div>
                <div>
                    <div class="text-muted small">Akumulasi Denda</div>
                    <div class="h5 fw-bold mb-0">Rp <?= number_format((int)$kpi_denda['total'], 0, ',', '.') ?></div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- FORM TRANSAKSI PEMINJAMAN -->
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header-clean d-flex align-items-center gap-2">
                <i class="bi bi-box-arrow-up-right text-primary fs-5"></i>
                <h6 class="fw-bold mb-0">Catat Peminjaman Baru</h6>
            </div>
            <div class="card-body p-4">
                <form method="POST">
                    <div class="mb-3">
                        <label class="form-label small">Pilih Anggota</label>
                        <select name="id_anggota" class="form-select form-select-sm" required>
                            <option value="">-- Pilih Anggota Terdaftar --</option>
                            <?php
                            $qa = mysqli_query($conn, "SELECT * FROM anggota ORDER BY nama_anggota ASC");
                            while ($a = mysqli_fetch_assoc($qa)) {
                                echo "<option value='{$a['id_anggota']}'>{$a['kode_anggota']} - " . htmlspecialchars($a['nama_anggota']) . "</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small">Pilih Buku (Stok Tersedia)</label>
                        <select name="id_buku" class="form-select form-select-sm" required>
                            <option value="">-- Pilih Koleksi Buku --</option>
                            <?php
                            $qb = mysqli_query($conn, "SELECT * FROM bibliografi WHERE stok > 0 ORDER BY judul ASC");
                            while ($b = mysqli_fetch_assoc($qb)) {
                                echo "<option value='{$b['id_buku']}'>[Stok: {$b['stok']}] {$b['kode_buku']} - " . htmlspecialchars($b['judul']) . "</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label small">Tanggal Pinjam</label>
                            <input type="date" name="tgl_pinjam" class="form-control form-control-sm" value="<?= date('Y-m-d') ?>" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label small">Jatuh Tempo</label>
                            <input type="date" name="tgl_jatuh_tempo" class="form-control form-control-sm" value="<?= date('Y-m-d', strtotime('+7 days')) ?>" required>
                        </div>
                    </div>
                    <button type="submit" name="pinjam" class="btn btn-primary btn-sm w-100 py-2">
                        <i class="bi bi-check2-circle me-1"></i> Proses Peminjaman
                    </button>
                </form>

                <div class="mt-3 p-3 rounded-3 bg-light border small text-muted">
                    <div class="fw-semibold text-dark mb-1"><i class="bi bi-cpu me-1 text-primary"></i> Aturan Automasi Sirkulasi:</div>
                    <ul class="ps-3 mb-0">
                        <li>Stok buku otomatis berkurang/bertambah.</li>
                        <li>Tarif denda: <b>Rp <?= number_format($tarif_denda_per_hari, 0, ',', '.') ?> / hari</b>.</li>
                        <li>Ubah tanggal pengembalian melewati jatuh tempo untuk simulasi denda.</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <!-- TABEL DAFTAR SIRKULASI -->
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header-clean d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="fw-bold mb-0">Riwayat & Status Sirkulasi</h6>
                    <small class="text-muted">Proses pengembalian buku dan kalkulasi keterlambatan otomatis</small>
                </div>
            </div>
            <div class="table-responsive">
                <table class="table table-modern small">
                    <thead>
                        <tr>
                            <th>Peminjam</th>
                            <th>Koleksi Buku</th>
                            <th>Jadwal</th>
                            <th>Status</th>
                            <th>Denda</th>
                            <th width="205">Aksi Pengembalian</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $qs = mysqli_query($conn, "SELECT s.*, b.kode_buku, b.judul, a.kode_anggota, a.nama_anggota 
                                                   FROM sirkulasi s
                                                   JOIN bibliografi b ON s.id_buku = b.id_buku
                                                   JOIN anggota a ON s.id_anggota = a.id_anggota
                                                   ORDER BY s.status ASC, s.id_pinjam DESC");
                        while ($s = mysqli_fetch_assoc($qs)):
                        ?>
                        <tr>
                            <td>
                                <div class="fw-bold text-dark"><?= htmlspecialchars($s['nama_anggota']) ?></div>
                                <span class="font-mono text-muted" style="font-size:0.75rem;"><?= htmlspecialchars($s['kode_anggota']) ?></span>
                            </td>
                            <td>
                                <div class="fw-semibold text-dark"><?= htmlspecialchars($s['judul']) ?></div>
                                <span class="badge-pill badge-soft-slate font-mono mt-1"><?= htmlspecialchars($s['kode_buku']) ?></span>
                            </td>
                            <td>
                                <div class="text-muted" style="font-size:0.78rem;">Pinjam: <?= $s['tgl_pinjam'] ?></div>
                                <div class="text-danger fw-semibold" style="font-size:0.78rem;">Tempo: <?= $s['tgl_jatuh_tempo'] ?></div>
                            </td>
                            <td>
                                <?php if ($s['status'] == 'Pinjam'): ?>
                                    <span class="badge-pill badge-soft-warning">Dipinjam</span>
                                <?php else: ?>
                                    <span class="badge-pill badge-soft-success">Kembali</span>
                                    <div class="text-muted mt-1" style="font-size:0.73rem;"><?= $s['tgl_kembali'] ?></div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($s['status'] == 'Kembali'): ?>
                                    <div class="text-muted" style="font-size:0.75rem;"><?= $s['terlambat_hari'] ?> Hari</div>
                                    <div class="fw-bold <?= $s['denda'] > 0 ? 'text-danger' : 'text-success' ?>">
                                        Rp <?= number_format($s['denda'], 0, ',', '.') ?>
                                    </div>
                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($s['status'] == 'Pinjam'): ?>
                                    <form method="POST" class="d-flex gap-1">
                                        <input type="hidden" name="id_pinjam" value="<?= $s['id_pinjam'] ?>">
                                        <input type="date" name="tgl_kembali_aktual" class="form-control form-control-sm px-2" value="<?= date('Y-m-d') ?>" title="Tanggal Pengembalian" required>
                                        <button type="submit" name="kembalikan" class="btn btn-success btn-sm px-2" title="Proses Pengembalian">
                                            <i class="bi bi-check2"></i>
                                        </button>
                                    </form>
                                <?php else: ?>
                                    <span class="text-success small fw-semibold"><i class="bi bi-check-circle-fill me-1"></i>Selesai</span>
                                <?php endif; ?>
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