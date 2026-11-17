<?php
require_once 'koneksi.php';
cek_login();

$where = "";
if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $where = "WHERE id_buku = $id";
}
$q = mysqli_query($conn, "SELECT * FROM bibliografi $where ORDER BY kode_buku ASC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Cetak Kartu Katalog — LookBook</title>
    <style>
        body { font-family: 'Courier New', Courier, monospace; font-size: 13px; margin: 24px; background: #f8fafc; }
        .toolbar { background: #fff; border: 1px solid #cbd5e1; padding: 12px 18px; border-radius: 10px; margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center; font-family: sans-serif; }
        .btn-print { background: #4338ca; color: #fff; border: none; padding: 8px 16px; border-radius: 8px; cursor: pointer; font-weight: 600; }
        .katalog-wrapper { display: flex; flex-wrap: wrap; gap: 20px; }
        .kartu-katalog {
            width: 12.5cm; height: 7.5cm; background: #fff;
            border: 1.8px solid #1e293b; padding: 16px;
            box-sizing: border-box; position: relative;
            page-break-inside: avoid;
        }
        .call-number {
            float: left; width: 92px; font-weight: bold;
            border-right: 1px dashed #64748b; padding-right: 8px; margin-right: 12px;
            height: 100%; line-height: 1.5;
        }
        .isi-katalog { overflow: hidden; line-height: 1.45; }
        .judul { font-weight: bold; font-size: 14px; margin: 4px 0 8px; text-decoration: underline; }
        @media print {
            body { background: #fff; margin: 10px; }
            .no-print { display: none; }
        }
    </style>
</head>
<body onload="window.print()">
    <div class="toolbar no-print">
        <span><strong>Pratinjau Cetak Kartu Katalog</strong> (Ukuran Standar 12.5 x 7.5 cm)</span>
        <button onclick="window.print()" class="btn-print">Cetak Sekarang</button>
    </div>

    <div class="katalog-wrapper">
        <?php while ($b = mysqli_fetch_assoc($q)): ?>
            <div class="kartu-katalog">
                <div class="call-number">
                    <?= htmlspecialchars($b['lokasi_rak']) ?><br>
                    <?= htmlspecialchars($b['kode_buku']) ?><br>
                    <?= strtoupper(substr($b['pengarang'], 0, 3)) ?><br>
                    <?= strtolower(substr($b['judul'], 0, 1)) ?>
                </div>
                <div class="isi-katalog">
                    <div><b><?= htmlspecialchars($b['pengarang']) ?></b></div>
                    <div class="judul"><?= htmlspecialchars($b['judul']) ?></div>
                    <div>Penerbit: <?= htmlspecialchars($b['penerbit']) ?>, <?= $b['tahun_terbit'] ?>.</div>
                    <div style="margin-top:8px;">ISBN: <?= htmlspecialchars($b['isbn']) ?></div>
                    <div>Subjek/Kategori: <?= htmlspecialchars($b['kategori']) ?></div>
                    <div style="position:absolute; bottom:12px; right:15px; font-size:11px; color:#334155;">
                        Eksemplar: <?= $b['stok'] ?> Eks
                    </div>
                </div>
            </div>
        <?php endwhile; ?>
    </div>
</body>
</html>