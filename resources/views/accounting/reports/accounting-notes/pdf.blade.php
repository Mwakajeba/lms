<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Accounting Notes Report</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 11px;
            line-height: 1.4;
            color: #333;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
        }
        .company-name {
            font-size: 16px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        .report-title {
            font-size: 14px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        .report-date {
            font-size: 11px;
            margin-bottom: 10px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 6px;
            text-align: left;
            vertical-align: top;
        }
        th {
            background-color: #f8f9fa;
            font-weight: bold;
            text-align: center;
        }
        .section-header {
            background-color: #e9ecef;
            font-weight: bold;
            text-align: center;
        }
        .subsection-header {
            background-color: #f8f9fa;
            font-weight: bold;
        }
        .indent {
            padding-left: 20px;
        }
        .text-center {
            text-align: center;
        }
        .text-left {
            text-align: left;
        }
        .page-break {
            page-break-before: always;
        }
        .logo-wrapper { text-align: center; margin-bottom: 10px; }
        .logo-wrapper img { max-height: 70px; }
    </style>
</head>
<body>
        @php
        $companyModel = isset($company) ? $company : (function_exists('current_company') ? current_company() : null);
        $logoPath = ($companyModel && !empty($companyModel->logo)) ? public_path('storage/' . $companyModel->logo) : null;
    @endphp
    @if($logoPath && file_exists($logoPath))
        <div class="logo-wrapper">
            <img src="{{ $logoPath }}" alt="Company Logo">
        </div>
    @endif
    <div class="header">
        <div class="company-name">{{ $company->name ?? 'SmartFinance' }}</div>
        <div class="report-title">ACCOUNT CLASSES REPORT</div>
        <div class="report-date">
            AS AT {{ \Carbon\Carbon::parse($accountingNotesData['as_of_date'])->format('d-m-Y') }}
            @if(isset($branchName))
                | BRANCH: {{ $branchName }}
            @endif
        </div>
    </div>

    <!-- Summary Statistics -->
    <table>
        <tr class="section-header">
            <td colspan="4">SUMMARY STATISTICS</td>
        </tr>
        <tr>
            <td><strong>Total Account Classes:</strong></td>
            <td>{{ $accountingNotesData['account_classes_data']['summary']['total_classes'] }}</td>
            <td><strong>Total Account Groups:</strong></td>
            <td>{{ $accountingNotesData['account_classes_data']['summary']['total_groups'] }}</td>
            </tr>
            <tr>
            <td><strong>Total Chart Accounts:</strong></td>
            <td>{{ $accountingNotesData['account_classes_data']['summary']['total_accounts'] }}</td>
            <td><strong>Total Transactions:</strong></td>
            <td>{{ number_format($accountingNotesData['account_classes_data']['summary']['total_transactions']) }}</td>
            </tr>
        <tr>
            <td><strong>Total Debit:</strong></td>
            <td>{{ number_format($accountingNotesData['account_classes_data']['summary']['total_debit'], 2) }}</td>
            <td><strong>Total Credit:</strong></td>
            <td>{{ number_format($accountingNotesData['account_classes_data']['summary']['total_credit'], 2) }}</td>
        </tr>
        <tr>
            <td><strong>Net Amount:</strong></td>
            <td colspan="3">{{ number_format($accountingNotesData['account_classes_data']['summary']['total_net'], 2) }}</td>
        </tr>
    </table>

    <!-- Account Classes Hierarchical Detail -->
    @php
        $groupedData = collect($accountingNotesData['account_classes_data']['data'])->groupBy('class_name');
    @endphp
    
    @foreach($groupedData as $className => $classData)
        <!-- Account Class Section -->
        <table>
            <tr class="section-header">
                <td colspan="6">{{ $className }}:</td>
            </tr>
        </table>
        
        @php
            $groupedByGroup = $classData->groupBy('group_name');
        @endphp
        
        @foreach($groupedByGroup as $groupName => $groupData)
            <!-- Account Group Section -->
            <table>
                @php
                    $groupTotalDebit = $groupData->sum('total_debit');
                    $groupTotalCredit = $groupData->sum('total_credit');
                    $groupNetAmount = $groupTotalDebit - $groupTotalCredit;
                    $groupAccountCount = $groupData->sum('account_count');
                    $groupTransactionCount = $groupData->sum('transaction_count');
                @endphp
            <tr class="subsection-header">
                    <td style="padding-left: 20px; width: 60%;">{{ $groupName }}</td>
                    <td style="text-align: right; width: 40%; font-size: 10px;">
                        D: {{ number_format($groupTotalDebit, 2) }} | 
                        C: {{ number_format($groupTotalCredit, 2) }} | 
                        <span style="color: #28a745; font-weight: bold;">Net: {{ number_format($groupNetAmount, 2) }}</span> | 
                        {{ $groupTransactionCount }} Transactions
                    </td>
            </tr>
                
                @if($accountingNotesData['account_classes_data']['level_of_detail'] === 'detailed')
                    <!-- Detailed View - Show individual accounts -->
                    <tr class="subsection-header">
                        <th style="width: 15%; padding-left: 40px;">Account Code</th>
                        <th style="width: 35%;">Account Name</th>
                        <th style="width: 12%; text-align: center;">Total Debit</th>
                        <th style="width: 12%; text-align: center;">Total Credit</th>
                        <th style="width: 12%; text-align: center;">Net Amount</th>
                        <th style="width: 14%; text-align: center;">Transactions</th>
        </tr>
        
                    @foreach($groupData as $item)
                        <tr>
                            <td style="padding-left: 40px;"><code>{{ $item->account_code }}</code></td>
                            <td>{{ $item->account_name }}</td>
                            <td class="text-center">{{ number_format($item->total_debit, 2) }}</td>
                            <td class="text-center">{{ number_format($item->total_credit, 2) }}</td>
                            <td class="text-center"><strong>{{ number_format($item->net_amount, 2) }}</strong></td>
                            <td class="text-center">{{ $item->transaction_count }}</td>
            </tr>
                    @endforeach
                @else
                    <!-- Summary View - No additional data needed since totals are in header -->
            <tr>
                        <td colspan="6" style="padding-left: 40px; text-align: center; color: #666; font-style: italic;">
                            Group totals shown in header above
                        </td>
            </tr>
                @endif
            </table>
            
            <!-- Add spacing between groups -->
            <table>
                <tr><td colspan="6" style="border: none; height: 10px;"></td></tr>
            </table>
        @endforeach
        
        <!-- Add spacing between classes -->
        <table>
            <tr><td colspan="6" style="border: none; height: 20px;"></td></tr>
    </table>
    @endforeach

    <div style="margin-top: 30px; font-size: 9px; color: #666;">
        <p><strong>This report was generated on {{ now()->format('d/m/Y H:i:s') }} by {{ $company->name ?? 'SAFCO FINTECH LTD' }}</strong></p>
        <p><strong>Report Period:</strong> 01/01/2025 to {{ \Carbon\Carbon::parse($accountingNotesData['as_of_date'])->format('d/m/Y') }}</p>
        <p><strong>Basis of Preparation:</strong> {{ ucfirst($accountingNotesData['reporting_type']) }}</p>
    </div>
</body>
</html> 