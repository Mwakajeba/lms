<form action="{{ isset($bankAccount) ? route('accounting.bank-accounts.update', \Vinkla\Hashids\Facades\Hashids::encode($bankAccount->id)) : route('accounting.bank-accounts.store') }}" method="POST">
    @csrf
    @if(isset($bankAccount)) @method('PUT') @endif

    <div class="row">
        <div class="col-md-6">
            <div class="mb-3">
                <label for="chart_account_id" class="form-label">Chart Account <span class="text-danger">*</span></label>
                <select class="form-select select2-single @error('chart_account_id') is-invalid @enderror" name="chart_account_id" id="chart_account_id" required>
                    <option value="">-- Choose Chart Account --</option>
                    @foreach($chartAccounts as $chartAccount)
                        <option value="{{ $chartAccount->id }}" {{ (old('chart_account_id') == $chartAccount->id || (isset($bankAccount) && $bankAccount->chart_account_id == $chartAccount->id)) ? 'selected' : '' }}>
                            {{ $chartAccount->account_name }} ({{ $chartAccount->accountClassGroup->accountClass->name ?? 'N/A' }} - {{ $chartAccount->accountClassGroup->name ?? 'N/A' }})
                        </option>
                    @endforeach
                </select>
                @error('chart_account_id')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>

        <div class="col-md-6">
            <div class="mb-3">
                <label for="name" class="form-label">Bank Name <span class="text-danger">*</span></label>
                <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" 
                       value="{{ old('name', $bankAccount->name ?? '') }}" required>
                @error('name')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>

        <div class="col-md-6">
            <div class="mb-3">
                <label for="account_number" class="form-label">Account Number <span class="text-danger">*</span></label>
                <input type="text" class="form-control @error('account_number') is-invalid @enderror" id="account_number" name="account_number" 
                       value="{{ old('account_number', $bankAccount->account_number ?? '') }}" required>
                @error('account_number')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>
    </div>

    <div class="d-flex justify-content-end gap-2">
        @can('view bank accounts')
        <a href="{{ route('accounting.bank-accounts') }}" class="btn btn-secondary">
            <i class="bx bx-x me-1"></i> Cancel
        </a>
        @endcan
        <button type="submit" class="btn btn-{{ isset($bankAccount) ? 'primary' : 'success' }}">
            <i class="bx bx-{{ isset($bankAccount) ? 'check' : 'plus' }} me-1"></i>
            {{ isset($bankAccount) ? 'Update Bank Account' : 'Create Bank Account' }}
        </button>
    </div>
</form> 