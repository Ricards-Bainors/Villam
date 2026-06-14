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

    public function usernameExists(string $username): bool
    {
        return $this->db->table($this->table)
            ->where('LOWER(username) = ' . $this->db->escape(strtolower($username)), null, false)
            ->countAllResults() > 0;
    }

    public function findUserById(int $id): ?array
    {
        return $this->db->table($this->table)
            ->where('id', $id)
            ->get()
            ->getRowArray();
    }

    public function getAllUsers(): array
    {
        return $this->db->table($this->table)
            ->select('id, username, email, profile_image, created_at, updated_at')
            ->orderBy('id', 'ASC')
            ->get()
            ->getResultArray();
    }

    public function updateUser(int $id, array $data): bool
    {
        return $this->db->table($this->table)
            ->where('id', $id)
            ->update($data);
    }

    public function deleteUser(int $id): bool
    {
        return $this->db->table($this->table)
            ->where('id', $id)
            ->delete();
    }
}
