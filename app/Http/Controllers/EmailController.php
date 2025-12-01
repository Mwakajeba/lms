<?php

namespace App\Http\Controllers;

use App\Models\Microfinance;
use App\Services\BulkEmailService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class EmailController extends Controller
{
    protected $bulkEmailService;

    public function __construct(BulkEmailService $bulkEmailService)
    {
        $this->bulkEmailService = $bulkEmailService;
    }

    /**
     * Display the email composition form
     */
    public function index()
    {
        $microfinances = Microfinance::withValidEmail()->get();
        $totalRecipients = $microfinances->count();
        
        return view('emails.compose', compact('microfinances', 'totalRecipients'));
    }

    /**
     * Send bulk emails to microfinances
     */
    public function sendBulkEmails(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'subject' => 'required|string|max:255',
            'content' => 'required|string',
            'send_type' => 'required|in:immediate,queue',
            'recipients' => 'nullable|array',
            'recipients.*' => 'integer|exists:microfinances,id'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Get recipients
            $query = Microfinance::withValidEmail();
            
            if ($request->has('recipients') && !empty($request->recipients)) {
                $query->whereIn('id', $request->recipients);
            }
            
            $recipients = $query->get()->map(function ($microfinance) {
                return [
                    'email' => $microfinance->email,
                    'name' => $microfinance->formatted_name,
                    'id' => $microfinance->id
                ];
            })->toArray();

            if (empty($recipients)) {
                return response()->json([
                    'success' => false,
                    'message' => 'No valid recipients found'
                ], 400);
            }

            // Send emails
            if ($request->send_type === 'queue') {
                $results = $this->bulkEmailService->sendBulkEmailsWithQueue(
                    $recipients,
                    $request->subject,
                    $request->content,
                    config('app.name')
                );
            } else {
                $results = $this->bulkEmailService->sendBulkEmails(
                    $recipients,
                    $request->subject,
                    $request->content,
                    config('app.name')
                );
            }

            // Log the results
            Log::info('Bulk email sent', [
                'total' => $results['total'],
                'successful' => $results['successful'] ?? $results['queued'] ?? 0,
                'failed' => $results['failed'],
                'subject' => $request->subject
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Emails sent successfully',
                'results' => $results
            ]);

        } catch (\Exception $e) {
            Log::error('Bulk email sending failed', [
                'error' => $e->getMessage(),
                'subject' => $request->subject ?? 'N/A'
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to send emails: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get microfinances for email selection
     */
    public function getMicrofinances()
    {
        $microfinances = Microfinance::withValidEmail()
            ->select('id', 'name', 'email')
            ->get()
            ->map(function ($microfinance) {
                return [
                    'id' => $microfinance->id,
                    'name' => $microfinance->formatted_name,
                    'email' => $microfinance->email
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $microfinances
        ]);
    }

    /**
     * Test email configuration
     */
    public function testEmail()
    {
        try {
            $testRecipient = Microfinance::withValidEmail()->first();
            
            if (!$testRecipient) {
                return response()->json([
                    'success' => false,
                    'message' => 'No valid recipients found for testing'
                ], 400);
            }

            $results = $this->bulkEmailService->sendBulkEmails(
                [[
                    'email' => $testRecipient->email,
                    'name' => $testRecipient->formatted_name
                ]],
                'Test Email - SmartFinance System',
                'This is a test email to verify that the email system is working correctly. If you receive this email, the system is functioning properly.',
                config('app.name')
            );

            if ($results['successful'] > 0) {
                return response()->json([
                    'success' => true,
                    'message' => 'Test email sent successfully to ' . $testRecipient->email
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to send test email'
                ], 500);
            }

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Test email failed: ' . $e->getMessage()
            ], 500);
        }
    }
}
