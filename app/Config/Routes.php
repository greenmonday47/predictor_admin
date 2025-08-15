<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->GET('/', 'Admin\Dashboard::index');

// Admin routes
$routes->group('admin', ['namespace' => 'App\Controllers\Admin'], function($routes) {
    $routes->GET('/', 'Dashboard::index');
    $routes->GET('users', 'Dashboard::users');
    $routes->GET('stacks', 'Dashboard::stacks');
    $routes->GET('stacks/create', 'Dashboard::createStack');
    $routes->POST('stacks/create', 'Dashboard::storeStack');
    $routes->GET('stacks/(:num)/edit', 'Dashboard::editStack/$1');
    $routes->POST('stacks/(:num)/edit', 'Dashboard::updateStack/$1');
    $routes->GET('stacks/(:num)/delete', 'Dashboard::deleteStack/$1');
    $routes->GET('stacks/(:num)/reset', 'Dashboard::resetStack/$1');
    $routes->POST('stacks/(:num)/process-reset', 'Dashboard::processStackReset/$1');
    $routes->GET('stacks/(:num)/mark-won', 'Dashboard::markStackAsWon/$1');
    $routes->GET('stacks/(:num)/update-scores', 'Dashboard::updateScores/$1');
    $routes->POST('stacks/(:num)/process-score-update', 'Dashboard::processScoreUpdate/$1');
    $routes->GET('stacks/(:num)/calculate-scores', 'Dashboard::calculateScores/$1');
    $routes->GET('stacks/calculate-all-scores', 'Dashboard::calculateAllScores');
    $routes->GET('payments', 'Dashboard::payments');
    $routes->GET('topup-transactions', 'Dashboard::topupTransactions');
    $routes->GET('reports', 'Dashboard::reports');
    $routes->GET('winners', 'Dashboard::winners');
    $routes->GET('winners/(:num)', 'Dashboard::getWinnerDetails/$1');
    $routes->GET('users/(:num)', 'Dashboard::getUserDetails/$1');
    $routes->GET('users/(:num)/predictions', 'Dashboard::getUserPredictions/$1');
    $routes->GET('uploads/(:any)', 'Dashboard::serveImage/$1');
});

// API Routes
$routes->group('api', ['namespace' => 'App\Controllers'], function($routes) {
    
    // Authentication routes
    $routes->POST('auth/register', 'AuthController::register');
    $routes->POST('auth/login', 'AuthController::login');
    $routes->GET('auth/profile/(:num)', 'AuthController::profile/$1');
    $routes->put('auth/profile/(:num)', 'AuthController::updateProfile/$1');
    
    // Stack routes
    $routes->GET('stacks', 'StackController::index');
    $routes->GET('stacks/(:num)', 'StackController::show/$1');
    $routes->GET('stacks/(:num)/user/(:num)', 'StackController::showWithUserStatus/$1/$2');
    $routes->GET('stacks/(:num)/leaderboard', 'StackController::leaderboard/$1');
    $routes->GET('stacks/user/(:num)', 'StackController::userStacks/$1');
    
    // Prediction routes
    $routes->POST('predictions/submit', 'PredictionController::submit');
    $routes->GET('predictions/user/(:num)/stack/(:num)', 'PredictionController::getUserPrediction/$1/$2');
    $routes->GET('predictions/stack/(:num)', 'PredictionController::getStackPredictions/$1');
    $routes->GET('predictions/user/(:num)', 'PredictionController::getUserPredictions/$1');
    $routes->GET('predictions/user/all', 'PredictionController::getAllUserPredictions');
    $routes->POST('predictions/calculate/(:num)', 'PredictionController::calculateScores/$1');
    
    // Payment routes
    $routes->POST('payments/initialize', 'PaymentController::initialize');
    $routes->POST('payments/process-with-predictions', 'PaymentController::processPaymentWithPredictions');
    $routes->GET('payments/verify/(:any)', 'PaymentController::verify/$1');
    $routes->POST('payments/update-status', 'PaymentController::updateStatus');
    $routes->POST('payments/gmpay-webhook', 'PaymentController::gmpayWebhook');
    $routes->GET('payments/user/(:num)', 'PaymentController::getUserPayments/$1');
    $routes->GET('payments/stack/(:num)', 'PaymentController::getStackPayments/$1');
    $routes->GET('payments/check/(:num)/(:num)', 'PaymentController::checkPayment/$1/$2');
    $routes->GET('payments/history', 'PaymentController::getPaymentHistory');
    
    // Score routes
    $routes->GET('scores/leaderboard/(:num)', 'ScoreController::leaderboard/$1');
    $routes->GET('scores/user/(:num)/stack/(:num)', 'ScoreController::getUserScore/$1/$2');
    $routes->GET('scores/stack/(:num)', 'ScoreController::getStackScores/$1');
    $routes->POST('scores/calculate/(:num)', 'ScoreController::calculateScores/$1');
    $routes->POST('scores/award-winners/(:num)', 'ScoreController::awardWinners/$1');
    $routes->GET('scores/winners/(:num)', 'ScoreController::getWinners/$1');
    $routes->GET('scores/ranking/(:num)/(:num)', 'ScoreController::getUserRanking/$1/$2');
    $routes->GET('scores/stats/(:num)', 'ScoreController::getUserStats/$1');
    $routes->GET('scores/scoring-stats/(:num)', 'ScoreController::getUserScoringStats/$1');
    $routes->GET('scores/detailed-scoring/(:num)/(:num)', 'ScoreController::getDetailedScoring/$1/$2');
    
    // Global routes
    $routes->GET('leaderboard', 'ScoreController::globalLeaderboard');
    $routes->GET('winners', 'ScoreController::globalWinners');
    $routes->GET('user/metrics', 'ScoreController::getUserMetrics');
    $routes->put('user/update', 'AuthController::updateProfile');

    // App update routes
    $routes->GET('app/version', 'AppController::version');
    $routes->GET('app/download/(:segment)', 'AppController::download/$1');
    $routes->GET('app/download/latest', 'AppController::downloadLatest');
    
    // Enhanced Leaderboard routes
    $routes->GET('leaderboard/global', 'LeaderboardController::getGlobalLeaderboard');
    $routes->GET('leaderboard/stack/(:num)', 'LeaderboardController::getStackLeaderboard/$1');
    $routes->GET('leaderboard/user/ranking', 'LeaderboardController::getUserGlobalRanking');
    $routes->GET('leaderboard/user/ranking/(:num)', 'LeaderboardController::getUserGlobalRanking/$1');
    $routes->GET('leaderboard/user/stack/(:num)/ranking', 'LeaderboardController::getUserStackRanking/$1');
    $routes->GET('leaderboard/user/stack/(:num)/(:num)/ranking', 'LeaderboardController::getUserStackRanking/$1/$2');
    $routes->GET('leaderboard/top-performers', 'LeaderboardController::getTopPerformers');
    $routes->GET('leaderboard/stats', 'LeaderboardController::getLeaderboardStats');
    
            // Wallet routes
        $routes->GET('wallet/balance', 'WalletController::getBalance');
        $routes->GET('wallet/transactions', 'WalletController::getTransactionHistory');
        $routes->POST('wallet/topup', 'WalletController::topUp');
        $routes->POST('wallet/participate', 'WalletController::participateInStack');
        $routes->GET('wallet/stats', 'WalletController::getWalletStats');
        $routes->GET('wallet/eligibility', 'WalletController::checkParticipationEligibility');
        
        // Top-up routes
        $routes->GET('wallet/topup/status/(:any)', 'WalletController::checkTopUpStatus/$1');
        $routes->GET('wallet/topup/details/(:num)', 'WalletController::getTopUpTransactionDetails/$1');
        $routes->GET('wallet/topup/history', 'WalletController::getTopUpHistory');
        $routes->GET('wallet/topup/check-pending', 'WalletController::checkPendingTopUps');
        
        // Cron job routes
        $routes->GET('cron/check-pending-topups', 'CronController::checkPendingTopUps');
        $routes->GET('cron/health', 'CronController::healthCheck');
    
});
