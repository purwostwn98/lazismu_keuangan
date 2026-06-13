<?php

/**
 * @var \CodeIgniter\View\View $this
 * @var string                 $pageTitle
 * @var array                  $periodeList
 * @var array|null             $periodeAktif
 * @var array                  $kategoriList
 * @var array                  $jenisZisGroups
 * @var array                  $jenisZisLabels
 * @var array                  $jenisDanaList
 * @var array                  $rekeningList
 * @var array                  $akunPenerimaan
 * @var array                  $donaturList
 */
?>
<?php $this->extend('layouts/main'); ?>
<?php $this->section('content'); ?>

<div class="container-fluid">

    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-0 fw-bold"><?= esc($pageTitle) ?></h4>
            <small class="text-muted">Catat penerimaan zakat, infak, sedekah, dan dana lainnya</small>
        </div>
        <a href="<?= base_url('penerimaan') ?>" class="btn btn-outline-secondary btn-sm">
            <i class="fa fa-arrow-left me-1"></i> Kembali ke Daftar
        </a>
    </div>

    <!-- Alerts -->
    <?php if (session('errors')): ?>
        <div class="alert alert-danger alert-dismissible fade show py-2 mb-3" role="alert">
            <i class="fa fa-triangle-exclamation me-1"></i>
            <ul class="mb-0 ps-3">
                <?php foreach (session('errors') as $err): ?>
                    <li><?= esc($err) ?></li>
                <?php endforeach; ?>
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    <?php if (session('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show py-2 mb-3" role="alert">
            <i class="fa fa-triangle-exclamation me-1"></i> <?= esc(session('error')) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="row justify-content-center">
        <div class="col-12 col-xl-9">
            <form action="<?= base_url('penerimaan/store') ?>" method="post" id="formPenerimaan">
                <?= csrf_field() ?>

                <!-- ─── Bagian: Info Transaksi ─── -->
                <div class="card border-0 shadow-sm mb-3">
                    <div class="card-header bg-transparent border-bottom py-2">
                        <span class="fw-semibold small text-uppercase text-muted letter-spacing-1">
                            <i class="fa fa-calendar me-1"></i> Informasi Transaksi
                        </span>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <!-- Tanggal -->
                            <div class="col-12 col-md-4">
                                <label class="form-label form-label-sm fw-semibold">
                                    Tanggal <span class="text-danger">*</span>
                                </label>
                                <input type="date" name="tanggal" id="tanggal"
                                    class="form-control form-control-sm"
                                    value="<?= old('tanggal', date('Y-m-d')) ?>"
                                    required onchange="autoDetectPeriode()">
                            </div>

                            <!-- Periode -->
                            <div class="col-12 col-md-4">
                                <label class="form-label form-label-sm fw-semibold">
                                    Periode <span class="text-danger">*</span>
                                </label>
                                <select name="periode_id" id="periodeId" class="form-select form-select-sm" required>
                                    <option value="">— Pilih Periode —</option>
                                    <?php foreach ($periodeList as $p): ?>
                                        <option value="<?= $p['id'] ?>"
                                            data-bulan="<?= $p['bulan'] ?>"
                                            data-tahun="<?= $p['tahun'] ?>"
                                            <?= old('periode_id', ($periodeAktif['id'] ?? '')) == $p['id'] ? 'selected' : '' ?>>
                                            <?= esc($p['nama']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <div class="form-text">Hanya periode yang masih aktif (belum ditutup).</div>
                            </div>

                            <!-- Nomor Jurnal (auto, display only) -->
                            <div class="col-12 col-md-4">
                                <label class="form-label form-label-sm fw-semibold text-muted">No. Jurnal</label>
                                <input type="text" class="form-control form-control-sm bg-light text-muted font-monospace"
                                    value="PNR / auto-generate" readonly tabindex="-1">
                                <div class="form-text">Dibuat otomatis saat disimpan.</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ─── Bagian: Donatur & Kategori ─── -->
                <div class="card border-0 shadow-sm mb-3">
                    <div class="card-header bg-transparent border-bottom py-2">
                        <span class="fw-semibold small text-uppercase text-muted">
                            <i class="fa fa-user me-1"></i> Donatur / Muzakki
                        </span>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <!-- Donatur -->
                            <div class="col-12 col-md-6">
                                <label class="form-label form-label-sm fw-semibold">
                                    Donatur <span class="text-muted fw-normal">(opsional)</span>
                                </label>
                                <div class="position-relative">
                                    <input type="text" id="donaturSearch"
                                        class="form-control form-control-sm"
                                        placeholder="Ketik nama atau kode donatur..."
                                        autocomplete="off">
                                    <input type="hidden" name="donatur_id" id="donaturId"
                                        value="<?= old('donatur_id') ?>">
                                    <div id="donaturDropdown" class="dropdown-menu w-100 shadow-sm p-0"
                                        style="max-height:200px;overflow-y:auto;font-size:.85rem;"></div>
                                </div>
                                <div class="form-text">Kosongkan jika donatur anonim / tidak tercatat.</div>
                            </div>

                            <!-- Kategori -->
                            <div class="col-12 col-md-6">
                                <label class="form-label form-label-sm fw-semibold">
                                    Kategori <span class="text-danger">*</span>
                                </label>
                                <select name="kategori_id" id="kategoriId" class="form-select form-select-sm" required>
                                    <option value="">— Pilih Kategori —</option>
                                    <?php foreach ($kategoriList as $parent): ?>
                                        <?php if (! empty($parent['children'])): ?>
                                            <optgroup label="<?= esc($parent['nama']) ?>">
                                                <?php foreach ($parent['children'] as $child): ?>
                                                    <option value="<?= $child['id'] ?>"
                                                        <?= old('kategori_id') == $child['id'] ? 'selected' : '' ?>>
                                                        <?= esc($child['nama']) ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </optgroup>
                                        <?php else: ?>
                                            <option value="<?= $parent['id'] ?>"
                                                <?= old('kategori_id') == $parent['id'] ? 'selected' : '' ?>>
                                                <?= esc($parent['nama']) ?>
                                            </option>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ─── Bagian: Jenis ZIS & Jumlah ─── -->
                <div class="card border-0 shadow-sm mb-3">
                    <div class="card-header bg-transparent border-bottom py-2">
                        <span class="fw-semibold small text-uppercase text-muted">
                            <i class="fa fa-star-and-crescent me-1"></i> Jenis & Jumlah
                        </span>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <!-- Jenis ZIS -->
                            <div class="col-12 col-md-5">
                                <label class="form-label form-label-sm fw-semibold">
                                    Jenis ZIS <span class="text-danger">*</span>
                                </label>
                                <select name="jenis_zis" id="jenisZis" class="form-select form-select-sm"
                                    required onchange="onJenisZisChange()">
                                    <option value="">— Pilih Jenis ZIS —</option>
                                    <?php foreach ($jenisZisGroups as $groupName => $items): ?>
                                        <optgroup label="<?= esc($groupName) ?>">
                                            <?php foreach ($items as $key): ?>
                                                <option value="<?= $key ?>"
                                                    <?= old('jenis_zis') === $key ? 'selected' : '' ?>>
                                                    <?= esc($jenisZisLabels[$key]) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </optgroup>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <!-- Jenis Dana -->
                            <div class="col-12 col-md-4">
                                <label class="form-label form-label-sm fw-semibold">
                                    Jenis Dana <span class="text-danger">*</span>
                                </label>
                                <select name="jenis_dana_id" id="jenisDanaId" class="form-select form-select-sm" required>
                                    <option value="">— Pilih Jenis Dana —</option>
                                    <?php foreach ($jenisDanaList as $jd): ?>
                                        <option value="<?= $jd['id'] ?>"
                                            <?= old('jenis_dana_id') == $jd['id'] ? 'selected' : '' ?>>
                                            <?= esc($jd['nama']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <div class="form-text">Dana yang menerima penerimaan ini.</div>
                            </div>

                            <!-- Jumlah -->
                            <div class="col-12 col-md-3">
                                <label class="form-label form-label-sm fw-semibold">
                                    Jumlah (Rp) <span class="text-danger">*</span>
                                </label>
                                <input type="number" name="jumlah" id="jumlah"
                                    class="form-control form-control-sm"
                                    value="<?= old('jumlah') ?>"
                                    min="1" step="any" required
                                    placeholder="0">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ─── Bagian: Akuntansi ─── -->
                <div class="card border-0 shadow-sm mb-3">
                    <div class="card-header bg-transparent border-bottom py-2">
                        <span class="fw-semibold small text-uppercase text-muted">
                            <i class="fa fa-book me-1"></i> Pencatatan Akuntansi
                        </span>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <!-- Rekening Tujuan (Debet) -->
                            <div class="col-12 col-md-6">
                                <label class="form-label form-label-sm fw-semibold">
                                    Rekening Tujuan <span class="text-danger">*</span>
                                </label>
                                <select name="rekening_id" id="rekeningId" class="form-select form-select-sm" required>
                                    <option value="">— Pilih Rekening —</option>
                                    <?php foreach ($rekeningList as $r): ?>
                                        <option value="<?= $r['id'] ?>"
                                            <?= old('rekening_id') == $r['id'] ? 'selected' : '' ?>>
                                            <?= esc($r['nama']) ?> (<?= esc($r['bank']) ?>)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <div class="form-text"><i class="fa fa-arrow-right fa-xs me-1"></i>Debet: kas/rekening bank yang menerima uang.</div>
                            </div>

                            <!-- Akun Penerimaan (Kredit) -->
                            <div class="col-12 col-md-6">
                                <label class="form-label form-label-sm fw-semibold">
                                    Akun Penerimaan (Kredit) <span class="text-danger">*</span>
                                </label>
                                <select name="akun_penerimaan_id" id="akunPenerimaanId" class="form-select form-select-sm ts-akun" required>
                                    <option value="">— Pilih Akun —</option>
                                    <?php foreach ($akunPenerimaan as $a): ?>
                                        <option value="<?= $a['id'] ?>"
                                            <?= old('akun_penerimaan_id') == $a['id'] ? 'selected' : '' ?>>
                                            <?= esc($a['nomor_akun']) ?> — <?= esc($a['nama_akun']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <div class="form-text"><i class="fa fa-arrow-left fa-xs me-1"></i>Kredit: akun pendapatan/penerimaan ZIS.</div>
                            </div>
                        </div>

                        <!-- Jurnal Preview -->
                        <div class="mt-3 p-3 bg-light rounded border" id="jurnalPreview" style="display:none;">
                            <div class="small text-muted fw-semibold mb-2">Preview Jurnal:</div>
                            <table class="table table-sm table-borderless mb-0 small font-monospace">
                                <tbody>
                                    <tr>
                                        <td class="text-muted ps-0" style="width:35%">Debet</td>
                                        <td id="previewDebet" class="fw-semibold">—</td>
                                        <td id="previewJumlahDebet" class="text-end">—</td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted ps-0">Kredit</td>
                                        <td id="previewKredit" class="ps-3">—</td>
                                        <td id="previewJumlahKredit" class="text-end">—</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- ─── Bagian: Keterangan ─── -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-transparent border-bottom py-2">
                        <span class="fw-semibold small text-uppercase text-muted">
                            <i class="fa fa-comment me-1"></i> Keterangan
                        </span>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label form-label-sm fw-semibold">
                                    Uraian <span class="text-danger">*</span>
                                </label>
                                <input type="text" name="uraian" id="uraian"
                                    class="form-control form-control-sm"
                                    value="<?= old('uraian') ?>"
                                    maxlength="255" required
                                    placeholder="cth: Penerimaan Zakat Maal Profesi - Ahmad">
                            </div>
                            <div class="col-12">
                                <label class="form-label form-label-sm fw-semibold">Keterangan <span class="text-muted fw-normal">(opsional)</span></label>
                                <textarea name="keterangan" class="form-control form-control-sm" rows="2"
                                    maxlength="500" placeholder="Catatan tambahan..."><?= old('keterangan') ?></textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Submit -->
                <div class="d-flex justify-content-end gap-2 mb-4">
                    <a href="<?= base_url('penerimaan') ?>" class="btn btn-outline-secondary">Batal</a>
                    <button type="submit" class="btn btn-primary px-4">
                        <i class="fa fa-save me-1"></i> Simpan Penerimaan
                    </button>
                </div>

            </form>
        </div>
    </div>

</div>

<?php $this->endSection(); ?>

<?php $this->section('scripts') ?>
<script>
    // ── Donatur data for autocomplete ──────────────────────────
    const donaturData = <?= json_encode(array_map(fn($d) => [
                            'id'    => $d['id'],
                            'label' => $d['nama'] . ($d['kode'] ? ' [' . $d['kode'] . ']' : ''),
                            'nama'  => $d['nama'],
                        ], $donaturList)) ?>;

    const rekData = <?= json_encode(array_map(fn($r) => [
                        'id'   => $r['id'],
                        'nama' => $r['nama'] . ' (' . $r['bank'] . ')',
                    ], $rekeningList)) ?>;

    const akunData = <?= json_encode(array_map(fn($a) => [
                            'id'   => $a['id'],
                            'nama' => $a['nomor_akun'] . ' — ' . $a['nama_akun'],
                        ], $akunPenerimaan)) ?>;

    // ── Donatur autocomplete ────────────────────────────────────
    const donaturSearchEl = document.getElementById('donaturSearch');
    const donaturIdEl = document.getElementById('donaturId');
    const donaturDropEl = document.getElementById('donaturDropdown');
    const kategoriIdEl = document.getElementById('kategoriId');

    // Pre-fill if old() has a value
    <?php if (old('donatur_id')): ?>
            (function() {
                const d = donaturData.find(x => x.id == <?= (int) old('donatur_id') ?>);
                if (d) {
                    donaturSearchEl.value = d.label;
                    donaturIdEl.value = d.id;
                }
            })();
    <?php endif; ?>

    donaturSearchEl.addEventListener('input', function() {
        const q = this.value.toLowerCase().trim();
        donaturIdEl.value = '';
        if (q.length < 1) {
            donaturDropEl.classList.remove('show');
            return;
        }

        const matches = donaturData.filter(d => d.label.toLowerCase().includes(q)).slice(0, 10);
        if (matches.length === 0) {
            donaturDropEl.classList.remove('show');
            return;
        }

        donaturDropEl.innerHTML = matches.map(d =>
            `<button type="button" class="dropdown-item py-1 px-3" data-id="${d.id}" data-label="${d.label}">
            ${d.label}
        </button>`
        ).join('');
        donaturDropEl.classList.add('show');
    });

    donaturDropEl.addEventListener('click', function(e) {
        const btn = e.target.closest('[data-id]');
        if (!btn) return;
        donaturSearchEl.value = btn.dataset.label;
        donaturIdEl.value = btn.dataset.id;
        donaturDropEl.classList.remove('show');
        updateUraian();
    });

    document.addEventListener('click', e => {
        if (!donaturSearchEl.contains(e.target) && !donaturDropEl.contains(e.target)) {
            donaturDropEl.classList.remove('show');
            // Clear if not matched
            if (donaturIdEl.value === '' && donaturSearchEl.value !== '') {
                donaturSearchEl.value = '';
            }
        }
    });

    // ── Auto-detect periode from tanggal ───────────────────────
    const periodeOptions = <?= json_encode(array_map(fn($p) => [
                                'id'    => $p['id'],
                                'bulan' => (int) $p['bulan'],
                                'tahun' => (int) $p['tahun'],
                            ], $periodeList)) ?>;

    function autoDetectPeriode() {
        const val = document.getElementById('tanggal').value;
        if (!val) return;
        const d = new Date(val);
        const m = d.getMonth() + 1;
        const y = d.getFullYear();
        const found = periodeOptions.find(p => p.bulan === m && p.tahun === y);
        if (found) {
            document.getElementById('periodeId').value = found.id;
        }
    }

    // ── Auto-fill uraian ────────────────────────────────────────
    const jenisZisLabels = <?= json_encode($jenisZisLabels) ?>;

    function updateUraian() {
        const jz = document.getElementById('jenisZis').value;
        const label = jenisZisLabels[jz] ?? '';
        const donNama = donaturIdEl.value ?
            (donaturData.find(d => d.id == donaturIdEl.value)?.nama ?? '') :
            '';

        const uraianEl = document.getElementById('uraian');
        if (uraianEl.dataset.manual) return; // user already typed manually

        let text = label ? 'Penerimaan ' + label : '';
        if (donNama) text += ' - ' + donNama;
        if (text) uraianEl.value = text;
    }

    document.getElementById('uraian').addEventListener('input', function() {
        this.dataset.manual = '1';
    });

    // ── Jenis ZIS change ────────────────────────────────────────
    function onJenisZisChange() {
        updateUraian();
        updateJurnalPreview();
    }

    // ── Jurnal preview ──────────────────────────────────────────
    function updateJurnalPreview() {
        const rId = document.getElementById('rekeningId').value;
        const aId = document.getElementById('akunPenerimaanId').value;
        const jml = parseFloat(document.getElementById('jumlah').value) || 0;

        const rNama = rId ? (rekData.find(r => r.id == rId)?.nama ?? '—') : '—';
        const aNama = aId ? (akunData.find(a => a.id == aId)?.nama ?? '—') : '—';

        const prev = document.getElementById('jurnalPreview');

        if (rId && aId && jml > 0) {
            const fmt = 'Rp ' + jml.toLocaleString('id-ID');
            document.getElementById('previewDebet').textContent = rNama;
            document.getElementById('previewKredit').textContent = aNama;
            document.getElementById('previewJumlahDebet').textContent = fmt;
            document.getElementById('previewJumlahKredit').textContent = fmt;
            prev.style.display = 'block';
        } else {
            prev.style.display = 'none';
        }
    }

    document.getElementById('rekeningId').addEventListener('change', updateJurnalPreview);
    document.getElementById('akunPenerimaanId').addEventListener('change', updateJurnalPreview);
    document.getElementById('jumlah').addEventListener('input', updateJurnalPreview);

    // Tom Select
    new TomSelect('#akunPenerimaanId', {
        maxOptions: 500,
        allowEmptyOption: true
    });

    // Initial state
    autoDetectPeriode();
    updateJurnalPreview();
</script>
<?php $this->endSection(); ?>