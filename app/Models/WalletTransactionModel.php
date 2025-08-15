<?php

namespace App\Models;

use CodeIgniter\Model;

class WalletTransactionModel extends Model
{
    protected $table = 'wallet_transactions';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;
    protected $allowedFields = [
        'user_id', 'type', 'amount', 'balance_before', 'balance_after', 
        'description', 'reference', 'reference_type', 'status'
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = false;
    protected $deletedField = false;

    // Validation
    protected $validationRules = [
        'user_id' => 'required|integer|is_natural_no_zero',
        'type' => 'required|in_list[credit,debit]',
        'amount' => 'required|numeric|greater_than[0]',
        'balance_before' => 'required|numeric|greater_than_equal_to[0]',
        'balance_after' => 'required|numeric|greater_than_equal_to[0]',
        'description' => 'required|min_length[3]|max_length[255]',
        'status' => 'required|in_list[pending,completed,failed,cancelled]',
    ];

    protected $validationMessages = [
        'user_id' => [
            'required' => 'User ID is required',
            'integer' => 'User ID must be an integer',
            'is_natural_no_zero' => 'User ID must be a positive integer',
        ],
        'type' => [
            'required' => 'Transaction type is required',
            'in_list' => 'Transaction type must be credit or debit',
        ],
        'amount' => [
            'required' => 'Amount is required',
            'numeric' => 'Amount must be a number',
            'greater_than' => 'Amount must be greater than 0',
        ],
        'balance_before' => [
            'required' => 'Balance before is required',
            'numeric' => 'Balance before must be a number',
            'greater_than_equal_to' => 'Balance before cannot be negative',
        ],
        'balance_after' => [
            'required' => 'Balance after is required',
            'numeric' => 'Balance after must be a number',
            'greater_than_equal_to' => 'Balance after cannot be negative',
        ],
        'description' => [
            'required' => 'Description is required',
            'min_length' => 'Description must be at least 3 characters',
            'max_length' => 'Description cannot exceed 255 characters',
        ],
        'status' => [
            'required' => 'Status is required',
            'in_list' => 'Status must be pending, completed, failed, or cancelled',
        ],
    ];

    protected $skipValidation = true;
    protected $cleanValidationRules = true;

    /**
     * Get user's transaction history
     */
    public function getUserTransactions($userId, $limit = 50, $offset = 0)
    {
        return $this->where('user_id', $userId)
                   ->orderBy('created_at', 'DESC')
                   ->limit($limit, $offset)
                   ->findAll();
    }

    /**
     * Get user's transaction history with pagination
     */
    public function getUserTransactionsPaginated($userId, $page = 1, $perPage = 20)
    {
        $offset = ($page - 1) * $perPage;
        
        $transactions = $this->where('user_id', $userId)
                           ->orderBy('created_at', 'DESC')
                           ->limit($perPage, $offset)
                           ->findAll();
        
        $total = $this->where('user_id', $userId)->countAllResults();
        
        return [
            'transactions' => $transactions,
            'total' => $total,
            'page' => $page,
            'per_page' => $perPage,
            'total_pages' => ceil($total / $perPage),
        ];
    }

    /**
     * Get transaction by reference
     */
    public function getByReference($reference)
    {
        return $this->where('reference', $reference)->first();
    }

    /**
     * Get transactions by reference type
     */
    public function getByReferenceType($userId, $referenceType)
    {
        return $this->where('user_id', $userId)
                   ->where('reference_type', $referenceType)
                   ->orderBy('created_at', 'DESC')
                   ->findAll();
    }

    /**
     * Get transaction statistics for user
     */
    public function getUserTransactionStats($userId)
    {
        $stats = [
            'total_transactions' => $this->where('user_id', $userId)->countAllResults(),
            'total_credits' => $this->where('user_id', $userId)
                                  ->where('type', 'credit')
                                  ->where('status', 'completed')
                                  ->selectSum('amount')
                                  ->first()['amount'] ?? 0,
            'total_debits' => $this->where('user_id', $userId)
                                 ->where('type', 'debit')
                                 ->where('status', 'completed')
                                 ->selectSum('amount')
                                 ->first()['amount'] ?? 0,
            'pending_transactions' => $this->where('user_id', $userId)
                                         ->where('status', 'pending')
                                         ->countAllResults(),
        ];

        return $stats;
    }

    /**
     * Get recent transactions for user
     */
    public function getRecentTransactions($userId, $limit = 10)
    {
        return $this->where('user_id', $userId)
                   ->where('status', 'completed')
                   ->orderBy('created_at', 'DESC')
                   ->limit($limit)
                   ->findAll();
    }

    /**
     * Update transaction status
     */
    public function updateTransactionStatus($id, $status)
    {
        return $this->where('id', $id)
                   ->set(['status' => $status])
                   ->update();
    }

    /**
     * Get all transactions with user details (for admin)
     */
    public function getAllTransactionsWithUserDetails($limit = 100, $offset = 0)
    {
        return $this->select('wallet_transactions.*, users.full_name, users.phone')
                   ->join('users', 'users.id = wallet_transactions.user_id')
                   ->orderBy('wallet_transactions.created_at', 'DESC')
                   ->limit($limit, $offset)
                   ->findAll();
    }
} 