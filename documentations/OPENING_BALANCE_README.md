# Opening Balance Feature

This feature allows bulk creation of loans with opening balances and automatic repayment processing.

## Features

1. **Bulk Loan Creation**: Create multiple loans at once from a CSV file
2. **Automatic Repayment Processing**: Process repayments automatically if amount_paid > 0
3. **Background Processing**: All operations run in background jobs
4. **Chunked Processing**: Loans are processed in chunks of 25 to prevent memory issues
5. **Error Handling**: Failed loans are logged and skipped

## How to Use

### Step 1: Access the Feature
1. Go to the Loans page
2. Click the "Opening Balance" button in the top right
3. A modal will open with the required fields

### Step 2: Download Template
1. Click "Download Template" to get the CSV template
2. The template includes sample data for the first 5 customers
3. Fill in your loan data following the template structure

### Step 3: Configure Settings
1. **Loan Product**: Select the loan product to use for all loans
2. **Branch**: Select the branch for the loans
3. **Chart Account**: Select the chart account for disbursements
4. **CSV File**: Upload your filled CSV file

### Step 4: Process
1. Click "Process Opening Balance"
2. The system will validate the CSV and start background processing
3. You'll receive a success message and can continue working
4. Check logs for processing status

## CSV Template Structure

The CSV template includes the following columns:

| Column | Description | Required | Notes |
|--------|-------------|----------|-------|
| customer_no | Customer number in system | Yes | Must exist in database |
| customer_name | Customer name | Yes | For reference only |
| group_id | Group ID | No | Will be auto-filled if customer has group |
| group_name | Group name | No | For reference only |
| amount | Loan amount | Yes | Must be within product limits |
| interest | Interest rate (%) | Yes | Must be within product limits |
| period | Loan period (months) | Yes | Must be within product limits |
| interest_cycle | Interest cycle | Yes | e.g., "Monthly", "Weekly" |
| date_applied | Application date | Yes | Format: YYYY-MM-DD |
| sector | Business sector | Yes | e.g., "Business", "Agriculture" |
| amount_paid | Amount already paid | No | If > 0, repayment will be processed |

## Processing Flow

1. **Validation**: CSV structure and data validation
2. **Customer Lookup**: Find customers by customer number
3. **Product Validation**: Check loan amounts and periods against product limits
4. **Collateral Check**: Verify collateral requirements if applicable
5. **Loan Creation**: Create loans with 'active' status
6. **Schedule Generation**: Generate repayment schedules
7. **GL Posting**: Post disbursement and penalty transactions
8. **Repayment Processing**: Process repayments if amount_paid > 0

## Error Handling

- **Invalid Customer**: Loans for non-existent customers are skipped and logged
- **Product Limits**: Loans outside product limits are rejected
- **Insufficient Collateral**: Loans requiring more collateral than available are rejected
- **Duplicate Loans**: Customers with existing active loans for the same product are rejected

## Background Jobs

### BulkLoanCreationJob
- Processes loans in chunks of 25
- Creates loans, schedules, and GL transactions
- Dispatches repayment job if needed

### BulkRepaymentJob
- Processes repayments for loans with amount_paid > 0
- Uses the existing LoanRepaymentService
- Skips loans with zero or negative amounts

## Logging

All operations are logged with detailed information:
- Loan creation success/failure
- Customer lookup results
- Validation errors
- Processing statistics

## Security

- File upload validation (CSV only, max 10MB)
- User authentication required
- Branch-based access control
- Input sanitization and validation

## Performance

- Chunked processing prevents memory issues
- Background jobs don't block the UI
- Database transactions ensure data integrity
- Efficient customer and product lookups
