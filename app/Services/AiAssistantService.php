<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\Company;
use App\Models\Branch;
use App\Models\User;

class AiAssistantService
{
    protected $company;
    protected $user;

    public function __construct()
    {
        try {
            $this->company = Company::find(current_company_id());
        } catch (\Exception $e) {
            $this->company = null;
        }
        $this->user = Auth::user();
    }

    /**
     * Process user message and generate AI response
     */
    public function processMessage($message)
    {
        try {
            $message = strtolower(trim($message));
            
            // System assistance and guidance (check these first for more specific matches)
            if ($this->containsKeywords($message, ['add user', 'create user', 'new user', 'user management'])) {
                return $this->generateUserManagementGuide($message);
            }
            
            if ($this->containsKeywords($message, ['backup', 'restore', 'data backup'])) {
                return $this->generateBackupGuide($message);
            }
            
            if ($this->containsKeywords($message, ['company', 'branch', 'organization'])) {
                return $this->generateCompanyManagementGuide($message);
            }
            
            if ($this->containsKeywords($message, ['settings', 'configure', 'setup'])) {
                return $this->generateSettingsGuide($message);
            }
            
            if ($this->containsKeywords($message, ['permission', 'role', 'access'])) {
                return $this->generatePermissionGuide($message);
            }
            
            // Check for different types of reports
            if ($this->containsKeywords($message, ['sales', 'report', 'revenue'])) {
                return $this->generateSalesReport($message);
            }
            
            if ($this->containsKeywords($message, ['financial', 'summary', 'finance', 'money'])) {
                return $this->generateFinancialSummary($message);
            }
            
            if ($this->containsKeywords($message, ['customer', 'client'])) {
                return $this->generateCustomerAnalysis($message);
            }
            
            if ($this->containsKeywords($message, ['inventory', 'stock', 'product'])) {
                return $this->generateInventoryReport($message);
            }
            
            if ($this->containsKeywords($message, ['profit', 'loss', 'p&l', 'income'])) {
                return $this->generateProfitLossStatement($message);
            }
            
            if ($this->containsKeywords($message, ['backup', 'restore', 'data backup'])) {
                return $this->generateBackupGuide($message);
            }
            
            if ($this->containsKeywords($message, ['company', 'branch', 'organization'])) {
                return $this->generateCompanyManagementGuide($message);
            }
            
            if ($this->containsKeywords($message, ['settings', 'configure', 'setup'])) {
                return $this->generateSettingsGuide($message);
            }
            
            if ($this->containsKeywords($message, ['permission', 'role', 'access'])) {
                return $this->generatePermissionGuide($message);
            }
            
            if ($this->containsKeywords($message, ['help', 'assist', 'support', 'guide'])) {
                return $this->generateHelpResponse();
            }
            
            // Default response for unrecognized requests
            return $this->generateDefaultResponse($message);
        } catch (\Exception $e) {
            return "I apologize, but I encountered an error while processing your request: " . $e->getMessage();
        }
    }

    /**
     * Generate sales report
     */
    protected function generateSalesReport($message)
    {
        $period = $this->extractPeriod($message);
        $companyName = $this->company->name ?? 'Your Company';
        
        // Mock data - in real implementation, you would query your database
        $totalSales = rand(50000, 200000);
        $totalOrders = rand(100, 500);
        $avgOrderValue = round($totalSales / $totalOrders, 2);
        $growthRate = rand(5, 25);
        
        $report = "## 📊 Sales Report - {$period}\n\n";
        $report .= "**Company:** {$companyName}\n";
        $report .= "**Report Period:** {$period}\n";
        $report .= "**Generated:** " . now()->format('Y-m-d H:i:s') . "\n\n";
        
        $report .= "### Key Metrics:\n";
        $report .= "- **Total Sales:** $" . number_format($totalSales, 2) . "\n";
        $report .= "- **Total Orders:** " . number_format($totalOrders) . "\n";
        $report .= "- **Average Order Value:** $" . number_format($avgOrderValue, 2) . "\n";
        $report .= "- **Growth Rate:** +{$growthRate}% vs previous period\n\n";
        
        $report .= "### Insights:\n";
        $report .= "- Sales performance is **strong** with {$growthRate}% growth\n";
        $report .= "- Average order value indicates **healthy** customer spending\n";
        $report .= "- Consider **promotional campaigns** for growth\n\n";
        
        $report .= "### Recommendations:\n";
        $report .= "1. **Increase marketing** for top categories\n";
        $report .= "2. **Bundle deals** to boost average order value\n";
        $report .= "3. **Customer loyalty program** to boost repeat purchases\n";
        
        return $report;
    }

    /**
     * Generate financial summary
     */
    protected function generateFinancialSummary($message)
    {
        $period = $this->extractPeriod($message);
        $companyName = $this->company->name ?? 'Your Company';
        
        // Mock financial data
        $revenue = rand(100000, 500000);
        $expenses = rand(60000, 300000);
        $profit = $revenue - $expenses;
        $profitMargin = round(($profit / $revenue) * 100, 2);
        
        $report = "## 💰 Financial Summary - {$period}\n\n";
        $report .= "**Company:** {$companyName}\n";
        $report .= "**Report Period:** {$period}\n";
        $report .= "**Generated:** " . now()->format('Y-m-d H:i:s') . "\n\n";
        
        $report .= "### Financial Overview:\n";
        $report .= "- **Total Revenue:** $" . number_format($revenue, 2) . "\n";
        $report .= "- **Total Expenses:** $" . number_format($expenses, 2) . "\n";
        $report .= "- **Net Profit:** $" . number_format($profit, 2) . "\n";
        $report .= "- **Profit Margin:** {$profitMargin}%\n\n";
        
        $report .= "### Financial Health Assessment:\n";
        if ($profitMargin > 20) {
            $report .= "✅ **Excellent** - Strong profitability with {$profitMargin}% margin\n";
        } elseif ($profitMargin > 10) {
            $report .= "✅ **Good** - Healthy profitability with {$profitMargin}% margin\n";
        } else {
            $report .= "⚠️ **Needs Attention** - Low profitability with {$profitMargin}% margin\n";
        }
        
        $report .= "\n### Recommendations:\n";
        $report .= "1. **Cost optimization** in operating expenses\n";
        $report .= "2. **Revenue diversification** strategies\n";
        $report .= "3. **Cash flow management** improvements\n";
        
        return $report;
    }

    /**
     * Generate customer analysis
     */
    protected function generateCustomerAnalysis($message)
    {
        $companyName = $this->company->name ?? 'Your Company';
        
        // Mock customer data
        $totalCustomers = rand(1000, 5000);
        $newCustomers = rand(50, 200);
        $repeatCustomers = rand(300, 800);
        $avgCustomerValue = rand(150, 500);
        
        $report = "## 👥 Customer Analysis Report\n\n";
        $report .= "**Company:** {$companyName}\n";
        $report .= "**Report Period:** Current Month\n";
        $report .= "**Generated:** " . now()->format('Y-m-d H:i:s') . "\n\n";
        
        $report .= "### Customer Metrics:\n";
        $report .= "- **Total Customers:** " . number_format($totalCustomers) . "\n";
        $report .= "- **New Customers:** " . number_format($newCustomers) . " (" . round(($newCustomers / $totalCustomers) * 100, 1) . "%)\n";
        $report .= "- **Repeat Customers:** " . number_format($repeatCustomers) . " (" . round(($repeatCustomers / $totalCustomers) * 100, 1) . "%)\n";
        $report .= "- **Average Customer Value:** $" . number_format($avgCustomerValue, 2) . "\n\n";
        
        $report .= "### Customer Insights:\n";
        $report .= "✅ **Strong retention** with " . rand(70, 90) . "% repeat customers\n";
        $report .= "✅ **High satisfaction** scores indicate good service quality\n";
        $report .= "✅ **Growing customer base** with new acquisitions\n";
        $report .= "⚠️ **Opportunity** to increase average customer value\n\n";
        
        $report .= "### Recommendations:\n";
        $report .= "1. **Loyalty program** for repeat customers\n";
        $report .= "2. **Personalized marketing** campaigns\n";
        $report .= "3. **Customer feedback** collection and analysis\n";
        
        return $report;
    }

    /**
     * Generate inventory report
     */
    protected function generateInventoryReport($message)
    {
        $companyName = $this->company->name ?? 'Your Company';
        
        // Mock inventory data
        $totalItems = rand(500, 2000);
        $lowStockItems = rand(20, 100);
        $outOfStockItems = rand(5, 30);
        $totalValue = rand(50000, 200000);
        
        $report = "## 📦 Inventory Report\n\n";
        $report .= "**Company:** {$companyName}\n";
        $report .= "**Report Period:** Current Status\n";
        $report .= "**Generated:** " . now()->format('Y-m-d H:i:s') . "\n\n";
        
        $report .= "### Inventory Overview:\n";
        $report .= "- **Total Items:** " . number_format($totalItems) . "\n";
        $report .= "- **Low Stock Items:** " . number_format($lowStockItems) . " (" . round(($lowStockItems / $totalItems) * 100, 1) . "%)\n";
        $report .= "- **Out of Stock:** " . number_format($outOfStockItems) . " (" . round(($outOfStockItems / $totalItems) * 100, 1) . "%)\n";
        $report .= "- **Total Inventory Value:** $" . number_format($totalValue, 2) . "\n\n";
        
        $report .= "### Critical Alerts:\n";
        if ($outOfStockItems > 0) {
            $report .= "🚨 **{$outOfStockItems} items out of stock** - Immediate reorder needed\n";
        }
        if ($lowStockItems > 50) {
            $report .= "⚠️ **{$lowStockItems} items low on stock** - Monitor closely\n";
        }
        $report .= "✅ **Inventory accuracy is high** - Good management\n\n";
        
        $report .= "### Recommendations:\n";
        $report .= "1. **Reorder** out-of-stock items immediately\n";
        $report .= "2. **Review** low stock items for reorder planning\n";
        $report .= "3. **Optimize** inventory levels based on demand\n";
        
        return $report;
    }

    /**
     * Generate profit and loss statement
     */
    protected function generateProfitLossStatement($message)
    {
        $period = $this->extractPeriod($message);
        $companyName = $this->company->name ?? 'Your Company';
        
        // Mock P&L data
        $revenue = rand(200000, 800000);
        $cogs = rand(120000, 400000);
        $grossProfit = $revenue - $cogs;
        $operatingExpenses = rand(80000, 200000);
        $operatingIncome = $grossProfit - $operatingExpenses;
        $netIncome = $operatingIncome;
        
        $report = "## 📈 Profit & Loss Statement - {$period}\n\n";
        $report .= "**Company:** {$companyName}\n";
        $report .= "**Report Period:** {$period}\n";
        $report .= "**Generated:** " . now()->format('Y-m-d H:i:s') . "\n\n";
        
        $report .= "### Revenue & Cost of Goods Sold:\n";
        $report .= "- **Total Revenue:** $" . number_format($revenue, 2) . "\n";
        $report .= "- **Cost of Goods Sold:** $" . number_format($cogs, 2) . "\n";
        $report .= "- **Gross Profit:** $" . number_format($grossProfit, 2) . " (" . round(($grossProfit / $revenue) * 100, 1) . "%)\n\n";
        
        $report .= "### Operating Expenses:\n";
        $report .= "- **Total Operating Expenses:** $" . number_format($operatingExpenses, 2) . "\n";
        $report .= "- **Operating Income:** $" . number_format($operatingIncome, 2) . " (" . round(($operatingIncome / $revenue) * 100, 1) . "%)\n\n";
        
        $report .= "### Net Income:\n";
        $report .= "- **Net Income:** $" . number_format($netIncome, 2) . " (" . round(($netIncome / $revenue) * 100, 1) . "%)\n\n";
        
        $report .= "### Financial Analysis:\n";
        if ($netIncome > 0) {
            $report .= "✅ **Profitable** - Positive net income of $" . number_format($netIncome, 2) . "\n";
        } else {
            $report .= "❌ **Loss** - Negative net income of $" . number_format(abs($netIncome), 2) . "\n";
        }
        
        $report .= "\n### Recommendations:\n";
        $report .= "1. **Optimize** cost of goods sold for better margins\n";
        $report .= "2. **Review** operating expenses for cost reduction\n";
        $report .= "3. **Increase** revenue through marketing and sales\n";
        
        return $report;
    }

    /**
     * Generate help response
     */
    protected function generateHelpResponse()
    {
        $response = "## 🤖 AI Assistant Help\n\n";
        $response .= "I'm here to help you with both business reports and system guidance. Here's what I can do:\n\n";
        
        $response .= "### 📊 Business Reports:\n";
        $response .= "- **Sales Reports** - Monthly, quarterly, or annual sales analysis\n";
        $response .= "- **Financial Summaries** - Revenue, expenses, and profitability\n";
        $response .= "- **Customer Analysis** - Customer behavior and demographics\n";
        $response .= "- **Inventory Reports** - Stock levels and management\n";
        $response .= "- **Profit & Loss Statements** - Detailed financial performance\n\n";
        
        $response .= "### 🛠️ System Guidance:\n";
        $response .= "- **User Management** - How to add, edit, and manage users\n";
        $response .= "- **Backup & Restore** - Creating and restoring data backups\n";
        $response .= "- **Company & Branch Setup** - Managing organizations and locations\n";
        $response .= "- **System Settings** - Configuring and customizing the system\n";
        $response .= "- **Permissions & Roles** - Managing user access and security\n\n";
        
        $response .= "### 💡 Example Prompts:\n";
        $response .= "**For Reports:**\n";
        $response .= "- \"Generate a sales report for this month\"\n";
        $response .= "- \"Create a financial summary for Q3\"\n";
        $response .= "- \"Analyze customer data and provide insights\"\n";
        $response .= "- \"Show me an inventory report\"\n";
        $response .= "- \"Create a profit and loss statement\"\n\n";
        
        $response .= "**For System Help:**\n";
        $response .= "- \"How do I add a new user?\"\n";
        $response .= "- \"Help me set up a backup\"\n";
        $response .= "- \"How to manage company settings?\"\n";
        $response .= "- \"What permissions do I need?\"\n";
        $response .= "- \"How do I create a new branch?\"\n\n";
        
        $response .= "### 🚀 Quick Actions:\n";
        $response .= "Use the **Quick Actions** buttons on the right for common tasks:\n";
        $response .= "- Sales Report\n";
        $response .= "- Financial Summary\n";
        $response .= "- Customer Analysis\n";
        $response .= "- Inventory Report\n";
        $response .= "- P&L Statement\n\n";
        
        $response .= "### 📤 Export Options:\n";
        $response .= "After I generate a response, you can:\n";
        $response .= "- **Export as PDF** - Professional formatted reports\n";
        $response .= "- **Export as Excel** - Data for further analysis\n";
        $response .= "- **Copy to Clipboard** - Quick sharing\n\n";
        
        $response .= "Just ask me anything - I'm here to help with both business insights and system guidance!";
        
        return $response;
    }

    /**
     * Generate user management guide
     */
    protected function generateUserManagementGuide($message)
    {
        $response = "## 👤 User Management Guide\n\n";
        $response .= "I'll help you understand how to manage users in the system.\n\n";
        
        $response .= "### 📋 How to Add a New User:\n\n";
        $response .= "**Step 1: Access User Management**\n";
        $response .= "- Go to **Settings** → **User Settings**\n";
        $response .= "- Or navigate to the main **Users** menu\n\n";
        
        $response .= "**Step 2: Create New User**\n";
        $response .= "- Click the **\"Add New User\"** or **\"Create User\"** button\n";
        $response .= "- Fill in the required information:\n";
        $response .= "  - **Full Name** (required)\n";
        $response .= "  - **Email Address** (required, must be unique)\n";
        $response .= "  - **Username** (optional, for login)\n";
        $response .= "  - **Password** (required, minimum 8 characters)\n";
        $response .= "  - **Role** (select appropriate role: Admin, Manager, User)\n";
        $response .= "  - **Company/Branch** (assign to specific company or branch)\n";
        $response .= "  - **Status** (Active/Inactive)\n\n";
        
        $response .= "**Step 3: Set Permissions**\n";
        $response .= "- Assign appropriate **roles and permissions**\n";
        $response .= "- Configure **access levels** based on user responsibilities\n";
        $response .= "- Set **branch/company restrictions** if needed\n\n";
        
        $response .= "**Step 4: Save and Notify**\n";
        $response .= "- Click **\"Save\"** or **\"Create User\"**\n";
        $response .= "- The system will send a **welcome email** to the new user\n";
        $response .= "- User can now **log in** with their credentials\n\n";
        
        $response .= "### 🔧 User Management Features:\n";
        $response .= "- **Edit User**: Modify user information and permissions\n";
        $response .= "- **Deactivate User**: Temporarily disable user access\n";
        $response .= "- **Reset Password**: Send password reset link\n";
        $response .= "- **User Profile**: View detailed user information\n";
        $response .= "- **Activity Log**: Track user actions and login history\n\n";
        
        $response .= "### ⚠️ Important Notes:\n";
        $response .= "- **Email addresses must be unique** across the system\n";
        $response .= "- **Passwords should be strong** (8+ characters, mix of letters/numbers)\n";
        $response .= "- **Roles determine access levels** - choose carefully\n";
        $response .= "- **Users can be assigned to specific branches** for multi-branch setups\n";
        $response .= "- **Inactive users cannot log in** but their data is preserved\n\n";
        
        $response .= "### 🆘 Need More Help?\n";
        $response .= "If you encounter any issues:\n";
        $response .= "- Check that the email address isn't already in use\n";
        $response .= "- Ensure all required fields are filled\n";
        $response .= "- Verify that the selected role has appropriate permissions\n";
        $response .= "- Contact your system administrator for role-related issues\n\n";
        
        $response .= "**Quick Tip**: You can also bulk import users using CSV files if you have many users to add at once!";
        
        return $response;
    }

    /**
     * Generate backup guide
     */
    protected function generateBackupGuide($message)
    {
        $response = "## 💾 Backup & Restore Guide\n\n";
        $response .= "I'll help you understand how to backup and restore your data safely.\n\n";
        
        $response .= "### 📤 Creating Backups:\n\n";
        $response .= "**Step 1: Access Backup Settings**\n";
        $response .= "- Go to **Settings** → **Backup Settings**\n";
        $response .= "- You'll see the backup dashboard with statistics\n\n";
        
        $response .= "**Step 2: Choose Backup Type**\n";
        $response .= "- **Database Only**: Backs up all data (recommended for regular backups)\n";
        $response .= "- **Files Only**: Backs up uploaded files and documents\n";
        $response .= "- **Full Backup**: Complete backup of database and files\n\n";
        
        $response .= "**Step 3: Create Backup**\n";
        $response .= "- Select the backup type from the dropdown\n";
        $response .= "- Add an optional description (e.g., \"Monthly backup - July 2025\")\n";
        $response .= "- Click **\"Create Backup\"**\n";
        $response .= "- Wait for the backup to complete (you'll see a success message)\n\n";
        
        $response .= "### 📥 Restoring Backups:\n\n";
        $response .= "**⚠️ Important Warning**: Restoring will overwrite current data!\n\n";
        $response .= "**Step 1: Select Backup**\n";
        $response .= "- Go to the **Backup History** section\n";
        $response .= "- Find the backup you want to restore\n";
        $response .= "- Click the **restore icon** (🔄) next to the backup\n\n";
        
        $response .= "**Step 2: Confirm Restore**\n";
        $response .= "- Review the backup details carefully\n";
        $response .= "- Confirm that you want to restore this backup\n";
        $response .= "- The system will restore all data from that backup\n\n";
        
        $response .= "### 📊 Backup Management:\n";
        $response .= "- **Download Backups**: Click the download icon to save locally\n";
        $response .= "- **Delete Old Backups**: Remove backups you no longer need\n";
        $response .= "- **Clean Old Backups**: Automatically remove backups older than X days\n";
        $response .= "- **Backup Statistics**: View total backups, sizes, and status\n\n";
        
        $response .= "### 🔒 Security Best Practices:\n";
        $response .= "- **Store backups securely** - don't leave them in public folders\n";
        $response .= "- **Test restores regularly** to ensure backups work correctly\n";
        $response .= "- **Keep multiple backup copies** in different locations\n";
        $response .= "- **Document your backup procedures** for team members\n";
        $response .= "- **Monitor backup success** and investigate any failures\n\n";
        
        $response .= "### 🆘 Troubleshooting:\n";
        $response .= "**If backup fails:**\n";
        $response .= "- Check available disk space\n";
        $response .= "- Verify database connection\n";
        $response .= "- Check file permissions\n";
        $response .= "- Review system logs for errors\n\n";
        
        $response .= "**If restore fails:**\n";
        $response .= "- Ensure backup file is not corrupted\n";
        $response .= "- Check database compatibility\n";
        $response .= "- Verify you have sufficient permissions\n";
        $response .= "- Contact system administrator if issues persist\n\n";
        
        $response .= "### 💡 Pro Tips:\n";
        $response .= "- **Schedule regular backups** (daily/weekly depending on data changes)\n";
        $response .= "- **Use descriptive names** for backups to easily identify them\n";
        $response .= "- **Keep at least 3 recent backups** for safety\n";
        $response .= "- **Test your backup strategy** before you need it!";
        
        return $response;
    }

    /**
     * Generate company management guide
     */
    protected function generateCompanyManagementGuide($message)
    {
        $response = "## 🏢 Company & Branch Management Guide\n\n";
        $response .= "I'll help you understand how to manage companies and branches in the system.\n\n";
        
        $response .= "### 🏢 Company Management:\n\n";
        $response .= "**Adding a New Company:**\n";
        $response .= "1. Go to **Settings** → **Company Settings**\n";
        $response .= "2. Click **\"Add New Company\"** or **\"Create Company\"**\n";
        $response .= "3. Fill in company details:\n";
        $response .= "   - **Company Name** (required)\n";
        $response .= "   - **Business Registration Number**\n";
        $response .= "   - **Tax ID/VAT Number**\n";
        $response .= "   - **Address** (street, city, state, postal code)\n";
        $response .= "   - **Contact Information** (phone, email, website)\n";
        $response .= "   - **Industry/Business Type**\n";
        $response .= "   - **Company Logo** (optional)\n";
        $response .= "4. Click **\"Save Company\"**\n\n";
        
        $response .= "**Editing Company Information:**\n";
        $response .= "- Go to **Settings** → **Company Settings**\n";
        $response .= "- Click **\"Edit\"** next to the company\n";
        $response .= "- Update the information as needed\n";
        $response .= "- Save changes\n\n";
        
        $response .= "### 🌿 Branch Management:\n\n";
        $response .= "**Adding a New Branch:**\n";
        $response .= "1. Go to **Settings** → **Branch Settings**\n";
        $response .= "2. Click **\"Add New Branch\"** or **\"Create Branch\"**\n";
        $response .= "3. Fill in branch details:\n";
        $response .= "   - **Branch Name** (required)\n";
        $response .= "   - **Parent Company** (select from dropdown)\n";
        $response .= "   - **Branch Code** (unique identifier)\n";
        $response .= "   - **Address** (complete address)\n";
        $response .= "   - **Contact Information** (phone, email)\n";
        $response .= "   - **Branch Manager** (assign from users)\n";
        $response .= "   - **Status** (Active/Inactive)\n";
        $response .= "4. Click **\"Save Branch\"**\n\n";
        
        $response .= "**Managing Branches:**\n";
        $response .= "- **View All Branches**: See list of all branches\n";
        $response .= "- **Edit Branch**: Modify branch information\n";
        $response .= "- **Deactivate Branch**: Temporarily disable branch\n";
        $response .= "- **Assign Users**: Link users to specific branches\n";
        $response .= "- **Branch Reports**: Generate branch-specific reports\n\n";
        
        $response .= "### 🔧 Multi-Company Setup:\n";
        $response .= "**For organizations with multiple companies:**\n";
        $response .= "- Each company can have its own **separate data**\n";
        $response .= "- **Users can be assigned** to specific companies\n";
        $response .= "- **Reports can be filtered** by company\n";
        $response .= "- **Backups can be company-specific**\n\n";
        
        $response .= "### 📊 Branch Operations:\n";
        $response .= "**Branch-specific features:**\n";
        $response .= "- **Local user management** for each branch\n";
        $response .= "- **Branch-specific settings** and configurations\n";
        $response .= "- **Local reporting** and analytics\n";
        $response .= "- **Inventory management** by branch\n";
        $response .= "- **Financial tracking** per branch\n\n";
        
        $response .= "### ⚠️ Important Considerations:\n";
        $response .= "- **Company names must be unique**\n";
        $response .= "- **Branch codes must be unique** within a company\n";
        $response .= "- **Deleting a company** will affect all associated branches\n";
        $response .= "- **Branch managers** must be existing users\n";
        $response .= "- **Inactive branches** won't appear in regular operations\n\n";
        
        $response .= "### 🆘 Common Issues:\n";
        $response .= "**If you can't add a company/branch:**\n";
        $response .= "- Check that the name/code isn't already in use\n";
        $response .= "- Ensure all required fields are filled\n";
        $response .= "- Verify you have the necessary permissions\n";
        $response .= "- Check that the parent company exists (for branches)\n\n";
        
        $response .= "### 💡 Best Practices:\n";
        $response .= "- **Use consistent naming conventions** for companies and branches\n";
        $response .= "- **Keep branch codes short and memorable**\n";
        $response .= "- **Regularly review and update** company/branch information\n";
        $response .= "- **Document your organizational structure**\n";
        $response .= "- **Train users** on company/branch-specific procedures";
        
        return $response;
    }

    /**
     * Generate settings guide
     */
    protected function generateSettingsGuide($message)
    {
        $response = "## ⚙️ System Settings Guide\n\n";
        $response .= "I'll help you understand how to configure and manage system settings.\n\n";
        
        $response .= "### 🏢 Company Settings:\n\n";
        $response .= "**Access**: Settings → Company Settings\n\n";
        $response .= "**What you can configure:**\n";
        $response .= "- **Company Information**: Name, address, contact details\n";
        $response .= "- **Business Details**: Registration numbers, tax IDs\n";
        $response .= "- **Company Logo**: Upload and manage company branding\n";
        $response .= "- **Default Settings**: Currency, timezone, language\n";
        $response .= "- **Contact Information**: Phone, email, website\n\n";
        
        $response .= "### 👥 User Settings:\n\n";
        $response .= "**Access**: Settings → User Settings\n\n";
        $response .= "**Personal Configuration:**\n";
        $response .= "- **Profile Information**: Name, email, phone\n";
        $response .= "- **Password Management**: Change password, security settings\n";
        $response .= "- **Preferences**: Language, timezone, notifications\n";
        $response .= "- **Security Settings**: Two-factor authentication, login history\n";
        $response .= "- **API Keys**: Manage API access tokens\n\n";
        
        $response .= "### 🌿 Branch Settings:\n\n";
        $response .= "**Access**: Settings → Branch Settings\n\n";
        $response .= "**Branch Management:**\n";
        $response .= "- **Add/Edit Branches**: Create and manage branch locations\n";
        $response .= "- **Branch Information**: Address, contact details, managers\n";
        $response .= "- **Branch Status**: Activate/deactivate branches\n";
        $response .= "- **User Assignment**: Assign users to specific branches\n\n";
        
        $response .= "### 💾 Backup Settings:\n\n";
        $response .= "**Access**: Settings → Backup Settings\n\n";
        $response .= "**Backup Management:**\n";
        $response .= "- **Create Backups**: Database, files, or full backups\n";
        $response .= "- **Restore Data**: Restore from previous backups\n";
        $response .= "- **Download Backups**: Save backups locally\n";
        $response .= "- **Clean Old Backups**: Remove outdated backups\n";
        $response .= "- **Backup Statistics**: View backup history and sizes\n\n";
        
        $response .= "### 🤖 AI Assistant Settings:\n\n";
        $response .= "**Access**: Settings → AI Assistant\n\n";
        $response .= "**AI Features:**\n";
        $response .= "- **Chat Interface**: Ask questions and get AI-powered responses\n";
        $response .= "- **Report Generation**: Create intelligent business reports\n";
        $response .= "- **Quick Actions**: Pre-defined report templates\n";
        $response .= "- **Export Options**: PDF, Excel, and clipboard export\n";
        $response .= "- **Conversation History**: Review previous AI interactions\n\n";
        
        $response .= "### 🔧 System Settings:\n\n";
        $response .= "**Access**: Settings → System Settings\n\n";
        $response .= "**System Configuration:**\n";
        $response .= "- **Application Name**: Customize system name\n";
        $response .= "- **Application URL**: Set system URL\n";
        $response .= "- **Timezone**: Configure system timezone\n";
        $response .= "- **Locale**: Set language and regional settings\n";
        $response .= "- **Email Configuration**: SMTP settings for notifications\n";
        $response .= "- **File Upload Settings**: Configure file size limits\n";
        $response .= "- **Security Settings**: Session timeout, password policies\n\n";
        
        $response .= "### 🔐 Security Settings:\n";
        $response .= "- **Password Policies**: Minimum length, complexity requirements\n";
        $response .= "- **Session Management**: Timeout settings, concurrent logins\n";
        $response .= "- **Two-Factor Authentication**: Enable/disable 2FA\n";
        $response .= "- **Login Attempts**: Configure failed login handling\n";
        $response .= "- **IP Restrictions**: Limit access to specific IP addresses\n\n";
        
        $response .= "### 📧 Notification Settings:\n";
        $response .= "- **Email Notifications**: Configure email alerts\n";
        $response .= "- **System Alerts**: Set up system-wide notifications\n";
        $response .= "- **User Notifications**: Manage user-specific alerts\n";
        $response .= "- **Backup Notifications**: Alerts for backup success/failure\n\n";
        
        $response .= "### ⚠️ Important Notes:\n";
        $response .= "- **Some settings require admin privileges**\n";
        $response .= "- **Changes may affect all users** in the system\n";
        $response .= "- **Backup before making major changes**\n";
        $response .= "- **Test changes in a development environment first**\n";
        $response .= "- **Document any custom configurations**\n\n";
        
        $response .= "### 🆘 Need Help?\n";
        $response .= "If you're unsure about a setting:\n";
        $response .= "- **Check the help text** next to each setting\n";
        $response .= "- **Review the documentation** for detailed explanations\n";
        $response .= "- **Contact your system administrator** for sensitive changes\n";
        $response .= "- **Use the AI Assistant** for guidance on specific settings\n\n";
        
        $response .= "### 💡 Pro Tips:\n";
        $response .= "- **Regularly review settings** to ensure they meet current needs\n";
        $response .= "- **Keep a record** of any custom configurations\n";
        $response .= "- **Test new settings** before applying to production\n";
        $response .= "- **Involve key users** in setting decisions that affect them";
        
        return $response;
    }

    /**
     * Generate permission guide
     */
    protected function generatePermissionGuide($message)
    {
        $response = "## 🔐 Permissions & Roles Guide\n\n";
        $response .= "I'll help you understand how to manage user permissions and roles in the system.\n\n";
        
        $response .= "### 👥 Understanding Roles:\n\n";
        $response .= "**System Roles:**\n";
        $response .= "- **Super Admin**: Full system access, can manage all companies\n";
        $response .= "- **Admin**: Company-level admin, manages company and branches\n";
        $response .= "- **Manager**: Branch-level management, oversees operations\n";
        $response .= "- **User**: Standard user with limited access\n";
        $response .= "- **Viewer**: Read-only access to assigned areas\n\n";
        
        $response .= "### 🔑 Permission Categories:\n\n";
        $response .= "**User Management:**\n";
        $response .= "- **Create Users**: Add new users to the system\n";
        $response .= "- **Edit Users**: Modify user information\n";
        $response .= "- **Delete Users**: Remove users from the system\n";
        $response .= "- **View Users**: See user lists and profiles\n";
        $response .= "- **Assign Roles**: Give users specific roles\n\n";
        
        $response .= "**Company Management:**\n";
        $response .= "- **Create Companies**: Add new companies\n";
        $response .= "- **Edit Companies**: Modify company information\n";
        $response .= "- **Delete Companies**: Remove companies\n";
        $response .= "- **View Companies**: Access company data\n\n";
        
        $response .= "**Branch Management:**\n";
        $response .= "- **Create Branches**: Add new branches\n";
        $response .= "- **Edit Branches**: Modify branch information\n";
        $response .= "- **Delete Branches**: Remove branches\n";
        $response .= "- **View Branches**: Access branch data\n";
        $response .= "- **Assign Users to Branches**: Link users to branches\n\n";
        
        $response .= "**Financial Access:**\n";
        $response .= "- **View Financial Data**: Access financial reports\n";
        $response .= "- **Create Transactions**: Add new financial entries\n";
        $response .= "- **Edit Transactions**: Modify existing entries\n";
        $response .= "- **Delete Transactions**: Remove financial entries\n";
        $response .= "- **Approve Transactions**: Authorize financial changes\n\n";
        
        $response .= "**System Settings:**\n";
        $response .= "- **View Settings**: Access system configuration\n";
        $response .= "- **Edit Settings**: Modify system settings\n";
        $response .= "- **Backup Management**: Create and restore backups\n";
        $response .= "- **Security Settings**: Configure security policies\n\n";
        
        $response .= "### 🛠️ Managing Permissions:\n\n";
        $response .= "**Assigning Roles to Users:**\n";
        $response .= "1. Go to **User Management** or **Settings** → **User Settings**\n";
        $response .= "2. Find the user you want to modify\n";
        $response .= "3. Click **\"Edit\"** or **\"Manage Permissions\"**\n";
        $response .= "4. Select the appropriate **role** from the dropdown\n";
        $response .= "5. **Save** the changes\n\n";
        
        $response .= "**Creating Custom Roles:**\n";
        $response .= "1. Go to **Settings** → **Role Management**\n";
        $response .= "2. Click **\"Create New Role\"**\n";
        $response .= "3. Enter **role name** and **description**\n";
        $response .= "4. Select **permissions** for this role\n";
        $response .= "5. **Save** the new role\n\n";
        
        $response .= "**Modifying Existing Roles:**\n";
        $response .= "1. Go to **Settings** → **Role Management**\n";
        $response .= "2. Find the role you want to modify\n";
        $response .= "3. Click **\"Edit\"**\n";
        $response .= "4. Modify **permissions** as needed\n";
        $response .= "5. **Save** changes\n\n";
        
        $response .= "### 🔒 Security Best Practices:\n";
        $response .= "- **Principle of Least Privilege**: Give users only necessary permissions\n";
        $response .= "- **Regular Reviews**: Periodically review user permissions\n";
        $response .= "- **Role-Based Access**: Use roles instead of individual permissions\n";
        $response .= "- **Documentation**: Keep records of permission assignments\n";
        $response .= "- **Testing**: Test permissions before applying to production\n\n";
        
        $response .= "### ⚠️ Important Considerations:\n";
        $response .= "- **Role changes affect all users** with that role\n";
        $response .= "- **Some permissions are hierarchical** (admin includes user permissions)\n";
        $response .= "- **Financial permissions** should be carefully controlled\n";
        $response .= "- **System settings** should be restricted to administrators\n";
        $response .= "- **Backup permissions** should be limited to trusted users\n\n";
        
        $response .= "### 🆘 Common Permission Issues:\n";
        $response .= "**If a user can't access something:**\n";
        $response .= "- Check their **assigned role**\n";
        $response .= "- Verify **specific permissions** for that feature\n";
        $response .= "- Ensure they're **assigned to the correct company/branch**\n";
        $response .= "- Check if the **feature is enabled** in system settings\n\n";
        
        $response .= "**If permissions aren't working:**\n";
        $response .= "- **Clear user cache** and have them log out/in\n";
        $response .= "- **Check role assignments** are saved correctly\n";
        $response .= "- **Verify permission inheritance** from roles\n";
        $response .= "- **Review system logs** for permission errors\n\n";
        
        $response .= "### 💡 Pro Tips:\n";
        $response .= "- **Create role templates** for common job functions\n";
        $response .= "- **Use descriptive role names** (e.g., \"Branch Manager\" vs \"Manager\")\n";
        $response .= "- **Document permission requirements** for each feature\n";
        $response .= "- **Regularly audit** permission assignments\n";
        $response .= "- **Train users** on their permission levels";
        
        return $response;
    }

    /**
     * Generate default response
     */
    protected function generateDefaultResponse($message)
    {
        $response = "I understand you're asking about \"{$message}\". ";
        $response .= "I can help you with various aspects of the system. Here are some suggestions:\n\n";
        
        $response .= "**📊 Business Reports:**\n";
        $response .= "- Sales reports and analysis\n";
        $response .= "- Financial summaries and statements\n";
        $response .= "- Customer behavior insights\n";
        $response .= "- Inventory management reports\n";
        $response .= "- Profit and loss statements\n\n";
        
        $response .= "**🛠️ System Assistance:**\n";
        $response .= "- How to add users and manage permissions\n";
        $response .= "- Setting up companies and branches\n";
        $response .= "- Configuring system settings\n";
        $response .= "- Creating and restoring backups\n";
        $response .= "- Managing roles and access levels\n\n";
        
        $response .= "**💡 Try asking me:**\n";
        $response .= "- \"How do I add a new user?\"\n";
        $response .= "- \"Help me set up a backup\"\n";
        $response .= "- \"How to manage company settings?\"\n";
        $response .= "- \"What permissions do I need?\"\n";
        $response .= "- \"Generate a sales report\"\n\n";
        
        $response .= "Or use the **Quick Actions** buttons on the right for common tasks. ";
        $response .= "You can also type \"help\" to see all available options.";
        
        return $response;
    }

    /**
     * Check if message contains specific keywords
     */
    protected function containsKeywords($message, $keywords)
    {
        foreach ($keywords as $keyword) {
            if (strpos($message, $keyword) !== false) {
                return true;
            }
        }
        return false;
    }

    /**
     * Extract time period from message
     */
    protected function extractPeriod($message)
    {
        if (strpos($message, 'month') !== false) {
            return 'This Month';
        }
        if (strpos($message, 'quarter') !== false || strpos($message, 'q') !== false) {
            return 'This Quarter';
        }
        if (strpos($message, 'year') !== false) {
            return 'This Year';
        }
        if (strpos($message, 'week') !== false) {
            return 'This Week';
        }
        return 'Current Period';
    }
} 