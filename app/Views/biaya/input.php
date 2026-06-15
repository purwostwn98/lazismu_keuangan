<?= $this->extend('layouts/main') ?>

<?= $this->section('styles') ?>
<style>
.detail-table th, .detail-table td { vertical-align: middle; }
.detail-table select, .detail-table input { font-size: .82rem; }
.btn-del-row {
    background: none; border: none; color: #dc3545; padding: 2px 6px;
    border-radius: 4px; cursor: pointer; line-height:1;
}
.btn-del-row:hover { background: #fff0f0; }
.row-num { width: 36px; text-align: center; color: #999; font-size: .8rem; }
.total-bar { background: #f8f9fa; border-top: 2px solid #dee2e6; }
.section-divider {
    border-top: 1px dashed #dee2e6;
    margin: 16px 0 8px;
    position: relative;
}
.section-divider span {
    position: absolute;
    top: -10px;
    left: 0;
    background: #fff;
    padding-right: 10px;
    font-size: .75rem;
    font-weight: 600;
    text-transform: uppercase;
    color: #999;
    letter-spacing: .5px;
}
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<?php
$periodeList  = $periodeList  ?? [];
$periodeAktif = $periodeAktif ?? null;
$rekeningList = $rekeningList ?? [];
$akunList     = $akunList     ?? [];
$old = fn(string $k, $d = '') => old($k, $d);
?>

<div class="container-fluid">

    <!-- Header -->
    <div class="d-flex justify-content-between align-items-start mb-4">
        <div>
            <h4 class="mb-0 fw-bold">Input Biaya Operasional</h4>
            <small class="text-muted">Catat pengeluaran operasional kegiatan dengan satu rekening sumber dana</small>
        </div>
        <a href="<?= base_url('biaya') ?>" class="btn btn-outline-secondary btn-sm">
            <i class="fa fa-arrow-left me-1"></i> Kembali
        </a>
    </div>

    <!-- Alerts -->
    <?php if (session('errors')): ?>
        <div class="alert alert-danger alert-dismissible fade show py-2 mb-3">
            <ul class="mb-0 ps-3 small">
                <?php foreach (session('errors') as $e): ?><li><?= esc($e) ?></li><?php endforeach; ?>
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    <?php if (session('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show py-2 mb-3">
            <i class="fa fa-triangle-exclamation me-1"></i><?= esc(session('error')) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <form method="post" action="<?= base_url('biaya/store') ?>" id="formBiaya">
    <?= csrf_field() ?>

    <!-- ─── Info Kegiatan ─── -->
    <div class="card border-0 shadow-sm mb-3">
        <div class="card-header bg-transparent border-bottom py-2">
            <span class="fw-semibold small text-uppercase text-muted">
                <i class="fa fa-calendar me-1"></i> Informasi Transaksi
            </span>
        </div>
        <div class="card-body">
            <div class="row g-3">

                <!-- Tanggal -->
                <div class="col-12 col-md-3">
                    <label class="form-label form-label-sm fw-semibold">
                        Tanggal <span class="text-danger">*</span>
                    </label>
                    <input type="date" name="tanggal" id="tanggal"
                           class="form-control form-control-sm"
                           value="<?= $old('tanggal', date('Y-m-d')) ?>"
                           required onchange="autoDetectPeriode()">
                </div>

                <!-- Periode -->
                <div class="col-12 col-md-3">
                    <label class="form-label form-label-sm fw-semibold">
                        Periode <span class="text-danger">*</span>
                    </label>
                    <select name="periode_id" id="periodeId" class="form-select form-select-sm" required>
                        <option value="">— Pilih Periode —</option>
                        <?php foreach ($periodeList as $p): ?>
                            <option value="<?= $p['id'] ?>"
                                data-bulan="<?= $p['bulan'] ?>" data-tahun="<?= $p['tahun'] ?>"
                                <?= $old('periode_id', $periodeAktif['id'] ?? '') == $p['id'] ? 'selected' : '' ?>>
                                <?= esc($p['nama']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <div class="form-text">Hanya periode aktif (belum ditutup).</div>
                </div>

                <!-- No. Jurnal -->
                <div class="col-12 col-md-3">
                    <label class="form-label form-label-sm fw-semibold text-muted">No. Jurnal</label>
                    <input type="text" class="form-control form-control-sm bg-light text-muted font-monospace"
                           value="BYA / auto-generate" readonly tabindex="-1">
                </div>

                <!-- Uraian Singkat -->
                <div class="col-12 col-md-9">
                    <label class="form-label form-label-sm fw-semibold">
                        Uraian / Judul Biaya <span class="text-danger">*</span>
                    </label>
                    <input type="text" name="uraian" id="uraian"
                           class="form-control form-control-sm"
                           value="<?= esc($old('uraian')) ?>"
                           maxlength="255" required
                           placeholder="cth: Biaya Konsumsi Rapat Koordinasi Agustus 2026">
                </div>

                <!-- Keterangan -->
                <div class="col-12">
                    <label class="form-label form-label-sm fw-semibold">
                        Keterangan <span class="text-muted fw-normal">(opsional)</span>
                    </label>
                    <textarea name="keterangan" class="form-control form-control-sm" rows="2"
                              maxlength="500" placeholder="Catatan tambahan..."><?= esc($old('keterangan')) ?></textarea>
                </div>

                <!-- ─── Detail Kegiatan ─── -->
                <div class="col-12">
                    <div class="section-divider"><span>Detail Kegiatan</span></div>
                </div>

                <!-- Program / Nama Kegiatan -->
                <div class="col-12 col-md-6">
                    <label class="form-label form-label-sm fw-semibold">
                        Program / Nama Kegiatan
                        <span class="text-muted fw-normal">(opsional)</span>
                    </label>
                    <input type="text" name="nama_kegiatan" class="form-control form-control-sm"
                           value="<?= esc($old('nama_kegiatan')) ?>"
                           maxlength="255"
                           placeholder="cth: Rapat Koordinasi, Survei Lapangan Penerima">
                </div>

                <!-- Lokasi -->
                <div class="col-12 col-md-6">
                    <label class="form-label form-label-sm fw-semibold">
                        Lokasi
                        <span class="text-muted fw-normal">(opsional)</span>
                    </label>
                    <input type="text" name="lokasi" class="form-control form-control-sm"
                           value="<?= esc($old('lokasi')) ?>"
                           maxlength="255"
                           placeholder="cth: Kantor LazisMu UMS, Surakarta">
                </div>

                <!-- Waktu Berangkat -->
                <div class="col-12 col-md-3">
                    <label class="form-label form-label-sm fw-semibold">
                        Waktu Berangkat
                        <span class="text-muted fw-normal">(opsional)</span>
                    </label>
                    <input type="datetime-local" name="tgl_berangkat" class="form-control form-control-sm"
                           value="<?= esc($old('tgl_berangkat')) ?>">
                </div>

                <!-- Waktu Kembali -->
                <div class="col-12 col-md-3">
                    <label class="form-label form-label-sm fw-semibold">
                        Waktu Kembali
                        <span class="text-muted fw-normal">(opsional)</span>
                    </label>
                    <input type="datetime-local" name="tgl_kembali" class="form-control form-control-sm"
                           value="<?= esc($old('tgl_kembali')) ?>">
                </div>

                <!-- Uraian Kegiatan -->
                <div class="col-12">
                    <label class="form-label form-label-sm fw-semibold">
                        Deskripsi Kegiatan
                        <span class="text-muted fw-normal">(opsional)</span>
                    </label>
                    <textarea name="uraian_kegiatan" class="form-control form-control-sm" rows="3"
                              maxlength="2000"
                              placeholder="Deskripsi lengkap kegiatan: agenda, peserta, tujuan, hasil kegiatan, dsb..."><?= esc($old('uraian_kegiatan')) ?></textarea>
                </div>

            </div>
        </div>
    </div>

    <!-- ─── Rekening Sumber Dana ─── -->
    <div class="card border-0 shadow-sm mb-3">
        <div class="card-header bg-transparent border-bottom py-2">
            <span class="fw-semibold small text-uppercase text-muted">
                <i class="fa fa-building-columns me-1"></i> Rekening Sumber Dana (Kredit)
            </span>
        </div>
        <div class="card-body">
            <div class="row g-3 align-items-start">
                <div class="col-12 col-md-6">
                    <label class="form-label form-label-sm fw-semibold">
                        Rekening Bank <span class="text-danger">*</span>
                    </label>
                    <select name="rekening_id" id="rekeningId" class="form-select form-select-sm" required>
                        <option value="">— Pilih Rekening —</option>
                        <?php
                        $currentDana = null;
                        foreach ($rekeningList as $rek):
                            if ($currentDana !== $rek['nama_dana']):
                                if ($currentDana !== null) echo '</optgroup>';
                                $currentDana = $rek['nama_dana'];
                                echo '<optgroup label="' . esc($currentDana) . '">';
                            endif;
                        ?>
                            <option value="<?= $rek['id'] ?>"
                                data-jenis-dana-id="<?= $rek['jenis_dana_id'] ?>"
                                data-akun-id="<?= $rek['akun_id'] ?>"
                                data-nama-dana="<?= esc($rek['nama_dana']) ?>"
                                <?= $old('rekening_id') == $rek['id'] ? 'selected' : '' ?>>
                                <?= esc($rek['nama']) ?>
                                <?= $rek['nomor_rekening'] ? '— ' . $rek['nomor_rekening'] : '' ?>
                                (<?= esc($rek['bank']) ?>)
                            </option>
                        <?php endforeach; if ($currentDana !== null) echo '</optgroup>'; ?>
                    </select>
                    <div class="form-text"><i class="fa fa-arrow-left fa-xs me-1"></i>Akun kas/rekening yang di-kredit sebagai sumber pembayaran.</div>
                </div>
                <div class="col-12 col-md-4">
                    <label class="form-label form-label-sm fw-semibold text-muted">Dana Terdeteksi</label>
                    <div id="danaBadge" class="mt-1">
                        <span class="badge bg-light text-muted border">— pilih rekening —</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ─── Rincian Pengeluaran ─── -->
    <div class="card border-0 shadow-sm mb-3">
        <div class="card-header bg-transparent border-bottom py-2 d-flex justify-content-between align-items-center">
            <span class="fw-semibold small text-uppercase text-muted">
                <i class="fa fa-list-ul me-1"></i> Rincian Pengeluaran (Debet)
            </span>
            <button type="button" id="btnAddRow" class="btn btn-sm btn-outline-primary py-0 px-2">
                <i class="fa fa-plus fa-xs me-1"></i> Tambah Baris
            </button>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-sm detail-table mb-0" id="detailTable">
                    <thead class="table-light">
                        <tr>
                            <th class="row-num px-2">#</th>
                            <th style="min-width:260px;">Akun Beban / Biaya</th>
                            <th style="min-width:200px;">Keterangan Baris</th>
                            <th style="width:160px;" class="text-end">Jumlah (Rp)</th>
                            <th style="width:40px;"></th>
                        </tr>
                    </thead>
                    <tbody id="detailBody"></tbody>
                    <tfoot class="total-bar">
                        <tr>
                            <td colspan="3" class="px-3 text-end fw-semibold small">Total Pengeluaran:</td>
                            <td class="text-end fw-bold pe-3" id="grandTotal">Rp 0</td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>

    <!-- ─── Preview Jurnal ─── -->
    <div class="card border-0 shadow-sm mb-3" id="cardPreview" style="display:none;">
        <div class="card-header bg-transparent border-bottom py-2">
            <span class="fw-semibold small text-uppercase text-muted">
                <i class="fa fa-eye me-1"></i> Preview Jurnal
            </span>
        </div>
        <div class="card-body p-0">
            <table class="table table-sm mb-0" style="font-size:.83rem;">
                <thead class="table-light">
                    <tr>
                        <th class="px-3" style="width:45%">Akun</th>
                        <th class="text-end">Debet</th>
                        <th class="text-end pe-3">Kredit</th>
                    </tr>
                </thead>
                <tbody id="previewBody"></tbody>
                <tfoot class="table-light">
                    <tr>
                        <td class="px-3 fw-semibold">Total</td>
                        <td class="text-end fw-semibold" id="previewTotalDebet">—</td>
                        <td class="text-end pe-3 fw-semibold" id="previewTotalKredit">—</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    <!-- Submit -->
    <div class="d-flex gap-2 justify-content-end mb-4">
        <a href="<?= base_url('biaya') ?>" class="btn btn-outline-secondary">Batal</a>
        <button type="submit" id="btnSubmit" class="btn btn-primary px-4" disabled>
            <i class="fa fa-save me-1"></i> Simpan Biaya
        </button>
    </div>

    </form>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
(function () {
    // ── Data dari PHP ──────────────────────────────────────────
    const akunOptions = <?= json_encode(array_map(fn($a) => [
        'value' => $a['id'],
        'text'  => $a['nomor_akun'] . ' — ' . $a['nama_akun'],
        'tipe'  => $a['tipe'],
    ], $akunList)) ?>;

    // ── Periode auto-detect ───────────────────────────────────
    const periodeOptions = <?= json_encode(array_map(fn($p) => [
        'id'    => $p['id'],
        'bulan' => (int)$p['bulan'],
        'tahun' => (int)$p['tahun'],
    ], $periodeList)) ?>;

    window.autoDetectPeriode = function () {
        const val = document.getElementById('tanggal').value;
        if (!val) return;
        const d = new Date(val);
        const m = d.getMonth() + 1;
        const y = d.getFullYear();
        const found = periodeOptions.find(p => p.bulan === m && p.tahun === y);
        if (found) document.getElementById('periodeId').value = found.id;
    };

    // ── Rekening select → badge dana ─────────────────────────
    const rekeningEl = document.getElementById('rekeningId');
    new TomSelect('#rekeningId', { maxOptions: 100, allowEmptyOption: true });

    rekeningEl.addEventListener('change', function () {
        const opt = this.options[this.selectedIndex];
        const badge = document.getElementById('danaBadge');
        if (opt && opt.value) {
            const nama = opt.dataset.namaDana || '—';
            badge.innerHTML = `<span class="badge" style="background:#E8622A;">${nama}</span>`;
        } else {
            badge.innerHTML = '<span class="badge bg-light text-muted border">— pilih rekening —</span>';
        }
        updatePreview();
    });

    // ── Rows ──────────────────────────────────────────────────
    let rowCount = 0;
    const tbody  = document.getElementById('detailBody');

    function fmtRp(n) {
        return 'Rp ' + (n || 0).toLocaleString('id-ID', { maximumFractionDigits: 0 });
    }

    function parseNum(v) {
        return parseFloat(String(v).replace(/\./g, '').replace(',', '.')) || 0;
    }

    function addRow() {
        rowCount++;
        const tr = document.createElement('tr');
        tr.dataset.row = rowCount;
        tr.innerHTML = `
            <td class="row-num">${rowCount}</td>
            <td>
                <select name="akun_id[]" class="form-select form-select-sm" required>
                    <option value="">— Pilih Akun —</option>
                    ${akunOptions.map(o => `<option value="${o.value}" data-tipe="${o.tipe}">${o.text}</option>`).join('')}
                </select>
            </td>
            <td>
                <input type="text" name="uraian_det[]" class="form-control form-control-sm"
                       placeholder="Keterangan baris (opsional)" maxlength="255">
            </td>
            <td>
                <input type="text" name="jumlah[]" class="form-control form-control-sm text-end jumlah-input"
                       placeholder="0" autocomplete="off" required>
            </td>
            <td class="text-center">
                <button type="button" class="btn-del-row" title="Hapus baris">
                    <i class="fa fa-times"></i>
                </button>
            </td>`;
        tbody.appendChild(tr);

        // Tom Select on akun
        new TomSelect(tr.querySelector('select[name="akun_id[]"]'), {
            maxOptions: 500,
            allowEmptyOption: true,
            onChange() { updatePreview(); },
        });

        // Jumlah input
        const jumlahEl = tr.querySelector('.jumlah-input');
        jumlahEl.addEventListener('input', function () {
            this.value = this.value.replace(/[^0-9.,]/g, '');
            recalculate();
        });
        jumlahEl.addEventListener('blur', function () {
            const n = parseNum(this.value);
            if (n > 0) this.value = n.toLocaleString('id-ID', { maximumFractionDigits: 0 });
            recalculate();
        });

        // Delete row
        tr.querySelector('.btn-del-row').addEventListener('click', function () {
            if (tbody.rows.length > 1) {
                tr.remove();
                reNumber();
                recalculate();
            } else {
                alert('Minimal 1 baris pengeluaran diperlukan.');
            }
        });

        recalculate();
    }

    function reNumber() {
        rowCount = 0;
        Array.from(tbody.rows).forEach(r => {
            rowCount++;
            r.querySelector('.row-num').textContent = rowCount;
        });
    }

    function recalculate() {
        let total = 0;
        tbody.querySelectorAll('.jumlah-input').forEach(inp => { total += parseNum(inp.value); });
        document.getElementById('grandTotal').textContent = fmtRp(total);
        updatePreview();

        const hasRows     = tbody.rows.length > 0;
        const hasRekening = !!document.getElementById('rekeningId').value;
        document.getElementById('btnSubmit').disabled = !(hasRows && hasRekening && total > 0);
    }

    function updatePreview() {
        const rekeningEl  = document.getElementById('rekeningId');
        const rekeningOpt = rekeningEl.options[rekeningEl.selectedIndex];

        const rows = [];
        let total  = 0;

        tbody.querySelectorAll('tr').forEach(tr => {
            const sel  = tr.querySelector('select[name="akun_id[]"]');
            const jml  = parseNum(tr.querySelector('.jumlah-input')?.value ?? '0');
            if (!sel || !sel.value || jml <= 0) return;
            const label = sel.options[sel.selectedIndex]?.text || '—';
            rows.push({ label, jml });
            total += jml;
        });

        const preview = document.getElementById('cardPreview');
        if (rows.length === 0 || !rekeningOpt?.value || total <= 0) {
            preview.style.display = 'none';
            return;
        }

        const pbody = document.getElementById('previewBody');
        pbody.innerHTML = rows.map(r => `
            <tr>
                <td class="px-3 text-muted">${r.label}</td>
                <td class="text-end text-success fw-semibold">${fmtRp(r.jml)}</td>
                <td class="text-end pe-3 text-muted">—</td>
            </tr>`).join('') + `
            <tr>
                <td class="px-3 text-muted fst-italic ps-4">${rekeningOpt.text.trim()}</td>
                <td class="text-end text-muted">—</td>
                <td class="text-end pe-3 text-danger fw-semibold">${fmtRp(total)}</td>
            </tr>`;

        document.getElementById('previewTotalDebet').textContent  = fmtRp(total);
        document.getElementById('previewTotalKredit').textContent = fmtRp(total);
        preview.style.display = '';
    }

    // ── Init ──────────────────────────────────────────────────
    document.getElementById('btnAddRow').addEventListener('click', addRow);

    // Strip formatting before submit
    document.getElementById('formBiaya').addEventListener('submit', function () {
        tbody.querySelectorAll('.jumlah-input').forEach(inp => {
            inp.value = String(parseNum(inp.value));
        });
    });

    // Start with 2 rows
    addRow();
    addRow();
    autoDetectPeriode();
})();
</script>
<?= $this->endSection() ?>
