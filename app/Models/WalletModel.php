<?php

namespace App\Models;

use CodeIgniter\Model;

class WalletModel extends Model
{
    protected $table = 'wallets';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;
    protected $allowedFields = ['user_id', 'balance'];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    protected $deletedField = false;

    // Validation
    protected $validationRules = [
        'user_id' => 'required|integer|is_natural_no_zero',
        'balance' => 'required|numeric|greater_than_equal_to[0]',
    ];

    protected $validationMessages = [
        'user_id' => [
            'required' => 'User ID is required',
            'integer' => 'User ID must be an integer',
            'is_natural_no_zero' => 'User ID must be a positive integer',
        ],
        'balance' => [
            'required' => 'Balance is required',
            'numeric' => 'Balance must be a number',
            'greater_than_equal_to' => 'Balance cannot be negative',
        ],
    ];

    protected $skipValidation = false;
    protected $cleanValidationRules = true;

    /**
     * Get or create wallet for user
     */
    public function getOrCreateWallet($userId)
    {
        $wallet = $this->where('user_id', $userId)->first();
        
        if (!$wallet) {
            $walletId = $this->insert([
                'user_id' => $userId,
                'balance' => 0.00
            ]);
            $wallet = $this->find($walletId);
        }
        
        return $wallet;
    }

    /**
     * Get wallet balance for user
     */
    public function getBalance($userId)
    {
        $wallet = $this->getOrCreateWallet($userId);
        return (float) $wallet['balance'];
    }

    /**
     * Credit wallet (add money)
     */
    public function credit($userId, $amount, $description, $reference = null, $referenceType = 'topup')
    {
        $db = \Config\Database::connect();
        $db->transStart();

        try {
            // Get current wallet
            $wallet = $this->getOrCreateWallet($userId);
            $balanceBefore = (float) $wallet['balance'];
            $balanceAfter = $balanceBefore + $amount;

            // Update wallet balance
            $this->where('user_id', $userId)
                 ->set(['balance' => $balanceAfter])
                 ->update();

            // Create transaction record using raw SQL
            $sql = "INSERT INTO wallet_transactions (user_id, type, amount, balance_before, balance_after, description, reference_type, status, created_at) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())";
            
            $params = [
                $userId,
                'credit',
                $amount,
                $balanceBefore,
                $balanceAfter,
                $description,
                $referenceType,
                'completed'
            ];
            
            $db->query($sql, $params);

            $db->transComplete();
            return $db->transStatus();
        } catch (\Exception $e) {
            $db->transRollback();
            throw $e;
        }
    }

    /**
     * Debit wallet (subtract money)
     */
    public function debit($userId, $amount, $description, $reference = null, $referenceType = 'stack_participation')
    {
        $db = \Config\Database::connect();
        $db->transStart();

        try {
            // Get current wallet
            $wallet = $this->getOrCreateWallet($userId);
            $balanceBefore = (float) $wallet['balance'];
            
            // Check if user has sufficient balance
            if ($balanceBefore < $amount) {
                throw new \Exception('Insufficient wallet balance');
            }
            
            $balanceAfter = $balanceBefore - $amount;

            // Update wallet balance
            $this->where('user_id', $userId)
                 ->set(['balance' => $balanceAfter])
                 ->update();

            // Create transaction record using raw SQL
            $sql = "INSERT INTO wallet_transactions (user_id, type, amount, balance_before, balance_after, description, reference_type, status, created_at) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())";
            
            $params = [
                $userId,
                'debit',
                $amount,
                $balanceBefore,
                $balanceAfter,
                $description,
                $referenceType,
                'completed'
            ];
            
            // Debug: Log the SQL and params
            log_message('debug', 'Wallet transaction SQL: ' . $sql);
            log_message('debug', 'Wallet transaction params: ' . json_encode($params));
            
            $db->query($sql, $params);

            $db->transComplete();
            return $db->transStatus();
        } catch (\Exception $e) {
            $db->transRollback();
            throw $e;
        }
    }

    /**
     * Check if user has sufficient balance
     */
    public function hasSufficientBalance($userId, $amount)
    {
        $balance = $this->getBalance($userId);
        return $balance >= $amount;
    }

    /**
     * Get wallet statistics
     */
    public function getWalletStats()
    {
        $stats = [
            'total_wallets' => $this->countAll(),
            'total_balance' => $this->selectSum('balance')->first()['balance'] ?? 0,
            'average_balance' => $this->selectAvg('balance')->first()['balance'] ?? 0,
        ];

        return $stats;
    }
} 