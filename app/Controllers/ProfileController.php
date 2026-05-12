<?php

namespace App\Controllers;

use App\Controllers\BaseController;

class ProfileController extends BaseController
{
    public function index()
    {
        $db = \Config\Database::connect();

        $user = $db->table('users')
            ->where('id', session()->get('user_id'))
            ->get()
            ->getRowArray();

        return view('profile', [
            'user_id'  => $user['id'] ?? '',
            'username' => $user['username'] ?? '',
            'email'    => $user['email'] ?? '',
        ]);
    }
}