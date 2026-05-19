<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddUserIdToPosts extends Migration
{
    public function up()
    {
        $this->forge->addColumn('posts', [
            'user_id' => [
                'type' => 'INT',
                'null' => true,
                'after' => 'id',
            ],
        ]);

        $this->forge->addForeignKey('user_id', 'users', 'id', 'CASCADE', 'SET NULL');
    }

    public function down()
    {
        $this->forge->dropColumn('posts', 'user_id');
    }
}
