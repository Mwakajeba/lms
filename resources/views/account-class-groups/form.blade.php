@php
    use Vinkla\Hashids\Facades\Hashids;
@endphp

<form
    action="{{ isset($accountClassGroup) ? route('accounting.account-class-groups.update', Hashids::encode($accountClassGroup->id)) : route('accounting.account-class-groups.store') }}"
    method="POST">
    @csrf
    @if(isset($accountClassGroup))
        @method('PUT')
    @endif

    <div class="row mb-3">
        <div class="col-md-6">
            <label class="form-label">Select Account Class</label>
            <select class="form-select" name="class_id" required>
                <option value="">-- Choose Account Class --</option>
                @foreach($accountClasses as $accountClass)
                    <option value="{{ $accountClass->id }}" {{ (old('class_id') == $accountClass->id || (isset($accountClassGroup) && $accountClassGroup->class_id == $accountClass->id)) ? 'selected' : '' }}>
                        {{ $accountClass->name }}
                    </option>
                @endforeach
            </select>
            @error('class_id')
                <div class="text-danger">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-md-6">
            <label class="form-label">Group Code</label>
            <input type="text" class="form-control" name="group_code"
                value="{{ $accountClassGroup->group_code ?? old('group_code') }}" placeholder="e.g., 1000, 2000, etc.">
            @error('group_code')
                <div class="text-danger">{{ $message }}</div>
            @enderror
        </div>
    </div>

    <div class="mb-3">
        <label class="form-label">Group Name</label>
        <input type="text" class="form-control" name="name" value="{{ $accountClassGroup->name ?? old('name') }}"
            required placeholder="e.g., Current Assets, Long-term Liabilities, etc.">
        @error('name')
            <div class="text-danger">{{ $message }}</div>
        @enderror
    </div>

    <div class="d-flex justify-content-end">
        <a href="{{ route('accounting.account-class-groups.index') }}" class="btn btn-secondary me-2">Cancel</a>
        <button type="submit" class="btn btn-{{ isset($accountClassGroup) ? 'primary' : 'success' }}">
            {{ isset($accountClassGroup) ? 'Update Group' : 'Create Group' }}
        </button>
    </div>
</form>