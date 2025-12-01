<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Subscription Activated</title>
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
            background-color: #28a745;
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

        .success {
            background-color: #d4edda;
            border: 1px solid #c3e6cb;
            color: #155724;
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
        <h1>Subscription Activated</h1>
    </div>

    <div class="content">
        <p>Dear {{ $admin->name }},</p>

        <div class="success">
            <strong>Great news!</strong> Your subscription for <strong>{{ $company->name }}</strong> has been
            successfully activated and all users have been unlocked.
        </div>

        <h3>Subscription Details:</h3>
        <ul>
            <li><strong>Plan:</strong> {{ $subscription->plan_name }}</li>
            <li><strong>Amount:</strong> {{ number_format($subscription->amount, 2) }} {{ $subscription->currency }}
            </li>
            <li><strong>Billing Cycle:</strong> {{ ucfirst($subscription->billing_cycle) }}</li>
            <li><strong>Start Date:</strong> {{ $subscription->start_date->format('M d, Y') }}</li>
            <li><strong>End Date:</strong> {{ $subscription->end_date->format('M d, Y') }}</li>
            <li><strong>Payment Status:</strong> {{ ucfirst($subscription->payment_status) }}</li>
            @if($subscription->payment_date)
                <li><strong>Payment Date:</strong> {{ $subscription->payment_date->format('M d, Y H:i') }}</li>
            @endif
        </ul>

        <p><strong>What's been restored:</strong></p>
        <ul>
            <li>Full access to the SmartFinance system</li>
            <li>All users in your company are now unlocked</li>
            <li>All features and functionality are available</li>
        </ul>

        <p>You can now continue using the system normally. We recommend setting up auto-renewal to avoid future
            interruptions.</p>

        <a href="{{ url('/dashboard') }}" class="button">Access Dashboard</a>

        <p>If you have any questions or need assistance, please contact our support team.</p>

        <p>Thank you for choosing SmartFinance!</p>

        <p>Best regards,<br>
            SmartFinance Support Team</p>
    </div>

    <div class="footer">
        <p>This is an automated message. Please do not reply to this email.</p>
        <p>&copy; {{ date('Y') }} SmartFinance. All rights reserved.</p>
    </div>
</body>

</html>