<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use App\Database\Seeds\TestDataSeeder;

class SeedTestData extends BaseCommand
{
    protected $group       = 'Database';
    protected $name        = 'seed:test';
    protected $description = 'Seed the database with test data for development';

    public function run(array $params)
    {
        CLI::write('Seeding test data...', 'yellow');
        
        try {
            $seeder = new TestDataSeeder();
            $seeder->run();
            
            CLI::write('Test data seeded successfully!', 'green');
            CLI::write('');
            CLI::write('Test Users (PIN: 1234):', 'cyan');
            CLI::write('- John Doe (256701234567)', 'white');
            CLI::write('- Jane Smith (256702345678)', 'white');
            CLI::write('- Mike Johnson (256703456789)', 'white');
            CLI::write('- Sarah Wilson (256704567890)', 'white');
            CLI::write('- David Brown (256705678901)', 'white');
            CLI::write('- Emma Davis (256706789012)', 'white');
            CLI::write('');
            CLI::write('Created:', 'cyan');
            CLI::write('- 6 users', 'white');
            CLI::write('- 3 stacks (2 completed, 1 active)', 'white');
            CLI::write('- 15 payments (success, pending, failed)', 'white');
            CLI::write('- 11 predictions', 'white');
            CLI::write('- 9 scores', 'white');
            CLI::write('- 6 winners', 'white');
            
        } catch (\Exception $e) {
            CLI::error('Failed to seed test data: ' . $e->getMessage());
        }
    }
} 