@php
use Vinkla\Hashids\Facades\Hashids;
// Build a JS object of class_id => {from, to} for all account classes
$classRanges = [];
foreach ($accountClasses as $class) {
    $classRanges[$class->id] = [
        'from' => $class->range_from,
        'to' => $class->range_to,
    ];
}
@endphp

<form
    action="{{ isset($chartAccount) ? route('accounting.chart-accounts.update', Hashids::encode($chartAccount->id)) : route('accounting.chart-accounts.store') }}"
    method="POST">
    @csrf
    @if(isset($chartAccount))
        @method('PUT')
    @endif

    <div class="row mb-3">
        <div class="col-md-6">
            <label class="form-label">Select Account Class Group</label>
            <select class="form-select" name="account_class_group_id" required>
                <option value="">-- Choose Account Class Group --</option>
                @foreach($accountClassGroups as $group)
                    <option value="{{ $group->id }}" {{ (old('account_class_group_id') == $group->id || (isset($chartAccount) && $chartAccount->account_class_group_id == $group->id)) ? 'selected' : '' }}>
                        {{ $group->accountClass->name ?? 'N/A' }} - {{ $group->name }}
                    </option>
                @endforeach
            </select>
            @error('account_class_group_id')
                <div class="text-danger">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-md-6">
            <label class="form-label">Account Code (<span style ="color:red" id="range_hint"></span>)</label>
            <div class="input-group">
                <input type="text" class="form-control" name="account_code"
                    id="account_code_input"
                    value="{{ $chartAccount->account_code ?? old('account_code') }}" required
                    placeholder="Choose from above range ...">
                <!-- <span class="input-group-text" id="range_hint"></span> -->
            </div>
            @error('account_code')
                <div class="text-danger">{{ $message }}</div>
            @enderror
        </div>
    </div>

    <div class="mb-3">
        <label class="form-label">Account Name</label>
        <input type="text" class="form-control" name="account_name"
            value="{{ $chartAccount->account_name ?? old('account_name') }}" required
            placeholder="e.g., Cash, Accounts Receivable, etc.">
        @error('account_name')
            <div class="text-danger">{{ $message }}</div>
        @enderror
    </div>

    <div class="row mb-3">
        <div class="col-md-6">
            <div class="form-check">
                <input class="form-check-input" type="checkbox" name="has_cash_flow" value="1" id="has_cash_flow" 
                    {{ (old('has_cash_flow') || (isset($chartAccount) && $chartAccount->has_cash_flow)) ? 'checked' : '' }}>
                <label class="form-check-label" for="has_cash_flow">
                    Has Cash Flow Impact
                </label>
            </div>
            @error('has_cash_flow')
                <div class="text-danger">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-md-6">
            <div class="form-check">
                <input class="form-check-input" type="checkbox" name="has_equity" value="1" id="has_equity" 
                    {{ (old('has_equity') || (isset($chartAccount) && $chartAccount->has_equity)) ? 'checked' : '' }}>
                <label class="form-check-label" for="has_equity">
                    Has Equity Impact
                </label>
            </div>
            @error('has_equity')
                <div class="text-danger">{{ $message }}</div>
            @enderror
        </div>
    </div>

    <!-- Cash Flow Category Dropdown (shown when has_cash_flow is checked) -->
    <div class="mb-3" id="cash_flow_category_div" style="display: {{ (old('has_cash_flow') || (isset($chartAccount) && $chartAccount->has_cash_flow)) ? 'block' : 'none' }};">
        <label class="form-label">Cash Flow Category</label>
        <select class="form-select" name="cash_flow_category_id">
            <option value="">-- Choose Cash Flow Category --</option>
            @foreach($cashFlowCategories as $category)
                <option value="{{ $category->id }}" {{ (old('cash_flow_category_id') == $category->id || (isset($chartAccount) && $chartAccount->cash_flow_category_id == $category->id)) ? 'selected' : '' }}>
                    {{ $category->name }}
                </option>
            @endforeach
        </select>
        @error('cash_flow_category_id')
            <div class="text-danger">{{ $message }}</div>
        @enderror
    </div>

    <!-- Equity Category Dropdown (shown when has_equity is checked) -->
    <div class="mb-3" id="equity_category_div" style="display: {{ (old('has_equity') || (isset($chartAccount) && $chartAccount->has_equity)) ? 'block' : 'none' }};">
        <label class="form-label">Equity Category</label>
        <select class="form-select" name="equity_category_id">
            <option value="">-- Choose Equity Category --</option>
            @foreach($equityCategories as $category)
                <option value="{{ $category->id }}" {{ (old('equity_category_id') == $category->id || (isset($chartAccount) && $chartAccount->equity_category_id == $category->id)) ? 'selected' : '' }}>
                    {{ $category->name }}
                </option>
            @endforeach
        </select>
        @error('equity_category_id')
            <div class="text-danger">{{ $message }}</div>
        @enderror
    </div>

    <div class="d-flex justify-content-end">
        <a href="{{ route('accounting.chart-accounts.index') }}" class="btn btn-secondary me-2">Cancel</a>
        <button type="submit" class="btn btn-{{ isset($chartAccount) ? 'primary' : 'success' }}">
            {{ isset($chartAccount) ? 'Update Account' : 'Create Account' }}
        </button>
    </div>
</form>

<script>
document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM loaded, initializing form...');
    
    // Get elements
    const cashFlowCheckbox = document.getElementById('has_cash_flow');
    const equityCheckbox = document.getElementById('has_equity');
    const cashFlowDiv = document.getElementById('cash_flow_category_div');
    const equityDiv = document.getElementById('equity_category_div');
    const groupSelect = document.querySelector('select[name="account_class_group_id"]');
    const rangeHint = document.getElementById('range_hint');
    const accountCodeInput = document.getElementById('account_code_input');
    
    // Build mapping of group_id => class_id
    const groupToClass = {};
    @foreach($accountClassGroups as $group)
        groupToClass[{{ $group->id }}] = {{ $group->class_id }};
    @endforeach
    // Build mapping of class_id => {from, to}
    const classRanges = @json($classRanges);

    console.log('Elements found:', {
        cashFlowCheckbox: cashFlowCheckbox,
        equityCheckbox: equityCheckbox,
        cashFlowDiv: cashFlowDiv,
        equityDiv: equityDiv,
        groupSelect: groupSelect,
        rangeHint: rangeHint,
        accountCodeInput: accountCodeInput
    });

    // Function to toggle cash flow category dropdown
    function toggleCashFlowCategory() {
        console.log('Cash flow checkbox changed:', cashFlowCheckbox.checked);
        if (cashFlowCheckbox.checked) {
            cashFlowDiv.style.display = 'block';
            console.log('Cash flow dropdown shown');
        } else {
            cashFlowDiv.style.display = 'none';
            // Clear the selection when hiding
            const select = cashFlowDiv.querySelector('select');
            if (select) {
                select.value = '';
            }
            console.log('Cash flow dropdown hidden and cleared');
        }
    }

    // Function to toggle equity category dropdown
    function toggleEquityCategory() {
        console.log('Equity checkbox changed:', equityCheckbox.checked);
        if (equityCheckbox.checked) {
            equityDiv.style.display = 'block';
            console.log('Equity dropdown shown');
        } else {
            equityDiv.style.display = 'none';
            // Clear the selection when hiding
            const select = equityDiv.querySelector('select');
            if (select) {
                select.value = '';
            }
            console.log('Equity dropdown hidden and cleared');
        }
    }

    // Function to update account code range hint
    function updateRangeHint() {
        const selectedGroupId = groupSelect.value;
        const classId = groupToClass[selectedGroupId];
        if (classRanges[classId] && classRanges[classId].from !== null && classRanges[classId].from !== undefined &&
            classRanges[classId].to !== null && classRanges[classId].to !== undefined) {
            rangeHint.textContent = `Range: ${classRanges[classId].from} - ${classRanges[classId].to}`;
        } else {
            rangeHint.textContent = '';
        }
    }

    // Add event listeners
    if (cashFlowCheckbox) {
        cashFlowCheckbox.addEventListener('change', toggleCashFlowCategory);
        console.log('Cash flow event listener added');
    }
    
    if (equityCheckbox) {
        equityCheckbox.addEventListener('change', toggleEquityCategory);
        console.log('Equity event listener added');
    }

    if (groupSelect) {
        groupSelect.addEventListener('change', updateRangeHint);
        console.log('Group select event listener added');
    }

    // Initialize on page load
    toggleCashFlowCategory();
    toggleEquityCategory();
    updateRangeHint();
    console.log('Initial toggle functions called');
});
</script>