<form action="{{ isset($branch) ? route('branches.update', $branch->id) : route('branches.store') }}" method="POST">
    @csrf
    @if(isset($branch))
        @method('PUT')
    @endif

    <div class="row mb-3">
        <div class="col-md-6">
            <label class="form-label">Select Company</label>
            <select class="form-select" name="company_id" required>
                <option value="">-- Choose Company --</option>
                @foreach($companies as $company)
                    <option value="{{ $company->id }}"
                        {{ (old('company_id') == $company->id || (isset($branch) && $branch->company_id == $company->id)) ? 'selected' : '' }}>
                        {{ $company->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="col-md-6">
            <label class="form-label">Branch Name</label>
            <input type="text" class="form-control" name="name" value="{{ $branch->name ?? old('name') }}" required>
        </div>
    </div>

    <div class="row mb-3">
        <div class="col-md-6">
            <label class="form-label">Email Address</label>
            <input type="email" class="form-control" name="email" value="{{ $branch->email ?? old('email') }}" required>
        </div>

        <div class="col-md-6">
            <label class="form-label">Phone Number</label>
            <input type="text" class="form-control" name="phone" value="{{ $branch->phone ?? old('phone') }}" required>
        </div>
    </div>

    <div class="mb-3">
        <label class="form-label">Address</label>
        <input type="text" class="form-control" name="address" value="{{ $branch->address ?? old('address') }}" required>
    </div>

    <div class="d-flex justify-content-end">
        <button type="submit" class="btn btn-{{ isset($branch) ? 'primary' : 'success' }}">
            {{ isset($branch) ? 'Update Branch' : 'Create Branch' }}
        </button>
    </div>
</form>
