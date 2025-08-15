<?php

namespace App\Models;

use CodeIgniter\Model;

class TopUpTransactionModel extends Model
{
    protected $table = 'topup_transactions';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;
    protected $allowedFields = [
        'user_id', 'msisdn', 'amount', 'transaction_id', 'gmpay_reference', 
        'status', 'gmpay_status', 'created_at', 'updated_at'
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    protected $deletedField = false;

    // Validation
    protected $validationRules = [
        'user_id' => 'required|integer|is_natural_no_zero',
        'msisdn' => 'required|min_length[12]|max_length[12]',
        'amount' => 'required|numeric|greater_than[0]',
        'transaction_id' => 'required|min_length[10]|max_length[50]',
        'status' => 'required|in_list[pending,success,failed]',
    ];

    protected $validationMessages = [
        'user_id' => [
            'required' => 'User ID is required',
            'integer' => 'User ID must be an integer',
            'is_natural_no_zero' => 'User ID must be a positive integer',
        ],
        'msisdn' => [
            'required' => 'Phone number is required',
            'min_length' => 'Phone number must be 12 digits (256XXXXXXXX)',
            'max_length' => 'Phone number must be 12 digits (256XXXXXXXX)',
        ],
        'amount' => [
            'required' => 'Amount is required',
            'numeric' => 'Amount must be a number',
            'greater_than' => 'Amount must be greater than 0',
        ],
        'transaction_id' => [
            'required' => 'Transaction ID is required',
            'min_length' => 'Transaction ID must be at least 10 characters',
            'max_length' => 'Transaction ID cannot exceed 50 characters',
        ],
        'status' => [
            'required' => 'Status is required',
            'in_list' => 'Status must be pending, success, or failed',
        ],
    ];

    protected $skipValidation = false;
    protected $cleanValidationRules = true;

    /**
     * Create a new top-up transaction
     */
    public function createTopUpTransaction($userId, $msisdn, $amount, $transactionId)
    {
        return $this->insert([
            'user_id' => $userId,
            'msisdn' => $msisdn,
            'amount' => $amount,
            'transaction_id' => $transactionId,
            'status' => 'pending',
            'gmpay_status' => 'PENDING'
        ]);
    }

    /**
     * Get transaction by transaction ID
     */
    public function getByTransactionId($transactionId)
    {
        return $this->where('transaction_id', $transactionId)->first();
    }

    /**
     * Update transaction status
     */
    public function updateStatus($transactionId, $status, $gmpayStatus = null, $gmpayReference = null)
    {
        $data = ['status' => $status];
        
        if ($gmpayStatus !== null) {
            $data['gmpay_status'] = $gmpayStatus;
        }
        
        if ($gmpayReference !== null) {
            $data['gmpay_reference'] = $gmpayReference;
        }

        return $this->where('transaction_id', $transactionId)
                   ->set($data)
                   ->update();
    }

    /**
     * Get pending transactions for cron job
     */
    public function getPendingTransactions()
    {
        return $this->where('status', 'pending')
                   ->where('gmpay_status', 'PENDING')
                   ->findAll();
    }

    /**
     * Get user's top-up history
     */
    public function getUserTopUpHistory($userId, $limit = 20, $offset = 0)
    {
        return $this->where('user_id', $userId)
                   ->orderBy('created_at', 'DESC')
                   ->limit($limit, $offset)
                   ->findAll();
    }

    /**
     * Get user's top-up statistics
     */
    public function getUserTopUpStats($userId)
    {
        $stats = [
            'total_topups' => $this->where('user_id', $userId)->countAllResults(),
            'successful_topups' => $this->where('user_id', $userId)
                                      ->where('status', 'success')
                                      ->countAllResults(),
            'pending_topups' => $this->where('user_id', $userId)
                                   ->where('status', 'pending')
                                   ->countAllResults(),
            'failed_topups' => $this->where('user_id', $userId)
                                  ->where('status', 'failed')
                                  ->countAllResults(),
            'total_amount' => $this->where('user_id', $userId)
                                 ->where('status', 'success')
                                 ->selectSum('amount')
                                 ->first()['amount'] ?? 0,
        ];

        return $stats;
    }

    /**
     * Generate unique transaction ID for GM Pay
     */
    public function generateTransactionId()
    {
        do {
            $transactionId = date('YmdHis') . rand(100, 999);
        } while ($this->getByTransactionId($transactionId));

        return $transactionId;
    }

    /**
     * Get all top-up transactions with user details
     */
    public function getAllTopUpTransactionsWithDetails()
    {
        return $this->select('topup_transactions.*, users.full_name, users.phone')
                   ->join('users', 'users.id = topup_transactions.user_id')
                   ->orderBy('topup_transactions.created_at', 'DESC')
                   ->findAll();
    }

    /**
     * Get top-up transaction statistics
     */
    public function getTopUpStats()
    {
        $stats = [
            'total_transactions' => $this->countAll(),
            'successful_transactions' => $this->where('status', 'success')->countAllResults(),
            'pending_transactions' => $this->where('status', 'pending')->countAllResults(),
            'failed_transactions' => $this->where('status', 'failed')->countAllResults(),
            'total_amount' => $this->where('status', 'success')->selectSum('amount')->first()['amount'] ?? 0,
        ];

        return $stats;
    }
} 