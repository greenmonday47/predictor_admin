<?php

namespace App\Models;

use CodeIgniter\Model;

class WinnerModel extends Model
{
    protected $table = 'winners';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;
    protected $allowedFields = ['user_id', 'stack_id', 'win_type'];

    // Dates
    protected $useTimestamps = false;
    protected $dateFormat = 'datetime';
    protected $createdField = '';
    protected $updatedField = '';
    protected $deletedField = false;

    // Validation
    protected $validationRules = [
        'user_id' => 'required|integer|is_natural_no_zero',
        'stack_id' => 'required|integer|is_natural_no_zero',
        'win_type' => 'required|in_list[full-correct,top-score]',
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
        'win_type' => [
            'required' => 'Win type is required',
            'in_list' => 'Win type must be full-correct or top-score',
        ],
    ];

    protected $skipValidation = false;
    protected $cleanValidationRules = true;

    /**
     * Check if user is already a winner for a stack
     */
    public function isUserWinner($userId, $stackId)
    {
        return $this->where('user_id', $userId)
                   ->where('stack_id', $stackId)
                   ->countAllResults() > 0;
    }

    /**
     * Get winners for a stack
     */
    public function getStackWinners($stackId)
    {
        return $this->select('winners.*, users.full_name, users.phone')
                   ->join('users', 'users.id = winners.user_id')
                   ->where('winners.stack_id', $stackId)
                   ->orderBy('winners.awarded_at', 'ASC')
                   ->findAll();
    }

    /**
     * Get user's wins
     */
    public function getUserWins($userId)
    {
        return $this->select('winners.*, stacks.title, stacks.prize_description')
                   ->join('stacks', 'stacks.id = winners.stack_id')
                   ->where('winners.user_id', $userId)
                   ->orderBy('winners.awarded_at', 'DESC')
                   ->findAll();
    }

    /**
     * Award winner
     */
    public function awardWinner($userId, $stackId, $winType)
    {
        // Check if user is already a winner
        if ($this->isUserWinner($userId, $stackId)) {
            return false;
        }

        return $this->insert([
            'user_id' => $userId,
            'stack_id' => $stackId,
            'win_type' => $winType,
            'awarded_at' => date('Y-m-d H:i:s')
        ]);
    }

    /**
     * Award perfect score winners (full-correct)
     */
    public function awardPerfectScoreWinners($stackId)
    {
        $scoreModel = new \App\Models\ScoreModel();
        $perfectScorers = $scoreModel->where('stack_id', $stackId)
                                   ->where('wrong_count', 0)
                                   ->where('exact_count >', 0)
                                   ->findAll();

        $awarded = 0;
        foreach ($perfectScorers as $scorer) {
            if ($this->awardWinner($scorer['user_id'], $stackId, 'full-correct')) {
                $awarded++;
            }
        }

        return $awarded;
    }

    /**
     * Award top score winners
     */
    public function awardTopScoreWinners($stackId, $topCount = 3)
    {
        $scoreModel = new \App\Models\ScoreModel();
        $topScores = $scoreModel->select('scores.*, users.full_name')
                               ->join('users', 'users.id = scores.user_id')
                               ->where('scores.stack_id', $stackId)
                               ->orderBy('scores.total_points', 'DESC')
                               ->orderBy('scores.exact_count', 'DESC')
                               ->limit($topCount)
                               ->findAll();

        $awarded = 0;
        foreach ($topScores as $score) {
            // Only award if not already a perfect score winner
            if (!$this->isUserWinner($score['user_id'], $stackId)) {
                if ($this->awardWinner($score['user_id'], $stackId, 'top-score')) {
                    $awarded++;
                }
            }
        }

        return $awarded;
    }

    /**
     * Get winner statistics
     */
    public function getWinnerStats($stackId)
    {
        $stats = [
            'total_winners' => $this->where('stack_id', $stackId)->countAllResults(),
            'perfect_winners' => $this->where('stack_id', $stackId)
                                    ->where('win_type', 'full-correct')
                                    ->countAllResults(),
            'top_score_winners' => $this->where('stack_id', $stackId)
                                      ->where('win_type', 'top-score')
                                      ->countAllResults(),
        ];

        return $stats;
    }

    /**
     * Get all winners with details
     */
    public function getAllWinners()
    {
        return $this->select('winners.*, users.full_name, users.phone, stacks.title, stacks.prize_description')
                   ->join('users', 'users.id = winners.user_id')
                   ->join('stacks', 'stacks.id = winners.stack_id')
                   ->orderBy('winners.awarded_at', 'DESC')
                   ->findAll();
    }

    /**
     * Get all winners with details (alias for getAllWinners)
     */
    public function getAllWinnersWithDetails()
    {
        return $this->select('winners.*, users.full_name, users.phone, stacks.title, stacks.entry_fee, stacks.prize_description')
                   ->join('users', 'users.id = winners.user_id')
                   ->join('stacks', 'stacks.id = winners.stack_id')
                   ->orderBy('winners.awarded_at', 'DESC')
                   ->findAll();
    }

    /**
     * Get recent winners
     */
    public function getRecentWinners($limit = 5)
    {
        return $this->select('winners.*, users.full_name, users.phone, stacks.title')
                   ->join('users', 'users.id = winners.user_id')
                   ->join('stacks', 'stacks.id = winners.stack_id')
                   ->orderBy('winners.awarded_at', 'DESC')
                   ->limit($limit)
                   ->findAll();
    }

    /**
     * Get top performers (users with most wins)
     */
    public function getTopPerformers($limit = 10)
    {
        return $this->select('
                users.id,
                users.full_name,
                users.phone,
                COUNT(winners.id) as total_wins,
                SUM(CASE WHEN winners.win_type = "full-correct" THEN 1 ELSE 0 END) as perfect_wins,
                SUM(CASE WHEN winners.win_type = "top-score" THEN 1 ELSE 0 END) as top_score_wins
            ')
            ->join('users', 'users.id = winners.user_id')
            ->groupBy('users.id, users.full_name, users.phone')
            ->having('COUNT(winners.id) > 0')
            ->orderBy('COUNT(winners.id)', 'DESC')
            ->orderBy('SUM(CASE WHEN winners.win_type = "full-correct" THEN 1 ELSE 0 END)', 'DESC')
            ->limit($limit)
            ->findAll();
    }
} 