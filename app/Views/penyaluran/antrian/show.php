<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<?php
$record      = $record      ?? [];
$periodeList = $periodeList ?? [];
$periodeAktif= $periodeAktif?? null;
$rekening    = $rekening    ?? [];
$penerima    = $penerima    ?? [];

$isPending      = ($record['status'] ?? '') === 'pending';
$isVerified     = ($record['status'] ?? '') === 'verified';
$isRejected     = ($record['status'] ?? '') === 'rejected';
$akunPenyaluran = $akunPenyaluran ?? [];

$errors   = session()->getFlashdata('errors') ?? [];
$errorMsg = session()->getFlashdata('error')  ?? '';
?>

<?php if ($errorMsg): ?>
    <div class="alert alert-danger alert-dismissible fade show py-2" role="alert">
        <i class="fas fa-triangle-exclamation me-1"></i> <?= esc($errorMsg) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>
<?php if ($errors): ?>
    <div class="alert alert-danger py-2">
        <i class="fas fa-circle-exclamation me-2"></i><strong>Periksa isian:</strong>
        <ul class="mb-0 mt-1 ps-3 small">
            <?php foreach ($errors as $e): ?>
                <li><?= esc($e) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<div class="row g-4">

    <!-- ── Kolom Kiri: Data dari Sistem Eksternal ─────────────── -->
    <div class="col-12 col-lg-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center gap-2">
                    <i class="fas fa-arrow-right-to-bracket text-primary"></i>
                    <span class="fw-semibold small">Data dari Sistem Eksternal</span>
                </div>
                <?php
                [$bgBadge, $txtBadge] = match($record['status']) {
                    'pending'  => ['bg-warning text-dark', 'Pending'],
                    'verified' => ['bg-success text-white', 'Terverifikasi'],
                    'rejected' => ['bg-danger text-white', 'Ditolak'],
                    default    => ['bg-secondary text-white', $record['status']],
                };
                ?>
                <span class="badge <?= $bgBadge ?>"><?= $txtBadge ?></span>
            </div>
            <div class="card-body">
                <table class="table table-borderless table-sm mb-0" style="font-size:.84rem;">
                    <?php
                    $field = function (string $label, string $val, bool $mono = false) {
                        echo '<tr>';
                        echo '<th style="width:150px;color:#6b7c93;font-weight:600;white-space:nowrap;">' . $label . '</th>';
                        echo '<td' . ($mono ? ' class="font-monospace"' : '') . '>' . $val . '</td>';
                        echo '</tr>';
                    };
                    ?>
                    <?php $field('Diterima', $record['created_at'] ? date('d/m/Y H:i', strtotime($record['created_at'])) : '—') ?>
                    <?php $field('Sumber', $record['sumber'] ? esc($record['sumber']) : '<span class="text-muted fst-italic">—</span>') ?>
                    <?php $field('Ref. Eksternal', $record['ref_eksternal'] ? esc($record['ref_eksternal']) : '<span class="text-muted fst-italic">—</span>', true) ?>
                    <tr><td colspan="2"><hr class="my-2"></td></tr>
                    <?php $field('Tanggal', date('d F Y', strtotime($record['tanggal']))) ?>
                    <?php $field('Jenis Dana', $record['nama_dana']
                        ? '<span class="badge badge-soft-blue">' . esc($record['nama_dana']) . '</span>'
                        : '<span class="text-muted">—</span>') ?>
                    <?php $field('Program', $record['program_nama']
                        ? esc($record['program_nama']) . ($record['program_ext_id'] ? ' <small class="text-muted">(#' . $record['program_ext_id'] . ')</small>' : '')
                        : '<span class="text-muted fst-italic">—</span>') ?>
                    <?php $field('Nama Penerima', $record['nama_penerima']
                        ? esc($record['nama_penerima'])
                        : '<span class="text-muted fst-italic">—</span>') ?>
                    <tr><td colspan="2"><hr class="my-2"></td></tr>
                    <?php $field('Uraian', '<strong>' . esc($record['uraian']) . '</strong>') ?>
                    <?php if ($record['keterangan']): ?>
                        <?php $field('Keterangan', esc($record['keterangan'])) ?>
                    <?php endif; ?>
                    <tr><td colspan="2"><hr class="my-2"></td></tr>
                    <tr>
                        <th style="color:#6b7c93;font-weight:600;">Jumlah</th>
                        <td class="fw-bold text-danger fs-6">
                            Rp <?= number_format($record['jumlah'], 0, ',', '.') ?>
                        </td>
                    </tr>
                </table>
            </div>
        </div>
    </div>

    <!-- ── Kolom Kanan: Verifikasi / Status ───────────────────── -->
    <div class="col-12 col-lg-6">

        <?php if ($isPending): ?>
        <!-- FORM VERIFIKASI -->
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header d-flex align-items-center gap-2">
                <i class="fas fa-shield-check text-success"></i>
                <span class="fw-semibold small">Verifikasi Akuntansi</span>
            </div>
            <div class="card-body">
                <form action="<?= base_url('penyaluran/antrian/verifikasi/' . $record['id']) ?>"
                      method="POST" id="formVerifikasi">
                    <?= csrf_field() ?>

                    <!-- Periode -->
                    <div class="mb-3">
                        <label class="form-label form-label-sm fw-semibold">
                            Periode <span class="text-danger">*</span>
                        </label>
                        <select name="periode_id" class="form-select form-select-sm <?= isset($errors['periode_id']) ? 'is-invalid' : '' ?>" required>
                            <option value="">— Pilih Periode —</option>
                            <?php foreach ($periodeList as $p): ?>
                                <option value="<?= $p['id'] ?>"
                                    <?= (old('periode_id', $periodeAktif['id'] ?? '') == $p['id']) ? 'selected' : '' ?>>
                                    <?= esc($p['nama']) ?>
                                    <?= $p['is_tutup'] ? '(Tutup)' : '' ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <?php if ($periodeAktif): ?>
                            <div class="form-text">Otomatis: <strong><?= esc($periodeAktif['nama']) ?></strong>
                                <?= $periodeAktif['is_tutup'] ? '<span class="text-danger">(ditutup)</span>' : '' ?>
                            </div>
                        <?php else: ?>
                            <div class="form-text text-warning">
                                <i class="fas fa-triangle-exclamation me-1"></i>
                                Periode untuk tanggal <strong><?= date('d/m/Y', strtotime($record['tanggal'])) ?></strong> tidak ditemukan.
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Akun Penyaluran (Debet) -->
                    <div class="mb-3">
                        <label class="form-label form-label-sm fw-semibold">
                            Akun Penyaluran (Debet) <span class="text-danger">*</span>
                        </label>
                        <select name="akun_debet_id" id="selAkunDebet"
                                class="form-select form-select-sm <?= isset($errors['akun_debet_id']) ? 'is-invalid' : '' ?>"
                                required>
                            <option value="">— Pilih Akun —</option>
                            <?php foreach ($akunPenyaluran as $a): ?>
                                <option value="<?= $a['id'] ?>"
                                    data-nomor="<?= esc($a['nomor_akun']) ?>"
                                    data-nama="<?= esc($a['nama_akun']) ?>"
                                    <?= old('akun_debet_id', $record['akun_debet_id'] ?? '') == $a['id'] ? 'selected' : '' ?>>
                                    <?= esc($a['nomor_akun']) ?> — <?= esc($a['nama_akun']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <?php if (!empty($record['akun_debet_id']) && !empty($record['nomor_akun_penyaluran'])): ?>
                            <div class="form-text text-success">
                                <i class="fas fa-link me-1"></i>Otomatis dari mapping:
                                <strong><?= esc($record['nomor_akun_penyaluran']) ?></strong>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Rekening Bank Sumber (Kredit) -->
                    <div class="mb-3">
                        <label class="form-label form-label-sm fw-semibold">
                            Rekening Bank Sumber (Kredit) <span class="text-danger">*</span>
                        </label>
                        <select name="rekening_id" id="selRekening"
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
                                    data-akun="<?= $rek['akun_id'] ?>"
                                    data-nama="<?= esc($rek['nama']) ?>"
                                    <?= old('rekening_id') == $rek['id'] ? 'selected' : '' ?>>
                                    <?= esc($rek['nama']) ?>
                                    <?= $rek['nomor_rekening'] ? '— ' . $rek['nomor_rekening'] : '' ?>
                                </option>
                            <?php endforeach;
                            if ($currentDana !== null) echo '</optgroup>'; ?>
                        </select>
                    </div>

                    <!-- Link Penerima Manfaat (opsional) -->
                    <div class="mb-3">
                        <label class="form-label form-label-sm fw-semibold">
                            Link Penerima Manfaat
                            <span class="text-muted fw-normal">(opsional)</span>
                        </label>
                        <select name="penerima_id" class="form-select form-select-sm">
                            <option value="">— Tidak dikaitkan —</option>
                            <?php foreach ($penerima as $pm): ?>
                                <option value="<?= $pm['id'] ?>"
                                    <?= old('penerima_id', $record['penerima_id'] ?? '') == $pm['id'] ? 'selected' : '' ?>>
                                    [<?= esc($pm['kode']) ?>] <?= esc($pm['nama']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <?php if (!empty($record['penerima_id'])): ?>
                            <div class="form-text text-success">
                                <i class="fas fa-user-check me-1"></i>Otomatis dari NIK/Nomor Lembaga:
                                <strong><?= esc($record['nik_nomor_lembaga'] ?? '') ?></strong>
                            </div>
                        <?php else: ?>
                            <div class="form-text">Hubungkan ke data master penerima manfaat jika ada.</div>
                        <?php endif; ?>
                    </div>

                    <!-- Catatan Admin -->
                    <div class="mb-3">
                        <label class="form-label form-label-sm fw-semibold">Catatan</label>
                        <input type="text" name="catatan" class="form-control form-control-sm"
                               placeholder="Catatan verifikasi (opsional)"
                               value="<?= esc(old('catatan')) ?>">
                    </div>

                    <!-- Preview Jurnal -->
                    <div id="cardPreview" class="p-3 rounded mb-3" style="background:#f8f9fc;display:none;">
                        <div class="small fw-semibold text-muted mb-2">
                            <i class="fas fa-eye me-1"></i> Preview Jurnal
                        </div>
                        <table class="table table-sm table-borderless mb-0" style="font-size:.78rem;">
                            <tr>
                                <td id="previewDebet" class="text-muted fst-italic">—</td>
                                <td class="text-end text-success fw-semibold text-nowrap">
                                    Rp <?= number_format($record['jumlah'], 0, ',', '.') ?>
                                </td>
                                <td></td>
                            </tr>
                            <tr>
                                <td id="previewKredit" class="text-muted fst-italic ps-3">—</td>
                                <td></td>
                                <td class="text-end text-danger fw-semibold text-nowrap">
                                    Rp <?= number_format($record['jumlah'], 0, ',', '.') ?>
                                </td>
                            </tr>
                        </table>
                    </div>

                    <button type="submit" class="btn btn-success w-100" id="btnVerifikasi">
                        <i class="fas fa-check-circle me-2"></i>Verifikasi & Buat Jurnal
                    </button>
                </form>
            </div>
        </div>

        <!-- Tombol Tolak -->
        <div class="card border-0 shadow-sm border-danger" style="border-left:3px solid #dc3545 !important;">
            <div class="card-body py-2 px-3">
                <div class="d-flex align-items-center justify-content-between">
                    <span class="small text-muted">Tolak data ini jika tidak valid atau tidak perlu diproses.</span>
                    <button type="button" class="btn btn-outline-danger btn-sm ms-2"
                            data-bs-toggle="modal" data-bs-target="#modalTolak">
                        <i class="fas fa-times me-1"></i>Tolak
                    </button>
                </div>
            </div>
        </div>

        <?php elseif ($isVerified): ?>
        <!-- STATUS TERVERIFIKASI -->
        <div class="card border-0 shadow-sm border-success" style="border-left:4px solid #198754 !important;">
            <div class="card-header d-flex align-items-center gap-2 text-success" style="background:#f0fdf4;">
                <i class="fas fa-circle-check"></i>
                <span class="fw-semibold small">Data Telah Terverifikasi</span>
            </div>
            <div class="card-body">
                <?php if ($record['nomor_jurnal']): ?>
                    <p class="mb-2 small">Jurnal berhasil dibuat:</p>
                    <div class="d-flex align-items-center gap-2 mb-3">
                        <span class="badge badge-soft-orange fw-normal fs-6 letter-spacing-1">
                            <?= esc($record['nomor_jurnal']) ?>
                        </span>
                        <a href="<?= base_url('penyaluran/' . $record['jurnal_id']) ?>"
                           class="btn btn-sm btn-outline-primary">
                            <i class="fas fa-eye me-1"></i>Lihat Jurnal
                        </a>
                    </div>
                <?php endif; ?>
                <?php if ($record['nama_penerima_master']): ?>
                    <p class="mb-1 small text-muted">
                        <i class="fas fa-user me-1"></i>Penerima manfaat:
                        <strong><?= esc($record['nama_penerima_master']) ?></strong>
                    </p>
                <?php endif; ?>
                <?php if ($record['catatan']): ?>
                    <p class="mb-0 small text-muted">
                        <i class="fas fa-comment me-1"></i>Catatan: <?= esc($record['catatan']) ?>
                    </p>
                <?php endif; ?>
            </div>
        </div>

        <?php elseif ($isRejected): ?>
        <!-- STATUS DITOLAK -->
        <div class="card border-0 shadow-sm border-danger" style="border-left:4px solid #dc3545 !important;">
            <div class="card-header d-flex align-items-center gap-2 text-danger" style="background:#fff5f5;">
                <i class="fas fa-circle-xmark"></i>
                <span class="fw-semibold small">Data Ditolak</span>
            </div>
            <div class="card-body">
                <p class="mb-1 small text-muted">
                    Alasan penolakan:
                </p>
                <p class="mb-0 fst-italic">
                    "<?= esc($record['catatan'] ?? 'Tidak ada keterangan.') ?>"
                </p>
            </div>
        </div>
        <?php endif; ?>

    </div>
</div>

<!-- Tombol Kembali -->
<div class="mt-4">
    <a href="<?= base_url('penyaluran/antrian') ?>" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left me-2"></i>Kembali ke Daftar
    </a>
</div>

<?php if ($isPending): ?>
<!-- Modal Tolak -->
<div class="modal fade" id="modalTolak" tabindex="-1">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header py-2">
                <h6 class="modal-title text-danger">
                    <i class="fas fa-times-circle me-1"></i>Tolak Antrian #<?= $record['id'] ?>
                </h6>
                <button type="button" class="btn-close btn-sm" data-bs-dismiss="modal"></button>
            </div>
            <form action="<?= base_url('penyaluran/antrian/tolak/' . $record['id']) ?>" method="POST">
                <?= csrf_field() ?>
                <div class="modal-body">
                    <label class="form-label small fw-semibold">Alasan penolakan</label>
                    <input type="text" name="catatan" class="form-control form-control-sm"
                           placeholder="Misal: data tidak sesuai, sudah diproses..."
                           required>
                </div>
                <div class="modal-footer py-2">
                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-sm btn-danger">
                        <i class="fas fa-times me-1"></i>Tolak
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?= $this->section('scripts') ?>
<script>
(function () {
    const selAkun    = document.getElementById('selAkunDebet');
    const selRek     = document.getElementById('selRekening');
    const preview    = document.getElementById('cardPreview');
    const preDebet   = document.getElementById('previewDebet');
    const preKredit  = document.getElementById('previewKredit');

    new TomSelect('#selAkunDebet', { maxOptions: 500, allowEmptyOption: true });
    new TomSelect('#selRekening',  { maxOptions: 200, allowEmptyOption: true });

    function syncPreview() {
        const akunOpt = selAkun.options[selAkun.selectedIndex];
        const rekOpt  = selRek.options[selRek.selectedIndex];

        const akunLabel = (akunOpt && akunOpt.value)
            ? (akunOpt.dataset.nomor + ' — ' + akunOpt.dataset.nama) : null;
        const rekLabel  = (rekOpt && rekOpt.value) ? rekOpt.dataset.nama : null;

        if (akunLabel || rekLabel) {
            preview.style.display = '';
            preDebet.textContent  = akunLabel  || '(belum dipilih)';
            preKredit.textContent = rekLabel ? '    ' + rekLabel : '(belum dipilih)';
        } else {
            preview.style.display = 'none';
        }
    }

    selAkun.addEventListener('change', syncPreview);
    selRek.addEventListener('change', syncPreview);
    syncPreview();
})();
</script>
<?= $this->endSection() ?>
<?php endif; ?>

<?= $this->section('styles') ?>
<style>
.form-label-sm { font-size: .78rem; margin-bottom: .3rem; }
</style>
<?= $this->endSection() ?>

<?= $this->endSection() ?>
