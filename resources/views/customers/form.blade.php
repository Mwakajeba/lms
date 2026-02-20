@php
$isEdit = isset($customer);
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

<form action="{{ $isEdit ? route('customers.update', $customer) : route('customers.store') }}"
      method="POST" enctype="multipart/form-data">
    @csrf
    @if($isEdit) @method('PUT') @endif

    <div class="row">
        <!-- Name -->
        <div class="col-md-6 mb-3">
            <label class="form-label">Full Name <span class="text-danger">*</span></label>
            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                value="{{ old('name', isset($customer) ? $customer->name : '') }}" placeholder="Enter full name (will be stored in uppercase)">
            @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <!-- Sex -->
        <div class="col-md-6 mb-3">
            <label for="sex" class="form-label">Sex <span class="text-danger">*</span></label>
            <select name="sex" id="sex" class="form-control @error('sex') is-invalid @enderror" required>
                <option value="">-- Select Sex --</option>
                <option value="M" {{ old('sex', $customer->sex ?? '') == 'M' ? 'selected' : '' }}>Male</option>
                <option value="F" {{ old('sex', $customer->sex ?? '') == 'F' ? 'selected' : '' }}>Female</option>
            </select>
            @error('sex')
            <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <!-- Description -->
        <div class="col-md-12 mb-3">
            <label class="form-label">Description</label>
            <textarea name="description" class="form-control @error('description') is-invalid @enderror"
                      rows="3" placeholder="Enter customer description">{{ old('description', $customer->description ?? '') }}</textarea>
            @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <!-- Phone 1 -->
        <div class="col-md-6 mb-3">
            <label class="form-label">Phone Number <span class="text-danger">*</span></label>
            <input type="text" name="phone1" id="phone1" class="form-control @error('phone1') is-invalid @enderror"
                value="{{ old('phone1', $customer->phone1 ?? '') }}" placeholder="e.g. 255712345678 or 0712345678">
            <div class="form-text">
                <i class="bx bx-info-circle"></i> Phone number must start with prefix <strong>255</strong> (e.g., 255712345678). If you enter a number starting with 0, it will be automatically formatted.
            </div>
            <div id="phone1-alert" class="alert alert-danger mt-2" style="display: none;">
                <i class="bx bx-error-circle me-1"></i>
                <span id="phone1-alert-message"></span>
            </div>
            @error('phone1') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <!-- Phone 2 -->
        <div class="col-md-6 mb-3">
            <label class="form-label">Alternative Phone Number</label>
            <input type="text" name="phone2" class="form-control @error('phone2') is-invalid @enderror"
                value="{{ old('phone2', $customer->phone2 ?? '') }}" placeholder="Enter alternative phone">
            @error('phone2') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <!-- Region -->
        <div class="col-md-6 mb-3">
            <label class="form-label">Region <span class="text-danger">*</span></label>
            <select name="region_id" id="region" class="form-select select2-single @error('region_id') is-invalid @enderror" required>
                <option value="">Select Region</option>
                @foreach($regions as $region)
                <option value="{{ $region->id }}" {{ old('region_id', $customer->region_id ?? '') == $region->id ? 'selected' : '' }}>
                    {{ $region->name }}
                </option>
                @endforeach
            </select>
            @error('region_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <!-- District -->
        <div class="col-md-6 mb-3">
            <label class="form-label">District <span class="text-danger">*</span></label>
            <select name="district_id" id="district" class="form-select @error('district_id') is-invalid @enderror"
                required>
                <option value="">Select District</option>
                @if($isEdit && $customer->district_id)
                <option value="{{ $customer->district_id }}" selected>
                    {{ $customer->district->name ?? 'Selected District' }}
                </option>
                @elseif(old('district_id'))
                <option value="{{ old('district_id') }}" selected>
                    {{ \App\Models\District::find(old('district_id'))->name ?? 'Selected District' }}
                </option>
                @endif
            </select>
            @error('district_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <!-- Work -->
        <div class="col-md-6 mb-3">
            <label class="form-label">Work</label>
            <input type="text" name="work" class="form-control @error('work') is-invalid @enderror"
                value="{{ old('work', $customer->work ?? '') }}" placeholder="e.g. Teacher">
            @error('work') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <!-- Work Address -->
        <div class="col-md-6 mb-3">
            <label class="form-label">Work Address</label>
            <input type="text" name="workAddress" class="form-control @error('workAddress') is-invalid @enderror"
                value="{{ old('workAddress', $customer->workAddress ?? '') }}" placeholder="e.g. ABC School, Dar">
            @error('workAddress') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <!-- ID Type -->
        <div class="col-md-6 mb-3">
            <label class="form-label">ID Type</label>
            <select name="idType" id="idType" class="form-select @error('idType') is-invalid @enderror">
                <option value="">Select ID Type</option>
                @foreach(['National ID', 'License', 'Voter Registration', 'Other'] as $type)
                <option value="{{ $type }}" {{ old('idType', $customer->idType ?? '') == $type ? 'selected' : '' }}>
                    {{ $type }}
                </option>
                @endforeach
            </select>
            @error('idType') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <!-- ID Number -->
        <div class="col-md-6 mb-3">
            <label class="form-label">ID Number</label>
            <input type="text" name="idNumber" id="idNumber" class="form-control @error('idNumber') is-invalid @enderror"
                value="{{ old('idNumber', $customer->idNumber ?? '') }}" placeholder="Enter ID Number">
            <div id="idNumber-feedback" class="form-text"></div>
            <div id="idNumber-alert" class="alert alert-danger mt-2" style="display: none;">
                <i class="bx bx-error-circle me-1"></i>
                <span id="idNumber-alert-message"></span>
            </div>
            @error('idNumber') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <!-- DOB -->
        <div class="col-md-6 mb-3">
            <label class="form-label">Date of Birth <span class="text-danger">*</span></label>
            <input type="date" name="dob" id="dob" class="form-control @error('dob') is-invalid @enderror"
                value="{{ old('dob', isset($customer) && $customer->dob ? \Carbon\Carbon::parse($customer->dob)->format('Y-m-d') : '') }}"
                max="{{ \Carbon\Carbon::now()->subYears(18)->format('Y-m-d') }}">
            <div class="form-text">
                <i class="bx bx-info-circle"></i> Customer must be <strong>at least 18 years old</strong> (Date of birth must be on or before {{ \Carbon\Carbon::now()->subYears(18)->format('F d, Y') }}).
            </div>
            <div id="age-display" class="text-muted mt-1" style="display: none;">
                <small>Age: <span id="calculated-age"></span> years</small>
            </div>
            @error('dob') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <!-- Relation -->
        <div class="col-md-6 mb-3">
            <label class="form-label">Relation</label>
            <input type="text" name="relation" class="form-control @error('relation') is-invalid @enderror"
                value="{{ old('relation', $customer->relation ?? '') }}" placeholder="e.g. Spouse, Parent">
            @error('relation') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <!-- Photo Upload -->
        <div class="col-md-6 mb-3">
            <label class="form-label">Photo</label>
            <input type="file" name="photo" accept="image/*" class="form-control" onchange="previewImage(event)">
            @error('photo') <div class="invalid-feedback">{{ $message }}</div> @enderror
            <div id="preview" class="mt-2">
                @if($isEdit && $customer->photo)
                <img src="{{ asset('storage/'.$customer->photo) }}" width="100">
                @endif
            </div>
        </div>

        <!-- Document Upload -->
        <!-- <div class="col-md-6 mb-3>
            <label class="form-label">Upload Document</label>
            <input type="file" name="document" class="form-control @error('document') is-invalid @enderror"
                accept=".pdf,.doc,.docx,image/*">
            @error('document') <div class="invalid-feedback">{{ $message }}</div> @enderror

            @if($isEdit && isset($customer) && $customer->document)
                <div class="mt-2">
                    <a href="{{ asset('storage/' . $customer->document) }}" target="_blank">
                        View Uploaded Document
                    </a>
                </div>
            @endif
        </div> -->

        @if($isEdit)
        <!-- Password (only for edit) -->
        <div class="col-md-6 mb-3">
            <label class="form-label">New Password (leave blank to keep current)</label>
            <input type="password" name="password" class="form-control @error('password') is-invalid @enderror"
                placeholder="Enter new password">
            @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
        @endif

        <!-- Cash Collateral -->
        <div class="col-md-6 mb-3">
            <div class="form-check">
                <input type="checkbox" class="form-check-input" value="1" name="has_cash_collateral"
                    id="has_cash_collateral" {{ old('has_cash_collateral', $customer->has_cash_collateral ?? false) ? 'checked' : '' }}>
                <label class="form-check-label" for="has_cash_collateral">Has Cash Collateral</label>
            </div>
        </div>

        <!-- Collateral Type -->
        <div class="col-md-6 mb-3" id="collateral-type-container" style="display: none;">
            <label class="form-label">Collateral Type</label>
            <select name="collateral_type_id" class="form-select">
                <option value="">Select Collateral Type</option>
                @foreach($collateralTypes as $type)
                    <option value="{{ $type->id }}"
                        {{ old('collateral_type_id', isset($customer) ? ($customer->collaterals->first()->type_id ?? $customer->collateral_type_id ?? '') : '') == $type->id ? 'selected' : '' }}>
                        {{ $type->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <!-- Loan Officers -->
        <div class="col-md-12 mb-3">
            <label class="form-label">Assign Loan Officer(s)</label>
            @if($loanOfficers->count() > 0)
            <div class="row">
                @foreach($loanOfficers as $officer)
                <div class="col-md-4 mb-2">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="loan_officer_ids[]"
                            value="{{ $officer->id }}"
                            {{ in_array($officer->id, old('loan_officer_ids', $customer->loan_officer_ids ?? [])) ? 'checked' : '' }}>
                        <label class="form-check-label">{{ $officer->name }}</label>
                    </div>
                </div>
                @endforeach
            </div>
            @else
            <div class="alert alert-info">
                <i class="bx bx-info-circle me-2"></i>
                No loan officers found. Please create loan officer roles first.
            </div>
            @endif
        </div>

        <!-- Category -->
        <div class="col-md-6 mb-3">
            <label class="form-label">Category <span class="text-danger">*</span></label>
            <select name="category" class="form-select @error('category') is-invalid @enderror" required>
                <option value="">Select Category</option>
                <option value="Guarantor" {{ old('category', $customer->category ?? '') == 'Guarantor' ? 'selected' : '' }}>Guarantor</option>
                <option value="Borrower" {{ old('category', $customer->category ?? '') == 'Borrower' ? 'selected' : '' }}>Borrower</option>
            </select>
            @error('category') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <!-- Group -->
        <!-- <div class="col-md-6 mb-3 hidden">
            <label class="form-label">Group</label>
            <select name="group_id" class="form-select selectpicker" data-live-search="true">
                <option value="">Select Group</option>
                @foreach($groups as $group)
                    @if($group)
                        <option value="{{ $group->id }}"
                            {{ (old('group_id', $customer->group_id ?? ((isset($customer) && isset($customer->groups) && $customer->groups->first() ? $customer->groups->first()->id : ''))) == $group->id) ? 'selected' : '' }}>
                            {{ $group->name }}
                        </option>
                    @endif
                @endforeach
            </select>
            @error('group_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div> -->

        <hr class="my-4">

        <div class="d-flex justify-content-between">
            @can('view borrower')
            <a href="{{ route('customers.index') }}" class="btn btn-secondary">
                <i class="bx bx-arrow-back me-1"></i> Back to Customers
            </a>
            @endcan
            <button type="submit" class="btn btn-primary">
                <i class="bx bx-save me-1"></i> {{ $isEdit ? 'Update Customer' : 'Create Customer' }}
            </button>
        </div>
</form>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const checkbox = document.querySelector('#has_cash_collateral');
        const collateralContainer = document.querySelector('#collateral-type-container');
        const regionSelect = document.querySelector('#region');
        const districtSelect = document.querySelector('#district');

        // Show/hide collateral type
        function toggleCollateralField() {
            if (checkbox.checked) {
                collateralContainer.style.display = 'block';
            } else {
                collateralContainer.style.display = 'none';
            }
        }

        checkbox.addEventListener('change', toggleCollateralField);
        toggleCollateralField(); // On load

        // Load districts on region change
        regionSelect.addEventListener('change', function() {
            const regionId = this.value;

            if (!regionId) {
                districtSelect.innerHTML = '<option value="">Select District</option>';
                return;
            }

            fetch(`/get-districts/${regionId}`)
                .then(response => response.json())
                .then(data => {
                    districtSelect.innerHTML = '<option value="">Select District</option>';
                    Object.entries(data).forEach(([id, name]) => {
                        const option = document.createElement('option');
                        option.value = id;
                        option.textContent = name;
                        districtSelect.appendChild(option);
                    });
                })
                .catch(error => console.error('Error loading districts:', error));
        });

        // Initialize Select2 for region only (not district)
        if (window.jQuery) {
            $('#region').select2({
                placeholder: 'Select Region',
                allowClear: true,
                width: '100%',
                theme: 'bootstrap-5'
            });
            // Use jQuery event for region change
            $('#region').on('change', function() {
                const regionId = this.value;
                const districtSelect = document.getElementById('district');
                if (!regionId) {
                    districtSelect.innerHTML = '<option value="">Select District</option>';
                    return;
                }
                fetch(`/get-districts/${regionId}`)
                    .then(response => response.json())
                    .then(data => {
                        districtSelect.innerHTML = '<option value="">Select District</option>';
                        Object.entries(data).forEach(([id, name]) => {
                            const option = document.createElement('option');
                            option.value = id;
                            option.textContent = name;
                            districtSelect.appendChild(option);
                        });
                    })
                    .catch(error => console.error('Error loading districts:', error));
            });
        } else {
            // Fallback for non-jQuery environments
            regionSelect.addEventListener('change', function() {
                const regionId = this.value;
                if (!regionId) {
                    districtSelect.innerHTML = '<option value="">Select District</option>';
                    return;
                }
                fetch(`/get-districts/${regionId}`)
                    .then(response => response.json())
                    .then(data => {
                        districtSelect.innerHTML = '<option value="">Select District</option>';
                        Object.entries(data).forEach(([id, name]) => {
                            const option = document.createElement('option');
                            option.value = id;
                            option.textContent = name;
                            districtSelect.appendChild(option);
                        });
                    })
                    .catch(error => console.error('Error loading districts:', error));
            });
        }
    });

    // Image preview function
    function previewImage(event) {
        const file = event.target.files[0];
        const preview = document.getElementById('preview');

        if (file) {
            const reader = new FileReader();
            reader.onload = function (e) {
                preview.innerHTML = `<img src="${e.target.result}" width="100" class="mt-2">`;
            }
            reader.readAsDataURL(file);
        }
    }

    // Calculate and display age from date of birth
    document.addEventListener('DOMContentLoaded', function() {
        const dobInput = document.getElementById('dob');
        const ageDisplay = document.getElementById('age-display');
        const calculatedAgeSpan = document.getElementById('calculated-age');

        if (dobInput && ageDisplay && calculatedAgeSpan) {
            function calculateAge() {
                const dobValue = dobInput.value;
                if (dobValue) {
                    const dob = new Date(dobValue);
                    const today = new Date();
                    let age = today.getFullYear() - dob.getFullYear();
                    const monthDiff = today.getMonth() - dob.getMonth();
                    
                    if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < dob.getDate())) {
                        age--;
                    }
                    
                    calculatedAgeSpan.textContent = age;
                    ageDisplay.style.display = 'block';
                    
                    // Add warning if age is less than 18
                    if (age < 18) {
                        ageDisplay.className = 'text-danger mt-1';
                        calculatedAgeSpan.textContent = age + ' (Must be at least 18)';
                    } else {
                        ageDisplay.className = 'text-success mt-1';
                    }
                } else {
                    ageDisplay.style.display = 'none';
                }
            }

            // Calculate age on page load if date is already set
            if (dobInput.value) {
                calculateAge();
            }

            // Calculate age when date changes
            dobInput.addEventListener('change', calculateAge);
            dobInput.addEventListener('input', calculateAge);
        }
    });


    // Add/remove filetype-document upload rows
    document.addEventListener('DOMContentLoaded', function() {
        const container = document.getElementById('file-type-upload-container');
        const addBtn = document.getElementById('add-filetype-row');

        if (container && addBtn) {
            // Ensure there's always at least one row for new customers
            if (!container.querySelector('.file-type-upload-row')) {
                addBtn.click(); // This will add the first row
            }

            addBtn.addEventListener('click', function() {
                const row = document.querySelector('.file-type-upload-row');
                const newRow = row.cloneNode(true);

                // Clear values
                newRow.querySelector('select').selectedIndex = 0;
                newRow.querySelector('input[type="file"]').value = '';

                container.appendChild(newRow);
            });

            container.addEventListener('click', function(e) {
                if (e.target.closest('.remove-filetype-row')) {
                    const rows = container.querySelectorAll('.file-type-upload-row');
                    if (rows.length > 1) {
                        e.target.closest('.file-type-upload-row').remove();
                    }
                }
            });
        }
    });

    // ID Number Formatting and Validation
    document.addEventListener('DOMContentLoaded', function() {
        const idTypeSelect = document.getElementById('idType');
        const idNumberInput = document.getElementById('idNumber');
        const idNumberFeedback = document.getElementById('idNumber-feedback');
        const idNumberAlert = document.getElementById('idNumber-alert');
        const idNumberAlertMessage = document.getElementById('idNumber-alert-message');
        const phone1Input = document.getElementById('phone1');
        const phone1Alert = document.getElementById('phone1-alert');
        const phone1AlertMessage = document.getElementById('phone1-alert-message');
        const customerId = @json($isEdit ? $customer->id : null);
        const sexSelect = document.getElementById('sex');
        const dobInput = document.getElementById('dob');
        let phoneValidationTimeout;
        let idValidationTimeout;

        // Format ID number based on ID type
        function formatIdNumber(value, idType) {
            // Remove all non-digit characters
            const digits = value.replace(/\D/g, '');
            
            if (idType === 'National ID') {
                // Format as XXXXXXXX-XXXXX-XXXXX-XX (20 digits: 8-5-5-2)
                if (digits.length <= 20) {
                    let formatted = '';
                    if (digits.length > 0) {
                        formatted += digits.substring(0, 8);
                        if (digits.length > 8) {
                            formatted += '-' + digits.substring(8, 13);
                            if (digits.length > 13) {
                                formatted += '-' + digits.substring(13, 18);
                                if (digits.length > 18) {
                                    formatted += '-' + digits.substring(18, 20);
                                }
                            }
                        }
                    }
                    return formatted || digits;
                }
                // Limit to 20 digits
                const limited = digits.substring(0, 20);
                return limited.substring(0, 8) + '-' + limited.substring(8, 13) + '-' + limited.substring(13, 18) + '-' + limited.substring(18, 20);
            } else if (idType === 'License') {
                // Format as XXXXXXXXX (9 digits, no dashes)
                if (digits.length <= 9) {
                    return digits;
                }
                return digits.substring(0, 9);
            } else if (idType === 'Voter Registration') {
                // Format as XXXXX-XXXXXX-XXX (14 digits: 5-6-3)
                if (digits.length <= 14) {
                    let formatted = '';
                    if (digits.length > 0) {
                        formatted += digits.substring(0, 5);
                        if (digits.length > 5) {
                            formatted += '-' + digits.substring(5, 11);
                            if (digits.length > 11) {
                                formatted += '-' + digits.substring(11, 14);
                            }
                        }
                    }
                    return formatted || digits;
                }
                // Limit to 14 digits
                const limited = digits.substring(0, 14);
                return limited.substring(0, 5) + '-' + limited.substring(5, 11) + '-' + limited.substring(11, 14);
            }
            // For 'Other', return as is
            return value;
        }

        // Handle ID type change
        if (idTypeSelect && idNumberInput) {
            idTypeSelect.addEventListener('change', function() {
                const idType = this.value;
                const currentValue = idNumberInput.value.replace(/\D/g, '');
                
                if (idType && currentValue) {
                    idNumberInput.value = formatIdNumber(currentValue, idType);
                }
                
                // Update feedback
                if (idType === 'National ID') {
                    idNumberFeedback.innerHTML = '<i class="bx bx-info-circle"></i> Format: XXXXXXXX-XXXXX-XXXXX-XX (20 digits). The first 8 digits should match DOB, and the 19th digit indicates gender (1=Female, 2=Male).';
                } else if (idType === 'License') {
                    idNumberFeedback.innerHTML = '<i class="bx bx-info-circle"></i> Format: XXXXXXXXX (9 digits, no dashes)';
                } else if (idType === 'Voter Registration') {
                    idNumberFeedback.innerHTML = '<i class="bx bx-info-circle"></i> Format: XXXXX-XXXXXX-XXX (14 digits)';
                } else if (idType === 'Other') {
                    idNumberFeedback.innerHTML = '<i class="bx bx-info-circle"></i> Enter ID number without formatting';
                } else {
                    idNumberFeedback.innerHTML = '';
                }

                // Validate National ID if type is National ID and ID number exists
                if (idType === 'National ID' && idNumberInput.value) {
                    setTimeout(() => {
                        validateNationalId(idNumberInput.value);
                    }, 100);
                } else if (idType !== 'National ID') {
                    // Clear National ID specific warnings when switching away
                    if (idNumberAlert.className.includes('alert-warning')) {
                        idNumberAlert.style.display = 'none';
                    }
                }
            });

            // Handle ID number input with formatting
            idNumberInput.addEventListener('input', function(e) {
                const idType = idTypeSelect.value;
                const cursorPosition = this.selectionStart;
                const originalLength = this.value.length;
                
                if (idType && idType !== 'Other') {
                    const formatted = formatIdNumber(this.value, idType);
                    this.value = formatted;
                    
                    // Adjust cursor position
                    const newLength = this.value.length;
                    const lengthDiff = newLength - originalLength;
                    this.setSelectionRange(cursorPosition + lengthDiff, cursorPosition + lengthDiff);
                }
                
                // Validate uniqueness and National ID format
                clearTimeout(idValidationTimeout);
                idValidationTimeout = setTimeout(() => {
                    validateIdNumber(this.value);
                    if (idType === 'National ID') {
                        validateNationalId(this.value);
                    }
                }, 500);
            });

            // Validate National ID format (DOB and Gender)
            function validateNationalId(idNumber) {
                if (!idNumber || idTypeSelect.value !== 'National ID') {
                    return;
                }

                // Remove formatting to get digits only
                const digits = idNumber.replace(/\D/g, '');
                
                // Need at least 19 digits to validate gender (19th digit)
                if (digits.length < 19) {
                    return;
                }

                const errors = [];

                // Extract first 8 digits (birth year + system code)
                const first8Digits = digits.substring(0, 8);
                
                // Extract 19th digit (gender indicator: 1 = Female, 2 = Male)
                const genderDigit = digits.charAt(18); // 19th digit (0-indexed: position 18)

                // Validate DOB match with first 8 digits
                if (dobInput && dobInput.value) {
                    const dob = new Date(dobInput.value);
                    const birthYear = dob.getFullYear();
                    const birthYearLast2 = String(birthYear).substring(2); // Last 2 digits of year (e.g., "95" for 1995)
                    
                    // Tanzanian National ID: First 8 digits typically contain:
                    // - Birth year (last 2 digits) usually at positions 0-1 or 2-3
                    // - System/region codes
                    // Check if the birth year (last 2 digits) appears in the first 8 digits
                    if (!first8Digits.includes(birthYearLast2)) {
                        errors.push(`The first 8 digits of the National ID (${first8Digits}) do not appear to match the date of birth year (${birthYear}). Please verify.`);
                    }
                }

                // Validate gender match
                if (sexSelect && sexSelect.value) {
                    const selectedSex = sexSelect.value; // 'M' or 'F'
                    const expectedGenderDigit = selectedSex === 'F' ? '1' : '2';
                    
                    if (genderDigit !== expectedGenderDigit) {
                        if (genderDigit === '1') {
                            errors.push('The National ID indicates Female (digit 1), but you selected Male. Please verify.');
                        } else if (genderDigit === '2') {
                            errors.push('The National ID indicates Male (digit 2), but you selected Female. Please verify.');
                        } else {
                            errors.push('The gender digit in the National ID (19th digit) does not match the selected sex.');
                        }
                    }
                }

                // Display errors
                if (errors.length > 0) {
                    idNumberAlertMessage.textContent = errors.join(' ');
                    idNumberAlert.style.display = 'block';
                    idNumberAlert.className = 'alert alert-warning mt-2';
                } else {
                    // Clear gender/DOB warnings but keep uniqueness check
                    if (idNumberAlert.className.includes('alert-warning')) {
                        idNumberAlert.style.display = 'none';
                    }
                }
            }

            // Validate when DOB changes
            if (dobInput) {
                dobInput.addEventListener('change', function() {
                    if (idTypeSelect.value === 'National ID' && idNumberInput.value) {
                        validateNationalId(idNumberInput.value);
                    }
                });
            }

            // Validate when sex changes
            if (sexSelect) {
                sexSelect.addEventListener('change', function() {
                    if (idTypeSelect.value === 'National ID' && idNumberInput.value) {
                        validateNationalId(idNumberInput.value);
                    }
                });
            }

            // Validate ID number uniqueness
            function validateIdNumber(idNumber) {
                if (!idNumber || idNumber.trim() === '') {
                    idNumberAlert.style.display = 'none';
                    return;
                }

                fetch('{{ route("customers.validate-id") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        idNumber: idNumber,
                        customerId: customerId
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.exists) {
                        idNumberAlertMessage.textContent = 'This ID number is already registered to another customer.';
                        idNumberAlert.style.display = 'block';
                        idNumberAlert.className = 'alert alert-danger mt-2';
                    } else {
                        idNumberAlert.style.display = 'none';
                    }
                })
                .catch(error => {
                    console.error('Error validating ID number:', error);
                });
            }
        }

        // Validate phone number uniqueness
        if (phone1Input) {
            phone1Input.addEventListener('input', function() {
                clearTimeout(phoneValidationTimeout);
                phoneValidationTimeout = setTimeout(() => {
                    validatePhoneNumber(this.value);
                }, 500);
            });

            function validatePhoneNumber(phone) {
                if (!phone || phone.trim() === '') {
                    phone1Alert.style.display = 'none';
                    return;
                }

                fetch('{{ route("customers.validate-phone") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        phone: phone,
                        customerId: customerId
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.exists) {
                        phone1AlertMessage.textContent = 'This phone number is already registered to another customer.';
                        phone1Alert.style.display = 'block';
                        phone1Alert.className = 'alert alert-danger mt-2';
                    } else {
                        phone1Alert.style.display = 'none';
                    }
                })
                .catch(error => {
                    console.error('Error validating phone number:', error);
                });
            }
        }

        // Initialize feedback on page load
        if (idTypeSelect && idNumberFeedback) {
            idTypeSelect.dispatchEvent(new Event('change'));
        }
    });
</script>
