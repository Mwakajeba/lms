@extends('layouts.main')

@section('title', 'Accounting Reports')

@section('content')
    <div class="page-wrapper">
        <div class="page-content">
            <x-breadcrumbs-with-icons :links="[
                ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
                ['label' => 'Accounting Reports', 'url' => '#', 'icon' => 'bx bx-calculator']
            ]" />
            <h6 class="mb-0 text-uppercase">ACCOUNTING REPORTS</h6>
            <hr />

            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <h4 class="card-title mb-4">Accounting Reports</h4>

                            @if(session('success'))
                                <div class="alert alert-success alert-dismissible fade show" role="alert">
                                    <i class="bx bx-check-circle me-2"></i>
                                    {{ session('success') }}
                                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                </div>
                            @endif

                            @if(isset($errors) && $errors->any())
                                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                    <i class="bx bx-error-circle me-2"></i>
                                    Please fix the following errors:
                                    <ul class="mb-0 mt-2">
                                        @foreach($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                </div>
                            @endif

                            <div class="row">
                                <!-- Balance Sheet Report -->
                                @can('view balance sheet report')
                                <div class="col-md-6 col-lg-4 mb-4">
                                    <div class="card border-warning">
                                        <div class="card-body text-center">
                                            <div class="mb-3">
                                                <i class="bx bx-bar-chart fs-1 text-warning"></i>
                                            </div>
                                            <h5 class="card-title">Balance Sheet Report</h5>
                                            <p class="card-text">View assets, liabilities, and equity position at a specific date.</p>
                                            <a href="{{ route('accounting.reports.balance-sheet') }}" class="btn btn-warning">
                                                <i class="bx bx-file me-1"></i> Generate Report
                                            </a>
                                        </div>
                                    </div>
                                </div>
                                @endcan

                                <!-- Trial Balance Report -->
                                @can('view trial balance report')
                                <div class="col-md-6 col-lg-4 mb-4">
                                    <div class="card border-success">
                                        <div class="card-body text-center">
                                            <div class="mb-3">
                                                <i class="bx bx-calculator fs-1 text-success"></i>
                                            </div>
                                            <h5 class="card-title">Trial Balance Report</h5>
                                            <p class="card-text">Generate trial balance with different layout options for any period.</p>
                                            <a href="{{ route('accounting.reports.trial-balance') }}" class="btn btn-success">
                                                <i class="bx bx-file me-1"></i> Generate Report
                                            </a>
                                        </div>
                                    </div>
                                </div>
                                @endcan

                                <!-- Income Statement Report -->
                                @can('view income statement report')
                                <div class="col-md-6 col-lg-4 mb-4">
                                    <div class="card border-info">
                                        <div class="card-body text-center">
                                            <div class="mb-3">
                                                <i class="bx bx-line-chart fs-1 text-info"></i>
                                            </div>
                                            <h5 class="card-title">Income Statement Report</h5>
                                            <p class="card-text">Generate profit and loss statements showing revenue and expenses.</p>
                                            <a href="{{ route('accounting.reports.income-statement') }}" class="btn btn-info">
                                                <i class="bx bx-file me-1"></i> Generate Report
                                            </a>
                                        </div>
                                    </div>
                                </div>
                                @endcan

                                <!-- Cash Book Report -->
                                @can('view cash book report')
                                <div class="col-md-6 col-lg-4 mb-4">
                                    <div class="card border-danger">
                                        <div class="card-body text-center">
                                            <div class="mb-3">
                                                <i class="bx bx-book fs-1 text-danger"></i>
                                            </div>
                                            <h5 class="card-title">Cash Book Report</h5>
                                            <p class="card-text">Track cash receipts and payments with detailed transaction records.</p>
                                            <a href="{{ route('accounting.reports.cash-book') }}" class="btn btn-danger">
                                                <i class="bx bx-file me-1"></i> Generate Report
                                            </a>
                                        </div>
                                    </div>
                                </div>
                                @endcan

                                <!-- Cash Flow Report -->
                                @can('view cash flow report')
                                <div class="col-md-6 col-lg-4 mb-4">
                                    <div class="card border-purple">
                                        <div class="card-body text-center">
                                            <div class="mb-3">
                                                <i class="bx bx-transfer fs-1 text-purple"></i>
                                            </div>
                                            <h5 class="card-title">Cash Flow Report</h5>
                                            <p class="card-text">Analyze cash inflows and outflows from operating, investing, and financing activities.</p>
                                            <a href="{{ route('accounting.reports.cash-flow') }}" class="btn btn-success">
                                                <i class="bx bx-file me-1"></i> Generate Report
                                            </a>
                                        </div>
                                    </div>
                                </div>
                                @endcan

                                <!-- General Ledger Report -->
                                @can('view general ledger report')
                                <div class="col-md-6 col-lg-4 mb-4">
                                    <div class="card border-secondary">
                                        <div class="card-body text-center">
                                            <div class="mb-3">
                                                <i class="bx bx-list-ul fs-1 text-secondary"></i>
                                            </div>
                                            <h5 class="card-title">General Ledger Report</h5>
                                            <p class="card-text">View detailed transaction history for all chart of accounts.</p>
                                            <a href="{{ route('accounting.reports.general-ledger') }}" class="btn btn-secondary">
                                                <i class="bx bx-file me-1"></i> Generate Report
                                            </a>
                                        </div>
                                    </div>
                                </div>
                                @endcan

                                <!-- Expenses Summary Report -->
                                @can('view expenses summary report')
                                <div class="col-md-6 col-lg-4 mb-4">
                                    <div class="card border-teal">
                                        <div class="card-body text-center">
                                            <div class="mb-3">
                                                <i class="bx bx-dollar-circle fs-1 text-teal"></i>
                                            </div>
                                            <h5 class="card-title">Expenses Summary Report</h5>
                                            <p class="card-text">Analyze and categorize all expenses with detailed breakdowns.</p>
                                            <a href="{{ route('accounting.reports.expenses-summary') }}" class="btn btn-success">
                                                <i class="bx bx-file me-1"></i> Generate Report
                                            </a>
                                        </div>
                                    </div>
                                </div>
                                @endcan

                                <!-- Accounting Notes Report -->
                                @can('view accounting notes report')
                                <div class="col-md-6 col-lg-4 mb-4">
                                    <div class="card border-indigo">
                                        <div class="card-body text-center">
                                            <div class="mb-3">
                                                <i class="bx bx-note fs-1 text-indigo"></i>
                                            </div>
                                            <h5 class="card-title">Accounting Notes Report</h5>
                                            <p class="card-text">Generate comprehensive accounting notes.</p>
                                            <a href="{{ route('accounting.reports.accounting-notes') }}" class="btn btn-success">
                                                <i class="bx bx-file me-1"></i> Generate Report
                                            </a>
                                        </div>
                                    </div>
                                </div>
                                @endcan

                                <!-- Changes in Equity Report -->
                                @can('view changes in equity report')
                                <div class="col-md-6 col-lg-4 mb-4">
                                    <div class="card border-orange">
                                        <div class="card-body text-center">
                                            <div class="mb-3">
                                                <i class="bx bx-trending-up fs-1 text-orange"></i>
                                            </div>
                                            <h5 class="card-title">Changes in Equity Report</h5>
                                            <p class="card-text">Track changes in shareholders' equity over time.</p>
                                            <a href="{{ route('accounting.reports.changes-equity') }}" class="btn btn-success">
                                                <i class="bx bx-file me-1"></i> Generate Report
                                            </a>
                                        </div>
                                    </div>
                                </div>
                                @endcan

                                <!-- Fees Report -->
                                @can('view fees report')
                                <div class="col-md-6 col-lg-4 mb-4">
                                    <div class="card border-primary">
                                        <div class="card-body text-center">
                                            <div class="mb-3">
                                                <i class="bx bx-money fs-1 text-primary"></i>
                                            </div>
                                            <h5 class="card-title">Fees Report</h5>
                                            <p class="card-text">Generate comprehensive reports on all loan fees collected and their breakdowns.</p>
                                            <a href="{{ route('accounting.reports.fees') }}" class="btn btn-primary">
                                                <i class="bx bx-file me-1"></i> Generate Report
                                            </a>
                                        </div>
                                    </div>
                                </div>
                                @endcan

                                <!-- Penalties Report -->
                                @can('view penalties report')
                                <div class="col-md-6 col-lg-4 mb-4">
                                    <div class="card border-dark">
                                        <div class="card-body text-center">
                                            <div class="mb-3">
                                                <i class="bx bx-error-circle fs-1 text-dark"></i>
                                            </div>
                                            <h5 class="card-title">Penalties Report</h5>
                                            <p class="card-text">Track and analyze penalty charges applied to loans and their collection status.</p>
                                            <a href="{{ route('accounting.reports.penalties') }}" class="btn btn-dark">
                                                <i class="bx bx-file me-1"></i> Generate Report
                                            </a>
                                        </div>
                                    </div>
                                </div>
                                @endcan

                                <!-- Other Income Report -->
                                @can('view other income report')
                                <div class="col-md-6 col-lg-4 mb-4">
                                    <div class="card border-success">
                                        <div class="card-body text-center">
                                            <div class="mb-3">
                                                <i class="bx bx-trending-up fs-1 text-success"></i>
                                            </div>
                                            <h5 class="card-title">Other Income Report</h5>
                                            <p class="card-text">Track and analyze other income sources and their contribution to revenue.</p>
                                            <a href="{{ route('accounting.reports.other-income') }}" class="btn btn-success">
                                                <i class="bx bx-file me-1"></i> Generate Report
                                            </a>
                                        </div>
                                    </div>
                                </div>
                                @endcan

                                <!-- Budget Report -->
                                @can('view budget report')
                                <div class="col-md-6 col-lg-4 mb-4">
                                    <div class="card border-info">
                                        <div class="card-body text-center">
                                            <div class="mb-3">
                                                <i class="bx bx-target-lock fs-1 text-info"></i>
                                            </div>
                                            <h5 class="card-title">Budget Report</h5>
                                            <p class="card-text">Compare actual performance against budgeted amounts and analyze variances.</p>
                                            <a href="{{ route('accounting.reports.budget-report') }}" class="btn btn-info">
                                                <i class="bx bx-file me-1"></i> Generate Report
                                            </a>
                                        </div>
                                    </div>
                                </div>
                                @endcan

                                <!-- Bank Reconciliation Report -->
                                @can('view bank reconciliation report')
                                <div class="col-md-6 col-lg-4 mb-4">
                                    <div class="card border-warning">
                                        <div class="card-body text-center">
                                            <div class="mb-3">
                                                <i class="bx bx-check-circle fs-1 text-warning"></i>
                                            </div>
                                            <h5 class="card-title">Bank Reconciliation Report</h5>
                                            <p class="card-text">Reconcile bank statements with accounting records and identify discrepancies.</p>
                                            <a href="{{ route('accounting.reports.bank-reconciliation-report') }}" class="btn btn-warning">
                                                <i class="bx bx-file me-1"></i> Generate Report
                                            </a>
                                        </div>
                                    </div>
                                </div>
                                @endcan
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
