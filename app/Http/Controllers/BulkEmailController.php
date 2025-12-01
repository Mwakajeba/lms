<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Services\BulkEmailService;
use App\Models\EmailLog;
use App\Models\Microfinance;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class BulkEmailController extends Controller
{
    protected $bulkEmailService;

    public function __construct(BulkEmailService $bulkEmailService)
    {
        $this->bulkEmailService = $bulkEmailService;
    }

    /**
     * Show the bulk email form
     */
    public function index()
    {
        // Get count of available recipients
        $recipientCount = Microfinance::withValidEmails()->count();

        return view('bulk-email.index', compact('recipientCount'));
    }

    /**
     * Send bulk emails to all microfinances
     */
    public function send(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'subject' => 'required|string|max:255',
            'content' => 'required|string|max:5000',
            'company_name' => 'nullable|string|max:255',
            'use_queue' => 'boolean'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Get all microfinances with valid emails
            $microfinances = Microfinance::withValidEmails()->get();
            
            if ($microfinances->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No valid email addresses found in microfinances table'
                ], 422);
            }

            // Convert to recipients format
            $recipients = $microfinances->map(function ($microfinance) {
                return [
                    'email' => $microfinance->email,
                    'name' => $microfinance->display_name
                ];
            })->toArray();

            $subject = $request->input('subject');
            $content = $request->input('content');
            $companyName = $request->input('company_name');
            $useQueue = $request->boolean('use_queue', false);

            // Send emails
            if ($useQueue) {
                $results = $this->bulkEmailService->sendBulkEmailsWithQueue(
                    $recipients,
                    $subject,
                    $content,
                    $companyName
                );
            } else {
                $results = $this->bulkEmailService->sendBulkEmails(
                    $recipients,
                    $subject,
                    $content,
                    $companyName
                );
            }

            return response()->json([
                'success' => true,
                'message' => 'Bulk emails sent successfully to ' . count($recipients) . ' recipients',
                'results' => $results
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while sending bulk emails',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get recipient count and preview
     */
    public function getRecipients(Request $request): JsonResponse
    {
        try {
            // Check if logs data is requested
            if ($request->has('logs') && $request->logs) {
                return $this->getEmailLogs($request);
            }

            $microfinances = Microfinance::withValidEmails()->get();
            
            $recipients = $microfinances->map(function ($microfinance) {
                return [
                    'id' => $microfinance->id,
                    'email' => $microfinance->email,
                    'name' => $microfinance->display_name
                ];
            });

            return response()->json([
                'success' => true,
                'total' => $recipients->count(),
                'recipients' => $recipients
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch recipients',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get email logs for DataTable
     */
    private function getEmailLogs(Request $request): JsonResponse
    {
        try {
            $query = EmailLog::query();

            // Apply search filter
            if ($request->has('search') && $request->search['value']) {
                $searchValue = $request->search['value'];
                $query->where(function($q) use ($searchValue) {
                    $q->where('recipient_email', 'like', "%{$searchValue}%")
                      ->orWhere('recipient_name', 'like', "%{$searchValue}%")
                      ->orWhere('subject', 'like', "%{$searchValue}%")
                      ->orWhere('status', 'like', "%{$searchValue}%");
                });
            }

            // Apply status filter
            if ($request->has('status') && $request->status && $request->status !== 'all') {
                $query->where('status', $request->status);
            }

            // Get total count
            $totalRecords = $query->count();

            // Check if this is an export request
            if ($request->has('export') && $request->export) {
                // Return all records for export (no pagination)
                $logs = $query->orderBy('id', 'desc')->get();
                
                return response()->json([
                    'success' => true,
                    'data' => $logs
                ]);
            }

            // Apply pagination for DataTable
            $start = $request->start ?? 0;
            $length = $request->length ?? 25;
            
            $logs = $query->orderBy('id', 'desc')
                        ->offset($start)
                        ->limit($length)
                        ->get();

            return response()->json([
                'draw' => intval($request->draw ?? 1),
                'recordsTotal' => $totalRecords,
                'recordsFiltered' => $totalRecords,
                'data' => $logs
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'draw' => intval($request->draw ?? 1),
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => [],
                'error' => $e->getMessage()
            ], 500);
        }
    }
} 