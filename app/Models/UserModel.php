<?php

namespace App\Models;

use CodeIgniter\Model;

class UserModel extends Model
{
    protected $table      = 'users';
    protected $primaryKey = 'id';
    protected $returnType = 'array';

    protected $allowedFields = [
        'nama', 'email', 'username', 'password',
        'role', 'is_muzaki', 'is_mustahik',
        'donatur_id', 'penerima_manfaat_id',
        'is_aktif', 'last_login',
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    protected $validationRules = [
        'nama'     => 'required|max_length[100]',
        'email'    => 'required|valid_email|max_length[100]',
        'username' => 'required|min_length[3]|max_length[50]|alpha_dash',
        'role'     => 'required|in_list[admin,bendahara,manajer,auditor]',
    ];

    public function getWithRelasi(): array
    {
        return $this->db->query("
            SELECT u.*,
                   d.nama  AS nama_donatur,
                   d.kode  AS kode_donatur,
                   pm.nama AS nama_penerima,
                   pm.kode AS kode_penerima
            FROM users u
            LEFT JOIN donatur d          ON d.id  = u.donatur_id
            LEFT JOIN penerima_manfaat pm ON pm.id = u.penerima_manfaat_id
            ORDER BY u.nama
        ")->getResultArray();
    }

    public function usernameExists(string $username, ?int $excludeId = null): bool
    {
        $builder = $this->where('username', $username);
        if ($excludeId !== null) {
            $builder->where('id !=', $excludeId);
        }
        return $builder->countAllResults() > 0;
    }

    public function emailExists(string $email, ?int $excludeId = null): bool
    {
        $builder = $this->where('email', $email);
        if ($excludeId !== null) {
            $builder->where('id !=', $excludeId);
        }
        return $builder->countAllResults() > 0;
    }
}
