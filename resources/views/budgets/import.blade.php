@extends('layouts.main')

@section('title', 'Import Budget')

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'Budgets', 'url' => route('accounting.budgets.index'), 'icon' => 'bx bx-chart'],
            ['label' => 'Import Budget', 'url' => '#', 'icon' => 'bx bx-import']
        ]" />

        <div class="row">
            <div class="col-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">
                            <i class="bx bx-import me-2"></i>Import Budget
                        </h5>
                    </div>
                    <div class="card-body">
                        @if(session('errors'))
                            <div class="alert alert-danger">
                                <h6 class="alert-heading">Import Errors:</h6>
                                <ul class="mb-0">
                                    @foreach(session('errors') as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        @if(session('error'))
                            <div class="alert alert-danger">
                                <i class="bx bx-error-circle me-2"></i>{{ session('error') }}
                            </div>
                        @endif

                        <div class="row mb-4">
                            <div class="col-12">
                                <div class="card bg-light border-0">
                                    <div class="card-body">
                                        <div class="d-flex align-items-center justify-content-between">
                                            <div>
                                                <h6 class="mb-1">
                                                    <i class="bx bx-download me-2"></i>Download Import Template
                                                </h6>
                                                <p class="mb-0 text-muted small">
                                                    Download the Excel template to see the required format for importing budgets.
                                                </p>
                                            </div>
                                            <a href="{{ route('accounting.budgets.download-template') }}" 
                                               class="btn btn-success">
                                                <i class="bx bx-download me-1"></i>Download Template
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <form method="POST" action="{{ route('accounting.budgets.store-import') }}" 
                              enctype="multipart/form-data" id="importForm">
                            @csrf
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="import_file" class="form-label fw-semibold">
                                        <i class="bx bx-file me-1"></i>Import File
                                    </label>
                                    <input type="file" class="form-control @error('import_file') is-invalid @enderror" 
                                           id="import_file" name="import_file" 
                                           accept=".xlsx,.xls,.csv" required>
                                    <div class="form-text">
                                        Supported formats: Excel (.xlsx, .xls) or CSV (.csv). Max size: 2MB
                                    </div>
                                    @error('import_file')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="budget_name" class="form-label fw-semibold">
                                        <i class="bx bx-bookmark me-1"></i>Budget Name
                                    </label>
                                    <input type="text" class="form-control @error('budget_name') is-invalid @enderror" 
                                           id="budget_name" name="budget_name" 
                                           value="{{ old('budget_name') }}" 
                                           placeholder="e.g., Annual Budget 2025" required>
                                    @error('budget_name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="year" class="form-label fw-semibold">
                                        <i class="bx bx-calendar me-1"></i>Budget Year
                                    </label>
                                    <select class="form-select @error('year') is-invalid @enderror" 
                                            id="year" name="year" required>
                                        <option value="">Select Year</option>
                                        @for($year = date('Y') + 2; $year >= 2020; $year--)
                                            <option value="{{ $year }}" {{ old('year') == $year ? 'selected' : '' }}>
                                                {{ $year }}
                                            </option>
                                        @endfor
                                    </select>
                                    @error('year')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="description" class="form-label fw-semibold">
                                        <i class="bx bx-note me-1"></i>Description (Optional)
                                    </label>
                                    <textarea class="form-control @error('description') is-invalid @enderror" 
                                              id="description" name="description" rows="1" 
                                              placeholder="Brief description of the budget">{{ old('description') }}</textarea>
                                    @error('description')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="row mb-4">
                                <div class="col-12">
                                    <div class="card bg-info bg-opacity-10 border-info">
                                        <div class="card-body">
                                            <h6 class="card-title text-info">
                                                <i class="bx bx-info-circle me-2"></i>File Format Requirements
                                            </h6>
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <h6 class="fw-semibold">Template Columns:</h6>
                                                    <ul class="mb-0">
                                                        <li><strong>Column A:</strong> Account Code (pre-filled)</li>
                                                        <li><strong>Column B:</strong> Account Name (pre-filled)</li>
                                                        <li><strong>Column C:</strong> Amount (fill in your budget amount)</li>
                                                        <li><strong>Column D:</strong> Category (Revenue, Expense, or Capital Expenditure)</li>
                                                    </ul>
                                                </div>
                                                <div class="col-md-6">
                                                    <h6 class="fw-semibold">Instructions:</h6>
                                                    <ul class="mb-0">
                                                        <li>Template includes all your chart accounts</li>
                                                        <li>Fill in amounts only for accounts you want to budget</li>
                                                        <li>Select category for each budget line</li>
                                                        <li>Delete sample data rows at the bottom</li>
                                                        <li>Empty amount/category rows will be ignored</li>
                                                        <li><strong>Note:</strong> Categories are case-sensitive (Revenue, Expense, Capital Expenditure)</li>
                                                    </ul>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-12">
                                    <div class="d-flex gap-2">
                                        <button type="submit" class="btn btn-primary" id="importBtn">
                                            <i class="bx bx-import me-1"></i>Import Budget
                                        </button>
                                        <a href="{{ route('accounting.budgets.index') }}" class="btn btn-outline-secondary">
                                            <i class="bx bx-arrow-back me-1"></i>Back to Budgets
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
$(document).ready(function() {
    $('#importForm').submit(function() {
        const file = $('#import_file')[0].files[0];
        const budgetName = $('#budget_name').val();
        const year = $('#year').val();
        
        if (!file) {
            alert('Please select a file to import');
            return false;
        }
        
        if (!budgetName.trim()) {
            alert('Please enter a budget name');
            return false;
        }
        
        if (!year) {
            alert('Please select a budget year');
            return false;
        }
        
        $('#importBtn').prop('disabled', true).html('<i class="bx bx-loader-alt bx-spin me-1"></i>Importing...');
        return true;
    });
});
</script>
@endpush
@endsection 