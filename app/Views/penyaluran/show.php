<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="row g-4">

    <!-- Informasi Header -->
    <div class="col-12 col-lg-7">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="fas fa-file-invoice me-2 text-primary"></i>Detail Penyaluran</span>
                <span class="badge badge-soft-orange" style="font-size:.82rem; letter-spacing:.3px;">
                    <?= esc($header['nomor_jurnal']) ?>
                </span>
            </div>
            <div class="card-body">
                <table class="table table-borderless mb-0" style="font-size:.85rem;">
                    <tr>
                        <th style="width:160px;color:#6b7c93;font-weight:600;">Tanggal</th>
                        <td><?= date('d F Y', strtotime($header['tanggal'])) ?></td>
                    </tr>
                    <tr>
                        <th style="color:#6b7c93;font-weight:600;">Periode</th>
                        <td><?= esc($header['nama_periode']) ?></td>
                    </tr>
                    <tr>
                        <th style="color:#6b7c93;font-weight:600;">Jenis Dana</th>
                        <td>
                            <span class="badge badge-soft-blue"><?= esc($header['nama_dana']) ?></span>
                        </td>
                    </tr>
                    <tr>
                        <th style="color:#6b7c93;font-weight:600;">Penerima</th>
                        <td><?= esc($header['nama_penerima'] ?? '—') ?></td>
                    </tr>
                    <tr>
                        <th style="color:#6b7c93;font-weight:600;">Uraian</th>
                        <td class="fw-semibold"><?= esc($header['uraian']) ?></td>
                    </tr>
                    <?php if ($header['keterangan']): ?>
                        <tr>
                            <th style="color:#6b7c93;font-weight:600;">Keterangan</th>
                            <td><?= esc($header['keterangan']) ?></td>
                        </tr>
                    <?php endif; ?>
                    <tr>
                        <th style="color:#6b7c93;font-weight:600;">Total</th>
                        <td class="fw-bold text-danger fs-6">
                            Rp <?= number_format($header['total_debet'], 0, ',', '.') ?>
                        </td>
                    </tr>
                </table>
            </div>
        </div>
    </div>

    <!-- Jurnal Entri -->
    <div class="col-12 col-lg-5">
        <div class="card">
            <div class="card-header">
                <i class="fas fa-book-open me-2 text-primary"></i>Entri Jurnal (Double Entry)
            </div>
            <div class="card-body p-0">
                <table class="table table-card mb-0" style="font-size:.83rem;">
                    <thead>
                        <tr>
                            <th>Akun</th>
                            <th class="text-end">Debet</th>
                            <th class="text-end">Kredit</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($details as $d): ?>
                            <tr>
                                <td>
                                    <div class="fw-semibold">
                                        <?= esc($d['nomor_akun']) ?> — <?= esc($d['nama_akun']) ?>
                                    </div>
                                    <?php if ($d['uraian']): ?>
                                        <div class="text-muted" style="font-size:.76rem;">
                                            <?= esc($d['uraian']) ?>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end text-success fw-semibold">
                                    <?= $d['debet'] > 0 ? 'Rp ' . number_format($d['debet'], 0, ',', '.') : '—' ?>
                                </td>
                                <td class="text-end text-danger fw-semibold">
                                    <?= $d['kredit'] > 0 ? 'Rp ' . number_format($d['kredit'], 0, ',', '.') : '—' ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot class="table-light">
                        <tr>
                            <th>Total</th>
                            <th class="text-end text-success">
                                Rp <?= number_format($header['total_debet'], 0, ',', '.') ?>
                            </th>
                            <th class="text-end text-danger">
                                Rp <?= number_format($header['total_kredit'], 0, ',', '.') ?>
                            </th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>

</div>

<div class="mt-4">
    <a href="<?= base_url('penyaluran/daftar') ?>" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left me-2"></i>Kembali ke Daftar
    </a>
</div>

<?= $this->endSection() ?>