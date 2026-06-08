# Airtel Money API Integration Guide

## Overview
This legal system now supports Airtel Money API for secure mobile money payments. Clients can pay for legal services directly through their Airtel Money account.

## Features

### Payment Processing
- **Make Payments**: Clients can pay any amount via Airtel Money
- **Payment History**: Track all payments and transaction status
- **Real-time Status**: Check payment confirmation status instantly
- **Webhook Support**: Automatic payment status updates from Airtel

### Payment Methods
- Airtel Money (Mobile money)
- Credit Card
- Debit Card
- Bank Account

## Setup Instructions

### 1. Get Airtel Money API Credentials

1. Visit [Airtel Money Merchant Dashboard](https://merchant.airtel.ug)
2. Register as a merchant or sign in
3. Generate API credentials:
   - Client ID
   - Client Secret
   - API Key
4. Configure webhook URL

### 2. Update Configuration

Edit `config/airtel_config.php` and replace placeholders:

```php
'client_id' => 'YOUR_ACTUAL_CLIENT_ID',
'client_secret' => 'YOUR_ACTUAL_CLIENT_SECRET',
'api_key' => 'YOUR_ACTUAL_API_KEY',
'webhook_secret' => 'YOUR_WEBHOOK_SECRET'
```

Or use environment variables:
```bash
AIRTEL_CLIENT_ID=your_client_id
AIRTEL_CLIENT_SECRET=your_client_secret
AIRTEL_API_KEY=your_api_key
AIRTEL_WEBHOOK_SECRET=your_webhook_secret
```

### 3. Configure Webhook in Airtel Dashboard

1. Go to your Merchant Dashboard
2. Find Webhook/Callback settings
3. Set webhook URL to: `https://yourdomain.com/legal_system/airtel_webhook.php`
4. Select events: Payment Completed, Payment Failed
5. Copy the webhook secret and update config

### 4. Database Setup

The payment tables are auto-created on first access via `includes/db.php`:

- `payments`: Stores all payment transactions
- `payment_methods`: Stores saved payment methods (cards, bank accounts)

## API Endpoints

### Client-Facing Pages

- **Make Payment**: `/make_payment.php`
  - Create a new payment
  - Support for all payment methods
  - Airtel Money integration

- **Payment Status**: `/payment_status.php?id=<payment_id>`
  - Check payment confirmation
  - Auto-refresh for processing payments
  - Transaction details

- **Payment History**: `/payment_history.php`
  - View all payments
  - Filter by status
  - Payment statistics

- **Payment Methods**: `/client_payment_methods.php`
  - Manage saved payment methods
  - Set default payment method
  - Add/remove cards and bank accounts

- **Add Payment Method**: `/add_payment_method.php`
  - Add credit/debit card
  - Add bank account
  - Set as default

### Webhook Endpoint

- **Airtel Webhook**: `/airtel_webhook.php`
  - Receives payment status callbacks
  - Validates requests
  - Updates payment records
  - Logs all callbacks

## Testing

### Sandbox Mode (Development)

By default, the integration runs in sandbox mode (`sandbox_mode = true` in config). This allows you to test without processing real payments.

**Test Phone Numbers** (provided by Airtel):
- Format: `0975123456` or `260975123456`
- Airtel will process test transactions immediately

### Production Mode

To switch to production:
1. Update `config/airtel_config.php`:
   ```php
   'sandbox_mode' => false
   ```
2. This changes the API endpoint to production servers
3. Real money will be processed

## Security Features

- Encrypted payment data in database
- Last 4 digits only displayed for cards
- Full account masking for bank accounts
- Transaction ID uniqueness enforcement
- Webhook signature validation
- HTTPS recommended for all payment pages

## Phone Number Formats Supported

The system automatically converts these formats:
- Zambian local: `0975123456` → `260975123456`
- International: `260975123456` → `260975123456`
- Alternative formats with spaces/dashes are cleaned

## Error Handling

- Network timeouts: Payment marked as failed
- Invalid credentials: Clear error messages
- Duplicate transactions: Prevented via unique constraint
- Webhook failures: Logged for investigation

## Logging

Payment webhooks are logged to: `/logs/airtel_webhook.log`

Each webhook entry includes:
- Timestamp
- Full request body
- Action taken
- Database changes

## Troubleshooting

### Issue: "Failed to authenticate with Airtel Money"
- Check Client ID and Client Secret in config
- Verify internet connectivity (curl required)
- Ensure credentials have sandbox access

### Issue: "Payment marked as pending forever"
- Check if webhook is configured correctly
- Verify webhook URL is publicly accessible
- Check `logs/airtel_webhook.log` for errors
- Manually retry via payment_status.php

### Issue: "Phone number validation failed"
- Ensure phone number is valid Zambian format
- Try international format: 260975123456
- Remove any spaces or special characters

## Production Checklist

- [ ] Update config with production credentials
- [ ] Set `sandbox_mode = false`
- [ ] Configure webhook URL in Airtel Dashboard
- [ ] Test end-to-end payment with real account
- [ ] Set up automated logs rotation
- [ ] Configure HTTPS/SSL certificate
- [ ] Set up payment confirmation emails
- [ ] Train staff on payment processing
- [ ] Document refund procedures

## Support

For issues with:
- **Airtel Money API**: Contact [Airtel Developer Support](https://developer.airtel.ug)
- **Payment Integration**: Check `/logs/airtel_webhook.log`
- **Database Issues**: Verify `payments` table structure

## File Structure

```
includes/
  ├── AirtelMoneyAPI.php          # API client class
  └── db.php                       # Auto-creates tables

config/
  └── airtel_config.php            # API credentials

make_payment.php                   # Payment form
payment_status.php                 # Status checker
payment_history.php                # Transaction history
airtel_webhook.php                 # Webhook handler
client_payment_methods.php         # Payment method manager
add_payment_method.php             # Add new payment method

logs/
  └── airtel_webhook.log          # Webhook request logs
```

---
Last Updated: June 8, 2026
