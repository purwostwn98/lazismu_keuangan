<?= $this->extend('layouts/main') ?>

<?= $this->section('styles') ?>
<style>
    .detail-table th, .detail-table td { padding: .3rem .4rem; vertical-align: middle; }
    .detail-table select, .detail-table input { font-size: .82rem; }
    .balance-ok   { color: #198754; font-weight: 700; }
    .balance-err  { color: #dc3545; font-weight: 700; }
    #balance-bar  { font-size: .85rem; background: #f8f9fa; border-radius: 6px; padding: .5rem .8rem; }
    .row-num      { color: #adb5bd; font-size: .75rem; text-align: center; }
    .btn-del-row  { color: #dc3545; border: none; background: none; padding: 0 4px; line-height:1; }
    .btn-del-row:hover { color: #a71d2a; }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<?php
$periodeList   = $periodeList   ?? [];
$periodeAktif  = $periodeAktif  ?? null;
$jenisDanaList = $jenisDanaList ?? [];
$akunList      = $akunList      ?? [];
$rekeningList  = $rekeningList  ?? [];
$refJurnal     = $refJurnal     ?? null;
$prefillRows   = $prefillRows   ?? [];
$old           = fn(string $k, $def = '') => old($k, $def);

$defJenisTrx  = $refJurnal ? 'koreksi' : 'biaya';
$defJenisDana = $refJurnal ? $refJurnal['jenis_dana_id'] : '';
$defUraian    = $refJurnal ? 'Pembalik [' . $refJurnal['nomor_jurnal'] . ']: ' . $refJurnal['uraian'] : '';
?>

<div class="container-fluid">

    <div class="d-flex justify-content-between align-items-start mb-3">
        <div>
            <h4 class="mb-0 fw-bold">Input Jurnal</h4>
            <small class="text-muted">Entri jurnal biaya operasional atau penyesuaian</small>
        </div>
        <a href="<?= base_url('jurnal') ?>" class="btn btn-sm btn-outline-secondary">
            <i class="fa fa-arrow-left me-1"></i> Buku Jurnal
        </a>
    </div>

    <!-- Errors -->
    <?php if (session('errors')): ?>
        <div class="alert alert-danger alert-dismissible fade show py-2 small">
            <i class="fa fa-exclamation-triangle me-1"></i>
            <ul class="mb-0 ps-3">
                <?php foreach (session('errors') as $e): ?>
                    <li><?= esc($e) ?></li>
                <?php endforeach; ?>
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    <?php if (session('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show py-2">
            <i class="fa fa-exclamation-circle me-1"></i><?= session('error') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if ($refJurnal): ?>
        <div class="alert alert-info py-2 small mb-3 d-flex align-items-center gap-2">
            <i class="fa fa-rotate-left fa-lg"></i>
            <div>
                Membalik jurnal <strong><?= esc($refJurnal['nomor_jurnal']) ?></strong>
                — <?= esc($refJurnal['uraian']) ?>
            </div>
        </div>
    <?php endif; ?>

    <form method="post" action="<?= base_url('jurnal/store') ?>" id="frmJurnal">
    <?= csrf_field() ?>
    <?php if ($refJurnal): ?>
        <input type="hidden" name="ref_jurnal_id" value="<?= $refJurnal['id'] ?>">
    <?php endif; ?>

    <!-- ── Header ──────────────────────────────────────────── -->
    <div class="card border-0 shadow-sm mb-3">
        <div class="card-header py-2 fw-semibold" style="background:#1a3f6f;color:#fff;">
            <i class="fa fa-file-alt me-1"></i> Header Jurnal
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-sm-3">
                    <label class="form-label small fw-semibold">Tanggal <span class="text-danger">*</span></label>
                    <input type="date" name="tanggal" class="form-control form-control-sm"
                           value="<?= $old('tanggal', date('Y-m-d')) ?>" required>
                </div>
                <div class="col-sm-3">
                    <label class="form-label small fw-semibold">Periode <span class="text-danger">*</span></label>
                    <select name="periode_id" class="form-select form-select-sm" required>
                        <option value="">— Pilih Periode —</option>
                        <?php foreach ($periodeList as $p): ?>
                            <option value="<?= $p['id'] ?>"
                                <?= $old('periode_id', $periodeAktif['id'] ?? '') == $p['id'] ? 'selected' : '' ?>>
                                <?= esc($p['nama']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-sm-3">
                    <label class="form-label small fw-semibold">Jenis Dana <span class="text-danger">*</span></label>
                    <select name="jenis_dana_id" class="form-select form-select-sm" required>
                        <option value="">— Pilih Dana —</option>
                        <?php foreach ($jenisDanaList as $jd): ?>
                            <option value="<?= $jd['id'] ?>"
                                <?= $old('jenis_dana_id', $defJenisDana) == $jd['id'] ? 'selected' : '' ?>>
                                <?= esc($jd['nama']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-sm-3">
                    <label class="form-label small fw-semibold">Jenis Transaksi <span class="text-danger">*</span></label>
                    <select name="jenis_transaksi" class="form-select form-select-sm" required>
                        <option value="biaya"       <?= $old('jenis_transaksi', $defJenisTrx) === 'biaya'       ? 'selected' : '' ?>>Biaya Operasional</option>
                        <option value="jurnal_umum" <?= $old('jenis_transaksi', $defJenisTrx) === 'jurnal_umum' ? 'selected' : '' ?>>Jurnal Umum / Penyesuaian</option>
                        <option value="koreksi"     <?= $old('jenis_transaksi', $defJenisTrx) === 'koreksi'     ? 'selected' : '' ?>>Jurnal Koreksi</option>
                    </select>
                </div>
                <div class="col-sm-8">
                    <label class="form-label small fw-semibold">Uraian <span class="text-danger">*</span></label>
                    <input type="text" name="uraian" class="form-control form-control-sm"
                           placeholder="Deskripsi singkat transaksi" maxlength="255"
                           value="<?= esc($old('uraian', $defUraian)) ?>" required>
                </div>
                <div class="col-sm-4">
                    <label class="form-label small fw-semibold">Keterangan</label>
                    <input type="text" name="keterangan" class="form-control form-control-sm"
                           placeholder="Opsional" maxlength="255"
                           value="<?= esc($old('keterangan')) ?>">
                </div>
            </div>
        </div>
    </div>

    <!-- ── Detail Jurnal ────────────────────────────────────── -->
    <div class="card border-0 shadow-sm mb-3">
        <div class="card-header py-2 d-flex justify-content-between align-items-center"
             style="background:#1a3f6f;color:#fff;">
            <span><i class="fa fa-table me-1"></i> Detail Debet / Kredit</span>
            <button type="button" id="btnAddRow" class="btn btn-sm btn-light py-0 px-2">
                <i class="fa fa-plus me-1"></i> Tambah Baris
            </button>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-sm table-bordered mb-0 detail-table" id="tblDetail">
                    <thead class="table-light">
                        <tr>
                            <th style="width:32px;" class="text-center">#</th>
                            <th style="min-width:280px;">Akun <span class="text-danger">*</span></th>
                            <th style="min-width:180px;">Rekening Bank</th>
                            <th style="min-width:160px;">Uraian Baris</th>
                            <th style="width:130px;" class="text-end">Debet (Rp)</th>
                            <th style="width:130px;" class="text-end">Kredit (Rp)</th>
                            <th style="width:36px;"></th>
                        </tr>
                    </thead>
                    <tbody id="detailBody">
                        <!-- rows injected by JS -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- ── Balance indicator ────────────────────────────────── -->
    <div id="balance-bar" class="mb-3 d-flex gap-4 align-items-center">
        <div>Total Debet: <span id="totalDebet" class="fw-bold">0</span></div>
        <div>Total Kredit: <span id="totalKredit" class="fw-bold">0</span></div>
        <div>Selisih: <span id="selisih" class="fw-bold balance-err">0</span></div>
        <div id="balanceMsg" class="ms-auto"></div>
    </div>

    <!-- ── Submit ────────────────────────────────────────────── -->
    <div class="d-flex gap-2">
        <button type="submit" id="btnSubmit" class="btn btn-primary" disabled>
            <i class="fa fa-save me-1"></i> Simpan Jurnal
        </button>
        <a href="<?= base_url('jurnal') ?>" class="btn btn-outline-secondary">Batal</a>
    </div>

    </form>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
(function () {
    // ── Data from PHP ──────────────────────────────────────────
    const periodeData = <?= json_encode(array_map(fn($p) => [
        'id'    => $p['id'],
        'bulan' => (int)$p['bulan'],
        'tahun' => (int)$p['tahun'],
    ], $periodeList)) ?>;

    const akunOptions = <?= json_encode(array_map(fn($a) => [
        'id'    => $a['id'],
        'label' => $a['nomor_akun'] . ' — ' . $a['nama_akun'],
    ], $akunList)) ?>;

    const rekOptions = <?= json_encode(array_map(fn($r) => [
        'id'     => $r['id'],
        'label'  => $r['nama'] . ' (' . $r['nama_dana'] . ')',
        'akunId' => (int)($r['akun_id'] ?? 0),
    ], $rekeningList)) ?>;

    // Lookup: akun.id → rekening_bank.id (hanya akun yang punya rekening)
    const akunToRek = {};
    rekOptions.forEach(r => { if (r.akunId) akunToRek[r.akunId] = String(r.id); });

    const prefillRows = <?= json_encode($prefillRows) ?>;

    // ── Helpers ────────────────────────────────────────────────
    function fmtNum(n) {
        return n === 0 ? '0' : new Intl.NumberFormat('id-ID').format(Math.abs(n));
    }

    function parseInput(val) {
        return parseFloat(val.replace(/\./g, '').replace(',', '.')) || 0;
    }

    function makeSelect(name, options, placeholder) {
        let html = `<select name="${name}[]" class="form-select form-select-sm">
            <option value="">— ${placeholder} —</option>`;
        options.forEach(o => { html += `<option value="${o.id}">${o.label}</option>`; });
        return html + '</select>';
    }

    function makeInput(name, cls = '') {
        return `<input type="text" name="${name}[]" class="form-control form-control-sm text-end num-input ${cls}"
                       placeholder="0" autocomplete="off">`;
    }

    // ── Add row ────────────────────────────────────────────────
    let rowCount = 0;

    function addRow(prefill = null) {
        rowCount++;
        const tbody = document.getElementById('detailBody');
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td class="row-num">${rowCount}</td>
            <td>${makeSelect('akun_id', akunOptions, 'Pilih Akun')}</td>
            <td>${makeSelect('rekening_id', rekOptions, 'Opsional')}</td>
            <td><input type="text" name="uraian_det[]" class="form-control form-control-sm"
                       placeholder="Keterangan baris" maxlength="255"></td>
            <td>${makeInput('debet', 'debet-col')}</td>
            <td>${makeInput('kredit', 'kredit-col')}</td>
            <td class="text-center">
                <button type="button" class="btn-del-row" title="Hapus baris">
                    <i class="fa fa-times"></i>
                </button>
            </td>`;
        tbody.appendChild(tr);

        // Tom Select on new row selects
        const selects = tr.querySelectorAll('select');
        const tsAkun = new TomSelect(selects[0], { maxOptions: 500, allowEmptyOption: true });
        const tsRek  = new TomSelect(selects[1], { maxOptions: 500, allowEmptyOption: true });

        // Auto-fill rekening saat akun yang punya rekening dipilih
        tsAkun.on('change', function (val) {
            const rekId = akunToRek[val];
            if (rekId !== undefined) tsRek.setValue(rekId);
        });

        // Pre-fill dari data pembalik
        if (prefill) {
            if (prefill.akun_id) tsAkun.setValue(String(prefill.akun_id));
            // Override rekening: gunakan persis nilai dari jurnal asal
            if (prefill.rekening_id) {
                tsRek.setValue(String(prefill.rekening_id));
            } else {
                tsRek.clear();
            }
            tr.querySelector('[name="uraian_det[]"]').value = prefill.uraian_det || '';
            const fmt = v => v > 0 ? new Intl.NumberFormat('id-ID').format(v) : '';
            tr.querySelector('.debet-col').value  = fmt(prefill.debet);
            tr.querySelector('.kredit-col').value = fmt(prefill.kredit);
            recalculate();
        }

        // Delete row
        tr.querySelector('.btn-del-row').addEventListener('click', function () {
            if (tbody.rows.length > 2) {
                tr.remove();
                reNumber();
                recalculate();
            } else {
                alert('Minimum 2 baris detail jurnal diperlukan.');
            }
        });

        // Recalculate on input
        tr.querySelectorAll('.num-input').forEach(inp => {
            inp.addEventListener('input', function () {
                // Allow only digits, dot, comma
                this.value = this.value.replace(/[^0-9.,]/g, '');
                recalculate();
            });
        });

        recalculate();
    }

    function reNumber() {
        document.querySelectorAll('#detailBody .row-num').forEach((td, i) => {
            td.textContent = i + 1;
        });
        rowCount = document.querySelectorAll('#detailBody tr').length;
    }

    // ── Recalculate balance ────────────────────────────────────
    function recalculate() {
        let debet  = 0;
        let kredit = 0;
        document.querySelectorAll('#detailBody .debet-col').forEach(inp  => { debet  += parseInput(inp.value); });
        document.querySelectorAll('#detailBody .kredit-col').forEach(inp => { kredit += parseInput(inp.value); });

        const selisih  = debet - kredit;
        const balanced = Math.abs(selisih) < 0.01 && debet > 0;

        document.getElementById('totalDebet').textContent  = fmtNum(debet);
        document.getElementById('totalKredit').textContent = fmtNum(kredit);
        document.getElementById('selisih').textContent     = fmtNum(selisih);

        const selEl  = document.getElementById('selisih');
        const msgEl  = document.getElementById('balanceMsg');
        const btnSub = document.getElementById('btnSubmit');

        if (balanced) {
            selEl.className  = 'fw-bold balance-ok';
            msgEl.innerHTML  = '<span class="badge bg-success"><i class="fa fa-check me-1"></i>Balance</span>';
            btnSub.disabled  = false;
        } else {
            selEl.className  = 'fw-bold balance-err';
            msgEl.innerHTML  = '<span class="badge bg-danger"><i class="fa fa-times me-1"></i>Belum Balance</span>';
            btnSub.disabled  = true;
        }
    }

    // ── Sinkronisasi periode dengan tanggal ───────────────────
    function syncPeriode() {
        const tgl = document.querySelector('[name="tanggal"]').value;
        if (!tgl) return;
        const d     = new Date(tgl);
        const bulan = d.getMonth() + 1;
        const tahun = d.getFullYear();
        const match = periodeData.find(p => p.bulan === bulan && p.tahun === tahun);
        if (match) document.querySelector('[name="periode_id"]').value = match.id;
    }

    document.querySelector('[name="tanggal"]').addEventListener('change', syncPeriode);
    syncPeriode();

    // ── Init ──────────────────────────────────────────────────
    document.getElementById('btnAddRow').addEventListener('click', () => addRow());
    if (prefillRows.length > 0) {
        prefillRows.forEach(r => addRow(r));
    } else {
        addRow();
        addRow();
    }
})();
</script>
<?= $this->endSection() ?>
