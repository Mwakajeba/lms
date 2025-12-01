@php
    use Vinkla\Hashids\Facades\Hashids;
    $isEdit = isset($supplier);
@endphp

@if($errors->any())
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

<form
    action="{{ $isEdit ? route('accounting.suppliers.update', Hashids::encode($supplier->id)) : route('accounting.suppliers.store') }}"
    method="POST" enctype="multipart/form-data">
    @csrf
    @if($isEdit) @method('PUT') @endif

    <div class="row">
        <!-- Basic Information Section -->
        <div class="col-12">
            <div class="card radius-10 mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="bx bx-info-circle me-2"></i>Basic Information</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Supplier Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                                value="{{ old('name', $supplier->name ?? '') }}" placeholder="Enter supplier name">
                            @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Status <span class="text-danger">*</span></label>
                            <select name="status" class="form-select select2-single @error('status') is-invalid @enderror" required>
                                <option value="">-- Select Status --</option>
                                @foreach($statusOptions as $value => $label)
                                    <option value="{{ $value }}" {{ old('status', $supplier->status ?? '') == $value ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                            @error('status') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Email Address</label>
                            <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                                value="{{ old('email', $supplier->email ?? '') }}" placeholder="Enter email address">
                            @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Phone Number</label>
                            <input type="text" name="phone" class="form-control @error('phone') is-invalid @enderror"
                                value="{{ old('phone', $supplier->phone ?? '') }}" placeholder="Enter phone number">
                            @error('phone') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Region</label>
                            <select name="region" class="form-select select2-single @error('region') is-invalid @enderror">
                                <option value="">-- Select Region --</option>
                                @foreach($regions as $region)
                                    <option value="{{ $region->name }}" {{ old('region', $supplier->region ?? '') == $region->name ? 'selected' : '' }}>
                                        {{ $region->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('region') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Branch</label>
                            <select name="branch_id" class="form-select select2-single @error('branch_id') is-invalid @enderror">
                                <option value="">-- Select Branch --</option>
                                @foreach($branches as $branch)
                                    <option value="{{ $branch->id }}" {{ old('branch_id', $supplier->branch_id ?? '') == $branch->id ? 'selected' : '' }}>
                                        {{ $branch->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('branch_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-12 mb-3">
                            <label class="form-label">Address</label>
                            <textarea name="address" class="form-control @error('address') is-invalid @enderror"
                                rows="3"
                                placeholder="Enter full address">{{ old('address', $supplier->address ?? '') }}</textarea>
                            @error('address') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Business & Legal Information Section -->
        <div class="col-12">
            <div class="card radius-10 mb-4">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0"><i class="bx bx-building me-2"></i>Business & Legal Information</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Company Registration Name</label>
                            <input type="text" name="company_registration_name"
                                class="form-control @error('company_registration_name') is-invalid @enderror"
                                value="{{ old('company_registration_name', $supplier->company_registration_name ?? '') }}"
                                placeholder="Enter registered company name">
                            @error('company_registration_name') <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">TIN Number</label>
                            <input type="text" name="tin_number"
                                class="form-control @error('tin_number') is-invalid @enderror"
                                value="{{ old('tin_number', $supplier->tin_number ?? '') }}"
                                placeholder="Enter TIN number" pattern="[0-9]+">
                            <div class="form-text">Enter numbers only (no dashes or special characters)</div>
                            @error('tin_number') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">VAT Number</label>
                            <input type="text" name="vat_number"
                                class="form-control @error('vat_number') is-invalid @enderror"
                                value="{{ old('vat_number', $supplier->vat_number ?? '') }}"
                                placeholder="Enter VAT number" pattern="[0-9]+">
                            <div class="form-text">Enter numbers only (no dashes or special characters)</div>
                            @error('vat_number') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-12 mb-3">
                            <label class="form-label">Products or Services</label>
                            <textarea name="products_or_services"
                                class="form-control @error('products_or_services') is-invalid @enderror" rows="3"
                                placeholder="Describe the products or services provided">{{ old('products_or_services', $supplier->products_or_services ?? '') }}</textarea>
                            @error('products_or_services') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Banking Information Section -->
        <div class="col-12">
            <div class="card radius-10 mb-4">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0"><i class="bx bx-credit-card me-2"></i>Banking Information</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Bank Name</label>
                            <input type="text" name="bank_name"
                                class="form-control @error('bank_name') is-invalid @enderror"
                                value="{{ old('bank_name', $supplier->bank_name ?? '') }}"
                                placeholder="Enter bank name">
                            @error('bank_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Bank Account Number</label>
                            <input type="text" name="bank_account_number"
                                class="form-control @error('bank_account_number') is-invalid @enderror"
                                value="{{ old('bank_account_number', $supplier->bank_account_number ?? '') }}"
                                placeholder="Enter account number">
                            @error('bank_account_number') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Account Name</label>
                            <input type="text" name="account_name"
                                class="form-control @error('account_name') is-invalid @enderror"
                                value="{{ old('account_name', $supplier->account_name ?? '') }}"
                                placeholder="Enter account holder name">
                            @error('account_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <hr class="my-4">

    <div class="d-flex justify-content-between">
        @can('view suppliers')
        <a href="{{ route('accounting.suppliers.index') }}" class="btn btn-secondary">
            Back to Suppliers
        </a>
        @endcan
        @if($isEdit)
            @can('edit supplier')
            <button type="submit" class="btn btn-primary">
                Update Supplier
            </button>
            @endcan
        @else
            @can('create supplier')
            <button type="submit" class="btn btn-primary">
                Create Supplier
            </button>
            @endcan
        @endif
    </div>
</form>

@push('scripts')
    <script>
        $(document).ready(function() {
            // Initialize Select2 for all select fields
            $('select.select2-single').select2({ width: '100%' });
        });

        // Phone number validation - only allow numbers and limit to 12 digits
        document.querySelector('input[name="phone"]').addEventListener('input', function (e) {
            let value = e.target.value.replace(/\D/g, '');
            if (value.length > 12) {
                value = value.slice(0, 12);
            }
            e.target.value = value;
        });

        // TIN and VAT number validation - only allow numbers
        document.querySelectorAll('input[name="tin_number"], input[name="vat_number"]').forEach(function (input) {
            input.addEventListener('input', function (e) {
                let value = e.target.value.replace(/\D/g, '');
                e.target.value = value;
            });
        });
    </script>
@endpush