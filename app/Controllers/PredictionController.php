<?php

namespace App\Controllers;

use App\Models\PredictionModel;
use App\Models\StackModel;
use App\Models\PaymentModel;
use App\Models\ScoreModel;
class PredictionController extends BaseController
{
    protected $format = 'json';

    public function __construct()
    {
        $this->predictionModel = new PredictionModel();
        $this->stackModel = new StackModel();
        $this->paymentModel = new PaymentModel();
        $this->scoreModel = new ScoreModel();
    }

    /**
     * Submit prediction for a stack
     */
    public function submit()
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

        // Validate predictions format
        if (!is_array($predictions)) {
            return $this->failValidationError('Predictions must be an array');
        }

        try {
            // Check if stack exists and is open
            $stack = $this->stackModel->find($stackId);
            if (!$stack) {
                return $this->failNotFound('Stack not found');
            }

            if (!$this->stackModel->isOpenForPredictions($stackId)) {
                return $this->failValidationError('Stack is closed for predictions');
            }

            // Allow multiple predictions per user (increased chances of winning)
            // Check if user has already predicted (for informational purposes only)
            $hasPredicted = $this->predictionModel->hasUserPredicted($userId, $stackId);
            if ($hasPredicted) {
                log_message('info', "User $userId submitting additional prediction for stack $stackId");
            }

            // Check if payment is required and user has paid
            if ($stack['entry_fee'] > 0) {
                if (!$this->paymentModel->hasUserPaid($userId, $stackId)) {
                    return $this->failValidationError('Payment required before prediction');
                }
            }

            // Validate predictions structure
            if (!$this->predictionModel->validatePredictionsStructure($predictions)) {
                return $this->failValidationError('Invalid predictions format');
            }

            // Validate that predictions match stack matches
            $stackMatches = json_decode($stack['matches_json'], true);
            if (!$this->validatePredictionsAgainstMatches($predictions, $stackMatches)) {
                return $this->failValidationError('Predictions do not match stack matches');
            }

            // Validate that no matches have already started
            if (!$this->validateMatchTimes($stackMatches)) {
                return $this->failValidationError('Cannot submit predictions for matches that have already started');
            }

            // Submit prediction
            $predictionId = $this->predictionModel->submitPrediction($userId, $stackId, $predictions);

            if ($predictionId) {
                return $this->respond([
                    'status' => 'success',
                    'message' => 'Prediction submitted successfully',
                    'data' => [
                        'prediction_id' => $predictionId,
                        'stack_id' => $stackId,
                        'user_id' => $userId
                    ]
                ], 201);
            } else {
                return $this->failServerError('Failed to submit prediction');
            }
        } catch (\Exception $e) {
            return $this->failServerError('Prediction submission failed: ' . $e->getMessage());
        }
    }

    /**
     * Get user's prediction for a stack
     */
    public function getUserPrediction($userId = null, $stackId = null)
    {
        if (!$userId || !$stackId) {
            return $this->failValidationError('User ID and Stack ID required');
        }

        try {
            $prediction = $this->predictionModel->getUserPrediction($userId, $stackId);
            
            if ($prediction) {
                return $this->respond([
                    'status' => 'success',
                    'data' => $prediction
                ]);
            } else {
                return $this->failNotFound('Prediction not found');
            }
        } catch (\Exception $e) {
            return $this->failServerError('Failed to fetch prediction: ' . $e->getMessage());
        }
    }

    /**
     * Get all predictions for a stack (admin function)
     */
    public function getStackPredictions($stackId = null)
    {
        if (!$stackId) {
            return $this->failValidationError('Stack ID required');
        }

        try {
            $predictions = $this->predictionModel->getStackPredictions($stackId);
            
            return $this->respond([
                'status' => 'success',
                'data' => $predictions
            ]);
        } catch (\Exception $e) {
            return $this->failServerError('Failed to fetch predictions: ' . $e->getMessage());
        }
    }

    /**
     * Get all predictions for current user with stack details
     */
    public function getAllUserPredictions()
    {
        try {
            // Get user ID from query parameter (for Flutter app)
            $userId = $this->request->getGet('user_id');
            
            if (!$userId || !is_numeric($userId)) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'User ID required'
                ])->setStatusCode(400);
            }

            $userId = (int) $userId;
            $predictions = $this->predictionModel->getUserPredictionsWithStacks($userId);
            
            // Add scores for closed stacks
            foreach ($predictions as &$prediction) {
                if (!$prediction['is_active']) {
                    $score = $this->scoreModel->getUserScore($userId, $prediction['stack_id']);
                    if ($score) {
                        $score['ranking'] = $this->scoreModel->getUserRanking($userId, $prediction['stack_id']);
                        $prediction['score'] = $score;
                    }
                }
            }

            return $this->respond([
                'status' => 'success',
                'data' => $predictions
            ]);
        } catch (\Exception $e) {
            return $this->failServerError('Failed to fetch predictions: ' . $e->getMessage());
        }
    }

    /**
     * Get user's prediction history
     */
    public function getUserPredictions($userId = null)
    {
        if (!$userId) {
            return $this->failValidationError('User ID required');
        }

        try {
            $predictions = $this->predictionModel->getUserPredictionsWithStacks($userId);
            
            // Add scores for closed stacks
            foreach ($predictions as &$prediction) {
                if (!$prediction['is_active']) {
                    $score = $this->scoreModel->getUserScore($userId, $prediction['stack_id']);
                    if ($score) {
                        $score['ranking'] = $this->scoreModel->getUserRanking($userId, $prediction['stack_id']);
                        $prediction['score'] = $score;
                    }
                }
            }

            return $this->respond([
                'status' => 'success',
                'data' => $predictions
            ]);
        } catch (\Exception $e) {
            return $this->failServerError('Failed to fetch predictions: ' . $e->getMessage());
        }
    }

    /**
     * Validate predictions against stack matches
     */
    private function validatePredictionsAgainstMatches($predictions, $matches)
    {
        if (count($predictions) !== count($matches)) {
            return false;
        }

        $matchIds = array_column($matches, 'match_id');
        $predictionMatchIds = array_column($predictions, 'match_id');

        // Check if all match IDs in predictions exist in matches
        foreach ($predictionMatchIds as $matchId) {
            if (!in_array($matchId, $matchIds)) {
                return false;
            }
        }

        // Check if all matches have predictions
        foreach ($matchIds as $matchId) {
            if (!in_array($matchId, $predictionMatchIds)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Validate that no matches have already started
     */
    private function validateMatchTimes($matches)
    {
        $currentTime = time();
        
        foreach ($matches as $match) {
            if (!isset($match['match_time'])) {
                continue; // Skip if no match time is set
            }
            
            $matchTime = strtotime($match['match_time']);
            if ($matchTime === false) {
                continue; // Skip if invalid date format
            }
            
            // Block predictions if match has already started
            if ($matchTime <= $currentTime) {
                return false;
            }
        }
        
        return true;
    }

    /**
     * Calculate scores for a stack (admin function)
     */
    public function calculateScores($stackId = null)
    {
        if (!$stackId) {
            return $this->failValidationError('Stack ID required');
        }

        try {
            $stack = $this->stackModel->find($stackId);
            if (!$stack) {
                return $this->failNotFound('Stack not found');
            }

            if (!$stack['actual_scores_json']) {
                return $this->failValidationError('Actual scores not available for this stack');
            }

            $actualScores = json_decode($stack['actual_scores_json'], true);
            $predictions = $this->predictionModel->getStackPredictions($stackId);

            $calculatedCount = 0;
            foreach ($predictions as $prediction) {
                $userPredictions = json_decode($prediction['predictions_json'], true);
                $this->scoreModel->calculateScore(
                    $prediction['user_id'], 
                    $stackId, 
                    $userPredictions, 
                    $actualScores
                );
                $calculatedCount++;
            }

            return $this->respond([
                'status' => 'success',
                'message' => "Scores calculated for {$calculatedCount} predictions",
                'data' => [
                    'stack_id' => $stackId,
                    'predictions_processed' => $calculatedCount
                ]
            ]);
        } catch (\Exception $e) {
            return $this->failServerError('Score calculation failed: ' . $e->getMessage());
        }
    }
} 