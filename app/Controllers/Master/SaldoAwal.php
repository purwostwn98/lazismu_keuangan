<?php

namespace App\Controllers\Master;

use App\Controllers\BaseController;

class SaldoAwal extends BaseController
{
    public function index(): string
    {
        $db    = \Config\Database::connect();
        $tahun = (int)($this->request->getGet('tahun') ?? date('Y'));

        $jenisDanaList = $db->table('jenis_dana')->orderBy('id', 'ASC')->get()->getResultArray();

        // Ambil data saldo_dana_awal untuk tahun ini
        $rows = $db->table('saldo_dana_awal')
            ->where('tahun', $tahun)
            ->get()->getResultArray();

        $savedMap = [];
        foreach ($rows as $r) {
            $savedMap[(int)$r['jenis_dana_id']] = (float)$r['saldo'];
        }

        // Tahun dari periode yang ada
        $tahunList = $db->table('periode')->select('tahun')->groupBy('tahun')->orderBy('tahun', 'DESC')->get()->getResultArray();
        $tahunList = array_column($tahunList, 'tahun');
        if (!in_array($tahun, $tahunList)) {
            array_unshift($tahunList, $tahun);
        }

        return view('master/saldo_awal/index', [
            'pageTitle'    => 'Saldo Dana Awal Tahun',
            'tahun'        => $tahun,
            'tahunList'    => $tahunList,
            'jenisDanaList'=> $jenisDanaList,
            'savedMap'     => $savedMap,
        ]);
    }

    public function store()
    {
        $tahun = (int)$this->request->getPost('tahun');
        if ($tahun < 2000 || $tahun > 2099) {
            return redirect()->back()->with('error', 'Tahun tidak valid.');
        }

        $db            = \Config\Database::connect();
        $jenisDanaList = $db->table('jenis_dana')->get()->getResultArray();
        $now           = date('Y-m-d H:i:s');
        $saldoInput    = (array)($this->request->getPost('saldo') ?? []);

        $db->transStart();

        foreach ($jenisDanaList as $jd) {
            $jdId  = (int)$jd['id'];
            $saldo = (float)str_replace(['.', ','], ['', '.'], $saldoInput[$jdId] ?? '0');

            $exists = $db->table('saldo_dana_awal')
                ->where('tahun', $tahun)
                ->where('jenis_dana_id', $jdId)
                ->get()->getRowArray();

            if ($exists) {
                $db->table('saldo_dana_awal')
                    ->where('tahun', $tahun)
                    ->where('jenis_dana_id', $jdId)
                    ->update(['saldo' => $saldo, 'updated_at' => $now]);
            } else {
                $db->table('saldo_dana_awal')->insert([
                    'tahun'         => $tahun,
                    'jenis_dana_id' => $jdId,
                    'saldo'         => $saldo,
                    'created_at'    => $now,
                    'updated_at'    => $now,
                ]);
            }
        }

        $db->transComplete();

        if (! $db->transStatus()) {
            return redirect()->back()->with('error', 'Gagal menyimpan saldo awal.');
        }

        return redirect()->to('master/saldo-awal?tahun=' . $tahun)
            ->with('success', "Saldo Dana Awal Tahun {$tahun} berhasil disimpan.");
    }
}
