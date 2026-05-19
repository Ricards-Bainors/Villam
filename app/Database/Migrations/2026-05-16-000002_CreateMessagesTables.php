<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateMessagesTables extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => ['type' => 'SERIAL'],
            'advertisement_id' => ['type' => 'INT', 'null' => true],
            'buyer_id' => ['type' => 'INT'],
            'seller_id' => ['type' => 'INT'],
            'created_at' => ['type' => 'TIMESTAMP', 'null' => true],
            'updated_at' => ['type' => 'TIMESTAMP', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('buyer_id', 'users', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('seller_id', 'users', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('conversations', true);

        $this->db->query(
            'CREATE UNIQUE INDEX IF NOT EXISTS conversations_ad_buyer_seller_unique
             ON conversations (advertisement_id, buyer_id, seller_id)'
        );

        $this->forge->addField([
            'id' => ['type' => 'SERIAL'],
            'conversation_id' => ['type' => 'INT'],
            'sender_id' => ['type' => 'INT'],
            'message' => ['type' => 'TEXT'],
            'created_at' => ['type' => 'TIMESTAMP', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('conversation_id', 'conversations', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('sender_id', 'users', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('conversation_messages', true);
    }

    public function down()
    {
        $this->forge->dropTable('conversation_messages', true);
        $this->forge->dropTable('conversations', true);
    }
}
