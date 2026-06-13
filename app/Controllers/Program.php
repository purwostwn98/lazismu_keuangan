<?php

namespace App\Controllers;

use App\Models\Ext\AdKategoriProgramModel;
use App\Models\Ext\AdProgramModel;

class Program extends BaseController
{
    private AdKategoriProgramModel $kategoriModel;
    private AdProgramModel $programModel;

    public function __construct()
    {
        $this->kategoriModel = new AdKategoriProgramModel();
        $this->programModel  = new AdProgramModel();
    }

    public function index(): string
    {
        $search    = $this->request->getGet('q');
        $kategori  = $this->request->getGet('kategori');

        $db = \Config\Database::connect('lazismu_ext');

        $builder = $db->table('ad_program p')
            ->select('p.id_program, p.nama_program, p.deskripsi_program, p.jenis_formulir, p.status_program, k.id_kategori_program, k.nama_kategori')
            ->join('ad_kategori_program k', 'k.id_kategori_program = p.id_kategori_program', 'left');

        if ($search) {
            $builder->groupStart()
                ->like('p.nama_program', $search)
                ->orLike('p.deskripsi_program', $search)
                ->groupEnd();
        }

        if ($kategori) {
            $builder->where('p.id_kategori_program', $kategori);
        }

        $programs   = $builder->orderBy('k.nama_kategori, p.nama_program', 'ASC')->get()->getResultArray();
        $kategoriList = $this->kategoriModel->orderBy('nama_kategori', 'ASC')->findAll();

        // Kelompokkan program per kategori
        $grouped = [];
        foreach ($programs as $p) {
            $grouped[$p['nama_kategori'] ?? 'Tanpa Kategori'][] = $p;
        }

        $data = [
            'pageTitle'    => 'Program Penyaluran',
            'breadcrumb'   => ['Penyaluran' => null, 'Program Penyaluran' => null],
            'programs'     => $programs,
            'grouped'      => $grouped,
            'kategoriList' => $kategoriList,
            'search'       => $search,
            'filterKategori' => $kategori,
            'totalAktif'   => count(array_filter($programs, fn($p) => $p['status_program'] == 1)),
            'totalNonAktif' => count(array_filter($programs, fn($p) => $p['status_program'] != 1)),
        ];

        return view('penyaluran/program', $data);
    }
}
