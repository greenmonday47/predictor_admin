<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class TestDataSeeder extends Seeder
{
    public function run()
    {
        // Clear existing data first
        $this->db->table('winners')->truncate();
        $this->db->table('scores')->truncate();
        $this->db->table('payments')->truncate();
        $this->db->table('predictions')->truncate();
        $this->db->table('stacks')->truncate();
        $this->db->table('users')->truncate();

        // Create test users
        $users = [
            [
                'full_name' => 'John Doe',
                'phone' => '256701234567',
                'pin' => password_hash('1234', PASSWORD_DEFAULT),
                'created_at' => date('Y-m-d H:i:s', strtotime('-30 days'))
            ],
            [
                'full_name' => 'Jane Smith',
                'phone' => '256702345678',
                'pin' => password_hash('1234', PASSWORD_DEFAULT),
                'created_at' => date('Y-m-d H:i:s', strtotime('-25 days'))
            ],
            [
                'full_name' => 'Mike Johnson',
                'phone' => '256703456789',
                'pin' => password_hash('1234', PASSWORD_DEFAULT),
                'created_at' => date('Y-m-d H:i:s', strtotime('-20 days'))
            ],
            [
                'full_name' => 'Sarah Wilson',
                'phone' => '256704567890',
                'pin' => password_hash('1234', PASSWORD_DEFAULT),
                'created_at' => date('Y-m-d H:i:s', strtotime('-15 days'))
            ],
            [
                'full_name' => 'David Brown',
                'phone' => '256705678901',
                'pin' => password_hash('1234', PASSWORD_DEFAULT),
                'created_at' => date('Y-m-d H:i:s', strtotime('-10 days'))
            ],
            [
                'full_name' => 'Emma Davis',
                'phone' => '256706789012',
                'pin' => password_hash('1234', PASSWORD_DEFAULT),
                'created_at' => date('Y-m-d H:i:s', strtotime('-5 days'))
            ]
        ];

        $userIds = [];
        foreach ($users as $user) {
            $userIds[] = $this->db->table('users')->insert($user);
        }

        // Create test stacks
        $stacks = [
            [
                'title' => 'Premier League Weekend',
                'entry_fee' => 5000,
                'deadline' => date('Y-m-d H:i:s', strtotime('-7 days')),
                'is_active' => 0,
                'status' => 'won',
                'won_at' => date('Y-m-d H:i:s', strtotime('-6 days')),
                'matches_json' => json_encode([
                    [
                        'match_id' => 'PL001',
                        'home_team' => 'Arsenal',
                        'away_team' => 'Chelsea',
                        'match_time' => date('Y-m-d H:i:s', strtotime('-8 days'))
                    ],
                    [
                        'match_id' => 'PL002',
                        'home_team' => 'Manchester United',
                        'away_team' => 'Liverpool',
                        'match_time' => date('Y-m-d H:i:s', strtotime('-8 days'))
                    ],
                    [
                        'match_id' => 'PL003',
                        'home_team' => 'Manchester City',
                        'away_team' => 'Tottenham',
                        'match_time' => date('Y-m-d H:i:s', strtotime('-8 days'))
                    ]
                ]),
                'actual_scores_json' => json_encode([
                    'PL001' => ['home_score' => 2, 'away_score' => 1],
                    'PL002' => ['home_score' => 1, 'away_score' => 1],
                    'PL003' => ['home_score' => 3, 'away_score' => 0]
                ]),
                'prize_description' => '500,000 UGX',
                'created_at' => date('Y-m-d H:i:s', strtotime('-14 days'))
            ],
            [
                'title' => 'Champions League Quarter Finals',
                'entry_fee' => 10000,
                'deadline' => date('Y-m-d H:i:s', strtotime('-3 days')),
                'is_active' => 0,
                'status' => 'won',
                'won_at' => date('Y-m-d H:i:s', strtotime('-2 days')),
                'matches_json' => json_encode([
                    [
                        'match_id' => 'CL001',
                        'home_team' => 'Real Madrid',
                        'away_team' => 'Bayern Munich',
                        'match_time' => date('Y-m-d H:i:s', strtotime('-4 days'))
                    ],
                    [
                        'match_id' => 'CL002',
                        'home_team' => 'Barcelona',
                        'away_team' => 'PSG',
                        'match_time' => date('Y-m-d H:i:s', strtotime('-4 days'))
                    ]
                ]),
                'actual_scores_json' => json_encode([
                    'CL001' => ['home_score' => 2, 'away_score' => 2],
                    'CL002' => ['home_score' => 1, 'away_score' => 3]
                ]),
                'prize_description' => '1,000,000 UGX',
                'created_at' => date('Y-m-d H:i:s', strtotime('-10 days'))
            ],
            [
                'title' => 'La Liga Showdown',
                'entry_fee' => 3000,
                'deadline' => date('Y-m-d H:i:s', strtotime('+2 days')),
                'is_active' => 1,
                'status' => 'active',
                'matches_json' => json_encode([
                    [
                        'match_id' => 'LL001',
                        'home_team' => 'Atletico Madrid',
                        'away_team' => 'Sevilla',
                        'match_time' => date('Y-m-d H:i:s', strtotime('+3 days'))
                    ],
                    [
                        'match_id' => 'LL002',
                        'home_team' => 'Valencia',
                        'away_team' => 'Villarreal',
                        'match_time' => date('Y-m-d H:i:s', strtotime('+3 days'))
                    ]
                ]),
                'prize_description' => '300,000 UGX',
                'created_at' => date('Y-m-d H:i:s', strtotime('-5 days'))
            ]
        ];

        $stackIds = [];
        foreach ($stacks as $stack) {
            $stackIds[] = $this->db->table('stacks')->insert($stack);
        }

        // Create payments with different statuses
        $payments = [
            // Stack 1 - Premier League (Completed)
            ['user_id' => $userIds[0], 'stack_id' => $stackIds[0], 'amount' => 5000, 'transaction_id' => 'TXN20240101001', 'status' => 'success', 'paid_at' => date('Y-m-d H:i:s', strtotime('-9 days'))],
            ['user_id' => $userIds[1], 'stack_id' => $stackIds[0], 'amount' => 5000, 'transaction_id' => 'TXN20240101002', 'status' => 'success', 'paid_at' => date('Y-m-d H:i:s', strtotime('-9 days'))],
            ['user_id' => $userIds[2], 'stack_id' => $stackIds[0], 'amount' => 5000, 'transaction_id' => 'TXN20240101003', 'status' => 'success', 'paid_at' => date('Y-m-d H:i:s', strtotime('-9 days'))],
            ['user_id' => $userIds[3], 'stack_id' => $stackIds[0], 'amount' => 5000, 'transaction_id' => 'TXN20240101004', 'status' => 'success', 'paid_at' => date('Y-m-d H:i:s', strtotime('-9 days'))],
            ['user_id' => $userIds[4], 'stack_id' => $stackIds[0], 'amount' => 5000, 'transaction_id' => 'TXN20240101005', 'status' => 'success', 'paid_at' => date('Y-m-d H:i:s', strtotime('-9 days'))],
            
            // Stack 2 - Champions League (Completed)
            ['user_id' => $userIds[0], 'stack_id' => $stackIds[1], 'amount' => 10000, 'transaction_id' => 'TXN20240102001', 'status' => 'success', 'paid_at' => date('Y-m-d H:i:s', strtotime('-6 days'))],
            ['user_id' => $userIds[1], 'stack_id' => $stackIds[1], 'amount' => 10000, 'transaction_id' => 'TXN20240102002', 'status' => 'success', 'paid_at' => date('Y-m-d H:i:s', strtotime('-6 days'))],
            ['user_id' => $userIds[2], 'stack_id' => $stackIds[1], 'amount' => 10000, 'transaction_id' => 'TXN20240102003', 'status' => 'success', 'paid_at' => date('Y-m-d H:i:s', strtotime('-6 days'))],
            ['user_id' => $userIds[3], 'stack_id' => $stackIds[1], 'amount' => 10000, 'transaction_id' => 'TXN20240102004', 'status' => 'success', 'paid_at' => date('Y-m-d H:i:s', strtotime('-6 days'))],
            
            // Stack 3 - La Liga (Active) - Mixed statuses
            ['user_id' => $userIds[0], 'stack_id' => $stackIds[2], 'amount' => 3000, 'transaction_id' => 'TXN20240103001', 'status' => 'success', 'paid_at' => date('Y-m-d H:i:s', strtotime('-4 days'))],
            ['user_id' => $userIds[1], 'stack_id' => $stackIds[2], 'amount' => 3000, 'transaction_id' => 'TXN20240103002', 'status' => 'success', 'paid_at' => date('Y-m-d H:i:s', strtotime('-4 days'))],
            ['user_id' => $userIds[2], 'stack_id' => $stackIds[2], 'amount' => 3000, 'transaction_id' => 'TXN20240103003', 'status' => 'pending', 'paid_at' => null],
            ['user_id' => $userIds[3], 'stack_id' => $stackIds[2], 'amount' => 3000, 'transaction_id' => 'TXN20240103004', 'status' => 'failed', 'paid_at' => null],
            ['user_id' => $userIds[4], 'stack_id' => $stackIds[2], 'amount' => 3000, 'transaction_id' => 'TXN20240103005', 'status' => 'success', 'paid_at' => date('Y-m-d H:i:s', strtotime('-3 days'))],
            ['user_id' => $userIds[5], 'stack_id' => $stackIds[2], 'amount' => 3000, 'transaction_id' => 'TXN20240103006', 'status' => 'pending', 'paid_at' => null],
        ];

        foreach ($payments as $payment) {
            $this->db->table('payments')->insert($payment);
        }

        // Create predictions for completed stacks
        $predictions = [
            // Stack 1 - Premier League predictions
            [
                'user_id' => $userIds[0],
                'stack_id' => $stackIds[0],
                'predictions_json' => json_encode([
                    ['match_id' => 'PL001', 'home_score' => 2, 'away_score' => 1],
                    ['match_id' => 'PL002', 'home_score' => 1, 'away_score' => 1],
                    ['match_id' => 'PL003', 'home_score' => 3, 'away_score' => 0]
                ]),
                'payment_status' => 'paid',
                'created_at' => date('Y-m-d H:i:s', strtotime('-9 days'))
            ],
            [
                'user_id' => $userIds[1],
                'stack_id' => $stackIds[0],
                'predictions_json' => json_encode([
                    ['match_id' => 'PL001', 'home_score' => 1, 'away_score' => 2],
                    ['match_id' => 'PL002', 'home_score' => 1, 'away_score' => 1],
                    ['match_id' => 'PL003', 'home_score' => 2, 'away_score' => 1]
                ]),
                'payment_status' => 'paid',
                'created_at' => date('Y-m-d H:i:s', strtotime('-9 days'))
            ],
            [
                'user_id' => $userIds[2],
                'stack_id' => $stackIds[0],
                'predictions_json' => json_encode([
                    ['match_id' => 'PL001', 'home_score' => 2, 'away_score' => 1],
                    ['match_id' => 'PL002', 'home_score' => 0, 'away_score' => 2],
                    ['match_id' => 'PL003', 'home_score' => 3, 'away_score' => 0]
                ]),
                'payment_status' => 'paid',
                'created_at' => date('Y-m-d H:i:s', strtotime('-9 days'))
            ],
            [
                'user_id' => $userIds[3],
                'stack_id' => $stackIds[0],
                'predictions_json' => json_encode([
                    ['match_id' => 'PL001', 'home_score' => 1, 'away_score' => 1],
                    ['match_id' => 'PL002', 'home_score' => 1, 'away_score' => 1],
                    ['match_id' => 'PL003', 'home_score' => 2, 'away_score' => 2]
                ]),
                'payment_status' => 'paid',
                'created_at' => date('Y-m-d H:i:s', strtotime('-9 days'))
            ],
            [
                'user_id' => $userIds[4],
                'stack_id' => $stackIds[0],
                'predictions_json' => json_encode([
                    ['match_id' => 'PL001', 'home_score' => 0, 'away_score' => 0],
                    ['match_id' => 'PL002', 'home_score' => 2, 'away_score' => 1],
                    ['match_id' => 'PL003', 'home_score' => 1, 'away_score' => 1]
                ]),
                'payment_status' => 'paid',
                'created_at' => date('Y-m-d H:i:s', strtotime('-9 days'))
            ],

            // Stack 2 - Champions League predictions
            [
                'user_id' => $userIds[0],
                'stack_id' => $stackIds[1],
                'predictions_json' => json_encode([
                    ['match_id' => 'CL001', 'home_score' => 2, 'away_score' => 2],
                    ['match_id' => 'CL002', 'home_score' => 1, 'away_score' => 3]
                ]),
                'payment_status' => 'paid',
                'created_at' => date('Y-m-d H:i:s', strtotime('-6 days'))
            ],
            [
                'user_id' => $userIds[1],
                'stack_id' => $stackIds[1],
                'predictions_json' => json_encode([
                    ['match_id' => 'CL001', 'home_score' => 1, 'away_score' => 1],
                    ['match_id' => 'CL002', 'home_score' => 2, 'away_score' => 2]
                ]),
                'payment_status' => 'paid',
                'created_at' => date('Y-m-d H:i:s', strtotime('-6 days'))
            ],
            [
                'user_id' => $userIds[2],
                'stack_id' => $stackIds[1],
                'predictions_json' => json_encode([
                    ['match_id' => 'CL001', 'home_score' => 2, 'away_score' => 2],
                    ['match_id' => 'CL002', 'home_score' => 0, 'away_score' => 1]
                ]),
                'payment_status' => 'paid',
                'created_at' => date('Y-m-d H:i:s', strtotime('-6 days'))
            ],
            [
                'user_id' => $userIds[3],
                'stack_id' => $stackIds[1],
                'predictions_json' => json_encode([
                    ['match_id' => 'CL001', 'home_score' => 3, 'away_score' => 1],
                    ['match_id' => 'CL002', 'home_score' => 1, 'away_score' => 3]
                ]),
                'payment_status' => 'paid',
                'created_at' => date('Y-m-d H:i:s', strtotime('-6 days'))
            ],

            // Stack 3 - La Liga predictions (only for successful payments)
            [
                'user_id' => $userIds[0],
                'stack_id' => $stackIds[2],
                'predictions_json' => json_encode([
                    ['match_id' => 'LL001', 'home_score' => 2, 'away_score' => 1],
                    ['match_id' => 'LL002', 'home_score' => 1, 'away_score' => 1]
                ]),
                'payment_status' => 'paid',
                'created_at' => date('Y-m-d H:i:s', strtotime('-4 days'))
            ],
            [
                'user_id' => $userIds[1],
                'stack_id' => $stackIds[2],
                'predictions_json' => json_encode([
                    ['match_id' => 'LL001', 'home_score' => 1, 'away_score' => 0],
                    ['match_id' => 'LL002', 'home_score' => 2, 'away_score' => 2]
                ]),
                'payment_status' => 'paid',
                'created_at' => date('Y-m-d H:i:s', strtotime('-4 days'))
            ],
            [
                'user_id' => $userIds[4],
                'stack_id' => $stackIds[2],
                'predictions_json' => json_encode([
                    ['match_id' => 'LL001', 'home_score' => 0, 'away_score' => 0],
                    ['match_id' => 'LL002', 'home_score' => 1, 'away_score' => 2]
                ]),
                'payment_status' => 'paid',
                'created_at' => date('Y-m-d H:i:s', strtotime('-3 days'))
            ],
        ];

        foreach ($predictions as $prediction) {
            $this->db->table('predictions')->insert($prediction);
        }

        // Create scores for completed stacks
        $scores = [
            // Stack 1 - Premier League scores
            [
                'user_id' => $userIds[0],
                'stack_id' => $stackIds[0],
                'total_points' => 9,
                'exact_count' => 3,
                'outcome_count' => 0,
                'wrong_count' => 0
            ],
            [
                'user_id' => $userIds[1],
                'stack_id' => $stackIds[0],
                'total_points' => 4,
                'exact_count' => 1,
                'outcome_count' => 1,
                'wrong_count' => 1
            ],
            [
                'user_id' => $userIds[2],
                'stack_id' => $stackIds[0],
                'total_points' => 6,
                'exact_count' => 2,
                'outcome_count' => 0,
                'wrong_count' => 1
            ],
            [
                'user_id' => $userIds[3],
                'stack_id' => $stackIds[0],
                'total_points' => 4,
                'exact_count' => 1,
                'outcome_count' => 1,
                'wrong_count' => 1
            ],
            [
                'user_id' => $userIds[4],
                'stack_id' => $stackIds[0],
                'total_points' => 1,
                'exact_count' => 0,
                'outcome_count' => 1,
                'wrong_count' => 2
            ],

            // Stack 2 - Champions League scores
            [
                'user_id' => $userIds[0],
                'stack_id' => $stackIds[1],
                'total_points' => 6,
                'exact_count' => 2,
                'outcome_count' => 0,
                'wrong_count' => 0
            ],
            [
                'user_id' => $userIds[1],
                'stack_id' => $stackIds[1],
                'total_points' => 2,
                'exact_count' => 0,
                'outcome_count' => 2,
                'wrong_count' => 0
            ],
            [
                'user_id' => $userIds[2],
                'stack_id' => $stackIds[1],
                'total_points' => 4,
                'exact_count' => 1,
                'outcome_count' => 1,
                'wrong_count' => 0
            ],
            [
                'user_id' => $userIds[3],
                'stack_id' => $stackIds[1],
                'total_points' => 4,
                'exact_count' => 1,
                'outcome_count' => 1,
                'wrong_count' => 0
            ],
        ];

        foreach ($scores as $score) {
            $this->db->table('scores')->insert($score);
        }

        // Create winners
        $winners = [
            // Stack 1 - Premier League winners (John Doe got perfect score)
            [
                'user_id' => $userIds[0],
                'stack_id' => $stackIds[0],
                'win_type' => 'full-correct',
                'awarded_at' => date('Y-m-d H:i:s', strtotime('-6 days'))
            ],
            [
                'user_id' => $userIds[2],
                'stack_id' => $stackIds[0],
                'win_type' => 'top-score',
                'awarded_at' => date('Y-m-d H:i:s', strtotime('-6 days'))
            ],
            [
                'user_id' => $userIds[1],
                'stack_id' => $stackIds[0],
                'win_type' => 'top-score',
                'awarded_at' => date('Y-m-d H:i:s', strtotime('-6 days'))
            ],

            // Stack 2 - Champions League winners (John Doe got perfect score again)
            [
                'user_id' => $userIds[0],
                'stack_id' => $stackIds[1],
                'win_type' => 'full-correct',
                'awarded_at' => date('Y-m-d H:i:s', strtotime('-3 days'))
            ],
            [
                'user_id' => $userIds[2],
                'stack_id' => $stackIds[1],
                'win_type' => 'top-score',
                'awarded_at' => date('Y-m-d H:i:s', strtotime('-3 days'))
            ],
            [
                'user_id' => $userIds[3],
                'stack_id' => $stackIds[1],
                'win_type' => 'top-score',
                'awarded_at' => date('Y-m-d H:i:s', strtotime('-3 days'))
            ],
        ];

        foreach ($winners as $winner) {
            $this->db->table('winners')->insert($winner);
        }

        echo "Test data seeded successfully!\n";
        echo "Created:\n";
        echo "- " . count($users) . " users\n";
        echo "- " . count($stacks) . " stacks (2 completed, 1 active)\n";
        echo "- " . count($payments) . " payments (success, pending, failed)\n";
        echo "- " . count($predictions) . " predictions\n";
        echo "- " . count($scores) . " scores\n";
        echo "- " . count($winners) . " winners\n";
        echo "\nTest Users:\n";
        foreach ($users as $index => $user) {
            echo "- " . $user['full_name'] . " (" . $user['phone'] . ") - PIN: 1234\n";
        }
    }
} 