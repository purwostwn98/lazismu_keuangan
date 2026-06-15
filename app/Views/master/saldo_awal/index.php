<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<?php
$tahunList    = $tahunList    ?? [];
$jenisDanaList= $jenisDanaList?? [];
$savedMap     = $savedMap     ?? [];
$tahun        = $tahun        ?? date('Y');
?>
<div class="container-fluid">

    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h4 class="mb-0 fw-bold">Saldo Dana Awal Tahun</h4>
            <small class="text-muted">Saldo tiap jenis dana pada awal tahun (sebelum ada transaksi di tahun tersebut)</small>
        </div>
        <form method="get" class="d-flex gap-2 align-items-center">
            <label class="form-label mb-0 text-nowrap small">Tahun:</label>
            <select name="tahun" class="form-select form-select-sm" style="width:auto" onchange="this.form.submit()">
                <?php foreach ($tahunList as $y): ?>
                    <option value="<?= $y ?>" <?= $y == $tahun ? 'selected' : '' ?>><?= $y ?></option>
                <?php endforeach; ?>
                <?php if (!in_array($tahun - 1, $tahunList)): ?>
                    <option value="<?= $tahun - 1 ?>" <?= ($tahun - 1) == $tahun ? 'selected' : '' ?>><?= $tahun - 1 ?></option>
                <?php endif; ?>
            </select>
        </form>
    </div>

    <?php if (session()->getFlashdata('success')): ?>
        <div class="alert alert-success alert-dismissible fade show py-2">
            <i class="fa fa-check-circle me-2"></i><?= session()->getFlashdata('success') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show py-2">
            <i class="fa fa-exclamation-circle me-2"></i><?= session()->getFlashdata('error') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3">
            <h6 class="mb-0 fw-semibold">
                <i class="fa fa-wallet text-primary me-2"></i>
                Saldo Dana per Jenis Dana — Awal Tahun <?= $tahun ?>
            </h6>
        </div>

        <div class="card-body">
            <div class="alert alert-info py-2 mb-3" style="font-size:.85rem;">
                <i class="fa fa-info-circle me-2"></i>
                Masukkan saldo dana pada awal tahun <strong><?= $tahun ?></strong> (saldo per 31 Desember <?= $tahun - 1 ?>).
                Data ini digunakan sebagai titik awal pada <strong>Laporan Perubahan Dana</strong> dan
                <strong>Laporan Posisi Keuangan</strong>.<br>
                Biarkan <strong>0</strong> jika tahun <?= $tahun ?> adalah tahun pertama operasional
                atau jika saldo sudah tersimpan melalui proses <em>Tutup Periode</em> Desember <?= $tahun - 1 ?>.
            </div>

            <form method="post" action="<?= base_url('master/saldo-awal/store') ?>">
                <?= csrf_field() ?>
                <input type="hidden" name="tahun" value="<?= $tahun ?>">

                <table class="table table-bordered table-hover align-middle" style="font-size:.9rem;">
                    <thead class="table-light">
                        <tr>
                            <th style="width:50px;">#</th>
                            <th>Jenis Dana</th>
                            <th>Kode</th>
                            <th style="width:220px;">Saldo Awal Tahun <?= $tahun ?> (Rp)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($jenisDanaList as $i => $jd):
                            $savedSaldo = $savedMap[(int)$jd['id']] ?? 0;
                        ?>
                            <tr>
                                <td class="text-center text-muted"><?= $i + 1 ?></td>
                                <td class="fw-semibold"><?= esc($jd['nama']) ?></td>
                                <td><span class="badge bg-secondary"><?= esc($jd['kode']) ?></span></td>
                                <td>
                                    <input
                                        type="text"
                                        name="saldo[<?= $jd['id'] ?>]"
                                        class="form-control form-control-sm text-end rupiah-input"
                                        value="<?= number_format($savedSaldo, 0, ',', '.') ?>"
                                        placeholder="0"
                                    >
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

                <div class="d-flex justify-content-end gap-2 mt-3">
                    <a href="<?= base_url('master/saldo-awal?tahun=' . $tahun) ?>" class="btn btn-outline-secondary btn-sm">
                        <i class="fa fa-undo me-1"></i> Reset
                    </a>
                    <button type="submit" class="btn btn-primary btn-sm">
                        <i class="fa fa-save me-1"></i> Simpan Saldo Awal Tahun <?= $tahun ?>
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
document.querySelectorAll('.rupiah-input').forEach(function(el) {
    function formatRupiah(val) {
        val = val.replace(/[^0-9]/g, '');
        if (!val) return '0';
        return parseInt(val, 10).toLocaleString('id-ID');
    }
    el.addEventListener('focus', function() {
        this.value = this.value.replace(/\./g, '');
    });
    el.addEventListener('blur', function() {
        let raw = this.value.replace(/[^0-9]/g, '');
        this.value = raw ? parseInt(raw, 10).toLocaleString('id-ID') : '0';
    });
});
</script>
<?= $this->endSection() ?>
