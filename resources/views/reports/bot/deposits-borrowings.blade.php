@extends('layouts.main')

@section('title', 'BOT Deposits & Borrowings (Banks & FIs)')

@section('content')
    <div class="page-wrapper">
        <div class="page-content">
            <x-breadcrumbs-with-icons :links="[
                ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
                ['label' => 'Reports', 'url' => route('reports.index'), 'icon' => 'bx bx-file'],
                ['label' => 'BOT Reports', 'url' => route('reports.bot'), 'icon' => 'bx bx-transfer'],
                ['label' => 'Deposits & Borrowings', 'url' => '#', 'icon' => 'bx bx-list-ul']
            ]" />
            <h6 class="mb-0 text-uppercase">BOT Deposits and Borrowings from Banks and Financial Institutions for the Quarter Ended {{ \Carbon\Carbon::parse($asOfDate)->format('d/m/Y') }}</h6>
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
                .col-name { width: 30%; }
                .col-num { width: 120px; text-align: right; font-weight: 600; }
                .top-actions { display: flex; gap: 8px; align-items: end; flex-wrap: wrap; }
                .section { background: #e5e7eb; font-weight: 700; }
                .total-row { background: #fef3c7; font-weight: 700; }
            </style>

            <div class="card">
                <div class="card-body">
                    <form method="GET" action="{{ route('reports.bot.deposits-borrowings') }}" class="row g-3 align-items-end">
                        <div class="col-md-3">
                            <label for="as_of_date" class="form-label">As at Date</label>
                            <input type="date" id="as_of_date" name="as_of_date" value="{{ $asOfDate }}" class="form-control" required>
                        </div>
                        <div class="col-md-9 top-actions">
                            <button type="submit" class="btn btn-dark"><i class="bx bx-filter me-1"></i> Filter</button>
                            <a href="{{ route('reports.bot.deposits-borrowings.export', ['as_of_date' => $asOfDate]) }}" class="btn btn-outline-secondary">
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
                            <div class="bot-title">NAME OF INSTITUTION: {{ $company->name ?? 'Company Name Not Set' }}</div>
                            <div class="bot-sub" style="margin-top: 6px;">BOT FORM MSP2-07 to be submitted Quarterly (Amount in TZS)</div>
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
                                    <th class="col-name">Name of Bank or Financial Institution</th>
                                    <th class="text-center" colspan="3">Deposit Amounts</th>
                                    <th class="text-center" colspan="3">Borrowed Amount</th>
                                    <th class="col-num">Validation</th>
                                </tr>
                                <tr>
                                    <th colspan="2"></th>
                                    <th class="col-num">TZS</th>
                                    <th class="col-num">Foreign Currency Equivalent in TZS</th>
                                    <th class="col-num">Total Deposits (c+d)</th>
                                    <th class="col-num">TZS</th>
                                    <th class="col-num">Foreign Currency Equivalent in TZS</th>
                                    <th class="col-num">Total Loan (f+g)</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr class="section"><td colspan="9">BANKS IN TANZANIA</td></tr>
                                @foreach($banksTz as $i => $name)
                                <tr>
                                    <td class="text-center">{{ $i + 1 }}</td>
                                    <td>{{ $name }}</td>
                                    @php
                                        $balance = 0;
                                        $matchedAccount = null;
                                        // Find matching bank account balance using improved LIKE logic
                                        foreach($data['bank_accounts'] as $account) {
                                            // Extract key words from BOT bank name (e.g., "CRDB BANK PLC" -> "CRDB")
                                            $botKeyWords = explode(' ', strtoupper($name));
                                            $accountKeyWords = explode(' ', strtoupper($account['name']));
                                            
                                            // Check if any key words match
                                            $matchFound = false;
                                            $matchScore = 0;
                                            
                                            foreach($botKeyWords as $botWord) {
                                                if (strlen($botWord) > 2) { // Only check words longer than 2 characters
                                                    foreach($accountKeyWords as $accountWord) {
                                                        if (strlen($accountWord) > 2 && 
                                                            (stripos($accountWord, $botWord) !== false || 
                                                             stripos($botWord, $accountWord) !== false)) {
                                                            $matchScore++;
                                                        }
                                                    }
                                                }
                                            }
                                            
                                            // Require at least 2 matching keywords for a valid match
                                            if ($matchScore >= 2) {
                                                $matchFound = true;
                                                $matchedAccount = $account;
                                                $balance = $account['balance'];
                                                break;
                                            }
                                        }
                                        
                                        // Debug logging
                                        \Log::info("View matching for '{$name}': balance={$balance}, matched=" . ($matchedAccount ? $matchedAccount['name'] : 'none'));
                                        
                                        $depositTz = $balance; // Show actual balance (positive or negative)
                                        $depositForeign = 0;
                                        $totalDeposit = $depositTz + $depositForeign;
                                        $borrowingTz = 0;
                                        $borrowingForeign = 0;
                                        $totalBorrowing = $borrowingTz + $borrowingForeign;
                                    @endphp
                                    <td class="col-num">{{ number_format($depositTz, 2) }}</td>
                                    <td class="col-num">{{ number_format($depositForeign, 2) }}</td>
                                    <td class="col-num">{{ number_format($totalDeposit, 2) }}</td>
                                    <td class="col-num">{{ number_format($borrowingTz, 2) }}</td>
                                    <td class="col-num">{{ number_format($borrowingForeign, 2) }}</td>
                                    <td class="col-num">{{ number_format($totalBorrowing, 2) }}</td>
                                    <td>
                                    </td>
                                </tr>
                                @endforeach
                                <tr class="total-row"><td colspan="2">TOTAL IN BANKS TANZANIA</td><td class="col-num">{{ number_format($data['total_deposits_tz'], 2) }}</td><td class="col-num">{{ number_format($data['total_deposits_foreign'], 2) }}</td><td class="col-num">{{ number_format($data['total_deposits'], 2) }}</td><td class="col-num">{{ number_format($data['total_borrowings_tz'], 2) }}</td><td class="col-num">{{ number_format($data['total_borrowings_foreign'], 2) }}</td><td class="col-num">{{ number_format($data['total_borrowings'], 2) }}</td><td class="col-num">H30=MSP2_01C37</td></tr>

                                <tr class="section"><td colspan="9">MICROFINANCE SERVICE PROVIDERS</td></tr>
                                @foreach($mfsp as $j => $name)
                                <tr>
                                    <td class="text-center">{{ 31 + $j }}</td>
                                    <td>{{ $name }}</td>
                                    <td class="col-num">0</td>
                                    <td class="col-num">0</td>
                                    <td class="col-num">0</td>
                                    <td class="col-num">0</td>
                                    <td class="col-num">0</td>
                                    <td class="col-num">0</td>
                                    <td></td>
                                </tr>
                                @endforeach
                                <tr class="total-row"><td colspan="2">TOTAL IN MICROFINANCE SERVICE PROVIDERS</td><td class="col-num">0</td><td class="col-num">0</td><td class="col-num">0</td><td class="col-num">0</td><td class="col-num">0</td><td class="col-num">0</td><td class="col-num">E46=MSP2_01C6, H46=MSP_01C38</td></tr>

                                <tr class="section"><td colspan="9">BALANCES WITH MNOs</td></tr>
                                @foreach($mnos as $k => $name)
                                <tr>
                                    <td class="text-center">{{ 47 + $k }}</td>
                                    <td>{{ $name }}</td>
                                    <td class="col-num">0</td>
                                    <td class="col-num">0</td>
                                    <td class="col-num">0</td>
                                    <td class="col-num">0</td>
                                    <td class="col-num">0</td>
                                    <td class="col-num">0</td>
                                    <td></td>
                                </tr>
                                @endforeach
                                <tr class="total-row"><td colspan="2">TOTAL BALANCES WITH MNOs</td><td class="col-num">0</td><td class="col-num">0</td><td class="col-num">0</td><td class="col-num">0</td><td class="col-num">0</td><td class="col-num">0</td><td class="col-num">E55=MSP2_01C7</td></tr>

                                <tr class="total-row"><td colspan="2">TOTAL BALANCES IN TANZANIA</td><td class="col-num">{{ number_format($data['total_deposits_tz'], 2) }}</td><td class="col-num">{{ number_format($data['total_deposits_foreign'], 2) }}</td><td class="col-num">{{ number_format($data['total_deposits'], 2) }}</td><td class="col-num">{{ number_format($data['total_borrowings_tz'], 2) }}</td><td class="col-num">{{ number_format($data['total_borrowings_foreign'], 2) }}</td><td class="col-num">{{ number_format($data['total_borrowings'], 2) }}</td><td class="col-num">E57=MSP2_01C3; MSP2_01C6+MSP2_01C7</td></tr>

                                <tr class="section"><td colspan="9">BANKS ABROAD</td></tr>
                                @for($r=0;$r<5;$r++)
                                <tr>
                                    <td class="text-center">{{ 58 + $r }}</td>
                                    <td></td>
                                    <td class="col-num">0</td>
                                    <td class="col-num">0</td>
                                    <td class="col-num">0</td>
                                    <td class="col-num">0</td>
                                    <td class="col-num">0</td>
                                    <td class="col-num">0</td>
                                    <td></td>
                                </tr>
                                @endfor
                                <tr class="total-row"><td colspan="2">TOTAL IN BANKS ABROAD</td><td class="col-num">0</td><td class="col-num">0</td><td class="col-num">0</td><td class="col-num">0</td><td class="col-num">0</td><td class="col-num">0</td><td class="col-num">H65=MSP2_01C43</td></tr>
                                <tr class="total-row"><td colspan="2">TOTAL IN BANKS</td><td class="col-num">{{ number_format($data['total_deposits_tz'], 2) }}</td><td class="col-num">{{ number_format($data['total_deposits_foreign'], 2) }}</td><td class="col-num">{{ number_format($data['total_deposits'], 2) }}</td><td class="col-num">{{ number_format($data['total_borrowings_tz'], 2) }}</td><td class="col-num">{{ number_format($data['total_borrowings_foreign'], 2) }}</td><td class="col-num">{{ number_format($data['total_borrowings'], 2) }}</td><td class="col-num">E66=MSP2_01C3</td></tr>
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
                            <p><strong>Quarter Start:</strong> {{ $data['quarter_start']->format('d/m/Y') }}</p>
                            <p><strong>Quarter End:</strong> {{ $data['quarter_end']->format('d/m/Y') }}</p>
                            <p><strong>Total Bank Balance:</strong> TZS {{ number_format($data['total_bank_balance'], 2) }}</p>
                            <p><strong>Total Cash Collateral:</strong> TZS {{ number_format($data['total_cash_collateral'], 2) }}</p>
                            <p><strong>Total Deposits (TZS):</strong> TZS {{ number_format($data['total_deposits_tz'], 2) }}</p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Total Deposits (Foreign):</strong> TZS {{ number_format($data['total_deposits_foreign'], 2) }}</p>
                            <p><strong>Total Deposits:</strong> TZS {{ number_format($data['total_deposits'], 2) }}</p>
                            <p><strong>Total Borrowings (TZS):</strong> TZS {{ number_format($data['total_borrowings_tz'], 2) }}</p>
                            <p><strong>Total Borrowings (Foreign):</strong> TZS {{ number_format($data['total_borrowings_foreign'], 2) }}</p>
                            <p><strong>Total Borrowings:</strong> TZS {{ number_format($data['total_borrowings'], 2) }}</p>
                        </div>
                    </div>
                    <div class="row mt-3">
                        <div class="col-12">
                            <h6>Bank Accounts Found:</h6>
                            <div class="table-responsive">
                                <table class="table table-sm table-bordered">
                                    <thead>
                                        <tr>
                                            <th>Bank Name</th>
                                            <th>Account Number</th>
                                            <th>Balance (TZS)</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($data['bank_accounts'] as $account)
                                        <tr>
                                            <td>{{ $account['name'] }}</td>
                                            <td>{{ $account['account_number'] }}</td>
                                            <td class="text-end">{{ number_format($account['balance'], 2) }}</td>
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