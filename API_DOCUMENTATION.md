# Score Predictor API Documentation

## Base URL
```
http://localhost/predictor/public/api
```

## Authentication

### Register User
**POST** `/auth/register`

Register a new user with phone number and PIN.

**Request Body:**
```json
{
  "full_name": "John Doe",
  "phone": "1234567890",
  "pin": "1234"
}
```

**Response:**
```json
{
  "status": "success",
  "message": "User registered successfully",
  "data": {
    "id": 1,
    "full_name": "John Doe",
    "phone": "1234567890",
    "created_at": "2024-01-01 12:00:00"
  }
}
```

### Login User
**POST** `/auth/login`

Authenticate user with phone and PIN.

**Request Body:**
```json
{
  "phone": "1234567890",
  "pin": "1234"
}
```

**Response:**
```json
{
  "status": "success",
  "message": "Login successful",
  "data": {
    "id": 1,
    "full_name": "John Doe",
    "phone": "1234567890",
    "created_at": "2024-01-01 12:00:00"
  }
}
```

## Stacks

### Get All Active Stacks
**GET** `/stacks`

Get all active stacks available for predictions.

**Response:**
```json
{
  "status": "success",
  "data": [
    {
      "id": 1,
      "title": "Premier League Weekend",
      "prize_description": "Win $1000 for perfect predictions",
      "entry_fee": "5.00",
      "deadline": "2024-01-15 19:00:00",
      "is_active": true,
      "matches": [
        {
          "match_id": "PL001",
          "home_team": "Manchester United",
          "away_team": "Liverpool",
          "match_time": "2024-01-15 20:00:00"
        }
      ],
      "participant_count": 25,
      "time_remaining": "2d 5h 30m"
    }
  ]
}
```

### Get Stack Details
**GET** `/stacks/{id}`

Get detailed information about a specific stack.

**Response:**
```json
{
  "status": "success",
  "data": {
    "id": 1,
    "title": "Premier League Weekend",
    "prize_description": "Win $1000 for perfect predictions",
    "entry_fee": "5.00",
    "deadline": "2024-01-15 19:00:00",
    "is_active": true,
    "matches": [...],
    "participant_count": 25,
    "time_remaining": "2d 5h 30m",
    "is_open": true
  }
}
```

### Get Stack with User Status
**GET** `/stacks/{stackId}/user/{userId}`

Get stack details with user's prediction and payment status.

**Response:**
```json
{
  "status": "success",
  "data": {
    "id": 1,
    "title": "Premier League Weekend",
    "matches": [...],
    "user_status": {
      "has_paid": true,
      "has_predicted": true,
      "prediction": {
        "id": 1,
        "predictions": [
          {
            "match_id": "PL001",
            "home_score": 2,
            "away_score": 1
          }
        ]
      },
      "score": null
    }
  }
}
```

## Predictions

### Submit Prediction
**POST** `/predictions/submit`

Submit predictions for a stack.

**Request Body:**
```json
{
  "user_id": 1,
  "stack_id": 1,
  "predictions": [
    {
      "match_id": "PL001",
      "home_score": 2,
      "away_score": 1
    },
    {
      "match_id": "PL002",
      "home_score": 0,
      "away_score": 0
    }
  ]
}
```

**Response:**
```json
{
  "status": "success",
  "message": "Prediction submitted successfully",
  "data": {
    "prediction_id": 1,
    "stack_id": 1,
    "user_id": 1
  }
}
```

### Get User's Prediction
**GET** `/predictions/user/{userId}/stack/{stackId}`

Get user's prediction for a specific stack.

**Response:**
```json
{
  "status": "success",
  "data": {
    "id": 1,
    "user_id": 1,
    "stack_id": 1,
    "predictions": [
      {
        "match_id": "PL001",
        "home_score": 2,
        "away_score": 1
      }
    ],
    "created_at": "2024-01-01 12:00:00"
  }
}
```

## Payments

### Initialize Payment
**POST** `/payments/initialize`

Initialize a payment for a stack.

**Request Body:**
```json
{
  "user_id": 1,
  "stack_id": 1
}
```

**Response:**
```json
{
  "status": "success",
  "message": "Payment initialized",
  "data": {
    "payment_id": 1,
    "transaction_id": "TXN202401011200001234",
    "amount": "5.00",
    "stack_title": "Premier League Weekend"
  }
}
```

### Check Payment Status
**GET** `/payments/check/{userId}/{stackId}`

Check if user has paid for a stack.

**Response:**
```json
{
  "status": "success",
  "data": {
    "has_paid": true,
    "payment_details": {
      "id": 1,
      "status": "success",
      "amount": "5.00",
      "transaction_id": "TXN202401011200001234"
    }
  }
}
```

## Scores

### Get Leaderboard
**GET** `/scores/leaderboard/{stackId}`

Get leaderboard for a specific stack.

**Response:**
```json
{
  "status": "success",
  "data": {
    "stack": {
      "id": 1,
      "title": "Premier League Weekend",
      "is_active": false
    },
    "leaderboard": [
      {
        "id": 1,
        "user_id": 1,
        "stack_id": 1,
        "exact_count": 2,
        "outcome_count": 1,
        "wrong_count": 0,
        "total_points": 7,
        "full_name": "John Doe",
        "phone": "1234567890"
      }
    ]
  }
}
```

### Get User's Score
**GET** `/scores/user/{userId}/stack/{stackId}`

Get user's score for a specific stack.

**Response:**
```json
{
  "status": "success",
  "data": {
    "id": 1,
    "user_id": 1,
    "stack_id": 1,
    "exact_count": 2,
    "outcome_count": 1,
    "wrong_count": 0,
    "total_points": 7,
    "ranking": 1,
    "has_perfect_score": true
  }
}
```

### Calculate Scores
**POST** `/scores/calculate/{stackId}`

Calculate scores for all predictions in a stack (admin function).

**Response:**
```json
{
  "status": "success",
  "message": "Scores calculated for 25 predictions",
  "data": {
    "stack_id": 1,
    "predictions_processed": 25
  }
}
```

### Award Winners
**POST** `/scores/award-winners/{stackId}`

Award winners for a completed stack (admin function).

**Response:**
```json
{
  "status": "success",
  "message": "Winners awarded successfully",
  "data": {
    "stack_id": 1,
    "perfect_winners": 2,
    "top_score_winners": 3,
    "total_winners": 5
  }
}
```

### Get Winners
**GET** `/scores/winners/{stackId}`

Get winners for a stack.

**Response:**
```json
{
  "status": "success",
  "data": {
    "winners": [
      {
        "id": 1,
        "user_id": 1,
        "stack_id": 1,
        "win_type": "full-correct",
        "awarded_at": "2024-01-15 20:00:00",
        "full_name": "John Doe",
        "phone": "1234567890"
      }
    ],
    "statistics": {
      "total_winners": 5,
      "perfect_winners": 2,
      "top_score_winners": 3
    }
  }
}
```

## Error Responses

### Validation Error
```json
{
  "status": "error",
  "message": "Validation failed",
  "errors": {
    "phone": "Phone number already registered",
    "pin": "PIN must be at least 4 digits"
  }
}
```

### Not Found Error
```json
{
  "status": "error",
  "message": "Stack not found"
}
```

### Server Error
```json
{
  "status": "error",
  "message": "Internal server error"
}
```

## Testing the API

### Using cURL

**Register a user:**
```bash
curl -X POST http://localhost/predictor/public/api/auth/register \
  -H "Content-Type: application/json" \
  -d '{
    "full_name": "Test User",
    "phone": "1234567890",
    "pin": "1234"
  }'
```

**Get active stacks:**
```bash
curl -X GET http://localhost/predictor/public/api/stacks
```

**Submit prediction:**
```bash
curl -X POST http://localhost/predictor/public/api/predictions/submit \
  -H "Content-Type: application/json" \
  -d '{
    "user_id": 1,
    "stack_id": 1,
    "predictions": [
      {
        "match_id": "PL001",
        "home_score": 2,
        "away_score": 1
      }
    ]
  }'
```

### Using Postman

1. Import the collection
2. Set the base URL to `http://localhost/predictor/public/api`
3. Use the provided examples for request bodies
4. Test each endpoint

## Rate Limiting

Currently, there are no rate limits implemented. For production, consider implementing:
- Request rate limiting per IP
- User-based rate limiting
- API key authentication for admin endpoints

## Security Notes

- All PINs are hashed before storage
- Input validation is performed on all endpoints
- SQL injection protection is built-in
- Consider implementing JWT tokens for production use 