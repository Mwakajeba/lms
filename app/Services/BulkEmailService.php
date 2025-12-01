<?php

namespace App\Services;

use App\Mail\MicrofinanceMail;
use App\Models\EmailLog;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class BulkEmailService
{
    /**
     * Send bulk emails to multiple recipients
     *
     * @param array $recipients Array of recipient data with 'email', 'name', etc.
     * @param string $subject Email subject
     * @param string $content Email content
     * @param string|null $companyName Company name (optional)
     * @return array Results of the bulk email operation
     */
    public function sendBulkEmails(array $recipients, string $subject, string $content, ?string $companyName = null): array
    {
        $results = [
            'total' => count($recipients),
            'successful' => 0,
            'failed' => 0,
            'errors' => []
        ];

        foreach ($recipients as $recipient) {
            try {
                $email = $recipient['email'] ?? null;
                $name = $recipient['name'] ?? 'Valued Customer';

                if (!$email) {
                    $results['failed']++;
                    $results['errors'][] = "Missing email for recipient: " . json_encode($recipient);
                    continue;
                }

                // Send the email
                Mail::to($email)->send(new MicrofinanceMail(
                    content: $content,
                    subject: $subject,
                    recipientName: $name,
                    companyName: $companyName
                ));

                $results['successful']++;

                // Log success
                EmailLog::create([
                    'recipient_email' => $email,
                    'recipient_name' => $name,
                    'subject' => $subject,
                    'status' => 'sent',
                    'sent_at' => now(),
                ]);
                
                // Add a small delay to avoid overwhelming the mail server
                usleep(100000); // 0.1 second delay
                
            } catch (\Exception $e) {
                $results['failed']++;
                $results['errors'][] = "Failed to send email to {$email}: " . $e->getMessage();
                Log::error("Bulk email failed for {$email}: " . $e->getMessage());

                // Log failure
                EmailLog::create([
                    'recipient_email' => $email ?? 'unknown',
                    'recipient_name' => $name ?? null,
                    'subject' => $subject,
                    'status' => 'failed',
                    'error_message' => $e->getMessage(),
                ]);
            }
        }

        return $results;
    }

    /**
     * Send bulk emails with queue for better performance
     *
     * @param array $recipients Array of recipient data
     * @param string $subject Email subject
     * @param string $content Email content
     * @param string|null $companyName Company name (optional)
     * @return array Results of the bulk email operation
     */
    public function sendBulkEmailsWithQueue(array $recipients, string $subject, string $content, ?string $companyName = null): array
    {
        $results = [
            'total' => count($recipients),
            'queued' => 0,
            'failed' => 0,
            'errors' => []
        ];

        foreach ($recipients as $recipient) {
            try {
                $email = $recipient['email'] ?? null;
                $name = $recipient['name'] ?? 'Valued Customer';

                if (!$email) {
                    $results['failed']++;
                    $results['errors'][] = "Missing email for recipient: " . json_encode($recipient);
                    continue;
                }

                // Queue the email
                Mail::to($email)->queue(new MicrofinanceMail(
                    content: $content,
                    subject: $subject,
                    recipientName: $name,
                    companyName: $companyName
                ));

                $results['queued']++;

                // Log queued
                EmailLog::create([
                    'recipient_email' => $email,
                    'recipient_name' => $name,
                    'subject' => $subject,
                    'status' => 'queued',
                ]);
                
            } catch (\Exception $e) {
                $results['failed']++;
                $results['errors'][] = "Failed to queue email for {$email}: " . $e->getMessage();
                Log::error("Bulk email queuing failed for {$email}: " . $e->getMessage());

                // Log failure
                EmailLog::create([
                    'recipient_email' => $email ?? 'unknown',
                    'recipient_name' => $name ?? null,
                    'subject' => $subject,
                    'status' => 'failed',
                    'error_message' => $e->getMessage(),
                ]);
            }
        }

        return $results;
    }

    /**
     * Validate recipient data
     *
     * @param array $recipients
     * @return array Validation results
     */
    public function validateRecipients(array $recipients): array
    {
        $validRecipients = [];
        $invalidRecipients = [];

        foreach ($recipients as $index => $recipient) {
            $email = $recipient['email'] ?? null;
            
            if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $invalidRecipients[] = [
                    'index' => $index,
                    'data' => $recipient,
                    'reason' => 'Invalid or missing email address'
                ];
            } else {
                $validRecipients[] = $recipient;
            }
        }

        return [
            'valid' => $validRecipients,
            'invalid' => $invalidRecipients,
            'totalValid' => count($validRecipients),
            'totalInvalid' => count($invalidRecipients)
        ];
    }
} 