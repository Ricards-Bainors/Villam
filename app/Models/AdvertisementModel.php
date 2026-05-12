<?php

namespace App\Models;

use CodeIgniter\Model;

class AdvertisementModel extends Model
{
    protected $table = 'advertisements';
    protected $primaryKey = 'id';

    protected $allowedFields = [
        'user_id',
        'title',
        'description',
        'price',
        'category_id',
        'location',
        'images',
        'status',
        'created_at',
        'updated_at'
    ];
}