@extends('layouts.main')

@section('title', 'BOT Sectoral Classification of Loans')

@section('content')
    <div class="page-wrapper">
        <div class="page-content">
            <x-breadcrumbs-with-icons :links="[
                ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
                ['label' => 'Reports', 'url' => route('reports.index'), 'icon' => 'bx bx-file'],
                ['label' => 'BOT Reports', 'url' => route('reports.bot'), 'icon' => 'bx bx-transfer'],
                ['label' => 'Sectoral Classification', 'url' => '#', 'icon' => 'bx bx-list-ul']
            ]" />
            <h6 class="mb-0 text-uppercase">BOT Sectoral Classification Of MICROFINANCE Loans</h6>
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
                .col-sector { width: 220px; }
                .col-num { width: 140px; text-align: right; font-weight: 600; }
                .section { background: #e5e7eb; font-weight: 700; }
                .total-row { background: #fef3c7; font-weight: 700; }
                .note { font-size: 11px; color: #6b7280; }
                .top-actions { display: flex; gap: 8px; align-items: end; flex-wrap: wrap; }
            </style>

            <div class="card">
                <div class="card-body">
                    <form method="GET" action="{{ route('reports.bot.sectoral-loans') }}" class="row g-3 align-items-end">
                        <div class="col-md-3">
                            <label for="as_of_date" class="form-label">As at Date</label>
                            <input type="date" id="as_of_date" name="as_of_date" value="{{ $asOfDate }}" class="form-control" required>
                        </div>
                        <div class="col-md-9 top-actions">
                            <button type="submit" class="btn btn-warning"><i class="bx bx-filter me-1"></i> Filter</button>
                            <a href="{{ route('reports.bot.sectoral-loans.export', ['as_of_date' => $asOfDate]) }}" class="btn btn-outline-secondary">
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
                            <div class="bot-title" style="margin-top: 8px;">SECTORAL:</div>
                            <div class="bot-sub" style="margin-top: 6px;">BOT FORM MSP2-03 to be submitted Quarterly (Amount in TZS)</div>
                        </div>
                        <div class="bot-meta">
                            <div class="bot-meta-row">
                                <div class="bot-meta-cell bot-meta-label">MSP CODE</div>
                                <div class="bot-meta-cell">{{ auth()->user()->company->msp_code ?? '—' }}</div>
                            </div>
                            <div class="bot-meta-row">
                                <div class="bot-meta-cell bot-meta-label">AS AT</div>
                                <div class="bot-meta-cell">{{ \Carbon\Carbon::parse($asOfDate)->format('d/m/Y') }}</div>
                            </div>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="bot-table">
                            <thead>
                                <tr>
                                    <th class="col-sno">Sno</th>
                                    <th class="col-sector">Sector</th>
                                    <th class="col-num">Number of Borrowers</th>
                                    <th class="col-num">Total Outstanding</th>
                                    <th class="col-num">Current Amount</th>
                                    <th class="col-num" colspan="4" style="text-align:center;">PAST DUE AMOUNT</th>
                                    <th class="col-num">Amount Written-off during the quarter</th>
                                    <th class="col-num">Validation</th>
                                </tr>
                                <tr>
                                    <th></th><th></th><th></th><th></th><th></th>
                                    <th class="col-num">ESM</th>
                                    <th class="col-num">Substandard</th>
                                    <th class="col-num">Doubtful</th>
                                    <th class="col-num">Loss</th>
                                    <th></th><th></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($rows as $row)
                                <tr>
                                    <td class="text-center">{{ $row['sno'] }}</td>
                                    <td>{{ $row['sector'] }}</td>
                                    <td class="col-num">{{ number_format($row['borrowers'], 0) }}</td>
                                    <td class="col-num">TZS {{ number_format($row['total_outstanding'], 2) }}</td>
                                    <td class="col-num">TZS {{ number_format($row['current_amount'], 2) }}</td>
                                    <td class="col-num">TZS {{ number_format($row['past_due']['ESM'], 2) }}</td>
                                    <td class="col-num">TZS {{ number_format($row['past_due']['Substandard'], 2) }}</td>
                                    <td class="col-num">TZS {{ number_format($row['past_due']['Doubtful'], 2) }}</td>
                                    <td class="col-num">TZS {{ number_format($row['past_due']['Loss'], 2) }}</td>
                                    <td class="col-num">TZS {{ number_format($row['written_off'], 2) }}</td>
                                    <td class="col-num"></td>
                                </tr>
                                @endforeach

                                <tr class="total-row">
                                    <td colspan="2">Total</td>
                                    <td class="col-num">{{ number_format(collect($rows)->sum('borrowers'), 0) }}</td>
                                    <td class="col-num">TZS {{ number_format(collect($rows)->sum('total_outstanding'), 2) }}</td>
                                    <td class="col-num">TZS {{ number_format(collect($rows)->sum('current_amount'), 2) }}</td>
                                    <td class="col-num">TZS {{ number_format(collect($rows)->sum('past_due.ESM'), 2) }}</td>
                                    <td class="col-num">TZS {{ number_format(collect($rows)->sum('past_due.Substandard'), 2) }}</td>
                                    <td class="col-num">TZS {{ number_format(collect($rows)->sum('past_due.Doubtful'), 2) }}</td>
                                    <td class="col-num">TZS {{ number_format(collect($rows)->sum('past_due.Loss'), 2) }}</td>
                                    <td class="col-num">TZS {{ number_format(collect($rows)->sum('written_off'), 2) }}</td>
                                    <td class="col-num"></td>
                                </tr>

                                <tr>
                                    <td colspan="12">
                                        <div class="row g-0">
                                            <div class="col-6 p-2">
                                                <div class="fw-bold">Provision Rate</div>
                                                <div class="note">Loans classification as days past due</div>
                                                <table class="table table-bordered mt-2" style="font-size:11px;">
                                                    @php
                                                        $totalOutstanding = collect($rows)->sum('total_outstanding');
                                                        $totalPastDue = collect($rows)->sum('past_due.ESM') + 
                                                                       collect($rows)->sum('past_due.Substandard') + 
                                                                       collect($rows)->sum('past_due.Doubtful') + 
                                                                       collect($rows)->sum('past_due.Loss');
                                                    @endphp
                                                    <tr><th>Classification</th><th class="text-end">Provision Rate</th><th class="text-end">Amount</th></tr>
                                                    <tr><td>ESM</td><td class="text-end">0%</td><td class="text-end">TZS {{ number_format(collect($rows)->sum('past_due.ESM'), 2) }}</td></tr>
                                                    <tr><td>Substandard</td><td class="text-end">10%</td><td class="text-end">TZS {{ number_format(collect($rows)->sum('past_due.Substandard'), 2) }}</td></tr>
                                                    <tr><td>Doubtful</td><td class="text-end">50%</td><td class="text-end">TZS {{ number_format(collect($rows)->sum('past_due.Doubtful'), 2) }}</td></tr>
                                                    <tr><td>Loss</td><td class="text-end">100%</td><td class="text-end">TZS {{ number_format(collect($rows)->sum('past_due.Loss'), 2) }}</td></tr>
                                                    <tr class="table-warning"><td><strong>Total Past Due</strong></td><td class="text-end"><strong>-</strong></td><td class="text-end"><strong>TZS {{ number_format($totalPastDue, 2) }}</strong></td></tr>
                                                </table>
                                            </div>
                                            <div class="col-6 p-2">
                                                <div class="fw-bold">Summary</div>
                                                <table class="table table-bordered mt-2" style="font-size:11px;">
                                                    @php
                                                        $totalOutstanding = collect($rows)->sum('total_outstanding');
                                                        $totalPastDue = collect($rows)->sum('past_due.ESM') + 
                                                                       collect($rows)->sum('past_due.Substandard') + 
                                                                       collect($rows)->sum('past_due.Doubtful') + 
                                                                       collect($rows)->sum('past_due.Loss');
                                                        $totalWrittenOff = collect($rows)->sum('written_off');
                                                        
                                                        // Calculate provision amounts based on BOT rates
                                                        $provisionESM = collect($rows)->sum('past_due.ESM') * 0.00; // 0%
                                                        $provisionSubstandard = collect($rows)->sum('past_due.Substandard') * 0.10; // 10%
                                                        $provisionDoubtful = collect($rows)->sum('past_due.Doubtful') * 0.50; // 50%
                                                        $provisionLoss = collect($rows)->sum('past_due.Loss') * 1.00; // 100%
                                                        $totalProvision = $provisionESM + $provisionSubstandard + $provisionDoubtful + $provisionLoss;
                                                        
                                                        // Get total cash collateral
                                                        $totalCashCollateral = collect($rows)->sum('cash_collateral');
                                                        
                                                        // Calculate non-performing loans ratio
                                                        $nonPerformingRatio = $totalOutstanding > 0 ? ($totalPastDue / $totalOutstanding) * 100 : 0;
                                                    @endphp
                                                    <tr><td>Provision Amount</td><td class="text-end">TZS {{ number_format($totalProvision, 2) }}</td></tr>
                                                    <tr><td>Cash Collateral/Insurance</td><td class="text-end">TZS {{ number_format(collect($rows)->sum('cash_collateral'), 2) }}</td></tr>
                                                    <tr><td>Guarantees/Compulsory Saving</td><td class="text-end">TZS {{ number_format(0, 2) }}</td></tr>
                                                    <tr><td>Net Provision Amount</td><td class="text-end">TZS {{ number_format(max(0, $totalProvision - $totalCashCollateral), 2) }}</td></tr>
                                                    <tr><td>TOTAL Net Amount</td><td class="text-end">TZS {{ number_format($totalOutstanding - max(0, $totalProvision - $totalCashCollateral), 2) }}</td></tr>
                                                    <tr><td>Ratio of Non-Performing Loans to Gross Loans</td><td class="text-end">{{ number_format($nonPerformingRatio, 2) }}%</td></tr>
                                                </table>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                        <div class="text-end mt-2">
                            <a href="{{ route('reports.bot.sectoral-loans.export', ['as_of_date' => $asOfDate]) }}" class="btn btn-warning">
                                <i class="bx bx-download me-1"></i> Export XLS
                            </a>
                        </div>
                        @if(count($rows) > 0)
                        <div class="mt-3">
                            <small class="text-info">
                                <strong>Current Data:</strong> 
                                @foreach($rows as $index => $row)
                                    @if($row['total_outstanding'] > 0)
                                        <strong>{{ $row['sector'] }}:</strong> 
                                        {{ $row['borrowers'] }} borrowers, 
                                        TZS {{ number_format($row['total_outstanding'], 2) }} outstanding
                                        @if($index < count($rows) - 1), @endif
                                    @endif
                                @endforeach
                            </small>
                        </div>
                        @endif
                        
                        <div class="note mt-2">
                            <strong>Note:</strong> This report shows sectoral classification of loans categorized by status. 
                            ESM = Early Stage of Default, Rates are in % per annum.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection 