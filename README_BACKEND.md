# Score Predictor App Backend

A CodeIgniter 4-based REST API backend for a score prediction application where users can predict match outcomes and compete for prizes.

## Features

- **User Authentication**: Phone number and PIN-based authentication
- **Stack Management**: Create and manage prediction stacks with multiple matches
- **Prediction System**: Submit predictions for match outcomes
- **Payment Integration**: Track payments for paid stacks
- **Scoring System**: Calculate scores based on exact predictions and outcomes
- **Leaderboards**: Real-time rankings and statistics
- **Winner Management**: Award winners for perfect scores and top performers

## Database Schema

### Tables

1. **users** - User information with PIN authentication
2. **stacks** - Prediction stacks containing matches and prizes
3. **payments** - Payment tracking for stack entries
4. **predictions** - User predictions stored as JSON
5. **scores** - Calculated scores and statistics
6. **winners** - Winner tracking and prize distribution

## Installation

### Prerequisites

- PHP 8.0 or higher
- MySQL 5.7 or higher
- Composer
- Web server (Apache/Nginx)

### Setup Steps

1. **Clone the repository**
   ```bash
   git clone <repository-url>
   cd predictor
   ```

2. **Install dependencies**
   ```bash
   composer install
   ```

3. **Configure environment**
   ```bash
   # Copy the environment file
   cp env_sample.txt .env
   
   # Edit .env with your database credentials
   nano .env
   ```

4. **Create database**
   ```sql
   CREATE DATABASE score_predictor CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   ```

5. **Run migrations**
   ```bash
   php spark migrate
   ```

6. **Seed test data (optional)**
   ```bash
   php spark db:seed TestDataSeeder
   ```

## API Endpoints

### Authentication

- `POST /api/auth/register` - Register new user
- `POST /api/auth/login` - User login
- `GET /api/auth/profile/{id}` - Get user profile
- `PUT /api/auth/profile/{id}` - Update user profile

### Stacks

- `GET /api/stacks` - Get all active stacks
- `GET /api/stacks/{id}` - Get specific stack details
- `GET /api/stacks/{id}/user/{userId}` - Get stack with user status
- `GET /api/stacks/{id}/leaderboard` - Get stack leaderboard
- `GET /api/stacks/user/{userId}` - Get user's stacks

### Predictions

- `POST /api/predictions/submit` - Submit prediction
- `GET /api/predictions/user/{userId}/stack/{stackId}` - Get user's prediction
- `GET /api/predictions/stack/{stackId}` - Get all predictions for stack
- `GET /api/predictions/user/{userId}` - Get user's prediction history
- `POST /api/predictions/calculate/{stackId}` - Calculate scores for stack

### Payments

- `POST /api/payments/initialize` - Initialize payment
- `GET /api/payments/verify/{transactionId}` - Verify payment status
- `POST /api/payments/update-status` - Update payment status (webhook)
- `GET /api/payments/user/{userId}` - Get user's payment history
- `GET /api/payments/stack/{stackId}` - Get stack payments
- `GET /api/payments/check/{userId}/{stackId}` - Check if user paid

### Scores

- `GET /api/scores/leaderboard/{stackId}` - Get stack leaderboard
- `GET /api/scores/user/{userId}/stack/{stackId}` - Get user's score
- `GET /api/scores/stack/{stackId}` - Get all scores for stack
- `POST /api/scores/calculate/{stackId}` - Calculate scores
- `POST /api/scores/award-winners/{stackId}` - Award winners
- `GET /api/scores/winners/{stackId}` - Get stack winners
- `GET /api/scores/ranking/{userId}/{stackId}` - Get user's ranking
- `GET /api/scores/stats/{userId}` - Get user's statistics

## Data Formats

### User Registration
```json
{
  "full_name": "John Doe",
  "phone": "1234567890",
  "pin": "1234"
}
```

### Stack Creation (Admin)
```json
{
  "title": "Premier League Weekend",
  "prize_description": "Win $1000 for perfect predictions",
  "entry_fee": 5.00,
  "matches_json": [
    {
      "match_id": "PL001",
      "home_team": "Manchester United",
      "away_team": "Liverpool",
      "match_time": "2024-01-15 20:00:00"
    }
  ],
  "deadline": "2024-01-15 19:00:00"
}
```

### Prediction Submission
```json
{
  "user_id": 1,
  "stack_id": 1,
  "predictions": [
    {
      "match_id": "PL001",
      "home_score": 2,
      "away_score": 1
    }
  ]
}
```

## Scoring System

- **Exact Score**: 3 points (correct home and away score)
- **Correct Outcome**: 1 point (correct win/lose/draw)
- **Wrong Prediction**: 0 points

## Winner Categories

1. **Perfect Score Winners** (`full-correct`): Users who get all predictions exactly right
2. **Top Score Winners** (`top-score`): Top 3 performers (excluding perfect score winners)

## Development

### Running the application
```bash
# Start development server
php spark serve

# The API will be available at http://localhost:8080/api/
```

### Testing
```bash
# Run tests
php spark test
```

### Database operations
```bash
# Create new migration
php spark make:migration CreateNewTable

# Run migrations
php spark migrate

# Rollback migrations
php spark migrate:rollback

# Create seeder
php spark make:seeder NewSeeder

# Run seeders
php spark db:seed
```

## Security Considerations

- PINs are hashed using PHP's `password_hash()` function
- Input validation on all endpoints
- SQL injection protection through CodeIgniter's query builder
- XSS protection through output escaping

## Deployment

1. Set `CI_ENVIRONMENT = production` in `.env`
2. Configure your web server to point to the `public/` directory
3. Set up proper database credentials
4. Generate a strong encryption key
5. Configure SSL certificates for HTTPS

## Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Add tests if applicable
5. Submit a pull request

## License

This project is licensed under the MIT License. 