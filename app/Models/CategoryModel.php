<?php
// filepath: /home/ricards/ci_crud_ajax/app/Models/CategoryModel.php

namespace App\Models;

use CodeIgniter\Model;

class CategoryModel extends Model
{
    protected $db;
    protected $table = 'categories'; // Table name

    public function __construct()
    {
        parent::__construct();
        $this->db = \Config\Database::connect();
    }

    // Fetch all categories using a raw SQL query
    public function getAllCategories(): array
    {
        $sql = "SELECT * FROM {$this->table} ORDER BY id ASC";
        return $this->db->query($sql)->getResultArray();
    }

    // Fetch a single category by ID using a raw SQL query
    public function getCategoryById(int $id): ?array
    {
        $sql = "SELECT * FROM {$this->table} WHERE id = ?";
        return $this->db->query($sql, [$id])->getRowArray();
    }

    // Insert a new category using the query builder
    public function createCategory(array $data): bool
    {
        $data['created_at'] = date('Y-m-d H:i:s'); // Add timestamp manually
        return $this->db->table($this->table)->insert($data);
    }

    // Update a category using the query builder
    public function updateCategory(int $id, array $data): bool
    {
        $data['updated_at'] = date('Y-m-d H:i:s'); // Add timestamp manually
        return $this->db->table($this->table)->where('id', $id)->update($data);
    }

    // Delete a category using the query builder
    public function deleteCategory(int $id): bool
    {
        return $this->db->table($this->table)->where('id', $id)->delete();
    }
}