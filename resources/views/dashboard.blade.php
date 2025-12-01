@extends('layouts.main')

@section('title', __('app.dashboard'))

@php
use Vinkla\Hashids\Facades\Hashids;
@endphp

<style>
    .financial-section {
        margin-bottom: 20px;
    }

    .section-header {
        border-radius: 8px 8px 0 0 !important;
    }

    .section-content {
        border-radius: 0 0 8px 8px !important;
        border-top: none !important;
    }

    .account-row:hover {
        background-color: #f8f9fa;
        transition: background-color 0.2s ease;
    }

    .account-row a:hover {
        color: #007bff !important;
        text-decoration: underline !important;
    }

    .table-sm td {
        padding: 0.5rem;
        vertical-align: middle;
    }

    .section-title {
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    }



    @media print {

        .btn,
        .overlay,
        .back-to-top,
        footer {
            display: none !important;
        }

        .card {
            border: none !important;
            box-shadow: none !important;
        }

        .section-header {
            background: #333 !important;
            color: white !important;
        }
    }
</style>

@section('content')
@can('view dashboard')
<div class="page-wrapper">
    <div class="page-content">
        <!-- Welcome Section -->
        <div class="row">
            <div class="col-12">
                <div class="card border-top border-0 border-4 border-primary">
                    <div class="card-body p-4">
                        <div class="row align-items-center">
                            <div class="col-md-8">
                                <div class="card-title d-flex align-items-center">
                                    <div><i class="bx bx-home me-1 font-22 text-primary"></i></div>
                                    <h5 class="mb-0 text-primary">Welcome back, {{ auth()->user()->name }}!
                                        <span class="badge bg-warning text-dark ms-2" style="font-size: 1rem; vertical-align: middle;">
                                            Branch: {{ session('branch_id') ? optional(auth()->user()->branches->where('id', session('branch_id'))->first())->name : (auth()->user()->branch->name ?? 'N/A') }}
                                        </span>
                                    </h5>
                                </div>
                                <p class="mb-0 text-muted">Here's what's happening with your financial data today</p>
                            </div>
                            <div class="col-md-4 text-end">
                                <div class="d-flex gap-2 justify-content-end">
                                    <button type="button" class="btn btn-sm btn-info" data-bs-toggle="modal" data-bs-target="#bulkSmsModal">
                                        <i class="bx bx-envelope"></i> SMS
                                    </button>
                                    <a href="{{ route('customers.create') }}" class="btn btn-sm btn-primary">
                                        <i class="bx bx-user-plus"></i> Create Customer
                                    </a>
                                    <a href="{{ route('loans.create') }}" class="btn btn-sm btn-success">
                                        <i class="bx bx-money"></i> Create Loan
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Branch Filter -->
        <div class="row mb-3">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <form method="GET" action="{{ route('dashboard') }}" class="d-flex align-items-center">
                            <div class="me-3">
                                <label for="branch_id" class="form-label mb-0"><strong>Filter by Branch:</strong></label>
                            </div>
                            <div class="me-3">
                                <select name="branch_id" id="branch_id" class="form-select" onchange="this.form.submit()">
                                    <option value="">All Branches</option>
                                    @foreach($branches as $branch)
                                        <option value="{{ $branch->id }}" {{ $selectedBranchId == $branch->id ? 'selected' : '' }}>
                                            {{ $branch->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            @if($selectedBranchId)
                                <div class="me-3">
                                    <span class="badge bg-primary">
                                        Showing: {{ collect($branches)->where('id', $selectedBranchId)->first()['name'] ?? 'Selected Branch' }}
                                    </span>
                                </div>
                            @endif
                        </form>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Quick Stats -->
        <div class="row row-cols-1 row-cols-lg-4">
            @can('view charges')
            <div class="col">
                <a href="{{ route('customers.penalty') }}" class="text-decoration-none">
                    <div class="card radius-10">
                        <div class="card-body position-relative">
                            <div class="d-flex align-items-center">
                                <div class="flex-grow-1">
                                    <p class="mb-0 text-muted">Total Penalty</p>
                                    <h4 class="font-weight-bold text-dark">
                                        TZS {{ number_format($penaltyBalance, 2) }}
                                    </h4>
                                    <p class="text-success mb-0 font-13">Penalty balance</p>
                                </div>
                                <div class="widgets-icons bg-gradient-cosmic text-white">
                                    <i class='bx bx-error'></i>
                                </div>
                            </div>
                            <span class="stretched-link"></span>
                        </div>
                    </div>
                </a>
            </div>
            @endcan


            @can('view journals')
            <div class="col">
                <div class="card radius-10">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <p class="mb-0">Total Journals</p>
                                <h4 class="font-weight-bold">{{ $recentJournals->count() > 0 ? $recentJournals->count() : 0 }}</h4>
                                <p class="text-success mb-0 font-13">This month</p>
                            </div>
                            <div class="widgets-icons bg-gradient-cosmic text-white"><i class='bx bx-book-open'></i></div>
                        </div>
                    </div>
                </div>
            </div>
            @endcan
            @can('view payments')
            <div class="col">
                <div class="card radius-10">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <p class="mb-0">Total Payments</p>
                                <h4 class="font-weight-bold">TZS {{ number_format($recentPayments->sum('amount') ?? 0, 2) }}</h4>
                                <p class="text-secondary mb-0 font-13">This month</p>
                            </div>
                            <div class="widgets-icons bg-gradient-burning text-white"><i class='bx bx-money'></i></div>
                        </div>
                    </div>
                </div>
            </div>
            @endcan
            @can('view receipts')
            <div class="col">
                <div class="card radius-10">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <p class="mb-0">Total Receipts</p>
                                <h4 class="font-weight-bold">TZS {{ number_format($recentReceipts->sum('amount') ?? 0, 2) }}</h4>
                                <p class="text-secondary mb-0 font-13">This month</p>
                            </div>
                            <div class="widgets-icons bg-gradient-lush text-white"><i class='bx bx-receipt'></i></div>
                        </div>
                    </div>
                </div>
            </div>
            @endcan
            <!-- Loan Stats Cards -->
            @can('view loans')
            <div class="col">
                <div class="card radius-10">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <p class="mb-0">Total Loan Amount</p>
                                <h4 class="font-weight-bold">TZS {{ number_format($totalLoanAmount ?? 0, 2) }}</h4>
                            </div>
                            <div class="widgets-icons bg-gradient-blues text-white"><i class='bx bx-wallet'></i></div>
                        </div>
                    </div>
                </div>
            </div>
            @endcan
            @can('view loans')
            <div class="col">
                <div class="card radius-10">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <p class="mb-0">Total Principal</p>
                                <h4 class="font-weight-bold">TZS {{ number_format($totalPrincipal ?? 0, 2) }}</h4>
                                <p class="mb-0">Total Interest: TZS {{ number_format($totalInterest ?? 0, 2) }}</p>
                            </div>
                            <div class="widgets-icons bg-gradient-burning text-white"><i class='bx bx-money'></i></div>
                        </div>
                    </div>
                </div>
            </div>
            @endcan
            @can('view loans')
            <div class="col">
                <div class="card radius-10">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <p class="mb-0">Repaid Principal</p>
                                <h4 class="font-weight-bold">TZS {{ number_format($repaidPrincipal ?? 0, 2) }}</h4>
                                <p class="mb-0">Repaid Interest: TZS {{ number_format($repaidInterest ?? 0, 2) }}</p>
                            </div>
                            <div class="widgets-icons bg-gradient-success text-white"><i class='bx bx-check-circle'></i></div>
                        </div>
                    </div>
                </div>
            </div>
            @endcan
            @can('view loans')
            <div class="col">
                <div class="card radius-10">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <p class="mb-0">Outstanding Total</p>
                                <h4 class="font-weight-bold">TZS {{ number_format(($outstandingPrincipal + $outstandingInterest) ?? 0, 0) }}</h4>
                                <p class="mb-0" style="font-size: 0.75rem;">Outstanding Interest: <b>TZS {{ number_format($outstandingInterestDetailed ?? 0, 0) }}</b></p>
                                <p class="mb-0" style="font-size: 0.75rem;">Accrued Interest: <b>TZS {{ number_format($accruedInterest ?? 0, 0) }}</b></p>
                                <p class="mb-0" style="font-size: 0.75rem;">Not Due Interest: <b>TZS {{ number_format($notDueInterest ?? 0, 0) }}</b></p>
                                <p class="mb-0" style="font-size: 0.75rem;">Paid Interest: <b>TZS {{ number_format($paidInterest ?? 0, 0) }}</b></p>
                                <p class="mb-0" style="font-size: 0.75rem;">Outstanding Principal: <b>TZS {{ number_format($outstandingPrincipal ?? 0, 0) }}</b></p>
                            </div>
                            <div class="widgets-icons bg-gradient-cosmic text-white"><i class='bx bx-hourglass'></i></div>
                        </div>
                    </div>
                </div>
            </div>
            @endcan
        </div>
        <!--end row-->

        @can('view graphs')
        <!-- Loan Product Disbursement Chart -->
        <div class="row">
            <div class="col-5">
                <div class="card radius-10">
                    <div class="card-body">
                        <h5 class="mb-3">Delinquency Loan Buckets (This Year)</h5>
                        <canvas id="delinquencyLoanChart"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-7">
                <div class="card radius-10">
                    <div class="card-body">
                        <h5 class="mb-3">Loan Product Disbursement (This Year)</h5>
                        <canvas id="loanProductChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
        @endcan
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Get current branch filter from URL or form
            const urlParams = new URLSearchParams(window.location.search);
            const branchId = urlParams.get('branch_id') || '';
            
            // Loan Product Disbursement Chart
            fetch('/dashboard/loan-product-disbursement' + (branchId ? '?branch_id=' + branchId : ''))
                .then(response => response.json())
                .then(data => {
                    const ctx = document.getElementById('loanProductChart').getContext('2d');
                    if (!data.products.length || !data.amounts.length || data.amounts.every(a => a == 0)) {
                        document.getElementById('loanProductChart').style.display = 'none';
                        const fallback = document.createElement('div');
                        fallback.style.textAlign = 'center';
                        fallback.style.padding = '40px 0';
                        fallback.style.color = '#888';
                        fallback.innerHTML = '<b>No loan product disbursement data available for this year.</b>';
                        ctx.canvas.parentNode.appendChild(fallback);
                        return;
                    }
                    new Chart(ctx, {
                        type: 'bar',
                        data: {
                            labels: data.products,
                            datasets: [{
                                label: 'Amount Disbursed (TZS)',
                                data: data.amounts,
                                backgroundColor: [
                                    '#8e44ad', '#e74c3c', '#f1c40f', '#27ae60', '#34495e', '#00bfff'
                                ],
                            }]
                        },
                        options: {
                            responsive: true,
                            plugins: {
                                legend: { display: false },
                                title: {
                                    display: true,
                                    text: 'Loan By Product Disbursement (This Year)'
                                }
                            },
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    title: { display: true, text: 'Amount (TZS)' }
                                },
                                x: {
                                    title: { display: true, text: 'Loan Product' }
                                }
                            }
                        }
                    });
                });

            // Delinquency Loan Pie Chart
            fetch('/dashboard/delinquency-loan-buckets' + (branchId ? '?branch_id=' + branchId : ''))
                .then(response => response.json())
                .then(data => {
                    const ctx = document.getElementById('delinquencyLoanChart').getContext('2d');
                    if (!data.labels.length || !data.values.length || data.values.every(v => v == 0)) {
                        document.getElementById('delinquencyLoanChart').style.display = 'none';
                        const fallback = document.createElement('div');
                        fallback.style.textAlign = 'center';
                        fallback.style.padding = '40px 0';
                        fallback.style.color = '#888';
                        fallback.innerHTML = '<b>No delinquency loan data available for this year.</b>';
                        ctx.canvas.parentNode.appendChild(fallback);
                        return;
                    }
                    new Chart(ctx, {
                        type: 'pie',
                        data: {
                            labels: data.labels,
                            datasets: [{
                                label: 'Delinquency Loans',
                                data: data.values,
                                backgroundColor: [
                                    '#e74c3c', '#f1c40f', '#27ae60', '#34495e', '#00bfff', '#8e44ad'
                                ],
                            }]
                        },
                        options: {
                            responsive: true,
                            plugins: {
                                legend: { display: true },
                                title: {
                                    display: true,
                                    text: 'Delinquency Loan Buckets (Percent)'
                                },
                                tooltip: {
                                    callbacks: {
                                        label: function(context) {
                                            const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                            const value = context.parsed;
                                            const percent = total ? ((value / total) * 100).toFixed(1) : 0;
                                            return `${context.label}: ${value} (${percent}%)`;
                                        }
                                    }
                                }
                            }
                        }
                    });
                });
        });

           // Monthly Collections Grouped Bar Chart
            fetch('/dashboard/monthly-collections')
                .then(response => response.json())
                .then(data => {
                    console.log('Monthly Collections Chart Data:', data);
                    const ctx = document.getElementById('monthlyCollectionsChart').getContext('2d');
                    const isEmpty = !data.months || !data.expected || !data.collected || !data.arrears ||
                        data.months.length === 0 ||
                        (data.expected.every(v => v == 0) && data.collected.every(v => v == 0) && data.arrears.every(v => v == 0));
                    if (isEmpty) {
                        document.getElementById('monthlyCollectionsChart').style.display = 'none';
                        const fallback = document.createElement('div');
                        fallback.style.textAlign = 'center';
                        fallback.style.padding = '40px 0';
                        fallback.style.color = '#888';
                        fallback.innerHTML = '<b>No monthly collections data available for this year.</b>';
                        ctx.canvas.parentNode.appendChild(fallback);
                        return;
                    }
                    // Highlight months with no repayments by changing the collected bar color to gray
                    const collectedColors = data.collected.map(v => v == 0 ? '#cccccc' : '#27ae60');
                    new Chart(ctx, {
                        type: 'bar',
                        data: {
                            labels: data.months,
                            datasets: [
                                {
                                    label: 'Expected',
                                    data: data.expected,
                                    backgroundColor: '#f1c40f',
                                    barPercentage: 0.3,
                                    categoryPercentage: 0.6
                                },
                                {
                                    label: 'Collected',
                                    data: data.collected,
                                    backgroundColor: collectedColors,
                                    barPercentage: 0.3,
                                    categoryPercentage: 0.6
                                },
                                {
                                    label: 'Arrears',
                                    data: data.arrears,
                                    backgroundColor: '#e74c3c',
                                    barPercentage: 0.3,
                                    categoryPercentage: 0.6
                                }
                            ]
                        },
                        options: {
                            responsive: true,
                            plugins: {
                                legend: { display: true },
                                title: {
                                    display: true,
                                    text: 'Monthly Expected vs Collected vs Arrears'
                                },
                                tooltip: {
                                    callbacks: {
                                        label: function(context) {
                                            let label = context.dataset.label || '';
                                            let value = context.parsed;
                                            if (label === 'Collected' && value === 0) {
                                                return `${context.label}: No repayments`;
                                            }
                                            return `${context.label}: ${value.toLocaleString()}`;
                                        }
                                    }
                                }
                            },
                            scales: {
                                x: {
                                    stacked: true,
                                    title: { display: true, text: 'Month' }
                                },
                                y: {
                                    stacked: false,
                                    beginAtZero: true,
                                    title: { display: true, text: 'Amount (TZS)' }
                                }
                            },
                            barThickness: 12
                        }
                    });
                });
        </script>
        <!--end row-->
        @can('view graphs')
        <!-- Balance Sheet Overview -->
        <div class="row">
            <div class="col-12 col-lg-8 d-lg-flex align-items-lg-stretch">
                <div class="card radius-10 w-100">
                    <div class="card-body">                
                        <div id="chart3"></div>
                        <div class="mt-4">
                            <div class="card border-0 shadow-sm" style="background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);">
                                <div class="card-header bg-white border-bottom-0 d-flex align-items-center">
                                    <i class="bx bx-bar-chart-alt-2 text-primary me-2 font-20"></i>
                                    <h6 class="mb-0 text-dark">Monthly Collections Overview (This Year)</h6>
                                </div>
                                <div class="card-body pt-3 pb-2">
                                    <canvas id="monthlyCollectionsChart" height="120"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-12 col-lg-4 d-lg-flex align-items-lg-stretch">
                <div class="card radius-10 w-100">
                    <div class="card-header bg-transparent">Account Class Balances</div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped mb-0">
                                <thead>
                                    <tr>
                                        <th>Class</th>
                                        <th>Balance</th>
                                        <th>Accounts</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($balanceSheetData as $item)
                                    <tr>
                                        <td>
                                            <div>
                                                <strong>{{ $item['class_code'] }}</strong>
                                                <br>
                                                <small class="text-muted">{{ $item['class_name'] }}</small>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge {{ $item['balance'] >= 0 ? 'bg-success' : 'bg-danger' }}">
                                                TZS {{ number_format(abs($item['balance']), 2) }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge bg-info">{{ $item['account_count'] }}</span>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="3" class="text-center text-muted">No account data available</td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!--end row-->
        @endcan
        <!-- Recent Activities -->
        @can('view recent activities') 
        <div class="row row-cols-1 row-cols-lg-3">
            <div class="col">
                <div class="card radius-10">
                    <div class="card-header bg-transparent">
                        <h6 class="mb-0"><i class="bx bx-book-open me-2"></i>Recent Journals</h6>
                    </div>
                    <div class="card-body">
                        @forelse($recentJournals as $journal)
                        <div class="d-flex align-items-center mb-3">
                            <div class="widgets-icons bg-light-primary text-primary me-3">
                                <i class="bx bx-book"></i>
                            </div>
                            <div class="flex-grow-1">
                                <h6 class="mb-1">{{ $journal->reference }}</h6>
                                <p class="mb-0 text-muted">{{ Str::limit($journal->description, 30) }}</p>
                                <small class="text-muted">{{ $journal->date ? $journal->date->format('M d, Y') : 'N/A' }}</small>
                            </div>
                        </div>
                        @empty
                        <p class="text-muted text-center">No recent journals</p>
                        @endforelse
                    </div>
                </div>
            </div>
            <div class="col">
                <div class="card radius-10">
                    <div class="card-header bg-transparent">
                        <h6 class="mb-0"><i class="bx bx-money me-2"></i>Recent Payments</h6>
                    </div>
                    <div class="card-body">
                        @forelse($recentPayments as $payment)
                        <div class="d-flex align-items-center mb-3">
                            <div class="widgets-icons bg-light-success text-success me-3">
                                <i class="bx bx-money"></i>
                            </div>
                            <div class="flex-grow-1">
                                <h6 class="mb-1">{{ $payment->reference }}</h6>
                                <p class="mb-0 text-muted">{{ Str::limit($payment->description, 30) }}</p>
                                <small class="text-muted">{{ $payment->date ? $payment->date->format('M d, Y') : 'N/A' }}</small>
                            </div>
                        </div>
                        @empty
                        <p class="text-muted text-center">No recent payments</p>
                        @endforelse
                    </div>
                </div>
            </div>
            <div class="col">
                <div class="card radius-10">
                    <div class="card-header bg-transparent">
                        <h6 class="mb-0"><i class="bx bx-receipt me-2"></i>Recent Receipts</h6>
                    </div>
                    <div class="card-body">
                        @forelse($recentReceipts as $receipt)
                        <div class="d-flex align-items-center mb-3">
                            <div class="widgets-icons bg-light-success text-success me-3">
                                <i class="bx bx-receipt"></i>
                            </div>
                            <div class="flex-grow-1">
                                <h6 class="mb-1">{{ $receipt->reference }}</h6>
                                <p class="mb-0 text-muted">{{ $receipt->description ?? 'N/A' }}</p>
                                <small class="text-muted">{{ $receipt->date ? $receipt->date->format('M d, Y') : 'N/A' }}</small>
                            </div>
                        </div>
                        @empty
                        <p class="text-muted text-center">No recent receipts</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
        @endcan
        <!-- Financial Report Summary -->
        @can('view financial reports')
        <div class="row">
            <div class="col-12">
                <div class="card radius-10 border-0 shadow-sm">
                    <div class="card-header bg-transparent border-0">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <h5 class="mb-0 text-dark"><i class="bx bx-bar-chart me-2"></i>FINANCIAL REPORT SUMMARY</h5>
                                <small class="text-muted">Comprehensive financial overview as of {{ date('d-m-Y') }}</small>
                            </div>
                        </div>
                    </div>
                    <div class="card-body p-4">
                        <div class="row">
                            <!-- Balance Sheet Section -->
                            <div class="col-md-6">
                                <div class="financial-section">
                                    <div class="section-header bg-light p-3 rounded-top">
                                        <h4 class="mb-0 text-dark"><i class="bx bx-balance me-2"></i>BALANCE SHEET</h4>
                                        <small class="text-muted">As of {{ date('d-m-Y') }} vs {{ $previousYearData['year'] }}</small>
                                    </div>

                                    <!-- Assets Section -->
                                    <div class="section-content border rounded-bottom">
                                        <div class="section-title bg-light p-2 border-bottom">
                                            <h6 class="mb-0 text-dark"><i class="bx bx-trending-up me-1"></i>ASSETS</h6>
                                        </div>
                                        <div class="table-responsive">
                                            <table class="table table-striped">
                                                <thead class="table-light">
                                                    <tr>
                                                        <th>Account</th>
                                                        <th class="text-end">Current Year</th>
                                                        <th class="text-end">Previous Year</th>
                                                        <th class="text-end">Change</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @php $sumAsset = 0; $sumAssetPrev = 0; @endphp
                                                    @foreach($financialReportData['chartAccountsAssets'] as $groupName => $accounts)
                                                    @php $groupTotal = collect($accounts)->sum(fn($account) => $account['sum'] ?? 0); @endphp
                                                    @if($groupTotal != 0)
                                                    <tr class="table-light">
                                                        <td colspan="4" class="fw-bold text-dark">{{ $groupName }}</td>
                                                    </tr>
                                                    @foreach($accounts as $chartAccountAsset)
                                                    @if($chartAccountAsset['sum'] != 0)
                                                    @php 
                                                        $sumAsset += $chartAccountAsset['sum'] ?? 0;
                                                        $prevYearAccount = collect($previousYearData['chartAccountsAssets'][$groupName] ?? [])->firstWhere('account_id', $chartAccountAsset['account_id']);
                                                        $prevYearAmount = $prevYearAccount['sum'] ?? 0;
                                                        $sumAssetPrev += $prevYearAmount;
                                                        $change = ($chartAccountAsset['sum'] ?? 0) - $prevYearAmount;
                                                    @endphp
                                                    <tr class="account-row">
                                                        <td>
                                                            <a href="{{ route('accounting.transactions.doubleEntries', Hashids::encode($chartAccountAsset['account_id'])) }}"
                                                                class="text-decoration-none text-dark fw-medium">
                                                                <i class="bx bx-chevron-right me-1 text-dark"></i>
                                                                {{ $chartAccountAsset['account'] }}
                                                            </a>
                                                        </td>
                                                        <td class="text-end">
                                                            <a href="{{ route('accounting.transactions.doubleEntries', Hashids::encode($chartAccountAsset['account_id'])) }}"
                                                                class="text-decoration-none fw-bold text-dark">
                                                                {{ number_format($chartAccountAsset['sum'] ?? 0,2) }}
                                                            </a>
                                                        </td>
                                                        <td class="text-end text-dark">
                                                            {{ number_format($prevYearAmount,2) }}
                                                        </td>
                                                        <td class="text-end">
                                                                {{ $change >= 0 ? '+' : '' }}{{ number_format($change,2) }}
                                                        </td>
                                                    </tr>
                                                    @endif
                                                    @endforeach
                                                    @endif
                                                    @endforeach
                                                    <tr class="table-secondary fw-bold">
                                                        <td>TOTAL ASSETS</td>
                                                        <td class="text-end">{{ number_format($sumAsset,2) }}</td>
                                                        <td class="text-end">{{ number_format($sumAssetPrev,2) }}</td>
                                                        <td class="text-end">
                                                            @php $assetChange = $sumAsset - $sumAssetPrev; @endphp
                                                                {{ $assetChange >= 0 ? '+' : '' }}{{ number_format($assetChange,2) }}
                                                        </td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>

                                        <!-- Equity Section -->
                                        <div class="section-title bg-light p-2 border-bottom mt-3">
                                            <h6 class="mb-0 text-dark"><i class="bx bx-user me-1"></i>EQUITY</h6>
                                        </div>
                                        <div class="table-responsive">
                                            <table class="table table-sm mb-0">
                                                <thead class="table-light">
                                                    <tr>
                                                        <th>Account</th>
                                                        <th class="text-end">Current Year</th>
                                                        <th class="text-end">Previous Year</th>
                                                        <th class="text-end">Change</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @php $sumEquity = 0; $sumEquityPrev = 0; @endphp
                                                    @foreach($financialReportData['chartAccountsEquitys'] as $groupName => $accounts)
                                                    @php $groupTotal = collect($accounts)->sum(fn($account) => $account['sum'] ?? 0); @endphp
                                                    @if($groupTotal != 0)
                                                    <tr class="table-light">
                                                        <td colspan="4" class="fw-bold text-dark">{{ $groupName }}</td>
                                                    </tr>
                                                    @foreach($accounts as $chartAccountEquity)
                                                    @if($chartAccountEquity['sum'] != 0)
                                                    @php 
                                                        $sumEquity += abs($chartAccountEquity['sum'] ?? 0);
                                                        $prevYearAccount = collect($previousYearData['chartAccountsEquitys'][$groupName] ?? [])->firstWhere('account_id', $chartAccountEquity['account_id']);
                                                        $prevYearAmount = abs($prevYearAccount['sum'] ?? 0);
                                                        $sumEquityPrev += $prevYearAmount;
                                                        $change = abs($chartAccountEquity['sum'] ?? 0) - $prevYearAmount;
                                                    @endphp
                                                    <tr class="account-row">
                                                        <td>
                                                            <a href="{{ route('accounting.transactions.doubleEntries', Hashids::encode($chartAccountEquity['account_id'])) }}"
                                                                class="text-decoration-none text-dark fw-medium">
                                                                <i class="bx bx-chevron-right me-1 text-dark"></i>
                                                                {{ $chartAccountEquity['account'] }}
                                                            </a>
                                                        </td>
                                                        <td class="text-end">
                                                            <a href="{{ route('accounting.transactions.doubleEntries', Hashids::encode($chartAccountEquity['account_id'])) }}"
                                                                class="text-decoration-none fw-bold text-dark">
                                                                {{ number_format(abs($chartAccountEquity['sum'] ?? 0),2) }}
                                                            </a>
                                                        </td>
                                                        <td class="text-end text-dark">
                                                            {{ number_format($prevYearAmount,2) }}
                                                        </td>
                                                        <td class="text-end">
                                                                {{ $change >= 0 ? '+' : '' }}{{ number_format($change,2) }}
                                                        </td>
                                                    </tr>
                                                    @endif
                                                    @endforeach
                                                    @endif
                                                    @endforeach
                                                    <tr class="table-info">
                                                        <td>Profit And Loss</td>
                                                        <td class="text-end fw-bold">{{ number_format($financialReportData['profitLoss'],2) }}</td>
                                                        <td class="text-end text-dark">{{ number_format($previousYearData['profitLoss'],2) }}</td>
                                                        <td class="text-end">
                                                            @php $profitChange = $financialReportData['profitLoss'] - $previousYearData['profitLoss']; @endphp
                                                                {{ $profitChange >= 0 ? '+' : '' }}{{ number_format($profitChange,2) }}
                                                        </td>
                                                    </tr>
                                                    <tr class="table-secondary fw-bold">
                                                        <td>TOTAL EQUITY</td>
                                                        <td class="text-end">{{ number_format($sumEquity + $financialReportData['profitLoss'],2) }}</td>
                                                        <td class="text-end">{{ number_format($sumEquityPrev + $previousYearData['profitLoss'],2) }}</td>
                                                        <td class="text-end">
                                                            @php $equityChange = ($sumEquity + $financialReportData['profitLoss']) - ($sumEquityPrev + $previousYearData['profitLoss']); @endphp
                                                                {{ $equityChange >= 0 ? '+' : '' }}{{ number_format($equityChange,2) }}
                                                        </td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>

                                        <!-- Liabilities Section -->
                                        <div class="section-title bg-light p-2 border-bottom mt-3">
                                            <h6 class="mb-0 text-dark"><i class="bx bx-trending-down me-1"></i>LIABILITIES</h6>
                                        </div>
                                        <div class="table-responsive">
                                            <table class="table table-sm mb-0">
                                                <thead class="table-light">
                                                    <tr>
                                                        <th>Account</th>
                                                        <th class="text-end">Current Year</th>
                                                        <th class="text-end">Previous Year</th>
                                                        <th class="text-end">Change</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @php $sumLiability = 0; $sumLiabilityPrev = 0; @endphp
                                                    @foreach($financialReportData['chartAccountsLiabilities'] as $groupName => $accounts)
                                                    @php $groupTotal = collect($accounts)->sum(fn($account) => $account['sum'] ?? 0); @endphp
                                                    @if($groupTotal != 0)
                                                    <tr class="table-secondary">
                                                        <td colspan="4" class="fw-bold text-dark">{{ $groupName }}</td>
                                                    </tr>
                                                    @foreach($accounts as $chartAccountLiability)
                                                    @if($chartAccountLiability['sum'] != 0)
                                                    @php 
                                                        $sumLiability += abs($chartAccountLiability['sum'] ?? 0);
                                                        $prevYearAccount = collect($previousYearData['chartAccountsLiabilities'][$groupName] ?? [])->firstWhere('account_id', $chartAccountLiability['account_id']);
                                                        $prevYearAmount = abs($prevYearAccount['sum'] ?? 0);
                                                        $sumLiabilityPrev += $prevYearAmount;
                                                        $change = abs($chartAccountLiability['sum'] ?? 0) - $prevYearAmount;
                                                    @endphp
                                                    <tr class="account-row">
                                                        <td>
                                                            <a href="{{ route('accounting.transactions.doubleEntries', Hashids::encode($chartAccountLiability['account_id'])) }}"
                                                                class="text-decoration-none text-dark fw-medium">
                                                                <i class="bx bx-chevron-right me-1 text-dark"></i>
                                                                {{ $chartAccountLiability['account'] }}
                                                            </a>
                                                        </td>
                                                        <td class="text-end">
                                                            <a href="{{ route('accounting.transactions.doubleEntries', Hashids::encode($chartAccountLiability['account_id'])) }}"
                                                                class="text-decoration-none fw-bold text-dark">
                                                                {{ number_format(abs($chartAccountLiability['sum'] ?? 0),2) }}
                                                            </a>
                                                        </td>
                                                        <td class="text-end text-dark">
                                                            {{ number_format($prevYearAmount,2) }}
                                                        </td>
                                                        <td class="text-end">
                                                                {{ $change >= 0 ? '+' : '' }}{{ number_format($change,2) }}
                                                        </td>
                                                    </tr>
                                                    @endif
                                                    @endforeach
                                                    @endif
                                                    @endforeach
                                                    <tr class="fw-bold">
                                                        <td>TOTAL LIABILITIES</td>
                                                        <td class="text-end">{{ number_format($sumLiability,2) }}</td>
                                                        <td class="text-end">{{ number_format($sumLiabilityPrev,2) }}</td>
                                                        <td class="text-end">
                                                            @php $liabilityChange = $sumLiability - $sumLiabilityPrev; @endphp
                                                                {{ $liabilityChange >= 0 ? '+' : '' }}{{ number_format($liabilityChange,2) }}
                                                        </td>
                                                    </tr>
                                                    <tr class="table-secondary fw-bold">
                                                        <td>TOTAL EQUITY & LIABILITY</td>
                                                        <td class="text-end">{{ number_format($sumLiability + $sumEquity + $financialReportData['profitLoss'],2) }}</td>
                                                        <td class="text-end">{{ number_format($sumLiabilityPrev + $sumEquityPrev + $previousYearData['profitLoss'],2) }}</td>
                                                        <td class="text-end">
                                                            @php $totalChange = ($sumLiability + $sumEquity + $financialReportData['profitLoss']) - ($sumLiabilityPrev + $sumEquityPrev + $previousYearData['profitLoss']); @endphp

                                                                {{ $totalChange >= 0 ? '+' : '' }}{{ number_format($totalChange,2) }}

                                                        </td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Profit & Loss Section -->
                            <div class="col-md-6">
                                <div class="financial-section">
                                    <div class="section-header bg-light p-3 rounded-top">
                                        <h4 class="mb-0 text-dark"><i class="bx bx-line-chart me-2"></i>PROFIT & LOSS STATEMENT</h4>
                                        <small class="text-muted">From 01-01-{{date('Y')}} to {{ date('d-m-Y') }} vs {{ $previousYearData['year'] }}</small>
                                    </div>

                                    <div class="section-content border rounded-bottom">
                                        <!-- Revenue Section -->
                                        <div class="section-title bg-light p-2 border-bottom">
                                            <h6 class="mb-0 text-dark"><i class="bx bx-trending-up me-1"></i>INCOME</h6>
                                        </div>
                                        <div class="table-responsive">
                                            <table class="table table-striped">
                                                <thead class="table-secondary">
                                                    <tr>
                                                        <th>Account</th>
                                                        <th class="text-end">Current Year</th>
                                                        <th class="text-end">Previous Year</th>
                                                        <th class="text-end">Change</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @php $sumRevenue = 0; $sumRevenuePrev = 0; @endphp
                                                    @foreach($financialReportData['chartAccountsRevenues'] as $groupName => $accounts)
                                                    @php $groupTotal = collect($accounts)->sum('sum'); @endphp
                                                    @if($groupTotal != 0)
                                                    <tr>
                                                        <td colspan="4" class="fw-bold text-dark">{{ $groupName }}</td>
                                                    </tr>
                                                    @foreach($accounts as $chartAccountRevenue)
                                                    @if($chartAccountRevenue['sum'] != 0)
                                                    @php 
                                                        $sumRevenue += $chartAccountRevenue['sum'];
                                                        $prevYearAccount = collect($previousYearData['chartAccountsRevenues'][$groupName] ?? [])->firstWhere('account_id', $chartAccountRevenue['account_id']);
                                                        $prevYearAmount = $prevYearAccount['sum'] ?? 0;
                                                        $sumRevenuePrev += $prevYearAmount;
                                                        $change = $chartAccountRevenue['sum'] - $prevYearAmount;
                                                    @endphp
                                                    <tr class="account-row">
                                                        <td>
                                                            <a href="{{ route('accounting.transactions.doubleEntries', Hashids::encode($chartAccountRevenue['account_id'])) }}"
                                                                class="text-decoration-none text-dark fw-medium">
                                                                <i class="bx bx-chevron-right me-1 text-dark"></i>
                                                                {{ $chartAccountRevenue['account'] }}
                                                            </a>
                                                        </td>
                                                        <td class="text-end">
                                                            <a href="{{ route('accounting.transactions.doubleEntries', Hashids::encode($chartAccountRevenue['account_id'])) }}"
                                                                class="text-decoration-none fw-bold text-dark">
                                                                {{ number_format($chartAccountRevenue['sum'],2) }}
                                                            </a>
                                                        </td>
                                                        <td class="text-end text-dark">
                                                            {{ number_format($prevYearAmount,2) }}
                                                        </td>
                                                        <td class="text-end">

                                                                {{ $change >= 0 ? '+' : '' }}{{ number_format($change,2) }}
                                                           
                                                        </td>
                                                    </tr>
                                                    @endif
                                                    @endforeach
                                                    @endif
                                                    @endforeach
                                                    <tr class="fw-bold">
                                                        <td>TOTAL INCOME</td>
                                                        <td class="text-end">{{ number_format($sumRevenue,2) }}</td>
                                                        <td class="text-end">{{ number_format($sumRevenuePrev,2) }}</td>
                                                        <td class="text-end">
                                                            @php $revenueChange = $sumRevenue - $sumRevenuePrev; @endphp

                                                                {{ $revenueChange >= 0 ? '+' : '' }}{{ number_format($revenueChange,2) }}

                                                        </td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>

                                        <!-- Expenses Section -->
                                        <div class="section-title bg-light p-2 border-bottom mt-3">
                                            <h6 class="mb-0 text-dark"><i class="bx bx-trending-down me-1"></i>EXPENSES</h6>
                                        </div>
                                        <div class="table-responsive">
                                            <table class="table table-striped">
                                                <thead>
                                                    <tr>
                                                        <th>Account</th>
                                                        <th class="text-end">Current Year</th>
                                                        <th class="text-end">Previous Year</th>
                                                        <th class="text-end">Change</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @php $sumExpense = 0; $sumExpensePrev = 0; @endphp
                                                    @foreach($financialReportData['chartAccountsExpense'] as $groupName => $accounts)
                                                    @php $groupTotal = collect($accounts)->sum('sum'); @endphp
                                                    @if($groupTotal != 0)
                                                    <tr class="table-light">
                                                        <td colspan="4" class="fw-bold text-dark">{{ $groupName }}</td>
                                                    </tr>
                                                    @foreach($accounts as $chartAccountExpense)
                                                    @if($chartAccountExpense['sum'] != 0)
                                                    @php 
                                                        $sumExpense += abs($chartAccountExpense['sum']);
                                                        $prevYearAccount = collect($previousYearData['chartAccountsExpense'][$groupName] ?? [])->firstWhere('account_id', $chartAccountExpense['account_id']);
                                                        $prevYearAmount = abs($prevYearAccount['sum'] ?? 0);
                                                        $sumExpensePrev += $prevYearAmount;
                                                        $change = abs($chartAccountExpense['sum']) - $prevYearAmount;
                                                    @endphp
                                                    <tr class="account-row">
                                                        <td>
                                                            <a href="{{ route('accounting.transactions.doubleEntries', Hashids::encode($chartAccountExpense['account_id'])) }}"
                                                                class="text-decoration-none text-dark fw-medium">
                                                                <i class="bx bx-chevron-right me-1 text-dark"></i>
                                                                {{ $chartAccountExpense['account'] }}
                                                            </a>
                                                        </td>
                                                        <td class="text-end">
                                                            <a href="{{ route('accounting.transactions.doubleEntries', Hashids::encode($chartAccountExpense['account_id'])) }}"
                                                                class="text-decoration-none fw-bold text-dark">
                                                                {{ number_format(abs($chartAccountExpense['sum']),2) }}
                                                            </a>
                                                        </td>
                                                        <td class="text-end text-dark">
                                                            {{ number_format($prevYearAmount,2) }}
                                                        </td>
                                                        <td class="text-end">

                                                                {{ $change >= 0 ? '+' : '' }}{{ number_format($change,2) }}
                                                        
                                                        </td>
                                                    </tr>
                                                    @endif
                                                    @endforeach
                                                    @endif
                                                    @endforeach
                                                    <tr class="table-secondary fw-bold">
                                                        <td>TOTAL EXPENSES</td>
                                                        <td class="text-end">{{ number_format($sumExpense,2) }}</td>
                                                        <td class="text-end">{{ number_format($sumExpensePrev,2) }}</td>
                                                        <td class="text-end">
                                                            @php $expenseChange = $sumExpense - $sumExpensePrev; @endphp

                                                                {{ $expenseChange >= 0 ? '+' : '' }}{{ number_format($expenseChange,2) }}

                                                        </td>
                                                    </tr>
                                                    <tr class="table-secondary">
                                                        <td>NET PROFIT/LOSS</td>
                                                        <td class="text-end">{{ number_format($sumRevenue - $sumExpense,2) }}</td>
                                                        <td class="text-end">{{ number_format($sumRevenuePrev - $sumExpensePrev,2) }}</td>
                                                        <td class="text-end">
                                                            @php $netProfitChange = ($sumRevenue - $sumExpense) - ($sumRevenuePrev - $sumExpensePrev); @endphp

                                                                {{ $netProfitChange >= 0 ? '+' : '' }}{{ number_format($netProfitChange,2) }}

                                                        </td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endcan

        <!-- Send Bulk SMS Button
        <div class="d-flex justify-content-end mb-3">
            <button type="button" class="btn btn-info" data-bs-toggle="modal" data-bs-target="#bulkSmsModal">
                <i class="bx bx-envelope"></i> Send Bulk SMS
            </button>
        </div> -->

        <!-- Bulk SMS Modal -->
        <div class="modal fade" id="bulkSmsModal" tabindex="-1" aria-labelledby="bulkSmsModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="bulkSmsModalLabel">
                            <i class="bx bx-envelope me-2"></i>Send Bulk SMS
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form id="bulkSmsForm" action="{{ route('sms.bulk') }}" method="POST">
                        @csrf
                        <div class="modal-body">
                            <div class="mb-3">
                                <label for="branch_id" class="form-label">Select Branch</label>
                                <select class="form-select" id="branch_id" name="branch_id" required>
                                    <option value="all">All Branches</option>
                                    @foreach(App\Models\Branch::all() as $branch)
                                        <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="message_title" class="form-label fw-bold">Message Title</label>
                                <select class="form-select" id="message_title" name="message_title" required>
                                    <option value="">Select a title...</option>
                                    <option value="Payment Reminder">Payment Reminder</option>
                                    <option value="Loan Approved">Loan Approved</option>
                                    <option value="Loan Disbursed">Loan Disbursed</option>
                                    <option value="Custom">Custom Title</option>
                                </select>
                                <div class="form-text">Choose a title for this SMS batch or select Custom to enter your own.</div>
                            </div>
                            <div class="mb-3">
                                <label for="bulk_message_content" class="form-label">Message Content</label>
                                <textarea class="form-control" id="bulk_message_content" name="bulk_message_content" rows="4" maxlength="500" required></textarea>
                                <div class="form-text"><span id="bulk_character_count">0</span>/500 characters</div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                <i class="bx bx-x me-1"></i>Cancel
                            </button>
                            <button type="submit" class="btn btn-primary" id="sendBulkSmsBtn">
                                <i class="bx bx-send me-1"></i>Send Bulk SMS
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        <script>
        // Character counter for bulk SMS
        function updateBulkCharacterCount() {
            const bulkMessageContent = document.getElementById('bulk_message_content');
            const bulkCharacterCount = document.getElementById('bulk_character_count');
            const count = bulkMessageContent.value.length;
            bulkCharacterCount.textContent = count;
            if (count > 500) {
                bulkCharacterCount.style.color = 'red';
            } else if (count > 450) {
                bulkCharacterCount.style.color = 'orange';
            } else {
                bulkCharacterCount.style.color = 'green';
            }
        }
        document.getElementById('bulk_message_content').addEventListener('input', updateBulkCharacterCount);

        // Bulk SMS form submission
        const bulkSmsForm = document.getElementById('bulkSmsForm');
        bulkSmsForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const sendBtn = document.getElementById('sendBulkSmsBtn');
            const originalText = sendBtn.innerHTML;
            const modal = document.getElementById('bulkSmsModal');
            const formElements = modal.querySelectorAll('input, textarea, select, button');
            const closeBtn = modal.querySelector('.btn-close');
            // Show loading state and disable all form elements
            sendBtn.innerHTML = '<i class="bx bx-loader-alt bx-spin me-1"></i>Sending...';
            sendBtn.disabled = true;
            formElements.forEach(element => { element.disabled = true; });
            if (closeBtn) closeBtn.disabled = true;
            const modalBody = modal.querySelector('.modal-body');
            modalBody.style.opacity = '0.7';
            // Submit the form via AJAX
            fetch(this.action, {
                method: 'POST',
                body: new FormData(this),
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(response => response.json())
            .then(data => {
                let responseMsg = '';
                if (typeof data.response === 'string') {
                    try {
                        const parsed = JSON.parse(data.response);
                        responseMsg = parsed.message || data.message || '';
                    } catch (e) {
                        responseMsg = data.response || data.message || '';
                    }
                } else if (typeof data.response === 'object' && data.response !== null) {
                    responseMsg = data.response.message || data.message || '';
                } else {
                    responseMsg = data.message || '';
                }
                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Bulk SMS Sent!',
                        html: `<div><b>${responseMsg}</b></div>`,
                        confirmButtonColor: '#28a745',
                        timer: 3000,
                        timerProgressBar: true,
                        showConfirmButton: true
                    });
                    bulkSmsForm.reset();
                    updateBulkCharacterCount();
                    const modalInstance = bootstrap.Modal.getInstance(modal);
                    modalInstance.hide();
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Failed to Send Bulk SMS',
                        text: responseMsg || 'Unknown error occurred',
                        confirmButtonColor: '#dc3545',
                        footer: 'Please try again or contact support if the problem persists.'
                    });
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Connection Error',
                    text: 'Failed to send bulk SMS due to connection issues.',
                    confirmButtonColor: '#dc3545',
                    footer: 'Please check your internet connection and try again.'
                });
            })
            .finally(() => {
                sendBtn.innerHTML = originalText;
                sendBtn.disabled = false;
                formElements.forEach(element => { element.disabled = false; });
                if (closeBtn) closeBtn.disabled = false;
                modalBody.style.opacity = '1';
            });
        });
        </script>
        @endpush
    </div>
</div>
@endcan
<!--end page wrapper -->
<!--start overlay-->
<div class="overlay toggle-icon"></div>
<!--end overlay-->
<!--Start Back To Top Button--> <a href="javaScript:;" class="back-to-top"><i class='bx bxs-up-arrow-alt'></i></a>
<!--End Back To Top Button-->
<footer class="page-footer">
    <p class="mb-0">Copyright © 2021. All right reserved.</p>
</footer>
@endsection