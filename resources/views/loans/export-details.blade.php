<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Loan Details Export</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            line-height: 1.4;
            color: #333;
            margin: 0;
            padding: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #007bff;
            padding-bottom: 20px;
        }
        .company-name {
            font-size: 18px;
            font-weight: bold;
            color: #007bff;
            margin-bottom: 5px;
        }
        .report-title {
            font-size: 16px;
            font-weight: bold;
            margin-bottom: 10px;
        }
        .export-date {
            font-size: 10px;
            color: #666;
        }
        .section {
            margin-bottom: 25px;
            page-break-inside: avoid;
        }
        .section-title {
            background-color: #f8f9fa;
            padding: 8px 12px;
            font-weight: bold;
            font-size: 14px;
            border-left: 4px solid #007bff;
            margin-bottom: 15px;
        }
        .info-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        .info-table td {
            padding: 6px 8px;
            border-bottom: 1px solid #e9ecef;
            vertical-align: top;
        }
        .info-table td:first-child {
            font-weight: bold;
            width: 30%;
            background-color: #f8f9fa;
        }
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
            font-size: 10px;
        }
        .data-table th,
        .data-table td {
            padding: 6px 8px;
            border: 1px solid #dee2e6;
            text-align: left;
        }
        .data-table th {
            background-color: #007bff;
            color: white;
            font-weight: bold;
        }
        .data-table tr:nth-child(even) {
            background-color: #f8f9fa;
        }
        .amount {
            text-align: right;
            font-weight: bold;
        }
        .status-active {
            color: #28a745;
            font-weight: bold;
        }
        .summary-box {
            background-color: #e3f2fd;
            border: 1px solid #2196f3;
            padding: 15px;
            margin: 15px 0;
            border-radius: 5px;
        }
        .summary-title {
            font-weight: bold;
            font-size: 14px;
            margin-bottom: 10px;
            color: #1976d2;
        }
        .summary-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 5px;
        }
        .summary-label {
            font-weight: bold;
        }
        .summary-value {
            font-weight: bold;
            color: #1976d2;
        }
        .page-break {
            page-break-before: always;
        }
        .no-data {
            text-align: center;
            color: #666;
            font-style: italic;
            padding: 20px;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="company-name">{{ $company->name ?? 'SmartFinance' }}</div>
        <div class="report-title">COMPREHENSIVE LOAN DETAILS REPORT</div>
        <div class="export-date">Exported on: {{ $exportDate }}</div>
    </div>

    <!-- Customer Information -->
    <div class="section">
        <div class="section-title">CUSTOMER INFORMATION</div>
        <table class="info-table">
            <tr>
                <td>Customer Name</td>
                <td>{{ $loan->customer->name }}</td>
            </tr>
            <tr>
                <td>Customer Number</td>
                <td>{{ $loan->customer->customerNo ?? 'N/A' }}</td>
            </tr>
            <tr>
                <td>Phone Number</td>
                <td>{{ $loan->customer->phone1 ?? 'N/A' }}</td>
            </tr>
            <tr>
                <td>Secondary Phone</td>
                <td>{{ $loan->customer->phone2 ?? 'N/A' }}</td>
            </tr>
            <tr>
                <td>ID Type</td>
                <td>{{ $loan->customer->idType ?? 'N/A' }}</td>
            </tr>
            <tr>
                <td>ID Number</td>
                <td>{{ $loan->customer->idNumber ?? 'N/A' }}</td>
            </tr>
            <tr>
                <td>Date of Birth</td>
                <td>{{ $loan->customer->dob ? $loan->customer->dob->format('Y-m-d') : 'N/A' }}</td>
            </tr>
            <tr>
                <td>Gender</td>
                <td>{{ ucfirst($loan->customer->sex ?? 'N/A') }}</td>
            </tr>
            <tr>
                <td>Region</td>
                <td>{{ $loan->customer->region->name ?? 'N/A' }}</td>
            </tr>
            <tr>
                <td>District</td>
                <td>{{ $loan->customer->district->name ?? 'N/A' }}</td>
            </tr>
            <tr>
                <td>Work</td>
                <td>{{ $loan->customer->work ?? 'N/A' }}</td>
            </tr>
            <tr>
                <td>Work Address</td>
                <td>{{ $loan->customer->workAddress ?? 'N/A' }}</td>
            </tr>
            <tr>
                <td>Date Registered</td>
                <td>{{ $loan->customer->dateRegistered ? $loan->customer->dateRegistered->format('Y-m-d') : 'N/A' }}</td>
            </tr>
        </table>
    </div>

    <!-- Loan Information -->
    <div class="section">
        <div class="section-title">LOAN INFORMATION</div>
        <table class="info-table">
            <tr>
                <td>Loan Number</td>
                <td>{{ $loan->loanNo }}</td>
            </tr>
            <tr>
                <td>Loan Status</td>
                <td><span class="status-active">{{ strtoupper($loan->status) }}</span></td>
            </tr>
            <tr>
                <td>Product</td>
                <td>{{ $loan->product->name ?? 'N/A' }}</td>
            </tr>
            <tr>
                <td>Branch</td>
                <td>{{ $loan->branch->name ?? 'N/A' }}</td>
            </tr>
            <tr>
                <td>Group</td>
                <td>{{ $loan->group->name ?? 'N/A' }}</td>
            </tr>
            <tr>
                <td>Bank Account</td>
                <td>{{ $loan->bankAccount->name ?? 'N/A' }}</td>
            </tr>
            <tr>
                <td>Sector</td>
                <td>{{ $loan->sector ?? 'N/A' }}</td>
            </tr>
            <tr>
                <td>Loan Officer</td>
                <td>{{ $loan->loanOfficer->name ?? 'N/A' }}</td>
            </tr>
            <tr>
                <td>Date Applied</td>
                <td>{{ $loan->date_applied ? \Carbon\Carbon::parse($loan->date_applied)->format('Y-m-d') : 'N/A' }}</td>
            </tr>
            <tr>
                <td>Date Disbursed</td>
                <td>{{ $loan->disbursed_on ? \Carbon\Carbon::parse($loan->disbursed_on)->format('Y-m-d') : 'N/A' }}</td>
            </tr>
            <tr>
                <td>First Repayment Date</td>
                <td>{{ $loan->first_repayment_date ? \Carbon\Carbon::parse($loan->first_repayment_date)->format('Y-m-d') : 'N/A' }}</td>
            </tr>
            <tr>
                <td>Last Repayment Date</td>
                <td>{{ $loan->last_repayment_date ? \Carbon\Carbon::parse($loan->last_repayment_date)->format('Y-m-d') : 'N/A' }}</td>
            </tr>
        </table>
    </div>

    <!-- Financial Summary -->
    <div class="section">
        <div class="section-title">FINANCIAL SUMMARY</div>
        <div class="summary-box">
            <div class="summary-title">Loan Financial Overview</div>
            <div class="summary-row">
                <span class="summary-label">Principal Amount:</span>
                <span class="summary-value">TZS {{ number_format($loan->amount, 2) }}</span>
            </div>
            <div class="summary-row">
                <span class="summary-label">Interest Rate:</span>
                <span class="summary-value">{{ $loan->interest }}%</span>
            </div>
            <div class="summary-row">
                <span class="summary-label">Interest Amount:</span>
                <span class="summary-value">TZS {{ number_format($loan->interest_amount, 2) }}</span>
            </div>
            <div class="summary-row">
                <span class="summary-label">Total Amount:</span>
                <span class="summary-value">TZS {{ number_format($loan->amount_total, 2) }}</span>
            </div>
            <div class="summary-row">
                <span class="summary-label">Period (Months):</span>
                <span class="summary-value">{{ $loan->period }}</span>
            </div>
            <div class="summary-row">
                <span class="summary-label">Total Paid:</span>
                <span class="summary-value">TZS {{ number_format($totalPaid, 2) }}</span>
            </div>
            <div class="summary-row">
                <span class="summary-label">Remaining Balance:</span>
                <span class="summary-value">TZS {{ number_format($remainingBalance, 2) }}</span>
            </div>
        </div>
    </div>

    <!-- Payment Breakdown -->
    <div class="section">
        <div class="section-title">PAYMENT BREAKDOWN</div>
        <div class="summary-box">
            <div class="summary-title">Payment Summary</div>
            <div class="summary-row">
                <span class="summary-label">Principal Paid:</span>
                <span class="summary-value">TZS {{ number_format($totalPrincipalPaid, 2) }}</span>
            </div>
            <div class="summary-row">
                <span class="summary-label">Interest Paid:</span>
                <span class="summary-value">TZS {{ number_format($totalInterestPaid, 2) }}</span>
            </div>
            <div class="summary-row">
                <span class="summary-label">Fees Paid:</span>
                <span class="summary-value">TZS {{ number_format($totalFeesPaid, 2) }}</span>
            </div>
            <div class="summary-row">
                <span class="summary-label">Penalties Paid:</span>
                <span class="summary-value">TZS {{ number_format($totalPenaltiesPaid, 2) }}</span>
            </div>
        </div>
    </div>

    <!-- Loan Schedule -->
    @if($loan->schedule && $loan->schedule->count() > 0)
    <div class="section page-break">
        <div class="section-title">LOAN REPAYMENT SCHEDULE</div>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Due Date</th>
                    <th>Principal</th>
                    <th>Interest</th>
                    <th>Fees</th>
                    <th>Penalties</th>
                    <th>Total Due</th>
                    <th>Paid Amount</th>
                    <th>Remaining</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach($loan->schedule as $schedule)
                <tr>
                    <td>{{ \Carbon\Carbon::parse($schedule->due_date)->format('Y-m-d') }}</td>
                    <td class="amount">TZS {{ number_format($schedule->principal, 2) }}</td>
                    <td class="amount">TZS {{ number_format($schedule->interest, 2) }}</td>
                    <td class="amount">TZS {{ number_format($schedule->fee_amount, 2) }}</td>
                    <td class="amount">TZS {{ number_format($schedule->penalty_amount, 2) }}</td>
                    <td class="amount">TZS {{ number_format($schedule->principal + $schedule->interest + $schedule->fee_amount + $schedule->penalty_amount, 2) }}</td>
                    <td class="amount">TZS {{ number_format($schedule->paid_amount, 2) }}</td>
                    <td class="amount">TZS {{ number_format($schedule->remaining_amount, 2) }}</td>
                    <td>{{ $schedule->is_fully_paid ? 'Paid' : 'Pending' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    <!-- Fees Received Through Receipts -->
    @if($receipts && $receipts->count() > 0)
    <div class="section page-break">
        <div class="section-title">FEES RECEIVED THROUGH RECEIPTS</div>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Receipt Date</th>
                    <th>Receipt Reference</th>
                    <th>Chart Account</th>
                    <th>Amount</th>
                    <th>Description</th>
                </tr>
            </thead>
            <tbody>
                @foreach($receipts as $receipt)
                    @foreach($receipt->receiptItems as $item)
                        @php
                            $chartAccount = \App\Models\ChartAccount::find($item->chart_account_id);
                            $isFeeRelated = $chartAccount && (
                                stripos($chartAccount->account_name, 'fee') !== false ||
                                stripos($chartAccount->account_name, 'income') !== false ||
                                stripos($chartAccount->account_name, 'service') !== false
                            );
                        @endphp
                        @if($isFeeRelated)
                        <tr>
                            <td>{{ $receipt->date ? $receipt->date->format('Y-m-d') : 'N/A' }}</td>
                            <td>{{ $receipt->reference_number ?? 'N/A' }}</td>
                            <td>{{ $chartAccount ? $chartAccount->account_name : 'N/A' }}</td>
                            <td class="amount">TZS {{ number_format($item->amount ?? 0, 2) }}</td>
                            <td>{{ $item->description ?? 'N/A' }}</td>
                        </tr>
                        @endif
                    @endforeach
                @endforeach
            </tbody>
        </table>
        <div class="summary-row">
            <strong>Total Fees Received Through Receipts: TZS {{ number_format($feesReceivedThroughReceipts, 2) }}</strong>
        </div>
    </div>
    @endif

    <!-- Repayment History -->
    @if($loan->repayments && $loan->repayments->count() > 0)
    <div class="section page-break">
        <div class="section-title">REPAYMENT HISTORY</div>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Principal</th>
                    <th>Interest</th>
                    <th>Fees</th>
                    <th>Penalties</th>
                    <th>Total Amount</th>
                    <th>Payment Method</th>
                    <th>Reference</th>
                </tr>
            </thead>
            <tbody>
                @foreach($loan->repayments as $repayment)
                <tr>
                    <td>{{ \Carbon\Carbon::parse($repayment->created_at)->format('Y-m-d H:i') }}</td>
                    <td class="amount">TZS {{ number_format($repayment->principal, 2) }}</td>
                    <td class="amount">TZS {{ number_format($repayment->interest, 2) }}</td>
                    <td class="amount">TZS {{ number_format($repayment->fee_amount, 2) }}</td>
                    <td class="amount">TZS {{ number_format($repayment->penalt_amount, 2) }}</td>
                    <td class="amount">TZS {{ number_format($repayment->principal + $repayment->interest + $repayment->fee_amount + $repayment->penalt_amount, 2) }}</td>
                    <td>{{ $repayment->payment_method ?? 'N/A' }}</td>
                    <td>{{ $repayment->reference ?? 'N/A' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    <!-- Guarantors -->
    @if($loan->guarantors && $loan->guarantors->count() > 0)
    <div class="section page-break">
        <div class="section-title">GUARANTORS</div>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Phone</th>
                    <th>ID Number</th>
                    <th>Relation</th>
                    <th>Date Added</th>
                </tr>
            </thead>
            <tbody>
                @foreach($loan->guarantors as $guarantor)
                <tr>
                    <td>{{ $guarantor->name }}</td>
                    <td>{{ $guarantor->phone1 ?? 'N/A' }}</td>
                    <td>{{ $guarantor->idNumber ?? 'N/A' }}</td>
                    <td>{{ $guarantor->pivot->relation ?? 'N/A' }}</td>
                    <td>{{ $guarantor->pivot->created_at ? \Carbon\Carbon::parse($guarantor->pivot->created_at)->format('Y-m-d') : 'N/A' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    <!-- Collaterals -->
    @if($loan->collaterals && $loan->collaterals->count() > 0)
    <div class="section page-break">
        <div class="section-title">COLLATERALS</div>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Type</th>
                    <th>Description</th>
                    <th>Value</th>
                    <th>Date Added</th>
                </tr>
            </thead>
            <tbody>
                @foreach($loan->collaterals as $collateral)
                <tr>
                    <td>{{ $collateral->type ?? 'N/A' }}</td>
                    <td>{{ $collateral->description ?? 'N/A' }}</td>
                    <td class="amount">TZS {{ number_format($collateral->value ?? 0, 2) }}</td>
                    <td>{{ $collateral->created_at ? \Carbon\Carbon::parse($collateral->created_at)->format('Y-m-d') : 'N/A' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    <!-- Loan Fees -->
    @if($loanFees && count($loanFees) > 0)
    <div class="section page-break">
        <div class="section-title">LOAN FEES</div>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Fee Name</th>
                    <th>Type</th>
                    <th>Amount</th>
                    <th>Description</th>
                </tr>
            </thead>
            <tbody>
                @foreach($loanFees as $fee)
                <tr>
                    <td>{{ $fee->name }}</td>
                    <td>{{ ucfirst($fee->fee_type ?? 'N/A') }}</td>
                    <td class="amount">
                        @if($fee->isPercentage())
                            {{ number_format($fee->amount ?? 0, 2) }}% 
                            (TZS {{ number_format(($loan->amount * ($fee->amount ?? 0)) / 100, 2) }})
                        @else
                            TZS {{ number_format($fee->amount ?? 0, 2) }}
                        @endif
                    </td>
                    <td>{{ $fee->description ?? 'N/A' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    <!-- Loan Penalties -->
    @if($loanPenalties && count($loanPenalties) > 0)
    <div class="section page-break">
        <div class="section-title">LOAN PENALTIES</div>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Penalty Name</th>
                    <th>Type</th>
                    <th>Amount/Rate</th>
                    <th>Charge Frequency</th>
                    <th>Deduction Type</th>
                    <th>Description</th>
                </tr>
            </thead>
            <tbody>
                @foreach($loanPenalties as $penalty)
                <tr>
                    <td>{{ $penalty->name }}</td>
                    <td>{{ ucfirst($penalty->penalty_type ?? 'N/A') }}</td>
                    <td class="amount">
                        @if($penalty->isPercentage())
                            {{ number_format($penalty->amount ?? 0, 2) }}% 
                            (TZS {{ number_format(($loan->amount * ($penalty->amount ?? 0)) / 100, 2) }})
                        @else
                            TZS {{ number_format($penalty->amount ?? 0, 2) }}
                        @endif
                    </td>
                    <td>{{ ucfirst(str_replace('_', ' ', $penalty->charge_frequency ?? 'N/A')) }}</td>
                    <td>{{ $penalty->deduction_type_label ?? 'N/A' }}</td>
                    <td>{{ $penalty->description ?? 'N/A' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    <!-- Approval History -->
    @if($loan->approvals && $loan->approvals->count() > 0)
    <div class="section page-break">
        <div class="section-title">APPROVAL HISTORY</div>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Level</th>
                    <th>Action</th>
                    <th>Approved By</th>
                    <th>Role</th>
                    <th>Date</th>
                    <th>Comments</th>
                </tr>
            </thead>
            <tbody>
                @foreach($loan->approvals as $approval)
                <tr>
                    <td>{{ $approval->approval_level }}</td>
                    <td>{{ ucfirst($approval->action) }}</td>
                    <td>{{ $approval->user->name ?? 'N/A' }}</td>
                    <td>{{ $approval->role_name ?? 'N/A' }}</td>
                    <td>{{ $approval->approved_at ? \Carbon\Carbon::parse($approval->approved_at)->format('Y-m-d H:i') : 'N/A' }}</td>
                    <td>{{ $approval->comments ?? 'N/A' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    <!-- Loan Documents -->
    @if($loan->loanFiles && $loan->loanFiles->count() > 0)
    <div class="section page-break">
        <div class="section-title">LOAN DOCUMENTS</div>
        <table class="data-table">
            <thead>
                <tr>
                    <th>File Type</th>
                    <th>File Name</th>
                    <th>Uploaded By</th>
                    <th>Date Uploaded</th>
                </tr>
            </thead>
            <tbody>
                @foreach($loan->loanFiles as $file)
                <tr>
                    <td>{{ $file->filetype->name ?? 'N/A' }}</td>
                    <td>{{ $file->filename ?? 'N/A' }}</td>
                    <td>{{ $file->user->name ?? 'N/A' }}</td>
                    <td>{{ $file->created_at ? \Carbon\Carbon::parse($file->created_at)->format('Y-m-d H:i') : 'N/A' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    <div style="margin-top: 50px; text-align: center; font-size: 10px; color: #666;">
        <p>This report was generated on {{ $exportDate }} by {{ $company->name ?? 'SmartFinance' }}</p>
        <p>For any questions regarding this loan, please contact your loan officer or branch manager.</p>
    </div>
</body>
</html>
