@extends('layouts.main')

@section('title', 'Customer Reports')

@section('content')
    <div class="page-wrapper">
        <div class="page-content">
            <x-breadcrumbs-with-icons :links="[
                ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
                ['label' => 'Reports', 'url' => route('reports.index'), 'icon' => 'bx bx-file'],
                ['label' => 'Customer Reports', 'url' => '#', 'icon' => 'bx bx-group']
            ]" />
            <h6 class="mb-0 text-uppercase">CUSTOMER REPORTS</h6>
            <hr />

            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <h4 class="card-title mb-4">Customer Reports</h4>
                            <p class="text-muted">Comprehensive customer reports and analytics.</p>

                            <div class="row">
                                <!-- Customer List Report -->
                                <div class="col-md-6 col-lg-4 mb-4">
                                    <div class="card border-primary">
                                        <div class="card-body text-center">
                                            <div class="mb-3">
                                                <i class="bx bx-list-ul fs-1 text-primary"></i>
                                            </div>
                                            <h5 class="card-title">Customer List Report</h5>
                                            <p class="card-text">Complete list of all customers with their details, loan information, and collateral status.</p>
                                            <a href="{{ route('reports.customers.list') }}" class="btn btn-primary">
                                                <i class="bx bx-file me-1"></i> Generate Report
                                            </a>
                                        </div>
                                    </div>
                                </div>

                                <!-- Customer Activity Report
                                <div class="col-md-6 col-lg-4 mb-4">
                                    <div class="card border-success">
                                        <div class="card-body text-center">
                                            <div class="mb-3">
                                                <i class="bx bx-user-check fs-1 text-success"></i>
                                            </div>
                                            <h5 class="card-title">Customer Activity Report</h5>
                                            <p class="card-text">Track customer activities and transaction history.</p>
                                            <a href="{{ route('reports.customers.activity') }}" class="btn btn-success">
                                                <i class="bx bx-file me-1"></i> Generate Report
                                            </a>
                                        </div>
                                    </div>
                                </div> -->

                                <!-- Customer Performance Report -->
                                <div class="col-md-6 col-lg-4 mb-4">
                                    <div class="card border-info">
                                        <div class="card-body text-center">
                                            <div class="mb-3">
                                                <i class="bx bx-trending-up fs-1 text-info"></i>
                                            </div>
                                            <h5 class="card-title">Customer Performance Report</h5>
                                            <p class="card-text">Analyze customer performance metrics and loan repayment patterns.</p>
                                            <a href="{{ route('reports.customers.performance') }}" class="btn btn-info">
                                                <i class="bx bx-file me-1"></i> Generate Report
                                            </a>
                                        </div>
                                    </div>
                                </div>

                                <!-- Customer Demographics Report -->
                                <div class="col-md-6 col-lg-4 mb-4">
                                    <div class="card border-warning">
                                        <div class="card-body text-center">
                                            <div class="mb-3">
                                                <i class="bx bx-pie-chart-alt fs-1 text-warning"></i>
                                            </div>
                                            <h5 class="card-title">Customer Demographics Report</h5>
                                            <p class="card-text">Demographic analysis of customer base by region, age, and gender.</p>
                                            <a href="{{ route('reports.customers.demographics') }}" class="btn btn-warning">
                                                <i class="bx bx-file me-1"></i> Generate Report
                                            </a>
                                        </div>
                                    </div>
                                </div>

                                {{-- <!-- Customer Risk Assessment Report -->
                                <div class="col-md-6 col-lg-4 mb-4">
                                    <div class="card border-danger">
                                        <div class="card-body text-center">
                                            <div class="mb-3">
                                                <i class="bx bx-shield-alt-2 fs-1 text-danger"></i>
                                            </div>
                                            <h5 class="card-title">Customer Risk Assessment Report</h5>
                                            <p class="card-text">Risk analysis and creditworthiness assessment of customers.</p>
                                            <a href="{{ route('reports.customers.risk-assessment') }}" class="btn btn-danger">
                                                <i class="bx bx-file me-1"></i> Generate Report
                                            </a>
                                        </div>
                                    </div>
                                </div> --}}

                                <!-- Customer Communication Report -->
                                <!-- <div class="col-md-6 col-lg-4 mb-4">
                                    <div class="card border-secondary">
                                        <div class="card-body text-center">
                                            <div class="mb-3">
                                                <i class="bx bx-message-dots fs-1 text-secondary"></i>
                                            </div>
                                            <h5 class="card-title">Customer Communication Report</h5>
                                            <p class="card-text">Track communication history and customer engagement.</p>
                                            <a href="{{ route('reports.customers.communication') }}" class="btn btn-secondary">
                                                <i class="bx bx-file me-1"></i> Generate Report
                                            </a>
                                        </div>
                                    </div>
                                </div> -->
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
