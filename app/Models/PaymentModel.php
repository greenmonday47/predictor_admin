<?php

namespace App\Models;

use CodeIgniter\Model;

class PaymentModel extends Model
{
    protected $table = 'payments';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;
    protected $allowedFields = ['user_id', 'stack_id', 'amount', 'transaction_id', 'status'];

    // Dates
    protected $useTimestamps = false;
    protected $dateFormat = 'datetime';
    protected $createdField = false;
    protected $updatedField = false;
    protected $deletedField = false;

    // Validation
    protected $validationRules = [
        'user_id' => 'required|integer|is_natural_no_zero',
        'stack_id' => 'required|integer|is_natural_no_zero',
        'amount' => 'required|numeric|greater_than[0]',
        'transaction_id' => 'required|min_length[10]|max_length[100]|is_unique[payments.transaction_id,id,{id}]',
        'status' => 'required|in_list[pending,success,failed]',
    ];

    protected $validationMessages = [
        'user_id' => [
            'required' => 'User ID is required',
            'integer' => 'User ID must be an integer',
            'is_natural_no_zero' => 'User ID must be a positive integer',
        ],
        'stack_id' => [
            'required' => 'Stack ID is required',
            'integer' => 'Stack ID must be an integer',
            'is_natural_no_zero' => 'Stack ID must be a positive integer',
        ],
        'amount' => [
            'required' => 'Amount is required',
            'numeric' => 'Amount must be a number',
            'greater_than' => 'Amount must be greater than 0',
        ],
        'transaction_id' => [
            'required' => 'Transaction ID is required',
            'min_length' => 'Transaction ID must be at least 10 characters',
            'max_length' => 'Transaction ID cannot exceed 100 characters',
            'is_unique' => 'Transaction ID already exists',
        ],
        'status' => [
            'required' => 'Status is required',
            'in_list' => 'Status must be pending, success, or failed',
        ],
    ];

    protected $skipValidation = false;
    protected $cleanValidationRules = true;

    /**
     * Check if user has paid for a stack
     */
    public function hasUserPaid($userId, $stackId)
    {
        return $this->where('user_id', $userId)
                   ->where('stack_id', $stackId)
                   ->where('status', 'success')
                   ->countAllResults() > 0;
    }

    /**
     * Get user's payment for a stack
     */
    public function getUserPayment($userId, $stackId)
    {
        return $this->where('user_id', $userId)
                   ->where('stack_id', $stackId)
                   ->orderBy('paid_at', 'DESC')
                   ->first();
    }

    /**
     * Create a new payment record
     */
    public function createPayment($userId, $stackId, $amount, $transactionId)
    {
        return $this->insert([
            'user_id' => $userId,
            'stack_id' => $stackId,
            'amount' => $amount,
            'transaction_id' => $transactionId,
            'status' => 'pending'
        ]);
    }

    /**
     * Update payment status
     */
    public function updatePaymentStatus($transactionId, $status)
    {
        return $this->where('transaction_id', $transactionId)
                   ->set(['status' => $status])
                   ->update();
    }

    /**
     * Get payment by transaction ID
     */
    public function getByTransactionId($transactionId)
    {
        return $this->where('transaction_id', $transactionId)->first();
    }

    /**
     * Get all payments for a user
     */
    public function getUserPayments($userId)
    {
        return $this->select('payments.*, stacks.title, stacks.entry_fee')
                   ->join('stacks', 'stacks.id = payments.stack_id')
                   ->where('payments.user_id', $userId)
                   ->orderBy('payments.paid_at', 'DESC')
                   ->findAll();
    }

    /**
     * Get all payments for a stack
     */
    public function getStackPayments($stackId)
    {
        return $this->select('payments.*, users.full_name, users.phone')
                   ->join('users', 'users.id = payments.user_id')
                   ->where('payments.stack_id', $stackId)
                   ->orderBy('payments.paid_at', 'DESC')
                   ->findAll();
    }

    /**
     * Generate unique transaction ID
     */
    public function generateTransactionId()
    {
        do {
            $transactionId = 'TXN' . date('YmdHis') . rand(1000, 9999);
        } while ($this->getByTransactionId($transactionId));

        return $transactionId;
    }

    /**
     * Get payment statistics
     */
    public function getPaymentStats()
    {
        $stats = [
            'total_payments' => $this->countAll(),
            'successful_payments' => $this->where('status', 'success')->countAllResults(),
            'pending_payments' => $this->where('status', 'pending')->countAllResults(),
            'failed_payments' => $this->where('status', 'failed')->countAllResults(),
            'total_revenue' => $this->where('status', 'success')->selectSum('amount')->first()['amount'] ?? 0,
        ];

        return $stats;
    }

    /**
     * Get all payments with user and stack details
     */
    public function getAllPaymentsWithDetails()
    {
        return $this->select('payments.*, users.full_name, users.phone, stacks.title, stacks.entry_fee')
                   ->join('users', 'users.id = payments.user_id')
                   ->join('stacks', 'stacks.id = payments.stack_id')
                   ->orderBy('payments.paid_at', 'DESC')
                   ->findAll();
    }
} 