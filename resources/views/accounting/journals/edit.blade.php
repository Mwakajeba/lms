@extends('layouts.main')

@section('title', 'Edit Journal Entry')

@section('content')
    <div class="page-wrapper">
        <div class="page-content">
        <!-- Breadcrumb -->
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'Journal Entries', 'url' => route('accounting.journals.index'), 'icon' => 'bx bx-book-open'],
            ['label' => 'Journal Entry #' . $journal->reference, 'url' => route('accounting.journals.show', $journal), 'icon' => 'bx bx-show'],
            ['label' => 'Edit Entry', 'url' => '#', 'icon' => 'bx bx-edit']
        ]" />
        <h6 class="mb-0 text-uppercase">EDIT JOURNAL ENTRY</h6>
        <hr />

        <!-- Page Header -->
        <div class="row">
            <div class="col-12">
                <div class="card border-top border-0 border-4 border-warning">
                    <div class="card-body p-5">
                        <div class="row align-items-center">
                            <div class="col-md-8">
                                <div class="card-title d-flex align-items-center">
                                    <div><i class="bx bx-edit me-1 font-22 text-warning"></i></div>
                                    <h5 class="mb-0 text-warning">Edit Journal Entry</h5>
                                </div>
                                <p class="mb-0 text-muted">Update journal entry details and transactions</p>
                            </div>
                            <div class="col-md-4 text-end">
                                <div class="d-flex gap-2 justify-content-end">
                                    <a href="{{ route('accounting.journals.show', $journal) }}" class="btn btn-outline-primary">
                                        <i class="bx bx-show me-1"></i> View Details
                                    </a>
                                    <a href="{{ route('accounting.journals.index') }}" class="btn btn-outline-secondary">
                                        <i class="bx bx-arrow-back me-1"></i> Back to Journals
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Journal Entry Form -->
        <div class="row">
            <div class="col-12">
                <div class="card radius-10 border-0 shadow-sm">
                    <div class="card-header bg-transparent border-0">
                        <h6 class="mb-0"><i class="bx bx-edit me-2"></i>Edit Journal Entry Details</h6>
                    </div>
                    <div class="card-body">
                        @include('accounting.journals.form')
                    </div>
                </div>
            </div>
        </div>
        </div>
    </div>
@endsection