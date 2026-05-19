<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class AdminSeeder extends Seeder
{
    public function run()
    {
        $data = [
            'username' => 'admin',
            'email' => 'admin@villam.local',
            'password' => password_hash('admin', PASSWORD_DEFAULT),
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        $existingAdmin = $this->db->table('users')
            ->where('username', 'admin')
            ->get()
            ->getRowArray();

        if ($existingAdmin) {
            $this->db->table('users')
                ->where('id', $existingAdmin['id'])
                ->update($data);

            return;
        }

        $data['created_at'] = date('Y-m-d H:i:s');

        $this->db->table('users')->insert($data);
    }
}
