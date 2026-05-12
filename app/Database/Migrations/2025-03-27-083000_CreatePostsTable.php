<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreatePostsTable extends Migration {
    public function up() {
        $this->forge->addField([
            'id' => [
                'type' => 'SERIAL',
            ],
            'title' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
            ],
            'category_id' => [
                'type' => 'SERIAL',
            ],
            'body' => [
                'type' => 'TEXT',
            ],
            'image' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
            ],
            'created_at' => [
                'type' => 'TIMESTAMP',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'TIMESTAMP',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('id', true); // Primary key
        $this->forge->addForeignKey('category_id', 'categories', 'id', 'CASCADE', 'SET NULL'); // Foreign key
        $this->forge->createTable('posts');
    }

    public function down() {
        $this->forge->dropTable('posts');
    }
}