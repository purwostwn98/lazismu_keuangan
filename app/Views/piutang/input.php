<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<?php
$penerimaList  = $penerimaList  ?? [];
$periodeList   = $periodeList   ?? [];
$periodeAktif  = $periodeAktif  ?? null;
$jenisDanaList = $jenisDanaList ?? [];
$rekeningList  = $rekeningList  ?? [];
$jenisLabels   = $jenisLabels   ?? [];
$old           = fn(string $k, $d = '') => old($k, $d);
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-start mb-3">
        <div>
            <h4 class="mb-0 fw-bold">Input Piutang</h4>
            <small class="text-muted">Catat pinjaman / piutang baru. Jurnal dibuat otomatis.</small>
        </div>
        <a href="<?= base_url('piutang') ?>" class="btn btn-sm btn-outline-secondary">
            <i class="fa fa-arrow-left me-1"></i> Kembali
        </a>
    </div>

    <?php if (empty($penerimaList)): ?>
        <div class="alert alert-warning">
            <i class="fa fa-exclamation-triangle me-1"></i>
            Belum ada data <strong>Penerima Manfaat</strong>. Tambahkan dulu sebelum mencatat piutang.
            <a href="<?= base_url('master/penerima') ?>" class="alert-link ms-1">Kelola Penerima &rarr;</a>
        </div>
    <?php endif; ?>

    <?php if (session('errors')): ?>
        <div class="alert alert-danger alert-dismissible fade show py-2 small">
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
            <?= session('error') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <form method="post" action="<?= base_url('piutang/store') ?>">
    <?= csrf_field() ?>
    <div class="card border-0 shadow-sm">
        <div class="card-header py-2 fw-semibold" style="background:#1a3f6f;color:#fff;">
            <i class="fa fa-file-invoice-dollar me-1"></i> Data Piutang
        </div>
        <div class="card-body">
            <div class="row g-3">

                <div class="col-sm-4">
                    <label class="form-label small fw-semibold">Penerima / Peminjam <span class="text-danger">*</span></label>
                    <select name="penerima_id" class="form-select form-select-sm" required
                            <?= empty($penerimaList) ? 'disabled' : '' ?>>
                        <option value="">— Pilih Penerima —</option>
                        <?php foreach ($penerimaList as $pm): ?>
                            <option value="<?= $pm['id'] ?>"
                                <?= $old('penerima_id') == $pm['id'] ? 'selected' : '' ?>>
                                <?= esc($pm['nama']) ?> (<?= esc($pm['asnaf']) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-sm-4">
                    <label class="form-label small fw-semibold">Jenis Piutang <span class="text-danger">*</span></label>
                    <select name="jenis" class="form-select form-select-sm" required>
                        <option value="">— Pilih Jenis —</option>
                        <?php foreach ($jenisLabels as $k => $l): ?>
                            <option value="<?= $k ?>" <?= $old('jenis') === $k ? 'selected' : '' ?>>
                                <?= $l ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-sm-4">
                    <label class="form-label small fw-semibold">Sumber Dana <span class="text-danger">*</span></label>
                    <select name="jenis_dana_id" class="form-select form-select-sm" required>
                        <option value="">— Pilih Dana —</option>
                        <?php foreach ($jenisDanaList as $jd): ?>
                            <option value="<?= $jd['id'] ?>"
                                <?= $old('jenis_dana_id') == $jd['id'] ? 'selected' : '' ?>>
                                <?= esc($jd['nama']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-sm-4">
                    <label class="form-label small fw-semibold">Jumlah Pokok (Rp) <span class="text-danger">*</span></label>
                    <input type="text" name="jumlah_pokok" class="form-control form-control-sm text-end"
                           placeholder="0" value="<?= esc($old('jumlah_pokok')) ?>"
                           autocomplete="off" required>
                </div>

                <div class="col-sm-4">
                    <label class="form-label small fw-semibold">Rekening Sumber <span class="text-danger">*</span></label>
                    <select name="rekening_id" class="form-select form-select-sm" required>
                        <option value="">— Pilih Rekening —</option>
                        <?php foreach ($rekeningList as $r): ?>
                            <option value="<?= $r['id'] ?>"
                                <?= $old('rekening_id') == $r['id'] ? 'selected' : '' ?>>
                                <?= esc($r['nama']) ?> (<?= esc($r['nama_dana']) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <div class="form-text">Rekening yang digunakan untuk mencairkan piutang.</div>
                </div>

                <div class="col-sm-4">
                    <label class="form-label small fw-semibold">Periode Akuntansi <span class="text-danger">*</span></label>
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

                <div class="col-sm-4">
                    <label class="form-label small fw-semibold">Tanggal Pinjam <span class="text-danger">*</span></label>
                    <input type="date" name="tanggal_pinjam" class="form-control form-control-sm"
                           value="<?= $old('tanggal_pinjam', date('Y-m-d')) ?>" required>
                </div>

                <div class="col-sm-4">
                    <label class="form-label small fw-semibold">Tanggal Jatuh Tempo</label>
                    <input type="date" name="tanggal_jatuh_tempo" class="form-control form-control-sm"
                           value="<?= $old('tanggal_jatuh_tempo') ?>">
                    <div class="form-text">Kosongkan jika tidak ada batas waktu.</div>
                </div>

                <div class="col-sm-8">
                    <label class="form-label small fw-semibold">Uraian / Keperluan <span class="text-danger">*</span></label>
                    <input type="text" name="uraian" class="form-control form-control-sm"
                           placeholder="Keperluan pinjaman / piutang" maxlength="255"
                           value="<?= esc($old('uraian')) ?>" required>
                </div>

                <div class="col-12">
                    <label class="form-label small fw-semibold">Keterangan Tambahan</label>
                    <textarea name="keterangan" class="form-control form-control-sm" rows="2"
                              placeholder="Opsional"><?= esc($old('keterangan')) ?></textarea>
                </div>

            </div>
        </div>
    </div>

    <div class="alert alert-info small mt-3 py-2">
        <i class="fa fa-info-circle me-1"></i>
        Sistem akan membuat jurnal otomatis:
        <strong>Debit</strong> Piutang (sesuai jenis) &rarr;
        <strong>Kredit</strong> Rekening Sumber.
        Sisa piutang aktif akan masuk ke Laporan Posisi Keuangan sebagai Aset Lancar.
    </div>

    <div class="d-flex gap-2 mt-3">
        <button type="submit" class="btn btn-primary" <?= empty($penerimaList) ? 'disabled' : '' ?>>
            <i class="fa fa-save me-1"></i> Simpan Piutang
        </button>
        <a href="<?= base_url('piutang') ?>" class="btn btn-outline-secondary">Batal</a>
    </div>
    </form>
</div>

<?= $this->section('scripts') ?>
<script>
// Auto-select jenis_dana berdasarkan jenis piutang yang dipilih
const danaDefault = {
    'talangan_zakat'        : '1',   // ZAKAT
    'talangan_infaq'        : '3',   // INFAK_TT
    'qardul_hasan_amil'     : '4',   // AMIL
    'talangan_amil'         : '4',   // AMIL
};

document.querySelector('[name="jenis"]').addEventListener('change', function () {
    const defaultId = danaDefault[this.value];
    if (defaultId) {
        const sel = document.querySelector('[name="jenis_dana_id"]');
        sel.value = defaultId;
    }
});
</script>
<?= $this->endSection() ?>
<?= $this->endSection() ?>
