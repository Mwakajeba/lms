# Loan Repayment System Documentation

## Overview

The Loan Repayment System is a comprehensive solution for managing loan repayments in a microfinance application. It provides flexible payment processing, multiple calculation methods, configurable repayment orders, and full accounting integration using receipts and GL transactions.

## Table of Contents

1. [Features](#features)
2. [Architecture](#architecture)
3. [Installation & Setup](#installation--setup)
4. [Configuration](#configuration)
5. [Usage Guide](#usage-guide)
6. [API Reference](#api-reference)
7. [Database Schema](#database-schema)
8. [Troubleshooting](#troubleshooting)

## Features

### 🏦 Core Functionality
- **Receipt-Based GL Transactions**: Uses receipts instead of journals for proper accounting
- **Multi-Schedule Payment Processing**: Automatically processes payments across multiple schedules
- **Configurable Repayment Order**: Follows loan product configuration for payment allocation
- **Automatic Loan Completion**: Marks loans as completed when fully paid
- **Bank Account Integration**: Tracks which bank account receives payments

### 📊 Calculation Methods
1. **Flat Rate Method**: Simple interest calculation
2. **Reducing Balance with Equal Installments**: Standard reducing balance method
3. **Reducing Balance with Equal Principal**: Equal principal payments

### 🔄 Payment Processing
- **Smart Allocation**: Allocates payments according to configured order (penalty → fees → interest → principal)
- **Overpayment Handling**: Automatically processes next schedules if current schedule is fully paid
- **Partial Payments**: Handles partial payments with proper allocation
- **Bulk Processing**: Process multiple loan repayments simultaneously

### 🛡️ Penalty Management
- **Automatic Penalty Removal**: Removes penalties when paid on due date
- **Manual Penalty Removal**: Staff can manually remove penalties with reason tracking
- **Penalty Tracking**: Maintains audit trail for penalty removals

### 🎨 User Interface
- **Professional Modal**: Comprehensive repayment form with all details
- **Real-time Validation**: Client-side and server-side validation
- **Success/Error Feedback**: SweetAlert2 notifications
- **Responsive Design**: Works on all device sizes

## Architecture

### Service Layer Pattern
The system uses a service layer pattern for clean separation of concerns:

```
Controller → Service → Models → Database
```

### Key Components

#### 1. LoanRepaymentService
- **Location**: `app/Services/LoanRepaymentService.php`
- **Purpose**: Core business logic for repayment processing
- **Methods**:
  - `processRepayment()`: Main repayment processing
  - `calculateSchedule()`: Schedule calculation methods
  - `removePenalty()`: Penalty removal functionality

#### 2. LoanRepaymentController
- **Location**: `app/Http/Controllers/LoanRepaymentController.php`
- **Purpose**: HTTP request handling and API endpoints
- **Dependencies**: LoanRepaymentService

#### 3. Models
- **Repayment**: `app/Models/Repayment.php`
- **Receipt**: `app/Models/Receipt.php`
- **ReceiptItem**: `app/Models/ReceiptItem.php`
- **GlTransaction**: `app/Models/GlTransaction.php`

## Installation & Setup

### Prerequisites
- Laravel 10+ application
- MySQL/PostgreSQL database
- Existing loan management system

### 1. Service Registration
The `LoanRepaymentService` is automatically resolved by Laravel's service container.

### 2. Routes
Routes are automatically registered in `routes/web.php`:

```php
// Loan Repayment Routes
Route::post('/repayments', [LoanRepaymentController::class, 'store'])->name('repayments.store');
Route::get('/repayments/history/{loanId}', [LoanRepaymentController::class, 'getRepaymentHistory'])->name('repayments.history');
Route::get('/repayments/schedule/{scheduleId}', [LoanRepaymentController::class, 'getScheduleDetails'])->name('repayments.schedule-details');
Route::post('/repayments/remove-penalty/{scheduleId}', [LoanRepaymentController::class, 'removePenalty'])->name('repayments.remove-penalty');
Route::post('/repayments/calculate-schedule/{loanId}', [LoanRepaymentController::class, 'calculateSchedule'])->name('repayments.calculate-schedule');
Route::post('/repayments/bulk', [LoanRepaymentController::class, 'bulkRepayment'])->name('repayments.bulk');
```

### 3. Database
No additional migrations required. Uses existing tables:
- `repayments`
- `receipts`
- `receipt_items`
- `gl_transactions`
- `loan_schedules`
- `loans`
- `loan_products`

## Configuration

### Loan Product Configuration

#### Repayment Order
Configure the repayment order in loan products:

```php
// In loan_products table
repayment_order = "penalties,fees,interest,principal"
```

**Valid Components:**
- `penalties`: Penalty amounts
- `fees`: Fee amounts
- `interest`: Interest amounts
- `principal`: Principal amounts

#### Calculation Method
Set the calculation method in loan products:

```php
// In loan_products table
interest_method = "flat_rate" // or "reducing_balance_with_equal_installment" or "reducing_balance_with_equal_principal"
```

### Chart Accounts
Configure chart accounts for GL transactions:

```php
// Default chart accounts (configure in your system)
- Loan Principal Receivable
- Loan Interest Receivable
- Loan Fee Receivable
- Loan Penalty Receivable
- Bank/Cash Account
```

## Usage Guide

### 1. Single Repayment Processing

#### Frontend (JavaScript)
```javascript
// Open repayment modal
function repayScheduleItem(scheduleId, amount, dueDate, principal, interest, penalty, fee) {
    // Set modal values
    document.getElementById('schedule_id').value = scheduleId;
    document.getElementById('payment_amount').value = amount;
    // ... set other values
    
    // Show modal
    const modal = new bootstrap.Modal(document.getElementById('repayScheduleModal'));
    modal.show();
}

// Handle form submission
$('#repayScheduleModal form').on('submit', function(e) {
    e.preventDefault();
    
    $.ajax({
        url: $(this).attr('action'),
        method: 'POST',
        data: $(this).serialize(),
        success: function(response) {
            // Handle success
            location.reload();
        },
        error: function(xhr) {
            // Handle error
        }
    });
});
```

#### Backend Processing
```php
// The service automatically:
// 1. Validates the payment
// 2. Allocates payment according to repayment order
// 3. Creates repayment record
// 4. Creates receipt and GL transactions
// 5. Updates loan status if fully paid
```

### 2. Penalty Removal

#### Frontend
```javascript
function removePenalty(scheduleId, penaltyAmount) {
    Swal.fire({
        title: 'Remove Penalty',
        html: `
            <div class="text-start">
                <p><strong>Penalty Amount:</strong> TZS ${penaltyAmount}</p>
                <div class="mb-3">
                    <label for="penalty_reason" class="form-label">Reason for Removal</label>
                    <textarea class="form-control" id="penalty_reason" rows="3"></textarea>
                </div>
            </div>
        `,
        // ... confirmation logic
    });
}
```

#### Backend
```php
// POST /repayments/remove-penalty/{scheduleId}
{
    "reason": "Customer hardship case"
}
```

### 3. Bulk Repayment Processing

#### Frontend
```javascript
// Submit multiple repayments
const repayments = [
    {
        loan_id: 1,
        amount: 50000,
        payment_date: "2024-01-15",
        bank_account_id: 1
    },
    // ... more repayments
];

$.ajax({
    url: '/repayments/bulk',
    method: 'POST',
    data: {
        repayments: repayments,
        payment_type: 'regular',
        comments: 'Bulk processing'
    },
    success: function(response) {
        console.log('Processed:', response.summary);
    }
});
```

### 4. Schedule Calculation

#### API Call
```javascript
$.ajax({
    url: `/repayments/calculate-schedule/${loanId}`,
    method: 'POST',
    data: {
        method: 'flat_rate' // or 'reducing_equal_installment' or 'reducing_equal_principal'
    },
    success: function(response) {
        console.log('Schedules:', response.schedules);
    }
});
```

## API Reference

### POST /repayments
Process a single loan repayment.

**Request Body:**
```json
{
    "loan_id": 1,
    "schedule_id": 5,
    "payment_date": "2024-01-15",
    "amount": 50000,
    "bank_account_id": 1
}
```

**Response:**
```json
{
    "success": true,
    "message": "Repayment recorded successfully!"
}
```

### POST /repayments/bulk
Process multiple loan repayments.

**Request Body:**
```json
{
    "repayments": [
        {
            "loan_id": 1,
            "amount": 50000,
            "payment_date": "2024-01-15",
            "bank_account_id": 1
        }
    ],
    "payment_type": "regular",
    "comments": "Bulk processing"
}
```

**Response:**
```json
{
    "success": true,
    "message": "Processed 1 repayments successfully, 0 failed",
    "summary": {
        "total": 1,
        "success": 1,
        "failed": 0
    },
    "results": [...]
}
```

### POST /repayments/remove-penalty/{scheduleId}
Remove penalty from a schedule.

**Request Body:**
```json
{
    "reason": "Customer hardship case"
}
```

**Response:**
```json
{
    "success": true,
    "message": "Penalty removed successfully"
}
```

### POST /repayments/calculate-schedule/{loanId}
Calculate loan schedule using different methods.

**Request Body:**
```json
{
    "method": "flat_rate"
}
```

**Response:**
```json
{
    "success": true,
    "schedules": [
        {
            "installment_no": 1,
            "due_date": "2024-02-15",
            "principal": 10000,
            "interest": 1000,
            "fee_amount": 0,
            "penalty_amount": 0,
            "total_installment": 11000
        }
    ]
}
```

### GET /repayments/history/{loanId}
Get repayment history for a loan.

**Response:**
```json
[
    {
        "id": 1,
        "loan_id": 1,
        "schedule_id": 5,
        "payment_date": "2024-01-15",
        "principal": 10000,
        "interest": 1000,
        "fee_amount": 0,
        "penalt_amount": 0,
        "cash_deposit": 11000,
        "schedule": {...},
        "bank_account": {...}
    }
]
```

## Database Schema

### Repayments Table
```sql
CREATE TABLE repayments (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    customer_id BIGINT NOT NULL,
    loan_id BIGINT NOT NULL,
    loan_schedule_id BIGINT NOT NULL,
    bank_account_id BIGINT NOT NULL,
    principal DECIMAL(12,2) NOT NULL,
    interest DECIMAL(12,2) NOT NULL,
    penalt_amount DECIMAL(15,2) NOT NULL,
    fee_amount DECIMAL(12,2) NOT NULL,
    payment_date DATE NOT NULL,
    cash_deposit DECIMAL(12,2) NOT NULL,
    due_date DATE NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    
    FOREIGN KEY (customer_id) REFERENCES customers(id),
    FOREIGN KEY (loan_id) REFERENCES loans(id),
    FOREIGN KEY (loan_schedule_id) REFERENCES loan_schedules(id),
    FOREIGN KEY (bank_account_id) REFERENCES bank_accounts(id)
);
```

### Key Relationships
- `repayments.customer_id` → `customers.id`
- `repayments.loan_id` → `loans.id`
- `repayments.loan_schedule_id` → `loan_schedules.id`
- `repayments.bank_account_id` → `bank_accounts.id`

## Troubleshooting

### Common Issues

#### 1. "Route not found" Error
**Problem**: `Route [repayments.store] not defined`
**Solution**: Ensure routes are properly registered in `routes/web.php`

#### 2. "Member has protected visibility" Error
**Problem**: Service method not accessible
**Solution**: Ensure all service methods are marked as `public`

#### 3. "Chart account not found" Error
**Problem**: GL transactions failing
**Solution**: Configure chart accounts in your system

#### 4. "No unpaid schedules found" Error
**Problem**: All schedules already paid
**Solution**: Check loan status and schedule payment history

### Debugging

#### Enable Logging
```php
// In .env
LOG_LEVEL=debug
```

#### Check Service Logs
```bash
tail -f storage/logs/laravel.log
```

#### Database Queries
```php
// Enable query logging
DB::enableQueryLog();
// ... your code
dd(DB::getQueryLog());
```

### Performance Optimization

#### 1. Database Indexing
```sql
-- Add indexes for better performance
CREATE INDEX idx_repayments_loan_id ON repayments(loan_id);
CREATE INDEX idx_repayments_schedule_id ON repayments(loan_schedule_id);
CREATE INDEX idx_repayments_payment_date ON repayments(payment_date);
```

#### 2. Eager Loading
```php
// Use eager loading to avoid N+1 queries
$loan = Loan::with(['schedules', 'repayments', 'product'])->find($loanId);
```

#### 3. Bulk Operations
```php
// Use bulk operations for multiple records
Repayment::insert($repayments);
```

## Security Considerations

### 1. Input Validation
- All inputs are validated using Laravel's validation system
- SQL injection protection through Eloquent ORM
- XSS protection through proper output escaping

### 2. Authorization
- Ensure proper permissions are set for repayment operations
- Use Laravel's authorization gates and policies

### 3. Data Integrity
- Database transactions ensure data consistency
- Proper error handling and rollback mechanisms

## Testing

### Unit Tests
```bash
# Run tests
php artisan test --filter=LoanRepaymentTest
```

### Manual Testing
1. Create a test loan with multiple schedules
2. Process various payment scenarios
3. Test penalty removal functionality
4. Verify GL transactions are created correctly

## Support

For technical support or questions about the loan repayment system:

1. Check the troubleshooting section above
2. Review the Laravel logs for error details
3. Verify database configuration and permissions
4. Ensure all required models and relationships exist

## Version History

### v1.0.0 (Current)
- Initial implementation
- Receipt-based GL transactions
- Three calculation methods
- Configurable repayment order
- Penalty management
- Bulk processing support
- Professional UI with modal forms

---

**Last Updated**: January 2024
**Author**: SmartFinance Development Team
**Version**: 1.0.0 