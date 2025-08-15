<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateWinnersTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'user_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => false,
            ],
            'stack_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => false,
            ],
            'win_type' => [
                'type' => 'ENUM',
                'constraint' => ['full-correct', 'top-score'],
                'null' => false,
                'default' => 'full-correct',
            ],
            'awarded_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('user_id', 'users', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('stack_id', 'stacks', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('winners');
    }

    public function down()
    {
        $this->forge->dropTable('winners');
    }
} 