@extends('layouts.main')

@section('title', $pageTitle ?? 'Activity Logs')

@section('content')
<div class="page-wrapper">
    <div class="page-content">

        <!-- Breadcrumbs -->
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
                ['label' => 'Settings', 'url' => route('settings.index'), 'icon' => 'bx bx-cog'],
            ['label' => $pageTitle ?? 'Activity Logs', 'url' => '#', 'icon' => 'bx bx-list-ul']
        ]" />

        <h6 class="mb-0 text-uppercase">{{ $pageTitle ?? 'ACTIVITY LOGS' }}</h6>
        <hr />

        <!-- Summary Cards -->
        <div class="row row-cols-1 row-cols-lg-4">
            <div class="col mb-4">
                <div class="card radius-10">
                    <div class="card-body d-flex align-items-center">
                        <div class="flex-grow-1">
                            <p class="text-muted mb-1">Total Logs</p>
                            <h4 class="mb-0">{{ $logs->count() }}</h4>
                        </div>
                        <div class="widgets-icons bg-gradient-burning text-white">
                            <i class='bx bx-history'></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Logs Table -->
        <div class="row">
            <div class="col-12">
                <div class="card radius-10">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered dt-responsive nowrap" id="logsTable">
                                <thead class="table-light">
                                    <tr>
                                        <th>Date</th>
                                        <th>User</th>
                                        <th>Model</th>
                                        <th>Action</th>
                                        <th>IP Address</th>
                                        <th>Device</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($logs as $log)
                                    <tr>
                                        <td>{{ $log->activity_time->format('Y-m-d H:i') }}</td>
                                        <td>{{ $log->user->name ?? 'Guest' }}</td>
                                        <td>{{ $log->model }}</td>
                                        <td>{{ ucfirst(str_replace('_', ' ', $log->action)) }}</td>
                                        <td>{{ $log->ip_address }}</td>
                                        <td>{{ $log->device }}</td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="6" class="text-center text-muted">No activity logs found</td>
                                    </tr>
                                    @endforelse
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
@push('scripts')
<script>
    $(document).ready(function() {
        $('#logsTable').DataTable({
            responsive: true,
            order: [
                [0, 'asc']
            ],
            pageLength: 10,
            language: {
                search: "",
                searchPlaceholder: "Search types..."
            },
            columnDefs: [{
                    targets: -1,
                    orderable: false,
                    searchable: false,
                    responsivePriority: 1
                },
                {
                    targets: [0, 1],
                    responsivePriority: 2
                }
            ]
        });
    });
</script>
@endpush