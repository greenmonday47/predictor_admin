<?php

namespace App\Controllers;

use App\Models\TopUpTransactionModel;
use App\Models\WalletModel;
use CodeIgniter\RESTful\ResourceController;
use CodeIgniter\API\ResponseTrait;

class CronController extends ResourceController
{
    use ResponseTrait;

    protected $topUpTransactionModel;
    protected $walletModel;

    public function __construct()
    {
        $this->topUpTransactionModel = new TopUpTransactionModel();
        $this->walletModel = new WalletModel();
    }

    /**
     * Check pending top-up transactions (cron job endpoint)
     * This can be called via URL: https://yourdomain.com/cron/check-pending-topups
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

            // Log the results
            log_message('info', "Cron job processed $processed top-ups: $successful successful, $failed failed");

            return $this->respond([
                'status' => 'success',
                'message' => 'Pending top-ups processed',
                'data' => [
                    'processed' => $processed,
                    'successful' => $successful,
                    'failed' => $failed,
                    'timestamp' => date('Y-m-d H:i:s')
                ]
            ]);
        } catch (\Exception $e) {
            log_message('error', 'Cron job failed: ' . $e->getMessage());
            return $this->failServerError('Failed to process pending top-ups: ' . $e->getMessage());
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
     * Health check endpoint for cron monitoring
     */
    public function healthCheck()
    {
        return $this->respond([
            'status' => 'success',
            'message' => 'Cron service is running',
            'timestamp' => date('Y-m-d H:i:s'),
            'server_time' => time()
        ]);
    }
} 