<?php
require_once 'koneksi.php';
tampilkan_header("OPAC - Katalog Publik");

// Ambil parameter filter & pencarian
$kata_kunci      = isset($_GET['keyword']) ? mysqli_real_escape_string($conn, trim($_GET['keyword'])) : '';
$kriteria        = isset($_GET['kriteria']) ? $_GET['kriteria'] : 'semua';
$filter_kategori = isset($_GET['kategori']) ? mysqli_real_escape_string($conn, $_GET['kategori']) : '';
$hanya_tersedia  = (isset($_GET['tersedia']) && $_GET['tersedia'] == '1') ? true : false;
$urutan          = isset($_GET['urutan']) ? $_GET['urutan'] : 'judul_asc';

// Bangun klausa WHERE
$where = "WHERE 1=1";
if ($kata_kunci != '') {
    if ($kriteria == 'judul') {
        $where .= " AND b.judul LIKE '%$kata_kunci%'";
    } elseif ($kriteria == 'pengarang') {
        $where .= " AND b.pengarang LIKE '%$kata_kunci%'";
    } elseif ($kriteria == 'isbn') {
        $where .= " AND b.isbn LIKE '%$kata_kunci%'";
    } elseif ($kriteria == 'kategori') {
        $where .= " AND b.kategori LIKE '%$kata_kunci%'";
    } elseif ($kriteria == 'lokasi_rak') {
        $where .= " AND b.lokasi_rak LIKE '%$kata_kunci%'";
    } else {
        $where .= " AND (b.judul LIKE '%$kata_kunci%' OR b.pengarang LIKE '%$kata_kunci%' OR b.isbn LIKE '%$kata_kunci%' OR b.kategori LIKE '%$kata_kunci%' OR b.penerbit LIKE '%$kata_kunci%')";
    }
}

if ($filter_kategori != '') {
    $where .= " AND b.kategori = '$filter_kategori'";
}

if ($hanya_tersedia) {
    $where .= " AND b.stok > 0";
}

// Pengurutan hasil (Whitelist aman)
if ($urutan == 'tahun_desc') {
    $order_by = "ORDER BY b.tahun_terbit DESC, b.judul ASC";
} elseif ($urutan == 'terpopuler') {
    $order_by = "ORDER BY total_dipinjam DESC, b.judul ASC";
} elseif ($urutan == 'terbaru') {
    $order_by = "ORDER BY b.id_buku DESC";
} else {
    $order_by = "ORDER BY b.judul ASC";
}

// Query utama dengan informasi popularitas & estimasi kembali
$sql = "SELECT b.*,
        (SELECT COUNT(*) FROM sirkulasi s WHERE s.id_buku = b.id_buku) AS total_dipinjam,
        (SELECT MIN(s.tgl_jatuh_tempo) FROM sirkulasi s WHERE s.id_buku = b.id_buku AND s.status = 'Pinjam') AS estimasi_kembali
        FROM bibliografi b
        $where
        $order_by";

$query = mysqli_query($conn, $sql);
$daftar_buku = array();
while ($row = mysqli_fetch_assoc($query)) {
    $daftar_buku[] = $row;
}
$total_hasil = count($daftar_buku);

// Ambil daftar kategori unik untuk Quick Filter Chips
$q_kat = mysqli_query($conn, "SELECT kategori, COUNT(*) AS jml FROM bibliografi GROUP BY kategori ORDER BY kategori ASC");

// Statistik ringkas koleksi OPAC
$stat_opac = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS jml_judul, COALESCE(SUM(stok), 0) AS jml_eks FROM bibliografi"));
$ada_filter_aktif = ($kata_kunci != '' || $filter_kategori != '' || $hanya_tersedia || $urutan != 'judul_asc');
?>

<style>
    .kat-pill {
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        padding: 0.4rem 0.85rem;
        border-radius: 999px;
        font-size: 0.8rem;
        font-weight: 500;
        text-decoration: none;
        border: 1px solid #e2e8f0;
        background: #ffffff;
        color: #475569;
        transition: all 0.15s ease;
    }
    .kat-pill:hover {
        border-color: #4338ca;
        color: #4338ca;
        background: #eef2ff;
    }
    .kat-pill.active {
        background: #4338ca;
        border-color: #4338ca;
        color: #ffffff;
        font-weight: 600;
    }
    .citation-box {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        padding: 0.75rem 1rem;
        font-size: 0.84rem;
        color: #1e293b;
        line-height: 1.5;
    }
    @media print {
        .no-print, .navbar, .sidebar, .topbar { display: none !important; }
        .main-area { margin-left: 0 !important; }
        .card { border: 1px solid #cbd5e1 !important; box-shadow: none !important; }
    }
</style>

<!-- HERO SEARCH & FILTER BAR -->
<div class="card p-4 p-md-5 mb-4 border-0 shadow-sm no-print" style="background: linear-gradient(135deg, #312e81 0%, #4338ca 100%); color: #fff;">
    <div class="row justify-content-center text-center mb-3">
        <div class="col-lg-8">
            <span class="badge bg-white bg-opacity-10 text-white px-3 py-2 rounded-pill mb-2">
                <i class="bi bi-compass me-1"></i> Online Public Access Catalog &bull; <?= (int)$stat_opac['jml_judul'] ?> Judul (<?= (int)$stat_opac['jml_eks'] ?> Eks Tersedia)
            </span>
            <h2 class="fw-bold mb-1">Telusuri Koleksi Perpustakaan</h2>
            <p class="text-white-50 small mb-0">Cari buku berdasarkan judul, pengarang, ISBN, subjek, lokasi rak, hingga sitasi pustaka otomatis.</p>
        </div>
    </div>

    <form method="GET" class="mt-2">
        <?php if ($filter_kategori != ''): ?>
            <input type="hidden" name="kategori" value="<?= htmlspecialchars($filter_kategori) ?>">
        <?php endif; ?>

        <div class="row g-2 justify-content-center">
            <div class="col-md-5">
                <div class="input-group">
                    <span class="input-group-text bg-white border-0"><i class="bi bi-search text-muted"></i></span>
                    <input type="text" name="keyword" class="form-control border-0 py-2" placeholder="Ketik kata kunci pencarian..." value="<?= htmlspecialchars($kata_kunci) ?>">
                </div>
            </div>
            <div class="col-md-3">
                <select name="kriteria" class="form-select border-0 py-2">
                    <option value="semua" <?= $kriteria=='semua'?'selected':'' ?>>Semua Kriteria</option>
                    <option value="judul" <?= $kriteria=='judul'?'selected':'' ?>>Judul Buku</option>
                    <option value="pengarang" <?= $kriteria=='pengarang'?'selected':'' ?>>Pengarang</option>
                    <option value="isbn" <?= $kriteria=='isbn'?'selected':'' ?>>Nomor ISBN</option>
                    <option value="kategori" <?= $kriteria=='kategori'?'selected':'' ?>>Kategori / Subjek</option>
                    <option value="lokasi_rak" <?= $kriteria=='lokasi_rak'?'selected':'' ?>>Lokasi Rak</option>
                </select>
            </div>
            <div class="col-md-2 d-grid">
                <button type="submit" class="btn btn-light text-primary fw-bold py-2">
                    <i class="bi bi-search me-1"></i> Cari Buku
                </button>
            </div>
        </div>

        <!-- OPSI FILTER LANJUTAN -->
        <div class="row justify-content-center mt-3">
            <div class="col-md-10 d-flex flex-wrap justify-content-between align-items-center gap-2 pt-2 border-top border-white border-opacity-10">
                <div class="form-check form-switch mb-0">
                    <input class="form-check-input" type="checkbox" role="switch" id="cekTersedia" name="tersedia" value="1" <?= $hanya_tersedia ? 'checked' : '' ?> onchange="this.form.submit()">
                    <label class="form-check-label small text-white" for="cekTersedia">
                        Hanya tampilkan yang tersedia di rak
                    </label>
                </div>

                <div class="d-flex align-items-center gap-2">
                    <label class="small text-white-50 mb-0">Urutkan:</label>
                    <select name="urutan" class="form-select form-select-sm bg-white bg-opacity-10 text-white border-0" style="width:auto;" onchange="this.form.submit()">
                        <option value="judul_asc" class="text-dark" <?= $urutan=='judul_asc'?'selected':'' ?>>Judul (A - Z)</option>
                        <option value="terpopuler" class="text-dark" <?= $urutan=='terpopuler'?'selected':'' ?>>Paling Sering Dipinjam</option>
                        <option value="tahun_desc" class="text-dark" <?= $urutan=='tahun_desc'?'selected':'' ?>>Tahun Terbit Terbaru</option>
                        <option value="terbaru" class="text-dark" <?= $urutan=='terbaru'?'selected':'' ?>>Koleksi Baru Masuk</option>
                    </select>
                </div>
            </div>
        </div>
    </form>
</div>

<!-- QUICK FILTER CHIPS KATEGORI -->
<div class="d-flex flex-wrap align-items-center gap-2 mb-3 no-print">
    <span class="small fw-semibold text-muted me-1"><i class="bi bi-tags me-1"></i>Subjek:</span>
    <a href="opac.php" class="kat-pill <?= $filter_kategori=='' ? 'active' : '' ?>">
        Semua Koleksi
    </a>
    <?php while ($kat = mysqli_fetch_assoc($q_kat)): ?>
        <?php
        $is_kat_active = ($filter_kategori == $kat['kategori']);
        $url_kat = "opac.php?kategori=" . urlencode($kat['kategori']);
        if ($hanya_tersedia) $url_kat .= "&tersedia=1";
        ?>
        <a href="<?= $url_kat ?>" class="kat-pill <?= $is_kat_active ? 'active' : '' ?>">
            <?= htmlspecialchars($kat['kategori']) ?>
            <span class="badge rounded-pill <?= $is_kat_active ? 'bg-white text-primary' : 'bg-light text-secondary' ?>" style="font-size:0.7rem;">
                <?= $kat['jml'] ?>
            </span>
        </a>
    <?php endwhile; ?>
</div>

<!-- TABEL HASIL PENCARIAN KOLEKSI -->
<div class="card">
    <div class="card-header-clean d-flex flex-wrap justify-content-between align-items-center gap-2">
        <div class="d-flex align-items-center gap-2">
            <h6 class="fw-bold mb-0">Hasil Penelusuran Katalog</h6>
            <span class="badge-pill badge-soft-primary"><?= $total_hasil ?> Judul Ditemukan</span>
            <?php if ($filter_kategori != ''): ?>
                <span class="badge-pill badge-soft-slate">Subjek: <?= htmlspecialchars($filter_kategori) ?></span>
            <?php endif; ?>
        </div>

        <div class="d-flex align-items-center gap-2 no-print">
            <?php if ($ada_filter_aktif): ?>
                <a href="opac.php" class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-arrow-counterclockwise me-1"></i> Reset Filter
                </a>
            <?php endif; ?>
            <?php if ($total_hasil > 0): ?>
                <button type="button" onclick="window.print()" class="btn btn-sm btn-light border">
                    <i class="bi bi-printer me-1"></i> Cetak Daftar
                </button>
            <?php endif; ?>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table table-modern">
            <thead>
                <tr>
                    <th width="115">Kode Panggil</th>
                    <th>Informasi Bibliografi</th>
                    <th>Kategori</th>
                    <th>Lokasi Rak</th>
                    <th>Ketersediaan</th>
                    <th width="135" class="text-end no-print">Detail</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($total_hasil == 0): ?>
                    <tr>
                        <td colspan="6" class="text-center py-5">
                            <i class="bi bi-journal-x fs-1 text-muted d-block mb-2"></i>
                            <div class="fw-semibold text-secondary">Koleksi tidak ditemukan</div>
                            <small class="text-muted">Coba gunakan kata kunci lain, pilih semua subjek, atau matikan filter stok tersedia.</small>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($daftar_buku as $b): ?>
                        <tr>
                            <td>
                                <span class="badge-pill badge-soft-slate font-mono"><?= htmlspecialchars($b['kode_buku']) ?></span>
                            </td>
                            <td>
                                <div class="fw-bold text-dark mb-1">
                                    <?= htmlspecialchars($b['judul']) ?>
                                    <?php if ((int)$b['total_dipinjam'] >= 2): ?>
                                        <span class="badge-pill badge-soft-warning ms-1" title="Sering dipinjam anggota">
                                            <i class="bi bi-star-fill"></i> Populer
                                        </span>
                                    <?php endif; ?>
                                </div>
                                <div class="small text-muted d-flex flex-wrap gap-3">
                                    <span><i class="bi bi-person me-1"></i><?= htmlspecialchars($b['pengarang']) ?></span>
                                    <span><i class="bi bi-building me-1"></i><?= htmlspecialchars($b['penerbit']) ?> (<?= $b['tahun_terbit'] ?>)</span>
                                    <span class="font-mono"><i class="bi bi-upc-scan me-1"></i><?= htmlspecialchars($b['isbn']) ?></span>
                                </div>
                            </td>
                            <td><span class="badge-pill badge-soft-primary"><?= htmlspecialchars($b['kategori']) ?></span></td>
                            <td>
                                <span class="badge-pill badge-soft-slate">
                                    <i class="bi bi-geo-alt"></i> <?= htmlspecialchars($b['lokasi_rak']) ?>
                                </span>
                            </td>
                            <td>
                                <?php if ($b['stok'] > 0): ?>
                                    <span class="badge-pill badge-soft-success">
                                        <i class="bi bi-check-circle-fill"></i> Tersedia (<?= $b['stok'] ?> Eks)
                                    </span>
                                <?php else: ?>
                                    <span class="badge-pill badge-soft-danger">
                                        <i class="bi bi-x-circle-fill"></i> Dipinjam Semua
                                    </span>
                                    <?php if (!empty($b['estimasi_kembali'])): ?>
                                        <div class="text-muted mt-1" style="font-size:0.73rem;">
                                            <i class="bi bi-clock-history"></i> Est. kembali: <b><?= $b['estimasi_kembali'] ?></b>
                                        </div>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </td>
                            <td class="text-end no-print">
                                <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#modalBuku<?= $b['id_buku'] ?>">
                                    <i class="bi bi-info-circle me-1"></i> Detail & Sitasi
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- MODAL DETAIL BIBLIOGRAFI & GENERATOR SITASI -->
<?php foreach ($daftar_buku as $b): ?>
    <?php
    $id_b      = (int)$b['id_buku'];
    $sitasi_apa  = htmlspecialchars($b['pengarang']) . ". (" . $b['tahun_terbit'] . "). " . htmlspecialchars($b['judul']) . ". " . htmlspecialchars($b['penerbit']) . ".";
    $sitasi_ieee = htmlspecialchars($b['pengarang']) . ", \"" . htmlspecialchars($b['judul']) . ",\" " . htmlspecialchars($b['penerbit']) . ", " . $b['tahun_terbit'] . ". [ISBN: " . htmlspecialchars($b['isbn']) . "].";
    ?>
    <div class="modal fade" id="modalBuku<?= $id_b ?>" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content border-0 shadow" style="border-radius:16px;">
                <div class="modal-header border-bottom px-4 py-3">
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge-pill badge-soft-primary font-mono"><?= htmlspecialchars($b['kode_buku']) ?></span>
                        <h6 class="modal-title fw-bold mb-0">Detail Metadata Bibliografi</h6>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <h5 class="fw-bold text-dark mb-3"><?= htmlspecialchars($b['judul']) ?></h5>

                    <div class="row g-3 mb-4">
                        <div class="col-sm-6">
                            <div class="p-3 rounded-3 bg-light border h-100 small">
                                <div class="mb-2"><span class="text-muted d-block">Pengarang:</span><b><?= htmlspecialchars($b['pengarang']) ?></b></div>
                                <div class="mb-2"><span class="text-muted d-block">Penerbit & Tahun:</span><b><?= htmlspecialchars($b['penerbit']) ?> (<?= $b['tahun_terbit'] ?>)</b></div>
                                <div><span class="text-muted d-block">Nomor ISBN:</span><span class="font-mono fw-semibold"><?= htmlspecialchars($b['isbn']) ?></span></div>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="p-3 rounded-3 bg-light border h-100 small">
                                <div class="mb-2"><span class="text-muted d-block">Subjek / Kategori:</span><span class="badge-pill badge-soft-primary"><?= htmlspecialchars($b['kategori']) ?></span></div>
                                <div class="mb-2"><span class="text-muted d-block">Lokasi Penyimpanan:</span><b><i class="bi bi-geo-alt text-primary"></i> <?= htmlspecialchars($b['lokasi_rak']) ?></b></div>
                                <div>
                                    <span class="text-muted d-block">Status Sirkulasi:</span>
                                    <?php if ($b['stok'] > 0): ?>
                                        <span class="text-success fw-bold">Tersedia <?= $b['stok'] ?> Eksemplar</span>
                                    <?php else: ?>
                                        <span class="text-danger fw-bold">Sedang Dipinjam Semua</span>
                                    <?php endif; ?>
                                    <span class="text-muted">(Dipinjam <?= (int)$b['total_dipinjam'] ?>x)</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- GENERATOR SITASI OTOMATIS -->
                    <div class="border-top pt-3">
                        <div class="fw-bold small text-dark mb-2"><i class="bi bi-quote text-primary me-1"></i> Generator Sitasi Pustaka Otomatis</div>

                        <div class="mb-2">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="text-muted" style="font-size:0.75rem; font-weight:600;">Format APA Style</span>
                                <button type="button" class="btn btn-sm btn-link text-decoration-none p-0 small" onclick="salinSitasi('apa_<?= $id_b ?>', this)">
                                    <i class="bi bi-clipboard me-1"></i>Salin APA
                                </button>
                            </div>
                            <div class="citation-box" id="apa_<?= $id_b ?>"><?= $sitasi_apa ?></div>
                        </div>

                        <div>
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="text-muted" style="font-size:0.75rem; font-weight:600;">Format IEEE Style</span>
                                <button type="button" class="btn btn-sm btn-link text-decoration-none p-0 small" onclick="salinSitasi('ieee_<?= $id_b ?>', this)">
                                    <i class="bi bi-clipboard me-1"></i>Salin IEEE
                                </button>
                            </div>
                            <div class="citation-box" id="ieee_<?= $id_b ?>"><?= $sitasi_ieee ?></div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-top px-4 py-2 bg-light">
                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>
<?php endforeach; ?>

<script>
function salinSitasi(elementId, btn) {
    var teks = document.getElementById(elementId).innerText;
    var tempInput = document.createElement("textarea");
    tempInput.value = teks;
    document.body.appendChild(tempInput);
    tempInput.select();
    document.execCommand("copy");
    document.body.removeChild(tempInput);

    var teksAsli = btn.innerHTML;
    btn.innerHTML = '<i class="bi bi-check2 text-success me-1"></i><span class="text-success fw-semibold">Tersalin!</span>';
    setTimeout(function() {
        btn.innerHTML = teksAsli;
    }, 1800);
}
</script>

<?php tampilkan_footer(); ?>