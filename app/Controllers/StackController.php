<?php

namespace App\Controllers;

use App\Models\StackModel;
use App\Models\PaymentModel;
use App\Models\PredictionModel;
use App\Models\ScoreModel;
use CodeIgniter\RESTful\ResourceController;

class StackController extends ResourceController
{
    protected $format = 'json';

    public function __construct()
    {
        $this->stackModel = new StackModel();
        $this->paymentModel = new PaymentModel();
        $this->predictionModel = new PredictionModel();
        $this->scoreModel = new ScoreModel();
    }

    /**
     * Get all active stacks
     */
    public function index()
    {
        try {
            $stacks = $this->stackModel->getActiveStacksForUsers();
            
            // Add additional info for each stack
            foreach ($stacks as &$stack) {
                $stack['matches'] = json_decode($stack['matches_json'], true);
                $stack['participant_count'] = $this->predictionModel->where('stack_id', $stack['id'])->countAllResults();
                $stack['time_remaining'] = $this->getTimeRemaining($stack['deadline']);
                
                // Ensure is_active is included in response
                $stack['is_active'] = (bool)$stack['is_active'];
                
                // Add security information
                $stack['is_locked_due_to_scores'] = $this->stackModel->isLockedDueToScores($stack['id']);
                
                // Add prize image URL if exists
                if (!empty($stack['prize_image'])) {
                    $stack['prize_image_url'] = base_url('admin/uploads/' . $stack['prize_image']);
                }
            }

            return $this->respond([
                'status' => 'success',
                'data' => $stacks,
                'debug_info' => [
                    'total_stacks_found' => count($stacks),
                    'current_time' => date('Y-m-d H:i:s'),
                    'server_timezone' => date_default_timezone_get()
                ]
            ]);
        } catch (\Exception $e) {
            return $this->failServerError('Failed to fetch stacks: ' . $e->getMessage());
        }
    }

    /**
     * Get specific stack details
     */
    public function show($id = null)
    {
        if (!$id) {
            return $this->failValidationError('Stack ID required');
        }

        try {
            $stack = $this->stackModel->getStackWithMatches($id);
            
            if (!$stack) {
                return $this->failNotFound('Stack not found');
            }

            // Add additional information
            $stack['participant_count'] = $this->predictionModel->where('stack_id', $id)->countAllResults();
            $stack['time_remaining'] = $this->getTimeRemaining($stack['deadline']);
            $stack['is_open'] = $this->stackModel->isOpenForPredictions($id);
            $stack['is_locked_due_to_scores'] = $this->stackModel->isLockedDueToScores($id);
            
            // Add prize image URL if exists
            if (!empty($stack['prize_image'])) {
                $stack['prize_image_url'] = base_url('admin/uploads/' . $stack['prize_image']);
            }

            return $this->respond([
                'status' => 'success',
                'data' => $stack
            ]);
        } catch (\Exception $e) {
            return $this->failServerError('Failed to fetch stack: ' . $e->getMessage());
        }
    }

    /**
     * Get stack with user's prediction status
     */
    public function showWithUserStatus($id = null, $userId = null)
    {
        if (!$id || !$userId) {
            return $this->failValidationError('Stack ID and User ID required');
        }

        try {
            $stack = $this->stackModel->getStackWithMatches($id);
            
            if (!$stack) {
                return $this->failNotFound('Stack not found');
            }

            // Check if user has paid
            $hasPaid = $this->paymentModel->hasUserPaid($userId, $id);
            
            // Check if user has predicted
            $hasPredicted = $this->predictionModel->hasUserPredicted($userId, $id);
            
            // Get user's prediction if exists
            $userPrediction = null;
            if ($hasPredicted) {
                $userPrediction = $this->predictionModel->getUserPrediction($userId, $id);
            }

            // Get user's score if stack is closed
            $userScore = null;
            if (!$stack['is_active'] && $hasPredicted) {
                $userScore = $this->scoreModel->getUserScore($userId, $id);
                if ($userScore) {
                    $userScore['ranking'] = $this->scoreModel->getUserRanking($userId, $id);
                }
            }

            $stack['user_status'] = [
                'has_paid' => $hasPaid,
                'has_predicted' => $hasPredicted,
                'prediction' => $userPrediction,
                'score' => $userScore
            ];

            return $this->respond([
                'status' => 'success',
                'data' => $stack
            ]);
        } catch (\Exception $e) {
            return $this->failServerError('Failed to fetch stack: ' . $e->getMessage());
        }
    }

    /**
     * Get stack leaderboard
     */
    public function leaderboard($id = null)
    {
        if (!$id) {
            return $this->failValidationError('Stack ID required');
        }

        try {
            $stack = $this->stackModel->find($id);
            if (!$stack) {
                return $this->failNotFound('Stack not found');
            }

            $leaderboard = $this->scoreModel->getLeaderboard($id, 50);

            return $this->respond([
                'status' => 'success',
                'data' => [
                    'stack' => [
                        'id' => $stack['id'],
                        'title' => $stack['title'],
                        'is_active' => $stack['is_active']
                    ],
                    'leaderboard' => $leaderboard
                ]
            ]);
        } catch (\Exception $e) {
            return $this->failServerError('Failed to fetch leaderboard: ' . $e->getMessage());
        }
    }

    /**
     * Get user's stacks (with predictions)
     */
    public function userStacks($userId = null)
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
            return $this->failServerError('Failed to fetch user stacks: ' . $e->getMessage());
        }
    }

    /**
     * Calculate time remaining until deadline
     */
    private function getTimeRemaining($deadline)
    {
        $deadlineTime = strtotime($deadline);
        $currentTime = time();
        $timeRemaining = $deadlineTime - $currentTime;

        if ($timeRemaining <= 0) {
            return 'Expired';
        }

        $days = floor($timeRemaining / 86400);
        $hours = floor(($timeRemaining % 86400) / 3600);
        $minutes = floor(($timeRemaining % 3600) / 60);

        if ($days > 0) {
            return "{$days}d {$hours}h {$minutes}m";
        } elseif ($hours > 0) {
            return "{$hours}h {$minutes}m";
        } else {
            return "{$minutes}m";
        }
    }
} 