<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class MappingAkunPenyaluranSeeder extends Seeder
{
    public function run()
    {
        $now = date('Y-m-d H:i:s');

        // Mapping id_kategori_penerima mylazismu → nomor_akun + id_dana aplikasi ini
        // Format: [id_eksternal, nomor_akun, id_dana, keterangan]
        $data = [
            // ── Zakat (id 1–8) → id_dana=1 (Dana Zakat) ────────────────
            [1,  '50100001', 1, 'Zakat - Fakir'],
            [2,  '50100002', 1, 'Zakat - Miskin'],
            [3,  '50100004', 1, 'Zakat - Muallaf'],
            [4,  '50100005', 1, 'Zakat - Riqab'],
            [5,  '50100006', 1, 'Zakat - Gharimin'],
            [6,  '50100007', 1, 'Zakat - Fisabilillah'],
            [7,  '50100008', 1, 'Zakat - Ibnu Sabil'],
            [8,  '50100003', 1, 'Zakat - Amil'],

            // ── Infaq Umum / Infak Tidak Terikat (id 9–16) → id_dana=3 ──
            [9,  '50202001', 3, 'Infak Tidak Terikat - Ekonomi'],
            [10, '50202002', 3, 'Infak Tidak Terikat - Sosial'],
            [11, '50202003', 3, 'Infak Tidak Terikat - Pendidikan'],
            [12, '50202004', 3, 'Infak Tidak Terikat - Kesehatan'],
            [13, '50202005', 3, 'Infak Tidak Terikat - Kemanusiaan'],
            [14, '50202006', 3, 'Infak Tidak Terikat - Keagamaan'],
            [15, '50202007', 3, 'Infak Tidak Terikat - PKBL/CSR'],
            [16, '50202008', 3, 'Infak Tidak Terikat - Ujrah Amil'],

            // ── Amil (id 17) → id_dana=4 (Dana Amil) ────────────────────
            [17, '50100003', 4, 'Dana Amil - Amil'],

            // ── Infaq Terikat (id 18–25) → id_dana=2 ─────────────────────
            [18, '50201001', 2, 'Infak Terikat - Ekonomi'],
            [19, '50201002', 2, 'Infak Terikat - Sosial'],
            [20, '50201003', 2, 'Infak Terikat - Pendidikan'],
            [21, '50201004', 2, 'Infak Terikat - Kesehatan'],
            [22, '50201005', 2, 'Infak Terikat - Kemanusiaan'],
            [23, '50201006', 2, 'Infak Terikat - Keagamaan'],
            [24, '50201007', 2, 'Infak Terikat - PKBL/CSR'],
            [25, '50201008', 2, 'Infak Terikat - Ujrah Amil'],
        ];

        $rows = [];
        foreach ($data as [$idEksternal, $nomorAkun, $idDana, $ket]) {
            $rows[] = [
                'nomor_akun'      => $nomorAkun,
                'id_eksternal'    => $idEksternal,
                'sumber_aplikasi' => 'mylazismu',
                'id_dana'         => $idDana,
                'keterangan'      => $ket,
                'created_at'      => $now,
                'updated_at'      => $now,
            ];
        }

        $this->db->table('mapping_akun_penyaluran')->insertBatch($rows);
    }
}
