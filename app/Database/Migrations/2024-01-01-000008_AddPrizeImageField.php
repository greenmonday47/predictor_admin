<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddPrizeImageField extends Migration
{
    public function up()
    {
        // Add prize_image field to stacks table
        $this->forge->addColumn('stacks', [
            'prize_image' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
                'after' => 'prize_description'
            ]
        ]);
    }

    public function down()
    {
        // Remove the added column
        $this->forge->dropColumn('stacks', ['prize_image']);
    }
} 