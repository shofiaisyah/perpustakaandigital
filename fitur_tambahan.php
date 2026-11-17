<?php
require_once 'koneksi.php';
cek_login();

// 1. FITUR EXPORT LAPORAN KE CSV (EXCEL)
if (isset($_GET['export']) && $_GET['export'] == 'csv') {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=Laporan_Sirkulasi_Perpustakaan.csv');
    $output = fopen('php://output', 'w');
    fputcsv($output, array('ID Pinjam', 'Kode Anggota', 'Nama Anggota', 'Kode Buku', 'Judul Buku', 'Tgl Pinjam', 'Jatuh Tempo', 'Tgl Kembali', 'Status', 'Terlambat (Hari)', 'Denda (Rp)'));

    $q_exp = mysqli_query($conn, "SELECT s.id_pinjam, a.kode_anggota, a.nama_anggota, b.kode_buku, b.judul, s.tgl_pinjam, s.tgl_jatuh_tempo, s.tgl_kembali, s.status, s.terlambat_hari, s.denda 
                                  FROM sirkulasi s 
                                  JOIN anggota a ON s.id_anggota = a.id_anggota 
                                  JOIN bibliografi b ON s.id_buku = b.id_buku");
    while ($row = mysqli_fetch_assoc($q_exp)) {
        fputcsv($output, $row);
    }
    fclose($output);
    exit;
}

// 2. FITUR CETAK SURAT BEBAS PUSTAKA
if (isset($_GET['cetak_skbp'])) {
    $id_ang = (int)$_GET['cetak_skbp'];
    $qa = mysqli_query($conn, "SELECT * FROM anggota WHERE id_anggota = $id_ang");
    $ag = mysqli_fetch_assoc($qa);
    ?>
    <!DOCTYPE html>
    <html lang="id">
    <head>
        <meta charset="UTF-8">
        <title>Surat Keterangan Bebas Pustaka — <?= htmlspecialchars($ag['nama_anggota']) ?></title>
        <style>
            body { font-family: 'Times New Roman', serif; margin: 40px 60px; line-height: 1.65; color: #000; }
            .kop { text-align: center; border-bottom: 3px double #000; padding-bottom: 12px; margin-bottom: 28px; }
        </style>
    </head>
    <body onload="window.print()">
        <div class="kop">
            <h2 style="margin:0;">UPT PERPUSTAKAAN TERPADU</h2>
            <p style="margin:0; font-size:14px;">Sistem Informasi Automasi Perpustakaan (LookBook)</p>
        </div>
        <h3 style="text-align:center; text-decoration:underline; margin-bottom:24px;">SURAT KETERANGAN BEBAS PUSTAKA</h3>
        <p>Yang bertanda tangan di bawah ini, Petugas Perpustakaan menerangkan bahwa anggota:</p>
        <table style="margin: 16px 0 20px 30px;">
            <tr><td width="160">Nama Lengkap</td><td>: <b><?= htmlspecialchars($ag['nama_anggota']) ?></b></td></tr>
            <tr><td>Kode Anggota</td><td>: <?= htmlspecialchars($ag['kode_anggota']) ?></td></tr>
            <tr><td>NIM / Identitas</td><td>: <?= htmlspecialchars($ag['identitas_id']) ?></td></tr>
            <tr><td>Prodi / Instansi</td><td>: <?= htmlspecialchars($ag['instansi_prodi']) ?></td></tr>
        </table>
        <p>Berdasarkan hasil verifikasi sistem sirkulasi perpustakaan, nama tersebut di atas dinyatakan <b>BEBAS PINJAMAN KOLEKSI DAN TANGGUNGAN DENDA</b>.</p>
        <div style="float:right; margin-top:45px; text-align:center;">
            Semarang, <?= date('d F Y') ?><br>
            Petugas Layanan Perpustakaan<br><br><br><br>
            <b>( <?= htmlspecialchars($_SESSION['nama_petugas']) ?> )</b>
        </div>
    </body>
    </html>
    <?php
    exit;
}

// STATISTIK DASHBOARD
$stat_buku   = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total_judul, SUM(stok) as total_eksemplar FROM bibliografi"));
$stat_ang    = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total_anggota FROM anggota"));
$stat_pinjam = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as aktif FROM sirkulasi WHERE status='Pinjam'"));
$stat_denda  = mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(denda) as kas_denda FROM sirkulasi"));

tampilkan_header("Dashboard & Bebas Pustaka");
?>

<!-- STATUS AUTOMASI SISTEM -->
<div class="card p-3 mb-4 border-start border-4 border-primary">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
        <div class="d-flex align-items-center gap-3">
            <div class="kpi-icon badge-soft-primary" style="width:40px;height:40px;font-size:1.1rem;">
                <i class="bi bi-cpu-fill"></i>
            </div>
            <div>
                <div class="fw-bold text-dark small">Status Automasi Perpustakaan Terpadu</div>
                <div class="text-muted" style="font-size:0.78rem;">
                    Sinkronisasi Stok Real-Time &bull; Kalkulasi Denda (Rp <?= number_format($tarif_denda_per_hari, 0, ',', '.') ?>/hari) &bull; Verifikasi Bebas Pustaka Otomatis
                </div>
            </div>
        </div>
        <div class="d-flex gap-2">
            <a href="sirkulasi.php" class="btn btn-sm btn-primary"><i class="bi bi-plus-circle me-1"></i> Transaksi Baru</a>
            <a href="fitur_tambahan.php?export=csv" class="btn btn-sm btn-outline-success"><i class="bi bi-file-earmark-spreadsheet me-1"></i> Export CSV</a>
        </div>
    </div>
</div>

<!-- 4 KARTU KPI UTAMA -->
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-xl-3">
        <div class="card p-4 h-100">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="text-muted small fw-semibold">Total Koleksi</div>
                    <h3 class="fw-bold mt-1 mb-0"><?= (int)$stat_buku['total_judul'] ?> <span class="fs-6 fw-normal text-muted">Judul</span></h3>
                    <small class="text-primary fw-medium"><?= (int)$stat_buku['total_eksemplar'] ?> Eksemplar tersedia</small>
                </div>
                <div class="kpi-icon badge-soft-primary"><i class="bi bi-book"></i></div>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-xl-3">
        <div class="card p-4 h-100">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="text-muted small fw-semibold">Anggota Terdaftar</div>
                    <h3 class="fw-bold mt-1 mb-0"><?= (int)$stat_ang['total_anggota'] ?> <span class="fs-6 fw-normal text-muted">Orang</span></h3>
                    <small class="text-success fw-medium">Keanggotaan aktif</small>
                </div>
                <div class="kpi-icon badge-soft-success"><i class="bi bi-people"></i></div>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-xl-3">
        <div class="card p-4 h-100">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="text-muted small fw-semibold">Peminjaman Aktif</div>
                    <h3 class="fw-bold mt-1 mb-0"><?= (int)$stat_pinjam['aktif'] ?> <span class="fs-6 fw-normal text-muted">Transaksi</span></h3>
                    <small class="text-warning fw-medium">Sedang beredar di peminjam</small>
                </div>
                <div class="kpi-icon badge-soft-warning"><i class="bi bi-arrow-repeat"></i></div>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-xl-3">
        <div class="card p-4 h-100">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="text-muted small fw-semibold">Kas Denda Terkumpul</div>
                    <h3 class="fw-bold mt-1 mb-0">Rp <?= number_format((int)$stat_denda['kas_denda'], 0, ',', '.') ?></h3>
                    <small class="text-danger fw-medium">Akumulasi keterlambatan</small>
                </div>
                <div class="kpi-icon badge-soft-danger"><i class="bi bi-wallet2"></i></div>
            </div>
        </div>
    </div>
</div>

<!-- MODUL VERIFIKASI BEBAS PUSTAKA -->
<div class="card">
    <div class="card-header-clean d-flex flex-wrap justify-content-between align-items-center gap-2">
        <div>
            <h6 class="fw-bold mb-0">Verifikasi & Cetak Surat Keterangan Bebas Pustaka (SKBP)</h6>
            <small class="text-muted">Sistem otomatis memeriksa tanggungan buku aktif setiap anggota sebelum pencetakan surat</small>
        </div>
        <a href="fitur_tambahan.php?export=csv" class="btn btn-sm btn-success">
            <i class="bi bi-download me-1"></i> Download Laporan Sirkulasi (.CSV)
        </a>
    </div>
    <div class="table-responsive">
        <table class="table table-modern small">
            <thead>
                <tr>
                    <th>Kode Anggota</th>
                    <th>Nama Anggota</th>
                    <th>NIM / NIK</th>
                    <th>Prodi / Instansi</th>
                    <th>Status Tanggungan</th>
                    <th width="190" class="text-end">Surat Bebas Pustaka</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $q_cek = mysqli_query($conn, "SELECT a.*, 
                                             (SELECT COUNT(*) FROM sirkulasi s WHERE s.id_anggota = a.id_anggota AND s.status = 'Pinjam') as tanggungan
                                             FROM anggota a ORDER BY a.nama_anggota ASC");
                while ($row = mysqli_fetch_assoc($q_cek)):
                ?>
                <tr>
                    <td><span class="badge-pill badge-soft-primary font-mono"><?= htmlspecialchars($row['kode_anggota']) ?></span></td>
                    <td class="fw-bold text-dark"><?= htmlspecialchars($row['nama_anggota']) ?></td>
                    <td class="font-mono text-muted"><?= htmlspecialchars($row['identitas_id']) ?></td>
                    <td><?= htmlspecialchars($row['instansi_prodi']) ?></td>
                    <td>
                        <?php if ($row['tanggungan'] > 0): ?>
                            <span class="badge-pill badge-soft-danger">
                                <i class="bi bi-exclamation-circle-fill"></i> Meminjam <?= $row['tanggungan'] ?> Buku
                            </span>
                        <?php else: ?>
                            <span class="badge-pill badge-soft-success">
                                <i class="bi bi-check-circle-fill"></i> Bebas Pinjaman
                            </span>
                        <?php endif; ?>
                    </td>
                    <td class="text-end">
                        <?php if ($row['tanggungan'] == 0): ?>
                            <a href="fitur_tambahan.php?cetak_skbp=<?= $row['id_anggota'] ?>" target="_blank" class="btn btn-primary btn-sm">
                                <i class="bi bi-printer me-1"></i> Cetak SKBP
                            </a>
                        <?php else: ?>
                            <button class="btn btn-light border text-muted btn-sm" disabled>Belum Memenuhi Syarat</button>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<?php tampilkan_footer(); ?>