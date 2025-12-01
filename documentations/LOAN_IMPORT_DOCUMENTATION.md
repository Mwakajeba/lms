# Loan Import Feature Documentation

## Overview
The loan import functionality allows users to bulk import loans using CSV files. The system validates customer numbers against the existing customer database and automatically skips rows with invalid customer numbers.

## CSV Format Requirements

### Required Columns
- `customer_no`: Customer number (must exist in customers table)
- `amount`: Loan amount (numeric, greater than 0)
- `period`: Loan period in months (integer, greater than 0)
- `interest`: Interest rate (numeric, 0 or greater)
- `date_applied`: Application date (YYYY-MM-DD format, not future date)
- `interest_cycle`: Interest calculation cycle (e.g., 'monthly')
- `loan_officer`: User ID of the loan officer (must exist in users table)
- `group_id`: Group ID (must exist in groups table)
- `sector`: Business sector (text)

### Sample CSV Format
```csv
customer_no,amount,period,interest,date_applied,interest_cycle,loan_officer,group_id,sector
100001,1000000,12,5.5,2024-01-15,monthly,2,1,Agriculture
100355,500000,6,4.0,2024-01-16,monthly,3,2,Business
```

## Import Process

1. **Access Import**: Navigate to Loans > List and click "Import Loans" button
2. **Select Loan Type**: Choose "New Loans" (Bank/Cash accounts) or "Old Loans" (Equity accounts)
3. **Configure Settings**:
   - Branch: Select the branch for imported loans
   - Loan Product: Choose the loan product
   - Chart Account: Automatically populated based on loan type
4. **Upload CSV**: Select your CSV file (max 5MB)
5. **Process Import**: Click "Import Loans" to start processing

## Import Behavior

### Successful Processing
- Valid rows are processed and loans are created
- Complete loan lifecycle: loan creation, payment records, GL transactions
- Repayment schedules are automatically generated

### Error Handling
- **Customer Not Found**: Rows with non-existent customer numbers are silently skipped
- **Validation Errors**: Other validation errors are reported with specific error messages
- **Partial Success**: Import continues even if some rows fail

### Import Results
The system provides detailed feedback:
- Successfully imported loans count
- Skipped loans count (customer not found)
- Failed loans count (validation errors)
- Detailed error messages for failed rows

## Features

### Data Validation
- Customer number existence check
- Data type and format validation
- Business rule validation (collateral, existing loans)
- Foreign key relationship validation

### Error Recovery
- Invalid customer numbers are skipped automatically
- Other errors are reported but don't stop the import
- Transaction rollback on critical errors

### Template Download
- Download CSV template with sample data
- Uses actual customer numbers from the database
- Proper format and structure

## Technical Details

### Database Impact
- Creates loan records with proper relationships
- Generates payment and payment item records
- Records GL transactions for accounting
- Creates loan repayment schedules
- Updates related tables (penalties, if applicable)

### Performance
- Processes imports within database transactions
- Efficient bulk processing for large files
- Memory-optimized CSV reading

### Security
- CSRF protection on all import requests
- File type validation (CSV/TXT only)
- Data sanitization and validation
- User permission checks

## Error Messages

Common error types:
- `Customer number 'XXXXX' not found` - Customer doesn't exist (auto-skipped)
- `Invalid amount` - Amount is not numeric or <= 0
- `Invalid date_applied` - Date format or future date issues
- `Invalid loan_officer` - User ID doesn't exist
- `Insufficient collateral` - Customer lacks required collateral
- `Customer already has an active loan` - Duplicate active loan check

## Best Practices

1. **Validate Data**: Check customer numbers exist before importing
2. **Test Small Batches**: Start with small CSV files to test the process
3. **Backup Data**: Ensure database backup before large imports
4. **Review Results**: Check import results and error messages carefully
5. **Use Template**: Download and use the provided CSV template for proper formatting
