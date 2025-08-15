<?php

namespace App\Controllers;

use App\Models\PaymentModel;
use App\Models\StackModel;
class PaymentController extends BaseController
{
    protected $format = 'json';

    public function __construct()
    {
        $this->paymentModel = new PaymentModel();
        $this->stackModel = new StackModel();
    }

    /**
     * Initialize payment for a stack using GMPay
     */
    public function initialize()
    {
        $rules = [
            'user_id' => 'required|integer|is_natural_no_zero',
            'stack_id' => 'required|integer|is_natural_no_zero',
        ];

        if (!$this->validate($rules)) {
            return $this->failValidationErrors($this->validator->getErrors());
        }

        $userId = $this->request->getPost('user_id');
        $stackId = $this->request->getPost('stack_id');

        try {
            // Get user details for phone number
            $userModel = new \App\Models\UserModel();
            $user = $userModel->find($userId);
            if (!$user) {
                return $this->failNotFound('User not found');
            }

            // Format phone number for GMPay (add 256 prefix if not present)
            $phoneNumber = $user['phone'];
            if (!str_starts_with($phoneNumber, '256')) {
                // Remove leading 0 if present and add 256
                $phoneNumber = '256' . ltrim($phoneNumber, '0');
            }

            // Check if stack exists
            $stack = $this->stackModel->find($stackId);
            if (!$stack) {
                return $this->failNotFound('Stack not found');
            }

            // Check if stack is still active
            if (!$this->stackModel->isOpenForPredictions($stackId)) {
                return $this->failValidationError('Stack is closed');
            }

            // Check if user has already paid
            if ($this->paymentModel->hasUserPaid($userId, $stackId)) {
                return $this->failValidationError('User has already paid for this stack');
            }

            // Generate 13-digit transaction ID for GMPay
            $transactionId = $this->generateGMPayTransactionId();

            // Create payment record
            $paymentId = $this->paymentModel->createPayment(
                $userId, 
                $stackId, 
                $stack['entry_fee'], 
                $transactionId
            );

            if ($paymentId) {
                            // Prepare GMPay payload
            $gmpayPayload = [
                'msisdn' => $phoneNumber,
                'amount' => $stack['entry_fee'],
                'transactionId' => $transactionId
            ];

                return $this->respond([
                    'status' => 'success',
                    'message' => 'Payment initialized successfully',
                    'data' => [
                        'payment_id' => $paymentId,
                        'transaction_id' => $transactionId,
                        'amount' => $stack['entry_fee'],
                        'stack_title' => $stack['title'],
                        'gmpay_payload' => $gmpayPayload,
                        'gmpay_url' => 'https://debit.gmpayapp.site/public/deposit/custom'
                    ]
                ], 201);
            } else {
                return $this->failServerError('Failed to initialize payment');
            }
        } catch (\Exception $e) {
            return $this->failServerError('Payment initialization failed: ' . $e->getMessage());
        }
    }

    /**
     * Generate 13-digit transaction ID for GMPay
     */
    private function generateGMPayTransactionId()
    {
        do {
            $transactionId = date('YmdHis') . rand(10, 99);
        } while ($this->paymentModel->getByTransactionId($transactionId));

        return $transactionId;
    }

    /**
     * Verify payment status from GMPay
     */
    public function verify($transactionId = null)
    {
        if (!$transactionId) {
            return $this->failValidationError('Transaction ID required');
        }

        try {
            $payment = $this->paymentModel->getByTransactionId($transactionId);
            
            if (!$payment) {
                return $this->failNotFound('Payment not found');
            }

            // Check GMPay status
            $gmpayStatus = $this->checkGMPayStatus($transactionId);
            
            // Update payment status if it has changed
            if ($gmpayStatus && $gmpayStatus !== $payment['status']) {
                $this->paymentModel->updatePaymentStatus($transactionId, $gmpayStatus);
                $payment['status'] = $gmpayStatus;
                
                // Update prediction status based on payment status
                $predictionModel = new \App\Models\PredictionModel();
                if ($gmpayStatus === 'SUCCESS') {
                    $predictionModel->updatePaymentStatus($transactionId, 'paid');
                } elseif ($gmpayStatus === 'FAILED') {
                    $predictionModel->updatePaymentStatus($transactionId, 'failed');
                }
            }

            return $this->respond([
                'status' => 'success',
                'data' => [
                    'transaction_id' => $payment['transaction_id'],
                    'status' => $payment['status'],
                    'amount' => $payment['amount'],
                    'paid_at' => $payment['paid_at'],
                    'gmpay_status' => $gmpayStatus
                ]
            ]);
        } catch (\Exception $e) {
            return $this->failServerError('Payment verification failed: ' . $e->getMessage());
        }
    }

    /**
     * Check payment status from GMPay API
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
            log_message('error', 'GMPay status check failed: ' . $e->getMessage());
        }

        return null;
    }

    /**
     * Update payment status (webhook from payment gateway)
     */
    public function updateStatus()
    {
        $rules = [
            'transaction_id' => 'required|min_length[10]|max_length[100]',
            'status' => 'required|in_list[pending,success,failed]',
        ];

        if (!$this->validate($rules)) {
            return $this->failValidationErrors($this->validator->getErrors());
        }

        $transactionId = $this->request->getPost('transaction_id');
        $status = $this->request->getPost('status');

        try {
            $payment = $this->paymentModel->getByTransactionId($transactionId);
            
            if (!$payment) {
                return $this->failNotFound('Payment not found');
            }

            $updated = $this->paymentModel->updatePaymentStatus($transactionId, $status);

            if ($updated) {
                // Update prediction status based on payment status
                $predictionModel = new \App\Models\PredictionModel();
                if ($status === 'success') {
                    $predictionModel->updatePaymentStatus($transactionId, 'paid');
                } elseif ($status === 'failed') {
                    $predictionModel->updatePaymentStatus($transactionId, 'failed');
                }

                return $this->respond([
                    'status' => 'success',
                    'message' => 'Payment status updated',
                    'data' => [
                        'transaction_id' => $transactionId,
                        'status' => $status
                    ]
                ]);
            } else {
                return $this->failServerError('Failed to update payment status');
            }
        } catch (\Exception $e) {
            return $this->failServerError('Payment status update failed: ' . $e->getMessage());
        }
    }

    /**
     * GMPay webhook endpoint
     */
    public function gmpayWebhook()
    {
        // Get the raw JSON data
        $jsonData = $this->request->getJSON(true);
        
        if (!$jsonData) {
            return $this->failValidationError('Invalid JSON data');
        }

        // Log the webhook data for debugging
        log_message('info', 'GMPay webhook received: ' . json_encode($jsonData));

        try {
            // Extract transaction data
            if (isset($jsonData['status']) && $jsonData['status'] === 'success' && isset($jsonData['transaction'])) {
                $transaction = $jsonData['transaction'];
                $transactionId = $transaction['transaction_id'];
                $status = strtoupper($transaction['status']); // SUCCESS, FAILED, PENDING
                
                // Update payment status
                $updated = $this->paymentModel->updatePaymentStatus($transactionId, $status);
                
                if ($updated) {
                    // Update prediction status based on payment status
                    $predictionModel = new \App\Models\PredictionModel();
                    if ($status === 'SUCCESS') {
                        $predictionModel->updatePaymentStatus($transactionId, 'paid');
                    } elseif ($status === 'FAILED') {
                        $predictionModel->updatePaymentStatus($transactionId, 'failed');
                    }

                    return $this->respond([
                        'status' => 'success',
                        'message' => 'Payment status updated via webhook',
                        'data' => [
                            'transaction_id' => $transactionId,
                            'status' => $status
                        ]
                    ]);
                } else {
                    return $this->failServerError('Failed to update payment status');
                }
            } else {
                return $this->failValidationError('Invalid webhook data structure');
            }
        } catch (\Exception $e) {
            log_message('error', 'GMPay webhook processing failed: ' . $e->getMessage());
            return $this->failServerError('Webhook processing failed: ' . $e->getMessage());
        }
    }

    /**
     * Get user's payment history
     */
    public function getUserPayments($userId = null)
    {
        if (!$userId) {
            return $this->failValidationError('User ID required');
        }

        try {
            $payments = $this->paymentModel->getUserPayments($userId);
            
            return $this->respond([
                'status' => 'success',
                'data' => $payments
            ]);
        } catch (\Exception $e) {
            return $this->failServerError('Failed to fetch payments: ' . $e->getMessage());
        }
    }

    /**
     * Get payment details for a stack (admin function)
     */
    public function getStackPayments($stackId = null)
    {
        if (!$stackId) {
            return $this->failValidationError('Stack ID required');
        }

        try {
            $payments = $this->paymentModel->getStackPayments($stackId);
            
            $stats = [
                'total_payments' => count($payments),
                'successful_payments' => 0,
                'pending_payments' => 0,
                'failed_payments' => 0,
                'total_amount' => 0
            ];

            foreach ($payments as $payment) {
                switch ($payment['status']) {
                    case 'success':
                        $stats['successful_payments']++;
                        $stats['total_amount'] += $payment['amount'];
                        break;
                    case 'pending':
                        $stats['pending_payments']++;
                        break;
                    case 'failed':
                        $stats['failed_payments']++;
                        break;
                }
            }

            return $this->respond([
                'status' => 'success',
                'data' => [
                    'payments' => $payments,
                    'statistics' => $stats
                ]
            ]);
        } catch (\Exception $e) {
            return $this->failServerError('Failed to fetch payments: ' . $e->getMessage());
        }
    }

    /**
     * Get payment history for current user
     */
    public function getPaymentHistory()
    {
        try {
            // Get current user from session or token
            $userId = $this->getCurrentUserId();
            
            if (!$userId) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'User not authenticated'
                ])->setStatusCode(401);
            }

            $paymentModel = new \App\Models\PaymentModel();
            $payments = $paymentModel->getUserPayments($userId);

            if ($payments) {
                return $this->response->setJSON([
                    'status' => 'success',
                    'message' => 'Payment history retrieved successfully',
                    'data' => $payments
                ]);
            } else {
                return $this->response->setJSON([
                    'status' => 'success',
                    'message' => 'No payment history found',
                    'data' => []
                ]);
            }
        } catch (\Exception $e) {
            log_message('error', 'Error getting payment history: ' . $e->getMessage());
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Failed to retrieve payment history'
            ])->setStatusCode(500);
        }
    }

    /**
     * Check if user has paid for a stack
     */
    public function checkPayment($userId = null, $stackId = null)
    {
        if (!$userId || !$stackId) {
            return $this->failValidationError('User ID and Stack ID required');
        }

        try {
            $hasPaid = $this->paymentModel->hasUserPaid($userId, $stackId);
            $payment = $this->paymentModel->getUserPayment($userId, $stackId);
            
            return $this->respond([
                'status' => 'success',
                'data' => [
                    'has_paid' => $hasPaid,
                    'payment_details' => $payment
                ]
            ]);
        } catch (\Exception $e) {
            return $this->failServerError('Payment check failed: ' . $e->getMessage());
        }
    }

    /**
     * Process payment and predictions together
     * This method handles the complete flow: payment + predictions
     */
    public function processPaymentWithPredictions()
    {
        $rules = [
            'user_id' => 'required|integer|is_natural_no_zero',
            'stack_id' => 'required|integer|is_natural_no_zero',
            'predictions' => 'required',
        ];

        if (!$this->validate($rules)) {
            return $this->failValidationErrors($this->validator->getErrors());
        }

        $userId = $this->request->getPost('user_id');
        $stackId = $this->request->getPost('stack_id');
        $predictions = $this->request->getPost('predictions');
        
        // Decode predictions if it's a JSON string
        if (is_string($predictions)) {
            $predictions = json_decode($predictions, true);
        }
        
        // Validate predictions format
        if (!is_array($predictions)) {
            return $this->failValidationError('Predictions must be an array');
        }

        try {
            // Step 1: Initialize payment
            $paymentResult = $this->initializePaymentInternal($userId, $stackId);
            if (!$paymentResult['success']) {
                return $this->failValidationError($paymentResult['message']);
            }

            $paymentData = $paymentResult['data'];
            $transactionId = $paymentData['transaction_id'];

            // Debug: Log the data being passed
            log_message('info', 'Payment initialized successfully. Transaction ID: ' . $transactionId);
            log_message('info', 'User ID: ' . $userId . ', Stack ID: ' . $stackId);
            log_message('info', 'Predictions: ' . json_encode($predictions));

            // Step 2: Store predictions temporarily (pending payment)
            $predictionModel = new \App\Models\PredictionModel();
            
            try {
                $predictionId = $predictionModel->submitPredictionWithPayment(
                    $userId, 
                    $stackId, 
                    $predictions, 
                    $transactionId
                );
                
                log_message('info', 'Prediction stored successfully. Prediction ID: ' . $predictionId);
            } catch (\Exception $e) {
                log_message('error', 'Error storing prediction: ' . $e->getMessage());
                log_message('error', 'Stack trace: ' . $e->getTraceAsString());
                return $this->failServerError('Failed to store predictions: ' . $e->getMessage());
            }

            if (!$predictionId) {
                return $this->failServerError('Failed to store predictions');
            }

            return $this->respond([
                'status' => 'success',
                'message' => 'Payment initialized and predictions stored pending payment',
                'data' => [
                    'payment_id' => $paymentData['payment_id'],
                    'transaction_id' => $transactionId,
                    'prediction_id' => $predictionId,
                    'amount' => $paymentData['amount'],
                    'stack_title' => $paymentData['stack_title'],
                    'gmpay_payload' => $paymentData['gmpay_payload'],
                    'gmpay_url' => $paymentData['gmpay_url'],
                    'status' => 'pending_payment'
                ]
            ], 201);

        } catch (\Exception $e) {
            return $this->failServerError('Payment and prediction processing failed: ' . $e->getMessage());
        }
    }

    /**
     * Internal method to initialize payment (used by processPaymentWithPredictions)
     */
    private function initializePaymentInternal($userId, $stackId)
    {
        try {
            // Get user details for phone number
            $userModel = new \App\Models\UserModel();
            $user = $userModel->find($userId);
            if (!$user) {
                return ['success' => false, 'message' => 'User not found'];
            }

            // Format phone number for GMPay (add 256 prefix if not present)
            $phoneNumber = $user['phone'];
            if (!str_starts_with($phoneNumber, '256')) {
                // Remove leading 0 if present and add 256
                $phoneNumber = '256' . ltrim($phoneNumber, '0');
            }

            // Check if stack exists
            $stack = $this->stackModel->find($stackId);
            if (!$stack) {
                return ['success' => false, 'message' => 'Stack not found'];
            }

            // Check if stack is still active
            if (!$this->stackModel->isOpenForPredictions($stackId)) {
                return ['success' => false, 'message' => 'Stack is closed'];
            }

            // Generate 13-digit transaction ID for GMPay
            $transactionId = $this->generateGMPayTransactionId();

            // Create payment record
            $paymentId = $this->paymentModel->createPayment(
                $userId, 
                $stackId, 
                $stack['entry_fee'], 
                $transactionId
            );

            if ($paymentId) {
                // Prepare GMPay payload
                $gmpayPayload = [
                    'msisdn' => $phoneNumber,
                    'amount' => $stack['entry_fee'],
                    'transactionId' => $transactionId
                ];

                // Log the GMPay payload for debugging
                log_message('info', 'GMPay Payload: ' . json_encode($gmpayPayload));
                log_message('info', 'Phone Number (original): ' . $user['phone']);
                log_message('info', 'Phone Number (formatted): ' . $phoneNumber);
                log_message('info', 'Amount: ' . $stack['entry_fee']);
                log_message('info', 'Transaction ID: ' . $transactionId);

                // Send clean payload to GMPay (only the fields GMPay expects)
                $cleanGMPayPayload = [
                    'msisdn' => $phoneNumber,
                    'amount' => (string)$stack['entry_fee'], // Convert to string as GMPay expects
                    'transactionId' => $transactionId
                ];
                
                log_message('info', 'About to send to GMPay: ' . json_encode($cleanGMPayPayload));
                $gmpayResponse = $this->sendToGMPay($cleanGMPayPayload);
                log_message('info', 'GMPay Response: ' . json_encode($gmpayResponse));

                return [
                    'success' => true,
                    'data' => [
                        'payment_id' => $paymentId,
                        'transaction_id' => $transactionId,
                        'prediction_id' => null, // Will be set by the calling method
                        'amount' => $stack['entry_fee'],
                        'stack_title' => $stack['title'],
                        'gmpay_payload' => $cleanGMPayPayload, // Use the clean payload
                        'gmpay_url' => 'https://debit.gmpayapp.site/public/deposit/custom',
                        'gmpay_response' => $gmpayResponse
                    ]
                ];
            } else {
                return ['success' => false, 'message' => 'Failed to initialize payment'];
            }
        } catch (\Exception $e) {
            return ['success' => false, 'message' => 'Payment initialization failed: ' . $e->getMessage()];
        }
    }

    /**
     * Send payment request to GMPay
     */
    private function sendToGMPay($payload)
    {
        $url = 'https://debit.gmpayapp.site/public/deposit/custom';
        
        // Validate payload format
        if (!$this->validateGMPayPayload($payload)) {
            log_message('error', 'Invalid GMPay payload format: ' . json_encode($payload));
            return [
                'status_code' => 0,
                'response' => null,
                'success' => false,
                'error' => 'Invalid payload format'
            ];
        }
        
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
            log_message('error', 'GMPay API call failed: ' . $e->getMessage());
            return [
                'status_code' => 0,
                'response' => null,
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Validate GMPay payload format
     */
    private function validateGMPayPayload($payload)
    {
        // Check required fields
        if (!isset($payload['msisdn']) || !isset($payload['amount']) || !isset($payload['transactionId'])) {
            log_message('error', 'Missing required fields in GMPay payload');
            return false;
        }
        
        // Validate msisdn format (should be 256XXXXXXXX - exactly 12 digits)
        if (!preg_match('/^256\d{9}$/', $payload['msisdn'])) {
            log_message('error', 'Invalid MSISDN format: ' . $payload['msisdn'] . ' (should be 256XXXXXXXX)');
            return false;
        }
        
        // Validate amount (should be string and numeric)
        if (!is_string($payload['amount']) || !is_numeric($payload['amount']) || $payload['amount'] <= 0) {
            log_message('error', 'Invalid amount: ' . $payload['amount'] . ' (should be string and numeric)');
            return false;
        }
        
        // Validate transactionId (should be string and not empty)
        if (empty($payload['transactionId']) || !is_string($payload['transactionId'])) {
            log_message('error', 'Invalid transaction ID: ' . $payload['transactionId']);
            return false;
        }
        
        log_message('info', 'GMPay payload validation passed');
        return true;
    }
} 