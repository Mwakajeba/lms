# Multi-Level Loan Approval System

## Overview

The SmartFinance system now includes a comprehensive multi-level approval system for loan applications. This system allows different loan products to have different approval workflows based on configured roles and ensures proper oversight and control over loan disbursements.

## Loan Status Flow

The loan approval system follows this status progression:

1. **Applied** - Initial status when loan application is submitted
2. **Checked** - First level approval (verification)
3. **Approved** - Second level approval (manager approval)
4. **Authorized** - Final level approval (senior management)
5. **Active** - Loan is disbursed and active
6. **Rejected** - Loan application is rejected (can only happen at applied, checked, or approved status)
7. **Defaulted** - Loan is marked as defaulted (final status for bad loans)

## Configuration

### Loan Product Setup

Each loan product can be configured with approval levels:

1. **Enable Approval Levels**: Set `has_approval_levels` to `true`
2. **Configure Approval Roles**: Set `approval_levels` as a comma-separated list of role IDs
3. **Example Configuration**:
   - Level 1: Loan Officer (ID: 1)
   - Level 2: Branch Manager (ID: 2) 
   - Level 3: Credit Manager (ID: 3)

### Role Configuration

The system uses the existing Spatie Permission roles. Common roles for loan approval:

- `loan-officer` - First level approval (check applications)
- `branch-manager` - Second level approval (approve applications)
- `credit-manager` - Third level approval (authorize applications)
- `accountant` - Final disbursement (only accountants can disburse)

## Database Structure

### Loan Approvals Table

```sql
CREATE TABLE loan_approvals (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    loan_id BIGINT NOT NULL,
    user_id BIGINT NOT NULL,
    role_name VARCHAR(255) NOT NULL,
    approval_level INT NOT NULL,
    action ENUM('approved', 'rejected', 'checked') DEFAULT 'checked',
    comments TEXT NULL,
    approved_at TIMESTAMP NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    UNIQUE KEY unique_loan_level (loan_id, approval_level)
);
```

### Loan Model Updates

The Loan model now includes:

- Status constants for all loan statuses
- Approval relationship methods
- Permission checking methods
- Status validation methods

## Approval Process

### 1. Loan Application Submission

When a loan application is submitted:

```php
// If product has approval levels
$loan->status = 'applied';

// If product has no approval levels
$loan->status = 'active'; // Auto-disbursed
```

### 2. First Level - Check

**Who**: Users with the first role in the approval hierarchy
**Action**: Verify loan application details
**Status Change**: `applied` → `checked`

```php
// Create approval record
LoanApproval::create([
    'loan_id' => $loan->id,
    'user_id' => $user->id,
    'role_name' => $user->roles->first()->name,
    'approval_level' => 1,
    'action' => 'checked',
    'comments' => $comments,
    'approved_at' => now(),
]);

// Update loan status
$loan->update(['status' => 'checked']);
```

### 3. Second Level - Approve

**Who**: Users with the second role in the approval hierarchy
**Action**: Approve the loan application
**Status Change**: `checked` → `approved`

```php
// Create approval record
LoanApproval::create([
    'loan_id' => $loan->id,
    'user_id' => $user->id,
    'role_name' => $user->roles->first()->name,
    'approval_level' => 2,
    'action' => 'approved',
    'comments' => $comments,
    'approved_at' => now(),
]);

// Update loan status
$loan->update(['status' => 'approved']);
```

### 4. Third Level - Authorize

**Who**: Users with the third role in the approval hierarchy
**Action**: Final authorization for disbursement
**Status Change**: `approved` → `authorized`

```php
// Create approval record
LoanApproval::create([
    'loan_id' => $loan->id,
    'user_id' => $user->id,
    'role_name' => $user->roles->first()->name,
    'approval_level' => 3,
    'action' => 'approved',
    'comments' => $comments,
    'approved_at' => now(),
]);

// Update loan status
$loan->update(['status' => 'authorized']);
```

### 5. Disbursement

**Who**: Users with `accountant` role
**Action**: Process loan disbursement
**Status Change**: `authorized` → `active`

```php
// Update loan status and process disbursement
$loan->update([
    'status' => 'active',
    'disbursed_on' => now(),
]);

// Generate repayment schedule
$loan->generateRepaymentSchedule($loan->interest);

// Process payment and GL transactions
$this->processLoanDisbursement($loan);
```

## Rejection Process

Loans can be rejected at any stage before authorization:

**Rejectable Statuses**: `applied`, `checked`, `approved`
**Non-Rejectable Statuses**: `authorized`, `active`

```php
// Create rejection record
LoanApproval::create([
    'loan_id' => $loan->id,
    'user_id' => $user->id,
    'role_name' => $user->roles->first()->name,
    'approval_level' => $nextLevel,
    'action' => 'rejected',
    'comments' => $rejectionReason, // Required
    'approved_at' => now(),
]);

// Update loan status
$loan->update(['status' => 'rejected']);
```

## Default Process

Active loans can be marked as defaulted:

```php
// Only active loans can be defaulted
if ($loan->status === 'active') {
    $loan->update(['status' => 'defaulted']);
}
```

## User Interface

### Approval Actions Component

The `x-loan-approval-actions` component displays appropriate action buttons based on:

- Current loan status
- User's roles and permissions
- Next required approval level

### Approval History Component

The `x-loan-approval-history` component displays:

- Timeline of all approvals
- Current status with progress indicator
- Next required action
- Comments from each approval

## API Endpoints

### Approval Routes

```php
// Check loan application
POST /loans/{encodedId}/check

// Approve loan application  
POST /loans/{encodedId}/approve

// Authorize loan application
POST /loans/{encodedId}/authorize

// Disburse authorized loan
POST /loans/{encodedId}/disburse

// Reject loan application
POST /loans/{encodedId}/reject

// Mark loan as defaulted
POST /loans/{encodedId}/default
```

### Request Parameters

All approval endpoints accept:

```json
{
    "comments": "Optional comments about the action"
}
```

Rejection endpoints require:

```json
{
    "comments": "Required reason for rejection"
}
```

## Security & Validation

### Permission Checks

Each approval action validates:

1. **User has required role** for the current approval level
2. **Loan is in correct status** for the action
3. **User has permission** to perform the action

### Status Validation

- Only `applied` loans can be checked
- Only `checked` loans can be approved  
- Only `approved` loans can be authorized
- Only `authorized` loans can be disbursed
- Only `active` loans can be defaulted

### Rejection Rules

- Loans can only be rejected at `applied`, `checked`, or `approved` status
- Rejection requires a reason (comments field)
- Rejection creates a permanent record

## Business Rules

### Approval Hierarchy

1. **Sequential Processing**: Each level must be completed before moving to the next
2. **Role-Based Access**: Only users with the correct role can approve at each level
3. **Audit Trail**: All approvals are recorded with user, timestamp, and comments
4. **Rejection Handling**: Rejected loans cannot be reactivated

### Disbursement Rules

1. **Accountant Only**: Only users with `accountant` role can disburse loans
2. **Full Approval Required**: All approval levels must be completed
3. **Automatic Processing**: Disbursement automatically creates payment records and GL transactions

### Status Management

1. **Immutable History**: Once a loan moves to a higher status, it cannot return to a lower status
2. **Default Status**: Defaulted loans represent the final status for problematic loans
3. **Active Loans**: Only disbursed loans are considered active

## Implementation Examples

### Setting Up a Loan Product

```php
$product = LoanProduct::create([
    'name' => 'Business Loan',
    'has_approval_levels' => true,
    'approval_levels' => '1,2,3', // loan-officer, branch-manager, credit-manager
    // ... other fields
]);
```

### Checking User Permissions

```php
// Check if user can approve this loan
if ($loan->canBeApprovedByUser($user)) {
    // User can take action
}

// Check if loan can be rejected
if ($loan->canBeRejected()) {
    // Loan can be rejected
}
```

### Getting Approval Status

```php
// Get current approval level
$currentLevel = $loan->getCurrentApprovalLevel();

// Get next required level
$nextLevel = $loan->getNextApprovalLevel();

// Check if fully approved
if ($loan->isFullyApproved()) {
    // Ready for disbursement
}
```

## Monitoring & Reporting

### Approval Metrics

- Time spent at each approval level
- Approval/rejection rates by role
- Average processing time
- Bottleneck identification

### Audit Reports

- Complete approval history for each loan
- User activity logs
- Status change tracking
- Comment and reason tracking

## Troubleshooting

### Common Issues

1. **User cannot approve**: Check user roles and loan product configuration
2. **Wrong status**: Ensure loan is in correct status for the action
3. **Missing comments**: Rejection requires comments
4. **Permission denied**: Verify user has required role

### Debug Information

```php
// Check loan approval configuration
$loan->product->has_approval_levels;
$loan->product->approval_levels;

// Check user roles
$user->roles->pluck('id');

// Check current approval status
$loan->getApprovalStatus();
$loan->getCurrentApprovalLevel();
$loan->getNextApprovalLevel();
```

## Future Enhancements

1. **Email Notifications**: Automatic notifications to next approver
2. **SLA Tracking**: Time-based alerts for overdue approvals
3. **Bulk Operations**: Approve multiple loans at once
4. **Delegation**: Allow users to delegate approval authority
5. **Conditional Approvals**: Different approval paths based on loan amount
6. **Mobile Approval**: Mobile-friendly approval interface 