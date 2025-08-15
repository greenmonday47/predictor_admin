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
            'is_active' => $isActive
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
        $data = [
            'title' => 'Winners',
            'winners' => $this->winnerModel->getAllWinnersWithDetails()
        ];

        return view('admin/winners/index', $data);
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
                'date' => date('Y-m-d'),
                'activity' => 'New User Registrations',
                'count' => $recentUsers
            ];
        }

        // Recent predictions
        $recentPredictions = $this->predictionModel->where('created_at >=', date('Y-m-d', strtotime('-7 days')))
                                                   ->countAllResults();
        if ($recentPredictions > 0) {
            $activity[] = [
                'date' => date('Y-m-d'),
                'activity' => 'New Predictions',
                'count' => $recentPredictions
            ];
        }

        // Recent payments
        $recentPayments = $this->paymentModel->where('paid_at >=', date('Y-m-d', strtotime('-7 days')))
                                            ->countAllResults();
        if ($recentPayments > 0) {
            $activity[] = [
                'date' => date('Y-m-d'),
                'activity' => 'New Payments',
                'count' => $recentPayments
            ];
        }

        return $activity;
    }
} 