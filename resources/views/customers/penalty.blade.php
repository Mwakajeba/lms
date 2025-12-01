@extends('layouts.main')

@section('title', 'Customer Penalty List') 

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'Penalty List', 'url' => '#', 'icon' => 'bx bx-error']
        ]" />
        <h6 class="mb-0 text-uppercase">CUSTOMER PENALTY LIST</h6>
        <hr />
   

        <!-- Customers Table -->
        <div class="row">
            <div class="col-12">
                <div class="card radius-10">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered nowrap" id="customersTable">
                                <thead>
                                    <tr>
                                        <th>Customer Name</th>
                                        <th>Phone</th>
                                        <th>Penalty Amount</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {{-- Loop through the $customerPenalties collection --}}
                                    @foreach($customerPenalties as $penaltyItem)
                                    <tr>
                                        <td>{{ $penaltyItem['customer_name'] }}</td> 
                                        <td>{{ $penaltyItem['customer_phone'] }}</td> 
                                        <td>{{ number_format($penaltyItem['penalty_balance'], 2) }}</td> 
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection

{{-- Include jQuery before DataTables script --}}
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
{{-- You might also need the DataTables JS library itself if it's not included in layouts.main --}}
{{-- Example: <script src="https://cdn.datatables.net/1.11.3/js/jquery.dataTables.min.js"></script> --}}


@push('scripts')
<script>
    $(document).ready(function() {
        $('#customersTable').DataTable({
            responsive: false,
            order: [
                [0, 'asc'] 
            ],
            pageLength: 10,
            language: {
                search: "",
                searchPlaceholder: "Search customers..."
            },
            columnDefs: [
                {
                    targets: -1,
                    orderable: true, 
                    searchable: false 
                },
                {
                    targets: [0, 1, 2],
                    responsivePriority: 2 
                }
            ]
        });
    });
</script>
@endpush
