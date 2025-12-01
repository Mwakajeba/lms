<?php
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\LanguageController;
use App\Http\Controllers\OtpEmailController;
use App\Http\Controllers\ReportsController;
use App\Http\Controllers\RolePermissionController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\ActivityLogsController;
use App\Http\Controllers\FiletypeController;
use App\Http\Controllers\BranchController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\CashCollateralTypeController;
use App\Http\Controllers\SuperAdminController;
use App\Http\Controllers\AccountClassGroupController;
use App\Http\Controllers\ChartAccountController;
use App\Http\Controllers\Accounting\SupplierController;
use App\Http\Controllers\Accounting\PaymentVoucherController;
use App\Http\Controllers\Accounting\BillPurchaseController;
use App\Http\Controllers\Accounting\ReceiptVoucherController;
use App\Http\Controllers\BankAccountController;
use App\Http\Controllers\Accounting\BankReconciliationController;
use App\Http\Controllers\Accounting\Reports\BankReconciliationReportController;
use App\Http\Controllers\Accounting\BudgetController;
use App\Http\Controllers\Accounting\FeeController;
use App\Http\Controllers\Accounting\PenaltyController;
use App\Http\Controllers\JournalController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\LoanProductController;
use App\Http\Controllers\GroupController;
use App\Http\Controllers\GroupMemberController;
use App\Http\Controllers\LoanController;
use App\Http\Controllers\LoanTopUpController;
use App\Http\Controllers\LoanReportController;
use App\Http\Controllers\LoanRepaymentController;
use App\Http\Controllers\LoanCollateralController;
use App\Http\Controllers\CashCollateralController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LoanCalculatorController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\Accounting\Reports\BalanceSheetReportController as NewBalanceSheetReportController;
use App\Http\Controllers\Reports\BotBalanceSheetController;
use App\Http\Controllers\Reports\BotIncomeStatementController;
use App\Http\Controllers\Reports\BotSectoralLoansController;
use App\Http\Controllers\Reports\BotInterestRatesController;
use App\Http\Controllers\Reports\BotLiquidAssetsController;
use App\Http\Controllers\Reports\BotComplaintsReportController;
use App\Http\Controllers\Reports\BotDepositsBorrowingsController;
use App\Http\Controllers\Reports\BotAgentBankingController;
use App\Http\Controllers\EmailController;
use App\Http\Controllers\Reports\BotLoansDisbursedController;
use App\Http\Controllers\Reports\BotGeographicalDistributionController;
use App\Http\Controllers\SubscriptionController;
use App\Http\Controllers\LaravelLogsController;
// Add other main app routes here
Route::get('/dashboard/loan-product-disbursement', [DashboardController::class, 'loanProductDisbursement'])->middleware('auth');
Route::get('/dashboard/delinquency-loan-buckets', [DashboardController::class, 'delinquencyLoanBuckets'])->middleware('auth');
Route::get('/dashboard/monthly-collections', [DashboardController::class, 'monthlyCollections'])->middleware('auth');
// API route for bank accounts
Route::get('/api/bank-accounts', [\App\Http\Controllers\Api\BankAccountController::class, 'index']);
Route::post('/receipts/store', [\App\Http\Controllers\ReceiptController::class, 'store'])->name('receipts.store');

// Route::middleware(['auth'])->group(function () {
Route::get('/change-branch', [\App\Http\Controllers\ChangeBranchController::class, 'show'])->name('change-branch');
Route::post('/change-branch', [\App\Http\Controllers\ChangeBranchController::class, 'change'])->name('change-branch.submit');
//     Route::get('/dashboard', [\App\Http\Controllers\DashboardController::class, 'index'])->name('dashboard');
// Group Loans AJAX
Route::get('group-loans-ajax/{group}', [\App\Http\Controllers\GroupLoanAjaxController::class, 'index'])->name('group.loans.ajax');
//     // Add other main app routes here
// });
Route::post('/loans/{hashid}/writeoff', [\App\Http\Controllers\LoanController::class, 'confirmWriteoff'])->name('loans.writeoff.confirm');
Route::get('/loans/{hashid}/writeoff', [\App\Http\Controllers\LoanController::class, 'writeoff'])->name('loans.writeoff');
// // ...existing code...

Route::get('loans/data', [LoanController::class, 'getLoansData'])->name('loans.data');
// Group Members AJAX
Route::get('group-members-ajax/{group}', [\App\Http\Controllers\GroupMemberAjaxController::class, 'index'])->name('group.members.ajax');
// Loans in Arrears (30+ days)
Route::get('arrears-loans', [\App\Http\Controllers\ArrearsLoanController::class, 'index'])->name('arrears.loans.list');
Route::get('arrears-loans/pdf', [\App\Http\Controllers\ArrearsLoanController::class, 'exportPdf'])->name('arrears.loans.pdf');


Route::get('/', [AuthController::class, 'showLoginForm'])->name('login');


Route::get('/login', [AuthController::class, 'showLoginForm']);
Route::get('/register', [AuthController::class, 'showRegisterForm'])->name('register');

Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);

Route::get('/verify-sms', [AuthController::class, 'showVerificationForm'])->name('verify-sms');
Route::post('/verify-sms', [AuthController::class, 'verifySmsCode']);

Route::get('/forgotPassword', [AuthController::class, 'showForgotPasswordForm'])->name('forgotPassword');
Route::post('/forgotPassword', [AuthController::class, 'forgotPassword']);

Route::get('/verify-otp-password', [AuthController::class, 'showVerificationForm'])->name('verify-otp-password');
Route::post('/verify-otp-password', [AuthController::class, 'verifyPasswordCode']);

Route::get('/reset-password', [AuthController::class, 'showNewPasswordForm'])->name('new-password-form');
Route::post('/reset-password', [AuthController::class, 'storeNewPassword']);

Route::get('/resend-otp/{phone}', [AuthController::class, 'resendOtp'])->name('resend.otp');

Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

// Laravel Logs Route
Route::get('/log', [LaravelLogsController::class, 'index'])->name('laravel-logs.index')->middleware('auth');
Route::post('/log/clear', [LaravelLogsController::class, 'clearLogs'])->name('laravel-logs.clear')->middleware('auth');

// Language switching
Route::get('/language/{locale}', [LanguageController::class, 'switchLanguage'])->name('language.switch');
// Test language route
Route::get('/test-language', function () {
    return view('test-language');
})->name('test.language');

Route::get('/request-email-otp', [OtpEmailController::class, 'showEmailForm'])->name('email-otp-form');
Route::post('/send-email-otp', [OtpEmailController::class, 'sendOtpEmail'])->name('email-otp-send');


// Reports Route
Route::get('/reports', [App\Http\Controllers\ReportsController::class, 'index'])->middleware('auth')->name('reports.index');
Route::get('/reports/loans', [App\Http\Controllers\ReportsController::class, 'loans'])->middleware('auth')->name('reports.loans');
Route::get('/reports/customers', [App\Http\Controllers\ReportsController::class, 'customers'])->middleware('auth')->name('reports.customers');
Route::get("/reports/customers/list", [App\Http\Controllers\Reports\CustomerListReportController::class, "index"])->middleware("auth")->name("reports.customers.list");
Route::get("/reports/customers/list/export", [App\Http\Controllers\Reports\CustomerListReportController::class, "export"])->middleware("auth")->name("reports.customers.list.export");
Route::get("/reports/customers/list/export-pdf", [App\Http\Controllers\Reports\CustomerListReportController::class, "exportPdf"])->middleware("auth")->name("reports.customers.list.export-pdf");
Route::get("/reports/customers/activity", [App\Http\Controllers\Reports\CustomerActivityReportController::class, "index"])->middleware("auth")->name("reports.customers.activity");
Route::get("/reports/customers/activity/export", [App\Http\Controllers\Reports\CustomerActivityReportController::class, "export"])->middleware("auth")->name("reports.customers.activity.export");
Route::get("/reports/customers/activity/export-pdf", [App\Http\Controllers\Reports\CustomerActivityReportController::class, "exportPdf"])->middleware("auth")->name("reports.customers.activity.export-pdf");
Route::get("/reports/customers/performance", [App\Http\Controllers\Reports\CustomerPerformanceReportController::class, "index"])->middleware("auth")->name("reports.customers.performance");
Route::get("/reports/customers/performance/export", [App\Http\Controllers\Reports\CustomerPerformanceReportController::class, "export"])->middleware("auth")->name("reports.customers.performance.export");
Route::get("/reports/customers/performance/export-pdf", [App\Http\Controllers\Reports\CustomerPerformanceReportController::class, "exportPdf"])->middleware("auth")->name("reports.customers.performance.export-pdf");
Route::get("/reports/customers/demographics", [App\Http\Controllers\Reports\CustomerDemographicsReportController::class, "index"])->middleware("auth")->name("reports.customers.demographics");
Route::get("/reports/customers/demographics/export", [App\Http\Controllers\Reports\CustomerDemographicsReportController::class, "export"])->middleware("auth")->name("reports.customers.demographics.export");
Route::get("/reports/customers/demographics/export-pdf", [App\Http\Controllers\Reports\CustomerDemographicsReportController::class, "exportPdf"])->middleware("auth")->name("reports.customers.demographics.export-pdf");
Route::get("/reports/customers/risk-assessment", [App\Http\Controllers\Reports\CustomerRiskAssessmentReportController::class, "index"])->middleware("auth")->name("reports.customers.risk-assessment");
Route::get("/reports/customers/risk-assessment/export", [App\Http\Controllers\Reports\CustomerRiskAssessmentReportController::class, "export"])->middleware("auth")->name("reports.customers.risk-assessment.export");
Route::get("/reports/customers/risk-assessment/export-pdf", [App\Http\Controllers\Reports\CustomerRiskAssessmentReportController::class, "exportPdf"])->middleware("auth")->name("reports.customers.risk-assessment.export-pdf");
Route::get("/reports/customers/communication", [App\Http\Controllers\Reports\CustomerCommunicationReportController::class, "index"])->middleware("auth")->name("reports.customers.communication");
Route::get("/reports/customers/communication/export", [App\Http\Controllers\Reports\CustomerCommunicationReportController::class, "export"])->middleware("auth")->name("reports.customers.communication.export");
Route::get("/reports/customers/communication/export-pdf", [App\Http\Controllers\Reports\CustomerCommunicationReportController::class, "exportPdf"])->middleware("auth")->name("reports.customers.communication.export-pdf");
Route::get('/reports/bot', [App\Http\Controllers\ReportsController::class, 'bot'])->middleware('auth')->name('reports.bot');
// BOT Balance Sheet & Income Statement
Route::prefix('reports/bot')->middleware('auth')->name('reports.bot.')->group(function () {

    // Accounting Reports Index
    Route::get("/accounting-reports", function () {
        return view("reports.index");
    })->name("index");
    Route::get('/balance-sheet', [BotBalanceSheetController::class, 'index'])->name('balance-sheet');
    Route::get('/balance-sheet/export', [BotBalanceSheetController::class, 'export'])->name('balance-sheet.export');
    Route::get('/income-statement', [BotIncomeStatementController::class, 'index'])->name('income-statement');
    Route::get('/income-statement/export', [BotIncomeStatementController::class, 'export'])->name('income-statement.export');
    Route::get('/sectoral-loans', [BotSectoralLoansController::class, 'index'])->name('sectoral-loans');
    Route::get('/sectoral-loans/export', [BotSectoralLoansController::class, 'export'])->name('sectoral-loans.export');
    Route::get('/interest-rates', [BotInterestRatesController::class, 'index'])->name('interest-rates');
    Route::get('/interest-rates/export', [BotInterestRatesController::class, 'export'])->name('interest-rates.export');
    Route::get('/liquid-assets', [BotLiquidAssetsController::class, 'index'])->name('liquid-assets');
    Route::get('/liquid-assets/export', [BotLiquidAssetsController::class, 'export'])->name('liquid-assets.export');
    Route::get('/complaints', [BotComplaintsReportController::class, 'index'])->name('complaints');
    Route::get('/complaints/export', [BotComplaintsReportController::class, 'export'])->name('complaints.export');
    Route::get('/deposits-borrowings', [BotDepositsBorrowingsController::class, 'index'])->name('deposits-borrowings');
    Route::get('/deposits-borrowings/export', [BotDepositsBorrowingsController::class, 'export'])->name('deposits-borrowings.export');
    Route::get('/agent-banking', [BotAgentBankingController::class, 'index'])->name('agent-banking');
    Route::get('/agent-banking/export', [BotAgentBankingController::class, 'export'])->name('agent-banking.export');
    Route::get('/loans-disbursed', [BotLoansDisbursedController::class, 'index'])->name('loans-disbursed');
    Route::get('/loans-disbursed/export', [BotLoansDisbursedController::class, 'export'])->name('loans-disbursed.export');
    Route::get('/geographical-distribution', [BotGeographicalDistributionController::class, 'index'])->name('geographical-distribution');
    Route::get('/geographical-distribution/export', [BotGeographicalDistributionController::class, 'export'])->name('geographical-distribution.export');
});

////////////////////////////////////////ROLES & PERMISSIONSMANAGEMENT /////////////////////////////////////////////
Route::middleware(['auth'])->group(function () {
    // Explicit route model binding for Role
    Route::model('role', \App\Models\Role::class);

    // Roles management
    Route::get('roles', [RolePermissionController::class, 'index'])->name('roles.index');
    Route::get('roles/create', [RolePermissionController::class, 'create'])->name('roles.create');
    Route::post('roles', [RolePermissionController::class, 'store'])->name('roles.store');
    Route::get('roles/{role}', [RolePermissionController::class, 'show'])->name('roles.show');
    Route::get('roles/{role}/edit', [RolePermissionController::class, 'edit'])->name('roles.edit');
    Route::match(['PUT', 'PATCH'], 'roles/{role}', [RolePermissionController::class, 'update'])->name('roles.update');
    Route::delete('roles/{role}', [RolePermissionController::class, 'destroy'])->name('roles.destroy');

    // Menu management for roles
    Route::get('roles/{role}/menus', [RolePermissionController::class, 'manageMenus'])->name('roles.menus');
    Route::post('roles/{role}/menus/assign', [RolePermissionController::class, 'assignMenus'])->name('roles.menus.assign');
    Route::delete('roles/{role}/menus/remove', [RolePermissionController::class, 'removeMenu'])->name('roles.menus.remove');

    // Permissions management
    Route::get('permissions', [RolePermissionController::class, 'permissions'])->name('permissions.index');
    Route::post('permissions', [RolePermissionController::class, 'createPermission'])->name('permissions.store');
    Route::delete('permissions/{permission}', [RolePermissionController::class, 'deletePermission'])->name('permissions.destroy');



    // User role assignment
    Route::post('users/{user}/assign-roles', [RolePermissionController::class, 'assignToUser'])->name('users.assign-roles');
    Route::delete('users/{user}/remove-role', [RolePermissionController::class, 'removeFromUser'])->name('users.remove-role');

    // Role statistics
    Route::get('roles-stats', [RolePermissionController::class, 'getStats'])->name('roles.stats');
});
////////////////////////////////////////////// END ROLES & PERMISSIONS MANAGEMENT //////////////////////////////////////////

////////////////////////////////////////////// USER MANAGEMENT /////////////////////////////////////////////////////

// Additional user routes (must come BEFORE resource route)
Route::get('/users/profile', [UserController::class, 'profile'])->name('users.profile')->middleware('auth');
Route::put('/users/profile', [UserController::class, 'updateProfile'])->name('users.profile.update')->middleware('auth');

Route::resource('users', UserController::class)->middleware(['auth', 'company.scope']);

// Additional user routes that require user parameter

Route::patch('/users/{user}/status', [UserController::class, 'changeStatus'])->name('users.status')->middleware(['auth', 'company.scope']);
Route::post('/users/{user}/roles', [UserController::class, 'assignRoles'])->name('users.roles')->middleware(['auth', 'company.scope']);
Route::post('/users/{user}/assign-branches', [UserController::class, 'assignBranches'])->name('users.assign-branches')->middleware(['auth', 'company.scope']);

////////////////////////////////////////////// END /////////////////////////////////////////////////////////////////

////////////////////////////////////////////// SETTINGS ROUTES ////////////////////////////////////////////////

Route::prefix('settings')->name('settings.')->middleware(['auth', 'company.scope'])->group(function () {

    //Filetypes settings
    Route::resource('filetypes', FiletypeController::class);

    Route::get('/', [SettingsController::class, 'index'])->name('index');

    // Company Settings
    Route::get('/company', [SettingsController::class, 'companySettings'])->name('company');
    Route::put('/company', [SettingsController::class, 'updateCompanySettings'])->name('company.update');

    // Branch Settings
    Route::get('/branches', [SettingsController::class, 'branchSettings'])->name('branches');
    Route::get('/branches/create', [SettingsController::class, 'createBranch'])->name('branches.create');
    Route::post('/branches', [SettingsController::class, 'storeBranch'])->name('branches.store');
    Route::get('/branches/{branch}/edit', [SettingsController::class, 'editBranch'])->name('branches.edit');
    Route::put('/branches/{branch}', [SettingsController::class, 'updateBranch'])->name('branches.update');
    Route::delete('/branches/{branch}', [SettingsController::class, 'destroyBranch'])->name('branches.destroy');

    // User Settings
    Route::get('/user', [SettingsController::class, 'userSettings'])->name('user');
    Route::put('/user', [SettingsController::class, 'updateUserSettings'])->name('user.update');

    // System Settings
    Route::get('/system', [SettingsController::class, 'systemSettings'])->name('system');
    Route::put('/system', [SettingsController::class, 'updateSystemSettings'])->name('system.update');
    Route::post('/system/reset', [SettingsController::class, 'resetSystemSettings'])->name('system.reset');
    Route::post('/system/test-email', [SettingsController::class, 'testEmailConfig'])->name('system.test-email');

    // Backup Settings
    Route::get('/backup', [SettingsController::class, 'backupSettings'])->name('backup');
    Route::post('/backup/create', [SettingsController::class, 'createBackup'])->name('backup.create');
    Route::post('/backup/restore', [SettingsController::class, 'restoreBackup'])->name('backup.restore');
    Route::get('/backup/{hash_id}/download', [SettingsController::class, 'downloadBackup'])->name('backup.download');
    Route::delete('/backup/{hash_id}', [SettingsController::class, 'deleteBackup'])->name('backup.delete');
    Route::post('/backup/clean', [SettingsController::class, 'cleanOldBackups'])->name('backup.clean');

    // AI Assistant Settings
    Route::get('/ai', [SettingsController::class, 'aiAssistantSettings'])->name('ai');
    Route::post('/ai/chat', [SettingsController::class, 'aiChat'])->name('ai.chat')->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class);
    Route::get('/ai/test', function () {
        return response()->json([
            'csrf_token' => csrf_token(),
            'status' => 'success',
            'message' => 'AI Assistant connection test successful'
        ]);
    })->name('ai.test');

    // Penalty Settings
    Route::get('/penalty', [SettingsController::class, 'penaltySettings'])->name('penalty');
    Route::put('/penalty', [SettingsController::class, 'updatePenaltySettings'])->name('penalty.update');
    //////logs route///
    Route::get('/logs', [ActivityLogsController::class, 'index'])->name('logs.index');

    // Fees Settings
    Route::get('/fees', [SettingsController::class, 'feesSettings'])->name('fees');
    Route::put('/fees', [SettingsController::class, 'updateFeesSettings'])->name('fees.update');

    // Subscription Settings
    Route::get('/subscription', [SettingsController::class, 'subscriptionSettings'])->name('subscription');
    Route::put('/subscription', [SettingsController::class, 'updateSubscriptionSettings'])->name('subscription.update');

    // Payment Voucher Approval Settings
    Route::get('/payment-voucher-approval', [SettingsController::class, 'paymentVoucherApprovalSettings'])->name('payment-voucher-approval');
    Route::put('/payment-voucher-approval', [SettingsController::class, 'updatePaymentVoucherApprovalSettings'])->name('payment-voucher-approval.update');

    // Bulk Email Settings (Super Admin only)
    Route::middleware(['role:super-admin'])->group(function () {
        Route::get('/bulk-email', [\App\Http\Controllers\BulkEmailController::class, 'index'])->name('bulk-email');
        Route::post('/bulk-email/send', [\App\Http\Controllers\BulkEmailController::class, 'send'])->name('bulk-email.send');
        Route::get('/bulk-email/recipients', [\App\Http\Controllers\BulkEmailController::class, 'getRecipients'])->name('bulk-email.recipients');
    });
});

////////////////////////////////////////////// END SETTINGS ROUTES /////////////////////////////////////////////

////////////////////////////////////////////// SUBSCRIPTION MANAGEMENT ///////////////////////////////////////////

Route::prefix('subscriptions')->name('subscriptions.')->middleware(['auth', 'role:super-admin'])->group(function () {
    // Subscription Dashboard
    Route::get('/dashboard', [SubscriptionController::class, 'dashboard'])->name('dashboard');

    // Subscription CRUD
    Route::get('/', [SubscriptionController::class, 'index'])->name('index');
    Route::get('/create', [SubscriptionController::class, 'create'])->name('create');
    Route::post('/', [SubscriptionController::class, 'store'])->name('store');
    Route::get('/{subscription}', [SubscriptionController::class, 'show'])->name('show');
    Route::get('/{subscription}/edit', [SubscriptionController::class, 'edit'])->name('edit');
    Route::put('/{subscription}', [SubscriptionController::class, 'update'])->name('update');
    Route::delete('/{subscription}', [SubscriptionController::class, 'destroy'])->name('destroy');

    // Subscription Actions
    Route::post('/{subscription}/mark-paid', [SubscriptionController::class, 'markAsPaid'])->name('mark-paid');
    Route::post('/{subscription}/cancel', [SubscriptionController::class, 'cancel'])->name('cancel');
    Route::post('/{subscription}/renew', [SubscriptionController::class, 'renew'])->name('renew');
    Route::post('/{subscription}/extend', [SubscriptionController::class, 'extend'])->name('extend');
});

// Ticker Messages API - Only for subscription expiry alerts
Route::get('/api/ticker-messages', function () {
    // Get subscription alerts - only show ticker if there are expiring subscriptions
    $expiringSubscriptions = \App\Models\Subscription::where('status', 'active')
        ->where('end_date', '<=', now()->addDays(5))
        ->where('end_date', '>=', now())
        ->with('company')
        ->get();

    // If no expiring subscriptions, return empty messages to hide ticker
    if ($expiringSubscriptions->count() == 0) {
        return response()->json([
            'success' => true,
            'messages' => [],
            'show_ticker' => false,
            'timestamp' => now()->toISOString()
        ]);
    }

    $messages = [];
    $now = now();

    // Build subscription expiry messages
    foreach ($expiringSubscriptions as $subscription) {
        $daysLeft = floor($now->diffInDays($subscription->end_date, false));
        $urgency = $daysLeft <= 1 ? 'urgent' : ($daysLeft <= 3 ? 'warning' : 'info');

        $daysText = $daysLeft == 0 ? 'expires today' : ($daysLeft == 1 ? 'expires tomorrow' : "expires in {$daysLeft} days");

        $messages[] = [
            'text' => "⚠️ URGENT: {$subscription->company->name} subscription ({$subscription->plan_name}) {$daysText} - Amount: " . number_format($subscription->amount, 2) . " {$subscription->currency}",
            'type' => $urgency,
            'icon' => 'bx-credit-card',
            'subscription_id' => $subscription->id,
            'company_name' => $subscription->company->name,
            'days_left' => $daysLeft,
            'expiry_date' => $subscription->end_date->format('M d, Y')
        ];
    }

    // Add a general reminder message
    $messages[] = [
        'text' => "🔔 Action Required: Please renew expiring subscriptions to avoid service interruption",
        'type' => 'urgent',
        'icon' => 'bx-bell'
    ];

    return response()->json([
        'success' => true,
        'messages' => $messages,
        'show_ticker' => true,
        'expiring_count' => $expiringSubscriptions->count(),
        'timestamp' => $now->toISOString()
    ]);
})->middleware('auth');

////////////////////////////////////////////// END SUBSCRIPTION MANAGEMENT ///////////////////////////////////////////

////////////////////////////////////////////// BRANCH MANAGEMENT ///////////////////////////////////////////////////

//Route::resource('branches', BranchController::class)->middleware('auth');

//Route::resource('companies', CompanyController::class)->middleware('auth');

Route::resource('cash_collateral_types', CashCollateralTypeController::class)->middleware('auth');

////////////////////////////////////////////// END /////////////////////////////////////////////////////////////////

////////////////////////////////////////////// SUPER ADMIN ROUTES ////////////////////////////////////////////////

Route::prefix('super-admin')->name('super-admin.')->middleware(['auth', 'role:super-admin'])->group(function () {

    Route::get('/dashboard', [SuperAdminController::class, 'dashboard'])->name('super-admin.dashboard');
    // Companies
    Route::get('/companies', [SuperAdminController::class, 'companies'])->name('companies');
    Route::get('/companies/create', [SuperAdminController::class, 'createCompany'])->name('companies.create');
    Route::post('/companies', [SuperAdminController::class, 'storeCompany'])->name('companies.store');
    Route::get('/companies/{company}', [SuperAdminController::class, 'showCompany'])->name('companies.show');
    Route::get('/companies/{company}/edit', [SuperAdminController::class, 'editCompany'])->name('companies.edit');
    Route::put('/companies/{company}', [SuperAdminController::class, 'updateCompany'])->name('companies.update');
    Route::delete('/companies/{company}', [SuperAdminController::class, 'destroyCompany'])->name('companies.destroy');

    // Branches
    Route::get('/branches', [SuperAdminController::class, 'branches'])->name('branches');

    // Users
    Route::get('/users', [SuperAdminController::class, 'users'])->name('users');
});

////////////////////////////////////////////// END SUPER ADMIN ROUTES /////////////////////////////////////////////

////////////////////////////////////////////// ACCOUNTING MANAGEMENT ///////////////////////////////////////////////

Route::prefix('accounting')->name('accounting.')->middleware('auth')->group(function () {
    // Account Class Groups
    Route::get('/account-class-groups', [AccountClassGroupController::class, 'index'])->name('account-class-groups.index');

    // Payment Voucher Approval Routes
    Route::prefix('payment-vouchers')->name('payment-vouchers.')->group(function () {
        Route::get('/pending-approvals', [PaymentVoucherController::class, 'pendingApprovals'])->name('pending-approvals');
        Route::get('/{paymentVoucher}/approval', [PaymentVoucherController::class, 'showApproval'])->name('approval');
        Route::post('/{paymentVoucher}/approve', [PaymentVoucherController::class, 'approve'])->name('approve');
        Route::post('/{paymentVoucher}/reject', [PaymentVoucherController::class, 'reject'])->name('reject');
        Route::get("/data", [PaymentVoucherController::class, "getPaymentVouchersData"])->name("data");
        Route::resource("", PaymentVoucherController::class)->parameters(["" => "paymentVoucher"]);
        Route::get("/{paymentVoucher}/download-attachment", [PaymentVoucherController::class, "downloadAttachment"])->name("download-attachment");
        Route::delete("/{paymentVoucher}/remove-attachment", [PaymentVoucherController::class, "removeAttachment"])->name("remove-attachment");
        Route::get("/{paymentVoucher}/export-pdf", [PaymentVoucherController::class, "exportPdf"])->name("export-pdf");

    });
    Route::get('/account-class-groups/create', [AccountClassGroupController::class, 'create'])->name('account-class-groups.create');
    Route::post('/account-class-groups', [AccountClassGroupController::class, 'store'])->name('account-class-groups.store');
    Route::get('/account-class-groups/{encodedId}', [AccountClassGroupController::class, 'show'])->name('account-class-groups.show');
    Route::get('/account-class-groups/{encodedId}/edit', [AccountClassGroupController::class, 'edit'])->name('account-class-groups.edit');
    Route::put('/account-class-groups/{encodedId}', [AccountClassGroupController::class, 'update'])->name('account-class-groups.update');
    Route::delete('/account-class-groups/{encodedId}', [AccountClassGroupController::class, 'destroy'])->name('account-class-groups.destroy');

    // Chart Accounts
    Route::get('/chart-accounts', [ChartAccountController::class, 'index'])->name('chart-accounts.index');
    Route::get('/chart-accounts/data', [ChartAccountController::class, 'getChartAccountsData'])->name('chart-accounts.data');
    Route::get('/chart-accounts/create', [ChartAccountController::class, 'create'])->name('chart-accounts.create');
    Route::post('/chart-accounts', [ChartAccountController::class, 'store'])->name('chart-accounts.store');
    Route::get('/chart-accounts/{encodedId}', [ChartAccountController::class, 'show'])->name('chart-accounts.show');
    Route::get('/chart-accounts/{encodedId}/edit', [ChartAccountController::class, 'edit'])->name('chart-accounts.edit');
    Route::put('/chart-accounts/{encodedId}', [ChartAccountController::class, 'update'])->name('chart-accounts.update');
    Route::delete('/chart-accounts/{encodedId}', [ChartAccountController::class, 'destroy'])->name('chart-accounts.destroy');

    // Suppliers
    Route::get('/suppliers', [SupplierController::class, 'index'])->name('suppliers.index');
    Route::get('/suppliers/data', [SupplierController::class, 'getSuppliersData'])->name('suppliers.data');
    Route::get('/suppliers/create', [SupplierController::class, 'create'])->name('suppliers.create');
    Route::post('/suppliers', [SupplierController::class, 'store'])->name('suppliers.store');
    Route::get('/suppliers/{encodedId}', [SupplierController::class, 'show'])->name('suppliers.show');
    Route::get('/suppliers/{encodedId}/edit', [SupplierController::class, 'edit'])->name('suppliers.edit');
    Route::put('/suppliers/{encodedId}', [SupplierController::class, 'update'])->name('suppliers.update');
    Route::patch('/suppliers/{encodedId}/status', [SupplierController::class, 'changeStatus'])->name('suppliers.changeStatus');
    Route::delete('/suppliers/{encodedId}', [SupplierController::class, 'destroy'])->name('suppliers.destroy');


    // Bill and Payment PDF Export Routes
    Route::get('/bill-purchases/{billPurchase}/export-pdf', [BillPurchaseController::class, 'exportPdf'])->name('bill-purchases.export-pdf');
    Route::get('/payments/{payment}/export-pdf', [BillPurchaseController::class, 'exportPaymentPdf'])->name('bill-payments.export-pdf');

    // Receipt Vouchers
    Route::get('/receipt-vouchers', [ReceiptVoucherController::class, 'index'])->name('receipt-vouchers.index');
    Route::get('/receipt-vouchers/data', [ReceiptVoucherController::class, 'getReceiptVouchersData'])->name('receipt-vouchers.data');
    Route::get('/receipt-vouchers/create', [ReceiptVoucherController::class, 'create'])->name('receipt-vouchers.create');
    Route::post('/receipt-vouchers', [ReceiptVoucherController::class, 'store'])->name('receipt-vouchers.store');
    Route::get('/receipt-vouchers/{encodedId}', [ReceiptVoucherController::class, 'show'])->name('receipt-vouchers.show');
    Route::get('/receipt-vouchers/{encodedId}/edit', [ReceiptVoucherController::class, 'edit'])->name('receipt-vouchers.edit');
    Route::put('/receipt-vouchers/{encodedId}', [ReceiptVoucherController::class, 'update'])->name('receipt-vouchers.update');
    Route::delete('/receipt-vouchers/{encodedId}', [ReceiptVoucherController::class, 'destroy'])->name('receipt-vouchers.destroy');
    Route::get('/receipt-vouchers/{encodedId}/download-attachment', [ReceiptVoucherController::class, 'downloadAttachment'])->name('receipt-vouchers.download-attachment');
    Route::delete('/receipt-vouchers/{encodedId}/remove-attachment', [ReceiptVoucherController::class, 'removeAttachment'])->name('receipt-vouchers.remove-attachment');
    Route::get('/receipt-vouchers/{encodedId}/export-pdf', [ReceiptVoucherController::class, 'exportPdf'])->name('receipt-vouchers.export-pdf');
    Route::get('/receipt-vouchers-debug', [ReceiptVoucherController::class, 'debug'])->name('receipt-vouchers.debug');

    // Receipt Vouchers from Loans
    Route::get('/loans/{encodedLoanId}/create-receipt', [ReceiptVoucherController::class, 'createFromLoan'])->name('loans.create-receipt');
    Route::post('/loans/{encodedLoanId}/store-receipt', [ReceiptVoucherController::class, 'storeFromLoan'])->name('loans.store-receipt');

    // Bank Accounts
    Route::get('/bank-accounts', [BankAccountController::class, 'index'])->name('bank-accounts');
    Route::get('/bank-accounts/create', [BankAccountController::class, 'create'])->name('bank-accounts.create');
    Route::post('/bank-accounts', [BankAccountController::class, 'store'])->name('bank-accounts.store');
    Route::get('/bank-accounts/{encodedId}', [BankAccountController::class, 'show'])->name('bank-accounts.show');
    Route::get('/bank-accounts/{encodedId}/edit', [BankAccountController::class, 'edit'])->name('bank-accounts.edit');
    Route::put('/bank-accounts/{encodedId}', [BankAccountController::class, 'update'])->name('bank-accounts.update');
    Route::delete('/bank-accounts/{encodedId}', [BankAccountController::class, 'destroy'])->name('bank-accounts.destroy');

    // Bank Reconciliation
    // Keep resource for other methods but avoid conflicting show/edit
    Route::resource('bank-reconciliation', BankReconciliationController::class)->except(['show', 'edit']);
    // Use hash id for show/edit routes (must come after resource to avoid conflicts)
    Route::get('bank-reconciliation/{hash}', [BankReconciliationController::class, 'show'])->name('bank-reconciliation.show');
    Route::get('bank-reconciliation/{hash}/edit', [BankReconciliationController::class, 'edit'])->name('bank-reconciliation.edit');

    Route::post('/bank-reconciliation/{bankReconciliation}/add-bank-statement-item', [BankReconciliationController::class, 'addBankStatementItem'])->name('bank-reconciliation.add-bank-statement-item');
    Route::post('/bank-reconciliation/{hash}/match-items', [BankReconciliationController::class, 'matchItems'])->name('bank-reconciliation.match-items');
    Route::post('/bank-reconciliation/{hash}/unmatch-items', [BankReconciliationController::class, 'unmatchItems'])->name('bank-reconciliation.unmatch-items');
    Route::post('/bank-reconciliation/{hash}/confirm-book-item', [BankReconciliationController::class, 'confirmBookItem'])->name('bank-reconciliation.confirm-book-item');
    Route::post('/bank-reconciliation/{hash}/complete', [BankReconciliationController::class, 'completeReconciliation'])->name('bank-reconciliation.complete');
    Route::post('/bank-reconciliation/{hash}/update-book-balance', [BankReconciliationController::class, 'updateBookBalance'])->name('bank-reconciliation.update-book-balance');
    Route::post('/bank-reconciliation/refresh-all', [BankReconciliationController::class, 'refreshAllReconciliations'])->name('bank-reconciliation.refresh-all');

    // Bill Purchases
    Route::get('/bill-purchases', [BillPurchaseController::class, 'index'])->name('bill-purchases');
    Route::get('/bill-purchases/create', [BillPurchaseController::class, 'create'])->name('bill-purchases.create');
    Route::post('/bill-purchases', [BillPurchaseController::class, 'store'])->name('bill-purchases.store');

    // Bill Payment Management (must come before bill-purchases/{billPurchase} routes)
    Route::get('/bill-purchases/payment/{payment}', [BillPurchaseController::class, 'showPayment'])->name('bill-purchases.payment.show');
    Route::get('/bill-purchases/payment/{payment}/edit', [BillPurchaseController::class, 'editPayment'])->name('bill-purchases.payment.edit');
    Route::put('/bill-purchases/payment/{payment}', [BillPurchaseController::class, 'updatePayment'])->name('bill-purchases.payment.update');
    Route::delete('/bill-purchases/payment/{payment}', [BillPurchaseController::class, 'deletePayment'])->name('bill-purchases.payment.delete');

    Route::get('/bill-purchases/{billPurchase}', [BillPurchaseController::class, 'show'])->name('bill-purchases.show');
    Route::get('/bill-purchases/{billPurchase}/edit', [BillPurchaseController::class, 'edit'])->name('bill-purchases.edit');
    Route::put('/bill-purchases/{billPurchase}', [BillPurchaseController::class, 'update'])->name('bill-purchases.update');
    Route::delete('/bill-purchases/{billPurchase}', [BillPurchaseController::class, 'destroy'])->name('bill-purchases.destroy');
    Route::get('/bill-purchases/{billPurchase}/payment', [BillPurchaseController::class, 'showPaymentForm'])->name('bill-purchases.payment');
    Route::post('/bill-purchases/{billPurchase}/payment', [BillPurchaseController::class, 'processPayment'])->name('bill-purchases.process-payment');

    // Budget
    Route::get('/budgets', [BudgetController::class, 'index'])->name('budgets.index');
    Route::get('/budgets/create', [BudgetController::class, 'create'])->name('budgets.create');
    Route::post('/budgets', [BudgetController::class, 'store'])->name('budgets.store');
    Route::get('/budgets/import', [BudgetController::class, 'import'])->name('budgets.import');
    Route::post('/budgets/import', [BudgetController::class, 'storeImport'])->name('budgets.store-import');
    Route::get('/budgets/template/download', [BudgetController::class, 'downloadTemplate'])->name('budgets.download-template');
    Route::get('/budgets/{budget}/export/excel', [BudgetController::class, 'exportExcel'])->name('budgets.export-excel');
    Route::get('/budgets/{budget}/export/pdf', [BudgetController::class, 'exportPdf'])->name('budgets.export-pdf');


    Route::get('/budgets/{budget}', [BudgetController::class, 'show'])->name('budgets.show');
    Route::get('/budgets/{budget}/edit', [BudgetController::class, 'edit'])->name('budgets.edit');
    Route::put('/budgets/{budget}', [BudgetController::class, 'update'])->name('budgets.update');
    Route::delete('/budgets/{budget}', [BudgetController::class, 'destroy'])->name('budgets.destroy');

    // Fees
    Route::get('/fees', [FeeController::class, 'index'])->name('fees.index');
    Route::get('/fees/create', [FeeController::class, 'create'])->name('fees.create');
    Route::post('/fees', [FeeController::class, 'store'])->name('fees.store');
    Route::get('/fees/{encodedId}', [FeeController::class, 'show'])->name('fees.show');
    Route::get('/fees/{encodedId}/edit', [FeeController::class, 'edit'])->name('fees.edit');
    Route::put('/fees/{encodedId}', [FeeController::class, 'update'])->name('fees.update');
    Route::patch('/fees/{encodedId}/status', [FeeController::class, 'changeStatus'])->name('fees.changeStatus');
    Route::delete('/fees/{encodedId}', [FeeController::class, 'destroy'])->name('fees.destroy');

    // Penalties
    Route::get('/penalties', [PenaltyController::class, 'index'])->name('penalties.index');
    Route::get('/penalties/create', [PenaltyController::class, 'create'])->name('penalties.create');
    Route::post('/penalties', [PenaltyController::class, 'store'])->name('penalties.store');
    Route::get('/penalties/{encodedId}', [PenaltyController::class, 'show'])->name('penalties.show');
    Route::get('/penalties/{encodedId}/edit', [PenaltyController::class, 'edit'])->name('penalties.edit');
    Route::put('/penalties/{encodedId}', [PenaltyController::class, 'update'])->name('penalties.update');
    Route::patch('/penalties/{encodedId}/status', [PenaltyController::class, 'changeStatus'])->name('penalties.changeStatus');
    Route::delete('/penalties/{encodedId}', [PenaltyController::class, 'destroy'])->name('penalties.destroy');

    // Journal Entries CRUD
    Route::get('/journals', [JournalController::class, 'index'])->name('journals.index');
    Route::get('/journals/create', [JournalController::class, 'create'])->name('journals.create');
    Route::post('/journals', [JournalController::class, 'store'])->name('journals.store');
    Route::get('/journals/{journal}', [JournalController::class, 'show'])->name('journals.show');
    Route::get('/journals/{journal}/edit', [JournalController::class, 'edit'])->name('journals.edit');
    Route::put('/journals/{journal}', [JournalController::class, 'update'])->name('journals.update');
    Route::delete('/journals/{journal}', [JournalController::class, 'destroy'])->name('journals.destroy');
    Route::get('/journals/{journal}/export-pdf', [JournalController::class, 'exportPdf'])->name('journals.export-pdf');

    // Reports Routes
    Route::prefix('reports')->name('reports.')->group(function () {

        // Main Reports Index
        Route::get("/", function () {
            return view("reports.index");
        })->name("index");

        // Accounting Reports Index
        Route::get("/accounting-reports", function () {
            if (!auth()->user()->can('view accounting reports')) {
                abort(403, 'Unauthorized access to accounting reports.');
            }
            return view("reports.index");
        })->name("accounting");
        Route::get('/other-income', [App\Http\Controllers\Accounting\Reports\OtherIncomeReportController::class, 'index'])->name('other-income');
        // Trial Balance Report
        Route::get('/trial-balance', [App\Http\Controllers\Accounting\Reports\TrialBalanceReportController::class, 'index'])->name('trial-balance');
        Route::get('/trial-balance/export', [App\Http\Controllers\Accounting\Reports\TrialBalanceReportController::class, 'export'])->name('trial-balance.export');
        Route::get('/income-statement', [App\Http\Controllers\Accounting\Reports\IncomeStatementReportController::class, 'index'])->name('income-statement');
        Route::get('/income-statement/export', [App\Http\Controllers\Accounting\Reports\IncomeStatementReportController::class, 'export'])->name('income-statement.export');
        Route::get('/cash-book', [App\Http\Controllers\Accounting\Reports\CashBookReportController::class, 'index'])->name('cash-book');
        Route::get('/cash-book/export', [App\Http\Controllers\Accounting\Reports\CashBookReportController::class, 'export'])->name('cash-book.export');
        Route::get('/accounting-notes', [App\Http\Controllers\Accounting\Reports\AccountingNotesReportController::class, 'index'])->name('accounting-notes');
        Route::get('/accounting-notes/export', [App\Http\Controllers\Accounting\Reports\AccountingNotesReportController::class, 'export'])->name('accounting-notes.export');
        Route::get('/balance-sheet', [App\Http\Controllers\Accounting\Reports\BalanceSheetReportController::class, 'index'])->name('balance-sheet');
        Route::get('/balance-sheet/export', [App\Http\Controllers\Accounting\Reports\BalanceSheetReportController::class, 'export'])->name('balance-sheet.export');
        Route::get('/cash-flow', [App\Http\Controllers\Accounting\Reports\CashFlowReportController::class, 'index'])->name('cash-flow');
        Route::match(['GET', 'POST'], '/cash-flow/export', [App\Http\Controllers\Accounting\Reports\CashFlowReportController::class, 'export'])->name('cash-flow.export');
        Route::get('/general-ledger', [App\Http\Controllers\Accounting\Reports\GeneralLedgerReportController::class, 'index'])->name('general-ledger');
        Route::get('/general-ledger/export', [App\Http\Controllers\Accounting\Reports\GeneralLedgerReportController::class, 'export'])->name('general-ledger.export');
        Route::get('/expenses-summary', [App\Http\Controllers\Accounting\Reports\ExpensesSummaryReportController::class, 'index'])->name('expenses-summary');
        Route::get('/expenses-summary/export', [App\Http\Controllers\Accounting\Reports\ExpensesSummaryReportController::class, 'export'])->name('expenses-summary.export');
        Route::get('/accounting-notes', [App\Http\Controllers\Accounting\Reports\AccountingNotesReportController::class, 'index'])->name('accounting-notes');
        Route::get('/changes-equity', [App\Http\Controllers\Accounting\Reports\ChangesEquityReportController::class, 'index'])->name('changes-equity');
        Route::post('/changes-equity', [App\Http\Controllers\Accounting\Reports\ChangesEquityReportController::class, 'export'])->name('changes-equity.export');
        Route::get('/bank-reconciliation', [BankReconciliationReportController::class, 'index'])->name('bank-reconciliation-report');
        Route::get('/bank-reconciliation/generate', [BankReconciliationReportController::class, 'generate'])->name('bank-reconciliation-report.generate');
        Route::get('/bank-reconciliation/{bankReconciliation}/show', [BankReconciliationReportController::class, 'show'])->name('bank-reconciliation-report.show');
        Route::get('/bank-reconciliation/{bankReconciliation}/export', [BankReconciliationReportController::class, 'exportReconciliation'])->name('bank-reconciliation-report.export');
        Route::get('/budget-report', [App\Http\Controllers\Accounting\Reports\BudgetReportController::class, 'index'])->name('budget-report');
        Route::get('/budget-report/export', [App\Http\Controllers\Accounting\Reports\BudgetReportController::class, 'export'])->name('budget-report.export');
        Route::get('/budget-report/export-pdf', [App\Http\Controllers\Accounting\Reports\BudgetReportController::class, 'exportPdf'])->name('budget-report.export-pdf');

        // Fees Report
        Route::get("/fees", [App\Http\Controllers\Accounting\Reports\FeesReportController::class, "index"])->name("fees");
        Route::get("/fees/export", [App\Http\Controllers\Accounting\Reports\FeesReportController::class, "export"])->name("fees.export");

        Route::get("/fees/export-pdf", [App\Http\Controllers\Accounting\Reports\FeesReportController::class, "exportPdf"])->name("fees.export-pdf");

        // Penalties Report
        Route::get("/penalties", [App\Http\Controllers\Accounting\Reports\PenaltiesReportController::class, "index"])->name("penalties");
        Route::get("/penalties/export", [App\Http\Controllers\Accounting\Reports\PenaltiesReportController::class, "export"])->name("penalties.export");
        Route::get("/penalties/export-pdf", [App\Http\Controllers\Accounting\Reports\PenaltiesReportController::class, "exportPdf"])->name("penalties.export-pdf");
    });

    // Transaction Routes
    Route::get('/transactions/double-entries/{accountId}', [App\Http\Controllers\TransactionController::class, 'doubleEntries'])->name('transactions.doubleEntries');
    Route::get('/transactions/details/{transactionId}/{transactionType?}', [App\Http\Controllers\TransactionController::class, 'showTransactionDetails'])->name('transactions.details');
});

//route

Route::name('loans.reports.')->group(function () {
        //////LOANS REPORT ROUTE////////
        Route::get('/loan-disbursement', [LoanReportController::class, 'loanDisbursementReport'])->name('disbursed');
        Route::get('/loan-disbursement/export', [LoanReportController::class, 'exportLoanDisbursement'])->name('loan-export');
        ////////REPAYMENT ROUTE///////
        Route::get('/loan-repayments', [LoanReportController::class, 'getRepaymentReport'])->name('repayment');
        Route::get('/loan-repayment', [LoanReportController::class, 'getRepaymentReport'])->name('loan-repayment');
        Route::get('/loan-repayment/export', [LoanReportController::class, 'exportLoanRepayment'])->name('loan-export-repayment');
        // Loan Aging Report
        Route::get('/loan-aging', [LoanReportController::class, 'loanAgingReport'])->name('loan_aging');
        Route::get('/loan-aging/export-excel', [LoanReportController::class, 'exportLoanAgingToExcel'])->name('loan_aging.export_excel');
        Route::get('/loan-aging/export-pdf', [LoanReportController::class, 'exportLoanAgingToPdf'])->name('loan_aging.export_pdf');

        // Loan Portfolio Tracking Report
        Route::get('/portfolio-tracking', [LoanReportController::class, 'portfolioTrackingReport'])->name('portfolio_tracking');
        Route::get('/portfolio-tracking/export-excel', [LoanReportController::class, 'exportPortfolioTrackingToExcel'])->name('portfolio_tracking.export_excel');
        Route::get('/portfolio-tracking/export-pdf', [LoanReportController::class, 'exportPortfolioTrackingToPdf'])->name('portfolio_tracking.export_pdf');

        // Loan Aging Installment Report
        Route::get('/loan-aging-installment', [LoanReportController::class, 'loanAgingInstallmentReport'])->name('loan_aging_installment');
        Route::get('/loan-aging-installment/export-excel', [LoanReportController::class, 'exportLoanAgingInstallmentToExcel'])->name('loan_aging_installment.export_excel');
        Route::get('/loan-aging-installment/export-pdf', [LoanReportController::class, 'exportLoanAgingInstallmentToPdf'])->name('loan_aging_installment.export_pdf');

        // Reports Index - Removed to avoid conflict with main reports.index

        // Loan Arrears Report
        Route::get('/loan-arrears', [LoanReportController::class, 'loanArrearsReport'])->name('loan_arrears');
        Route::get('/loan-arrears/export-excel', [LoanReportController::class, 'exportLoanArrearsToExcel'])->name('loan_arrears.export_excel');
        Route::get('/loan-arrears/export-pdf', [LoanReportController::class, 'exportLoanArrearsToPdf'])->name('loan_arrears.export_pdf');

        // Expected vs Collected Report
        Route::get('/expected-vs-collected', [LoanReportController::class, 'expectedVsCollectedReport'])->name('expected_vs_collected');
        Route::get('/expected-vs-collected/export-excel', [LoanReportController::class, 'exportExpectedVsCollectedToExcel'])->name('expected_vs_collected.export_excel');
        Route::get('/expected-vs-collected/export-pdf', [LoanReportController::class, 'exportExpectedVsCollectedToPdf'])->name('expected_vs_collected.export_pdf');

        // Portfolio at Risk (PAR) Report
        Route::get('/portfolio-at-risk', [LoanReportController::class, 'portfolioAtRiskReport'])->name('portfolio_at_risk');
        Route::get('/portfolio-at-risk/export-excel', [LoanReportController::class, 'exportPortfolioAtRiskToExcel'])->name('portfolio_at_risk.export_excel');
        Route::get('/portfolio-at-risk/export-pdf', [LoanReportController::class, 'exportPortfolioAtRiskToPdf'])->name('portfolio_at_risk.export_pdf');

        // Internal Portfolio Analysis Report
        Route::get('/internal-portfolio-analysis', [LoanReportController::class, 'internalPortfolioAnalysisReport'])->name('internal_portfolio_analysis');
        Route::get('/internal-portfolio-analysis/export-excel', [LoanReportController::class, 'exportInternalPortfolioAnalysisToExcel'])->name('internal_portfolio_analysis.export_excel');
        Route::get('/internal-portfolio-analysis/export-pdf', [LoanReportController::class, 'exportInternalPortfolioAnalysisToPdf'])->name('internal_portfolio_analysis.export_pdf');

        // Loan Portfolio Report
        Route::get('/portfolio', [LoanReportController::class, 'portfolioReport'])->name('portfolio');
        Route::get('/portfolio/export-excel', [LoanReportController::class, 'exportPortfolioToExcel'])->name('portfolio.export_excel');
        Route::get('/portfolio/export-pdf', [LoanReportController::class, 'exportPortfolioToPdf'])->name('portfolio.export_pdf');

        // Loan Performance Report
        Route::get('/performance', [LoanReportController::class, 'performanceReport'])->name('performance');
        Route::get('/performance/export-excel', [LoanReportController::class, 'exportPerformanceToExcel'])->name('performance.export_excel');
        Route::get('/performance/export-pdf', [LoanReportController::class, 'exportPerformanceToPdf'])->name('performance.export_pdf');

        // Delinquency Report
        Route::get('/delinquency', [LoanReportController::class, 'delinquencyReport'])->name('delinquency');
        Route::get('/delinquency/export-excel', [LoanReportController::class, 'exportDelinquencyToExcel'])->name('delinquency.export_excel');
        Route::get('/delinquency/export-pdf', [LoanReportController::class, 'exportDelinquencyToPdf'])->name('delinquency.export_pdf');

        // Loan Outstanding Report
        Route::get('/loan-outstanding', [LoanReportController::class, 'loanOutstandingReport'])->name('loan_outstanding');

        // Non Performing Loan Report
        Route::get('/npl', [LoanReportController::class, 'nonPerformingLoanReport'])->name('npl');
        Route::get('/npl/export-excel', [LoanReportController::class, 'exportNPLToExcel'])->name('npl.export_excel');
        Route::get('/npl/export-pdf', [LoanReportController::class, 'exportNPLToPdf'])->name('npl.export_pdf');
    });

    // Loan Reports Routes (accounting.loans.reports.*)
    Route::prefix('accounting/loans/reports')->name('accounting.loans.reports.')->group(function () {
        // Loan Portfolio Report
        Route::get('/portfolio', [LoanReportController::class, 'portfolioReport'])->name('portfolio');
        Route::get('/portfolio/export-excel', [LoanReportController::class, 'exportPortfolioToExcel'])->name('portfolio.export_excel');
        Route::get('/portfolio/export-pdf', [LoanReportController::class, 'exportPortfolioToPdf'])->name('portfolio.export_pdf');

        // Loan Performance Report
        Route::get('/performance', [LoanReportController::class, 'performanceReport'])->name('performance');
        Route::get('/performance/export-excel', [LoanReportController::class, 'exportPerformanceToExcel'])->name('performance.export_excel');
        Route::get('/performance/export-pdf', [LoanReportController::class, 'exportPerformanceToPdf'])->name('performance.export_pdf');

        // Delinquency Report
        Route::get('/delinquency', [LoanReportController::class, 'delinquencyReport'])->name('delinquency');
        Route::get('/delinquency/export-excel', [LoanReportController::class, 'exportDelinquencyToExcel'])->name('delinquency.export_excel');
        Route::get('/delinquency/export-pdf', [LoanReportController::class, 'exportDelinquencyToPdf'])->name('delinquency.export_pdf');

        // Loan Disbursement Report
        Route::get('/disbursed', [LoanReportController::class, 'loanDisbursementReport'])->name('disbursed');
        Route::get('/disbursed/export', [LoanReportController::class, 'exportLoanDisbursement'])->name('loan-export');

        // Loan Repayment Report
        Route::get('/repayment', [LoanReportController::class, 'getRepaymentReport'])->name('repayment');
        Route::get('/repayment/export', [LoanReportController::class, 'exportLoanRepayment'])->name('loan-export-repayment');

        // Loan Aging Report
        Route::get('/loan-aging', [LoanReportController::class, 'loanAgingReport'])->name('loan_aging');
        Route::get('/loan-aging/export-excel', [LoanReportController::class, 'exportLoanAgingToExcel'])->name('loan_aging.export_excel');
        Route::get('/loan-aging/export-pdf', [LoanReportController::class, 'exportLoanAgingToPdf'])->name('loan_aging.export_pdf');

        // Loan Aging Installment Report
        Route::get('/loan-aging-installment', [LoanReportController::class, 'loanAgingInstallmentReport'])->name('loan_aging_installment');
        Route::get('/loan-aging-installment/export-excel', [LoanReportController::class, 'exportLoanAgingInstallmentToExcel'])->name('loan_aging_installment.export_excel');
        Route::get('/loan-aging-installment/export-pdf', [LoanReportController::class, 'exportLoanAgingInstallmentToPdf'])->name('loan_aging_installment.export_pdf');

        // Loan Outstanding Report
        Route::get('/loan-outstanding', [LoanReportController::class, 'loanOutstandingReport'])->name('loan_outstanding');

        // Loan Arrears Report
        Route::get('/loan-arrears', [LoanReportController::class, 'loanArrearsReport'])->name('loan_arrears');
        Route::get('/loan-arrears/export-excel', [LoanReportController::class, 'exportLoanArrearsToExcel'])->name('loan_arrears.export_excel');
        Route::get('/loan-arrears/export-pdf', [LoanReportController::class, 'exportLoanArrearsToPdf'])->name('loan_arrears.export_pdf');

        // Expected vs Collected Report
        Route::get('/expected-vs-collected', [LoanReportController::class, 'expectedVsCollectedReport'])->name('expected_vs_collected');
        Route::get('/expected-vs-collected/export-excel', [LoanReportController::class, 'exportExpectedVsCollectedToExcel'])->name('expected_vs_collected.export_excel');
        Route::get('/expected-vs-collected/export-pdf', [LoanReportController::class, 'exportExpectedVsCollectedToPdf'])->name('expected_vs_collected.export_pdf');

        // Portfolio at Risk (PAR) Report
        Route::get('/portfolio-at-risk', [LoanReportController::class, 'portfolioAtRiskReport'])->name('portfolio_at_risk');
        Route::get('/portfolio-at-risk/export-excel', [LoanReportController::class, 'exportPortfolioAtRiskToExcel'])->name('portfolio_at_risk.export_excel');
        Route::get('/portfolio-at-risk/export-pdf', [LoanReportController::class, 'exportPortfolioAtRiskToPdf'])->name('portfolio_at_risk.export_pdf');

        // Internal Portfolio Analysis Report
        Route::get('/internal-portfolio-analysis', [LoanReportController::class, 'internalPortfolioAnalysisReport'])->name('internal_portfolio_analysis');
        Route::get('/internal-portfolio-analysis/export-excel', [LoanReportController::class, 'exportInternalPortfolioAnalysisToExcel'])->name('internal_portfolio_analysis.export_excel');
        Route::get('/internal-portfolio-analysis/export-pdf', [LoanReportController::class, 'exportInternalPortfolioAnalysisToPdf'])->name('internal_portfolio_analysis.export_pdf');

        // Non Performing Loan Report
        Route::get('/npl', [LoanReportController::class, 'nonPerformingLoanReport'])->name('npl');
        Route::get('/npl/export-excel', [LoanReportController::class, 'exportNPLToExcel'])->name('npl.export_excel');
        Route::get('/npl/export-pdf', [LoanReportController::class, 'exportNPLToPdf'])->name('npl.export_pdf');
    });

////////////////////////////////////////////// END ACCOUNTING MANAGEMENT ///////////////////////////////////////////

////////////////////////////////////////////// CUSTOMER MANAGEMENT ///////////////////////////////////////////

Route::middleware(['auth'])->group(function () {
    Route::get('customers', [CustomerController::class, 'index'])->name('customers.index');
    Route::get('customers/data', [CustomerController::class, 'getCustomersData'])->name('customers.data');
    Route::get('customers/penalty', [CustomerController::class, 'penaltList'])->name('customers.penalty');
    Route::get('customers/create', [CustomerController::class, 'create'])->name('customers.create');
    Route::post('customers', [CustomerController::class, 'store'])->name('customers.store');

    // Bulk upload routes (must come before parameterized routes)
    Route::get('customers/bulk-upload', [CustomerController::class, 'bulkUpload'])->name('customers.bulk-upload');
    Route::post('customers/bulk-upload', [CustomerController::class, 'bulkUploadStore'])->name('customers.bulk-upload.store');
    Route::get('customers/download-sample', [CustomerController::class, 'downloadSample'])->name('customers.download-sample');

    // Documents upload/delete
    Route::post('customers/{encodedCustomerId}/documents', [CustomerController::class, 'uploadDocuments'])->name('customers.documents.upload');
    Route::delete('customers/{encodedCustomerId}/documents/{pivotId}', [CustomerController::class, 'deleteDocument'])->name('customers.documents.delete');
    Route::get('customers/{encodedCustomerId}/documents/{pivotId}/view', [CustomerController::class, 'viewDocument'])->name('customers.documents.view');
    Route::get('customers/{encodedCustomerId}/documents/{pivotId}/download', [CustomerController::class, 'downloadDocument'])->name('customers.documents.download');

    // Parameterized routes (must come after specific routes)
    Route::post('customers/{customerId}/send-message', [CustomerController::class, 'sendMessage'])->name('customers.send-message');
    Route::get('customers/{customer}', [CustomerController::class, 'show'])->name('customers.show');
    Route::get('customers/{customer}/edit', [CustomerController::class, 'edit'])->name('customers.edit');
    Route::put('customers/{customer}', [CustomerController::class, 'update'])->name('customers.update');
    Route::delete('customers/{customer}', [CustomerController::class, 'destroy'])->name('customers.destroy');

});

////////////////////////////////////////////// END CUSTOMER MANAGEMENT ///////////////////////////////////////////

////////////////////////////////////////////// LOAN PRODUCT MANAGEMENT ///////////////////////////////////////////

Route::middleware(['auth'])->group(function () {
    Route::get('loan-products', [LoanProductController::class, 'index'])->name('loan-products.index');
    Route::get('loan-products/create', [LoanProductController::class, 'create'])->name('loan-products.create');
    Route::post('loan-products', [LoanProductController::class, 'store'])->name('loan-products.store');
    Route::get('loan-products/{encodedId}', [LoanProductController::class, 'show'])->name('loan-products.show');
    Route::get('loan-products/{encodedId}/edit', [LoanProductController::class, 'edit'])->name('loan-products.edit');
    Route::put('loan-products/{encodedId}', [LoanProductController::class, 'update'])->name('loan-products.update');
    Route::delete('loan-products/{encodedId}', [LoanProductController::class, 'destroy'])->name('loan-products.destroy');
    Route::patch('loan-products/{encodedId}/toggle-status', [LoanProductController::class, 'toggleStatus'])->name('loan-products.toggle-status');
});

////////////////////////////////////////////// END LOAN PRODUCT MANAGEMENT ///////////////////////////////////////////

////////////////////////////////////////////// LOAN CALCULATOR ///////////////////////////////////////////

Route::middleware(['auth'])->group(function () {
    Route::get('loan-calculator', [LoanCalculatorController::class, 'index'])->name('loan-calculator.index');
    Route::post('loan-calculator/calculate', [LoanCalculatorController::class, 'calculate'])->name('loan-calculator.calculate');
    Route::post('loan-calculator/compare', [LoanCalculatorController::class, 'compare'])->name('loan-calculator.compare');
    Route::get('loan-calculator/products', [LoanCalculatorController::class, 'products'])->name('loan-calculator.products');
    Route::get('loan-calculator/product-details', [LoanCalculatorController::class, 'productDetails'])->name('loan-calculator.product-details');
    Route::get('loan-calculator/export-pdf', [LoanCalculatorController::class, 'exportPdf'])->name('loan-calculator.export-pdf');
    Route::get('loan-calculator/export-excel', [LoanCalculatorController::class, 'exportExcel'])->name('loan-calculator.export-excel');
    Route::get('loan-calculator/history', [LoanCalculatorController::class, 'history'])->name('loan-calculator.history');
    Route::post('loan-calculator/save', [LoanCalculatorController::class, 'save'])->name('loan-calculator.save');

    // Loan size type report
    Route::get('reports/loan-size-type', [LoanReportController::class, 'loanSizeTypeReport'])->name('reports.loan-size-type');
    Route::get('reports/loan-size-type/export', [LoanReportController::class, 'loanSizeTypeExport'])->name('reports.loan-size-type.export');
    Route::get('reports/loan-size-type/export-pdf', [LoanReportController::class, 'loanSizeTypeExportPdf'])->name('reports.loan-size-type.export-pdf');

    // Monthly performance report
    Route::get('reports/monthly-performance', [LoanReportController::class, 'monthlyPerformanceReport'])->name('reports.monthly-performance');
    Route::get('reports/monthly-performance/export', [LoanReportController::class, 'monthlyPerformanceExport'])->name('reports.monthly-performance.export');
    Route::get('reports/monthly-performance/export-pdf', [LoanReportController::class, 'monthlyPerformanceExportPdf'])->name('reports.monthly-performance.export-pdf');

  // New Balance Sheet report
  Route::get('reports/balance-sheet', [NewBalanceSheetReportController::class, 'index'])->name('reports.balance-sheet');

  // Simple SMS send endpoint for navbar modal
  Route::post('sms/send', function(\Illuminate\Http\Request $request) {
      $validated = $request->validate([
          'phone' => 'required|string',
          'message' => 'required|string|max:500'
      ]);
      try {
          \App\Helpers\SmsHelper::send($validated['phone'], $validated['message']);
          return response()->json(['success' => true]);
      } catch (\Throwable $e) {
          \Log::error('SMS send failed: '.$e->getMessage());
          return response()->json(['success' => false, 'message' => 'SMS send failed'], 500);
      }
  })->name('sms.send');
});

////////////////////////////////////////////// END LOAN CALCULATOR ///////////////////////////////////////////

////////////////////////////////////////////// GROUP MANAGEMENT ///////////////////////////////////////////

Route::middleware(['auth'])->group(function () {
    Route::get('groups', [GroupController::class, 'index'])->name('groups.index');
    Route::get('groups/create', [GroupController::class, 'create'])->name('groups.create');
    Route::post('groups', [GroupController::class, 'store'])->name('groups.store');
    Route::get('groups/{encodedId}', [GroupController::class, 'show'])->name('groups.show');
    Route::get('groups/{encodedId}/edit', [GroupController::class, 'edit'])->name('groups.edit');
    Route::put('groups/{encodedId}', [GroupController::class, 'update'])->name('groups.update');// Badilisha 'GroupController' na jina la controller yako halisi.
    Route::post('repayments/{encodedId}', [GroupController::class, 'groupStore'])->name('groups.groupStore');
    Route::delete('groups/{encodedId}', [GroupController::class, 'destroy'])->name('groups.destroy');
    Route::get('groups/{encodedId}/payment', [GroupController::class, 'payment'])->name('groups.payment');

    // Group member management routes
    Route::delete('groups/{encodedId}/members/{memberId}', [GroupController::class, 'removeMember'])->name('groups.members.remove');
    Route::post('groups/{encodedId}/transfer-member', [GroupController::class, 'transferMember'])->name('groups.members.transfer');
    Route::get('groups/{encodedId}/members-for-transfer', [GroupController::class, 'getMembersForTransfer'])->name('groups.members.for-transfer');
});
////////////////////////////////////////////// GROUP MEMBER MANAGEMENT ///////////////////////////////////////////

Route::middleware(['auth'])->group(function () {
    Route::get('groups/{encodedId}/members/create', [GroupMemberController::class, 'create'])->name('group-members.create');
    Route::post('groups/{encodedId}/members', [GroupMemberController::class, 'store'])->name('group-members.store');
    Route::delete('groups/{encodedId}/members/{member}', [GroupMemberController::class, 'destroy'])->name('group-members.destroy');
});

////////////////////////////////////////////// END GROUP MANAGEMENT ///////////////////////////////////////////

////////////////////////////////////////////// LOAN MANAGEMENT ///////////////////////////////////////////

Route::middleware(['auth'])->group(function () {
    Route::get('loans', [LoanController::class, 'index'])->name('loans.index');
    Route::get('loans/{encodedId}/fees-receipt', [LoanController::class, 'feesReceipt'])->name('loans.fees_receipt');
    Route::get('loans/list', [LoanController::class, 'listLoans'])->name('loans.list');
    Route::get('loans/writtenoff/data', [LoanController::class, 'getWrittenOffLoansData'])->name('loans.writtenoff.data');
    Route::get('loans/writtenoff', function () {
        $loans = \App\Models\Loan::with(['customer', 'product', 'branch'])
            ->where('status', 'written_off')
            ->get();
        return view('loans.written_off', compact('loans'));
    })->name('loans.writtenoff');
    Route::get('loans/chart-accounts/{type}', [LoanController::class, 'getChartAccountsByType'])->name('loans.chart-accounts');
    Route::post('loans/import', [LoanController::class, 'importLoans'])->name('loans.import');
    Route::get('loans/import-template', [LoanController::class, 'downloadTemplate'])->name('loans.import-template');
    Route::get('loans/status/{status}', [LoanController::class, 'loansByStatus'])->name('loans.by-status');

    // Opening Balance Routes for loans
    Route::get('loans/opening-balance/template', [LoanController::class, 'downloadOpeningBalanceTemplate'])->name('loans.opening-balance.template');
    Route::post('loans/opening-balance', [LoanController::class, 'storeOpeningBalance'])->name('loans.opening-balance.store');

    // New Loan Application Routes (must come BEFORE general loan routes)
    Route::get('loans/application', [LoanController::class, 'applicationIndex'])->name('loans.application.index');
    Route::get('loans/application/create', [LoanController::class, 'applicationCreate'])->name('loans.application.create');
    Route::post('loans/application', [LoanController::class, 'applicationStore'])->name('loans.application.store');
    Route::get('loans/application/{encodedId}', [LoanController::class, 'applicationShow'])->name('loans.application.show');
    Route::get('loans/application/{encodedId}/edit', [LoanController::class, 'applicationEdit'])->name('loans.application.edit');
    Route::put('loans/application/{encodedId}', [LoanController::class, 'applicationUpdate'])->name('loans.application.update');
    Route::patch('loans/application/{encodedId}/approve', [LoanController::class, 'applicationApprove'])->name('loans.application.approve');
    Route::patch('loans/application/{encodedId}/reject', [LoanController::class, 'applicationReject'])->name('loans.application.reject');
    Route::delete('loans/application/{encodedId}', [LoanController::class, 'applicationDelete'])->name('loans.application.delete');

    // General loan routes (must come AFTER specific routes)
    Route::get('loans/create', [LoanController::class, 'create'])->name('loans.create');
    Route::post('loans', [LoanController::class, 'store'])->name('loans.store');
    Route::get('loans/{loan}', [LoanController::class, 'show'])->name('loans.show');
    Route::get('loans/{encodedId}/edit', [LoanController::class, 'edit'])->name('loans.edit');
    Route::put('loans/{encodedId}', [LoanController::class, 'update'])->name('loans.update');
    Route::get('loans/{encodedId}/top-up', [LoanTopUpController::class, 'show'])->name('loans.top_up');
    Route::post('loans/{encodedId}/top-up', [LoanTopUpController::class, 'store'])->name('loans.top_up.store');
    Route::delete('loans/{loan}', [LoanController::class, 'destroy'])->name('loans.destroy');
    Route::get('loans/applist', [LoanController::class, 'appList'])->name('loans.applist');
    Route::get('loans/appcreate', [LoanController::class, 'appCreate'])->name('loans.appcreate');
    Route::post('loans/appstore', [LoanController::class, 'appStore'])->name('loans.appstore');
    Route::get('loans/{loan}/appedit', [LoanController::class, 'appEdit'])->name('loans.appedit');
    Route::put('loans/{encodedId}/appupdate', [LoanController::class, 'appUpdate'])->name('loans.appupdate');
    Route::delete('loans/{loan}/appdestroy', [LoanController::class, 'appDestroy'])->name('loans.appdestroy');
    Route::get('loans/{loan}/appshow', [LoanController::class, 'appShow'])->name('loans.appshow');
    Route::post('/loan-files', [LoanController::class, 'loanDocument'])->name('loan-documents.store');
    Route::delete('/loan-documents/{loanFile}', [LoanController::class, 'destroyLoanDocument'])->name('loan-documents.destroy');
    Route::post('/loans/{loan}/guarantors', [LoanController::class, 'addGuarantor'])->name('loans.addGuarantor');
    Route::delete('/loans/{loan}/guarantors/{guarantor}', [LoanController::class, 'removeGuarantor'])->name('loans.removeGuarantor');
    Route::get('/loans/{encodedId}/export-details', [LoanController::class, 'exportLoanDetails'])->name('loans.export-details');

    // Loan Collateral Routes
    Route::post('/loan-collaterals', [LoanCollateralController::class, 'store'])->name('loan-collaterals.store');
    Route::get('/loan-collaterals/{collateral}', [LoanCollateralController::class, 'show'])->name('loan-collaterals.show');
    Route::put('/loan-collaterals/{collateral}', [LoanCollateralController::class, 'update'])->name('loan-collaterals.update');
    Route::patch('/loan-collaterals/{collateral}/status', [LoanCollateralController::class, 'updateStatus'])->name('loan-collaterals.update-status');
    Route::delete('/loan-collaterals/{collateral}', [LoanCollateralController::class, 'destroy'])->name('loan-collaterals.destroy');
    Route::delete('/loan-collaterals/{collateral}/remove-file', [LoanCollateralController::class, 'removeFile'])->name('loan-collaterals.remove-file');

    // Loan Repayment Routes
    Route::post('/repayments', [LoanRepaymentController::class, 'store'])->name('repayments.store');
    Route::post('/repayments/settle', [LoanRepaymentController::class, 'storeSettlementRepayment'])->name('repayments.settle');
    Route::get('/repayments/history/{loanId}', [LoanRepaymentController::class, 'getRepaymentHistory'])->name('repayments.history');
    Route::get('/repayments/schedule/{scheduleId}', [LoanRepaymentController::class, 'getScheduleDetails'])->name('repayments.schedule-details');
    Route::post('/repayments/remove-penalty/{scheduleId}', [LoanRepaymentController::class, 'removePenalty'])->name('repayments.remove-penalty');
    Route::post('/repayments/calculate-schedule/{loanId}', [LoanRepaymentController::class, 'calculateSchedule'])->name('repayments.calculate-schedule');
    Route::post('/repayments/bulk', [LoanRepaymentController::class, 'bulkRepayment'])->name('repayments.bulk');
    Route::delete('/repayments/bulk-delete', [LoanRepaymentController::class, 'bulkDestroy'])->name('repayments.bulk-delete');

    // Repayment CRUD Routes
    Route::get('/repayments/{id}/edit', [LoanRepaymentController::class, 'edit'])->name('repayments.edit');
    Route::put('/repayments/{id}', [LoanRepaymentController::class, 'update'])->name('repayments.update');
    Route::delete('/repayments/{id}', [LoanRepaymentController::class, 'destroy'])->name('repayments.destroy');
    Route::get('/repayments/{id}/print', [LoanRepaymentController::class, 'printReceipt'])->name('repayments.print');


});

////////////////////////////////////////////// END LOAN MANAGEMENT ///////////////////////////////////////////

// Loan Approval Routes
Route::middleware(['auth'])->group(function () {
    Route::post('/loans/{encodedId}/check', [LoanController::class, 'checkLoan'])->name('loans.check');
    Route::post('/loans/{encodedId}/approve', [LoanController::class, 'approveLoan'])->name('loans.approve');
    Route::post('/loans/{encodedId}/authorize', [LoanController::class, 'authorizeLoan'])->name('loans.authorize');
    Route::post('/loans/{encodedId}/disburse', [LoanController::class, 'disburseLoan'])->name('loans.disburse');
    Route::post('/loans/{encodedId}/reject', [LoanController::class, 'rejectLoan'])->name('loans.reject');
    Route::post('/loans/{encodedId}/default', [LoanController::class, 'defaultLoan'])->name('loans.default');
    Route::post('/loans/{encodedId}/settle', [LoanController::class, 'settleRepayment'])->name('loans.settle');
});

////////////////////////////////////////////// CASHCOLLATERALS MANAGEMENT ///////////////////////////////////////////

Route::middleware(['auth'])->prefix('cash_collaterals')->group(function () {
    Route::get('/', [CashCollateralController::class, 'index'])->name('cash_collaterals.index');
    Route::get('/create', [CashCollateralController::class, 'create'])->name('cash_collaterals.create');
    Route::post('/', [CashCollateralController::class, 'store'])->name('cash_collaterals.store');
    Route::get('/{cashcollateral}', [CashCollateralController::class, 'show'])->name('cash_collaterals.show');
    Route::get('/{cashcollateral}/edit', [CashCollateralController::class, 'edit'])->name('cash_collaterals.edit');
    Route::delete('/{cashcollateral}/delete', [CashCollateralController::class, 'destroy'])->name('cash_collaterals.destroy');
    Route::put('/{cashcollateral}', [CashCollateralController::class, 'update'])->name('cash_collaterals.update');


    // Direct Receipt and Payment Routes for Cash Collateral
    Route::get('/receipts/{receipt}/edit', [CashCollateralController::class, 'editReceipt'])->name('receipts.edit');
    Route::put('/receipts/{receipt}', [CashCollateralController::class, 'updateReceipt'])->name('receipts.update');
    Route::delete('/receipts/{receipt}', [CashCollateralController::class, 'deleteReceipt'])->name('receipts.destroy');

    Route::get('/payments/{payment}/edit', [CashCollateralController::class, 'editPayment'])->name('payments.edit');
    Route::put('/payments/{payment}', [CashCollateralController::class, 'updatePayment'])->name('payments.update');
    Route::delete('/payments/{payment}', [CashCollateralController::class, 'deletePayment'])->name('payments.destroy');

    // Deposit and Withdrawal routes
    Route::get('/{cashcollateral}/deposit', [CashCollateralController::class, 'deposit'])->name('cash_collaterals.deposit');
    Route::post('/deposit-store', [CashCollateralController::class, 'depositStore'])->name('cash_collaterals.depositStore');
    Route::get('/print-deposit-receipt/{id}', [CashCollateralController::class, 'printDepositReceipt'])->name('cash_collaterals.printDepositReceipt');
    Route::get('/{cashcollateral}/withdraw', [CashCollateralController::class, 'withdraw'])->name('cash_collaterals.withdraw');
    Route::post('/withdraw-store', [CashCollateralController::class, 'withdrawStore'])->name('cash_collaterals.withdrawStore');
    Route::get('/print-withdrawal-receipt/{id}', [CashCollateralController::class, 'printWithdrawalReceipt'])->name('cash_collaterals.printWithdrawalReceipt');
});

////////////////////////////////////////////// END CASHCOLLATERALS  MANAGEMENT ///////////////////////////////////////////

Route::get('/get-districts/{regionId}', [LocationController::class, 'getDistricts']);

// Chat routes
Route::middleware(['auth'])->group(function () {
    Route::get('/chat', [App\Http\Controllers\ChatController::class, 'index'])->name('chat.index');
    Route::get('/chat/messages/{user}', [App\Http\Controllers\ChatController::class, 'fetchMessages'])->name('chat.messages');
    Route::post('/chat/send', [App\Http\Controllers\ChatController::class, 'sendMessage'])->name('chat.send');
    Route::post('/chat/mark-read', [App\Http\Controllers\ChatController::class, 'markAsRead'])->name('chat.mark-read');
    Route::get('/chat/unread-count', [App\Http\Controllers\ChatController::class, 'getUnreadCount'])->name('chat.unread-count');
    Route::post('/chat/clear', [App\Http\Controllers\ChatController::class, 'clearChat'])->name('chat.clear');
    Route::get('/chat/online-users', [App\Http\Controllers\ChatController::class, 'getOnlineUsers'])->name('chat.online-users');
    Route::get('/chat/download/{messageId}', [App\Http\Controllers\ChatController::class, 'downloadFile'])->name('chat.download');
});

// Calendar Routes
Route::middleware(['auth'])->group(function () {
    Route::get('/calendar', [App\Http\Controllers\CalendarController::class, 'index'])->name('calendar.index');
    Route::get('/loan-messages', [App\Http\Controllers\LoanMessagesController::class, 'getMessages'])->name('loan-messages.get');
});

Route::post('sms/bulk', [App\Http\Controllers\DashboardController::class, 'sendBulkSms'])->name('sms.bulk');

// Email Routes
Route::middleware(['auth'])->group(function () {
    Route::get('/emails/compose', [EmailController::class, 'index'])->name('emails.compose');
    Route::get('/emails/microfinances', [EmailController::class, 'getMicrofinances'])->name('emails.microfinances');
    Route::post('/emails/send', [EmailController::class, 'sendBulkEmails'])->name('emails.send');
    Route::post('/emails/test', [EmailController::class, 'testEmail'])->name('emails.test');
});

Route::post('/logout', function () {
    Auth::logout();
    return redirect('/')->with('success', 'You are successfully logout.');
})->middleware('auth');
