<?php

namespace App\Models;

use CodeIgniter\Model;

class KategoriDonaturModel extends Model
{
    protected $table         = 'kategori_donatur';
    protected $primaryKey    = 'id';
    protected $allowedFields = ['kode', 'nama', 'parent_id'];
    protected $useTimestamps = true;

    /**
     * Returns categories grouped by parent:
     * [ ['id'=>1,'nama'=>'...','parent_id'=>null, 'children'=>[...]], ... ]
     */
    public function getGrouped(): array
    {
        $all     = $this->orderBy('parent_id', 'ASC')->orderBy('nama', 'ASC')->findAll();
        $parents = [];
        $children = [];

        foreach ($all as $row) {
            if ($row['parent_id'] === null) {
                $parents[$row['id']] = $row;
            } else {
                $children[$row['parent_id']][] = $row;
            }
        }

        foreach ($parents as $id => &$p) {
            $p['children'] = $children[$id] ?? [];
        }

        return array_values($parents);
    }
}