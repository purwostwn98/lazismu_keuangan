<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<?php
$errors    = session()->getFlashdata('errors') ?? [];
$errorMsg  = session()->getFlashdata('error') ?? '';
$old       = fn(string $k, $def = '') => old($k, $def);
?>

<?php if ($errorMsg): ?>
    <div class="alert alert-danger d-flex gap-2 align-items-start py-2 mb-4">
        <i class="fas fa-circle-xmark mt-1"></i>
        <span><?= esc($errorMsg) ?></span>
    </div>
<?php endif; ?>

<?php if ($errors): ?>
    <div class="alert alert-danger py-2 mb-4">
        <i class="fas fa-circle-exclamation me-2"></i><strong>Periksa isian berikut:</strong>
        <ul class="mb-0 mt-1 ps-3" style="font-size:.82rem;">
            <?php foreach ($errors as $e): ?>
                <li><?= esc($e) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<form action="<?= base_url('penyaluran/store') ?>" method="POST" id="formPenyaluran" novalidate>
    <?= csrf_field() ?>

    <div class="row g-4">

        <!-- ── Kolom Kiri: Informasi Penyaluran ─────────────── -->
        <div class="col-12 col-lg-7">
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-hands-holding-circle me-2 text-primary"></i>
                    Informasi Penyaluran
                </div>
                <div class="card-body">

                    <!-- Tanggal & Periode -->
                    <div class="row g-3 mb-3">
                        <div class="col-sm-6">
                            <label class="form-label form-label-sm fw-semibold">
                                Tanggal <span class="text-danger">*</span>
                            </label>
                            <input type="date" name="tanggal" id="inputTanggal"
                                   class="form-control form-control-sm <?= isset($errors['tanggal']) ? 'is-invalid' : '' ?>"
                                   value="<?= $old('tanggal', date('Y-m-d')) ?>" required>
                        </div>
                        <div class="col-sm-6">
                            <label class="form-label form-label-sm fw-semibold">
                                Periode <span class="text-danger">*</span>
                            </label>
                            <select name="periode_id" id="inputPeriode"
                                    class="form-select form-select-sm <?= isset($errors['periode_id']) ? 'is-invalid' : '' ?>"
                                    required>
                                <option value="">— Pilih Periode —</option>
                                <?php foreach ($periodeList as $p): ?>
                                    <option value="<?= $p['id'] ?>"
                                        <?= ($old('periode_id', $periodeAktif['id'] ?? '') == $p['id']) ? 'selected' : '' ?>>
                                        <?= esc($p['nama']) ?>
                                        <?= $p['is_tutup'] ? '(Tutup)' : '' ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <!-- Jenis Dana -->
                    <div class="mb-3">
                        <label class="form-label form-label-sm fw-semibold">
                            Jenis Dana <span class="text-danger">*</span>
                        </label>
                        <select name="jenis_dana_id" id="inputJenisDana"
                                class="form-select form-select-sm <?= isset($errors['jenis_dana_id']) ? 'is-invalid' : '' ?>"
                                required>
                            <option value="">— Pilih Jenis Dana —</option>
                            <?php foreach ($jenisDana as $d): ?>
                                <option value="<?= $d['id'] ?>"
                                    <?= ($old('jenis_dana_id') == $d['id']) ? 'selected' : '' ?>>
                                    <?= esc($d['nama']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Program Penyaluran -->
                    <div class="mb-3">
                        <label class="form-label form-label-sm fw-semibold">
                            Program Penyaluran
                        </label>
                        <select name="program_ext_id" id="inputProgram" class="form-select form-select-sm">
                            <option value="">— Pilih Program (Opsional) —</option>
                            <?php
                            $currentKat = null;
                            foreach ($programs as $pr):
                                if ($currentKat !== $pr['nama_kategori']):
                                    if ($currentKat !== null) echo '</optgroup>';
                                    $currentKat = $pr['nama_kategori'];
                                    echo '<optgroup label="' . esc($currentKat) . '">';
                                endif;
                            ?>
                                <option value="<?= $pr['id_program'] ?>"
                                    data-nama="<?= esc($pr['nama_program']) ?>"
                                    <?= ($old('program_ext_id') == $pr['id_program']) ? 'selected' : '' ?>>
                                    <?= esc($pr['nama_program']) ?>
                                </option>
                            <?php endforeach;
                            if ($currentKat !== null) echo '</optgroup>'; ?>
                        </select>
                        <div class="form-text">Data program dari sistem Lazismu.</div>
                    </div>

                    <!-- Penerima Manfaat -->
                    <div class="mb-3">
                        <label class="form-label form-label-sm fw-semibold">Penerima Manfaat</label>
                        <select name="penerima_id" id="inputPenerima" class="form-select form-select-sm">
                            <option value="">— Pilih Penerima (Opsional) —</option>
                            <?php foreach ($penerima as $pm): ?>
                                <option value="<?= $pm['id'] ?>"
                                    data-asnaf="<?= esc($pm['asnaf'] ?? '') ?>"
                                    <?= ($old('penerima_id') == $pm['id']) ? 'selected' : '' ?>>
                                    [<?= esc($pm['kode']) ?>] <?= esc($pm['nama']) ?>
                                    <?= $pm['asnaf'] ? '— ' . ucfirst($pm['asnaf']) : '' ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Uraian -->
                    <div class="mb-3">
                        <label class="form-label form-label-sm fw-semibold">
                            Uraian <span class="text-danger">*</span>
                        </label>
                        <input type="text" name="uraian"
                               class="form-control form-control-sm <?= isset($errors['uraian']) ? 'is-invalid' : '' ?>"
                               placeholder="Keterangan singkat transaksi..."
                               value="<?= esc($old('uraian')) ?>" required>
                    </div>

                    <!-- Keterangan -->
                    <div class="mb-0">
                        <label class="form-label form-label-sm fw-semibold">Keterangan</label>
                        <textarea name="keterangan" class="form-control form-control-sm"
                                  rows="2" placeholder="Catatan tambahan (opsional)"><?= esc($old('keterangan')) ?></textarea>
                    </div>

                </div>
            </div>
        </div>

        <!-- ── Kolom Kanan: Detail Keuangan ─────────────────── -->
        <div class="col-12 col-lg-5">

            <!-- Jumlah & Akun -->
            <div class="card mb-3">
                <div class="card-header">
                    <i class="fas fa-calculator me-2 text-primary"></i>
                    Detail Keuangan
                </div>
                <div class="card-body">

                    <!-- Jumlah -->
                    <div class="mb-3">
                        <label class="form-label form-label-sm fw-semibold">
                            Jumlah <span class="text-danger">*</span>
                        </label>
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-light fw-semibold">Rp</span>
                            <input type="text" name="jumlah" id="inputJumlah"
                                   class="form-control form-control-sm text-end fw-bold <?= isset($errors['jumlah']) ? 'is-invalid' : '' ?>"
                                   placeholder="0" value="<?= esc($old('jumlah')) ?>"
                                   data-rupiah required autocomplete="off">
                        </div>
                    </div>

                    <!-- Akun Penyaluran (Debet) -->
                    <div class="mb-3">
                        <label class="form-label form-label-sm fw-semibold">
                            Akun Penyaluran (Debet) <span class="text-danger">*</span>
                        </label>
                        <select name="akun_debet_id" id="inputAkunDebet"
                                class="form-select form-select-sm <?= isset($errors['akun_debet_id']) ? 'is-invalid' : '' ?>"
                                required>
                            <option value="">— Pilih Akun —</option>
                            <?php foreach ($akunPenyaluran as $a): ?>
                                <option value="<?= $a['id'] ?>"
                                    data-nomor="<?= esc($a['nomor_akun']) ?>"
                                    data-nama="<?= esc($a['nama_akun']) ?>"
                                    <?= ($old('akun_debet_id') == $a['id']) ? 'selected' : '' ?>>
                                    <?= esc($a['nomor_akun']) ?> — <?= esc($a['nama_akun']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <div class="form-text">Akun beban/penyaluran yang di-debet.</div>
                    </div>

                    <!-- Rekening Bank (Kredit) -->
                    <div class="mb-0">
                        <label class="form-label form-label-sm fw-semibold">
                            Rekening Bank Sumber (Kredit) <span class="text-danger">*</span>
                        </label>
                        <select name="rekening_id" id="inputRekening"
                                class="form-select form-select-sm <?= isset($errors['rekening_id']) ? 'is-invalid' : '' ?>"
                                required>
                            <option value="">— Pilih Rekening —</option>
                            <?php
                            $currentDana = null;
                            foreach ($rekening as $rek):
                                if ($currentDana !== $rek['nama_dana']):
                                    if ($currentDana !== null) echo '</optgroup>';
                                    $currentDana = $rek['nama_dana'];
                                    echo '<optgroup label="' . esc($currentDana) . '">';
                                endif;
                            ?>
                                <option value="<?= $rek['id'] ?>"
                                    data-jenis="<?= $rek['jenis_dana_id'] ?>"
                                    data-akun="<?= $rek['akun_id'] ?>"
                                    <?= ($old('rekening_id') == $rek['id']) ? 'selected' : '' ?>>
                                    <?= esc($rek['nama']) ?>
                                    <?= $rek['nomor_rekening'] ? '— ' . $rek['nomor_rekening'] : '' ?>
                                </option>
                            <?php endforeach;
                            if ($currentDana !== null) echo '</optgroup>'; ?>
                        </select>
                        <div class="form-text">Kas/rekening yang di-kredit (sumber dana).</div>
                    </div>

                </div>
            </div>

            <!-- Preview Jurnal -->
            <div class="card" id="cardPreview" style="display:none;">
                <div class="card-header d-flex align-items-center gap-2">
                    <i class="fas fa-eye text-primary"></i>
                    Preview Jurnal
                </div>
                <div class="card-body p-0">
                    <table class="table table-card mb-0" style="font-size:.8rem;">
                        <thead>
                            <tr>
                                <th>Akun</th>
                                <th class="text-end">Debet</th>
                                <th class="text-end">Kredit</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td id="previewAkunDebet" class="text-muted fst-italic">—</td>
                                <td id="previewDebet" class="text-end text-success fw-semibold">—</td>
                                <td></td>
                            </tr>
                            <tr>
                                <td id="previewAkunKredit" class="text-muted fst-italic ps-4">—</td>
                                <td></td>
                                <td id="previewKredit" class="text-end text-danger fw-semibold">—</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>

    <!-- Action Buttons -->
    <div class="d-flex gap-2 mt-4 pt-3" style="border-top:1px solid #eef0f3;">
        <button type="submit" class="btn btn-primary px-4">
            <i class="fas fa-save me-2"></i>Simpan Penyaluran
        </button>
        <a href="<?= base_url('penyaluran') ?>" class="btn btn-outline-secondary px-4">
            <i class="fas fa-arrow-left me-2"></i>Batal
        </a>
    </div>

</form>

<?= $this->endSection() ?>

<?= $this->section('styles') ?>
<style>
.form-label-sm { font-size: .78rem; margin-bottom: .3rem; }
</style>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
const fmtRp = v => 'Rp ' + Number(v || 0).toLocaleString('id-ID');

function updatePreview() {
    const akunDebetSel = document.getElementById('inputAkunDebet');
    const rekeningsel  = document.getElementById('inputRekening');
    const jumlahInput  = document.getElementById('inputJumlah');

    const akunDebet  = akunDebetSel.options[akunDebetSel.selectedIndex];
    const rekening   = rekeningsel.options[rekeningsel.selectedIndex];
    const jumlahRaw  = jumlahInput.value.replace(/\./g, '').replace(',', '.').trim();
    const jumlah     = parseFloat(jumlahRaw) || 0;

    const hasDebet   = akunDebet && akunDebet.value;
    const hasKredit  = rekening && rekening.value;
    const hasJumlah  = jumlah > 0;

    const card = document.getElementById('cardPreview');

    if (hasDebet || hasKredit || hasJumlah) {
        card.style.display = '';
        document.getElementById('previewAkunDebet').textContent =
            hasDebet ? (akunDebet.dataset.nomor + ' — ' + akunDebet.dataset.nama) : '—';
        document.getElementById('previewDebet').textContent =
            hasJumlah ? fmtRp(jumlah) : '—';
        document.getElementById('previewAkunKredit').textContent =
            hasKredit ? rekening.text.trim() : '—';
        document.getElementById('previewKredit').textContent =
            hasJumlah ? fmtRp(jumlah) : '—';
    } else {
        card.style.display = 'none';
    }
}

// Auto-sinkron rekening saat pilih jenis dana
document.getElementById('inputJenisDana').addEventListener('change', function () {
    const jenisDanaId = this.value;
    const sel = document.getElementById('inputRekening');

    // Highlight rekening sesuai dana yang dipilih
    Array.from(sel.options).forEach(opt => {
        opt.style.fontWeight = (opt.dataset.jenis == jenisDanaId) ? 'bold' : '';
    });
});

// Auto-isi uraian dari program
document.getElementById('inputProgram').addEventListener('change', function () {
    const opt = this.options[this.selectedIndex];
    if (opt.value) {
        const uraianInput = document.querySelector('input[name="uraian"]');
        if (!uraianInput.value) {
            uraianInput.value = 'Penyaluran ' + opt.dataset.nama;
        }
    }
});

// Update preview on change
['inputAkunDebet', 'inputRekening'].forEach(id => {
    document.getElementById(id).addEventListener('change', updatePreview);
});
document.getElementById('inputJumlah').addEventListener('input', updatePreview);

// Format rupiah on jumlah input
document.getElementById('inputJumlah').addEventListener('blur', function () {
    const raw = this.value.replace(/\./g, '').replace(',', '.').trim();
    const num = parseFloat(raw);
    if (!isNaN(num) && num > 0) {
        this.value = num.toLocaleString('id-ID');
    }
    updatePreview();
});

// Submit: strip format rupiah before submit
document.getElementById('formPenyaluran').addEventListener('submit', function () {
    const inp = document.getElementById('inputJumlah');
    inp.value = inp.value.replace(/\./g, '').replace(',', '.').trim();
});
</script>
<?= $this->endSection() ?>
