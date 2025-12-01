@extends('layouts.main')

@php
    use Vinkla\Hashids\Facades\Hashids;
@endphp

@section('title', 'Account Class Groups')
@section('content')
    <div class="page-wrapper">
        <div class="page-content">
            <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'Account Class Groups', 'url' => '#', 'icon' => 'bx bx-category']
        ]" />

            <div class="row row-cols-1 row-cols-lg-3">
                <div class="col">
                    <div class="card radius-10">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="flex-grow-1">
                                    <p class="mb-0">Total</p>
                                    <h4 class="font-weight-bold">{{ $accountClassGroups->count() }}</h4>
                                </div>
                                <div class="widgets-icons bg-gradient-cosmic text-white"><i class='bx bx-refresh'></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!--end row-->

            <h6 class="mb-0 text-uppercase">ACCOUNT CLASS GROUPS </h6>
            <hr />
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="mb-0">Chart of Accounts - Account Class Groups</h5>
                        @can('create account class group')
                        <div>
                            <a href="{{ route('accounting.account-class-groups.create') }}" class="btn btn-primary ms-2">
                                <i class="bx bx-plus"></i> Add New Group
                            </a>
                        </div>
                        @endcan
                    </div>
                    <div class="table-responsive">
                        <table id="accountClassGroupsTable" class="table table-striped table-bordered" style="width:100%">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Account Class</th>
                                    <th>Group Code</th>
                                    <th>Name</th>
                                    <th>Created At</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($accountClassGroups as $index => $group)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>{{ $group->accountClass->name ?? 'N/A' }}</td>
                                        <td>{{ $group->group_code ?? 'N/A' }}</td>
                                        <td>{{ $group->name }}</td>
                                        <td>{{ $group->created_at->format('M d, Y') }}</td>
                                        <td>
                                            @can('view account class group details')
                                            <a href="{{ route('accounting.account-class-groups.show', Hashids::encode($group->id)) }}"
                                                class="btn btn-sm btn-outline-primary">View</a>
                                            @endcan
                                            @can('edit account class group')
                                            <a href="{{ route('accounting.account-class-groups.edit', Hashids::encode($group->id)) }}"
                                                class="btn btn-sm btn-outline-warning">Edit</a>
                                            @endcan
                                            @can('delete account class group')
                                            <form
                                                action="{{ route('accounting.account-class-groups.destroy', Hashids::encode($group->id)) }}"
                                                method="POST" class="d-inline delete-form">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger"
                                                    data-name="{{ $group->name }}">Delete</button>
                                            </form>
                                            @endcan
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!--end page wrapper -->
    <!--start overlay-->
    <div class="overlay toggle-icon"></div>
    <!--end overlay-->
    <!--Start Back To Top Button--> <a href="javaScript:;" class="back-to-top"><i class='bx bxs-up-arrow-alt'></i></a>
    <!--End Back To Top Button-->
    <footer class="page-footer">
        <p class="mb-0">Copyright © 2021. All right reserved.</p>
    </footer>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    $('#accountClassGroupsTable').DataTable({
        responsive: true,
        paging: true,
        searching: true,
        ordering: true,
        info: true,
        lengthChange: true,
        pageLength: 10,
        language: {
            search: "",
            searchPlaceholder: "Search account class groups..."
        }
    });
});
</script>
@endpush