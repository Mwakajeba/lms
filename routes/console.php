<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use App\Jobs\CollectMatureInterestJob;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('loans:collect-mature-interest', function () {
    $this->info('Starting mature interest collection...');

    try {
        CollectMatureInterestJob::dispatch();
        $this->info('Mature interest collection job has been dispatched successfully.');
        $this->info('Check the logs for detailed information about the process.');
    } catch (\Exception $e) {
        $this->error('Error dispatching mature interest collection job: ' . $e->getMessage());
        return 1;
    }

    return 0;
})->purpose('Collect mature interest from active loans and post to GL');

Artisan::command('emails:send-invitations', function () {
    $this->info('🚀 Starting bulk email invitation process...');

    // Get all valid recipients
    $microfinances = \App\Models\Microfinance::withValidEmails()->get();
    
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

    // Set default content
    $subject = 'Karibu SmartFinance - Mfumo wa Usimamizi wa Fedha';
    $content = 'Mpendwa Mshirika,

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
    $companyName = 'SmartFinance';

    // Show what will be sent
    $this->info("📤 Sending invitations to {$microfinances->count()} recipients");
    $this->info("Subject: {$subject}");
    $this->info("Company: {$companyName}");

    // Skip confirmation for non-interactive mode
    if ($this->input->isInteractive() && !$this->confirm('Do you want to proceed with sending these invitations?')) {
        $this->info('❌ Operation cancelled');
        return 0;
    }

    try {
        $bulkEmailService = new \App\Services\BulkEmailService();
        
        $this->info('📤 Sending emails immediately...');
        $results = $bulkEmailService->sendBulkEmails(
            $recipients,
            $subject,
            $content,
            $companyName
        );

        // Display results
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
        
        if (isset($results['errors']) && !empty($results['errors'])) {
            $this->newLine();
            $this->warn('⚠️ Errors encountered:');
            foreach ($results['errors'] as $error) {
                $this->line("  - {$error}");
            }
        }

        $this->info('✅ Bulk email invitations completed successfully!');
        return 0;

    } catch (\Exception $e) {
        $this->error('❌ An error occurred: ' . $e->getMessage());
        return 1;
    }
})->purpose('Send SmartFinance system invitations to all microfinance contacts');
