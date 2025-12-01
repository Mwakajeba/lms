# Bulk Email System for SmartFinance

This system allows you to send bulk emails to multiple recipients using a user-friendly interface.

## Features

- **Bulk Email Sending**: Send emails to multiple recipients at once
- **Recipient Validation**: Validate email addresses before sending
- **Queue Support**: Option to use Laravel queues for better performance
- **Customizable Content**: Support for custom subjects, content, and company names
- **Real-time Results**: View success/failure statistics after sending

## Files Created

1. **`app/Mail/MicrofinanceMail.php`** - Email template class
2. **`resources/views/emails/microfinance.blade.php`** - Email HTML template
3. **`app/Services/BulkEmailService.php`** - Service class for bulk email operations
4. **`app/Http/Controllers/BulkEmailController.php`** - Controller for handling bulk email requests
5. **`resources/views/bulk-email/index.blade.php`** - User interface for bulk email sending

## How to Use

### 1. Access the Bulk Email Interface

Navigate to: `/settings/bulk-email`

### 2. Fill in the Form

- **Email Subject**: Enter the subject line for your emails
- **Company Name**: (Optional) Your company name to display in emails
- **Email Content**: Write your email message (supports HTML formatting)
- **Recipients**: Add email addresses and names for each recipient

### 3. Send Emails

- **Validate Recipients**: Click to check if all email addresses are valid
- **Send Emails**: Click to send emails to all recipients
- **Use Queue**: Check this option for better performance with large recipient lists

## API Usage

### Send Bulk Emails

```php
use App\Services\BulkEmailService;

$bulkEmailService = new BulkEmailService();

$recipients = [
    ['email' => 'user1@example.com', 'name' => 'User One'],
    ['email' => 'user2@example.com', 'name' => 'User Two'],
];

$results = $bulkEmailService->sendBulkEmails(
    recipients: $recipients,
    subject: 'Important Update',
    content: 'This is your email content.',
    companyName: 'Your Company'
);
```

### Send with Queue

```php
$results = $bulkEmailService->sendBulkEmailsWithQueue(
    recipients: $recipients,
    subject: 'Important Update',
    content: 'This is your email content.',
    companyName: 'Your Company'
);
```

### Validate Recipients

```php
$validation = $bulkEmailService->validateRecipients($recipients);

if ($validation['totalInvalid'] > 0) {
    // Handle invalid emails
    foreach ($validation['invalid'] as $invalid) {
        echo "Invalid email at index {$invalid['index']}: {$invalid['reason']}";
    }
}
```

## Email Template

The email template (`resources/views/emails/microfinance.blade.php`) includes:

- Responsive design
- Company branding
- Personalized greeting
- Professional footer
- Support for HTML content

## Configuration

### Mail Configuration

Ensure your Laravel mail configuration is properly set up in `.env`:

```env
MAIL_MAILER=smtp
MAIL_HOST=your-smtp-host
MAIL_PORT=587
MAIL_USERNAME=your-username
MAIL_PASSWORD=your-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=your-email@domain.com
MAIL_FROM_NAME="${APP_NAME}"
```

### Queue Configuration (Optional)

For better performance with large recipient lists, configure Laravel queues:

```env
QUEUE_CONNECTION=database
```

Then run:
```bash
php artisan queue:table
php artisan migrate
php artisan queue:work
```

## Security Features

- CSRF protection on all forms
- Input validation and sanitization
- Rate limiting (can be configured)
- Email address validation

## Customization

### Modify Email Template

Edit `resources/views/emails/microfinance.blade.php` to customize the email appearance.

### Add Attachments

Modify the `attachments()` method in `MicrofinanceMail.php`:

```php
public function attachments(): array
{
    return [
        Attachment::fromPath('/path/to/file.pdf')
            ->as('document.pdf')
            ->withMime('application/pdf'),
    ];
}
```

### Custom Validation Rules

Modify the validation rules in `BulkEmailController.php` as needed.

## Troubleshooting

### Common Issues

1. **Emails not sending**: Check mail configuration and SMTP settings
2. **Queue not working**: Ensure queue worker is running
3. **Validation errors**: Check email format and required fields

### Logs

Check Laravel logs for detailed error information:
```bash
tail -f storage/logs/laravel.log
```

## Support

For issues or questions, check the Laravel documentation or contact your system administrator. 