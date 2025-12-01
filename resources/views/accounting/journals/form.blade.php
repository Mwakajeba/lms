<form 
    action="{{ isset($journal) ? route('accounting.journals.update', $journal) : route('accounting.journals.store') }}" 
    method="POST" 
    enctype="multipart/form-data"
    id="journalForm"
    onsubmit="return handleSubmit(this)"
>
    @csrf
    @if(isset($journal))
        @method('PUT')
    @endif

    <!-- Basic Information -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="form-group">
                <label for="date" class="form-label fw-bold">
                    <i class="bx bx-calendar me-1"></i>Entry Date
                </label>
                <input type="date" 
                       name="date" 
                       id="date"
                       class="form-control @error('date') is-invalid @enderror" 
                       value="{{ old('date', isset($journal) ? $journal->date->format('Y-m-d') : now()->format('Y-m-d')) }}"
                       required>
                @error('date')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                <label for="attachment" class="form-label fw-bold">
                    <i class="bx bx-paperclip me-1"></i>Attachment
                </label>
                <input type="file" 
                       name="attachment" 
                       id="attachment"
                       class="form-control @error('attachment') is-invalid @enderror"
                       accept=".pdf,.doc,.docx,.jpg,.jpeg,.png">
                @error('attachment')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            @if(isset($journal) && $journal->attachment)
                <div class="mt-2">
                        <a href="{{ asset('storage/' . $journal->attachment) }}" 
                           target="_blank" 
                           class="btn btn-sm btn-outline-primary">
                            <i class="bx bx-download me-1"></i>View Current Attachment
                        </a>
                </div>
            @endif
            </div>
        </div>
    </div>

    <!-- Description -->
    <div class="form-group mb-4">
        <label for="description" class="form-label fw-bold">
            <i class="bx bx-message-square-detail me-1"></i>Description
        </label>
        <textarea name="description" 
                  id="description"
                  class="form-control @error('description') is-invalid @enderror" 
                  rows="3"
                  placeholder="Enter a detailed description of this journal entry..."
                  required>{{ old('description', $journal->description ?? '') }}</textarea>
        @error('description')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <!-- Journal Items Section -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-light border-0">
            <div class="d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-bold">
                    <i class="bx bx-list-ul me-2"></i>Journal Entries (Debit / Credit)
                </h6>
                <button type="button" class="btn btn-primary btn-sm" onclick="addItem()">
                    <i class="bx bx-plus me-1"></i>Add Entry
                </button>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover" id="items-table">
                    <thead class="table-light">
            <tr>
                            <th style="width: 35%;">Account</th>
                            <th style="width: 15%;">Nature</th>
                            <th style="width: 20%;">Amount</th>
                            <th style="width: 25%;">Description</th>
                            <th style="width: 5%;">Action</th>
            </tr>
        </thead>
        <tbody>
                        @php 
                            $items = old('items', []);
                            if (isset($journal) && $journal->items->count() > 0) {
                                $items = $journal->items->map(function($item) {
                                    return [
                                        'account_id' => $item->chart_account_id,
                                        'amount' => $item->amount,
                                        'nature' => $item->nature,
                                        'description' => $item->description,
                                    ];
                                })->toArray();
                            }
                        @endphp
                        @if(count($items) > 0)
            @foreach($items as $index => $item)
                                <tr class="journal-item">
                    <td>
                                        <select name="items[{{ $index }}][account_id]" 
                                                class="form-select account-select @error('items.'.$index.'.account_id') is-invalid @enderror"
                                                required>
                            <option value="">-- Select Account --</option>
                            @foreach($accounts as $account)
                                <option value="{{ $account->id }}"
                                                    {{ isset($item['account_id']) && $item['account_id'] == $account->id ? 'selected' : '' }}>
                                                    {{ $account->account_code }} - {{ $account->account_name }}
                                </option>
                            @endforeach
                        </select>
                                        @error('items.'.$index.'.account_id')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                    </td>
                    <td>
                                        <select name="items[{{ $index }}][nature]" 
                                                class="form-select nature-select @error('items.'.$index.'.nature') is-invalid @enderror"
                                                required>
                                            <option value="debit" {{ isset($item['nature']) && $item['nature'] == 'debit' ? 'selected' : '' }}>Debit</option>
                                            <option value="credit" {{ isset($item['nature']) && $item['nature'] == 'credit' ? 'selected' : '' }}>Credit</option>
                        </select>
                                        @error('items.'.$index.'.nature')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                    </td>
                    <td>
                                        <input type="number" 
                                               step="0.01" 
                                               name="items[{{ $index }}][amount]" 
                                               class="form-control amount-input @error('items.'.$index.'.amount') is-invalid @enderror"
                                               value="{{ $item['amount'] ?? '' }}"
                                               placeholder="0.00"
                                               required>
                                        @error('items.'.$index.'.amount')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                    </td>
                    <td>
                                        <input type="text" 
                                               name="items[{{ $index }}][description]" 
                                               class="form-control @error('items.'.$index.'.description') is-invalid @enderror"
                                               value="{{ $item['description'] ?? '' }}"
                                               placeholder="Optional description">
                                        @error('items.'.$index.'.description')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                    </td>
                                    <td class="text-center">
                                        <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeItem(this)">
                                            <i class="bx bx-trash"></i>
                                        </button>
                    </td>
                </tr>
            @endforeach
                        @else
                            <tr id="no-items-row">
                                <td colspan="5" class="text-center py-4">
                                    <div class="d-flex flex-column align-items-center">
                                        <i class="bx bx-list-ul font-48 text-muted mb-3"></i>
                                        <h6 class="text-muted">No Journal Entries Added</h6>
                                        <p class="text-muted mb-0">Click "Add Entry" to start adding journal items</p>
                                    </div>
                                </td>
                            </tr>
                        @endif
        </tbody>
    </table>
            </div>
        </div>
    </div>

    <!-- Totals Summary -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card border-0 bg-light">
                <div class="card-body text-center">
                    <h6 class="text-muted mb-1">Total Debit</h6>
                    <h4 class="mb-0 text-success" id="total-debit">TZS 0.00</h4>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 bg-light">
                <div class="card-body text-center">
                    <h6 class="text-muted mb-1">Total Credit</h6>
                    <h4 class="mb-0 text-danger" id="total-credit">TZS 0.00</h4>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 bg-light">
                <div class="card-body text-center">
                    <h6 class="text-muted mb-1">Balance</h6>
                    <h4 class="mb-0" id="balance">TZS 0.00</h4>
                    <div id="balance-status" class="mt-2" style="display: none;">
                        <span class="badge bg-success" id="balanced-badge">
                            <i class="bx bx-check-circle me-1"></i>Entry Balanced
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Submit Buttons -->
    <div class="d-flex justify-content-between align-items-center">
        <div>
            @can('view journals')
            <a href="{{ route('accounting.journals.index') }}" class="btn btn-outline-secondary">
                <i class="bx bx-arrow-back me-1"></i>Cancel
            </a>
            @endcan
        </div>
        <div class="d-flex gap-2">
            <button type="button" class="btn btn-outline-primary" onclick="validateAndSubmit()">
                <i class="bx bx-check me-1"></i>Validate Entry
            </button>
            @if(isset($journal))
                @can('edit journal')
                <button type="submit" class="btn btn-outline-primary" id="submitBtn" style="display: none;">
                    <i class="bx bx-save me-1"></i>Update Journal Entry
                </button>
                @endcan
            @else
                @can('create journal')
                <button type="submit" class="btn btn-outline-primary" id="submitBtn" style="display: none;">
                    <i class="bx bx-save me-1"></i>Create Journal Entry
                </button>
                @endcan
            @endif
        </div>
    </div>
</form>

<!-- Include Select2 CSS/JS -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

<script>
// Wait for jQuery to be available
function waitForJQuery(callback) {
    if (typeof jQuery !== 'undefined') {
        callback();
    } else {
        setTimeout(function() {
            waitForJQuery(callback);
        }, 50);
    }
}

// Initialize form when jQuery is ready
waitForJQuery(function() {
    // Load Select2 after jQuery is available
    if (typeof $.fn.select2 === 'undefined') {
        $.getScript('https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js', function() {
            initializeForm();
        });
    } else {
        initializeForm();
    }
});

// Global variable for item index
let itemIndex = {{ count($items) }};

function initializeForm() {
    console.log('Document ready - initializing form...');
    console.log('Initial item count:', itemIndex);
    
    // Initialize Select2 for account dropdowns
    $('.account-select').select2({
        placeholder: 'Select an account',
        allowClear: true,
        width: '100%'
    });

    // Calculate totals on page load with delay to ensure DOM is ready
    setTimeout(function() {
        console.log('Calculating initial totals...');
        calculateTotals();
    }, 300);

    // Add event listeners for amount changes
    $(document).on('input', '.amount-input', function() {
        console.log('Amount changed:', $(this).val());
        calculateTotals();
    });

    // Add event listeners for nature changes
    $(document).on('change', '.nature-select', function() {
        console.log('Nature changed:', $(this).val());
        calculateTotals();
    });
}

    function addItem() {
    const tbody = $('#items-table tbody');
    const newRow = `
        <tr class="journal-item">
            <td>
                <select name="items[${itemIndex}][account_id]" class="form-select account-select" required>
                    <option value="">-- Select Account --</option>
                    @foreach($accounts as $account)
                        <option value="{{ $account->id }}">{{ $account->account_code }} - {{ $account->account_name }}</option>
                    @endforeach
                </select>
            </td>
            <td>
                <select name="items[${itemIndex}][nature]" class="form-select nature-select" required>
                    <option value="debit">Debit</option>
                    <option value="credit">Credit</option>
                </select>
            </td>
            <td>
                <input type="number" step="0.01" name="items[${itemIndex}][amount]" 
                       class="form-control amount-input" placeholder="0.00" required>
            </td>
            <td>
                <input type="text" name="items[${itemIndex}][description]" 
                       class="form-control" placeholder="Optional description">
            </td>
            <td class="text-center">
                <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeItem(this)">
                    <i class="bx bx-trash"></i>
                </button>
            </td>
        </tr>
        `;
    
    // Remove the "no items" row if it exists
    $('#no-items-row').remove();
    
    tbody.append(newRow);
    
    // Initialize Select2 for the new row
    setTimeout(function() {
        tbody.find('.account-select').last().select2({
            placeholder: 'Select an account',
            allowClear: true,
            width: '100%'
        });
    }, 100);
    
    // Add event listeners to the new row
    const newRowElement = tbody.find('tr').last();
    newRowElement.find('.amount-input').on('input', function() {
        console.log('New amount input changed:', $(this).val());
        calculateTotals();
    });
    
    newRowElement.find('.nature-select').on('change', function() {
        console.log('New nature select changed:', $(this).val());
        calculateTotals();
    });
    
        itemIndex++;
    console.log('Added new item, total items:', $('.journal-item').length);
}

function removeItem(button) {
    $(button).closest('tr').remove();
    calculateTotals();
    
    // If no items left, show the "no items" message
    if ($('.journal-item').length === 0) {
        const tbody = $('#items-table tbody');
        tbody.append(`
            <tr id="no-items-row">
                <td colspan="5" class="text-center py-4">
                    <div class="d-flex flex-column align-items-center">
                        <i class="bx bx-list-ul font-48 text-muted mb-3"></i>
                        <h6 class="text-muted">No Journal Entries Added</h6>
                        <p class="text-muted mb-0">Click "Add Entry" to start adding journal items</p>
                    </div>
                </td>
            </tr>
        `);
    }
}

function calculateTotals() {
    let totalDebit = 0;
    let totalCredit = 0;
    
    console.log('Calculating totals...');
    console.log('Found journal items:', $('.journal-item').length);
    
    $('.journal-item').each(function(index) {
        const amountInput = $(this).find('.amount-input');
        const natureSelect = $(this).find('.nature-select');
        
        console.log(`Item ${index + 1}:`);
        console.log('  - Amount input found:', amountInput.length > 0);
        console.log('  - Nature select found:', natureSelect.length > 0);
        
        const amount = parseFloat(amountInput.val()) || 0;
        const nature = natureSelect.val();
        
        console.log(`  - Amount value: ${amountInput.val()}, parsed: ${amount}`);
        console.log(`  - Nature value: ${natureSelect.val()}`);
        
        if (nature === 'debit') {
            totalDebit += amount;
            console.log(`  - Added to debit: ${amount}`);
        } else if (nature === 'credit') {
            totalCredit += amount;
            console.log(`  - Added to credit: ${amount}`);
        }
    });
    
    const balance = totalDebit - totalCredit;
    
    console.log(`Final Totals: Debit = ${totalDebit}, Credit = ${totalCredit}, Balance = ${balance}`);
    
    $('#total-debit').text('TZS ' + totalDebit.toFixed(2));
    $('#total-credit').text('TZS ' + totalCredit.toFixed(2));
    $('#balance').text('TZS ' + balance.toFixed(2));
    
    // Update balance color and show/hide submit button
    if (balance === 0 && $('.journal-item').length > 0) {
        $('#balance').removeClass('text-warning text-danger').addClass('text-success');
        $('#submitBtn').show();
        $('#balance-status').show();
        console.log('Entry is balanced - showing submit button');
    } else {
        if (balance > 0) {
            $('#balance').removeClass('text-success text-danger').addClass('text-warning');
        } else {
            $('#balance').removeClass('text-success text-warning').addClass('text-danger');
        }
        $('#submitBtn').hide();
        $('#balance-status').hide();
        console.log('Entry is not balanced - hiding submit button');
    }
    }

function validateAndSubmit() {
    // Check if form is already submitted
    const form = document.getElementById('journalForm');
    if (form && form.dataset.submitted === "true") {
        return false;
    }
    
    const balance = parseFloat($('#balance').text().replace('TZS ', ''));
    
    if (balance !== 0) {
        Swal.fire({
            title: 'Unbalanced Entry',
            text: 'The journal entry is not balanced. Debit and Credit totals must be equal.',
            icon: 'warning',
            confirmButtonText: 'OK'
        });
    } else {
        Swal.fire({
            title: 'Entry Validated',
            text: 'The journal entry is balanced and ready to save.',
            icon: 'success',
            confirmButtonText: 'OK'
        });
    }
}

    // Calculate totals on page load
    $(document).ready(function() {
        // Wait a bit longer to ensure all elements are properly loaded
        setTimeout(function() {
            console.log('Document ready - calculating totals...');
            calculateTotals();
        }, 500);
    });
    
    // Also calculate totals when window is fully loaded
    $(window).on('load', function() {
        setTimeout(function() {
            console.log('Window loaded - calculating totals...');
            calculateTotals();
        }, 100);
    });

// Form validation before submission
function validateForm() {
    const balance = parseFloat($('#balance').text().replace('TZS ', ''));
    
    if (balance !== 0) {
        Swal.fire({
            title: 'Cannot Save',
            text: 'The journal entry must be balanced before saving. Debit and Credit totals must be equal.',
            icon: 'error',
            confirmButtonText: 'OK'
        });
        return false;
    }
    
    if ($('.journal-item').length === 0) {
        Swal.fire({
            title: 'No Entries',
            text: 'Please add at least one journal entry before saving.',
            icon: 'error',
            confirmButtonText: 'OK'
        });
        return false;
    }
    
    return true;
}
</script>

@push('scripts')
    <script>
        function handleSubmit(form) {
            // Validate form first
            if (!validateForm()) {
                return false;
            }
            
            // Prevent multiple submissions
            if (form.dataset.submitted === "true") return false;
            form.dataset.submitted = "true";

            // Disable ALL submit buttons in this form
            form.querySelectorAll('button[type="submit"]').forEach(btn => {
                btn.disabled = true;
                btn.classList.add('opacity-50', 'cursor-not-allowed');
                btn.setAttribute('aria-disabled', 'true');

                const label = btn.querySelector('.label');
                const spinner = btn.querySelector('.spinner');
                if (label) label.textContent = 'Processing...';
                if (spinner) spinner.classList.remove('hidden');
            });

            // Also disable the validate button
            const validateBtn = document.querySelector('button[onclick="validateAndSubmit()"]');
            if (validateBtn) {
                validateBtn.disabled = true;
                validateBtn.classList.add('opacity-50', 'cursor-not-allowed');
            }

            // Add loading overlay to prevent any further interactions
            const overlay = document.createElement('div');
            overlay.id = 'form-loading-overlay';
            overlay.style.cssText = 'position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.3); z-index: 9999; display: flex; align-items: center; justify-content: center;';
            overlay.innerHTML = '<div style="background: white; padding: 20px; border-radius: 8px; text-align: center;"><i class="bx bx-loader-alt bx-spin" style="font-size: 24px; color: #007bff;"></i><br><span style="margin-top: 10px; display: block;">Processing...</span></div>';
            document.body.appendChild(overlay);

            // Allow the submit to proceed
            return true;
        }

        // Optional safety: prevent Enter-key spamming multiple submits in some browsers
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Enter') {
                const active = document.activeElement;
                // Only submit on Enter when focused on a button or inside a textarea (adjust to your UX)
                if (active && active.tagName !== 'TEXTAREA' && active.type !== 'submit') {
                    // e.preventDefault(); // uncomment if Enter should NOT submit forms
                }
            }
        });
    </script>
@endpush
