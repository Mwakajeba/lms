@extends('layouts.main')

@section('title', 'BOT Interest Rate Structure')

@section('content')
    <div class="page-wrapper">
        <div class="page-content">
            <x-breadcrumbs-with-icons :links="[
                ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
                ['label' => 'Reports', 'url' => route('reports.index'), 'icon' => 'bx bx-file'],
                ['label' => 'BOT Reports', 'url' => route('reports.bot'), 'icon' => 'bx bx-transfer'],
                ['label' => 'Interest Rate Structure', 'url' => '#', 'icon' => 'bx bx-list-ul']
            ]" />
            <h6 class="mb-0 text-uppercase">BOT Interest Rate Structure</h6>
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
                .col-num { width: 140px; text-align: right; font-weight: 600; }
                .note { font-size: 11px; color: #6b7280; }
                .top-actions { display: flex; gap: 8px; align-items: end; flex-wrap: wrap; }
            </style>

            <div class="card">
                <div class="card-body">
                    <form method="GET" action="{{ route('reports.bot.interest-rates') }}" class="row g-3 align-items-end">
                        <div class="col-md-3">
                            <label for="as_of_date" class="form-label">As at Date</label>
                            <input type="date" id="as_of_date" name="as_of_date" value="{{ $asOfDate }}" class="form-control" required>
                        </div>
                        <div class="col-md-9 top-actions">
                            <button type="submit" class="btn btn-info"><i class="bx bx-filter me-1"></i> Filter</button>
                            <a href="{{ route('reports.bot.interest-rates.export', ['as_of_date' => $asOfDate]) }}" class="btn btn-outline-secondary">
                                <i class="bx bx-download me-1"></i> Download Template (XLS)
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
                            <div class="bot-title">INTEREST RATE STRUCTURE</div>
                            <div class="bot-sub" style="margin-top: 6px;">(Amount in TZS; Rates in % p.a.)</div>
                        </div>
                        <div class="bot-meta">
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
                                    <th>Outstanding Loan Amount</th>
                                    <th class="text-center" colspan="1">Weighted Average Interest Rate Straight Line Amortization (% p.a.)</th>
                                    <th class="text-center" colspan="2">Nominal Interest Rate (% p.a.) for Straight Line Amortization</th>
                                    <th class="text-center" colspan="1">Weighted Average Interest Rate Reducing Balance Amortization (% p.a.)</th>
                                    <th class="text-center" colspan="2">Nominal Interest Rate (% p.a.) for Reducing Balance Amortization</th>
                                </tr>
                                <tr>
                                    <th></th>
                                    <th class="text-end">WA</th>
                                    <th class="text-end">Lowest</th>
                                    <th class="text-end">Highest</th>
                                    <th class="text-end">WA</th>
                                    <th class="text-end">Lowest</th>
                                    <th class="text-end">Highest</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($rows as $index => $row)
                                <tr>
                                    <td class="col-num">TZS {{ number_format($row['outstanding'], 2) }}</td>
                                    <td class="col-num">{{ number_format($row['wa_straight'], 2) }}</td>
                                    <td class="col-num">{{ number_format($row['nom_straight_low'], 2) }}</td>
                                    <td class="col-num">{{ number_format($row['nom_straight_high'], 2) }}</td>
                                    <td class="col-num">{{ number_format($row['wa_reducing'], 2) }}</td>
                                    <td class="col-num">{{ number_format($row['nom_reducing_low'], 2) }}</td>
                                    <td class="col-num">{{ number_format($row['nom_reducing_high'], 2) }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                        
                        @if(count($rows) > 0)
                        <div class="mt-3">
                            <small class="text-info">
                                <strong>Current Data:</strong> 
                                @foreach($rows as $index => $row)
                                    @if($row['outstanding'] > 0)
                                        <strong>{{ $row['method'] ?? 'Unknown Method' }}:</strong> 
                                        {{ $row['loan_count'] }} loans, 
                                        TZS {{ number_format($row['outstanding'], 2) }} outstanding
                                        @if($index < count($rows) - 1), @endif
                                    @endif
                                @endforeach
                            </small>
                        </div>
                        @endif
                        
                        <div class="note mt-2">
                            <strong>Note:</strong> This report shows interest rate structure categorized by amortization method. 
                            WA = Weighted Average, Rates are in % per annum.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection 