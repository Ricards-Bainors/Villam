<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateCategoriesTable extends Migration {
    public function up() {
        $this->forge->addField([
            'id' => [
                'type' => 'SERIAL', 
            ],
            'category_name' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
            ],
            'created_at' => [
                'type' => 'TIMESTAMP',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('categories');
    }

    public function down() {
        $this->forge->dropTable('categories'); 
    }
}
