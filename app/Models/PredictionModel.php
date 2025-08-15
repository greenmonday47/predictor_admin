<?php

namespace App\Models;

use CodeIgniter\Model;

class PredictionModel extends Model
{
    protected $table = 'predictions';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;
    protected $allowedFields = ['user_id', 'stack_id', 'predictions_json', 'transaction_id', 'payment_status'];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = false;
    protected $deletedField = false;

    // Validation
    protected $validationRules = [
        'user_id' => 'required|integer|is_natural_no_zero',
        'stack_id' => 'required|integer|is_natural_no_zero',
        'predictions_json' => 'required|valid_json',
        'transaction_id' => 'permit_empty|max_length[50]',
        'payment_status' => 'permit_empty|in_list[pending,paid,failed]',
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
        'predictions_json' => [
            'required' => 'Predictions data is required',
            'valid_json' => 'Predictions data must be valid JSON',
        ],
        'transaction_id' => [
            'max_length' => 'Transaction ID must not exceed 50 characters',
        ],
        'payment_status' => [
            'in_list' => 'Payment status must be pending, paid, or failed',
        ],
    ];

    protected $skipValidation = false;
    protected $cleanValidationRules = true;

    /**
     * Get user's prediction for a specific stack
     */
    public function getUserPrediction($userId, $stackId)
    {
        $prediction = $this->where('user_id', $userId)
                          ->where('stack_id', $stackId)
                          ->first();
        
        if ($prediction) {
            $prediction['predictions'] = json_decode($prediction['predictions_json'], true);
        }
        
        return $prediction;
    }

    /**
     * Check if user has already predicted for a stack
     */
    public function hasUserPredicted($userId, $stackId)
    {
        return $this->where('user_id', $userId)
                   ->where('stack_id', $stackId)
                   ->countAllResults() > 0;
    }

    /**
     * Get all predictions for a stack
     */
    public function getStackPredictions($stackId)
    {
        $predictions = $this->where('stack_id', $stackId)->findAll();
        
        foreach ($predictions as &$prediction) {
            $prediction['predictions'] = json_decode($prediction['predictions_json'], true);
        }
        
        return $predictions;
    }

    /**
     * Get user's predictions with stack details
     */
    public function getUserPredictionsWithStacks($userId)
    {
        $predictions = $this->select('predictions.*, stacks.title, stacks.prize_description, stacks.entry_fee, stacks.deadline, stacks.is_active, stacks.matches_json, stacks.actual_scores_json')
                   ->join('stacks', 'stacks.id = predictions.stack_id')
                   ->where('predictions.user_id', $userId)
                   ->orderBy('predictions.created_at', 'DESC')
                   ->findAll();

        // Process each prediction to include team names
        foreach ($predictions as &$prediction) {
            $prediction = $this->processPredictionWithTeamNames($prediction);
        }

        return $predictions;
    }

    /**
     * Process prediction to include team names instead of match IDs
     */
    private function processPredictionWithTeamNames($prediction)
    {
        // Decode predictions and matches
        $userPredictions = json_decode($prediction['predictions_json'], true);
        $stackMatches = json_decode($prediction['matches_json'], true);
        $actualScores = $prediction['actual_scores_json'] ? 
            json_decode($prediction['actual_scores_json'], true) : null;

        // Create a map of match_id to match details
        $matchMap = [];
        if ($stackMatches) {
            foreach ($stackMatches as $match) {
                $matchMap[$match['match_id']] = $match;
            }
        }

        // Process user predictions to include team names
        $processedPredictions = [];
        if ($userPredictions) {
            foreach ($userPredictions as $pred) {
                $matchId = $pred['match_id'];
                $matchDetails = $matchMap[$matchId] ?? null;
                
                if ($matchDetails) {
                    $processedPred = [
                        'match_id' => $matchId,
                        'home_team' => $matchDetails['home_team'],
                        'away_team' => $matchDetails['away_team'],
                        'home_score' => $pred['home_score'],
                        'away_score' => $pred['away_score'],
                        'match_time' => $matchDetails['match_time'] ?? null,
                        'match_date' => $matchDetails['match_date'] ?? null,
                    ];

                    // Add actual scores if available
                    if ($actualScores) {
                        foreach ($actualScores as $actualScore) {
                            if ($actualScore['match_id'] === $matchId) {
                                $processedPred['actual_home_score'] = $actualScore['home_score'];
                                $processedPred['actual_away_score'] = $actualScore['away_score'];
                                break;
                            }
                        }
                    }

                    $processedPredictions[] = $processedPred;
                }
            }
        }

        // Replace the predictions_json with processed predictions
        $prediction['predictions'] = $processedPredictions;
        
        // Remove the raw JSON fields to avoid confusion
        unset($prediction['predictions_json']);
        unset($prediction['matches_json']);
        unset($prediction['actual_scores_json']);

        return $prediction;
    }

    /**
     * Validate predictions JSON structure
     */
    public function validatePredictionsStructure($predictions)
    {
        if (!is_array($predictions)) {
            return false;
        }

        foreach ($predictions as $prediction) {
            if (!isset($prediction['match_id']) || 
                !isset($prediction['home_score']) || 
                !isset($prediction['away_score'])) {
                return false;
            }
            
            // Validate scores are integers
            if (!is_numeric($prediction['home_score']) || 
                !is_numeric($prediction['away_score']) ||
                $prediction['home_score'] < 0 || 
                $prediction['away_score'] < 0) {
                return false;
            }
        }

        return true;
    }

    /**
     * Submit prediction for a user (allows multiple predictions)
     */
    public function submitPrediction($userId, $stackId, $predictions)
    {
        // Allow multiple predictions per user for increased chances of winning
        return $this->insert([
            'user_id' => $userId,
            'stack_id' => $stackId,
            'predictions_json' => json_encode($predictions),
            'payment_status' => 'paid' // Default to paid for backward compatibility
        ]);
    }

    /**
     * Submit prediction with payment transaction ID (pending payment)
     */
    public function submitPredictionWithPayment($userId, $stackId, $predictions, $transactionId)
    {
        // Use Query Builder directly to avoid Model issues
        $db = \Config\Database::connect();
        
        $data = [
            'user_id' => $userId,
            'stack_id' => $stackId,
            'predictions_json' => json_encode($predictions),
            'transaction_id' => $transactionId,
            'payment_status' => 'pending',
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        // Debug: Log the data being inserted
        log_message('info', 'Attempting to insert prediction with data: ' . json_encode($data));
        
        try {
            $result = $db->table('predictions')->insert($data);
            
            if ($result) {
                $insertId = $db->insertID();
                log_message('info', 'Prediction inserted successfully. Insert ID: ' . $insertId);
                return $insertId;
            } else {
                log_message('error', 'Failed to insert prediction. No result returned.');
                return false;
            }
        } catch (\Exception $e) {
            log_message('error', 'Exception during prediction insert: ' . $e->getMessage());
            log_message('error', 'Stack trace: ' . $e->getTraceAsString());
            throw $e;
        }
    }

    /**
     * Update prediction payment status when payment is confirmed
     */
    public function updatePaymentStatus($transactionId, $status)
    {
        // Use direct database update to avoid escape array issues
        return $this->db->table($this->table)
                       ->where('transaction_id', $transactionId)
                       ->update(['payment_status' => $status]);
    }

    /**
     * Get predictions by transaction ID
     */
    public function getPredictionsByTransaction($transactionId)
    {
        return $this->where('transaction_id', $transactionId)->findAll();
    }
} 