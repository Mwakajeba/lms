@extends('layouts.main')

@push('styles')
<style>
    .no-print {
        print: none;
    }

    .summary-card {
        transition: transform 0.2s ease;
        height: 100%;
    }

    .summary-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    }

    .summary-card .card-body {
        padding: 1.5rem;
    }

    .summary-icon {
        width: 50px;
        height: 50px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        color: white;
    }

    .icon-primary { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
    .icon-success { background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); }
    .icon-warning { background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); }
    .icon-info { background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); }

    .table-responsive {
        max-height: 70vh;
        overflow-y: auto;
    }

    .table th {
        position: sticky;
        top: 0;
        background-color: #f8f9fa;
        z-index: 10;
        border-bottom: 2px solid #dee2e6;
    }

    .table tfoot {
        position: sticky;
        bottom: 0;
        z-index: 10;
    }

    @media print {
        .no-print {
            display: none !important;
        }
        
        .table-responsive {
            max-height: none;
            overflow: visible;
        }
    }
</style>
@endpush

@section('title', 'Expected vs Collected Report')

@push('styles')
<style>
    .summary-card {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-radius: 10px;
        padding: 20px;
        margin-bottom: 20px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    }
    
    .summary-card h3 {
        font-size: 14px;
        margin-bottom: 10px;
        opacity: 0.9;
    }
    
    .summary-card .value {
        font-size: 24px;
        font-weight: bold;
        margin: 0;
    }
    
    .filter-card {
        background: white;
        border-radius: 10px;
        padding: 20px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        margin-bottom: 20px;
    }
    
    .status-badge {
        padding: 4px 8px;
        border-radius: 4px;
        font-size: 11px;
        font-weight: bold;
    }
    
    .status-excellent { background-color: #d1edff; color: #0066cc; }
    .status-good { background-color: #d4edda; color: #155724; }
    .status-fair { background-color: #fff3cd; color: #856404; }
    .status-poor { background-color: #f8d7da; color: #721c24; }
    .status-critical { background-color: #f5c6cb; color: #721c24; }
    
    .variance-positive { color: #28a745; font-weight: bold; }
    .variance-negative { color: #dc3545; font-weight: bold; }
    .variance-zero { color: #6c757d; }
    
    .table-responsive {
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }
    
    .table th {
        background-color: #f8f9fa;
        border-top: none;
        font-weight: 600;
        font-size: 12px;
    }
    
    .table td {
        font-size: 12px;
        vertical-align: middle;
    }
    
    .btn-export {
        margin-left: 5px;
    }
</style>
@endpush

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'Loans Reports', 'url' => route('reports.loans'), 'icon' => 'bx bx-credit-card'],
            ['label' => 'Expected vs Collected Report', 'url' => '#', 'icon' => 'bx bx-bar-chart-alt-2']
        ]" />
        
        <h6 class="mb-0 text-uppercase">Expected vs Collected Report</h6>
        <hr />

        <!-- Summary Cards -->
        @if(!empty($reportData))
        <div class="row">
            <div class="col-xl-3 col-md-6">
                <div class="card radius-10 border-start border-0 border-4 border-info">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div>
                                <p class="mb-0 text-secondary">Total Expected</p>
                                <h4 class="my-1 text-info" id="totalExpected">TZS {{ number_format(array_sum(array_column($reportData, 'expected_total')), 2) }}</h4>
                            </div>
                            <div class="widgets-icons-2 rounded-circle bg-gradient-blues text-white ms-auto">
                                <i class='bx bxs-wallet'></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="card radius-10 border-start border-0 border-4 border-success">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div>
                                <p class="mb-0 text-secondary">Total Collected</p>
                                <h4 class="my-1 text-success" id="totalCollected">TZS {{ number_format(array_sum(array_column($reportData, 'collected_total')), 2) }}</h4>
                            </div>
                            <div class="widgets-icons-2 rounded-circle bg-gradient-greens text-white ms-auto">
                                <i class='bx bxs-credit-card'></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="card radius-10 border-start border-0 border-4 border-danger">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div>
                                <p class="mb-0 text-secondary">Variance</p>
                                <h4 class="my-1 text-danger" id="totalVariance">TZS {{ number_format(array_sum(array_column($reportData, 'variance')), 2) }}</h4>
                            </div>
                            <div class="widgets-icons-2 rounded-circle bg-gradient-reds text-white ms-auto">
                                <i class='bx bx-trending-down'></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="card radius-10 border-start border-0 border-4 border-warning">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div>
                                <p class="mb-0 text-secondary">Collection Rate</p>
                                <h4 class="my-1 text-warning" id="avgCollectionRate">
                                    {{ array_sum(array_column($reportData, 'expected_total')) > 0 ? 
                                       number_format((array_sum(array_column($reportData, 'collected_total')) / array_sum(array_column($reportData, 'expected_total'))) * 100, 1) : '0' }}%
                                </h4>
                            </div>
                            <div class="widgets-icons-2 rounded-circle bg-gradient-oranges text-white ms-auto">
                                <i class='bx bx-bar-chart-alt-2'></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif

        <!-- Filter Section -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Filter Report</h5>
                        <form method="GET" action="{{ route('accounting.loans.reports.expected_vs_collected') }}" id="filterForm">
                            <div class="row g-3">
                                <div class="col-md-2">
                                    <label for="start_date" class="form-label">Start Date</label>
                                    <input type="date" class="form-control" id="start_date" name="start_date" 
                                           value="{{ $startDate }}" required>
                                </div>
                                <div class="col-md-2">
                                    <label for="end_date" class="form-label">End Date</label>
                                    <input type="date" class="form-control" id="end_date" name="end_date" 
                                           value="{{ $endDate }}" required>
                                </div>
                                <div class="col-md-2">
                                    <label for="branch_id" class="form-label">Branch</label>
                                    <select class="form-select" id="branch_id" name="branch_id">
                                        @if(($branches->count() ?? 0) > 1)
                                            <option value="all" {{ $branchId === 'all' ? 'selected' : '' }}>All My Branches</option>
                                        @endif
                                        @foreach($branches as $branch)
                                            <option value="{{ $branch->id }}" {{ $branchId == $branch->id ? 'selected' : '' }}>
                                                {{ $branch->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <label for="group_id" class="form-label">Group</label>
                                    <select class="form-select" id="group_id" name="group_id">
                                        <option value="">All Groups</option>
                                        @foreach($groups as $group)
                                            <option value="{{ $group->id }}" {{ $groupId == $group->id ? 'selected' : '' }}>
                                                {{ $group->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <label for="loan_officer_id" class="form-label">Loan Officer</label>
                                    <select class="form-select" id="loan_officer_id" name="loan_officer_id">
                                        <option value="">All Officers</option>
                                        @foreach($loanOfficers as $officer)
                                            <option value="{{ $officer->id }}" {{ $loanOfficerId == $officer->id ? 'selected' : '' }}>
                                                {{ $officer->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label">&nbsp;</label>
                                    <div class="d-grid gap-2">
                                        <button type="submit" class="btn btn-primary" id="filterBtn">
                                            <i class="bx bx-search-alt me-1"></i> Filter
                                        </button>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Export Buttons -->
                            @if(!empty($reportData))
                            <div class="row mt-3">
                                <div class="col-12">
                                    <div class="d-flex justify-content-end gap-2">
                                        <button type="button" class="btn btn-outline-success" onclick="exportReport('excel')">
                                            <i class="bx bx-download me-1"></i> Export Excel
                                        </button>
                                        <button type="button" class="btn btn-outline-danger" onclick="exportReport('pdf')">
                                            <i class="bx bx-download me-1"></i> Export PDF
                                        </button>
                                        <button type="button" class="btn btn-outline-secondary" onclick="resetFilters()">
                                            <i class="bx bx-refresh me-1"></i> Reset
                                        </button>
                                    </div>
                                </div>
                            </div>
                            @endif
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Data Table -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="card-title mb-0">Expected vs Collected Data</h5>
                            @if(!empty($reportData))
                                <span class="badge bg-primary">{{ count($reportData) }} Records</span>
                            @endif
                        </div>
                    </div>
                    <div class="card-body">
                        @if(!empty($reportData))
                            <div class="table-responsive">
                                <table class="table table-striped table-bordered" id="expectedVsCollectedTable">
                                    <thead class="table-light">
                                        <tr>
                                            <th style="width: 3%;">#</th>
                                            <th style="width: 10%;">Customer</th>
                                            <th style="width: 6%;">Customer No</th>
                                            <th style="width: 7%;">Phone</th>
                                            <th style="width: 6%;">Loan No</th>
                                            <th style="width: 7%;">Loan Amount</th>
                                            <th style="width: 6%;">Branch</th>
                                            <th style="width: 6%;">Group</th>
                                            <th style="width: 7%;">Officer</th>
                                            <th style="width: 8%;">Expected Total</th>
                                            <th style="width: 8%;">Collected Total</th>
                                            <th style="width: 7%;">Variance</th>
                                            <th style="width: 6%;">Rate</th>
                                            <th style="width: 6%;">Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($reportData as $index => $row)
                                            <tr>
                                                <td class="text-center">{{ $index + 1 }}</td>
                                                <td>{{ $row['customer'] }}</td>
                                                <td class="text-center">{{ $row['customer_no'] }}</td>
                                                <td class="text-center">{{ $row['phone'] }}</td>
                                                <td class="text-center">{{ $row['loan_no'] }}</td>
                                                <td class="text-end">{{ number_format($row['loan_amount'], 0) }}</td>
                                                <td>{{ $row['branch'] }}</td>
                                                <td>{{ $row['group'] }}</td>
                                                <td>{{ $row['loan_officer'] }}</td>
                                                <td class="text-end">{{ number_format($row['expected_total'], 2) }}</td>
                                                <td class="text-end">{{ number_format($row['collected_total'], 2) }}</td>
                                                <td class="text-end 
                                                    @if($row['variance'] > 0) text-success fw-bold 
                                                    @elseif($row['variance'] < 0) text-danger fw-bold 
                                                    @else text-muted @endif">
                                                    {{ number_format($row['variance'], 2) }}
                                                </td>
                                                <td class="text-center">{{ $row['collection_rate'] }}%</td>
                                                <td class="text-center">
                                                    <span class="badge 
                                                        @if($row['collection_status'] == 'Excellent') bg-primary
                                                        @elseif($row['collection_status'] == 'Good') bg-success
                                                        @elseif($row['collection_status'] == 'Fair') bg-warning
                                                        @elseif($row['collection_status'] == 'Poor') bg-danger
                                                        @else bg-secondary
                                                        @endif">
                                                        {{ $row['collection_status'] }}
                                                    </span>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                    <tfoot class="table-dark">
                                        <tr>
                                            <td colspan="9" class="text-center fw-bold">TOTALS</td>
                                            <td class="text-end fw-bold">{{ number_format(array_sum(array_column($reportData, 'expected_total')), 2) }}</td>
                                            <td class="text-end fw-bold">{{ number_format(array_sum(array_column($reportData, 'collected_total')), 2) }}</td>
                                            <td class="text-end fw-bold">{{ number_format(array_sum(array_column($reportData, 'variance')), 2) }}</td>
                                            <td class="text-center fw-bold">
                                                {{ array_sum(array_column($reportData, 'expected_total')) > 0 ? 
                                                   number_format((array_sum(array_column($reportData, 'collected_total')) / array_sum(array_column($reportData, 'expected_total'))) * 100, 1) : '0' }}%
                                            </td>
                                            <td class="text-center fw-bold">{{ count($reportData) }} Loans</td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        @else
                            <div class="text-center py-4">
                                <div class="my-3">
                                    <i class="bx bx-search font-size-50 text-muted"></i>
                                </div>
                                <h5 class="text-muted">No Data Found</h5>
                                <p class="text-muted mb-0">Please select filters and click "Filter" to generate the report.</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function exportReport(format) {
        // Check if there's data to export
        if (!$('#expectedVsCollectedTable tbody tr').length) {
            Swal.fire({
                icon: 'warning',
                title: 'No Data to Export',
                text: 'Please filter the report first to generate data for export.',
                confirmButtonText: 'OK'
            });
            return;
        }

        const form = document.getElementById('filterForm');
        const originalAction = form.action;
        
        if (format === 'excel') {
            form.action = '{{ route('accounting.loans.reports.expected_vs_collected.export_excel') }}';
        } else if (format === 'pdf') {
            form.action = '{{ route('accounting.loans.reports.expected_vs_collected.export_pdf') }}';
        }
        
        form.submit();
        
        // Restore original action
        setTimeout(() => {
            form.action = originalAction;
        }, 100);
    }
    
    function resetFilters() {
        document.getElementById('start_date').value = '{{ Carbon\Carbon::now()->startOfMonth()->toDateString() }}';
        document.getElementById('end_date').value = '{{ Carbon\Carbon::now()->toDateString() }}';
        document.getElementById('branch_id').value = '';
        document.getElementById('group_id').value = '';
        document.getElementById('loan_officer_id').value = '';
        document.getElementById('filterForm').submit();
    }
    
    $(document).ready(function() {
        // Animate summary cards on page load
        if ($('.summary-card').length) {
            $('.summary-card').each(function(index) {
                $(this).css('opacity', '0').css('transform', 'translateY(20px)');
                $(this).delay(index * 100).animate({
                    opacity: 1
                }, 500, function() {
                    $(this).css('transform', 'translateY(0)');
                });
            });
        }

        // Auto-submit form when date range changes
        $('#start_date, #end_date').on('change', function() {
            if ($('#start_date').val() && $('#end_date').val()) {
                // Show loading state
                $('#filterBtn').html('<i class="bx bx-loader-alt spin me-1"></i> Filtering...');
                $('#filterBtn').prop('disabled', true);
                $('#filterForm').submit();
            }
        });

        // Reset button functionality
        $('#resetBtn').on('click', function() {
            resetFilters();
        });

        // Initialize tooltips
        $('[data-bs-toggle="tooltip"]').tooltip();

        // Add loading state to filter button
        $('#filterForm').on('submit', function() {
            $('#filterBtn').html('<i class="bx bx-loader-alt spin me-1"></i> Filtering...');
            $('#filterBtn').prop('disabled', true);
        });

        // Add spinner CSS
        $('<style>')
            .prop('type', 'text/css')
            .html(`
                .spin {
                    animation: spin 1s linear infinite;
                }
                @keyframes spin {
                    0% { transform: rotate(0deg); }
                    100% { transform: rotate(360deg); }
                }
            `)
            .appendTo('head');
    });
</script>
@endpush
