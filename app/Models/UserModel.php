<?php

namespace App\Models;

use CodeIgniter\Database\BaseBuilder;

class UserModel
{
    protected $db;
    protected $table = 'users';

    public function __construct()
    {
        $this->db = \Config\Database::connect();
    }

    public function createUser(array $data): bool
    {
        $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        return $this->db->table($this->table)->insert($data);
    }

    public function findUserByUsername(string $username): ?array
    {
        return $this->db->table($this->table)
            ->where('username', $username)
            ->get()
            ->getRowArray();
    }
}