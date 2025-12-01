<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Subscription Expired</title>
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
            background-color: #dc3545;
            color: white;
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

        .alert {
            background-color: #f8d7da;
            border: 1px solid #f5c6cb;
            color: #721c24;
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
        }

        .button {
            display: inline-block;
            background-color: #28a745;
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
        <h1>Subscription Expired</h1>
    </div>

    <div class="content">
        <p>Dear {{ $admin->name }},</p>

        <div class="alert">
            <strong>URGENT:</strong> Your subscription for <strong>{{ $company->name }}</strong> has expired and all
            users have been locked out of the system.
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

        <p><strong>What happened:</strong></p>
        <ul>
            <li>Your subscription expired on {{ $subscription->end_date->format('M d, Y') }}</li>
            <li>All users in your company have been automatically locked out</li>
            <li>No one can access the system until payment is made</li>
        </ul>

        <p><strong>To restore access:</strong></p>
        <ol>
            <li>Make payment for your subscription</li>
            <li>Mark the subscription as paid in the system</li>
            <li>All users will be automatically unlocked</li>
        </ol>

        <a href="{{ url('/subscriptions/dashboard') }}" class="button">Renew Subscription Now</a>

        <p>If you have any questions or need immediate assistance, please contact our support team.</p>

        <p>Best regards,<br>
            SmartFinance Support Team</p>
    </div>

    <div class="footer">
        <p>This is an automated message. Please do not reply to this email.</p>
        <p>&copy; {{ date('Y') }} SmartFinance. All rights reserved.</p>
    </div>
</body>

</html>