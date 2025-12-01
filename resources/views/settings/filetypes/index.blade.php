@extends('layouts.main')

@section('title', 'File Types')

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'File Types', 'url' => '#', 'icon' => 'bx bx-file']
        ]" />
        <h6 class="mb-0 text-uppercase">FILE TYPES</h6>
        <hr/>

        <div class="card radius-10">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h4 class="card-title mb-0">List of File Types</h4>
                    <a href="{{ route('settings.filetypes.create') }}" class="btn btn-primary">
                        <i class="bx bx-plus"></i> Add File Type
                    </a>
                </div>

                <div class="table-responsive">
                    <table class="table table-bordered dt-responsive nowrap" id="fileTypeTable">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Name</th>
                                <th>Created At</th>
                                <th>Updated At</th>
                                <th class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($filetypes as $index => $type)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>{{ $type->name }}</td>
                                    <td>{{ $type->created_at->format('Y-m-d H:i:s') }}</td>
                                    <td>{{ $type->updated_at->format('Y-m-d H:i:s') }}</td>
                                    <td class="text-center">
                                        <a href="{{ route('settings.filetypes.edit', $type) }}" class="btn btn-sm btn-outline-warning">Edit</a>

                                        <form action="{{ route('settings.filetypes.destroy', $type) }}" method="POST" class="d-inline delete-form flex-fill">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger" data-name="{{ $type->name }}">
                                                Delete
                                            </button>
                                        </form>
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
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        $('#fileTypeTable').DataTable({
            responsive: true,
            order: [[1, 'asc']], // Order by name
            pageLength: 10,
            language: {
                search: "",
                searchPlaceholder: "Search file types..."
            },
            columnDefs: [
                { targets: -1, orderable: false, searchable: false, responsivePriority: 1 }
            ]
        });
    });
</script>
@endpush
