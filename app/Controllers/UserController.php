<?php

namespace App\Controllers;

use App\Controllers\BaseController;

class UserController extends BaseController
{
    public function search()
    {
        $currentUserId = session()->get('user_id');

        if (!$currentUserId) {
            return redirect()->to('/login');
        }

        $query = trim((string) $this->request->getGet('q'));
        $users = [];

        if ($query !== '') {
            $db = \Config\Database::connect();

            $users = $db->table('users')
                ->select('id, username, email, profile_image')
                ->where('id !=', $currentUserId)
                ->groupStart()
                    ->like('username', $query, 'both', null, true)
                    ->orLike('email', $query, 'both', null, true)
                ->groupEnd()
                ->orderBy('username', 'ASC')
                ->limit(20)
                ->get()
                ->getResultArray();
        }

        return view('users/search', [
            'query' => $query,
            'users' => $users,
        ]);
    }

    public function searchJson()
    {
        $currentUserId = session()->get('user_id');

        if (!$currentUserId) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Lietotājs nav ielogojies.',
            ])->setStatusCode(401);
        }

        $query = trim((string) $this->request->getGet('q'));

        if ($query === '') {
            return $this->response->setJSON([
                'success' => true,
                'data' => [],
            ]);
        }

        $db = \Config\Database::connect();
        $users = $db->table('users')
            ->select('id, username, profile_image')
            ->where('id !=', $currentUserId)
            ->like('username', $query, 'both', null, true)
            ->orderBy('username', 'ASC')
            ->limit(20)
            ->get()
            ->getResultArray();

        return $this->response->setJSON([
            'success' => true,
            'data' => $users,
        ]);
    }
}
