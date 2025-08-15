<?php

namespace App\Controllers;

use App\Models\WalletModel;
use App\Models\WalletTransactionModel;
use App\Models\TopUpTransactionModel;
use App\Models\PaymentModel;
use App\Models\StackModel;
use CodeIgniter\RESTful\ResourceController;
use CodeIgniter\API\ResponseTrait;

class WalletController extends ResourceController
{
    use ResponseTrait;

    protected $walletModel;
    protected $walletTransactionModel;
    protected $topUpTransactionModel;
    protected $paymentModel;
    protected $stackModel;

    public function __construct()
    {
        $this->walletModel = new WalletModel();
        $this->walletTransactionModel = new WalletTransactionModel();
        $this->topUpTransactionModel = new TopUpTransactionModel();
        $this->paymentModel = new PaymentModel();
        $this->stackModel = new StackModel();
    }

    /**
     * Get user's wallet balance
     */
    public function getBalance()
    {
        $userId = $this->request->getPost('user_id') ?? $this->request->getGet('user_id');
        
        if (!$userId) {
            return $this->failValidationErrors(['user_id' => 'User ID is required']);
        }

        try {
            $balance = $this->walletModel->getBalance($userId);
            
            return $this->respond([
                'status' => 'success',
                'message' => 'Wallet balance retrieved successfully',
                'data' => [
                    'user_id' => $userId,
                    'balance' => $balance,
                    'formatted_balance' => number_format($balance, 0, '.', ',')
                ]
            ]);
        } catch (\Exception $e) {
            return $this->failServerError('Failed to get wallet balance: ' . $e->getMessage());
        }
    }

    /**
     * Get user's transaction history
     */
    public function getTransactionHistory()
    {
        $userId = $this->request->getPost('user_id') ?? $this->request->getGet('user_id');
        $page = (int) ($this->request->getGet('page') ?? 1);
        $perPage = (int) ($this->request->getGet('per_page') ?? 20);
        
        if (!$userId) {
            return $this->failValidationErrors(['user_id' => 'User ID is required']);
        }

        try {
            // Get wallet transactions (stack participations)
            $walletTransactions = $this->walletTransactionModel->getUserTransactionsPaginated($userId, $page, $perPage);
            
            // Get successful top-up transactions
            $topUpTransactions = $this->topUpTransactionModel->getUserTopUpHistory($userId, 100, 0);
            $successfulTopUps = [];
            
            foreach ($topUpTransactions as $topUp) {
                if ($topUp['status'] === 'success') {
                    $successfulTopUps[] = [
                        'id' => 'topup_' . $topUp['id'],
                        'user_id' => $topUp['user_id'],
                        'type' => 'credit',
                        'amount' => $topUp['amount'],
                        'balance_before' => 0, // We don't track this for top-ups
                        'balance_after' => 0,   // We don't track this for top-ups
                        'description' => 'Wallet top-up via GM Pay',
                        'reference' => $topUp['transaction_id'],
                        'reference_type' => 'topup',
                        'status' => 'completed',
                        'created_at' => $topUp['created_at'],
                        'transaction_source' => 'topup'
                    ];
                }
            }
            
            // Combine and sort all transactions by date
            $allTransactions = array_merge($walletTransactions['transactions'], $successfulTopUps);
            usort($allTransactions, function($a, $b) {
                return strtotime($b['created_at']) - strtotime($a['created_at']);
            });
            
            // Apply pagination to combined results
            $offset = ($page - 1) * $perPage;
            $paginatedTransactions = array_slice($allTransactions, $offset, $perPage);
            $total = count($allTransactions);
            
            $result = [
                'transactions' => $paginatedTransactions,
                'total' => $total,
                'page' => $page,
                'per_page' => $perPage,
                'total_pages' => ceil($total / $perPage),
            ];
            
            return $this->respond([
                'status' => 'success',
                'message' => 'Transaction history retrieved successfully',
                'data' => $result
            ]);
        } catch (\Exception $e) {
            return $this->failServerError('Failed to get transaction history: ' . $e->getMessage());
        }
    }

    /**
     * Top up wallet with GM Pay integration
     */
    public function topUp()
    {
        $userId = $this->request->getPost('user_id');
        $amount = $this->request->getPost('amount');
        $msisdn = $this->request->getPost('msisdn');
        
        if (!$userId || !$amount || !$msisdn) {
            return $this->failValidationErrors(['fields' => 'User ID, amount, and phone number are required']);
        }

        if (!is_numeric($amount) || $amount <= 0) {
            return $this->failValidationErrors(['amount' => 'Amount must be a positive number']);
        }

        // Validate and format phone number
        $formattedMsisdn = $this->formatPhoneNumber($msisdn);
        if (!$formattedMsisdn) {
            return $this->failValidationErrors(['msisdn' => 'Invalid phone number format. Please use format: 256XXXXXXXX']);
        }

        try {
            // Generate unique transaction ID
            $transactionId = $this->topUpTransactionModel->generateTransactionId();
            
            // Create top-up transaction record
            $topUpId = $this->topUpTransactionModel->createTopUpTransaction(
                $userId, 
                $formattedMsisdn, 
                $amount, 
                $transactionId
            );

            if (!$topUpId) {
                return $this->failServerError('Failed to create top-up transaction');
            }

            // Prepare GM Pay payload
            $gmpayPayload = [
                'msisdn' => $formattedMsisdn,
                'amount' => (string)$amount,
                'transactionId' => $transactionId
            ];

            // Send request to GM Pay
            $gmpayResponse = $this->sendToGMPay($gmpayPayload);
            
            if ($gmpayResponse['success']) {
                return $this->respond([
                    'status' => 'success',
                    'message' => 'Top-up request sent to GM Pay successfully',
                    'data' => [
                        'user_id' => $userId,
                        'amount' => $amount,
                        'msisdn' => $formattedMsisdn,
                        'transaction_id' => $transactionId,
                        'gmpay_payload' => $gmpayPayload,
                        'gmpay_response' => $gmpayResponse['response'],
                        'status' => 'pending',
                        'message' => 'Please check your phone for the payment prompt'
                    ]
                ]);
            } else {
                // Update transaction status to failed
                $this->topUpTransactionModel->updateStatus($transactionId, 'failed', 'FAILED');
                
                return $this->failServerError('Failed to process payment with GM Pay: ' . ($gmpayResponse['error'] ?? 'Unknown error'));
            }
        } catch (\Exception $e) {
            return $this->failServerError('Failed to top up wallet: ' . $e->getMessage());
        }
    }

    /**
     * Participate in stack using wallet balance
     */
    public function participateInStack()
    {
        $userId = $this->request->getPost('user_id');
        $stackId = $this->request->getPost('stack_id');
        $predictions = $this->request->getPost('predictions');
        
        if (!$userId || !$stackId) {
            return $this->failValidationErrors(['fields' => 'User ID and stack ID are required']);
        }

        try {
            // Get stack details
            $stack = $this->stackModel->find($stackId);
            if (!$stack) {
                return $this->failNotFound('Stack not found');
            }

            $entryFee = (float) $stack['entry_fee'];
            
            // Check if user has sufficient balance
            if (!$this->walletModel->hasSufficientBalance($userId, $entryFee)) {
                return $this->failValidationErrors(['balance' => 'Insufficient wallet balance. Required: ' . number_format($entryFee, 0, '.', ',') . ' UGX']);
            }

            // Note: Users can participate multiple times in the same stack to increase their chances of winning

            // Generate transaction reference
            $reference = 'STK' . $stackId . date('YmdHis') . rand(100, 999);
            
            // Debit wallet
            $description = "Participation in stack: {$stack['title']}";
            $success = $this->walletModel->debit($userId, $entryFee, $description, $reference, 'stack_participation');
            
            if (!$success) {
                return $this->failServerError('Failed to process wallet transaction');
            }

            // Create payment record
            $transactionId = $this->paymentModel->generateTransactionId();
            $paymentId = $this->paymentModel->createPayment($userId, $stackId, $entryFee, $transactionId);
            
            if (!$paymentId) {
                // If payment record creation fails, we should refund the wallet
                $this->walletModel->credit($userId, $entryFee, "Refund for failed payment record", $reference . '_REFUND', 'refund');
                return $this->failServerError('Failed to create payment record');
            }

            // Update payment status to success
            $this->paymentModel->updatePaymentStatus($transactionId, 'success');
            
            // Process predictions if provided
            $predictionsData = null;
            if ($predictions) {
                $predictionsArray = json_decode($predictions, true);
                if ($predictionsArray) {
                    // Save the predictions to the database
                    $predictionModel = new \App\Models\PredictionModel();
                    
                    // Validate predictions structure
                    if ($predictionModel->validatePredictionsStructure($predictionsArray)) {
                        // Save prediction with payment transaction ID
                        $predictionId = $predictionModel->submitPredictionWithPayment($userId, $stackId, $predictionsArray, $transactionId);
                        
                        if ($predictionId) {
                            // Update payment status to paid since wallet transaction was successful
                            $predictionModel->updatePaymentStatus($transactionId, 'paid');
                            $predictionsData = $predictionsArray;
                        } else {
                            // If prediction saving fails, refund the wallet
                            $this->walletModel->credit($userId, $entryFee, "Refund for failed prediction save", $reference . '_REFUND', 'refund');
                            return $this->failServerError('Failed to save predictions');
                        }
                    } else {
                        // If predictions are invalid, refund the wallet
                        $this->walletModel->credit($userId, $entryFee, "Refund for invalid predictions", $reference . '_REFUND', 'refund');
                        return $this->failServerError('Invalid predictions format');
                    }
                }
            }

            $newBalance = $this->walletModel->getBalance($userId);
            
            return $this->respond([
                'status' => 'success',
                'message' => 'Successfully participated in stack',
                'data' => [
                    'user_id' => $userId,
                    'stack_id' => $stackId,
                    'amount' => $entryFee,
                    'transaction_id' => $transactionId,
                    'wallet_reference' => $reference,
                    'new_balance' => $newBalance,
                    'formatted_new_balance' => number_format($newBalance, 0, '.', ','),
                    'predictions' => $predictionsData
                ]
            ]);
        } catch (\Exception $e) {
            return $this->failServerError('Failed to participate in stack: ' . $e->getMessage());
        }
    }

    /**
     * Get wallet statistics
     */
    public function getWalletStats()
    {
        $userId = $this->request->getPost('user_id') ?? $this->request->getGet('user_id');
        
        if (!$userId) {
            return $this->failValidationErrors(['user_id' => 'User ID is required']);
        }

        try {
            $balance = $this->walletModel->getBalance($userId);
            $transactionStats = $this->walletTransactionModel->getUserTransactionStats($userId);
            $recentTransactions = $this->walletTransactionModel->getRecentTransactions($userId, 5);
            
            // Get top-up statistics
            $topUpStats = $this->topUpTransactionModel->getUserTopUpStats($userId);
            
            // Combine recent transactions with recent top-ups
            $recentTopUps = $this->topUpTransactionModel->getUserTopUpHistory($userId, 5, 0);
            $recentTopUpTransactions = [];
            
            foreach ($recentTopUps as $topUp) {
                if ($topUp['status'] === 'success') {
                    $recentTopUpTransactions[] = [
                        'id' => 'topup_' . $topUp['id'],
                        'user_id' => $topUp['user_id'],
                        'type' => 'credit',
                        'amount' => $topUp['amount'],
                        'balance_before' => 0,
                        'balance_after' => 0,
                        'description' => 'Wallet top-up via GM Pay',
                        'reference' => $topUp['transaction_id'],
                        'reference_type' => 'topup',
                        'status' => 'completed',
                        'created_at' => $topUp['created_at'],
                        'transaction_source' => 'topup'
                    ];
                }
            }
            
            // Combine and sort recent transactions
            $allRecentTransactions = array_merge($recentTransactions, $recentTopUpTransactions);
            usort($allRecentTransactions, function($a, $b) {
                return strtotime($b['created_at']) - strtotime($a['created_at']);
            });
            
            // Take only the 5 most recent
            $allRecentTransactions = array_slice($allRecentTransactions, 0, 5);
            
            return $this->respond([
                'status' => 'success',
                'message' => 'Wallet statistics retrieved successfully',
                'data' => [
                    'user_id' => $userId,
                    'current_balance' => $balance,
                    'formatted_balance' => number_format($balance, 0, '.', ','),
                    'transaction_stats' => $transactionStats,
                    'topup_stats' => $topUpStats,
                    'recent_transactions' => $allRecentTransactions
                ]
            ]);
        } catch (\Exception $e) {
            return $this->failServerError('Failed to get wallet statistics: ' . $e->getMessage());
        }
    }

    /**
     * Check if user can participate in stack
     */
    public function checkParticipationEligibility()
    {
        $userId = $this->request->getPost('user_id') ?? $this->request->getGet('user_id');
        $stackId = $this->request->getPost('stack_id') ?? $this->request->getGet('stack_id');
        
        if (!$userId || !$stackId) {
            return $this->failValidationErrors(['fields' => 'User ID and stack ID are required']);
        }

        try {
            // Get stack details
            $stack = $this->stackModel->find($stackId);
            if (!$stack) {
                return $this->failNotFound('Stack not found');
            }

            $entryFee = (float) $stack['entry_fee'];
            $walletBalance = $this->walletModel->getBalance($userId);
            $hasPaid = $this->paymentModel->hasUserPaid($userId, $stackId);
            
            return $this->respond([
                'status' => 'success',
                'message' => 'Eligibility check completed',
                'data' => [
                    'user_id' => $userId,
                    'stack_id' => $stackId,
                    'entry_fee' => $entryFee,
                    'wallet_balance' => $walletBalance,
                    'formatted_balance' => number_format($walletBalance, 0, '.', ','),
                    'has_sufficient_balance' => $walletBalance >= $entryFee,
                    'has_already_participated' => $hasPaid,
                    'can_participate' => $walletBalance >= $entryFee // Users can participate multiple times
                ]
            ]);
        } catch (\Exception $e) {
            return $this->failServerError('Failed to check eligibility: ' . $e->getMessage());
        }
    }

    /**
     * Format phone number for GM Pay (256XXXXXXXX format)
     */
    private function formatPhoneNumber($phoneNumber)
    {
        // Remove any non-digit characters
        $phoneNumber = preg_replace('/[^0-9]/', '', $phoneNumber);
        
        // If it starts with 0, remove it
        if (str_starts_with($phoneNumber, '0')) {
            $phoneNumber = substr($phoneNumber, 1);
        }
        
        // If it doesn't start with 256, add it
        if (!str_starts_with($phoneNumber, '256')) {
            $phoneNumber = '256' . $phoneNumber;
        }
        
        // Validate the final format (should be exactly 12 digits: 256XXXXXXXX)
        if (strlen($phoneNumber) === 12 && preg_match('/^256\d{9}$/', $phoneNumber)) {
            return $phoneNumber;
        }
        
        return false;
    }

    /**
     * Send payment request to GM Pay
     */
    private function sendToGMPay($payload)
    {
        $url = 'https://debit.gmpayapp.site/public/deposit/custom';
        
        try {
            $client = \Config\Services::curlrequest();
            $response = $client->post($url, [
                'timeout' => 30,
                'verify' => false, // Disable SSL verification
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json'
                ],
                'json' => $payload
            ]);

            $responseData = json_decode($response->getBody(), true);
            
            return [
                'status_code' => $response->getStatusCode(),
                'response' => $responseData,
                'success' => $response->getStatusCode() === 200
            ];
        } catch (\Exception $e) {
            log_message('error', 'GM Pay API call failed: ' . $e->getMessage());
            return [
                'status_code' => 0,
                'response' => null,
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Check transaction status from GM Pay
     */
    public function checkTopUpStatus($transactionId = null)
    {
        if (!$transactionId) {
            return $this->failValidationErrors(['transaction_id' => 'Transaction ID required']);
        }

        try {
            $topUpTransaction = $this->topUpTransactionModel->getByTransactionId($transactionId);
            
            if (!$topUpTransaction) {
                return $this->failNotFound('Top-up transaction not found');
            }

            // Check GM Pay status
            $gmpayStatus = $this->checkGMPayStatus($transactionId);
            
            if ($gmpayStatus) {
                // Update transaction status if it has changed
                if ($gmpayStatus !== $topUpTransaction['gmpay_status']) {
                    $this->topUpTransactionModel->updateStatus($transactionId, strtolower($gmpayStatus), $gmpayStatus);
                    $topUpTransaction['gmpay_status'] = $gmpayStatus;
                    $topUpTransaction['status'] = strtolower($gmpayStatus);
                    
                    // If payment is successful, credit the wallet
                    if ($gmpayStatus === 'SUCCESS') {
                        $this->processSuccessfulTopUp($topUpTransaction);
                    }
                }
            }

            return $this->respond([
                'status' => 'success',
                'data' => [
                    'transaction_id' => $topUpTransaction['transaction_id'],
                    'status' => $topUpTransaction['status'],
                    'gmpay_status' => $topUpTransaction['gmpay_status'],
                    'amount' => $topUpTransaction['amount'],
                    'msisdn' => $topUpTransaction['msisdn'],
                    'created_at' => $topUpTransaction['created_at']
                ]
            ]);
        } catch (\Exception $e) {
            return $this->failServerError('Status check failed: ' . $e->getMessage());
        }
    }

    /**
     * Check GM Pay transaction status
     */
    private function checkGMPayStatus($transactionId)
    {
        $url = "https://debit.gmpayapp.site/public/transaction-status/{$transactionId}";
        
        try {
            $client = \Config\Services::curlrequest();
            $response = $client->get($url, [
                'timeout' => 10,
                'verify' => false, // Disable SSL verification
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json'
                ]
            ]);

            if ($response->getStatusCode() === 200) {
                $data = json_decode($response->getBody(), true);
                
                if (isset($data['status']) && $data['status'] === 'success') {
                    $transaction = $data['transaction'];
                    return strtoupper($transaction['status']); // SUCCESS, FAILED, PENDING
                }
            }
        } catch (\Exception $e) {
            log_message('error', 'GM Pay status check failed: ' . $e->getMessage());
        }

        return null;
    }

    /**
     * Process successful top-up by crediting wallet
     */
    private function processSuccessfulTopUp($topUpTransaction)
    {
        try {
            $userId = $topUpTransaction['user_id'];
            $amount = $topUpTransaction['amount'];
            $transactionId = $topUpTransaction['transaction_id'];
            
            // Credit the wallet
            $description = "Wallet top-up via GM Pay (Transaction: {$transactionId})";
            $reference = "TOPUP_{$transactionId}";
            
            $success = $this->walletModel->credit($userId, $amount, $description, $reference, 'topup');
            
            if ($success) {
                log_message('info', "Wallet credited successfully for top-up transaction: {$transactionId}");
            } else {
                log_message('error', "Failed to credit wallet for top-up transaction: {$transactionId}");
            }
        } catch (\Exception $e) {
            log_message('error', "Error processing successful top-up: " . $e->getMessage());
        }
    }

    /**
     * Cron job endpoint to check pending top-up transactions
     */
    public function checkPendingTopUps()
    {
        try {
            $pendingTransactions = $this->topUpTransactionModel->getPendingTransactions();
            $processed = 0;
            $successful = 0;
            $failed = 0;

            foreach ($pendingTransactions as $transaction) {
                $processed++;
                
                // Check GM Pay status
                $gmpayStatus = $this->checkGMPayStatus($transaction['transaction_id']);
                
                if ($gmpayStatus && $gmpayStatus !== $transaction['gmpay_status']) {
                    // Update transaction status
                    $this->topUpTransactionModel->updateStatus(
                        $transaction['transaction_id'], 
                        strtolower($gmpayStatus), 
                        $gmpayStatus
                    );
                    
                    // If payment is successful, credit the wallet
                    if ($gmpayStatus === 'SUCCESS') {
                        $this->processSuccessfulTopUp($transaction);
                        $successful++;
                    } elseif ($gmpayStatus === 'FAILED') {
                        $failed++;
                    }
                }
            }

            return $this->respond([
                'status' => 'success',
                'message' => 'Pending top-ups processed',
                'data' => [
                    'processed' => $processed,
                    'successful' => $successful,
                    'failed' => $failed
                ]
            ]);
        } catch (\Exception $e) {
            return $this->failServerError('Failed to process pending top-ups: ' . $e->getMessage());
        }
    }

    /**
     * Get user's top-up history
     */
    public function getTopUpHistory()
    {
        $userId = $this->request->getPost('user_id') ?? $this->request->getGet('user_id');
        $page = (int) ($this->request->getGet('page') ?? 1);
        $perPage = (int) ($this->request->getGet('per_page') ?? 20);
        
        if (!$userId) {
            return $this->failValidationErrors(['user_id' => 'User ID is required']);
        }

        try {
            $offset = ($page - 1) * $perPage;
            $topUps = $this->topUpTransactionModel->getUserTopUpHistory($userId, $perPage, $offset);
            $stats = $this->topUpTransactionModel->getUserTopUpStats($userId);
            
            return $this->respond([
                'status' => 'success',
                'message' => 'Top-up history retrieved successfully',
                'data' => [
                    'topups' => $topUps,
                    'statistics' => $stats,
                    'page' => $page,
                    'per_page' => $perPage
                ]
            ]);
        } catch (\Exception $e) {
            return $this->failServerError('Failed to get top-up history: ' . $e->getMessage());
        }
    }

    /**
     * Get top-up transaction details with user information
     */
    public function getTopUpTransactionDetails($transactionId = null)
    {
        if (!$transactionId) {
            return $this->failValidationErrors(['transaction_id' => 'Transaction ID required']);
        }

        try {
            // Get transaction with user details
            $transaction = $this->topUpTransactionModel->select('topup_transactions.*, users.full_name, users.phone')
                                                      ->join('users', 'users.id = topup_transactions.user_id')
                                                      ->where('topup_transactions.id', $transactionId)
                                                      ->first();
            
            if (!$transaction) {
                return $this->failNotFound('Top-up transaction not found');
            }

            return $this->respond([
                'status' => 'success',
                'data' => $transaction
            ]);
        } catch (\Exception $e) {
            return $this->failServerError('Failed to get transaction details: ' . $e->getMessage());
        }
    }
} 