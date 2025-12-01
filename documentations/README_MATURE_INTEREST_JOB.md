# Mature Interest Collection Job

This job automatically collects mature interest from active loans and posts the accounting entries to the General Ledger.

## Overview

The `CollectMatureInterestJob` processes all active loans daily to:
1. Find loan schedules with due dates that have passed
2. Calculate unpaid interest amounts
3. Post accounting entries to Interest Receivable and Interest Revenue accounts
4. Prevent duplicate postings

## Features

### ✅ **Automatic Processing**
- Runs daily at 6:00 AM automatically
- Processes all active loans
- Handles multiple loan products with different chart accounts

### ✅ **Duplicate Prevention**
- Checks if interest has already been posted for each schedule
- Uses unique transaction identifiers to prevent double-posting
- Safe to run multiple times

### ✅ **Accounting Accuracy**
- Posts correct double-entry accounting:
  - **Credit** Interest Receivable (Asset)
  - **Debit** Interest Revenue (Income)
- Uses chart accounts from loan product settings

### ✅ **Error Handling**
- Comprehensive logging for debugging
- Database transactions for data integrity
- Graceful error handling with rollback

## How It Works

### 1. **Loan Selection**
```php
$activeLoans = Loan::where('status', 'active')
    ->with(['product', 'customer', 'branch'])
    ->get();
```

### 2. **Schedule Processing**
```php
$maturedSchedules = $loan->schedule()
    ->where('due_date', '<=', Carbon::today())
    ->where('interest', '>', 0)
    ->get();
```

### 3. **Interest Calculation**
```php
$totalInterest = $schedule->interest;
$paidInterest = $schedule->repayments->sum('interest');
$unpaidInterest = $totalInterest - $paidInterest;
```

### 4. **Duplicate Check**
```php
$existingPosting = GlTransaction::where('chart_account_id', $interestReceivableAccountId)
    ->where('customer_id', $loan->customer_id)
    ->where('date', $schedule->due_date)
    ->where('amount', $unpaidInterest)
    ->first();
```

### 5. **GL Posting**
```php
// Credit Interest Receivable
GlTransaction::create([
    'chart_account_id' => $interestReceivableAccountId,
    'amount' => $unpaidInterest,
    'nature' => 'credit',
    'transaction_type' => 'mature_interest',
    'transaction_id' => $schedule->id,
]);

// Debit Interest Revenue
GlTransaction::create([
    'chart_account_id' => $interestRevenueAccountId,
    'amount' => $unpaidInterest,
    'nature' => 'debit',
    'transaction_type' => 'mature_interest',
    'transaction_id' => $schedule->id,
]);
```

## Usage

### **Automatic Execution**
The job runs automatically every day at 6:00 AM. No manual intervention required.

### **Manual Execution**
Run the job manually using the artisan command:

```bash
php artisan loans:collect-mature-interest
```

### **Queue Processing**
The job is queued and processed in the background. Ensure your queue worker is running:

```bash
php artisan queue:work
```

## Configuration

### **Loan Product Setup**
Ensure your loan products have the correct chart accounts configured:

```php
// In LoanProduct model
'principal_receivable_account_id' => 1001, // Asset account
'interest_receivable_account_id' => 1002,  // Asset account  
'interest_revenue_account_id' => 4001,     // Income account
```

### **Scheduling**
The job is scheduled in `app/Providers/ScheduleServiceProvider.php`:

```php
$schedule->job(new CollectMatureInterestJob())
    ->dailyAt('06:00')
    ->withoutOverlapping()
    ->runInBackground()
    ->onOneServer();
```

## Monitoring

### **Logs**
Check the logs for job execution details:

```bash
tail -f storage/logs/laravel.log
tail -f storage/logs/mature-interest-collection.log
```

### **Database Queries**
Monitor GL transactions with transaction type `mature_interest`:

```sql
SELECT * FROM gl_transactions 
WHERE transaction_type = 'mature_interest' 
ORDER BY created_at DESC;
```

Check for duplicate interest postings by due date:

```sql
SELECT 
    customer_id,
    chart_account_id,
    date,
    amount,
    COUNT(*) as posting_count
FROM gl_transactions 
WHERE transaction_type = 'mature_interest'
GROUP BY customer_id, chart_account_id, date, amount
HAVING COUNT(*) > 1;
```

## Troubleshooting

### **Job Not Running**
1. Check if queue worker is running: `php artisan queue:work`
2. Verify scheduler is running: `php artisan schedule:list`
3. Check logs for errors: `tail -f storage/logs/laravel.log`

### **Missing Chart Accounts**
1. Ensure loan products have chart accounts configured
2. Check `interest_receivable_account_id` and `interest_revenue_account_id`
3. Verify chart accounts exist in the database

### **Duplicate Postings**
1. Check existing GL transactions for the customer and due date
2. Verify chart account and amount combinations
3. Review job logs for duplicate detection messages
4. Use the duplicate detection query above to identify issues

### **Incorrect Interest Amounts**
1. Verify loan schedule calculations
2. Check repayment records for accuracy
3. Review interest calculation methods in loan products

## Database Schema

### **Required Fields**
- `loans.status` = 'active'
- `loan_schedules.due_date` <= today
- `loan_schedules.interest` > 0
- `loan_products.interest_receivable_account_id`
- `loan_products.interest_revenue_account_id`

### **GL Transaction Structure**
```sql
CREATE TABLE gl_transactions (
    id bigint unsigned NOT NULL AUTO_INCREMENT,
    chart_account_id bigint unsigned NOT NULL,
    customer_id bigint unsigned NULL,
    amount decimal(15,2) NOT NULL,
    nature enum('debit','credit') NOT NULL,
    transaction_id bigint unsigned NOT NULL,
    transaction_type varchar(255) NOT NULL,
    date datetime NOT NULL,
    description text NULL,
    branch_id bigint unsigned NULL,
    user_id bigint unsigned NULL,
    created_at timestamp NULL DEFAULT NULL,
    updated_at timestamp NULL DEFAULT NULL,
    PRIMARY KEY (id)
);
```

## Security Considerations

### **Data Integrity**
- Uses database transactions for atomicity
- Prevents duplicate postings
- Validates chart account existence

### **Access Control**
- Job runs with system privileges
- No user authentication required
- Logs all activities for audit trail

### **Error Recovery**
- Automatic rollback on errors
- Retry mechanism (3 attempts)
- Comprehensive error logging

## Performance Optimization

### **Batch Processing**
- Processes loans individually to avoid memory issues
- Uses eager loading for relationships
- Efficient database queries

### **Queue Management**
- Runs in background to avoid blocking
- Configurable timeout (5 minutes)
- Retry mechanism for failed jobs

## Testing

### **Manual Testing**
```bash
# Run job manually
php artisan loans:collect-mature-interest

# Check results
php artisan tinker
>>> App\Models\GlTransaction::where('transaction_type', 'mature_interest')->count()
```

### **Unit Testing**
Create tests in `tests/Unit/CollectMatureInterestJobTest.php`:

```php
public function test_job_processes_mature_interest()
{
    // Create test loan with matured schedule
    // Run job
    // Assert GL transactions created
}
```

## Support

For issues or questions:
1. Check the logs first
2. Verify loan product configuration
3. Review database schema
4. Contact development team

---

**Last Updated**: {{ date('Y-m-d') }}
**Version**: 1.0.0 
