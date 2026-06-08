<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddMissingFeatureTables extends Migration
{
    public function up()
    {
        $this->syncPostsTable();
        $this->syncCategoriesTable();
        $this->createAdvertisementsTable();
        $this->createDiscussionsTables();
        $this->createPostInteractionTables();
    }

    public function down()
    {
        $this->forge->dropTable('post_comments', true);
        $this->forge->dropTable('post_likes', true);
        $this->forge->dropTable('discussion_replies', true);
        $this->forge->dropTable('discussions', true);
        $this->forge->dropTable('advertisements', true);

        if ($this->db->tableExists('posts') && $this->db->fieldExists('images', 'posts')) {
            $this->forge->dropColumn('posts', 'images');
        }
    }

    private function syncPostsTable(): void
    {
        if (!$this->db->tableExists('posts')) {
            return;
        }

        if (!$this->db->fieldExists('images', 'posts')) {
            $this->forge->addColumn('posts', [
                'images' => [
                    'type' => 'TEXT',
                    'null' => true,
                ],
            ]);
        }

        if ($this->db->fieldExists('image', 'posts')) {
            $this->db->query('ALTER TABLE "posts" ALTER COLUMN "image" DROP NOT NULL');
        }
    }

    private function syncCategoriesTable(): void
    {
        if (
            $this->db->tableExists('categories')
            && !$this->db->fieldExists('updated_at', 'categories')
        ) {
            $this->forge->addColumn('categories', [
                'updated_at' => [
                    'type' => 'TIMESTAMP',
                    'null' => true,
                ],
            ]);
        }
    }

    private function createAdvertisementsTable(): void
    {
        if ($this->db->tableExists('advertisements')) {
            return;
        }

        $this->forge->addField([
            'id' => ['type' => 'SERIAL'],
            'user_id' => ['type' => 'INT', 'null' => true],
            'title' => ['type' => 'VARCHAR', 'constraint' => 255],
            'description' => ['type' => 'TEXT'],
            'price' => ['type' => 'NUMERIC', 'constraint' => '12,2'],
            'category_id' => ['type' => 'INT', 'null' => true],
            'location' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'images' => ['type' => 'TEXT', 'null' => true],
            'status' => ['type' => 'VARCHAR', 'constraint' => 30, 'default' => 'active'],
            'created_at' => ['type' => 'TIMESTAMP', 'null' => true],
            'updated_at' => ['type' => 'TIMESTAMP', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('user_id', 'users', 'id', 'CASCADE', 'SET NULL');
        $this->forge->addForeignKey('category_id', 'categories', 'id', 'CASCADE', 'SET NULL');
        $this->forge->createTable('advertisements', true);
    }

    private function createDiscussionsTables(): void
    {
        if (!$this->db->tableExists('discussions')) {
            $this->forge->addField([
                'id' => ['type' => 'SERIAL'],
                'user_id' => ['type' => 'INT', 'null' => true],
                'title' => ['type' => 'VARCHAR', 'constraint' => 255],
                'body' => ['type' => 'TEXT'],
                'category_id' => ['type' => 'INT', 'null' => true],
                'status' => ['type' => 'VARCHAR', 'constraint' => 30, 'default' => 'open'],
                'created_at' => ['type' => 'TIMESTAMP', 'null' => true],
                'updated_at' => ['type' => 'TIMESTAMP', 'null' => true],
            ]);
            $this->forge->addKey('id', true);
            $this->forge->addForeignKey('user_id', 'users', 'id', 'CASCADE', 'SET NULL');
            $this->forge->addForeignKey('category_id', 'categories', 'id', 'CASCADE', 'SET NULL');
            $this->forge->createTable('discussions', true);
        }

        if (!$this->db->tableExists('discussion_replies')) {
            $this->forge->addField([
                'id' => ['type' => 'SERIAL'],
                'discussion_id' => ['type' => 'INT'],
                'user_id' => ['type' => 'INT', 'null' => true],
                'reply' => ['type' => 'TEXT'],
                'created_at' => ['type' => 'TIMESTAMP', 'null' => true],
            ]);
            $this->forge->addKey('id', true);
            $this->forge->addForeignKey('discussion_id', 'discussions', 'id', 'CASCADE', 'CASCADE');
            $this->forge->addForeignKey('user_id', 'users', 'id', 'CASCADE', 'SET NULL');
            $this->forge->createTable('discussion_replies', true);
        }
    }

    private function createPostInteractionTables(): void
    {
        if (!$this->db->tableExists('post_likes')) {
            $this->forge->addField([
                'id' => ['type' => 'SERIAL'],
                'post_id' => ['type' => 'INT'],
                'user_id' => ['type' => 'INT', 'null' => true],
                'created_at' => ['type' => 'TIMESTAMP', 'null' => true],
            ]);
            $this->forge->addKey('id', true);
            $this->forge->addForeignKey('post_id', 'posts', 'id', 'CASCADE', 'CASCADE');
            $this->forge->addForeignKey('user_id', 'users', 'id', 'CASCADE', 'CASCADE');
            $this->forge->createTable('post_likes', true);

            $this->db->query(
                'CREATE UNIQUE INDEX IF NOT EXISTS post_likes_post_user_unique
                 ON post_likes (post_id, user_id)'
            );
        }

        if (!$this->db->tableExists('post_comments')) {
            $this->forge->addField([
                'id' => ['type' => 'SERIAL'],
                'post_id' => ['type' => 'INT'],
                'user_id' => ['type' => 'INT', 'null' => true],
                'comment' => ['type' => 'TEXT'],
                'created_at' => ['type' => 'TIMESTAMP', 'null' => true],
            ]);
            $this->forge->addKey('id', true);
            $this->forge->addForeignKey('post_id', 'posts', 'id', 'CASCADE', 'CASCADE');
            $this->forge->addForeignKey('user_id', 'users', 'id', 'CASCADE', 'SET NULL');
            $this->forge->createTable('post_comments', true);
        }
    }
}
