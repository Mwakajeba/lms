# SmartFinance - Loan Management System Documentation

## Overview
SmartFinance is a comprehensive loan management system built with Laravel that provides dynamic multi-level approval workflows, customer management, loan processing, and financial tracking capabilities.

## 🚀 Key Features

- **Dynamic Multi-Level Approval System** - Configurable approval workflows based on roles
- **Customer Management** - Complete customer lifecycle management including bulk upload
- **Loan Processing** - End-to-end loan application and disbursement
- **Document Management** - Secure document upload and storage
- **Guarantor Management** - Multiple guarantor support per loan
- **Repayment Tracking** - Automated repayment schedules and tracking
- **Financial Accounting** - Integrated GL transactions and payment processing
- **Role-Based Access Control** - Secure user permissions and access management

---

# 🔄 Multi-Level Approval Process

## Overview

The SmartFinance system implements a **dynamic multi-level approval process** that automatically adapts based on the roles configured in each loan product. The system ensures that **the accountant is always the final approver** for disbursement.

## Approval Workflow Types

### 1. Single Level Approval (1 Role + Accountant)
```
Loan Officer → Accountant (Disbursement)
```
- **Configuration**: `approval_levels = "1"` (Role ID 1)
- **Flow**: Officer checks → Accountant disburses
- **Use Case**: Simple loans requiring minimal oversight

### 2. Two Level Approval (2 Roles + Accountant)
```
First Role → Second Role → Accountant (Disbursement)
```
- **Configuration**: `approval_levels = "1,2"` (Role IDs 1 and 2)
- **Flow**: First role checks → Second role approves → Accountant disburses
- **Use Case**: Medium-risk loans requiring additional review

### 3. Three Level Approval (3 Roles + Accountant)
```
First Role → Second Role → Third Role → Accountant (Disbursement)
```
- **Configuration**: `approval_levels = "1,2,3"` (Role IDs 1, 2, and 3)
- **Flow**: First role checks → Second role approves → Third role authorizes → Accountant disburses
- **Use Case**: High-risk loans requiring maximum oversight

## Dynamic Action Mapping

The system automatically determines the appropriate action for each approval level:

| Level | Action | Description |
|-------|--------|-------------|
| 1 | `check` | Initial review and verification |
| 2 | `approve` | Secondary approval |
| 3 | `authorize` | Final authorization |
| Last | `disburse` | Fund disbursement (Accountant only) |

## Configuration

### Loan Product Setup

1. **Enable Approval Levels**:
   ```php
   $product->has_approval_levels = true;
   ```

2. **Set Approval Roles**:
   ```php
   $product->approval_levels = "1,2,3"; // Comma-separated role IDs
   ```

3. **Role Assignment**:
   - Role ID 1: Loan Officer
   - Role ID 2: Supervisor
   - Role ID 3: Manager
   - Last Role: Accountant (automatic)

### Database Schema

```sql
-- Loan Products Table
ALTER TABLE loan_products ADD COLUMN has_approval_levels BOOLEAN DEFAULT FALSE;
ALTER TABLE loan_products ADD COLUMN approval_levels TEXT NULL;

-- Loan Approvals Table
CREATE TABLE loan_approvals (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    loan_id BIGINT NOT NULL,
    user_id BIGINT NOT NULL,
    role_name VARCHAR(255) NOT NULL,
    approval_level INT NOT NULL,
    action VARCHAR(50) NOT NULL,
    comments TEXT NULL,
    approved_at TIMESTAMP NOT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
);
```

## Implementation Details

### Loan Model Methods

```php
// Get approval roles from product configuration
public function getApprovalRoles()

// Get next approval level
public function getNextApprovalLevel()

// Get next approval role ID
public function getNextApprovalRole()

// Get next action (check/approve/authorize/disburse)
public function getNextApprovalAction()

// Check if user can approve
public function canBeApprovedByUser($user)

// Get role name for display
public function getApprovalLevelName($level)
```

### Controller Methods

```php
// Main approval method (handles all levels)
public function approveLoan($encodedId, Request $request)

// Rejection method
public function rejectLoan($encodedId, Request $request)

// Legacy compatibility methods
public function checkLoan($encodedId, Request $request)
public function authorizeLoan($encodedId, Request $request)
public function disburseLoan($encodedId, Request $request)
```

## User Interface

### Approval Actions Display

The system dynamically shows:
- **Next Required Action**: Based on current approval level
- **Role Information**: Which role needs to approve
- **Approval Flow**: Visual representation of the complete chain
- **Status Indicators**: Current progress through approval levels

### Approval History

Complete audit trail showing:
- **Date & Time**: When each approval occurred
- **Action**: What was done (check/approve/authorize/disburse)
- **Level**: Which approval level was completed
- **User**: Who performed the action
- **Comments**: Any notes left during approval

## Security Features

1. **Role-Based Permissions**: Only users with the correct role can approve
2. **Audit Trail**: Complete history of all approval actions
3. **Status Validation**: Prevents invalid state transitions
4. **Transaction Safety**: Database transactions ensure data integrity

## API Endpoints

```php
// Approval endpoints
POST /loans/{id}/approve
POST /loans/{id}/reject

// Legacy endpoints (for backward compatibility)
POST /loans/{id}/check
POST /loans/{id}/authorize
POST /loans/{id}/disburse
```

---

# 📥 Customer Bulk Upload Feature

## Overview
The bulk upload feature allows you to import multiple customers at once using a CSV file. This feature is accessible from the Customers index page via the "Bulk Upload" button.

## Features
- **CSV Upload**: Upload customer data in CSV format
- **Sample Download**: Download a sample CSV file with the correct format
- **Cash Collateral**: Option to apply cash collateral to all uploaded customers
- **Validation**: Comprehensive validation of CSV data
- **Error Handling**: Detailed error reporting for failed rows
- **Progress Feedback**: Visual feedback during upload process

## How to Use

### 1. Access Bulk Upload
- Navigate to Customers → Click "Bulk Upload" button

### 2. Download Sample CSV
- Click "Download Sample CSV" to get the template
- The sample file contains:
  - Header row with column names
  - Two example customer records
  - Required and optional field examples

### 3. Prepare Your CSV File
The CSV file must contain the following columns:

**Required Columns:**
- `name` - Customer's full name
- `phone1` - Primary phone number
- `dob` - Date of birth (YYYY-MM-DD format)
- `sex` - Gender (M or F)

**Optional Columns:**
- `phone2` - Secondary phone number
- `work` - Occupation
- `workaddress` - Work address
- `idtype` - ID type (e.g., National ID, License)
- `idnumber` - ID number
- `relation` - Relationship (e.g., Spouse, Parent)
- `description` - Customer description
- `region_id` - Region ID (can be added later)
- `district_id` - District ID (can be added later)

### 4. Upload Process
1. Select your prepared CSV file
2. Choose cash collateral options (optional):
   - Check "Apply Cash Collateral to All Customers"
   - Select collateral type if checked
3. Click "Upload Customers"
4. Wait for processing (button will show "Uploading...")
5. Review results

### 5. Results
- **Success**: Redirected to customers list with success message
- **Errors**: Stay on upload page with detailed error messages
- **Partial Success**: Warning message with list of failed rows

## CSV Format Example
```csv
name,phone1,phone2,dob,sex,work,workaddress,idtype,idnumber,relation,description
John Doe,0712345678,0755123456,1990-01-15,M,Teacher,ABC School,National ID,123456789,Spouse,Sample customer
Jane Smith,0723456789,,1985-05-20,F,Nurse,City Hospital,License,987654321,Parent,Another sample
```

## Validation Rules
- File size: Maximum 5MB
- File type: CSV only
- Required fields: name, phone1, dob, sex
- Sex values: M or F only
- Date format: YYYY-MM-DD
- Region and District can be added later through customer edit

## Error Handling
The system provides detailed error messages for:
- Missing required columns
- Invalid data formats
- Missing required fields
- Database constraint violations

## Security Features
- File type validation
- File size limits
- SQL injection prevention
- Transaction rollback on errors
- User authentication required

## Notes
- All customers get default password: 12345
- Customer numbers are auto-generated
- Branch and company are set from logged-in user
- Registrar is set to current user
- Registration date is set to current date
- Cash collateral amount is set to 0 initially
- Region and District can be updated later through customer edit form

---

# 📊 Usage Examples

## Creating a Loan Product with Approval Levels

```php
$product = LoanProduct::create([
    'name' => 'Business Loan',
    'has_approval_levels' => true,
    'approval_levels' => '1,2,3', // Officer, Supervisor, Manager + Accountant
    'minimum_principal' => 1000000,
    'maximum_principal' => 50000000,
    'interest_method' => 'flat_rate',
]);
```

## Processing Loan Approval

```php
// The system automatically determines the next action
$loan = Loan::find(1);
$nextAction = $loan->getNextApprovalAction(); // 'check', 'approve', 'authorize', 'disburse'
$nextRole = $loan->getNextApprovalRole(); // Role ID for next approval
```

## Role Configuration

1. **Create Roles**:
   ```php
   Role::create(['name' => 'Loan Officer', 'id' => 1]);
   Role::create(['name' => 'Supervisor', 'id' => 2]);
   Role::create(['name' => 'Manager', 'id' => 3]);
   Role::create(['name' => 'Accountant', 'id' => 4]);
   ```

2. **Assign Roles to Users**:
   ```php
   $user->assignRole('Loan Officer');
   ```

---

# 🔄 Version History

- **v1.0.0** - Initial release with basic loan management
- **v1.1.0** - Added multi-level approval system
- **v1.2.0** - Dynamic approval workflows based on roles
- **v1.3.0** - Enhanced UI and approval history tracking
- **v1.4.0** - Added customer bulk upload feature

---

**SmartFinance** - Empowering financial institutions with intelligent loan management solutions. 