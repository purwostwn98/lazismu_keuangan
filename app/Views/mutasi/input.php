<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="container-fluid">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-0 fw-bold"><?= esc($pageTitle) ?></h4>
            <small class="text-muted">Pindah saldo antar rekening (debet tujuan, kredit asal)</small>
        </div>
        <a href="<?= base_url('mutasi') ?>" class="btn btn-sm btn-outline-secondary">
            <i class="fa fa-arrow-left me-1"></i> Kembali
        </a>
    </div>

    <?php if (session('errors')): ?>
        <div class="alert alert-danger alert-dismissible fade show py-2">
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
            <i class="fa fa-triangle-exclamation me-1"></i> <?= esc(session('error')) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="card border-0 shadow-sm" style="max-width:640px;">
        <div class="card-body">
            <form action="<?= base_url('mutasi/store') ?>" method="post">
                <?= csrf_field() ?>

                <!-- Tanggal & Periode -->
                <div class="row g-3 mb-3">
                    <div class="col-6">
                        <label class="form-label small fw-semibold">Tanggal <span class="text-danger">*</span></label>
                        <input type="date" name="tanggal" class="form-control form-control-sm"
                               value="<?= old('tanggal', date('Y-m-d')) ?>" required>
                    </div>
                    <div class="col-6">
                        <label class="form-label small fw-semibold">Periode <span class="text-danger">*</span></label>
                        <select name="periode_id" class="form-select form-select-sm" required>
                            <option value="">— Pilih Periode —</option>
                            <?php foreach ($periodeList as $p): ?>
                                <option value="<?= $p['id'] ?>"
                                    <?= old('periode_id', $periodeAktif['id'] ?? '') == $p['id'] ? 'selected' : '' ?>>
                                    <?= esc($p['nama']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <!-- Rekening Asal -->
                <div class="mb-3">
                    <label class="form-label small fw-semibold">Rekening Asal (Kredit) <span class="text-danger">*</span></label>
                    <select name="rekening_asal_id" id="rek_asal" class="form-select form-select-sm" required>
                        <option value="">— Pilih Rekening Asal —</option>
                        <?php foreach ($rekeningList as $r): ?>
                            <option value="<?= $r['id'] ?>"
                                    data-dana="<?= esc($r['nama_dana'] ?? '') ?>"
                                    <?= old('rekening_asal_id') == $r['id'] ? 'selected' : '' ?>>
                                <?= esc($r['nama']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <div class="form-text" id="dana_asal_info"></div>
                </div>

                <!-- Rekening Tujuan -->
                <div class="mb-3">
                    <label class="form-label small fw-semibold">Rekening Tujuan (Debet) <span class="text-danger">*</span></label>
                    <select name="rekening_tujuan_id" id="rek_tujuan" class="form-select form-select-sm" required>
                        <option value="">— Pilih Rekening Tujuan —</option>
                        <?php foreach ($rekeningList as $r): ?>
                            <option value="<?= $r['id'] ?>"
                                    <?= old('rekening_tujuan_id') == $r['id'] ? 'selected' : '' ?>>
                                <?= esc($r['nama']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Jumlah -->
                <div class="mb-3">
                    <label class="form-label small fw-semibold">Jumlah (Rp) <span class="text-danger">*</span></label>
                    <input type="number" name="jumlah" class="form-control form-control-sm"
                           placeholder="0" min="1" step="any"
                           value="<?= old('jumlah') ?>" required>
                </div>

                <!-- Uraian -->
                <div class="mb-3">
                    <label class="form-label small fw-semibold">Uraian <span class="text-danger">*</span></label>
                    <input type="text" name="uraian" class="form-control form-control-sm"
                           placeholder="misal: Transfer ke rekening operasional"
                           value="<?= old('uraian') ?>" required>
                </div>

                <!-- Keterangan (opsional) -->
                <div class="mb-4">
                    <label class="form-label small fw-semibold">Keterangan <span class="text-muted">(opsional)</span></label>
                    <textarea name="keterangan" class="form-control form-control-sm" rows="2"
                              placeholder="Catatan tambahan..."><?= old('keterangan') ?></textarea>
                </div>

                <!-- Info jurnal -->
                <div class="alert alert-info py-2 small mb-4">
                    <i class="fa fa-circle-info me-1"></i>
                    Transaksi ini akan membuat jurnal <code>TRF/YYYYMM/NNNN</code>:<br>
                    &nbsp;&nbsp;Debet &nbsp;→ Rekening Tujuan<br>
                    &nbsp;&nbsp;Kredit → Rekening Asal
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary btn-sm px-4">
                        <i class="fa fa-save me-1"></i> Simpan Mutasi
                    </button>
                    <a href="<?= base_url('mutasi') ?>" class="btn btn-outline-secondary btn-sm">Batal</a>
                </div>
            </form>
        </div>
    </div>

</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
document.getElementById('rek_asal').addEventListener('change', function () {
    const opt  = this.options[this.selectedIndex];
    const dana = opt.dataset.dana ?? '';
    const info = document.getElementById('dana_asal_info');
    info.textContent = dana ? 'Dana: ' + dana : '';
});
</script>
<?= $this->endSection() ?>