<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateTopUpTransactionsTable extends Migration
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
            'msisdn' => [
                'type' => 'VARCHAR',
                'constraint' => 12,
                'null' => false,
                'comment' => 'Phone number in 256XXXXXXXX format',
            ],
            'amount' => [
                'type' => 'DECIMAL',
                'constraint' => '15,2',
                'null' => false,
            ],
            'transaction_id' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'null' => false,
                'comment' => 'Unique transaction ID for GM Pay',
            ],
            'gmpay_reference' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => true,
                'comment' => 'Reference from GM Pay response',
            ],
            'status' => [
                'type' => 'ENUM',
                'constraint' => ['pending', 'success', 'failed'],
                'null' => false,
                'default' => 'pending',
            ],
            'gmpay_status' => [
                'type' => 'ENUM',
                'constraint' => ['PENDING', 'SUCCESS', 'FAILED'],
                'null' => false,
                'default' => 'PENDING',
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => false,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => false,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey('user_id');
        $this->forge->addKey('transaction_id');
        $this->forge->addKey('status');
        $this->forge->addKey('created_at');
        $this->forge->addForeignKey('user_id', 'users', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('topup_transactions');
    }

    public function down()
    {
        $this->forge->dropTable('topup_transactions');
    }
} 