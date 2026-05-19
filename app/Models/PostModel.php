<?php

namespace App\Models;

use CodeIgniter\Model;

class PostModel extends Model {
    protected $table = 'posts';

    protected $db;

    public function __construct()
    {
        parent::__construct();
        $this->db = \Config\Database::connect();
    }

    public function getAllPosts(): array
    {
        return $this->db->table($this->table)
            ->select('posts.*, categories.category_name as category, users.username as author_name, users.profile_image as author_image')
            ->join('categories', 'categories.id = posts.category_id', 'left')
            ->join('users', 'users.id = posts.user_id', 'left')
            ->orderBy('posts.created_at', 'DESC')
            ->get()
            ->getResultArray();
    }

    public function getPostsByUserId(int $userId): array
    {
        return $this->db->table($this->table)
            ->select('posts.*, categories.category_name as category')
            ->join('categories', 'categories.id = posts.category_id', 'left')
            ->where('posts.user_id', $userId)
            ->orderBy('posts.created_at', 'DESC')
            ->get()
            ->getResultArray();
    }

    public function createPost(array $data): bool
    {
        return $this->db->table($this->table)->insert($data);
    }

    public function updatePost(int $id, array $data): bool
    {
        return $this->db->table($this->table)->where('id', $id)->update($data);
    }

    public function deletePost(int $id): bool
    {
        return $this->db->table($this->table)->where('id', $id)->delete();
    }
}
