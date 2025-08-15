<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddStackStatusFields extends Migration
{
    public function up()
    {
        // Add new fields to stacks table
        $this->forge->addColumn('stacks', [
            'status' => [
                'type' => 'ENUM',
                'constraint' => ['active', 'reset', 'won'],
                'null' => false,
                'default' => 'active',
                'after' => 'is_active'
            ],
            'reset_count' => [
                'type' => 'INT',
                'constraint' => 11,
                'null' => false,
                'default' => 0,
                'after' => 'status'
            ],
            'won_at' => [
                'type' => 'DATETIME',
                'null' => true,
                'after' => 'reset_count'
            ],
            'previous_matches_json' => [
                'type' => 'JSON',
                'null' => true,
                'after' => 'won_at'
            ]
        ]);
    }

    public function down()
    {
        // Remove the added columns
        $this->forge->dropColumn('stacks', ['status', 'reset_count', 'won_at', 'previous_matches_json']);
    }
} 