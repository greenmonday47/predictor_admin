<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\UserModel;
use App\Models\StackModel;
use App\Models\PredictionModel;
use App\Models\ScoreModel;
use App\Models\WinnerModel;
use App\Models\PaymentModel;
use App\Models\TopUpTransactionModel;

class Dashboard extends BaseController
{
    protected $userModel;
    protected $stackModel;
    protected $predictionModel;
    protected $scoreModel;
    protected $winnerModel;
    protected $paymentModel;
    protected $topUpTransactionModel;

    public function __construct()
    {
        $this->userModel = new UserModel();
        $this->stackModel = new StackModel();
        $this->predictionModel = new PredictionModel();
        $this->scoreModel = new ScoreModel();
        $this->winnerModel = new WinnerModel();
        $this->paymentModel = new PaymentModel();
        $this->topUpTransactionModel = new TopUpTransactionModel();
    }

    public function index()
    {
        $data = [
            'title' => 'Admin Dashboard',
            'totalUsers' => $this->userModel->countAll(),
            'activeStacks' => $this->stackModel->where('is_active', true)->countAllResults(),
            'totalPredictions' => $this->predictionModel->countAll(),
            'totalWinners' => $this->winnerModel->countAll(),
            'recentUsers' => $this->userModel->orderBy('created_at', 'DESC')->limit(5)->findAll(),
            'recentStacks' => $this->stackModel->orderBy('created_at', 'DESC')->limit(5)->findAll()
        ];

        return view('admin/dashboard', $data);
    }

    public function users()
    {
        $data = [
            'title' => 'Manage Users',
            'users' => $this->userModel->orderBy('created_at', 'DESC')->findAll()
        ];

        return view('admin/users/index', $data);
    }

    public function stacks()
    {
        $data = [
            'title' => 'Manage Stacks',
            'stacks' => $this->stackModel->getAllStacksWithParticipantCount()
        ];

        return view('admin/stacks/index', $data);
    }

    public function createStack()
    {
        return view('admin/stacks/create', ['title' => 'Create New Stack']);
    }

    public function storeStack()
    {
        $title = $this->request->getPost('title');
        $prizeDescription = $this->request->getPost('prize_description');
        $entryFee = $this->request->getPost('entry_fee');
        $deadline = $this->request->getPost('deadline');
        $matches = $this->request->getPost('matches');
        
        // Handle prize image upload
        $prizeImage = null;
        $imageFile = $this->request->getFile('prize_image');
        
        if ($imageFile && $imageFile->isValid() && !$imageFile->hasMoved()) {
            // Validate file size (2MB max)
            if ($imageFile->getSize() > 2 * 1024 * 1024) {
                return redirect()->back()
                               ->withInput()
                               ->with('error', 'Image size must be less than 2MB');
            }
            
            // Validate file type
            $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
            if (!in_array($imageFile->getMimeType(), $allowedTypes)) {
                return redirect()->back()
                               ->withInput()
                               ->with('error', 'Please upload a valid image file (JPG, PNG, GIF)');
            }
            
            // Generate unique filename
            $newName = 'prize_' . time() . '_' . $imageFile->getRandomName();
            
            // Move file to uploads directory
            $imageFile->move(WRITEPATH . 'uploads/prizes/', $newName);
            $prizeImage = 'uploads/prizes/' . $newName;
        }

        // Validate matches
        if (!is_array($matches) || empty($matches)) {
            return redirect()->back()
                           ->withInput()
                           ->with('error', 'At least one match is required');
        }

        // Format matches for JSON storage
        $matchesData = [];
        foreach ($matches as $match) {
            if (!empty($match['home_team']) && !empty($match['away_team']) && !empty($match['match_time'])) {
                $matchesData[] = [
                    'match_id' => uniqid('MATCH_'),
                    'home_team' => $match['home_team'],
                    'away_team' => $match['away_team'],
                    'match_time' => $match['match_time']
                ];
            }
        }

        if (empty($matchesData)) {
            return redirect()->back()
                           ->withInput()
                           ->with('error', 'Valid match data is required');
        }

        $stackData = [
            'title' => $title,
            'prize_description' => $prizeDescription,
            'prize_image' => $prizeImage,
            'entry_fee' => (float) $entryFee,  // Ensure it's a float
            'matches_json' => json_encode($matchesData),
            'deadline' => $deadline,
            'is_active' => 1,  // Use integer instead of boolean
            'status' => 'active',  // Set initial status
            'reset_count' => 0,  // Initialize reset count
            'created_at' => date('Y-m-d H:i:s')  // Explicitly set timestamp
        ];

        try {
            // Log the data being inserted for debugging
            log_message('info', 'Attempting to insert stack data: ' . json_encode($stackData));
            
            // Use direct database query to avoid model issues
            $db = \Config\Database::connect();
            $inserted = $db->table('stacks')->insert($stackData);
            
            if ($inserted) {
                return redirect()->to('/admin/stacks')->with('success', 'Stack created successfully');
            } else {
                log_message('error', 'Stack insertion failed with database error');
                return redirect()->back()
                               ->withInput()
                               ->with('error', 'Failed to create stack: Database error');
            }
        } catch (\Exception $e) {
            log_message('error', 'Exception during stack creation: ' . $e->getMessage());
            log_message('error', 'Stack trace: ' . $e->getTraceAsString());
            return redirect()->back()
                           ->withInput()
                           ->with('error', 'Failed to create stack: ' . $e->getMessage());
        }
    }

    public function editStack($id)
    {
        $stack = $this->stackModel->find($id);
        
        if (!$stack) {
            return redirect()->to('/admin/stacks')->with('error', 'Stack not found');
        }

        $stack['matches'] = json_decode($stack['matches_json'], true);

        $data = [
            'title' => 'Edit Stack',
            'stack' => $stack
        ];

        return view('admin/stacks/edit', $data);
    }

    public function updateStack($id)
    {
        $stack = $this->stackModel->find($id);
        
        if (!$stack) {
            return redirect()->to('/admin/stacks')->with('error', 'Stack not found');
        }

        $title = $this->request->getPost('title');
        $prizeDescription = $this->request->getPost('prize_description');
        $entryFee = $this->request->getPost('entry_fee');
        $deadline = $this->request->getPost('deadline');
        $isActive = $this->request->getPost('is_active') ? true : false;
        $matches = $this->request->getPost('matches');

        // Validate matches
        if (!is_array($matches) || empty($matches)) {
            return redirect()->back()
                           ->withInput()
                           ->with('error', 'At least one match is required');
        }

        // Format matches for JSON storage
        $matchesData = [];
        foreach ($matches as $match) {
            if (!empty($match['home_team']) && !empty($match['away_team']) && !empty($match['match_time'])) {
                $matchesData[] = [
                    'match_id' => $match['match_id'] ?? uniqid('MATCH_'),
                    'home_team' => $match['home_team'],
                    'away_team' => $match['away_team'],
                    'match_time' => $match['match_time']
                ];
            }
        }

        if (empty($matchesData)) {
            return redirect()->back()
                           ->withInput()
                           ->with('error', 'Valid match data is required');
        }

        $stackData = [
            'title' => $title,
            'prize_description' => $prizeDescription,
            'entry_fee' => $entryFee,
            'matches_json' => json_encode($matchesData),
            'deadline' => $deadline,
            'is_active' => $isActive,
            'status' => $stack['status'] ?? 'active' // Preserve existing status
        ];

        try {
            $this->stackModel->update($id, $stackData);
            return redirect()->to('/admin/stacks')->with('success', 'Stack updated successfully');
        } catch (\Exception $e) {
            return redirect()->back()
                           ->withInput()
                           ->with('error', 'Failed to update stack: ' . $e->getMessage());
        }
    }

    public function deleteStack($id)
    {
        $stack = $this->stackModel->find($id);
        
        if (!$stack) {
            return redirect()->to('/admin/stacks')->with('error', 'Stack not found');
        }

        // Check if stack has predictions
        $predictionCount = $this->predictionModel->where('stack_id', $id)->countAllResults();
        if ($predictionCount > 0) {
            return redirect()->to('/admin/stacks')->with('error', 'Cannot delete stack with existing predictions');
        }

        try {
            $this->stackModel->delete($id);
            return redirect()->to('/admin/stacks')->with('success', 'Stack deleted successfully');
        } catch (\Exception $e) {
            return redirect()->to('/admin/stacks')->with('error', 'Failed to delete stack');
        }
    }

    public function resetStack($id)
    {
        $stack = $this->stackModel->find($id);
        
        if (!$stack) {
            return redirect()->to('/admin/stacks')->with('error', 'Stack not found');
        }

        // Check if stack has actual scores (matches have ended)
        if (empty($stack['actual_scores_json'])) {
            return redirect()->to('/admin/stacks')->with('error', 'Cannot reset stack - matches have not ended yet');
        }

        $data = [
            'title' => 'Reset Stack',
            'stack' => $stack,
            'currentMatches' => json_decode($stack['matches_json'], true)
        ];

        return view('admin/stacks/reset', $data);
    }

    public function processStackReset($id)
    {
        $stack = $this->stackModel->find($id);
        
        if (!$stack) {
            return redirect()->to('/admin/stacks')->with('error', 'Stack not found');
        }

        $newMatches = $this->request->getPost('matches');
        $newDeadline = $this->request->getPost('deadline');

        // Validate new matches
        if (!is_array($newMatches) || empty($newMatches)) {
            return redirect()->back()
                           ->withInput()
                           ->with('error', 'At least one match is required');
        }

        // Format new matches
        $matchesData = [];
        foreach ($newMatches as $match) {
            if (!empty($match['home_team']) && !empty($match['away_team']) && !empty($match['match_time'])) {
                $matchesData[] = [
                    'match_id' => uniqid('MATCH_'),
                    'home_team' => $match['home_team'],
                    'away_team' => $match['away_team'],
                    'match_time' => $match['match_time']
                ];
            }
        }

        if (empty($matchesData)) {
            return redirect()->back()
                           ->withInput()
                           ->with('error', 'Valid match data is required');
        }

        try {
            $this->stackModel->resetStack($id, $matchesData, $newDeadline);
            return redirect()->to('/admin/stacks')->with('success', 'Stack reset successfully with new matches');
        } catch (\Exception $e) {
            return redirect()->back()
                           ->withInput()
                           ->with('error', 'Failed to reset stack: ' . $e->getMessage());
        }
    }

    public function markStackAsWon($id)
    {
        $stack = $this->stackModel->find($id);
        
        if (!$stack) {
            return redirect()->to('/admin/stacks')->with('error', 'Stack not found');
        }

        // Check if stack has actual scores
        if (empty($stack['actual_scores_json'])) {
            return redirect()->to('/admin/stacks')->with('error', 'Cannot mark as won - matches have not ended yet');
        }

        try {
            // First, calculate scores if not already done
            $this->calculateUserScores($id);
            
            // Mark stack as won
            $this->stackModel->markAsWon($id);
            
            // Automatically award winners
            $perfectWinners = $this->winnerModel->awardPerfectScoreWinners($id);
            $topScoreWinners = $this->winnerModel->awardTopScoreWinners($id, 3);
            
            $totalWinners = $perfectWinners + $topScoreWinners;
            
            $message = "Stack marked as won successfully! ";
            if ($totalWinners > 0) {
                $message .= "Awarded {$perfectWinners} perfect score winner(s) and {$topScoreWinners} top score winner(s).";
            } else {
                $message .= "No winners found for this stack.";
            }

            return redirect()->to('/admin/stacks')->with('success', $message);
        } catch (\Exception $e) {
            return redirect()->to('/admin/stacks')->with('error', 'Failed to mark stack as won: ' . $e->getMessage());
        }
    }

    public function updateScores($id)
    {
        $stack = $this->stackModel->find($id);
        
        if (!$stack) {
            return redirect()->to('/admin/stacks')->with('error', 'Stack not found');
        }

        $matches = $this->stackModel->getMatchesWithScores($id);

        $data = [
            'title' => 'Update Scores',
            'stack' => $stack,
            'matches' => $matches
        ];

        return view('admin/stacks/update-scores', $data);
    }

    public function processScoreUpdate($id)
    {
        $stack = $this->stackModel->find($id);
        
        if (!$stack) {
            return redirect()->to('/admin/stacks')->with('error', 'Stack not found');
        }

        $scores = $this->request->getPost('scores');

        if (!is_array($scores)) {
            return redirect()->back()->with('error', 'Invalid scores data');
        }

        try {
            $scoresUpdated = false;
            
            foreach ($scores as $matchId => $score) {
                if (isset($score['home_score']) && isset($score['away_score']) && 
                    is_numeric($score['home_score']) && is_numeric($score['away_score'])) {
                    $this->stackModel->updateMatchScore(
                        $id, 
                        $matchId, 
                        (int) $score['home_score'], 
                        (int) $score['away_score']
                    );
                    $scoresUpdated = true;
                }
            }

            // Automatically calculate user scores if any scores were updated
            if ($scoresUpdated) {
                $this->calculateUserScores($id);
                return redirect()->to('/admin/stacks')->with('success', 'Scores updated, stack automatically locked, and user scores calculated successfully');
            }

            return redirect()->to('/admin/stacks')->with('success', 'Scores updated and stack automatically locked for security');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to update scores: ' . $e->getMessage());
        }
    }

    /**
     * Calculate user scores for a stack
     */
    private function calculateUserScores($stackId)
    {
        try {
            $stack = $this->stackModel->find($stackId);
            if (!$stack || !$stack['actual_scores_json']) {
                return false;
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

            log_message('info', "Calculated scores for {$calculatedCount} predictions in stack {$stackId}");
            return $calculatedCount;
        } catch (\Exception $e) {
            log_message('error', "Failed to calculate scores for stack {$stackId}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Manually trigger score calculation for a stack (admin action)
     */
    public function calculateScores($id)
    {
        $stack = $this->stackModel->find($id);
        
        if (!$stack) {
            return redirect()->to('/admin/stacks')->with('error', 'Stack not found');
        }

        if (!$stack['actual_scores_json']) {
            return redirect()->to('/admin/stacks')->with('error', 'No actual scores available for this stack');
        }

        try {
            $calculatedCount = $this->calculateUserScores($id);
            
            if ($calculatedCount === false) {
                return redirect()->to('/admin/stacks')->with('error', 'Failed to calculate scores');
            }

            return redirect()->to('/admin/stacks')->with('success', "Scores calculated for {$calculatedCount} predictions");
        } catch (\Exception $e) {
            return redirect()->to('/admin/stacks')->with('error', 'Failed to calculate scores: ' . $e->getMessage());
        }
    }

    /**
     * Calculate scores for all stacks that need it (admin action)
     */
    public function calculateAllScores()
    {
        try {
            $stacks = $this->stackModel->getStacksForScoring();
            $totalCalculated = 0;
            $processedStacks = 0;

            foreach ($stacks as $stack) {
                $calculatedCount = $this->calculateUserScores($stack['id']);
                if ($calculatedCount !== false) {
                    $totalCalculated += $calculatedCount;
                    $processedStacks++;
                }
            }

            return redirect()->to('/admin/stacks')->with('success', "Calculated scores for {$totalCalculated} predictions across {$processedStacks} stacks");
        } catch (\Exception $e) {
            return redirect()->to('/admin/stacks')->with('error', 'Failed to calculate all scores: ' . $e->getMessage());
        }
    }

    public function payments()
    {
        $payments = $this->paymentModel->getAllPaymentsWithDetails();
        $totalPayments = count($payments);
        $successfulPayments = $this->paymentModel->where('status', 'SUCCESS')->countAllResults();
        $pendingPayments = $this->paymentModel->where('status', 'PENDING')->countAllResults();
        $failedPayments = $this->paymentModel->where('status', 'FAILED')->countAllResults();

        $data = [
            'title' => 'Payment History',
            'payments' => $payments,
            'totalPayments' => $totalPayments,
            'successfulPayments' => $successfulPayments,
            'pendingPayments' => $pendingPayments,
            'failedPayments' => $failedPayments
        ];

        return view('admin/payments/index', $data);
    }

    public function topupTransactions()
    {
        $transactions = $this->topUpTransactionModel->getAllTopUpTransactionsWithDetails();
        $stats = $this->topUpTransactionModel->getTopUpStats();

        $data = [
            'title' => 'Top-Up Transactions',
            'transactions' => $transactions,
            'totalTransactions' => $stats['total_transactions'],
            'successfulTransactions' => $stats['successful_transactions'],
            'pendingTransactions' => $stats['pending_transactions'],
            'failedTransactions' => $stats['failed_transactions'],
            'totalAmount' => $stats['total_amount']
        ];

        return view('admin/topup-transactions/index', $data);
    }

    public function reports()
    {
        // Get statistics for reports
        $totalRevenue = $this->paymentModel->where('status', 'SUCCESS')
                                          ->selectSum('amount')
                                          ->first()['amount'] ?? 0;

        $activeUsers = $this->userModel->countAll();
        $totalPredictions = $this->predictionModel->countAll();
        $activeStacks = $this->stackModel->where('is_active', true)->countAllResults();

        $monthlyRevenue = $this->getMonthlyRevenue();
        $userRegistrations = $this->getUserRegistrations();
        $paymentStats = $this->getPaymentStats();
        $topStacks = $this->getTopStacks();
        $recentActivity = $this->getRecentActivity();

        // Calculate performance metrics
        $totalPayments = $this->paymentModel->countAll();
        $successfulPayments = $this->paymentModel->where('status', 'SUCCESS')->countAllResults();
        $successRate = $totalPayments > 0 ? round(($successfulPayments / $totalPayments) * 100, 1) : 0;
        
        $avgPredictions = $activeUsers > 0 ? round($totalPredictions / $activeUsers, 1) : 0;
        $avgStackParticipants = $activeStacks > 0 ? round($totalPredictions / $activeStacks, 1) : 0;
        $avgRevenuePerStack = $activeStacks > 0 ? round($totalRevenue / $activeStacks, 2) : 0;

        $data = [
            'title' => 'Reports & Analytics',
            'totalRevenue' => $totalRevenue,
            'activeUsers' => $activeUsers,
            'totalPredictions' => $totalPredictions,
            'activeStacks' => $activeStacks,
            'monthlyRevenue' => $monthlyRevenue,
            'userRegistrations' => $userRegistrations,
            'paymentStats' => $paymentStats,
            'topStacks' => $topStacks,
            'recentActivity' => $recentActivity,
            'successRate' => $successRate,
            'avgPredictions' => $avgPredictions,
            'avgStackParticipants' => $avgStackParticipants,
            'avgRevenuePerStack' => $avgRevenuePerStack
        ];

        return view('admin/reports/index', $data);
    }

    public function winners()
    {
        // Get all winners with details
        $winners = $this->winnerModel->getAllWinnersWithDetails();
        
        // Query database for accurate statistics
        $totalWinners = $this->winnerModel->countAll();
        
        // Count perfect winners (full-correct)
        $perfectWinners = $this->winnerModel->where('win_type', 'full-correct')->countAllResults();
        
        // Count top score winners (top-score)
        $topScorers = $this->winnerModel->where('win_type', 'top-score')->countAllResults();
        
        // Count winners from current month
        $currentMonth = date('Y-m');
        $thisMonthWinners = $this->winnerModel->where('DATE_FORMAT(awarded_at, "%Y-%m")', $currentMonth)->countAllResults();
        
        // Prepare won stacks data with actual scores from scores table
        $wonStacks = [];
        foreach ($winners as $winner) {
            $stackId = $winner['stack_id'];
            $userId = $winner['user_id'];
            
            // Get actual score from scores table
            $userScore = $this->scoreModel->where('user_id', $userId)
                                        ->where('stack_id', $stackId)
                                        ->first();
            
            // Get participant count for this stack
            $participantCount = $this->predictionModel->where('stack_id', $stackId)->countAllResults();
            
            if (!isset($wonStacks[$stackId])) {
                $wonStacks[$stackId] = [
                    'id' => $stackId,
                    'stack_title' => $winner['title'] ?? 'Unknown Stack',
                    'winner_name' => $winner['full_name'] ?? 'Unknown',
                    'winner_phone' => $winner['phone'] ?? 'N/A',
                    'score' => $userScore ? $userScore['total_points'] : 0,
                    'prize_amount' => $winner['entry_fee'] ?? 0,
                    'won_at' => $winner['awarded_at'] ?? date('Y-m-d H:i:s'),
                    'created_at' => $winner['awarded_at'] ?? date('Y-m-d H:i:s'),
                    'total_participants' => $participantCount,
                    'win_type' => $winner['win_type'],
                    'exact_count' => $userScore ? ($userScore['exact_count'] ?? 0) : 0,
                    'correct_count' => $userScore ? ($userScore['outcome_count'] ?? 0) : 0,
                    'wrong_count' => $userScore ? ($userScore['wrong_count'] ?? 0) : 0
                ];
            }
        }
        
        $data = [
            'title' => 'Winners',
            'winners' => $winners,
            'wonStacks' => array_values($wonStacks),
            'totalWinners' => $totalWinners,
            'perfectWinners' => $perfectWinners,
            'topScorers' => $topScorers,
            'thisMonthWinners' => $thisMonthWinners
        ];

        return view('admin/winners/index', $data);
    }

    public function getWinnerDetails($winnerId)
    {
        try {
            $winner = $this->winnerModel->find($winnerId);
            
            if (!$winner) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Winner not found'
                ]);
            }
            
            // Get additional details with score information
            $winnerDetails = $this->winnerModel->select('winners.*, users.full_name, users.phone, stacks.title, stacks.entry_fee')
                                             ->join('users', 'users.id = winners.user_id')
                                             ->join('stacks', 'stacks.id = winners.stack_id')
                                             ->where('winners.id', $winnerId)
                                             ->first();
            
            // Get score details from scores table
            $scoreDetails = $this->scoreModel->where('user_id', $winner['user_id'])
                                           ->where('stack_id', $winner['stack_id'])
                                           ->first();
            
            // Get participant count for this stack
            $participantCount = $this->predictionModel->where('stack_id', $winner['stack_id'])->countAllResults();
            
            // Merge score details with winner details
            if ($scoreDetails) {
                $winnerDetails['total_points'] = $scoreDetails['total_points'] ?? 0;
                $winnerDetails['exact_count'] = $scoreDetails['exact_count'] ?? 0;
                $winnerDetails['correct_count'] = $scoreDetails['outcome_count'] ?? 0;
                $winnerDetails['wrong_count'] = $scoreDetails['wrong_count'] ?? 0;
            } else {
                $winnerDetails['total_points'] = 0;
                $winnerDetails['exact_count'] = 0;
                $winnerDetails['correct_count'] = 0;
                $winnerDetails['wrong_count'] = 0;
            }
            
            $winnerDetails['total_participants'] = $participantCount;
            
            return $this->response->setJSON([
                'success' => true,
                'winner' => $winnerDetails
            ]);
            
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error loading winner details'
            ]);
        }
    }

    public function getUserDetails($userId)
    {
        try {
            $user = $this->userModel->find($userId);
            
            if (!$user) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'User not found'
                ]);
            }
            
            // Get user statistics
            $totalPredictions = $this->predictionModel->where('user_id', $userId)->countAllResults();
            $totalStacks = $this->predictionModel->where('user_id', $userId)->distinct()->countAllResults('stack_id');
            $totalWins = $this->winnerModel->where('user_id', $userId)->countAllResults();
            $totalPoints = $this->scoreModel->where('user_id', $userId)->selectSum('total_points')->first()['total_points'] ?? 0;
            
            // Get recent activity
            $recentPredictions = $this->predictionModel->where('user_id', $userId)
                                                      ->orderBy('created_at', 'DESC')
                                                      ->limit(5)
                                                      ->findAll();
            
            // Get performance stats
            $perfectScores = $this->scoreModel->where('user_id', $userId)
                                             ->where('exact_count >', 0)
                                             ->countAllResults();
            
            $stats = [
                'total_predictions' => $totalPredictions,
                'total_stacks' => $totalStacks,
                'total_wins' => $totalWins,
                'total_points' => $totalPoints,
                'perfect_scores' => $perfectScores,
                'recent_predictions' => $recentPredictions
            ];
            
            return $this->response->setJSON([
                'success' => true,
                'user' => $user,
                'stats' => $stats
            ]);
            
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error loading user details'
            ]);
        }
    }

    public function getUserPredictions($userId)
    {
        try {
            $user = $this->userModel->find($userId);
            
            if (!$user) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'User not found'
                ]);
            }
            
            // Get all predictions with stack details, grouped by stack
            $predictions = $this->predictionModel->select('
                predictions.*, 
                stacks.title as stack_title,
                stacks.entry_fee,
                stacks.deadline,
                stacks.status as stack_status,
                scores.total_points,
                scores.exact_count,
                scores.outcome_count,
                scores.wrong_count
            ')
            ->join('stacks', 'stacks.id = predictions.stack_id')
            ->join('scores', 'scores.user_id = predictions.user_id AND scores.stack_id = predictions.stack_id', 'left')
            ->where('predictions.user_id', $userId)
            ->orderBy('stacks.title', 'ASC')
            ->orderBy('predictions.created_at', 'DESC')
            ->findAll();
            
            // Group predictions by stack
            $groupedPredictions = [];
            foreach ($predictions as $prediction) {
                $stackId = $prediction['stack_id'];
                if (!isset($groupedPredictions[$stackId])) {
                    $groupedPredictions[$stackId] = [
                        'stack_info' => [
                            'title' => $prediction['stack_title'],
                            'entry_fee' => $prediction['entry_fee'],
                            'deadline' => $prediction['deadline'],
                            'status' => $prediction['stack_status']
                        ],
                        'predictions' => []
                    ];
                }
                
                // Parse predictions JSON to get individual match predictions
                $matchPredictions = json_decode($prediction['predictions_json'], true);
                $actualScores = json_decode($prediction['actual_scores_json'] ?? '{}', true);
                
                $groupedPredictions[$stackId]['predictions'][] = [
                    'id' => $prediction['id'],
                    'created_at' => $prediction['created_at'],
                    'match_predictions' => $matchPredictions,
                    'actual_scores' => $actualScores,
                    'total_points' => $prediction['total_points'] ?? 0,
                    'exact_count' => $prediction['exact_count'] ?? 0,
                    'outcome_count' => $prediction['outcome_count'] ?? 0,
                    'wrong_count' => $prediction['wrong_count'] ?? 0
                ];
            }
            
            return $this->response->setJSON([
                'success' => true,
                'user' => $user,
                'predictions' => $groupedPredictions
            ]);
            
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error loading user predictions'
            ]);
        }
    }

    public function serveImage($path)
    {
        // Security: Only allow access to uploads directory
        $fullPath = WRITEPATH . 'uploads/' . $path;
        
        // Prevent directory traversal
        if (strpos($path, '..') !== false || !file_exists($fullPath)) {
            return $this->response->setStatusCode(404)->setBody('Image not found');
        }
        
        // Get file info
        $fileInfo = pathinfo($fullPath);
        $extension = strtolower($fileInfo['extension']);
        
        // Only allow image files
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        if (!in_array($extension, $allowedExtensions)) {
            return $this->response->setStatusCode(403)->setBody('Access denied');
        }
        
        // Set appropriate content type
        $contentTypes = [
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'gif' => 'image/gif',
            'webp' => 'image/webp'
        ];
        
        $contentType = $contentTypes[$extension] ?? 'application/octet-stream';
        
        // Serve the file
        return $this->response
            ->setContentType($contentType)
            ->setBody(file_get_contents($fullPath));
    }

    private function getMonthlyStats()
    {
        $currentYear = date('Y');
        $stats = [];

        for ($month = 1; $month <= 12; $month++) {
            $startDate = "$currentYear-$month-01";
            $endDate = date('Y-m-t', strtotime($startDate));

            $stats[$month] = [
                'users' => $this->userModel->where('created_at >=', $startDate)
                                          ->where('created_at <=', $endDate . ' 23:59:59')
                                          ->countAllResults(),
                'predictions' => $this->predictionModel->where('created_at >=', $startDate)
                                                      ->where('created_at <=', $endDate . ' 23:59:59')
                                                      ->countAllResults(),
                'revenue' => $this->paymentModel->where('status', 'success')
                                               ->where('paid_at >=', $startDate)
                                               ->where('paid_at <=', $endDate . ' 23:59:59')
                                               ->selectSum('amount')
                                               ->first()['amount'] ?? 0
            ];
        }

        return $stats;
    }

    private function getMonthlyRevenue()
    {
        $currentYear = date('Y');
        $revenue = [];

        for ($month = 1; $month <= 12; $month++) {
            $startDate = "$currentYear-$month-01";
            $endDate = date('Y-m-t', strtotime($startDate));
            $monthName = date('M', strtotime($startDate));

            $monthlyRevenue = $this->paymentModel->where('status', 'SUCCESS')
                                                ->where('paid_at >=', $startDate)
                                                ->where('paid_at <=', $endDate . ' 23:59:59')
                                                ->selectSum('amount')
                                                ->first()['amount'] ?? 0;

            $revenue[] = [
                'month' => $monthName,
                'revenue' => $monthlyRevenue
            ];
        }

        return $revenue;
    }

    private function getUserRegistrations()
    {
        $currentYear = date('Y');
        $registrations = [];

        for ($month = 1; $month <= 12; $month++) {
            $startDate = "$currentYear-$month-01";
            $endDate = date('Y-m-t', strtotime($startDate));
            $monthName = date('M', strtotime($startDate));

            $monthlyUsers = $this->userModel->where('created_at >=', $startDate)
                                           ->where('created_at <=', $endDate . ' 23:59:59')
                                           ->countAllResults();

            $registrations[] = [
                'month' => $monthName,
                'count' => $monthlyUsers
            ];
        }

        return $registrations;
    }

    private function getPaymentStats()
    {
        return [
            'success' => $this->paymentModel->where('status', 'SUCCESS')->countAllResults(),
            'pending' => $this->paymentModel->where('status', 'PENDING')->countAllResults(),
            'failed' => $this->paymentModel->where('status', 'FAILED')->countAllResults()
        ];
    }

    private function getTopStacks()
    {
        return $this->stackModel->select('stacks.*, COUNT(predictions.id) as participant_count, SUM(payments.amount) as revenue')
                               ->join('predictions', 'predictions.stack_id = stacks.id', 'left')
                               ->join('payments', 'payments.stack_id = stacks.id AND payments.status = "SUCCESS"', 'left')
                               ->groupBy('stacks.id')
                               ->orderBy('revenue', 'DESC')
                               ->limit(5)
                               ->findAll();
    }

    private function getRecentActivity()
    {
        $activity = [];
        
        // Recent user registrations
        $recentUsers = $this->userModel->where('created_at >=', date('Y-m-d', strtotime('-7 days')))
                                      ->countAllResults();
        if ($recentUsers > 0) {
            $activity[] = [
                'type' => 'User Registration',
                'user_name' => 'System',
                'description' => "$recentUsers new users registered in the last 7 days",
                'created_at' => date('Y-m-d H:i:s')
            ];
        }

        // Recent predictions
        $recentPredictions = $this->predictionModel->where('created_at >=', date('Y-m-d', strtotime('-7 days')))
                                                   ->countAllResults();
        if ($recentPredictions > 0) {
            $activity[] = [
                'type' => 'Prediction',
                'user_name' => 'System',
                'description' => "$recentPredictions new predictions made in the last 7 days",
                'created_at' => date('Y-m-d H:i:s')
            ];
        }

        // Recent payments
        $recentPayments = $this->paymentModel->where('paid_at >=', date('Y-m-d', strtotime('-7 days')))
                                            ->countAllResults();
        if ($recentPayments > 0) {
            $activity[] = [
                'type' => 'Payment',
                'user_name' => 'System',
                'description' => "$recentPayments new payments processed in the last 7 days",
                'created_at' => date('Y-m-d H:i:s')
            ];
        }

        // Add some individual recent activities for more detail
        $recentUserRegistrations = $this->userModel->select('users.full_name, users.created_at')
                                                  ->where('users.created_at >=', date('Y-m-d', strtotime('-3 days')))
                                                  ->orderBy('users.created_at', 'DESC')
                                                  ->limit(3)
                                                  ->findAll();
        
        foreach ($recentUserRegistrations as $user) {
            $activity[] = [
                'type' => 'User Registration',
                'user_name' => $user['full_name'],
                'description' => 'New user registered',
                'created_at' => $user['created_at']
            ];
        }

        // Sort by created_at descending to show most recent first
        usort($activity, function($a, $b) {
            return strtotime($b['created_at']) - strtotime($a['created_at']);
        });

        return array_slice($activity, 0, 10); // Return only the 10 most recent activities
    }
} 