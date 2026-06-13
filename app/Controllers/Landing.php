<?php

namespace App\Controllers;

class Landing extends BaseController
{
    public function index(): string
    {
        $db = \Config\Database::connect();

        $totalPenerimaan = (float) $db->query(
            "SELECT COALESCE(SUM(total_debet),0) FROM jurnal WHERE jenis_transaksi='penerimaan'"
        )->getRow()?->{'COALESCE(SUM(total_debet),0)'} ?? 0;

        $totalPenyaluran = (float) $db->query(
            "SELECT COALESCE(SUM(total_debet),0) FROM jurnal WHERE jenis_transaksi IN ('penyaluran','biaya')"
        )->getRow()?->{'COALESCE(SUM(total_debet),0)'} ?? 0;

        $jumlahDonatur = (int) $db->query(
            "SELECT COUNT(*) AS n FROM donatur WHERE is_aktif=1"
        )->getRow()->n;

        $tahun = date('Y');
        $penTahunIni = (float) $db->query(
            "SELECT COALESCE(SUM(j.total_debet),0) AS total
             FROM jurnal j JOIN periode p ON p.id=j.periode_id
             WHERE j.jenis_transaksi='penerimaan' AND p.tahun=?",
            [$tahun]
        )->getRow()->total ?? 0;

        return view('landing/index', [
            'pageTitle'       => 'LAZISMU UMS — Zakat, Infak & Sedekah',
            'totalPenerimaan' => $totalPenerimaan,
            'totalPenyaluran' => $totalPenyaluran,
            'jumlahDonatur'   => $jumlahDonatur,
            'penTahunIni'     => $penTahunIni,
            'tahun'           => $tahun,
        ]);
    }
}
