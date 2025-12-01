@extends('layouts.main')

@section('title', __('app.budget_details'))

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <!-- Breadcrumb -->
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => __('app.budgets'), 'url' => route('accounting.budgets.index'), 'icon' => 'bx bx-chart'],
            ['label' => $budget->name, 'url' => '#', 'icon' => 'bx bx-show']
        ]" />
        <h6 class="mb-0 text-uppercase">{{ __('app.budget_details') }}</h6>
        <hr />

        <!-- Budget Information -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="card-title mb-0">{{ __('app.budget') }} {{ __('app.info') }}</h5>
                            <div class="btn-group">
                                <a href="{{ route('accounting.budgets.export-excel', $budget) }}" class="btn btn-success btn-sm">
                                    <i class="bx bx-export"></i> Excel
                                </a>
                                <a href="{{ route('accounting.budgets.export-pdf', $budget) }}" class="btn btn-danger btn-sm">
                                    <i class="bx bx-file-pdf"></i> PDF
                                </a>
                                <a href="{{ route('accounting.budgets.edit', $budget) }}" class="btn btn-warning btn-sm">
                                    <i class="bx bx-edit"></i> {{ __('app.edit') }}
                                </a>
                                <a href="{{ route('accounting.budgets.index') }}" class="btn btn-secondary btn-sm">
                                    <i class="bx bx-arrow-back"></i> {{ __('app.back') }}
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <table class="table table-borderless">
                                    <tr>
                                        <td class="fw-bold" width="150">{{ __('app.budget_name') }}:</td>
                                        <td>{{ $budget->name }}</td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold">{{ __('app.budget_year') }}:</td>
                                        <td><span class="badge bg-info">{{ $budget->year }}</span></td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold">{{ __('app.budget_branch') }}:</td>
                                        <td>{{ $budget->branch->name ?? 'N/A' }}</td>
                                    </tr>
                                </table>
                            </div>
                            <div class="col-md-6">
                                <table class="table table-borderless">
                                    <tr>
                                        <td class="fw-bold" width="150">{{ __('app.budget_created_by') }}:</td>
                                        <td>{{ $budget->user->name ?? 'N/A' }}</td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold">{{ __('app.budget_created_date') }}:</td>
                                        <td>{{ $budget->created_at->format('M d, Y H:i') }}</td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold">{{ __('app.budget_total_amount') }}:</td>
                                        <td><span class="fw-bold text-success fs-5">TZS {{ number_format($budget->total_amount, 2) }}</span></td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Budget Lines -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">{{ __('app.budget_lines') }}</h5>
                    </div>
                    <div class="card-body">
                        @if($budget->budgetLines->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>{{ __('app.account') }} {{ __('app.code') }}</th>
                                        <th>{{ __('app.account') }} {{ __('app.name') }}</th>
                                        <th>{{ __('app.amount') }}</th>
                                        <th>{{ __('app.description') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($budget->budgetLines as $index => $line)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>
                                            <span class="badge bg-secondary">{{ $line->account->account_code ?? 'N/A' }}</span>
                                        </td>
                                        <td>{{ $line->account->account_name ?? 'N/A' }}</td>
                                        <td>
                                            <span class="fw-bold text-success">
                                                TZS {{ number_format($line->amount, 2) }}
                                            </span>
                                        </td>
                                        <td>{{ $line->description ?? '-' }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                                <tfoot>
                                    <tr class="table-dark">
                                                                                  <td colspan="3" class="fw-bold">{{ __('app.total') }}</td>
                                        <td class="fw-bold text-success">
                                            TZS {{ number_format($budget->total_amount, 2) }}
                                        </td>
                                        <td></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                        @else
                        <div class="text-center py-4">
                            <i class="bx bx-error-circle bx-lg text-warning mb-3"></i>
                            <h5 class="text-muted">{{ __('app.no_budget_lines_found') }}</h5>
                            <p class="text-muted">{{ __('app.no_budget_lines_message') }}</p>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Budget Summary -->
        <div class="row">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">{{ __('app.budget_summary') }}</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-6">
                                <div class="text-center">
                                    <h3 class="text-primary">{{ $budget->budgetLines->count() }}</h3>
                                    <p class="text-muted mb-0">{{ __('app.budget_lines') }}</p>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="text-center">
                                    <h3 class="text-success">TZS {{ number_format($budget->total_amount, 2) }}</h3>
                                    <p class="text-muted mb-0">{{ __('app.total_budget') }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">{{ __('app.quick_actions') }}</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <a href="{{ route('accounting.budgets.edit', $budget) }}" class="btn btn-warning">
                                <i class="bx bx-edit"></i> {{ __('app.edit_budget') }}
                            </a>
                            <a href="{{ route('accounting.budgets.export-excel', $budget) }}" class="btn btn-success">
                                <i class="bx bx-export"></i> Export to Excel
                            </a>
                            <a href="{{ route('accounting.budgets.export-pdf', $budget) }}" class="btn btn-danger">
                                <i class="bx bx-file-pdf"></i> Export to PDF
                            </a>
                            <a href="{{ route('accounting.budgets.index') }}" class="btn btn-secondary">
                                <i class="bx bx-list-ul"></i> {{ __('app.view_all_budgets') }}
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection