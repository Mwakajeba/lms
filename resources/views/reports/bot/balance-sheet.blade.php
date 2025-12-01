@extends('layouts.main')

@section('title', 'BOT Balance Sheet')

@section('content')
    <div class="page-wrapper">
        <div class="page-content">
            <x-breadcrumbs-with-icons :links="[
                ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
                ['label' => 'Reports', 'url' => route('reports.index'), 'icon' => 'bx bx-file'],
                ['label' => 'BOT Reports', 'url' => route('reports.bot'), 'icon' => 'bx bx-transfer'],
                ['label' => 'Balance Sheet', 'url' => '#', 'icon' => 'bx bx-list-ul']
            ]" />
            <h6 class="mb-0 text-uppercase">BOT Balance Sheet</h6>
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
                .col-sno { width: 60px; text-align: center; }
                .col-particular { width: 60%; }
                .col-amount { width: 200px; text-align: right; font-weight: 600; }
                .col-validation { width: 160px; color: #6b7280; font-size: 11px; }
                .section { background: #e5e7eb; font-weight: 700; }
                .indent-1 { padding-left: 20px !important; }
                .indent-2 { padding-left: 40px !important; }
                .total-row { background: #fef3c7; font-weight: 700; }
                .note { font-size: 11px; color: #6b7280; }
                .top-actions { display: flex; gap: 8px; align-items: end; flex-wrap: wrap; }
            </style>

            <div class="card">
                <div class="card-body">
                    <form method="GET" action="{{ route('reports.bot.balance-sheet') }}" class="row g-3 align-items-end">
                        <div class="col-md-3">
                            <label for="as_of_date" class="form-label">As at Date</label>
                            <input type="date" id="as_of_date" name="as_of_date" value="{{ $asOfDate }}" class="form-control" required>
                        </div>
                        <div class="col-md-9 top-actions">
                            <button type="submit" class="btn btn-primary"><i class="bx bx-filter me-1"></i> Filter</button>
                            <a href="{{ route('reports.bot.balance-sheet.export', ['as_of_date' => $asOfDate]) }}" class="btn btn-outline-secondary">
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
                            <div class="bot-title">MSP CODE:</div>
                            <div class="bot-title" style="margin-top: 8px;">BALANCE SHEET AS AT THE QUARTER ENDED: {{ \Carbon\Carbon::parse($asOfDate)->format('d/m/Y') }}</div>
                            <div class="bot-sub" style="margin-top: 6px;">BOT FORM MSP2-01: To be submitted Quarterly</div>
                            <div class="bot-sub">(Amount in TZS)</div>
                        </div>
                        <div class="bot-meta">
                            <div class="bot-meta-row">
                                <div class="bot-meta-cell bot-meta-label">MSP NAME</div>
                                <div class="bot-meta-cell">{{ auth()->user()->company->name ?? '—' }}</div>
                            </div>
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
                                    <th class="col-particular">Particulars</th>
                                    <th class="col-amount">Amount</th>
                                    <th class="col-validation">Validation</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr class="section"><td>1.</td><td class="fw-bold">CASH AND CASH EQUIVALENTS</td><td class="col-amount">{{ number_format($data['cash_and_cash_equivalents'], 2) }}</td><td></td></tr>
                                <tr><td>(a)</td><td class="indent-1">Cash in Hand</td><td class="col-amount">0</td><td></td></tr>
                                <tr><td>(b)</td><td class="indent-1">Balances with Banks and Financial Institutions (sum i:iii)</td><td class="col-amount">{{ number_format($data['cash_and_cash_equivalents'], 2) }}</td><td></td></tr>
                                <tr><td></td><td class="indent-2">(i) Non-Agent Banking Balances</td><td class="col-amount">{{ number_format($data['cash_and_cash_equivalents'], 2) }}</td><td></td></tr>
                                <tr><td></td><td class="indent-2">(ii) Agent-Banking Balances</td><td class="col-amount">0</td><td></td></tr>
                                <tr><td></td><td class="indent-2">(iii) Balances with Microfinance Service Providers</td><td class="col-amount">0</td><td></td></tr>
                                <tr><td>(d)</td><td class="indent-1">MNOs Float Balances</td><td class="col-amount">0</td><td></td></tr>

                                <tr class="section"><td>2.</td><td class="fw-bold">INVESTMENT IN DEBT SECURITIES - NET (sum a:d minus e)</td><td class="col-amount">0</td><td></td></tr>
                                <tr><td>(a)</td><td class="indent-1">Treasury Bills</td><td class="col-amount">0</td><td></td></tr>
                                <tr><td>(b)</td><td class="indent-1">Other Government Securities</td><td class="col-amount">0</td><td></td></tr>
                                <tr><td>(c)</td><td class="indent-1">Private Securities</td><td class="col-amount">0</td><td></td></tr>
                                <tr><td>(d)</td><td class="indent-1">Others</td><td class="col-amount">0</td><td></td></tr>
                                <tr><td>(e)</td><td class="indent-1">Allowance for Probable Losses (Deduction)</td><td class="col-amount">0</td><td></td></tr>

                                <tr class="section"><td>3.</td><td class="fw-bold">EQUITY INVESTMENTS - NET (a - b)</td><td class="col-amount">0</td><td></td></tr>
                                <tr><td>(a)</td><td class="indent-1">Equity Investment</td><td class="col-amount">0</td><td></td></tr>
                                <tr><td>(b)</td><td class="indent-1">Allowance for Probable Losses (Deduction)</td><td class="col-amount">0</td><td></td></tr>

                                <tr class="section"><td>4.</td><td class="fw-bold">LOANS - NET (sum a:d less e)</td><td class="col-amount">{{ number_format($data['loans_net'], 2) }}</td><td></td></tr>
                                <tr><td>(a)</td><td class="indent-1">Loans to Clients</td><td class="col-amount">{{ number_format($data['loans_to_clients'], 2) }}</td><td></td></tr>
                                <tr><td>(b)</td><td class="indent-1">Loan to Staff and Related Parties</td><td class="col-amount">{{ number_format($data['loans_to_staff'], 2) }}</td><td></td></tr>
                                <tr><td>(c)</td><td class="indent-1">Loans to other Microfinance Service Providers</td><td class="col-amount">{{ number_format($data['loans_to_mfsps'], 2) }}</td><td></td></tr>
                                <tr><td>(d)</td><td class="indent-1">Accrued Interest on Loans</td><td class="col-amount">{{ number_format($data['accrued_interest'], 2) }}</td><td></td></tr>
                                <tr><td>(e)</td><td class="indent-1">Allowance for Probable Losses (Deduction)</td><td class="col-amount">{{ number_format($data['allowance_for_losses'], 2) }}</td><td></td></tr>

                                <tr class="section"><td>5.</td><td class="fw-bold">PROPERTY, PLANT AND EQUIPMENT - NET (a - b)</td><td class="col-amount">{{ number_format($data['property_plant_equipment_net'], 2) }}</td><td></td></tr>
                                <tr><td>(a)</td><td class="indent-1">Property, Plant and Equipment</td><td class="col-amount">0</td><td></td></tr>
                                <tr><td>(b)</td><td class="indent-1">Accumulated Depreciation (Deduction)</td><td class="col-amount">0</td><td></td></tr>

                                <tr class="section"><td>6.</td><td class="fw-bold">OTHER ASSETS (sum a:e less f)</td><td class="col-amount">{{ number_format($data['other_assets'], 2) }}</td><td></td></tr>
                                <tr><td>(a)</td><td class="indent-1">Receivables</td><td class="col-amount">0</td><td></td></tr>
                                <tr><td>(b)</td><td class="indent-1">Prepaid Expenses</td><td class="col-amount">0</td><td></td></tr>
                                <tr><td>(c)</td><td class="indent-1">Deferred Tax Assets</td><td class="col-amount">0</td><td></td></tr>
                                <tr><td>(d)</td><td class="indent-1">Intangible Assets</td><td class="col-amount">0</td><td></td></tr>
                                <tr><td>(e)</td><td class="indent-1">Miscellaneous Assets</td><td class="col-amount">0</td><td></td></tr>
                                <tr><td>(f)</td><td class="indent-1">Allowance for Probable Losses (Deduction)</td><td class="col-amount">0</td><td></td></tr>

                                <tr class="total-row"><td>7.</td><td class="fw-bold">TOTAL ASSETS</td><td class="col-amount">{{ number_format($data['total_assets'], 2) }}</td><td class="col-validation">C33==C61</td></tr>

                                <tr class="section"><td>8.</td><td class="fw-bold">LIABILITIES</td><td class="col-amount"></td><td></td></tr>
                                <tr class="section"><td>9.</td><td class="fw-bold">BORROWINGS (sum a:b)</td><td class="col-amount">{{ number_format($data['borrowings'], 2) }}</td><td></td></tr>
                                <tr><td>(a)</td><td class="indent-1">Borrowings in Tanzania (sum i:v)</td><td class="col-amount">{{ number_format($data['borrowings'], 2) }}</td><td></td></tr>
                                <tr><td></td><td class="indent-2">(i) Borrowings from Banks and Financial Institutions</td><td class="col-amount">0</td><td></td></tr>
                                <tr><td></td><td class="indent-2">(ii) Borrowings from Other Microfinance Service Providers</td><td class="col-amount">0</td><td></td></tr>
                                <tr><td></td><td class="indent-2">(iii) Borrowing from Shareholders</td><td class="col-amount">0</td><td></td></tr>
                                <tr><td></td><td class="indent-2">(iv) Borrowing from Public through Debt Securities</td><td class="col-amount">0</td><td></td></tr>
                                <tr><td></td><td class="indent-2">(v) Other Borrowings</td><td class="col-amount">0</td><td></td></tr>
                                <tr><td>(b)</td><td class="indent-1">Borrowings from Abroad (sum i:iii)</td><td class="col-amount">0</td><td></td></tr>
                                <tr><td></td><td class="indent-2">(i) Borrowings from Banks and Financial Institutions</td><td class="col-amount">0</td><td></td></tr>
                                <tr><td></td><td class="indent-2">(ii) Borrowing from Shareholders</td><td class="col-amount">0</td><td></td></tr>
                                <tr><td></td><td class="indent-2">(iii) Other Borrowings</td><td class="col-amount">0</td><td></td></tr>

                                <tr class="section"><td>10.</td><td class="fw-bold">CASH COLLATERAL/LOAN INSURANCE GUARANTEES/COMPULSORY SAVINGS</td><td class="col-amount">{{ number_format($data['cash_collateral'], 2) }}</td><td></td></tr>
                                <tr class="section"><td>11.</td><td class="fw-bold">TAX PAYABLES</td><td class="col-amount">{{ number_format($data['tax_payables'], 2) }}</td><td></td></tr>
                                <tr class="section"><td>12.</td><td class="fw-bold">DIVIDEND PAYABLES</td><td class="col-amount">0.00</td><td></td></tr>
                                <tr class="section"><td>13.</td><td class="fw-bold">OTHER PAYABLES AND ACCRUALS</td><td class="col-amount">{{ number_format($data['other_payables'], 2) }}</td><td></td></tr>

                                <tr class="total-row"><td>14.</td><td class="fw-bold">TOTAL LIABILITIES (sum 9:13)</td><td class="col-amount">{{ number_format($data['total_liabilities'], 2) }}</td><td></td></tr>

                                <tr class="section"><td>15.</td><td class="fw-bold">TOTAL CAPITAL (sum a:i)</td><td class="col-amount"></td><td></td></tr>
                                <tr><td>(a)</td><td class="indent-1">Paid-up Ordinary Share Capital</td><td class="col-amount">{{ number_format($data['paid_up_capital'], 2) }}</td><td></td></tr>
                                <tr><td>(b)</td><td class="indent-1">Paid-up Preference Shares</td><td class="col-amount">0.00</td><td></td></tr>
                                <tr><td>(c)</td><td class="indent-1">Capital Grants</td><td class="col-amount">0.00</td><td></td></tr>
                                <tr><td>(d)</td><td class="indent-1">Donation</td><td class="col-amount">0.00</td><td></td></tr>
                                <tr><td>(e)</td><td class="indent-1">Share Premium</td><td class="col-amount">0.00</td><td></td></tr>
                                <tr><td>(f)</td><td class="indent-1">General Reserves</td><td class="col-amount">0.00</td><td></td></tr>
                                <tr><td>(g)</td><td class="indent-1">Retained Earnings</td><td class="col-amount">{{ number_format($data['retained_earnings'], 2) }}</td><td></td></tr>
                                <tr><td>(i)</td><td class="indent-1">Profit/Loss</td><td class="col-amount">{{ number_format($data['profit_loss'], 2) }}</td><td></td></tr>
                                <tr><td>(j)</td><td class="indent-1">Other Reserves</td><td class="col-amount">0.00</td><td></td></tr>

                                <tr class="total-row"><td>16.</td><td class="fw-bold">TOTAL LIABILITIES AND CAPITAL (14+15)</td><td class="col-amount">{{ number_format($data['total_liabilities_and_capital'], 2) }}</td><td></td></tr>
                            </tbody>
                        </table>
                        <div class="note mt-2">Note: This is a preview layout matching BOT MSP2-01. Figures are now populated from real data.</div>
                    </div>
                </div>
            </div>
            
            <!-- Debug Section -->
            <div class="card mt-3">
                <div class="card-body">
                    <h6 class="card-title">Current Data</h6>
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Cash and Cash Equivalents:</strong> TZS {{ number_format($data['cash_and_cash_equivalents'], 2) }}</p>
                            <p><strong>Loans Net:</strong> TZS {{ number_format($data['loans_net'], 2) }}</p>
                            <p><strong>Total Assets:</strong> TZS {{ number_format($data['total_assets'], 2) }}</p>
                            <p><strong>Total Liabilities:</strong> TZS {{ number_format($data['total_liabilities'], 2) }}</p>
                            <p><strong>Total Capital:</strong> TZS {{ number_format($data['total_liabilities_and_capital'] - $data['total_liabilities'], 2) }}</p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Bank Accounts:</strong></p>
                            <ul>
                                @foreach($data['bank_accounts'] as $account)
                                    <li>{{ $account['name'] }}: TZS {{ number_format($account['balance'], 2) }}</li>
                                @endforeach
                            </ul>
                            <p><strong>Cash Collateral:</strong> TZS {{ number_format($data['cash_collateral'], 2) }}</p>
                            <p><strong>Total Bank Balance:</strong> TZS {{ number_format($data['total_bank_balance'], 2) }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection 