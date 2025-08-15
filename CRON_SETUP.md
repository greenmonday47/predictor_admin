# Cron Job Setup for Top-Up Transactions

This document explains how to set up the cron job to automatically check pending top-up transactions and update their status.

## Overview

The system includes a cron job endpoint that checks all pending top-up transactions and updates their status based on the GM Pay API response. When a payment is successful, the user's wallet is automatically credited.

## Cron Job Endpoint

**URL:** `https://yourdomain.com/cron/check-pending-topups`

**Method:** GET

**Purpose:** Check all pending top-up transactions and update their status

## Setting Up the Cron Job

### Option 1: Using cPanel Cron Jobs

1. Log in to your cPanel account
2. Navigate to "Cron Jobs" in the Advanced section
3. Add a new cron job with the following settings:
   - **Common Settings:** Every Minute
   - **Command:** `curl -s "https://yourdomain.com/cron/check-pending-topups" > /dev/null 2>&1`

### Option 2: Using Linux/Unix Cron

1. Open your crontab:
   ```bash
   crontab -e
   ```

2. Add the following line to run every minute:
   ```bash
   * * * * * curl -s "https://yourdomain.com/cron/check-pending-topups" > /dev/null 2>&1
   ```

### Option 3: Using Windows Task Scheduler

1. Open Task Scheduler
2. Create a new Basic Task
3. Set the trigger to run every minute
4. Set the action to start a program
5. Program: `curl`
6. Arguments: `-s "https://yourdomain.com/cron/check-pending-topups"`

## Health Check Endpoint

**URL:** `https://yourdomain.com/cron/health`

**Method:** GET

**Purpose:** Verify that the cron service is accessible

**Response:**
```json
{
    "status": "success",
    "message": "Cron service is running",
    "timestamp": "2024-01-01 12:00:00",
    "server_time": 1704110400
}
```

## How It Works

1. **Every minute**, the cron job calls the endpoint
2. The system fetches all pending top-up transactions
3. For each transaction, it calls the GM Pay status API
4. If the status has changed:
   - Updates the transaction status in the database
   - If successful, credits the user's wallet
   - If failed, marks the transaction as failed
5. Logs the results for monitoring

## Monitoring

### Logs

The cron job logs its activity to the CodeIgniter log files:
- **Location:** `writable/logs/`
- **Success:** `Cron job processed X top-ups: Y successful, Z failed`
- **Errors:** `Cron job failed: [error message]`

### Manual Testing

You can manually test the cron job by visiting:
```
https://yourdomain.com/cron/check-pending-topups
```

Expected response:
```json
{
    "status": "success",
    "message": "Pending top-ups processed",
    "data": {
        "processed": 5,
        "successful": 2,
        "failed": 1,
        "timestamp": "2024-01-01 12:00:00"
    }
}
```

## Troubleshooting

### Common Issues

1. **Cron job not running:**
   - Check if the URL is accessible
   - Verify server permissions
   - Check server logs for errors

2. **Transactions not updating:**
   - Verify GM Pay API is accessible
   - Check network connectivity
   - Review application logs

3. **Wallet not being credited:**
   - Check if the wallet model is working
   - Verify database connections
   - Review transaction logs

### Testing Commands

Test the health endpoint:
```bash
curl "https://yourdomain.com/cron/health"
```

Test the cron job manually:
```bash
curl "https://yourdomain.com/cron/check-pending-topups"
```

## Security Considerations

- The cron endpoints are publicly accessible
- Consider adding authentication if needed
- Monitor for abuse or excessive requests
- Consider rate limiting for the endpoints

## Alternative: Webhook Integration

Instead of polling, you can also set up webhooks with GM Pay to receive real-time status updates. This would require:

1. Implementing a webhook endpoint in your application
2. Configuring GM Pay to send webhooks to your endpoint
3. Processing webhook data to update transaction status

This approach would be more efficient than polling every minute. 