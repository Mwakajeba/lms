@extends('layouts.main')

@section('title', 'BOT Reports')

@section('content')
    <div class="page-wrapper">
        <div class="page-content">
            <x-breadcrumbs-with-icons :links="[
                ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
                ['label' => 'Reports', 'url' => route('reports.index'), 'icon' => 'bx bx-file'],
                ['label' => 'Bot Reports', 'url' => '#', 'icon' => 'bx bx-transfer']
            ]" />
            <h6 class="mb-0 text-uppercase">BOT REPORTS</h6>
            <hr />

            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <h4 class="card-title mb-4">BOT Reports</h4>
                            <p class="text-muted">Bot reports functionality will be implemented here.</p>
                            
                            <div class="row">
                                <div class="col-md-6 col-lg-4 mb-4">
                                    <div class="card border-primary">
                                        <div class="card-body text-center">
                                            <div class="mb-3">
                                                <i class="bx bx-transfer fs-1 text-primary"></i>
                                            </div>
                                            <h5 class="card-title">Balance Sheet Report</h5>
                                            <p class="card-text">All the assets and liabilities of the company.</p>
                                            <a class="btn btn-primary" href="{{ route('reports.bot.balance-sheet') }}">
                                                <i class="bx bx-file me-1"></i> Generate Report
                                            </a>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-6 col-lg-4 mb-4">
                                    <div class="card border-success">
                                        <div class="card-body text-center">
                                            <div class="mb-3">
                                                <i class="bx bx-calendar fs-1 text-success"></i>
                                            </div>
                                            <h5 class="card-title">Statement of Income and Expense</h5>
                                            <p class="card-text">All the income and expenses of the company.</p>
                                            <a class="btn btn-success" href="{{ route('reports.bot.income-statement') }}">
                                                <i class="bx bx-file me-1"></i> Generate Report
                                            </a>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-6 col-lg-4 mb-4">
                                    <div class="card border-warning">
                                        <div class="card-body text-center">
                                            <div class="mb-3">
                                                <i class="bx bx-bar-chart fs-1 text-warning"></i>
                                            </div>
                                            <h5 class="card-title">Sectoral Classification Of MICROFINANCE Loans</h5>
                                            <p class="card-text">All the loans of the company.</p>
                                            <a class="btn btn-warning" href="{{ route('reports.bot.sectoral-loans') }}">
                                                <i class="bx bx-file me-1"></i> Generate Report
                                            </a>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-6 col-lg-4 mb-4">
                                    <div class="card border-info">
                                        <div class="card-body text-center">
                                            <div class="mb-3">
                                                <i class="bx bx-slider fs-1 text-info"></i>
                                            </div>
                                            <h5 class="card-title">Interest Rate Structure</h5>
                                            <p class="card-text">Weighted averages and ranges for straight line and reducing balance.</p>
                                            <a class="btn btn-info" href="{{ route('reports.bot.interest-rates') }}">
                                                <i class="bx bx-file me-1"></i> Generate Report
                                            </a>
                                        </div>
                                    </div>
                                </div>

                                 <div class="col-md-6 col-lg-4 mb-4">
                                    <div class="card border-warning">
                                        <div class="card-body text-center">
                                            <div class="mb-3">
                                                <i class="bx bx-droplet fs-1 text-warning"></i>
                                            </div>
                                            <h5 class="card-title">Computation of Liquid Assets</h5>
                                            <p class="card-text">Quarterly computation and ratios.</p>
                                            <a class="btn btn-warning" href="{{ route('reports.bot.liquid-assets') }}">
                                                <i class="bx bx-file me-1"></i> Generate Report
                                            </a>
                                        </div>
                                    </div>
                                </div>

                                 <div class="col-md-6 col-lg-4 mb-4">
                                    <div class="card border-secondary">
                                        <div class="card-body text-center">
                                            <div class="mb-3">
                                                <i class="bx bx-message-rounded-error fs-1 text-secondary"></i>
                                            </div>
                                            <h5 class="card-title">Complaint Report</h5>
                                            <p class="card-text">Quarterly complaints status and nature.</p>
                                            <a class="btn btn-secondary" href="{{ route('reports.bot.complaints') }}">
                                                <i class="bx bx-file me-1"></i> Generate Report
                                            </a>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-6 col-lg-4 mb-4">
                                    <div class="card border-dark">
                                        <div class="card-body text-center">
                                            <div class="mb-3">
                                                <i class="bx bx-building-house fs-1 text-dark"></i>
                                            </div>
                                            <h5 class="card-title">Deposits & Borrowings (Banks & FIs)</h5>
                                            <p class="card-text">Quarterly deposits and borrowings by institution.</p>
                                            <a class="btn btn-dark" href="{{ route('reports.bot.deposits-borrowings') }}">
                                                <i class="bx bx-file me-1"></i> Generate Report
                                            </a>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-6 col-lg-4 mb-4">
                                    <div class="card border-primary">
                                        <div class="card-body text-center">
                                            <div class="mb-3">
                                                <i class="bx bx-money fs-1 text-primary"></i>
                                            </div>
                                            <h5 class="card-title">Agent Banking Balances</h5>
                                            <p class="card-text">Quarterly agent banking balances in banks & FIs.</p>
                                            <a class="btn btn-primary" href="{{ route('reports.bot.agent-banking') }}">
                                                <i class="bx bx-file me-1"></i> Generate Report
                                            </a>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-6 col-lg-4 mb-4">
                                    <div class="card border-success">
                                        <div class="card-body text-center">
                                            <div class="mb-3">
                                                <i class="bx bx-chart fs-1 text-success"></i>
                                            </div>
                                            <h5 class="card-title">Loans Disbursed by Sector, Gender & Amount</h5>
                                            <p class="card-text">Quarterly loan disbursements by sector and gender.</p>
                                            <a class="btn btn-success" href="{{ route('reports.bot.loans-disbursed') }}">
                                                <i class="bx bx-file me-1"></i> Generate Report
                                            </a>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-6 col-lg-4 mb-4">
                                    <div class="card border-info">
                                        <div class="card-body text-center">
                                            <div class="mb-3">
                                                <i class="bx bx-map fs-1 text-info"></i>
                                            </div>
                                            <h5 class="card-title">Geographical Distribution</h5>
                                            <p class="card-text">Branches, employees & loans by age & gender.</p>
                                            <a class="btn btn-info" href="{{ route('reports.bot.geographical-distribution') }}">
                                                <i class="bx bx-file me-1"></i> Generate Report
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection 