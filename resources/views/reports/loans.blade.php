@extends('layouts.main')

@section('title', 'Loans Reports')

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <x-breadcrumbs-with-icons :links="[
                ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
                ['label' => 'Loans Reports', 'url' => '#', 'icon' => 'bx bx-credit-card']
            ]" />
        <h6 class="mb-0 text-uppercase">LOANS REPORTS</h6>
        <hr />

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title mb-4">Loans Reports</h4>
                        <p class="text-muted">Loans reports functionality will be implemented here.</p>

                        <div class="row">
                            @can('view loan portfolio report')
                            <div class="col-md-6 col-lg-4 mb-4">
                                <div class="card border-primary">
                                    <div class="card-body text-center">
                                        <div class="mb-3">
                                            <i class="bx bx-credit-card fs-1 text-primary"></i>
                                        </div>
                                        <h5 class="card-title">Loan Portfolio Report</h5>
                                        <p class="card-text">Comprehensive overview of all active loans and their status.</p>
                                        <a href="{{ route('accounting.loans.reports.portfolio') }}" class="btn btn-primary">
                                            <i class="bx bx-file me-1"></i> Generate Report
                                        </a>
                                    </div>
                                </div>
                            </div>
                            @endcan
                            <!-- Loan Portfolio Tracking Report -->
                            <div class="col-lg-4 col-md-6 mb-4">
                                <div class="card h-100">
                                    <div class="card-body text-center">
                                        <div class="icon-box mb-3">
                                            <i class="bx bx-line-chart fs-1 text-primary"></i>
                                        </div>
                                        <h5 class="card-title">Loan Portfolio Tracking</h5>
                                        <p class="card-text text-muted">Track portfolio by period, officer, branch and group by day/week/month.</p>
                                        <a href="{{ route('loans.reports.portfolio_tracking') }}" class="btn btn-primary">
                                            <i class="fas fa-file-alt me-1"></i> View Report
                                        </a>
                                    </div>
                                </div>
                            </div>

                            @can('view loan performance report')
                            <div class="col-md-6 col-lg-4 mb-4">
                                <div class="card border-success">
                                    <div class="card-body text-center">
                                        <div class="mb-3">
                                            <i class="bx bx-trending-up fs-1 text-success"></i>
                                        </div>
                                        <h5 class="card-title">Loan Performance Report</h5>
                                        <p class="card-text">Analyze loan performance metrics and repayment trends.</p>
                                        <a href="{{ route('accounting.loans.reports.performance') }}" class="btn btn-success">
                                            <i class="bx bx-file me-1"></i> Generate Report
                                        </a>
                                    </div>
                                </div>
                            </div>
                            @endcan

                            <!-- Loan Size Type Report -->
                            <div class="col-md-6 col-lg-4 mb-4">
                                <div class="card border-secondary">
                                    <div class="card-body text-center">
                                        <div class="mb-3">
                                            <i class="bx bx-grid-alt fs-1 text-secondary"></i>
                                        </div>
                                        <h5 class="card-title">Loan Size Type Report</h5>
                                        <p class="card-text">Bucket loans by size and show counts, amounts, arrears and outstanding.</p>
                                        <a href="{{ route('reports.loan-size-type') }}" class="btn btn-secondary">
                                            <i class="bx bx-file me-1"></i> View Report
                                        </a>
                                    </div>
                                </div>
                            </div>

                            <!-- Monthly Loan Performance Report -->
                            <div class="col-md-6 col-lg-4 mb-4">
                                <div class="card border-secondary">
                                    <div class="card-body text-center">
                                        <div class="mb-3">
                                            <i class="bx bx-pie-chart fs-1 text-secondary"></i>
                                        </div>
                                        <h5 class="card-title">Monthly Loan Performance</h5>
                                        <p class="card-text">Monthly view of loan given, interest, collections, outstanding and performance.</p>
                                        <a href="{{ route('reports.monthly-performance') }}" class="btn btn-secondary">
                                            <i class="bx bx-file me-1"></i> View Report
                                        </a>
                                    </div>
                                </div>
                            </div>
                            @can('view loan delinquency report')
                            <div class="col-md-6 col-lg-4 mb-4">
                                <div class="card border-warning">
                                    <div class="card-body text-center">
                                        <div class="mb-3">
                                            <i class="bx bx-error-circle fs-1 text-warning"></i>
                                        </div>
                                        <h5 class="card-title">Delinquency Report</h5>
                                        <p class="card-text">Track overdue loans and payment delinquencies.</p>
                                        <a href="{{ route('accounting.loans.reports.delinquency') }}" class="btn btn-warning">
                                            <i class="bx bx-file me-1"></i> Generate Report
                                        </a>
                                    </div>
                                </div>
                            </div>
                            @endcan
                            @can('view loan disbursement report')
                            <div class="col-md-6 col-lg-4 mb-4">
                                <div class="card border-primary">
                                    <div class="card-body text-center">
                                        <div class="mb-3">
                                            <i class="bx bx-dollar-circle fs-1 text-primary"></i>
                                        </div>
                                        <h5 class="card-title">Loan Disbursement Report</h5>
                                        <p class="card-text">Generate a detailed summary of all loans disbursed within a specific period.</p>
                                        <a href="{{ route('accounting.loans.reports.disbursed') }}" class="btn btn-success">
                                            <i class="bx bx-file me-1"></i> Generate Report
                                        </a>
                                    </div>
                                </div>
                            </div>
                            @endcan
                            @can('view loan repayments report')
                            <div class="col-md-6 col-lg-4 mb-4">
                                <div class="card border-success">
                                    <div class="card-body text-center">
                                        <div class="mb-3">
                                            <i class="bx bx-money fs-1 text-success"></i>
                                        </div>
                                        <h5 class="card-title">Loan Repayments Report</h5>
                                        <p class="card-text">Generate a detailed summary of all loan repayments received within a specific period.</p>
                                        <a href="{{ route('accounting.loans.reports.repayment') }}" class="btn btn-success">
                                            <i class="bx bx-file me-1"></i> Generate Report
                                        </a>
                                    </div>
                                </div>
                            </div>
                            @endcan


                            <!-- Additional Loan Reports -->
                            @can('view loan aging report')
                            <div class="col-md-6 col-lg-4 mb-4">
                                <div class="card border-danger">
                                    <div class="card-body text-center">
                                        <div class="mb-3">
                                            <i class="bx bx-timer fs-1 text-danger"></i>
                                        </div>
                                        <h5 class="card-title">Loan Aging Report</h5>
                                        <p class="card-text">Analyze overdue loans and aging buckets for receivables within a specific period</p>
                                        <a href="{{ route('accounting.loans.reports.loan_aging') }}" class="btn btn-danger">
                                            <i class="bx bx-file me-1"></i> Generate Report
                                        </a>
                                    </div>
                                </div>
                            </div>
                            @endcan
                            @can('view loan aging installment report')
                            <div class="col-md-6 col-lg-4 mb-4">
                                <div class="card border-dark">
                                    <div class="card-body text-center">
                                        <div class="mb-3">
                                            <i class="bx bx-calendar-check fs-1 text-dark"></i>
                                        </div>
                                        <h5 class="card-title">Loan Aging Installment Report</h5>
                                        <p class="card-text">Analyze outstanding installment principal amounts and aging buckets for scheduled payments</p>
                                        <a href="{{ route('accounting.loans.reports.loan_aging_installment') }}" class="btn btn-dark">
                                            <i class="bx bx-file me-1"></i> Generate Report
                                        </a>
                                    </div>
                                </div>
                            </div>
                            @endcan
                            @can('view loan outstanding report')
                            <div class="col-md-6 col-lg-4 mb-4">
                                <div class="card border-info">
                                    <div class="card-body text-center">
                                        <div class="mb-3">
                                            <i class="bx bx-calculator fs-1 text-info"></i>
                                        </div>
                                        <h5 class="card-title">Loan Outstanding Balance Report</h5>
                                        <p class="card-text">View all loans with their current outstanding balances and details.</p>
                                        <a href="{{ route('accounting.loans.reports.loan_outstanding') }}" class="btn btn-info">
                                            <i class="bx bx-file me-1"></i> Generate Report
                                        </a>
                                    </div>
                                </div>
                            </div>
                            @endcan


                            <!-- More Loan Reports -->
                            @can('view loan arrears report')
                            <div class="col-md-6 col-lg-4 mb-4">
                                <div class="card border-warning">
                                    <div class="card-body text-center">
                                        <div class="mb-3">
                                            <i class="bx bx-error-circle fs-1 text-warning"></i>
                                        </div>
                                        <h5 class="card-title">Loan Arrears Report</h5>
                                        <p class="card-text">Track loans that are in arrears with overdue payments and arrears analysis.</p>
                                        <a href="{{ route('accounting.loans.reports.loan_arrears') }}" class="btn btn-warning">
                                            <i class="bx bx-file me-1"></i> Generate Report
                                        </a>
                                    </div>
                                </div>
                            </div>
                            @endcan


                            @can('view loan expected vs collected report')


                            <!-- Expected vs Collected Report -->
                            <div class="col-md-6 col-lg-4 mb-4">
                                <div class="card border-primary">
                                    <div class="card-body text-center">
                                        <div class="mb-3">
                                            <i class="bx bx-bar-chart-alt-2 fs-1 text-primary"></i>
                                        </div>
                                        <h5 class="card-title">Expected vs Collected Report</h5>
                                        <p class="card-text">Compare expected collections from loan schedules against actual collections for any period.</p>
                                        <a href="{{ route('accounting.loans.reports.expected_vs_collected') }}" class="btn btn-primary">
                                            <i class="bx bx-file me-1"></i> Generate Report
                                        </a>
                                    </div>
                                </div>
                            </div>
                            @endcan
                            @can('view loan portfolio at risk report')


                            <!-- Portfolio at Risk Report -->
                            <div class="col-md-6 col-lg-4 mb-4">
                                <div class="card border-danger">
                                    <div class="card-body text-center">
                                        <div class="mb-3">
                                            <i class="bx bx-shield-x fs-1 text-danger"></i>
                                        </div>
                                        <h5 class="card-title">Portfolio at Risk (PAR) Report</h5>
                                        <p class="card-text">Assess portfolio risk with PAR analysis showing loans past due and risk indicators.</p>
                                        <a href="{{ route('accounting.loans.reports.portfolio_at_risk') }}" class="btn btn-danger">
                                            <i class="bx bx-file me-1"></i> Generate Report
                                        </a>
                                    </div>
                                </div>
                            </div>
                            @endcan


                            <!-- Internal Portfolio Analysis Report -->
                            @can('view loan internal portfolio analysis report')

                            <div class="col-md-6 col-lg-4 mb-4">
                                <div class="card border-info">
                                    <div class="card-body text-center">
                                        <div class="mb-3">
                                            <i class="bx bx-analyze fs-1 text-info"></i>
                                        </div>
                                        <h5 class="card-title">Internal Portfolio Analysis</h5>
                                        <p class="card-text">Conservative risk analysis showing only overdue amounts at risk for detailed internal assessment.</p>
                                        <a href="{{ route('accounting.loans.reports.internal_portfolio_analysis') }}" class="btn btn-info">
                                            <i class="bx bx-file me-1"></i> Generate Report
                                        </a>
                                    </div>
                                </div>
                            </div>
                            @endcan
                            <!-- Non Performing Loan Report -->
                            @can('view loan non performing loan report')
                            <div class="col-lg-4 col-md-6 mb-4">
                                <div class="card h-100">
                                    <div class="card-body text-center">
                                        <div class="icon-box mb-3">
                                            <i class="fas fa-ban fa-3x text-danger"></i>
                                        </div>
                                        <h5 class="card-title">Non Performing Loan Report</h5>
                                        <p class="card-text text-muted">View and analyze non performing loans, provisions, and risk metrics.</p>
                                        <a href="{{ route('accounting.loans.reports.npl') }}" class="btn btn-danger">
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
