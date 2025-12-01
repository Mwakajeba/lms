@php
    $user = auth()->user();
    $activeReconciliations = \App\Models\BankReconciliation::with(['bankAccount'])
        ->whereHas('bankAccount.chartAccount.accountClassGroup', function ($query) use ($user) {
            $query->where('company_id', $user->company_id);
        })
        ->whereIn('status', ['draft', 'in_progress'])
        ->orderBy('reconciliation_date', 'desc')
        ->limit(5)
        ->get();
@endphp

@if($activeReconciliations->count() > 0)
<div class="card border-0 shadow-sm">
    <div class="card-header bg-primary text-white">
        <h6 class="mb-0">
            <i class="bx bx-sync me-2"></i>
            Active Bank Reconciliations
        </h6>
    </div>
    <div class="card-body p-0">
        <div class="list-group list-group-flush">
            @foreach($activeReconciliations as $reconciliation)
            <div class="list-group-item d-flex justify-content-between align-items-center">
                <div class="flex-grow-1">
                    <h6 class="mb-1">{{ $reconciliation->bankAccount->name }}</h6>
                    <small class="text-muted">
                        {{ $reconciliation->start_date->format('M d') }} - {{ $reconciliation->end_date->format('M d, Y') }}
                    </small>
                    <div class="mt-1">
                        <span class="badge bg-{{ $reconciliation->status === 'draft' ? 'secondary' : 'warning' }}">
                            {{ ucfirst(str_replace('_', ' ', $reconciliation->status)) }}
                        </span>
                        @if($reconciliation->difference != 0)
                            <span class="badge bg-danger ms-1">
                                Difference: {{ number_format($reconciliation->difference, 2) }}
                            </span>
                        @else
                            <span class="badge bg-success ms-1">Balanced</span>
                        @endif
                    </div>
                </div>
                <div class="ms-3">
                    <a href="{{ route('accounting.bank-reconciliation.show', $reconciliation) }}" 
                       class="btn btn-sm btn-outline-primary">
                        <i class="bx bx-show"></i> View
                    </a>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    <div class="card-footer bg-light">
        <a href="{{ route('accounting.bank-reconciliation.index') }}" class="btn btn-sm btn-primary w-100">
            <i class="bx bx-list-ul me-1"></i> View All Reconciliations
        </a>
    </div>
</div>
@endif 