<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddPaymentFieldsToPredictions extends Migration
{
    public function up()
    {
        $this->forge->addColumn('predictions', [
            'transaction_id' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'null' => true,
                'after' => 'predictions_json'
            ],
            'payment_status' => [
                'type' => 'ENUM',
                'constraint' => ['pending', 'paid', 'failed'],
                'default' => 'paid',
                'after' => 'transaction_id'
            ]
        ]);

        // Add index for transaction_id for faster lookups
        $this->forge->addKey('predictions', 'transaction_id', false, 'idx_transaction_id');
    }

    public function down()
    {
        $this->forge->dropColumn('predictions', ['transaction_id', 'payment_status']);
    }
} 