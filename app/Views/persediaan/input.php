<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<?php
$akunPersediaan = $akunPersediaan ?? [];
$jenisDanaList  = $jenisDanaList  ?? [];
$old = fn(string $k, $d = '') => old($k, $d);
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-start mb-3">
        <div>
            <h4 class="mb-0 fw-bold">Tambah Barang Persediaan</h4>
            <small class="text-muted">Daftarkan item baru ke master persediaan.</small>
        </div>
        <a href="<?= base_url('persediaan') ?>" class="btn btn-sm btn-outline-secondary">
            <i class="fa fa-arrow-left me-1"></i> Kembali
        </a>
    </div>

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

    <form method="post" action="<?= base_url('persediaan/store') ?>">
    <?= csrf_field() ?>
    <div class="card border-0 shadow-sm">
        <div class="card-header py-2 fw-semibold" style="background:#1a3f6f;color:#fff;">
            <i class="fa fa-box me-1"></i> Data Barang
        </div>
        <div class="card-body">
            <div class="row g-3">

                <div class="col-sm-6">
                    <label class="form-label small fw-semibold">Nama Barang <span class="text-danger">*</span></label>
                    <input type="text" name="nama_barang" class="form-control form-control-sm"
                           placeholder="Contoh: Beras Zakat" maxlength="150"
                           value="<?= esc($old('nama_barang')) ?>" required>
                    <div class="form-text">Kode barang dibuat otomatis.</div>
                </div>

                <div class="col-sm-3">
                    <label class="form-label small fw-semibold">Satuan <span class="text-danger">*</span></label>
                    <input type="text" name="satuan" class="form-control form-control-sm"
                           placeholder="Kg / Liter / Ekor / Pcs…" maxlength="20"
                           value="<?= esc($old('satuan', 'Kg')) ?>" required>
                </div>

                <div class="col-sm-3">
                    <label class="form-label small fw-semibold">Nilai per Satuan (Rp) <span class="text-danger">*</span></label>
                    <input type="text" name="nilai_per_satuan" class="form-control form-control-sm text-end"
                           placeholder="0" value="<?= esc($old('nilai_per_satuan', '0')) ?>" required>
                    <div class="form-text">Harga / taksiran per satuan.</div>
                </div>

                <div class="col-sm-6">
                    <label class="form-label small fw-semibold">Akun Persediaan (CoA) <span class="text-danger">*</span></label>
                    <select name="akun_id" class="form-select form-select-sm" required>
                        <option value="">— Pilih Akun —</option>
                        <?php foreach ($akunPersediaan as $a): ?>
                            <option value="<?= $a['id'] ?>" <?= $old('akun_id') == $a['id'] ? 'selected' : '' ?>>
                                <?= esc($a['nomor_akun']) ?> — <?= esc($a['nama_akun']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <div class="form-text">Akun 113xxxxx di Bagan Akun.</div>
                </div>

                <div class="col-sm-6">
                    <label class="form-label small fw-semibold">Sumber Dana Default</label>
                    <select name="jenis_dana_id" class="form-select form-select-sm">
                        <option value="">— Pilih Dana (Opsional) —</option>
                        <?php foreach ($jenisDanaList as $jd): ?>
                            <option value="<?= $jd['id'] ?>" <?= $old('jenis_dana_id') == $jd['id'] ? 'selected' : '' ?>>
                                <?= esc($jd['nama']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <div class="form-text">Digunakan saat membuat jurnal mutasi.</div>
                </div>

                <div class="col-12">
                    <label class="form-label small fw-semibold">Keterangan</label>
                    <textarea name="keterangan" class="form-control form-control-sm" rows="2"
                              placeholder="Deskripsi barang (opsional)"><?= esc($old('keterangan')) ?></textarea>
                </div>

            </div>
        </div>
    </div>

    <div class="d-flex gap-2 mt-3">
        <button type="submit" class="btn btn-primary">
            <i class="fa fa-save me-1"></i> Simpan Barang
        </button>
        <a href="<?= base_url('persediaan') ?>" class="btn btn-outline-secondary">Batal</a>
    </div>
    </form>
</div>
<?= $this->endSection() ?>
