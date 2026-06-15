<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Voucher Biaya — <?= esc($header['nomor_jurnal'] ?? '') ?></title>
    <style>
        /* ── Reset & Base ─────────────────────────────────── */
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Arial', sans-serif;
            font-size: 11pt;
            color: #222;
            background: #fff;
            padding: 24px 32px;
            max-width: 800px;
            margin: 0 auto;
        }

        /* ── Print settings ───────────────────────────────── */
        @page {
            size: A4 portrait;
            margin: 1.5cm 2cm 2cm 2cm;
        }
        @media print {
            body { padding: 0; max-width: 100%; }
            .no-print { display: none !important; }
            .page-break { page-break-before: always; }
        }

        /* ── Kop surat ────────────────────────────────────── */
        .kop {
            display: flex;
            align-items: center;
            gap: 16px;
            border-bottom: 2.5px solid #1a3f6f;
            padding-bottom: 12px;
            margin-bottom: 16px;
        }
        .kop-logo {
            width: 56px;
            height: 56px;
            object-fit: contain;
            flex-shrink: 0;
        }
        .kop-logo-placeholder {
            width: 56px; height: 56px;
            background: #1a3f6f;
            border-radius: 6px;
            display: flex; align-items: center; justify-content: center;
            color: #fff; font-size: 10pt; font-weight: bold; text-align: center;
            line-height: 1.2;
            flex-shrink: 0;
        }
        .kop-text h1 { font-size: 14pt; font-weight: bold; color: #1a3f6f; margin-bottom: 2px; }
        .kop-text p  { font-size: 8.5pt; color: #555; line-height: 1.4; }

        /* ── Judul Voucher ────────────────────────────────── */
        .voucher-title {
            text-align: center;
            margin: 12px 0 16px;
        }
        .voucher-title h2 {
            font-size: 13pt;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 1px;
            border: 2px solid #1a3f6f;
            display: inline-block;
            padding: 4px 24px;
            color: #1a3f6f;
        }

        /* ── Info header ──────────────────────────────────── */
        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 4px 24px;
            margin-bottom: 14px;
            font-size: 10pt;
        }
        .info-grid .row { display: flex; gap: 8px; }
        .info-grid .label { color: #555; width: 130px; flex-shrink: 0; }
        .info-grid .label::after { content: ':'; }
        .info-grid .value { font-weight: 600; }

        /* ── Detail Kegiatan ──────────────────────────────── */
        .kegiatan-box {
            border: 1px solid #cce0ff;
            border-radius: 5px;
            background: #f5f9ff;
            padding: 10px 14px;
            margin-bottom: 14px;
            font-size: 9.5pt;
        }
        .kegiatan-box .kg-title {
            font-size: 8.5pt;
            font-weight: bold;
            text-transform: uppercase;
            color: #1a3f6f;
            letter-spacing: .4px;
            margin-bottom: 8px;
            border-bottom: 1px solid #cce0ff;
            padding-bottom: 4px;
        }
        .kegiatan-box .kg-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 4px 20px;
        }
        .kegiatan-box .kg-row { display: flex; gap: 6px; }
        .kegiatan-box .kg-label { color: #555; width: 110px; flex-shrink: 0; }
        .kegiatan-box .kg-label::after { content: ':'; }
        .kegiatan-box .kg-value { font-weight: 600; }
        .kegiatan-box .kg-full { grid-column: 1 / -1; }
        .kegiatan-box .kg-desc {
            margin-top: 6px;
            background: #fff;
            border: 1px solid #dde8f8;
            border-radius: 3px;
            padding: 6px 10px;
            white-space: pre-wrap;
            font-size: 9pt;
            line-height: 1.5;
            color: #333;
        }

        /* ── Tabel detail ─────────────────────────────────── */
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 10pt;
            margin-bottom: 12px;
        }
        thead th {
            background: #1a3f6f;
            color: #fff;
            padding: 6px 8px;
            text-align: left;
            font-size: 9.5pt;
        }
        thead th.text-right { text-align: right; }
        tbody tr:nth-child(even) { background: #f5f8ff; }
        tbody td {
            padding: 5px 8px;
            border-bottom: 1px solid #e8e8e8;
            vertical-align: top;
        }
        tbody td.text-right { text-align: right; font-variant-numeric: tabular-nums; }
        tbody td.num        { text-align: right; font-variant-numeric: tabular-nums; white-space: nowrap; }
        tfoot td {
            padding: 6px 8px;
            border-top: 2px solid #1a3f6f;
            font-weight: bold;
        }
        tfoot td.num { text-align: right; font-variant-numeric: tabular-nums; }

        /* ── Rekening sumber ──────────────────────────────── */
        .rekening-box {
            border: 1.5px solid #1a3f6f;
            border-radius: 6px;
            padding: 10px 14px;
            margin-bottom: 16px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .rekening-box .label-sm { font-size: 8.5pt; color: #555; margin-bottom: 2px; }
        .rekening-box .rek-nama { font-weight: bold; font-size: 11pt; color: #1a3f6f; }
        .rekening-box .rek-bank { font-size: 9pt; color: #666; }
        .rekening-box .kredit-amount { font-size: 14pt; font-weight: bold; color: #c0392b; }

        /* ── Tanda tangan ─────────────────────────────────── */
        .ttd-area {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 12px;
            margin-top: 32px;
            font-size: 9.5pt;
        }
        .ttd-col {
            text-align: center;
            border: 1px solid #ccc;
            border-radius: 4px;
            padding: 8px 4px;
        }
        .ttd-col .ttd-label { font-weight: bold; color: #1a3f6f; margin-bottom: 48px; }
        .ttd-col .ttd-name  { border-top: 1px solid #555; padding-top: 4px; font-size: 9pt; color: #333; }

        /* ── Footer ───────────────────────────────────────── */
        .print-footer {
            margin-top: 20px;
            padding-top: 8px;
            border-top: 1px solid #ddd;
            font-size: 8pt;
            color: #999;
            text-align: center;
        }

        /* ── No-print toolbar ─────────────────────────────── */
        .toolbar {
            background: #f0f0f0;
            border: 1px solid #ccc;
            border-radius: 6px;
            padding: 10px 16px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 10pt;
        }
        .btn-print {
            background: #c0392b; color: #fff; border: none;
            padding: 6px 16px; border-radius: 4px; cursor: pointer;
            font-size: 10pt; font-weight: bold;
        }
        .btn-print:hover { background: #a93226; }
        .btn-back {
            background: #555; color: #fff; border: none;
            padding: 6px 14px; border-radius: 4px; cursor: pointer;
            font-size: 10pt; text-decoration: none;
        }
        .btn-back:hover { background: #333; }
    </style>
</head>
<body>
<?php
$header     = $header     ?? [];
$details    = $details    ?? [];
$kegiatan   = $kegiatan   ?? [];
$debetRows  = array_values(array_filter($details, fn($d) => (float)$d['debet']  > 0));
$kreditRows = array_values(array_filter($details, fn($d) => (float)$d['kredit'] > 0));
$kreditRow  = $kreditRows[0] ?? null;
$totalDebet = array_sum(array_column($debetRows, 'debet'));

function printRp(float $v): string { return 'Rp ' . number_format($v, 0, ',', '.'); }
function printDt(?string $v): string {
    if (!$v || $v === '0000-00-00 00:00:00') return '—';
    return date('d M Y, H:i', strtotime($v));
}

$hasKegiatan = ! empty($kegiatan) && (
    ($kegiatan['nama_kegiatan'] ?? '') ||
    ($kegiatan['lokasi'] ?? '') ||
    ($kegiatan['tgl_berangkat'] ?? '') ||
    ($kegiatan['tgl_kembali'] ?? '') ||
    ($kegiatan['uraian_kegiatan'] ?? '')
);
?>

<!-- Toolbar (tidak ikut cetak) -->
<div class="toolbar no-print">
    <button class="btn-print" onclick="window.print()">
        &#128438; Cetak / Simpan PDF
    </button>
    <a class="btn-back" href="javascript:history.back()">&#8592; Kembali</a>
    <span style="color:#666; font-size:9pt;">
        Pilih tujuan <strong>Save as PDF</strong> di dialog cetak untuk menyimpan sebagai file PDF.
    </span>
</div>

<!-- Kop Surat -->
<div class="kop">
    <?php
    $logoPath = FCPATH . 'assets/img/logo/logo.png';
    if (file_exists($logoPath)):
    ?>
        <img src="<?= base_url('assets/img/logo/logo.png') ?>" class="kop-logo" alt="Logo">
    <?php else: ?>
        <div class="kop-logo-placeholder">LazisMu</div>
    <?php endif; ?>
    <div class="kop-text">
        <h1>LAZISMU UMS</h1>
        <p>Lembaga Amil Zakat, Infak dan Sedekah Muhammadiyah<br>
           Universitas Muhammadiyah Surakarta<br>
           Jl. A. Yani Tromol Pos 1, Pabelan, Kartasura, Sukoharjo 57162</p>
    </div>
</div>

<!-- Judul -->
<div class="voucher-title">
    <h2>Bukti Pengeluaran Operasional</h2>
</div>

<!-- Info Header -->
<div class="info-grid">
    <div class="row">
        <span class="label">No. Voucher</span>
        <span class="value" style="font-family:monospace;"><?= esc($header['nomor_jurnal'] ?? '—') ?></span>
    </div>
    <div class="row">
        <span class="label">Tanggal</span>
        <span class="value"><?= isset($header['tanggal']) ? date('d F Y', strtotime($header['tanggal'])) : '—' ?></span>
    </div>
    <div class="row">
        <span class="label">Periode</span>
        <span class="value"><?= esc($header['nama_periode'] ?? '—') ?></span>
    </div>
    <div class="row">
        <span class="label">Jenis Dana</span>
        <span class="value"><?= esc($header['nama_dana'] ?? '—') ?></span>
    </div>
    <div class="row" style="grid-column: 1 / -1;">
        <span class="label">Uraian Biaya</span>
        <span class="value"><?= esc($header['uraian'] ?? '—') ?></span>
    </div>
    <?php if ($header['keterangan'] ?? ''): ?>
    <div class="row" style="grid-column: 1 / -1;">
        <span class="label">Keterangan</span>
        <span class="value" style="font-weight:normal;"><?= esc($header['keterangan']) ?></span>
    </div>
    <?php endif; ?>
</div>

<!-- Detail Kegiatan (jika ada) -->
<?php if ($hasKegiatan): ?>
<div class="kegiatan-box">
    <div class="kg-title">&#128205; Detail Kegiatan</div>
    <div class="kg-grid">
        <?php if ($kegiatan['nama_kegiatan'] ?? ''): ?>
        <div class="kg-row">
            <span class="kg-label">Nama Kegiatan</span>
            <span class="kg-value"><?= esc($kegiatan['nama_kegiatan']) ?></span>
        </div>
        <?php endif; ?>
        <?php if ($kegiatan['lokasi'] ?? ''): ?>
        <div class="kg-row">
            <span class="kg-label">Lokasi</span>
            <span class="kg-value"><?= esc($kegiatan['lokasi']) ?></span>
        </div>
        <?php endif; ?>
        <?php if ($kegiatan['tgl_berangkat'] ?? ''): ?>
        <div class="kg-row">
            <span class="kg-label">Tgl Berangkat</span>
            <span class="kg-value"><?= printDt($kegiatan['tgl_berangkat']) ?></span>
        </div>
        <?php endif; ?>
        <?php if ($kegiatan['tgl_kembali'] ?? ''): ?>
        <div class="kg-row">
            <span class="kg-label">Tgl Kembali</span>
            <span class="kg-value"><?= printDt($kegiatan['tgl_kembali']) ?></span>
        </div>
        <?php endif; ?>
        <?php if ($kegiatan['uraian_kegiatan'] ?? ''): ?>
        <div class="kg-full">
            <div class="kg-label" style="width:auto; margin-bottom:4px;">Deskripsi Kegiatan</div>
            <div class="kg-desc"><?= esc($kegiatan['uraian_kegiatan']) ?></div>
        </div>
        <?php endif; ?>
    </div>
</div>
<?php endif; ?>

<!-- Tabel Rincian Pengeluaran -->
<table>
    <thead>
        <tr>
            <th style="width:30px;">#</th>
            <th style="width:180px;">Kode Akun</th>
            <th>Nama Akun / Keterangan</th>
            <th class="text-right" style="width:140px;">Jumlah (Rp)</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($debetRows as $i => $d): ?>
        <tr>
            <td style="text-align:center;"><?= $i + 1 ?></td>
            <td style="font-family:monospace; font-size:9.5pt;"><?= esc($d['nomor_akun'] ?? '—') ?></td>
            <td>
                <strong><?= esc($d['nama_akun'] ?? '—') ?></strong>
                <?php if ($d['uraian'] ?? ''): ?>
                    <br><span style="color:#666; font-size:9pt;"><?= esc($d['uraian']) ?></span>
                <?php endif; ?>
            </td>
            <td class="num"><?= printRp((float)$d['debet']) ?></td>
        </tr>
        <?php endforeach; ?>
    </tbody>
    <tfoot>
        <tr>
            <td colspan="3" style="text-align:right;">Total Pengeluaran</td>
            <td class="num"><?= printRp($totalDebet) ?></td>
        </tr>
    </tfoot>
</table>

<!-- Rekening Sumber -->
<?php if ($kreditRow): ?>
<div class="rekening-box">
    <div>
        <div class="label-sm">Dibayarkan dari Rekening (Kredit)</div>
        <div class="rek-nama">
            <?= esc($kreditRow['nama_rekening'] ?? $kreditRow['nama_akun'] ?? '—') ?>
        </div>
        <?php if ($kreditRow['nama_bank'] ?? ''): ?>
            <div class="rek-bank"><?= esc($kreditRow['nama_bank']) ?></div>
        <?php endif; ?>
        <div style="font-family:monospace; font-size:9pt; color:#888; margin-top:2px;">
            <?= esc($kreditRow['nomor_akun'] ?? '') ?>
        </div>
    </div>
    <div style="text-align:right;">
        <div class="label-sm">Total Dibayar</div>
        <div class="kredit-amount"><?= printRp((float)($kreditRow['kredit'] ?? 0)) ?></div>
    </div>
</div>
<?php endif; ?>

<!-- Tanda Tangan -->
<div class="ttd-area">
    <div class="ttd-col">
        <div class="ttd-label">Dibuat Oleh</div>
        <div class="ttd-name">( &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; )</div>
    </div>
    <div class="ttd-col">
        <div class="ttd-label">Disetujui Oleh</div>
        <div class="ttd-name">( &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; )</div>
    </div>
    <div class="ttd-col">
        <div class="ttd-label">Bendahara</div>
        <div class="ttd-name">( &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; )</div>
    </div>
</div>

<!-- Footer -->
<div class="print-footer">
    Dicetak oleh Sistem Informasi Keuangan LazisMu UMS &nbsp;|&nbsp;
    <?= date('d M Y, H:i') ?> &nbsp;|&nbsp;
    <?= esc($header['nomor_jurnal'] ?? '') ?>
</div>

<script>
    // Auto-print only when opened in new tab (not when navigating back)
    if (!document.referrer.includes('/cetak/')) {
        window.addEventListener('load', function () {
            setTimeout(function () { window.print(); }, 400);
        });
    }
</script>
</body>
</html>
