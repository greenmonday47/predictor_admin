<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use App\Models\WalletModel;
use App\Models\WalletTransactionModel;
use App\Models\UserModel;

class SeedWalletData extends BaseCommand
{
    protected $group       = 'Database';
    protected $name        = 'seed:wallet';
    protected $description = 'Seed wallet data for testing';

    public function run(array $params)
    {
        CLI::write('Seeding wallet data...', 'green');

        $walletModel = new WalletModel();
        $walletTransactionModel = new WalletTransactionModel();
        $userModel = new UserModel();

        // Get all users
        $users = $userModel->findAll();

        if (empty($users)) {
            CLI::error('No users found. Please create users first.');
            return;
        }

        foreach ($users as $user) {
            CLI::write("Processing user: {$user['full_name']}", 'yellow');

            // Create wallet for user
            $wallet = $walletModel->getOrCreateWallet($user['id']);
            CLI::write("  - Wallet created/retrieved for user {$user['id']}", 'blue');

            // Add some test transactions
            $testTransactions = [
                [
                    'amount' => 50000,
                    'description' => 'Initial wallet credit',
                    'reference_type' => 'bonus'
                ],
                [
                    'amount' => 25000,
                    'description' => 'Mobile money top-up',
                    'reference_type' => 'topup'
                ],
                [
                    'amount' => 10000,
                    'description' => 'Participation in Premier League Stack',
                    'reference_type' => 'stack_participation'
                ],
                [
                    'amount' => 15000,
                    'description' => 'Card payment top-up',
                    'reference_type' => 'topup'
                ],
            ];

            foreach ($testTransactions as $transaction) {
                if ($transaction['reference_type'] === 'stack_participation') {
                    // Debit transaction
                    $walletModel->debit(
                        $user['id'],
                        $transaction['amount'],
                        $transaction['description'],
                        'TEST_' . date('YmdHis') . rand(1000, 9999),
                        $transaction['reference_type']
                    );
                } else {
                    // Credit transaction
                    $walletModel->credit(
                        $user['id'],
                        $transaction['amount'],
                        $transaction['description'],
                        'TEST_' . date('YmdHis') . rand(1000, 9999),
                        $transaction['reference_type']
                    );
                }
            }

            CLI::write("  - Added test transactions for user {$user['id']}", 'blue');
        }

        CLI::write('Wallet data seeding completed!', 'green');
    }
} 