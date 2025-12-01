@extends('layouts.main')

@section('title', 'BOT Computation of Liquid Assets')

@section('content')
    <div class="page-wrapper">
        <div class="page-content">
            <x-breadcrumbs-with-icons :links="[
                ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
                ['label' => 'Reports', 'url' => route('reports.index'), 'icon' => 'bx bx-file'],
                ['label' => 'BOT Reports', 'url' => route('reports.bot'), 'icon' => 'bx bx-transfer'],
                ['label' => 'Liquid Assets', 'url' => '#', 'icon' => 'bx bx-list-ul']
            ]" />
            <h6 class="mb-0 text-uppercase">BOT Computation of Liquid Assets for the Quarter Ended</h6>
            <hr />

            <style>
                .bot-container { background: #fff; padding: 16px; border: 1px solid #e5e7eb; }
                .bot-header { display: grid; grid-template-columns: 1fr 300px; gap: 12px; margin-bottom: 12px; }
                .bot-title { font-weight: 700; font-size: 14px; line-height: 1.2; }
                .bot-sub { font-size: 12px; color: #374151; }
                .bot-meta { border: 1px solid #111827; }
                .bot-meta-row { display: grid; grid-template-columns: 140px 1fr; border-bottom: 1px solid #111827; }
                .bot-meta-row:last-child { border-bottom: none; }
                .bot-meta-cell { padding: 6px 8px; font-size: 12px; }
                .bot-meta-label { background: #f3f4f6; font-weight: 600; border-right: 1px solid #111827; }
                .bot-table { width: 100%; border-collapse: collapse; font-size: 12px; }
                .bot-table th, .bot-table td { border: 1px solid #111827; padding: 6px 8px; vertical-align: top; }
                .bot-table thead th { background: #f3f4f6; font-weight: 700; text-transform: uppercase; font-size: 12px; }
                .col-sno { width: 50px; text-align: center; }
                .col-particular { width: 60%; }
                .col-num { width: 200px; text-align: right; font-weight: 600; }
                .note { font-size: 11px; color: #6b7280; }
                .top-actions { display: flex; gap: 8px; align-items: end; flex-wrap: wrap; }
                .section { background: #e5e7eb; font-weight: 700; }
                .total-row { background: #fef3c7; font-weight: 700; }
            </style>

            <div class="card">
                <div class="card-body">
                    <form method="GET" action="{{ route('reports.bot.liquid-assets') }}" class="row g-3 align-items-end">
                        <div class="col-md-3">
                            <label for="as_of_date" class="form-label">As at Date</label>
                            <input type="date" id="as_of_date" name="as_of_date" value="{{ $asOfDate }}" class="form-control" required>
                        </div>
                        <div class="col-md-9 top-actions">
                            <button type="submit" class="btn btn-warning"><i class="bx bx-filter me-1"></i> Filter</button>
                            <a href="{{ route('reports.bot.liquid-assets.export', ['as_of_date' => $asOfDate]) }}" class="btn btn-outline-secondary">
                                <i class="bx bx-download me-1"></i> Export Excel (XLSX)
                            </a>
                            <a href="{{ route('reports.bot') }}" class="btn btn-light"><i class="bx bx-arrow-back me-1"></i> Back</a>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card">
                <div class="card-body bot-container">
                    <div class="bot-header">
                        <div>
                            <div class="bot-title">NAME OF INSTITUTION:</div>
                            <div class="bot-sub" style="margin-top: 6px;">BOT Form MSP2-05 To be submitted Quarterly</div>
                            <div class="bot-sub">(Amount in TZS)</div>
                        </div>
                        <div class="bot-meta">
                            <div class="bot-meta-row">
                                <div class="bot-meta-cell bot-meta-label">MSP CODE</div>
                                <div class="bot-meta-cell">{{ auth()->user()->company->msp_code ?? '—' }}</div>
                            </div>
                            <div class="bot-meta-row">
                                <div class="bot-meta-cell bot-meta-label">DATE</div>
                                <div class="bot-meta-cell">{{ \Carbon\Carbon::parse($asOfDate)->format('d/m/Y') }}</div>
                            </div>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="bot-table">
                            <thead>
                                <tr>
                                    <th class="col-sno">Sno</th>
                                    <th class="col-particular">REQUIRED MINIMUM AMOUNT OF LIQUID ASSETS</th>
                                    <th class="col-num">AMOUNT</th>
                                    <th class="col-num">VALIDATION</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr class="section"><td>A:</td><td class="fw-bold">TOTAL AVAILABLE LIQUID ASSETS</td><td class="col-num">TZS {{ number_format($liquidAssetsData['total_available_liquid_assets'], 2) }}</td><td></td></tr>
                                <tr><td>(a)</td><td>Cash in hand</td><td class="col-num">TZS {{ number_format(0, 2) }}</td><td></td></tr>
                                <tr><td>(b)</td><td>Balances with Banks and Financial Institutions</td><td class="col-num">TZS {{ number_format($liquidAssetsData['bank_accounts']->sum('balance'), 2) }}</td><td></td></tr>
                                <tr><td>(c)</td><td>Cash Collateral</td><td class="col-num">TZS {{ number_format($liquidAssetsData['cash_collateral'], 2) }}</td><td></td></tr>
                                <tr><td>(d)</td><td>MNOs Float Cash Balances</td><td class="col-num">TZS {{ number_format(0, 2) }}</td><td></td></tr>
                                <tr><td>(e)</td><td>Treasury Bills (Unencumbered)</td><td class="col-num">TZS {{ number_format(0, 2) }}</td><td></td></tr>
                                <tr><td>(f)</td><td>Other Government Securities with Residual Maturity of One Year or Less (Unencumbered)</td><td class="col-num">TZS {{ number_format(0, 2) }}</td><td></td></tr>
                                <tr><td>(g)</td><td>Private Securities with Residual Maturity of One Year or Less (Unencumbered)</td><td class="col-num">TZS {{ number_format(0, 2) }}</td><td></td></tr>
                                <tr><td>(h)</td><td>Other Liquid Assets Maturing within 12 Months</td><td class="col-num">TZS {{ number_format(0, 2) }}</td><td></td></tr>

                                <tr class="total-row"><td>B.</td><td class="fw-bold">TOTAL ASSETS</td><td class="col-num">TZS {{ number_format($liquidAssetsData['total_assets'], 2) }}</td><td></td></tr>
                                <tr class="total-row"><td>C.</td><td class="fw-bold">Required Minimum Liquid Assets (20%*B)</td><td class="col-num">TZS {{ number_format($liquidAssetsData['required_minimum_liquid_assets'], 2) }}</td><td></td></tr>
                                <tr class="total-row"><td>D.</td><td class="fw-bold">Excess (Deficiency) Liquid Assets (A-C)</td><td class="col-num">TZS {{ number_format($liquidAssetsData['excess_deficiency'], 2) }}</td><td></td></tr>
                                <tr class="total-row"><td>E.</td><td class="fw-bold">Liquid Asset Ratio (A / B)</td><td class="col-num">{{ number_format($liquidAssetsData['liquid_asset_ratio'], 2) }}%</td><td></td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            
            <!-- Debug Section -->
            <div class="card mt-3">
                <div class="card-body">
                    <h6 class="card-title">Current Data</h6>
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Total Available Liquid Assets:</strong> TZS {{ number_format($liquidAssetsData['total_available_liquid_assets'], 2) }}</p>
                            <p><strong>Total Assets:</strong> TZS {{ number_format($liquidAssetsData['total_assets'], 2) }}</p>
                            <p><strong>Required Minimum (20%):</strong> TZS {{ number_format($liquidAssetsData['required_minimum_liquid_assets'], 2) }}</p>
                            <p><strong>Excess/Deficiency:</strong> TZS {{ number_format($liquidAssetsData['excess_deficiency'], 2) }}</p>
                            <p><strong>Liquid Asset Ratio:</strong> {{ number_format($liquidAssetsData['liquid_asset_ratio'], 2) }}%</p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Bank Accounts:</strong></p>
                            <ul>
                                @foreach($liquidAssetsData['bank_accounts'] as $account)
                                    <li>{{ $account['name'] }}: TZS {{ number_format($account['balance'], 2) }}</li>
                                @endforeach
                            </ul>
                            <p><strong>Cash Collateral:</strong> TZS {{ number_format($liquidAssetsData['cash_collateral'], 2) }}</p>
                            <p><strong>Loans Outstanding:</strong> TZS {{ number_format($liquidAssetsData['loans_outstanding'], 2) }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection 