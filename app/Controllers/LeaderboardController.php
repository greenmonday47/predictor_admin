<?php

namespace App\Controllers;

use App\Models\ScoreModel;
use App\Models\UserModel;
use App\Models\WinnerModel;

class LeaderboardController extends BaseController
{
    protected $scoreModel;
    protected $userModel;
    protected $winnerModel;

    public function __construct()
    {
        $this->scoreModel = new ScoreModel();
        $this->userModel = new UserModel();
        $this->winnerModel = new WinnerModel();
    }

    /**
     * Get global leaderboard with aggregated points
     */
    public function getGlobalLeaderboard()
    {
        try {
            $leaderboard = $this->scoreModel->getAggregatedLeaderboard();
            
            return $this->respond([
                'status' => 'success',
                'message' => 'Leaderboard fetched successfully',
                'data' => $leaderboard
            ]);
        } catch (\Exception $e) {
            return $this->failServerError('Failed to fetch leaderboard: ' . $e->getMessage());
        }
    }

    /**
     * Get stack-specific leaderboard
     */
    public function getStackLeaderboard($stackId = null)
    {
        try {
            if (!$stackId) {
                return $this->failValidationError('Stack ID is required');
            }

            $leaderboard = $this->scoreModel->getLeaderboard($stackId, 50);
            
            return $this->respond([
                'status' => 'success',
                'message' => 'Stack leaderboard fetched successfully',
                'data' => $leaderboard
            ]);
        } catch (\Exception $e) {
            return $this->failServerError('Failed to fetch stack leaderboard: ' . $e->getMessage());
        }
    }

    /**
     * Get user's global ranking
     */
    public function getUserGlobalRanking($userId = null)
    {
        try {
            if (!$userId) {
                $userId = $this->getCurrentUserId();
                if (!$userId) {
                    return $this->failUnauthorized('User not authenticated');
                }
            }

            $ranking = $this->scoreModel->getUserGlobalRanking($userId);
            
            return $this->respond([
                'status' => 'success',
                'message' => 'User ranking fetched successfully',
                'data' => $ranking
            ]);
        } catch (\Exception $e) {
            return $this->failServerError('Failed to fetch user ranking: ' . $e->getMessage());
        }
    }

    /**
     * Get user's stack ranking
     */
    public function getUserStackRanking($userId = null, $stackId = null)
    {
        try {
            if (!$userId) {
                $userId = $this->getCurrentUserId();
                if (!$userId) {
                    return $this->failUnauthorized('User not authenticated');
                }
            }

            if (!$stackId) {
                return $this->failValidationError('Stack ID is required');
            }

            $ranking = $this->scoreModel->getUserRanking($userId, $stackId);
            
            return $this->respond([
                'status' => 'success',
                'message' => 'User stack ranking fetched successfully',
                'data' => [
                    'user_id' => $userId,
                    'stack_id' => $stackId,
                    'ranking' => $ranking
                ]
            ]);
        } catch (\Exception $e) {
            return $this->failServerError('Failed to fetch user stack ranking: ' . $e->getMessage());
        }
    }

    /**
     * Get top performers (users with most wins)
     */
    public function getTopPerformers()
    {
        try {
            $topPerformers = $this->winnerModel->getTopPerformers();
            
            return $this->respond([
                'status' => 'success',
                'message' => 'Top performers fetched successfully',
                'data' => $topPerformers
            ]);
        } catch (\Exception $e) {
            return $this->failServerError('Failed to fetch top performers: ' . $e->getMessage());
        }
    }

    /**
     * Get leaderboard statistics
     */
    public function getLeaderboardStats()
    {
        try {
            $stats = [
                'total_users' => $this->userModel->countAll(),
                'total_stacks' => $this->scoreModel->db->table('stacks')->countAllResults(),
                'total_predictions' => $this->scoreModel->db->table('predictions')->countAllResults(),
                'total_winners' => $this->winnerModel->countAll(),
                'highest_score' => $this->scoreModel->getHighestScore(),
                'average_score' => $this->scoreModel->getAverageScore()
            ];
            
            return $this->respond([
                'status' => 'success',
                'message' => 'Leaderboard statistics fetched successfully',
                'data' => $stats
            ]);
        } catch (\Exception $e) {
            return $this->failServerError('Failed to fetch leaderboard statistics: ' . $e->getMessage());
        }
    }
} 