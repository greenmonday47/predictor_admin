<?php

namespace App\Models;

use CodeIgniter\Model;

class ScoreModel extends Model
{
    protected $table = 'scores';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;
    protected $allowedFields = ['user_id', 'stack_id', 'exact_count', 'outcome_count', 'wrong_count', 'total_points'];

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
        'exact_count' => 'required|integer|greater_than_equal_to[0]',
        'outcome_count' => 'required|integer|greater_than_equal_to[0]',
        'wrong_count' => 'required|integer|greater_than_equal_to[0]',
        'total_points' => 'required|integer|greater_than_equal_to[0]',
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
        'exact_count' => [
            'required' => 'Exact count is required',
            'integer' => 'Exact count must be an integer',
            'greater_than_equal_to' => 'Exact count cannot be negative',
        ],
        'outcome_count' => [
            'required' => 'Outcome count is required',
            'integer' => 'Outcome count must be an integer',
            'greater_than_equal_to' => 'Outcome count cannot be negative',
        ],
        'wrong_count' => [
            'required' => 'Wrong count is required',
            'integer' => 'Wrong count must be an integer',
            'greater_than_equal_to' => 'Wrong count cannot be negative',
        ],
        'total_points' => [
            'required' => 'Total points is required',
            'integer' => 'Total points must be an integer',
            'greater_than_equal_to' => 'Total points cannot be negative',
        ],
    ];

    protected $skipValidation = false;
    protected $cleanValidationRules = true;

    /**
     * Calculate and save score for a user's prediction
     * Points System:
     * - 3 points: Exact score match (Actual)
     * - 1 point: Correct outcome but not exact score (Correct)
     * - 0 points: Wrong outcome (Wrong)
     * 
     * NOTE: This method now accumulates points from multiple predictions
     */
    public function calculateScore($userId, $stackId, $predictions, $actualScores)
    {
        $exactCount = 0;
        $outcomeCount = 0;
        $wrongCount = 0;
        $totalPoints = 0;

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

            if ($actualScore) {
                $actualHome = (int) $actualScore['home_score'];
                $actualAway = (int) $actualScore['away_score'];

                // Calculate outcome (win/lose/draw)
                $predictedOutcome = $this->getOutcome($predictedHome, $predictedAway);
                $actualOutcome = $this->getOutcome($actualHome, $actualAway);

                if ($predictedHome == $actualHome && $predictedAway == $actualAway) {
                    // Exact score match - 3 points (Actual)
                    $exactCount++;
                    $totalPoints += 3;
                } elseif ($predictedOutcome == $actualOutcome) {
                    // Correct outcome but not exact score - 1 point (Correct)
                    $outcomeCount++;
                    $totalPoints += 1;
                } else {
                    // Wrong outcome - 0 points (Wrong)
                    $wrongCount++;
                }
            }
        }

        // Save or update score - NOW ACCUMULATES POINTS FROM MULTIPLE PREDICTIONS
        $existingScore = $this->where('user_id', $userId)
                             ->where('stack_id', $stackId)
                             ->first();

        if ($existingScore) {
            // ACCUMULATE points from multiple predictions
            $scoreData = [
                'exact_count' => $existingScore['exact_count'] + $exactCount,
                'outcome_count' => $existingScore['outcome_count'] + $outcomeCount,
                'wrong_count' => $existingScore['wrong_count'] + $wrongCount,
                'total_points' => $existingScore['total_points'] + $totalPoints,
            ];
            return $this->update($existingScore['id'], $scoreData);
        } else {
            // First prediction for this user in this stack
            $scoreData = [
                'user_id' => $userId,
                'stack_id' => $stackId,
                'exact_count' => $exactCount,
                'outcome_count' => $outcomeCount,
                'wrong_count' => $wrongCount,
                'total_points' => $totalPoints,
            ];
            return $this->insert($scoreData);
        }
    }

    /**
     * Calculate scores for ALL predictions of a user in a stack
     * This method properly handles multiple predictions per user
     */
    public function calculateUserScoresForStack($userId, $stackId, $actualScores)
    {
        // Get ALL predictions for this user in this stack
        $predictionModel = new \App\Models\PredictionModel();
        $userPredictions = $predictionModel->where('user_id', $userId)
                                         ->where('stack_id', $stackId)
                                         ->findAll();

        $totalExactCount = 0;
        $totalOutcomeCount = 0;
        $totalWrongCount = 0;
        $totalPoints = 0;

        // Process each prediction and accumulate points
        foreach ($userPredictions as $predictionRecord) {
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

                if ($actualScore) {
                    $actualHome = (int) $actualScore['home_score'];
                    $actualAway = (int) $actualScore['away_score'];

                    // Calculate outcome (win/lose/draw)
                    $predictedOutcome = $this->getOutcome($predictedHome, $predictedAway);
                    $actualOutcome = $this->getOutcome($actualHome, $actualAway);

                    if ($predictedHome == $actualHome && $predictedAway == $actualAway) {
                        // Exact score match - 3 points (Actual)
                        $totalExactCount++;
                        $totalPoints += 3;
                    } elseif ($predictedOutcome == $actualOutcome) {
                        // Correct outcome but not exact score - 1 point (Correct)
                        $totalOutcomeCount++;
                        $totalPoints += 1;
                    } else {
                        // Wrong outcome - 0 points (Wrong)
                        $totalWrongCount++;
                    }
                }
            }
        }

        // Save or update the accumulated score
        $existingScore = $this->where('user_id', $userId)
                             ->where('stack_id', $stackId)
                             ->first();

        $scoreData = [
            'user_id' => $userId,
            'stack_id' => $stackId,
            'exact_count' => $totalExactCount,
            'outcome_count' => $totalOutcomeCount,
            'wrong_count' => $totalWrongCount,
            'total_points' => $totalPoints,
        ];

        if ($existingScore) {
            return $this->update($existingScore['id'], $scoreData);
        } else {
            return $this->insert($scoreData);
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

    /**
     * Get leaderboard for a stack
     */
    public function getLeaderboard($stackId, $limit = 10)
    {
        return $this->select('scores.*, users.full_name, users.phone')
                   ->join('users', 'users.id = scores.user_id')
                   ->where('scores.stack_id', $stackId)
                   ->orderBy('scores.total_points', 'DESC')
                   ->orderBy('scores.exact_count', 'DESC')
                   ->orderBy('scores.outcome_count', 'DESC')
                   ->limit($limit)
                   ->findAll();
    }

    /**
     * Get user's score for a stack
     */
    public function getUserScore($userId, $stackId)
    {
        return $this->where('user_id', $userId)
                   ->where('stack_id', $stackId)
                   ->first();
    }

    /**
     * Get user's ranking in a stack
     */
    public function getUserRanking($userId, $stackId)
    {
        $userScore = $this->getUserScore($userId, $stackId);
        if (!$userScore) {
            return null;
        }

        $betterScores = $this->where('stack_id', $stackId)
                            ->where('total_points >', $userScore['total_points'])
                            ->countAllResults();

        return $betterScores + 1;
    }

    /**
     * Get all scores for a stack
     */
    public function getStackScores($stackId)
    {
        return $this->select('scores.*, users.full_name, users.phone')
                   ->join('users', 'users.id = scores.user_id')
                   ->where('scores.stack_id', $stackId)
                   ->orderBy('scores.total_points', 'DESC')
                   ->findAll();
    }

    /**
     * Check if user has perfect score (all exact predictions)
     */
    public function hasPerfectScore($userId, $stackId)
    {
        $score = $this->getUserScore($userId, $stackId);
        if (!$score) {
            return false;
        }

        return $score['wrong_count'] == 0 && $score['exact_count'] > 0;
    }

    /**
     * Get global leaderboard
     */
    public function getGlobalLeaderboard()
    {
        return $this->select('scores.*, users.full_name, users.phone, stacks.title')
                   ->join('users', 'users.id = scores.user_id')
                   ->join('stacks', 'stacks.id = scores.stack_id')
                   ->orderBy('scores.total_points', 'DESC')
                   ->orderBy('scores.exact_count', 'DESC')
                   ->limit(50)
                   ->findAll();
    }

    /**
     * Get detailed scoring breakdown for a user's predictions
     * Returns array with match-by-match scoring details
     */
    public function getDetailedScoring($userId, $stackId, $predictions, $actualScores)
    {
        $detailedScores = [];
        $totalPoints = 0;
        $exactCount = 0;
        $outcomeCount = 0;
        $wrongCount = 0;

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
                    $exactCount++;
                    $totalPoints += 3;
                } elseif ($predictedOutcome == $actualOutcome) {
                    // Correct outcome but not exact score - 1 point (Correct)
                    $matchScore['points'] = 1;
                    $matchScore['result_type'] = 'correct';
                    $matchScore['explanation'] = 'Correct outcome (win/lose/draw)';
                    $outcomeCount++;
                    $totalPoints += 1;
                } else {
                    // Wrong outcome - 0 points (Wrong)
                    $matchScore['points'] = 0;
                    $matchScore['result_type'] = 'wrong';
                    $matchScore['explanation'] = 'Wrong outcome';
                    $wrongCount++;
                }
            }

            $detailedScores[] = $matchScore;
        }

        return [
            'detailed_scores' => $detailedScores,
            'summary' => [
                'total_points' => $totalPoints,
                'exact_count' => $exactCount,
                'outcome_count' => $outcomeCount,
                'wrong_count' => $wrongCount,
                'total_matches' => count($predictions)
            ]
        ];
    }

    /**
     * Get user's scoring statistics across all stacks
     */
    public function getUserScoringStats($userId)
    {
        $scores = $this->where('user_id', $userId)->findAll();
        
        $stats = [
            'total_stacks_played' => count($scores),
            'total_points' => 0,
            'total_exact_predictions' => 0,
            'total_outcome_predictions' => 0,
            'total_wrong_predictions' => 0,
            'average_points_per_stack' => 0,
            'perfect_stacks' => 0
        ];

        foreach ($scores as $score) {
            $stats['total_points'] += $score['total_points'];
            $stats['total_exact_predictions'] += $score['exact_count'];
            $stats['total_outcome_predictions'] += $score['outcome_count'];
            $stats['total_wrong_predictions'] += $score['wrong_count'];
            
            // Count perfect stacks (all predictions correct)
            if ($score['wrong_count'] == 0 && $score['exact_count'] > 0) {
                $stats['perfect_stacks']++;
            }
        }

        if ($stats['total_stacks_played'] > 0) {
            $stats['average_points_per_stack'] = round($stats['total_points'] / $stats['total_stacks_played'], 2);
        }

        return $stats;
    }

    /**
     * Get aggregated leaderboard with total points across all stacks
     */
    public function getAggregatedLeaderboard($limit = 50)
    {
        $query = $this->select('
                users.id,
                users.full_name,
                users.phone,
                SUM(scores.total_points) as total_points,
                SUM(scores.exact_count) as total_exact,
                SUM(scores.outcome_count) as total_outcome,
                SUM(scores.wrong_count) as total_wrong,
                COUNT(DISTINCT scores.stack_id) as stacks_played,
                AVG(scores.total_points) as average_points_per_stack
            ')
            ->join('users', 'users.id = scores.user_id')
            ->groupBy('users.id, users.full_name, users.phone')
            ->having('SUM(scores.total_points) > 0')
            ->orderBy('SUM(scores.total_points)', 'DESC')
            ->orderBy('SUM(scores.exact_count)', 'DESC')
            ->orderBy('SUM(scores.outcome_count)', 'DESC')
            ->limit($limit);

        return $query->findAll();
    }

    /**
     * Get user's global ranking based on total points
     */
    public function getUserGlobalRanking($userId)
    {
        // Get user's total points
        $userTotal = $this->select('SUM(total_points) as total_points')
                         ->where('user_id', $userId)
                         ->first();

        if (!$userTotal || $userTotal['total_points'] == 0) {
            return null;
        }

        // Count users with higher total points
        $betterUsers = $this->select('COUNT(DISTINCT user_id) as count')
                           ->where('user_id !=', $userId)
                           ->groupBy('user_id')
                           ->having('SUM(total_points) >', $userTotal['total_points'])
                           ->countAllResults();

        return $betterUsers + 1;
    }

    /**
     * Get highest score achieved
     */
    public function getHighestScore()
    {
        $result = $this->select('MAX(total_points) as highest_score')
                      ->first();
        return $result ? $result['highest_score'] : 0;
    }

    /**
     * Get average score across all users
     */
    public function getAverageScore()
    {
        $result = $this->select('AVG(total_points) as average_score')
                      ->first();
        return $result ? round($result['average_score'], 2) : 0;
    }

    /**
     * Get leaderboard with accuracy percentage
     */
    public function getLeaderboardWithAccuracy($stackId = null, $limit = 50)
    {
        $query = $this->select('
                scores.*,
                users.full_name,
                users.phone,
                CASE 
                    WHEN (scores.exact_count + scores.outcome_count + scores.wrong_count) > 0 
                    THEN ROUND(((scores.exact_count + scores.outcome_count) / (scores.exact_count + scores.outcome_count + scores.wrong_count)) * 100, 1)
                    ELSE 0 
                END as accuracy
            ')
            ->join('users', 'users.id = scores.user_id');

        if ($stackId) {
            $query->where('scores.stack_id', $stackId);
        }

        return $query->orderBy('scores.total_points', 'DESC')
                    ->orderBy('accuracy', 'DESC')
                    ->limit($limit)
                    ->findAll();
    }
} 