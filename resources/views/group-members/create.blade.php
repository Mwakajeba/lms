@php
    use Vinkla\Hashids\Facades\Hashids;
@endphp

@extends('layouts.main')

@section('title', 'Add Member to Group')

@section('content')
    <div class="page-wrapper">
        <div class="page-content">
            <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'Groups', 'url' => route('groups.index'), 'icon' => 'bx bx-group'],
            ['label' => $group->name, 'url' => route('groups.show', Hashids::encode($group->id)), 'icon' => 'bx bx-info-circle'],
            ['label' => 'Add Member', 'url' => '#', 'icon' => 'bx bx-plus-circle']
        ]" />

            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <div class="d-flex justify-content-between align-items-center">
                                <h5 class="mb-0">Add Members to: {{ $group->name }}</h5>
                                <a href="{{ route('groups.show', Hashids::encode($group->id)) }}"
                                    class="btn btn-secondary btn-sm">
                                    <i class="bx bx-arrow-back"></i> Back
                                </a>
                            </div>
                        </div>
                        <div class="card-body">
                            @if($errors->any())
                                <div class="alert alert-danger">
                                    <ul class="mb-0">
                                        @foreach($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif

                            @if($isFirstMember && $groupLeader)
                                <div class="alert alert-info">
                                    <i class="bx bx-info-circle me-2"></i>
                                    <strong>Important:</strong> This is the first member of the group.
                                    The group leader <strong>{{ $groupLeader->name }}</strong> should be included as the first
                                    member.
                                </div>
                            @endif

                            <div class="alert alert-info">
                                <i class="bx bx-info-circle me-2"></i>
                                <strong>Available Customers:</strong> Only customers who are not in any group or are in the
                                "Individual" group are shown below.
                                Customers in other regular groups are not available for selection.
                            </div>

                            @if($availableCustomers->count() > 0)
                                <form action="{{ route('group-members.store', Hashids::encode($group->id)) }}" method="POST"
                                    id="addMembersForm">
                                    @csrf

                                    <div class="row mb-4">
                                        <div class="col-md-8">
                                            <select id="customerSelect" class="form-select select2-single">
                                                <option value="">Select Customer</option>
                                                @foreach($availableCustomers as $customer)
                                                    <option value="{{ $customer->id }}"
                                                        data-name="{{ $customer->name ?? 'Unknown' }}"
                                                        data-gender="{{ $customer->sex ?? 'N/A' }}"
                                                        data-region="{{ $customer->region && $customer->region->name ? $customer->region->name : 'N/A' }}"
                                                        data-district="{{ $customer->district && $customer->district->name ? $customer->district->name : 'N/A' }}"
                                                        data-phone="{{ $customer->phone1 ?? 'N/A' }}"
                                                        data-current-group="{{ $customer->current_group ? $customer->current_group->group_name : 'No Group' }}">
                                                        {{ $customer->name ?? 'Unknown' }} - {{ $customer->phone1 ?? 'No phone' }}
                                                        @if($customer->current_group)
                                                            (Currently in: {{ $customer->current_group->group_name }})
                                                        @endif
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-4">
                                            <button type="button" class="btn btn-primary w-100" onclick="addCustomerToList()">
                                                <i class="bx bx-plus"></i> Add
                                            </button>
                                        </div>
                                    </div>

                                    <div class="selected-customers-section">
                                        <div class="d-flex justify-content-between align-items-center mb-3">
                                            <h6 class="mb-0">Selected Customers (<span id="selectedCount">0</span>)</h6>
                                            <button type="submit" class="btn btn-success btn-sm" id="submitBtn" disabled>
                                                <i class="bx bx-save"></i> Add to Group
                                            </button>
                                        </div>

                                        <div id="selectedCustomersList" class="selected-customers-list">
                                            <div class="empty-state">
                                                <i class="bx bx-user-plus"></i>
                                                <p>No customers selected</p>
                                            </div>
                                        </div>
                                    </div>
                                </form>
                            @else
                                <div class="text-center py-4">
                                    <i class="bx bx-user-x text-muted" style="font-size: 3rem;"></i>
                                    <h5 class="text-muted mt-3">No Available Customers</h5>
                                    <p class="text-muted">All customers are either already members of this group or are in other
                                        regular groups.</p>
                                    <p class="text-muted">Only customers who are not in any group or are in the "Individual"
                                        group can be added.</p>
                                    <a href="{{ route('groups.show', Hashids::encode($group->id)) }}" class="btn btn-primary">
                                        <i class="bx bx-arrow-back"></i> Back to Group
                                    </a>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
    <style>
        .card {
            border: none;
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
        }

        .card-header {
            background-color: #f8f9fa;
            border-bottom: 1px solid #dee2e6;
            padding: 1rem 1.5rem;
        }

        .card-body {
            padding: 1.5rem;
        }

        .form-select {
            border-radius: 6px;
            border: 1px solid #dee2e6;
            padding: 0.75rem 1rem;
            font-size: 0.875rem;
        }

        .form-select:focus {
            border-color: #0d6efd;
            box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.15);
        }

        .btn {
            border-radius: 6px;
            font-weight: 500;
            transition: all 0.2s ease;
        }

        .btn:hover {
            transform: translateY(-1px);
        }

        .selected-customers-section {
            border: 1px solid #e9ecef;
            border-radius: 8px;
            padding: 1.5rem;
            background-color: #f8f9fa;
        }

        .selected-customers-list {
            min-height: 120px;
        }

        .empty-state {
            text-align: center;
            padding: 2rem 1rem;
            color: #6c757d;
        }

        .empty-state i {
            font-size: 2rem;
            margin-bottom: 1rem;
            display: block;
        }

        .empty-state p {
            margin-bottom: 0;
            font-size: 0.875rem;
        }

        .table th {
            background-color: #f8f9fa;
            border-bottom: 2px solid #dee2e6;
            font-weight: 600;
            font-size: 0.875rem;
        }

        .table td {
            vertical-align: middle;
            font-size: 0.875rem;
        }

        .avatar-sm {
            width: 2.5rem;
            height: 2.5rem;
        }

        .bg-light-primary {
            background-color: rgba(13, 110, 253, 0.1) !important;
            color: #0d6efd !important;
        }

        .badge {
            font-size: 0.75em;
            font-weight: 500;
        }

        .alert {
            border-radius: 6px;
            border: none;
        }

        .alert-danger {
            background-color: #f8d7da;
            color: #721c24;
        }
    </style>
@endpush

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            if (window.jQuery) {
                $('#customerSelect.select2-single').select2({
                    placeholder: 'Select Customer',
                    allowClear: true,
                    width: '100%',
                    theme: 'bootstrap-5'
                });
            }
        });

        let selectedCustomers = [];

        function addCustomerToList() {
            const select = document.getElementById('customerSelect');
            const selectedOption = select.options[select.selectedIndex];

            if (!selectedOption.value) {
                alert('Please select a customer first.');
                return;
            }

            const customerId = selectedOption.value;

            // Check if customer is already in the list
            if (selectedCustomers.some(c => c.id === customerId)) {
                alert('This customer is already in the list.');
                return;
            }

            const customer = {
                id: customerId,
                name: selectedOption.dataset.name,
                gender: selectedOption.dataset.gender,
                region: selectedOption.dataset.region,
                district: selectedOption.dataset.district,
                phone: selectedOption.dataset.phone,
                currentGroup: selectedOption.dataset.currentGroup
            };

            selectedCustomers.push(customer);
            updateSelectedCustomersList();
            updateSelectedCount();
            updateSubmitButton();

            // Reset select
            select.value = '';
        }

        function removeCustomerFromList(customerId) {
            selectedCustomers = selectedCustomers.filter(c => c.id !== customerId);
            updateSelectedCustomersList();
            updateSelectedCount();
            updateSubmitButton();
        }

        function updateSelectedCustomersList() {
            const container = document.getElementById('selectedCustomersList');

            if (selectedCustomers.length === 0) {
                container.innerHTML = `
                                                                <div class="empty-state">
                                                                    <i class="bx bx-user-plus"></i>
                                                                    <p>No customers selected</p>
                                                                </div>
                                                            `;
                return;
            }

            let html = `
                                                            <div class="table-responsive">
                                                                <table class="table table-hover">
                                                                    <thead>
                                                                        <tr>
                                                                            <th>Customer</th>
                                                                            <th>Gender</th>
                                                                            <th>Region</th>
                                                                            <th>District</th>
                                                                            <th>Phone</th>
                                                                            <th>Current Group</th>
                                                                            <th class="text-center">Actions</th>
                                                                        </tr>
                                                                    </thead>
                                                                    <tbody>
                                                        `;

            selectedCustomers.forEach(customer => {
                html += `
                                                                <tr>
                                                                    <td>
                                                                        <div class="d-flex align-items-center">
                                                                            <div class="avatar-sm bg-light-primary rounded-circle d-flex align-items-center justify-content-center me-2">
                                                                                <i class="bx bx-user font-size-16"></i>
                                                                            </div>
                                                                            <div>
                                                                                <strong>${customer.name}</strong>
                                                                            </div>
                                                                        </div>
                                                                    </td>
                                                                    <td>
                                                                        <span class="badge bg-info">${customer.gender}</span>
                                                                    </td>
                                                                    <td>${customer.region}</td>
                                                                    <td>${customer.district}</td>
                                                                    <td>${customer.phone}</td>
                                                                    <td>
                                                                        <span class="badge ${customer.currentGroup === 'Individual' ? 'bg-warning' : customer.currentGroup === 'No Group' ? 'bg-success' : 'bg-info'}">
                                                                            ${customer.currentGroup}
                                                                        </span>
                                                                    </td>
                                                                    <td class="text-center">
                                                                        <div class="btn-group" role="group">
                                                                            <button type="button" class="btn btn-sm btn-outline-danger"
                                                                                onclick="removeCustomerFromList('${customer.id}')"
                                                                                title="Remove from list">
                                                                                <i class="bx bx-trash"></i>
                                                                            </button>
                                                                        </div>
                                                                    </td>
                                                                </tr>
                                                            `;
            });

            html += `
                                                                    </tbody>
                                                                </table>
                                                            </div>
                                                        `;

            container.innerHTML = html;
        }

        function updateSelectedCount() {
            document.getElementById('selectedCount').textContent = selectedCustomers.length;
        }

        function updateSubmitButton() {
            const submitBtn = document.getElementById('submitBtn');
            submitBtn.disabled = selectedCustomers.length === 0;
        }

        // Handle form submission
        document.getElementById('addMembersForm').addEventListener('submit', function (e) {
            if (selectedCustomers.length === 0) {
                e.preventDefault();
                alert('Please select at least one customer.');
                return;
            }

            // Add hidden inputs for selected customers
            selectedCustomers.forEach(customer => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'customer_ids[]';
                input.value = customer.id;
                this.appendChild(input);
            });
        });

        // Initialize
        updateSelectedCount();
        updateSubmitButton();
    </script>
@endpush