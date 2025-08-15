<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateStacksTable extends Migration
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
            'title' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => false,
            ],
            'prize_description' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'entry_fee' => [
                'type' => 'DECIMAL',
                'constraint' => '10,2',
                'null' => false,
                'default' => 0.00,
            ],
            'matches_json' => [
                'type' => 'JSON',
                'null' => false,
            ],
            'actual_scores_json' => [
                'type' => 'JSON',
                'null' => true,
            ],
            'deadline' => [
                'type' => 'DATETIME',
                'null' => false,
            ],
            'is_active' => [
                'type' => 'BOOLEAN',
                'null' => false,
                'default' => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->createTable('stacks');
    }

    public function down()
    {
        $this->forge->dropTable('stacks');
    }
} 