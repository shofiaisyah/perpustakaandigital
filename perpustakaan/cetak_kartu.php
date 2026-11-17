<?php
require_once 'koneksi.php';
cek_login();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$q  = mysqli_query($conn, "SELECT * FROM anggota WHERE id_anggota = $id");
$a  = mysqli_fetch_assoc($q);
if (!$a) die("Data anggota tidak ditemukan.");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Kartu Anggota — <?= htmlspecialchars($a['nama_anggota']) ?></title>
    <style>
        body { font-family: 'Segoe UI', Arial, sans-serif; margin: 30px; background: #f8fafc; }
        .toolbar { background: #fff; border: 1px solid #cbd5e1; padding: 10px 16px; border-radius: 10px; margin-bottom: 20px; display: inline-flex; gap: 15px; align-items: center; }
        .btn-print { background: #4338ca; color: #fff; border: none; padding: 7px 14px; border-radius: 7px; cursor: pointer; font-weight: 600; }
        .kartu {
            width: 8.6cm; height: 5.4cm; background: #fff;
            border: 1.5px solid #312e81; border-radius: 12px;
            overflow: hidden; position: relative;
            box-shadow: 0 6px 16px rgba(15, 23, 42, 0.12);
        }
        .header-kartu {
            background: linear-gradient(135deg, #312e81, #4338ca);
            color: #fff; padding: 9px 14px;
            display: flex; justify-content: space-between; align-items: center;
        }
        .header-title { font-weight: 700; font-size: 12px; letter-spacing: 0.4px; }
        .header-sub { font-size: 9.5px; opacity: 0.85; }
        .body-kartu { padding: 12px 14px; font-size: 11px; line-height: 1.65; color: #1e293b; }
        .label { display: inline-block; width: 78px; font-weight: 600; color: #64748b; }
        .barcode-box {
            position: absolute; bottom: 10px; right: 14px;
            font-family: 'Courier New', monospace; font-weight: bold;
            font-size: 11px; background: #f1f5f9; border: 1px solid #94a3b8;
            padding: 2px 8px; border-radius: 4px;
        }
        @media print {
            body { background: #fff; margin: 15px; }
            .no-print { display: none; }
            .kartu { box-shadow: none; }
        }
    </style>
</head>
<body onload="window.print()">
    <div class="toolbar no-print">
        <span>Kartu Anggota (Standar ISO ID-1)</span>
        <button onclick="window.print()" class="btn-print">Print Kartu</button>
    </div>

    <div class="kartu">
        <div class="header-kartu">
            <div>
                <div class="header-title">KARTU ANGGOTA PERPUSTAKAAN</div>
                <div class="header-sub">SIM-PERPUS &bull; Layanan Sirkulasi Terpadu</div>
            </div>
        </div>
        <div class="body-kartu">
            <div><span class="label">No. Anggota</span>: <b><?= htmlspecialchars($a['kode_anggota']) ?></b></div>
            <div><span class="label">Nama</span>: <b><?= htmlspecialchars($a['nama_anggota']) ?></b></div>
            <div><span class="label">NIM / NIK</span>: <?= htmlspecialchars($a['identitas_id']) ?></div>
            <div><span class="label">Unit / Prodi</span>: <?= htmlspecialchars($a['instansi_prodi']) ?></div>
            <div><span class="label">Terdaftar</span>: <?= $a['tgl_daftar'] ?></div>
        </div>
        <div class="barcode-box">||| <?= htmlspecialchars($a['kode_anggota']) ?> |||</div>
    </div>
</body>
</html>