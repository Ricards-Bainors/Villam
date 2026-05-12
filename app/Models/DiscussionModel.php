<?php

namespace App\Models;

use CodeIgniter\Model;

class DiscussionModel extends Model
{
    protected $table = 'discussions';
    protected $primaryKey = 'id';

    protected $allowedFields = [
        'user_id',
        'title',
        'body',
        'category_id',
        'status',
        'created_at',
        'updated_at'
    ];
}