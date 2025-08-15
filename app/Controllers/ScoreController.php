<?php

namespace App\Controllers;

use App\Models\ScoreModel;
use App\Models\StackModel;
use App\Models\PredictionModel;
use App\Models\WinnerModel;
class ScoreController extends BaseController
{
    protected $format = 'json';

    public function __construct()
    {
        $this->scoreModel = new ScoreModel();
        $this->stackModel = new StackModel();
        $this->predictionModel = new PredictionModel();
        $this->winnerModel = new WinnerModel();
    }

    /**
     * Get leaderboard for a stack
     */
    public function leaderboard($stackId = null)
    {
        if (!$stackId) {
            return $this->failValidationError('Stack ID required');
        }

        try {
            $stack = $this->stackModel->find($stackId);
            if (!$stack) {
                return $this->failNotFound('Stack not found');
            }

            $leaderboard = $this->scoreModel->getLeaderboard($stackId, 50);

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
     * Get user's score for a stack
     */
    public function getUserScore($userId = null, $stackId = null)
    {
        if (!$userId || !$stackId) {
            return $this->failValidationError('User ID and Stack ID required');
        }

        try {
            $score = $this->scoreModel->getUserScore($userId, $stackId);
            
            if ($score) {
                $score['ranking'] = $this->scoreModel->getUserRanking($userId, $stackId);
                $score['has_perfect_score'] = $this->scoreModel->hasPerfectScore($userId, $stackId);
                
                return $this->respond([
                    'status' => 'success',
                    'data' => $score
                ]);
            } else {
                return $this->failNotFound('Score not found');
            }
        } catch (\Exception $e) {
            return $this->failServerError('Failed to fetch score: ' . $e->getMessage());
        }
    }

    /**
     * Get all scores for a stack (admin function)
     */
    public function getStackScores($stackId = null)
    {
        if (!$stackId) {
            return $this->failValidationError('Stack ID required');
        }

        try {
            $scores = $this->scoreModel->getStackScores($stackId);
            
            $stats = [
                'total_participants' => count($scores),
                'perfect_scores' => 0,
                'average_points' => 0,
                'highest_score' => 0,
                'lowest_score' => 999999
            ];

            $totalPoints = 0;
            foreach ($scores as $score) {
                $totalPoints += $score['total_points'];
                
                if ($score['total_points'] > $stats['highest_score']) {
                    $stats['highest_score'] = $score['total_points'];
                }
                
                if ($score['total_points'] < $stats['lowest_score']) {
                    $stats['lowest_score'] = $score['total_points'];
                }
                
                if ($score['wrong_count'] == 0 && $score['exact_count'] > 0) {
                    $stats['perfect_scores']++;
                }
            }

            if (count($scores) > 0) {
                $stats['average_points'] = round($totalPoints / count($scores), 2);
            }

            return $this->respond([
                'status' => 'success',
                'data' => [
                    'scores' => $scores,
                    'statistics' => $stats
                ]
            ]);
        } catch (\Exception $e) {
            return $this->failServerError('Failed to fetch scores: ' . $e->getMessage());
        }
    }

    /**
     * Calculate scores for all predictions in a stack
     * UPDATED: Now properly handles multiple predictions per user
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
            
            // Get all unique users who have made predictions for this stack
            $predictionModel = new \App\Models\PredictionModel();
            $uniqueUsers = $predictionModel->select('DISTINCT user_id')
                                         ->where('stack_id', $stackId)
                                         ->findAll();

            $calculatedCount = 0;
            foreach ($uniqueUsers as $user) {
                $userId = $user['user_id'];
                
                // Calculate scores for ALL predictions of this user in this stack
                $this->scoreModel->calculateUserScoresForStack($userId, $stackId, $actualScores);
                $calculatedCount++;
            }

            return $this->respond([
                'status' => 'success',
                'message' => "Scores calculated for {$calculatedCount} users (including multiple predictions)",
                'data' => [
                    'stack_id' => $stackId,
                    'users_processed' => $calculatedCount,
                    'note' => 'Multiple predictions per user are now properly accumulated'
                ]
            ]);
        } catch (\Exception $e) {
            return $this->failServerError('Score calculation failed: ' . $e->getMessage());
        }
    }

    /**
     * Award winners for a stack
     */
    public function awardWinners($stackId = null)
    {
        if (!$stackId) {
            return $this->failValidationError('Stack ID required');
        }

        try {
            $stack = $this->stackModel->find($stackId);
            if (!$stack) {
                return $this->failNotFound('Stack not found');
            }

            if ($stack['is_active']) {
                return $this->failValidationError('Stack is still active. Cannot award winners yet.');
            }

            // Award perfect score winners first
            $perfectWinners = $this->winnerModel->awardPerfectScoreWinners($stackId);
            
            // Award top score winners (excluding perfect score winners)
            $topScoreWinners = $this->winnerModel->awardTopScoreWinners($stackId, 3);

            $totalWinners = $perfectWinners + $topScoreWinners;

            return $this->respond([
                'status' => 'success',
                'message' => "Winners awarded successfully",
                'data' => [
                    'stack_id' => $stackId,
                    'perfect_winners' => $perfectWinners,
                    'top_score_winners' => $topScoreWinners,
                    'total_winners' => $totalWinners
                ]
            ]);
        } catch (\Exception $e) {
            return $this->failServerError('Winner awarding failed: ' . $e->getMessage());
        }
    }

    /**
     * Get winners for a stack
     */
    public function getWinners($stackId = null)
    {
        if (!$stackId) {
            return $this->failValidationError('Stack ID required');
        }

        try {
            $winners = $this->winnerModel->getStackWinners($stackId);
            $stats = $this->winnerModel->getWinnerStats($stackId);
            
            return $this->respond([
                'status' => 'success',
                'data' => [
                    'winners' => $winners,
                    'statistics' => $stats
                ]
            ]);
        } catch (\Exception $e) {
            return $this->failServerError('Failed to fetch winners: ' . $e->getMessage());
        }
    }

    /**
     * Get user's ranking in a stack
     */
    public function getUserRanking($userId = null, $stackId = null)
    {
        if (!$userId || !$stackId) {
            return $this->failValidationError('User ID and Stack ID required');
        }

        try {
            $ranking = $this->scoreModel->getUserRanking($userId, $stackId);
            
            if ($ranking !== null) {
                return $this->respond([
                    'status' => 'success',
                    'data' => [
                        'user_id' => $userId,
                        'stack_id' => $stackId,
                        'ranking' => $ranking
                    ]
                ]);
            } else {
                return $this->failNotFound('User has no score for this stack');
            }
        } catch (\Exception $e) {
            return $this->failServerError('Failed to get ranking: ' . $e->getMessage());
        }
    }

    /**
     * Get global leaderboard
     */
    public function globalLeaderboard()
    {
        try {
            $leaderboard = $this->scoreModel->getGlobalLeaderboard();
            
            return $this->respond([
                'status' => 'success',
                'data' => $leaderboard
            ]);
        } catch (\Exception $e) {
            return $this->failServerError('Failed to fetch global leaderboard: ' . $e->getMessage());
        }
    }

    /**
     * Get global winners
     */
    public function globalWinners()
    {
        try {
            $winners = $this->winnerModel->getAllWinners();
            
            return $this->respond([
                'status' => 'success',
                'data' => $winners
            ]);
        } catch (\Exception $e) {
            return $this->failServerError('Failed to fetch global winners: ' . $e->getMessage());
        }
    }

    /**
     * Get user metrics for current user
     */
    public function getUserMetrics()
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

            $scores = $this->scoreModel->where('user_id', $userId)->findAll();
            $wins = $this->winnerModel->getUserWins($userId);
            
            $stats = [
                'total_plays' => count($scores),
                'exact' => 0,
                'outcome' => 0,
                'wrong' => 0,
                'total_points' => 0,
                'accuracy' => 0
            ];

            $totalPredictions = 0;
            foreach ($scores as $score) {
                $stats['total_points'] += $score['total_points'];
                $stats['exact'] += $score['exact_count'];
                $stats['outcome'] += $score['outcome_count'];
                $stats['wrong'] += $score['wrong_count'];
                $totalPredictions += ($score['exact_count'] + $score['outcome_count'] + $score['wrong_count']);
            }

            // Calculate accuracy percentage
            if ($totalPredictions > 0) {
                $correctPredictions = $stats['exact'] + $stats['outcome'];
                $stats['accuracy'] = round(($correctPredictions / $totalPredictions) * 100, 1);
            }

            return $this->respond([
                'status' => 'success',
                'data' => $stats
            ]);
        } catch (\Exception $e) {
            return $this->failServerError('Failed to get user metrics: ' . $e->getMessage());
        }
    }

    /**
     * Get user's overall statistics
     */
    public function getUserStats($userId = null)
    {
        if (!$userId) {
            return $this->failValidationError('User ID required');
        }

        try {
            $scoringStats = $this->scoreModel->getUserScoringStats($userId);
            $wins = $this->winnerModel->getUserWins($userId);
            
            $stats = [
                'total_stacks_played' => $scoringStats['total_stacks_played'],
                'total_points' => $scoringStats['total_points'],
                'total_exact_predictions' => $scoringStats['total_exact_predictions'],
                'total_outcome_predictions' => $scoringStats['total_outcome_predictions'],
                'total_wrong_predictions' => $scoringStats['total_wrong_predictions'],
                'average_points_per_stack' => $scoringStats['average_points_per_stack'],
                'perfect_stacks' => $scoringStats['perfect_stacks'],
                'total_wins' => count($wins),
                'perfect_wins' => 0,
                'top_score_wins' => 0
            ];

            foreach ($wins as $win) {
                if ($win['win_type'] == 'full-correct') {
                    $stats['perfect_wins']++;
                } else {
                    $stats['top_score_wins']++;
                }
            }

            return $this->respond([
                'status' => 'success',
                'data' => $stats
            ]);
        } catch (\Exception $e) {
            return $this->failServerError('Failed to get user stats: ' . $e->getMessage());
        }
    }

    /**
     * Get detailed scoring breakdown for a user's predictions in a specific stack
     * UPDATED: Now shows ALL predictions for the user, not just one
     */
    public function getDetailedScoring($userId = null, $stackId = null)
    {
        if (!$userId || !$stackId) {
            return $this->failValidationError('User ID and Stack ID required');
        }

        try {
            // Get ALL predictions for this user in this stack
            $predictionModel = new \App\Models\PredictionModel();
            $userPredictions = $predictionModel->where('user_id', $userId)
                                             ->where('stack_id', $stackId)
                                             ->findAll();
            
            if (empty($userPredictions)) {
                return $this->failNotFound('No predictions found for this user and stack');
            }

            // Get actual scores for this stack
            $stack = $this->stackModel->find($stackId);
            if (!$stack || !$stack['actual_scores_json']) {
                return $this->failNotFound('Actual scores not available for this stack');
            }

            $actualScores = json_decode($stack['actual_scores_json'], true);
            
            // Process ALL predictions and accumulate detailed scoring
            $allDetailedScores = [];
            $totalPoints = 0;
            $totalExactCount = 0;
            $totalOutcomeCount = 0;
            $totalWrongCount = 0;
            $predictionCount = 0;

            foreach ($userPredictions as $predictionRecord) {
                $predictionCount++;
                $predictions = json_decode($predictionRecord['predictions_json'], true);
                
                foreach ($predictions as $prediction) {
                    $matchId = $prediction['match_id'];
                    $predictedHome = (int) $prediction['home_score'];
                    $predictedAway = (int) $prediction['away_score'];

                    // Find corresponding actual score
                    $actualScore = null;
                    foreach ($actualScores as $score) {
                        if ($score['match_id'] == $matchId) {
                            $actualScore = $score;
                            break;
                        }
                    }

                    $matchScore = [
                        'match_id' => $matchId,
                        'prediction_number' => $predictionCount,
                        'predicted_score' => $predictedHome . '-' . $predictedAway,
                        'actual_score' => $actualScore ? $actualScore['home_score'] . '-' . $actualScore['away_score'] : 'Not available',
                        'points' => 0,
                        'result_type' => 'wrong',
                        'explanation' => 'Match not found or not scored'
                    ];

                    if ($actualScore) {
                        $actualHome = (int) $actualScore['home_score'];
                        $actualAway = (int) $actualScore['away_score'];

                        // Calculate outcome (win/lose/draw)
                        $predictedOutcome = $this->getOutcome($predictedHome, $predictedAway);
                        $actualOutcome = $this->getOutcome($actualHome, $actualAway);

                        if ($predictedHome == $actualHome && $predictedAway == $actualAway) {
                            // Exact score match - 3 points (Actual)
                            $matchScore['points'] = 3;
                            $matchScore['result_type'] = 'actual';
                            $matchScore['explanation'] = 'Exact score match!';
                            $totalExactCount++;
                            $totalPoints += 3;
                        } elseif ($predictedOutcome == $actualOutcome) {
                            // Correct outcome but not exact score - 1 point (Correct)
                            $matchScore['points'] = 1;
                            $matchScore['result_type'] = 'correct';
                            $matchScore['explanation'] = 'Correct outcome (win/lose/draw)';
                            $totalOutcomeCount++;
                            $totalPoints += 1;
                        } else {
                            // Wrong outcome - 0 points (Wrong)
                            $matchScore['points'] = 0;
                            $matchScore['result_type'] = 'wrong';
                            $matchScore['explanation'] = 'Wrong outcome';
                            $totalWrongCount++;
                        }
                    }

                    $allDetailedScores[] = $matchScore;
                }
            }

            return $this->respond([
                'status' => 'success',
                'data' => [
                    'stack_id' => $stackId,
                    'stack_title' => $stack['title'],
                    'detailed_scoring' => $allDetailedScores,
                    'summary' => [
                        'total_points' => $totalPoints,
                        'exact_count' => $totalExactCount,
                        'outcome_count' => $totalOutcomeCount,
                        'wrong_count' => $totalWrongCount,
                        'total_matches' => count($allDetailedScores),
                        'total_predictions' => $predictionCount
                    ],
                    'points_system' => [
                        'actual' => '3 points - Exact score match',
                        'correct' => '1 point - Correct outcome (win/lose/draw)',
                        'wrong' => '0 points - Wrong outcome'
                    ]
                ]
            ]);
        } catch (\Exception $e) {
            return $this->failServerError('Failed to get detailed scoring: ' . $e->getMessage());
        }
    }

    /**
     * Get user's scoring statistics
     */
    public function getUserScoringStats($userId = null)
    {
        if (!$userId) {
            return $this->failValidationError('User ID required');
        }

        try {
            $scoringStats = $this->scoreModel->getUserScoringStats($userId);
            $wins = $this->winnerModel->getUserWins($userId);
            
            $stats = [
                'total_stacks_played' => $scoringStats['total_stacks_played'],
                'total_points' => $scoringStats['total_points'],
                'total_exact_predictions' => $scoringStats['total_exact_predictions'],
                'total_outcome_predictions' => $scoringStats['total_outcome_predictions'],
                'total_wrong_predictions' => $scoringStats['total_wrong_predictions'],
                'average_points_per_stack' => $scoringStats['average_points_per_stack'],
                'perfect_stacks' => $scoringStats['perfect_stacks'],
                'total_wins' => count($wins),
                'perfect_wins' => 0,
                'top_score_wins' => 0
            ];

            foreach ($wins as $win) {
                if ($win['win_type'] == 'full-correct') {
                    $stats['perfect_wins']++;
                } else {
                    $stats['top_score_wins']++;
                }
            }
            
            return $this->respond([
                'status' => 'success',
                'data' => $stats
            ]);
        } catch (\Exception $e) {
            return $this->failServerError('Failed to get scoring stats: ' . $e->getMessage());
        }
    }

    /**
     * Get outcome (home win, away win, or draw)
     */
    private function getOutcome($homeScore, $awayScore)
    {
        if ($homeScore > $awayScore) {
            return 'home_win';
        } elseif ($awayScore > $homeScore) {
            return 'away_win';
        } else {
            return 'draw';
        }
    }
} 