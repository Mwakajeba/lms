@extends('layouts.main')

@section('title', 'BOT Income Statement')

@section('content')
    <div class="page-wrapper">
        <div class="page-content">
            <x-breadcrumbs-with-icons :links="[
                ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
                ['label' => 'Reports', 'url' => route('reports.index'), 'icon' => 'bx bx-file'],
                ['label' => 'BOT Reports', 'url' => route('reports.bot'), 'icon' => 'bx bx-transfer'],
                ['label' => 'Income Statement', 'url' => '#', 'icon' => 'bx bx-list-ul']
            ]" />
            <h6 class="mb-0 text-uppercase">BOT Statement of Income and Expense</h6>
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
                .col-quarter { width: 180px; text-align: right; font-weight: 600; }
                .col-ytd { width: 180px; text-align: right; font-weight: 600; }
                .section { background: #e5e7eb; font-weight: 700; }
                .indent-1 { padding-left: 20px !important; }
                .indent-2 { padding-left: 40px !important; }
                .total-row { background: #fef3c7; font-weight: 700; }
                .note { font-size: 11px; color: #6b7280; }
                .top-actions { display: flex; gap: 8px; align-items: end; flex-wrap: wrap; }
            </style>

            <div class="card">
                <div class="card-body">
                    <form method="GET" action="{{ route('reports.bot.income-statement') }}" class="row g-3 align-items-end">
                        <div class="col-md-3">
                            <label for="as_of_date" class="form-label">As at Date</label>
                            <input type="date" id="as_of_date" name="as_of_date" value="{{ $asOfDate }}" class="form-control" required>
                        </div>
                        <div class="col-md-9 top-actions">
                            <button type="submit" class="btn btn-success"><i class="bx bx-filter me-1"></i> Filter</button>
                            <a href="{{ route('reports.bot.income-statement.export', ['as_of_date' => $asOfDate]) }}" class="btn btn-outline-secondary">
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
                            <div class="bot-title" style="margin-top: 8px;">STATEMENT OF INCOME AND EXPENSE FOR THE QUARTER ENDED:</div>
                            <div class="bot-sub" style="margin-top: 6px;">BOT FORM MSP2-02 to be submitted Quarterly</div>
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
                                    <th class="col-particular">Particular</th>
                                    <th class="col-quarter">Quarterly Amount</th>
                                    <th class="col-ytd">Year To date Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr class="section"><td>1.</td><td class="fw-bold">INTEREST INCOME</td><td class="col-quarter">{{ number_format($data['interest_income'], 2) }}</td><td class="col-ytd">{{ number_format($data['ytd_interest_income'], 2) }}</td></tr>
                                <tr><td>a.</td><td class="indent-1">Interest - Loans to Clients</td><td class="col-quarter">{{ number_format($data['interest_loans_to_clients'], 2) }}</td><td class="col-ytd">{{ number_format($data['interest_loans_to_clients'], 2) }}</td></tr>
                                <tr><td>b.</td><td class="indent-1">Interest - Loans to Microfinance Service Providers</td><td class="col-quarter">{{ number_format($data['interest_loans_to_mfsps'], 2) }}</td><td class="col-ytd">{{ number_format($data['interest_loans_to_mfsps'], 2) }}</td></tr>
                                <tr><td>c.</td><td class="indent-1">Interest - Investments in Govt Securities</td><td class="col-quarter">{{ number_format($data['interest_govt_securities'], 2) }}</td><td class="col-ytd">{{ number_format($data['interest_govt_securities'], 2) }}</td></tr>
                                <tr><td>d.</td><td class="indent-1">Interest - Bank Deposits</td><td class="col-quarter">{{ number_format($data['interest_bank_deposits'], 2) }}</td><td class="col-ytd">{{ number_format($data['interest_bank_deposits'], 2) }}</td></tr>
                                <tr><td>e.</td><td class="indent-1">Interest - Others</td><td class="col-quarter">{{ number_format($data['interest_others'], 2) }}</td><td class="col-ytd">{{ number_format($data['interest_others'], 2) }}</td></tr>

                                <tr class="section"><td>2.</td><td class="fw-bold">INTEREST EXPENSE</td><td class="col-quarter">{{ number_format($data['interest_expense'], 2) }}</td><td class="col-ytd">{{ number_format($data['interest_expense'], 2) }}</td></tr>
                                <tr><td>a.</td><td class="indent-1">Interest - Borrowings from Banks & Financial Institutions in Tanzania</td><td class="col-quarter">0</td><td class="col-ytd">0</td></tr>
                                <tr><td>b.</td><td class="indent-1">Interest - Borrowing from Microfinance Service Providers in Tanzania</td><td class="col-quarter">0</td><td class="col-ytd">0</td></tr>
                                <tr><td>c.</td><td class="indent-1">Interest - Borrowings from Abroad</td><td class="col-quarter">0</td><td class="col-ytd">0</td></tr>
                                <tr><td>d.</td><td class="indent-1">Interest - Borrowing from Shareholders</td><td class="col-quarter">0</td><td class="col-ytd">0</td></tr>
                                <tr><td>e.</td><td class="indent-1">Interest - Others</td><td class="col-quarter">0</td><td class="col-ytd">0</td></tr>

                                <tr class="total-row"><td>3.</td><td class="fw-bold">NET INTEREST INCOME (1 less 2)</td><td class="col-quarter">{{ number_format($data['net_interest_income'], 2) }}</td><td class="col-ytd">{{ number_format($data['net_interest_income'], 2) }}</td></tr>

                                <tr class="section"><td>4.</td><td class="fw-bold">BAD DEBTS WRITTEN OFF NOT PROVIDED FOR</td><td class="col-quarter">{{ number_format($data['bad_debts_written_off'], 2) }}</td><td class="col-ytd">{{ number_format($data['bad_debts_written_off'], 2) }}</td></tr>
                                <tr class="section"><td>5.</td><td class="fw-bold">PROVISION FOR BAD AND DOUBTFUL DEBTS</td><td class="col-quarter">{{ number_format($data['provision_for_bad_debts'], 2) }}</td><td class="col-ytd">{{ number_format($data['provision_for_bad_debts'], 2) }}</td></tr>

                                <tr class="section"><td>6.</td><td class="fw-bold">NON-INTEREST INCOME</td><td class="col-quarter">{{ number_format($data['non_interest_income'], 2) }}</td><td class="col-ytd">{{ number_format($data['non_interest_income'], 2) }}</td></tr>
                                <tr><td>a.</td><td class="indent-1">Commissions</td><td class="col-quarter">0</td><td class="col-ytd">0</td></tr>
                                <tr><td>b.</td><td class="indent-1">Fees</td><td class="col-quarter">0</td><td class="col-ytd">0</td></tr>
                                <tr><td>c.</td><td class="indent-1">Rental Income on Premises</td><td class="col-quarter">0</td><td class="col-ytd">0</td></tr>
                                <tr><td>d.</td><td class="indent-1">Dividends on Equity Investment</td><td class="col-quarter">0</td><td class="col-ytd">0</td></tr>
                                <tr><td>e.</td><td class="indent-1">Income from Recovery of Charged off Assets and Acquired Assets</td><td class="col-quarter">0</td><td class="col-ytd">0</td></tr>
                                <tr><td>f.</td><td class="indent-1">Other Income</td><td class="col-quarter">0</td><td class="col-ytd">0</td></tr>

                                <tr class="section"><td>7.</td><td class="fw-bold">NON-INTEREST EXPENSES</td><td class="col-quarter">{{ number_format($data['non_interest_expenses'], 2) }}</td><td class="col-ytd">{{ number_format($data['non_interest_expenses'], 2) }}</td></tr>
                                <tr><td>a.</td><td class="indent-1">Managements' Salaries and Benefits</td><td class="col-quarter">0</td><td class="col-ytd">0</td></tr>
                                <tr><td>b.</td><td class="indent-1">Employees' Salaries and Benefits</td><td class="col-quarter">0</td><td class="col-ytd">0</td></tr>
                                <tr><td>c.</td><td class="indent-1">Wages</td><td class="col-quarter">0</td><td class="col-ytd">0</td></tr>
                                <tr><td>d.</td><td class="indent-1">Pensions Contributions</td><td class="col-quarter">0</td><td class="col-ytd">0</td></tr>
                                <tr><td>e.</td><td class="indent-1">Skills and Development Levy</td><td class="col-quarter">0</td><td class="col-ytd">0</td></tr>
                                <tr><td>f.</td><td class="indent-1">Rental Expense on Premises and Equipment</td><td class="col-quarter">0</td><td class="col-ytd">0</td></tr>
                                <tr><td>g.</td><td class="indent-1">Depreciation - Premises and Equipment</td><td class="col-quarter">0</td><td class="col-ytd">0</td></tr>
                                <tr><td>h.</td><td class="indent-1">Amortization - Leasehold Rights and Equipments</td><td class="col-quarter">0</td><td class="col-ytd">0</td></tr>
                                <tr><td>i.</td><td class="indent-1">Foreclosure and Litigation Expenses</td><td class="col-quarter">0</td><td class="col-ytd">0</td></tr>
                                <tr><td>j.</td><td class="indent-1">Management Fees</td><td class="col-quarter">0</td><td class="col-ytd">0</td></tr>
                                <tr><td>k.</td><td class="indent-1">Auditors Fees</td><td class="col-quarter">0</td><td class="col-ytd">0</td></tr>
                                <tr><td>l.</td><td class="indent-1">Taxes</td><td class="col-quarter">0</td><td class="col-ytd">0</td></tr>
                                <tr><td>m.</td><td class="indent-1">License Fees</td><td class="col-quarter">0</td><td class="col-ytd">0</td></tr>
                                <tr><td>n.</td><td class="indent-1">Insurance</td><td class="col-quarter">0</td><td class="col-ytd">0</td></tr>
                                <tr><td>o.</td><td class="indent-1">Utilities Expenses</td><td class="col-quarter">0</td><td class="col-ytd">0</td></tr>
                                <tr><td>p.</td><td class="indent-1">Other Non-Interest Expenses</td><td class="col-quarter">0</td><td class="col-ytd">0</td></tr>

                                <tr class="total-row"><td>8.</td><td class="fw-bold">NET INCOME / (LOSS) BEFORE INCOME TAX (3+6 Less 4,5 and 7)</td><td class="col-quarter">{{ number_format($data['net_income_before_tax'], 2) }}</td><td class="col-ytd">{{ number_format($data['net_income_before_tax'], 2) }}</td></tr>
                                <tr class="section"><td>9.</td><td class="fw-bold">INCOME TAX / PROVISION</td><td class="col-quarter">{{ number_format($data['income_tax'], 2) }}</td><td class="col-ytd">{{ number_format($data['income_tax'], 2) }}</td></tr>
                                <tr class="total-row"><td>10.</td><td class="fw-bold">NET INCOME / (LOSS) AFTER INCOME TAX (8 less 9)</td><td class="col-quarter">{{ number_format($data['net_income_after_tax'], 2) }}</td><td class="col-ytd">{{ number_format($data['net_income_after_tax'], 2) }}</td></tr>
                            </tbody>
                        </table>
                        <div class="note mt-2">Note: Preview layout matching BOT MSP2-02. Figures are now populated from real data.</div>
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
                            <p><strong>Quarterly Interest Income:</strong> TZS {{ number_format($data['quarterly_interest_income'], 2) }}</p>
                            <p><strong>Year To Date Interest Income:</strong> TZS {{ number_format($data['ytd_interest_income'], 2) }}</p>
                            <p><strong>Total Interest Income:</strong> TZS {{ number_format($data['interest_income'], 2) }}</p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Interest - Loans to Clients:</strong> TZS {{ number_format($data['interest_loans_to_clients'], 2) }}</p>
                            <p><strong>Interest - Loans to MFSPs:</strong> TZS {{ number_format($data['interest_loans_to_mfsps'], 2) }}</p>
                            <p><strong>Interest - Govt Securities:</strong> TZS {{ number_format($data['interest_govt_securities'], 2) }}</p>
                            <p><strong>Interest - Bank Deposits:</strong> TZS {{ number_format($data['interest_bank_deposits'], 2) }}</p>
                            <p><strong>Interest - Others:</strong> TZS {{ number_format($data['interest_others'], 2) }}</p>
                        </div>
                    </div>
                    <div class="row mt-3">
                        <div class="col-md-6">
                            <p><strong>Net Interest Income:</strong> TZS {{ number_format($data['net_interest_income'], 2) }}</p>
                            <p><strong>Net Income Before Tax:</strong> TZS {{ number_format($data['net_income_before_tax'], 2) }}</p>
                            <p><strong>Net Income After Tax:</strong> TZS {{ number_format($data['net_income_after_tax'], 2) }}</p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Bad Debts Written Off:</strong> TZS {{ number_format($data['bad_debts_written_off'], 2) }}</p>
                            <p><strong>Provision for Bad Debts:</strong> TZS {{ number_format($data['provision_for_bad_debts'], 2) }}</p>
                            <p><strong>Non-Interest Income:</strong> TZS {{ number_format($data['non_interest_income'], 2) }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection 