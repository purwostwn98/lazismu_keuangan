<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class RekeningBankSeeder extends Seeder
{
    public function run()
    {
        $db = \Config\Database::connect();

        // Lookup akun by nomor_akun
        $akunRows = $db->table('akun')->select('id, nomor_akun')->get()->getResultArray();
        $akun     = array_column($akunRows, 'id', 'nomor_akun');

        // Lookup jenis_dana by kode
        $danaRows = $db->table('jenis_dana')->select('id, kode')->get()->getResultArray();
        $dana     = array_column($danaRows, 'id', 'kode');

        $now = date('Y-m-d H:i:s');

        // Format: [nama, nomor_rekening, bank, jenis_dana_kode, nomor_akun]
        $rekening = [
            // ─── KAS TUNAI ────────────────────────────────────────────────
            ['Kas Zakat',            null, 'Kas Tunai',          'ZAKAT',    '11101001'],
            ['Kas Infak Sedekah',    null, 'Kas Tunai',          'INFAK_TT', '11101002'],
            ['Kas Amil',             null, 'Kas Tunai',          'AMIL',     '11101003'],
            ['Kas Kemitraan',        null, 'Kas Tunai',          'INFAK_T',  '11101004'],
            ['Kas Tanggap Darurat',  null, 'Kas Tunai',          'INFAK_T',  '11101005'],
            ['Kas E Gizzi',          null, 'Kas Tunai',          'INFAK_T',  '11101006'],
            ['Kas CSR',              null, 'Kas Tunai',          'CSR',      '11101007'],
            ['Kas Kecil',            null, 'Kas Tunai',          'KAS_KECIL','11101008'],

            // ─── BANK ZAKAT ───────────────────────────────────────────────
            ['Jateng Syariah - Zakat',      null, 'Bank Jateng Syariah', 'ZAKAT', '11102011'],
            ['BMT Amanah Ummah - Zakat',    null, 'BMT Amanah Ummah',   'ZAKAT', '11102012'],
            ['BRI Syariah - Zakat',         null, 'BRI Syariah',         'ZAKAT', '11102013'],
            ['BMT Dana Mentari - Zakat',    null, 'BMT Dana Mentari',    'ZAKAT', '11102014'],

            // ─── BANK INFAK SEDEKAH (Tidak Terikat) ─────────────────────
            ['Jateng Syariah - Infak Sedekah',    null, 'Bank Jateng Syariah', 'INFAK_TT', '11102021'],
            ['BMT Amanah Ummah - Infak Sedekah',  null, 'BMT Amanah Ummah',   'INFAK_TT', '11102022'],
            ['BRI Syariah - Infaq',               null, 'BRI Syariah',         'INFAK_TT', '11102026'],
            ['BMT Dana Mentari - Infaq',          null, 'BMT Dana Mentari',    'INFAK_TT', '11102028'],
            ['Bank Muamalat - Infaq',             null, 'Bank Muamalat',       'INFAK_TT', '11102030'],

            // ─── BANK INFAK (Terikat) ─────────────────────────────────────
            ['BMT Amanah Ummah - Kemitraan',        null, 'BMT Amanah Ummah', 'INFAK_T', '11102023'],
            ['BMT Amanah Ummah - Tanggap Darurat',  null, 'BMT Amanah Ummah', 'INFAK_T', '11102024'],
            ['BMT Amanah Ummah - E Gizzi',          null, 'BMT Amanah Ummah', 'INFAK_T', '11102025'],
            ['BRI Syariah - Kemanusiaan',           null, 'BRI Syariah',       'INFAK_T', '11102027'],
            ['BMT Dana Mentari - Qurban',           null, 'BMT Dana Mentari',  'INFAK_T', '11102029'],

            // ─── BANK WAKAF ───────────────────────────────────────────────
            ['Bank Syariah Mandiri - Wakaf', null, 'Bank Syariah Mandiri', 'WAKAF', '11102031'],
            ['Bank Muamalat - Wakaf',        null, 'Bank Muamalat',        'WAKAF', '11102032'],

            // ─── BANK AMIL ────────────────────────────────────────────────
            ['BMT Dana Mentari Ummat - Amil', null, 'BMT Dana Mentari', 'AMIL', '11102041'],
            ['Sinarmas Syariah - Amil',       null, 'Sinarmas Syariah', 'AMIL', '11102042'],

            // ─── SIMKA (Simpanan Berjangka) ───────────────────────────────
            ['SIMKA Zakat',         null, 'Simpanan Berjangka', 'ZAKAT',    '11102051'],
            ['SIMKA Infak Sedekah', null, 'Simpanan Berjangka', 'INFAK_TT', '11102052'],
            ['SIMKA Amil',          null, 'Simpanan Berjangka', 'AMIL',     '11102053'],
            ['SIMKA CSR',           null, 'Simpanan Berjangka', 'CSR',      '11102054'],
        ];

        $rows = [];
        foreach ($rekening as [$nama, $noRek, $bank, $danaKode, $nomorAkun]) {
            if (! isset($akun[$nomorAkun])) {
                log_message('warning', "RekeningBankSeeder: akun {$nomorAkun} tidak ditemukan, dilewati.");
                continue;
            }
            if (! isset($dana[$danaKode])) {
                log_message('warning', "RekeningBankSeeder: jenis_dana {$danaKode} tidak ditemukan, dilewati.");
                continue;
            }

            $rows[] = [
                'nama'           => $nama,
                'nomor_rekening' => $noRek,
                'bank'           => $bank,
                'jenis_dana_id'  => $dana[$danaKode],
                'akun_id'        => $akun[$nomorAkun],
                'saldo_awal'     => 0,
                'is_aktif'       => 1,
                'created_at'     => $now,
                'updated_at'     => $now,
            ];
        }

        if (! empty($rows)) {
            $db->table('rekening_bank')->insertBatch($rows);
        }
    }
}
