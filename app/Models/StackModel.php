<?php

namespace App\Models;

use CodeIgniter\Model;

class StackModel extends Model
{
    protected $table = 'stacks';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;
    protected $allowedFields = [
        'title', 
        'prize_description', 
        'prize_image', 
        'entry_fee', 
        'matches_json', 
        'actual_scores_json', 
        'deadline', 
        'is_active',
        'status',
        'reset_count',
        'won_at',
        'previous_matches_json'
    ];

    // Dates
    protected $useTimestamps = false; // Disable automatic timestamps to avoid conflicts
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = false;
    protected $deletedField = false;

    // Validation
    protected $validationRules = [
        'title' => 'required|min_length[3]|max_length[255]',
        'entry_fee' => 'required|numeric|greater_than_equal_to[0]',
        'matches_json' => 'required|valid_json',
        'deadline' => 'required|valid_date',
    ];

    protected $validationMessages = [
        'title' => [
            'required' => 'Stack title is required',
            'min_length' => 'Title must be at least 3 characters long',
            'max_length' => 'Title cannot exceed 255 characters',
        ],
        'entry_fee' => [
            'required' => 'Entry fee is required',
            'numeric' => 'Entry fee must be a number',
            'greater_than_equal_to' => 'Entry fee cannot be negative',
        ],
        'matches_json' => [
            'required' => 'Matches data is required',
            'valid_json' => 'Matches data must be valid JSON',
        ],
        'deadline' => [
            'required' => 'Deadline is required',
            'valid_date' => 'Deadline must be a valid date',
        ],
    ];

    protected $skipValidation = false;
    protected $cleanValidationRules = true;

    /**
     * Get active stacks
     */
    public function getActiveStacks()
    {
        return $this->where('is_active', true)
                   ->where('deadline >', date('Y-m-d H:i:s'))
                   ->orderBy('deadline', 'ASC')
                   ->findAll();
    }

    /**
     * Get stack with matches decoded
     */
    public function getStackWithMatches($id)
    {
        $stack = $this->find($id);
        if ($stack) {
            $stack['matches'] = json_decode($stack['matches_json'], true);
            $stack['actual_scores'] = $stack['actual_scores_json'] ? 
                json_decode($stack['actual_scores_json'], true) : null;
        }
        return $stack;
    }

    /**
     * Update actual scores for a stack
     */
    public function updateActualScores($id, $scores)
    {
        // Use direct database update to avoid issues with date fields
        $updateData = [
            'actual_scores_json' => json_encode($scores),
            'is_active' => false // Close the stack when scores are added
        ];

        $result = $this->db->table($this->table)
                          ->where('id', $id)
                          ->update($updateData);

        if ($result) {
            log_message('info', "Stack {$id} actual scores updated and stack closed");
        }

        return $result;
    }

    /**
     * Update individual match score
     * SECURITY: Automatically locks the stack when any score is updated to prevent
     * users from making predictions after they know the actual results
     */
    public function updateMatchScore($id, $matchId, $homeScore, $awayScore)
    {
        log_message('info', "updateMatchScore called: stack={$id}, match={$matchId}, home={$homeScore}, away={$awayScore}");
        
        $stack = $this->find($id);
        if (!$stack) {
            log_message('error', "Stack not found in updateMatchScore: {$id}");
            return false;
        }

        log_message('info', "Found stack: " . $stack['title']);

        // Get current actual scores
        $actualScores = $stack['actual_scores_json'] ? 
            json_decode($stack['actual_scores_json'], true) : [];
        
        log_message('info', "Current actual scores: " . json_encode($actualScores));

        // Find and update the specific match score
        $scoreUpdated = false;
        foreach ($actualScores as &$score) {
            if ($score['match_id'] === $matchId) {
                log_message('info', "Updating existing score for match {$matchId}");
                $score['home_score'] = $homeScore;
                $score['away_score'] = $awayScore;
                $score['updated_at'] = date('Y-m-d H:i:s');
                $scoreUpdated = true;
                break;
            }
        }

        // If match not found, add it
        if (!$scoreUpdated) {
            log_message('info', "Adding new score for match {$matchId}");
            $actualScores[] = [
                'match_id' => $matchId,
                'home_score' => $homeScore,
                'away_score' => $awayScore,
                'updated_at' => date('Y-m-d H:i:s')
            ];
        }

        log_message('info', "Updated actual scores: " . json_encode($actualScores));

        // SECURITY: Automatically lock the stack when any score is updated
        // This prevents users from making predictions after they know the results
        $updateData = [
            'actual_scores_json' => json_encode($actualScores),
            'is_active' => false // Lock the stack immediately
        ];

        log_message('info', "Updating stack with data: " . json_encode($updateData));

        // Use direct database update to avoid escape array issues
        $result = $this->db->table($this->table)
                          ->where('id', $id)
                          ->update($updateData);

        if ($result) {
            log_message('info', "Successfully updated stack {$id} with new scores");
        } else {
            log_message('error', "Failed to update stack {$id} with new scores");
        }

        return $result;
    }

    /**
     * Get matches with their actual scores
     */
    public function getMatchesWithScores($id)
    {
        $stack = $this->find($id);
        if (!$stack) {
            return null;
        }

        $matches = json_decode($stack['matches_json'], true);
        $actualScores = $stack['actual_scores_json'] ? 
            json_decode($stack['actual_scores_json'], true) : [];

        // Merge matches with their scores
        foreach ($matches as &$match) {
            $match['actual_score'] = null;
            $match['score_updated'] = false;
            
            foreach ($actualScores as $score) {
                if ($score['match_id'] === $match['match_id']) {
                    $match['actual_score'] = $score['home_score'] . ' - ' . $score['away_score'];
                    $match['home_score'] = $score['home_score'];
                    $match['away_score'] = $score['away_score'];
                    $match['score_updated'] = true;
                    $match['updated_at'] = $score['updated_at'] ?? null;
                    break;
                }
            }
        }

        return $matches;
    }

    /**
     * Check if stack is still open for predictions
     * SECURITY: Also checks if any scores have been updated to prevent
     * predictions after results are known
     */
    public function isOpenForPredictions($id)
    {
        $stack = $this->find($id);
        if (!$stack) {
            return false;
        }
        
        // SECURITY: If stack has any actual scores, it's automatically locked
        // This prevents users from making predictions after they know the results
        if ($this->hasActualScores($id)) {
            log_message('info', "Stack {$id} locked due to existing actual scores");
            return false;
        }
        
        // For testing, let's be more lenient with deadline check
        // TODO: Re-enable strict deadline check once we have proper test data
        $deadlinePassed = strtotime($stack['deadline']) <= time();
        log_message('info', 'Stack ' . $id . ' deadline check: ' . $stack['deadline'] . ' vs ' . date('Y-m-d H:i:s') . ' - Passed: ' . ($deadlinePassed ? 'YES' : 'NO'));
        
        return $stack['is_active'] && !$deadlinePassed;
    }

    /**
     * Check if stack has any actual scores
     */
    public function hasActualScores($id)
    {
        $stack = $this->find($id);
        if (!$stack) {
            return false;
        }
        
        return !empty($stack['actual_scores_json']) && 
               $stack['actual_scores_json'] !== 'null' && 
               $stack['actual_scores_json'] !== '[]';
    }

    /**
     * Check if stack is locked due to score updates
     */
    public function isLockedDueToScores($id)
    {
        $stack = $this->find($id);
        if (!$stack) {
            return false;
        }
        
        // SECURITY: Stack is locked if it has any actual scores
        // This prevents users from making predictions after they know the results
        return $this->hasActualScores($id);
    }

    /**
     * Get stacks that need scoring (have actual scores but are still active)
     */
    public function getStacksForScoring()
    {
        return $this->where('is_active', true)
                   ->where('actual_scores_json IS NOT NULL')
                   ->where('actual_scores_json !=', 'null')
                   ->findAll();
    }

    /**
     * Validate matches JSON structure
     */
    public function validateMatchesStructure($matches)
    {
        if (!is_array($matches)) {
            return false;
        }

        foreach ($matches as $match) {
            if (!isset($match['home_team']) || !isset($match['away_team']) || !isset($match['match_id'])) {
                return false;
            }
        }

        return true;
    }

    /**
     * Get all stacks with participant count
     */
    public function getAllStacksWithParticipantCount()
    {
        $stacks = $this->findAll();
        
        foreach ($stacks as &$stack) {
            $stack['participant_count'] = $this->db->table('predictions')
                                                   ->where('stack_id', $stack['id'])
                                                   ->countAllResults();
        }
        
        return $stacks;
    }

    /**
     * Reset stack with new matches
     */
    public function resetStack($id, $newMatches, $newDeadline)
    {
        $stack = $this->find($id);
        if (!$stack) {
            return false;
        }

        // Store current matches as previous matches
        $previousMatches = $stack['matches_json'];
        
        // Update stack with new matches
        $updateData = [
            'matches_json' => json_encode($newMatches),
            'actual_scores_json' => null, // Clear previous scores
            'deadline' => $newDeadline,
            'status' => 'active',
            'reset_count' => $stack['reset_count'] + 1,
            'previous_matches_json' => $previousMatches
        ];

        $updated = $this->db->table($this->table)
                           ->where('id', $id)
                           ->update($updateData);
        
        if ($updated) {
            // Clear all payments for this stack (users need to pay again)
            $db = \Config\Database::connect();
            $db->table('payments')->where('stack_id', $id)->delete();
            
            // Clear all predictions for this stack
            $db->table('predictions')->where('stack_id', $id)->delete();
            
            // Clear all scores for this stack
            $db->table('scores')->where('stack_id', $id)->delete();
        }

        return $updated;
    }

    /**
     * Mark stack as won
     */
    public function markAsWon($id)
    {
        try {
            // Use direct database update to avoid issues with date fields
            $updateData = [
                'status' => 'won',
                'is_active' => false,
                'won_at' => date('Y-m-d H:i:s')
            ];

            $result = $this->db->table($this->table)
                              ->where('id', $id)
                              ->update($updateData);

            if ($result) {
                log_message('info', "Stack {$id} marked as won");
            }

            return $result;
        } catch (\Exception $e) {
            log_message('error', "Failed to mark stack {$id} as won: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get stacks that can be reset (have actual scores but no winners)
     */
    public function getStacksForReset()
    {
        return $this->where('status', 'active')
                   ->where('actual_scores_json IS NOT NULL')
                   ->where('actual_scores_json !=', 'null')
                   ->findAll();
    }

    /**
     * Get won stacks
     */
    public function getWonStacks()
    {
        return $this->where('status', 'won')
                   ->orderBy('won_at', 'DESC')
                   ->findAll();
    }

    /**
     * Get active stacks (not won and not expired)
     */
    public function getActiveStacksForUsers()
    {
        // Get only active stacks that haven't expired and aren't won
        $stacks = $this->where('status', 'active')
                      ->where('is_active', true)
                      ->where('deadline >', date('Y-m-d H:i:s'))
                      ->orderBy('deadline', 'ASC')
                      ->findAll();
        
        // Log for debugging
        log_message('info', 'Found ' . count($stacks) . ' active stacks that haven\'t expired');
        
        foreach ($stacks as $stack) {
            log_message('info', 'Active Stack ID: ' . $stack['id'] . ', Title: ' . $stack['title'] . ', Deadline: ' . $stack['deadline']);
        }
        
        return $stacks;
    }
} 