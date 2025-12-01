<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Subscription Expiry Reminder</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }

        .header {
            background-color: #f8f9fa;
            padding: 20px;
            text-align: center;
            border-radius: 5px;
            margin-bottom: 20px;
        }

        .content {
            background-color: #fff;
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }

        .warning {
            background-color: #fff3cd;
            border: 1px solid #ffeaa7;
            color: #856404;
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
        }

        .button {
            display: inline-block;
            background-color: #007bff;
            color: white;
            padding: 12px 24px;
            text-decoration: none;
            border-radius: 5px;
            margin: 20px 0;
        }

        .footer {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
            font-size: 12px;
            color: #666;
        }
    </style>
</head>

<body>
    <div class="header">
        <h1>Subscription Expiry Reminder</h1>
    </div>

    <div class="content">
        <p>Dear {{ $admin->name }},</p>

        <p>This is a reminder that your subscription for <strong>{{ $company->name }}</strong> will expire in
            <strong>{{ $daysUntilExpiry }} days</strong>.
        </p>

        <div class="warning">
            <strong>Important:</strong> If your subscription expires, all users in your company will be locked out of
            the system until payment is made.
        </div>

        <h3>Subscription Details:</h3>
        <ul>
            <li><strong>Plan:</strong> {{ $subscription->plan_name }}</li>
            <li><strong>Amount:</strong> {{ number_format($subscription->amount, 2) }} {{ $subscription->currency }}
            </li>
            <li><strong>Billing Cycle:</strong> {{ ucfirst($subscription->billing_cycle) }}</li>
            <li><strong>Start Date:</strong> {{ $subscription->start_date->format('M d, Y') }}</li>
            <li><strong>End Date:</strong> {{ $subscription->end_date->format('M d, Y') }}</li>
        </ul>

        <p>To avoid service interruption, please renew your subscription as soon as possible.</p>

        <a href="{{ url('/subscriptions/dashboard') }}" class="button">Manage Subscription</a>

        <p>If you have any questions or need assistance, please contact our support team.</p>

        <p>Best regards,<br>
            SmartFinance Support Team</p>
    </div>

    <div class="footer">
        <p>This is an automated message. Please do not reply to this email.</p>
        <p>&copy; {{ date('Y') }} SmartFinance. All rights reserved.</p>
    </div>
</body>

</html>