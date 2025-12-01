<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\BulkEmailService;
use App\Models\Microfinance;

class SendBulkInvitationsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'emails:send-invitations 
                            {--subject= : Custom email subject}
                            {--content= : Custom email content}
                            {--company= : Company name}
                            {--queue : Use queue for sending emails}
                            {--dry-run : Show what would be sent without actually sending}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send SmartFinance system invitations to all microfinance contacts';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🚀 Starting bulk email invitation process...');

        // Get all valid recipients
        $microfinances = Microfinance::withValidEmails()->get();
        
        if ($microfinances->isEmpty()) {
            $this->error('❌ No valid email addresses found in microfinances table');
            return 1;
        }

        $this->info("📧 Found {$microfinances->count()} recipients with valid emails");

        // Prepare recipients data
        $recipients = $microfinances->map(function ($microfinance) {
            return [
                'email' => $microfinance->email,
                'name' => $microfinance->display_name
            ];
        })->toArray();

        // Set default content if not provided
        $subject = $this->option('subject') ?: 'Karibu SmartFinance - Mfumo wa Usimamizi wa Fedha';
        $content = $this->option('content') ?: $this->getDefaultContent();
        $companyName = $this->option('company') ?: 'SmartFinance';
        $useQueue = $this->option('queue');
        $dryRun = $this->option('dry-run');

        if ($dryRun) {
            $this->info('🔍 DRY RUN MODE - No emails will be sent');
            $this->info("Subject: {$subject}");
            $this->info("Company: {$companyName}");
            $this->info("Recipients: " . count($recipients));
            $this->info("Content preview:");
            $this->line(substr($content, 0, 200) . '...');
            return 0;
        }

        // Show what will be sent
        $this->info("📤 Sending invitations to {$microfinances->count()} recipients");
        $this->info("Subject: {$subject}");
        $this->info("Company: {$companyName}");
        $this->info("Queue mode: " . ($useQueue ? 'Yes' : 'No'));

        // Confirm before sending
        if (!$this->confirm('Do you want to proceed with sending these invitations?')) {
            $this->info('❌ Operation cancelled');
            return 0;
        }

        try {
            $bulkEmailService = new BulkEmailService();
            
            if ($useQueue) {
                $this->info('⏳ Sending emails via queue...');
                $results = $bulkEmailService->sendBulkEmailsWithQueue(
                    $recipients,
                    $subject,
                    $content,
                    $companyName
                );
            } else {
                $this->info('📤 Sending emails immediately...');
                $results = $bulkEmailService->sendBulkEmails(
                    $recipients,
                    $subject,
                    $content,
                    $companyName
                );
            }

            // Display results
            $this->displayResults($results);

            $this->info('✅ Bulk email invitations completed successfully!');
            return 0;

        } catch (\Exception $e) {
            $this->error('❌ An error occurred: ' . $e->getMessage());
            return 1;
        }
    }

    /**
     * Get default email content
     */
    private function getDefaultContent()
    {
        return 'Mpendwa Mshirika,

Tunafurahi kukualika kutumia SmartFinance, mfumo wetu kamili wa usimamizi wa fedha ulioundwa kurahisisha shughuli zako.

SmartFinance inatoa:
• Usimamizi kamili wa mikopo
• Usimamizi wa uhusiano na wateja
• Ripoti za kifedha na uchambuzi
• Shughuli za matawi mengi
• Interface salama na rahisi kutumia

Ikiwa unahitaji kuona mfano, tafadhali tembelea: https://dev.smartsoft.co.tz

Maelezo ya kuingia:
Jina la mtumiaji: 2556555778030
Nywila: 12345

Maelezo yako ya kuingia yatakupokelewa kando.

Kwa maswali au msaada, wasiliana nasi: +255 747 762 244

Kwa heshima,
Timu ya SmartFinance';
    }

    /**
     * Display results in a formatted way
     */
    private function displayResults($results)
    {
        $this->newLine();
        $this->info('📊 Results Summary:');
        
        if (isset($results['total'])) {
            $this->line("Total processed: {$results['total']}");
        }
        
        if (isset($results['successful'])) {
            $this->line("Successful: {$results['successful']}");
        }
        
        if (isset($results['failed'])) {
            $this->line("Failed: {$results['failed']}");
        }
        
        if (isset($results['queued'])) {
            $this->line("Queued: {$results['queued']}");
        }
        
        if (isset($results['errors']) && !empty($results['errors'])) {
            $this->newLine();
            $this->warn('⚠️ Errors encountered:');
            foreach ($results['errors'] as $error) {
                $this->line("  - {$error}");
            }
        }
    }
} 