<?php

require_once 'vendor/autoload.php';

use Illuminate\Support\Facades\DB;

// Bootstrap Laravel application
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

// Find orphaned penalty GL transactions
// These are GL transactions where penalty_amount = 0 in schedule but GL transactions still exist
$orphanedTransactions = DB::select("
    SELECT gt.id, gt.transaction_id, gt.transaction_type, gt.amount, gt.nature, ls.penalty_amount
    FROM gl_transactions gt
    LEFT JOIN loan_schedules ls ON gt.transaction_id = ls.id
    WHERE gt.transaction_type IN ('Penalty', 'penalty', 'Loan Penalty')
    AND (ls.penalty_amount = 0 OR ls.penalty_amount IS NULL)
");

echo "Found " . count($orphanedTransactions) . " orphaned penalty GL transactions:\n";

foreach ($orphanedTransactions as $transaction) {
    echo "GL ID: {$transaction->id}, Schedule ID: {$transaction->transaction_id}, Type: {$transaction->transaction_type}, Amount: {$transaction->amount}, Nature: {$transaction->nature}, Schedule Penalty: {$transaction->penalty_amount}\n";
}

// You can uncomment the following lines to actually delete them:
/*
if (count($orphanedTransactions) > 0) {
    $transactionIds = array_column($orphanedTransactions, 'id');
    $deletedCount = DB::table('gl_transactions')->whereIn('id', $transactionIds)->delete();
    echo "Deleted {$deletedCount} orphaned penalty GL transactions.\n";
}
*/

echo "\nTo clean up these orphaned transactions, you can run the penalty removal service for each schedule or manually delete the GL transactions.\n";
