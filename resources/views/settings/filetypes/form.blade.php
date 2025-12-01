 @if($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
<form action="{{ isset($filetype) ? route('settings.filetypes.update', $filetype) : route('settings.filetypes.store') }}"
      method="POST">
    @csrf
    @if(isset($filetype))
        @method('PUT')
    @endif

    <div class="mb-3">
        <label for="name" class="form-label">File Type Name <span class="text-danger">*</span></label>
        <input type="text" name="name" id="name"
               class="form-control @error('name') is-invalid @enderror"
               value="{{ old('name', $filetype->name ?? '') }}"
               placeholder="e.g. Passport, ID, License">
        @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="d-flex justify-content-between">
        <a href="{{ route('settings.filetypes.index') }}" class="btn btn-secondary">
            <i class="bx bx-arrow-back me-1"></i> Back
        </a>
        <button type="submit" class="btn btn-primary">
            <i class="bx bx-save me-1"></i> {{ isset($filetype) ? 'Update' : 'Save' }}
        </button>
    </div>
</form>
