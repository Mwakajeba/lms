@extends('layouts.main')

@section('title', ucfirst($status) . ' Loan Applications')

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <x-breadcrumbs-with-icons :links="[
                ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
                ['label' => 'Loans', 'url' => route('loans.index'), 'icon' => 'bx bx-credit-card'],
                ['label' => ucfirst($status) . ' Applications', 'url' => '#', 'icon' => 'bx bx-file-plus'],    
            ]" />

        <div class="d-flex justify-content-between align-items-center mb-3">
            <h6 class="mb-0 text-uppercase">LOAN APPLICATIONS</h6>
            @can('create loan')
                @if(!in_array($status, ['checked', 'approved', 'authorized', 'rejected']))
                <a href="{{ route('loans.application.create') }}" class="btn btn-primary">
                    <i class="bx bx-plus me-1"></i> Apply for Loan
                </a>
                @endif
            @endcan
        </div>
        
        <!-- Status Navigation Tabs -->
        <div class="row mb-3">
            <div class="col-12">
                <ul class="nav nav-tabs" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link {{ $status === 'applied' ? 'active' : '' }}" 
                           href="{{ route('loans.by-status', ['status' => 'applied']) }}">
                            Applied Applications
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ $status === 'checked' ? 'active' : '' }}" 
                           href="{{ route('loans.by-status', ['status' => 'checked']) }}">
                            Checked Applications
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ $status === 'approved' ? 'active' : '' }}" 
                           href="{{ route('loans.by-status', ['status' => 'approved']) }}">
                            Approved Applications
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ $status === 'authorized' ? 'active' : '' }}" 
                           href="{{ route('loans.by-status', ['status' => 'authorized']) }}">
                            Authorized Applications
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ $status === 'rejected' ? 'active' : '' }}" 
                           href="{{ route('loans.by-status', ['status' => 'rejected']) }}">
                            Rejected Applications
                        </a>
                    </li>
                </ul>
            </div>
        </div>
        
        <hr />

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-bordered">
                                <thead>
                                    <tr>
                                        <th>Application ID</th>
                                        <th>Customer</th>
                                        <th>Product</th>
                                        <th>Amount</th>
                                        <th>Period</th>
                                        <th>Interest Rate</th>
                                        <th>Date Applied</th>
                                        <th>Status</th>
                                        <th>Comment</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($loanApplications as $application)
                                    <tr>
                                        <td>
                                            <span class="badge bg-primary">#{{ $application->id }}</span>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="avatar avatar-sm me-2">
                                                    <i class="bx bx-user-circle fs-4"></i>
                                                </div>
                                                <div>
                                                    <strong>{{ $application->customer->name }}</strong>
                                                    <br>
                                                    <small class="text-muted">{{ $application->customer->phone ?? 'No phone' }}</small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge bg-info">{{ $application->product->name ?? 'No Product' }}</span>
                                        </td>
                                        <td>
                                            <strong>TZS {{ number_format($application->amount, 2) }}</strong>
                                        </td>
                                        <td>
                                            <span class="badge bg-secondary">{{ $application->period }} {{ $application->getPeriodUnit() }}</span>
                                        </td>
                                        <td>
                                            <span class="badge bg-warning">{{ $application->interest ?? 'N/A' }}%</span>
                                        </td>
                                        <td>
                                            {{ \Carbon\Carbon::parse($application->date_applied)->format('M d, Y') }}
                                        </td>
                                        <td>
                                            @switch($application->status)
                                            @case('applied')
                                            <span class="badge bg-warning">Applied</span>
                                            @break
                                            @case('checked')
                                            <span class="badge bg-info">Checked</span>
                                            @break
                                            @case('approved')
                                            <span class="badge bg-primary">Approved</span>
                                            @break
                                            @case('authorized')
                                            <span class="badge bg-success">Authorized</span>
                                            @break
                                            @case('active')
                                            <span class="badge bg-success">Active</span>
                                            @break
                                            @case('rejected')
                                            <span class="badge bg-danger">Rejected</span>
                                            @break
                                            @case('defaulted')
                                            <span class="badge bg-dark">Defaulted</span>
                                            @break
                                            @default
                                            <span class="badge bg-secondary">{{ ucfirst($application->status) }}</span>
                                            @endswitch
                                        </td>
                                        <td>
                                            @php
                                                $latestApproval = $application->approvals->sortByDesc('approved_at')->first();
                                            @endphp
                                            @if($latestApproval && $latestApproval->comments)
                                                <div class="text-truncate" style="max-width: 200px;" title="{{ $latestApproval->comments }}">
                                                    <small class="text-muted">{{ $latestApproval->comments }}</small>
                                                </div>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                @can('view loan details')
                                                <a href="{{ route('loans.application.show', Hashids::encode($application->id)) }}"
                                                    class="btn btn-sm btn-outline-primary"
                                                    title="View Details">view
                                                </a>
                                                @endcan

                                                @can('edit loan')
                                                @if(in_array($application->status, ['applied', 'rejected']))
                                                <a href="{{ route('loans.application.edit', Hashids::encode($application->id)) }}"
                                                    class="btn btn-sm btn-outline-warning"
                                                    title="Edit Application">Edit
                                                </a>
                                                @if($application->status === 'rejected')
                                                <a href="{{ route('loans.application.edit', Hashids::encode($application->id)) }}"
                                                    class="btn btn-sm btn-outline-primary"
                                                    title="Fix issues and re-apply">Fix & Re-apply
                                                </a>
                                                @endif
                                                @endif
                                                @endcan
                                                
                                                @can('delete loan')
                                                @if(!in_array($application->status, ['authorized', 'checked', 'approved']))
                                                <button type="button"
                                                    class="btn btn-sm btn-outline-dark"
                                                    title="Delete Application"
                                                    onclick="deleteApplication('{{ Hashids::encode($application->id) }}')">
                                                    Delete
                                                </button>
                                                @endif
                                                @endcan
                                            </div>
                                        </td>
                                    </tr>
                                    @if($application->status === 'rejected')
                                    <tr>
                                        <td colspan="10">
                                            @php
                                                $rejection = optional($application->approvals)->where('action','rejected')->sortByDesc('approved_at')->first();
                                            @endphp
                                            @if($rejection && $rejection->comments)
                                            <div class="alert alert-danger mb-0">
                                                <i class="bx bx-error-circle me-2"></i>
                                                <strong>Rejection Comment:</strong>
                                                <span>{{ $rejection->comments }}</span>
                                            </div>
                                            @endif
                                        </td>
                                    </tr>
                                    @endif
                                    @empty
                                    <tr>
                                        <td colspan="10" class="text-center py-4">
                                            <div class="text-muted">
                                                <i class="bx bx-file-plus fs-1 mb-3"></i>
                                                <h6>No {{ ucfirst($status) }} Applications Found</h6>
                                                <p>
                                                    @if($status === 'applied')
                                                        Start by creating a new loan application.
                                                    @else
                                                        No applications found with {{ $status }} status.
                                                    @endif
                                                </p>
                                                @if(!in_array($status, ['checked', 'approved', 'authorized', 'rejected']))
                                                <a href="{{ route('loans.application.create') }}" class="btn btn-primary">
                                                    <i class="bx bx-plus me-1"></i> Apply for Loan
                                                </a>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        @if($loanApplications->hasPages())
                        <div class="d-flex justify-content-center mt-3">
                            {{ $loanApplications->links() }}
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
    function deleteApplication(encodedId) {
        Swal.fire({
            title: 'Are you sure?',
            text: "You won't be able to revert this!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                // Create a form and submit it
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = `/loans/application/${encodedId}`;

                const csrfToken = document.createElement('input');
                csrfToken.type = 'hidden';
                csrfToken.name = '_token';
                csrfToken.value = '{{ csrf_token() }}';

                const methodField = document.createElement('input');
                methodField.type = 'hidden';
                methodField.name = '_method';
                methodField.value = 'DELETE';

                form.appendChild(csrfToken);
                form.appendChild(methodField);
                document.body.appendChild(form);
                form.submit();
            }
        });
    }
</script>
@endpush