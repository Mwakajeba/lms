<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\StreamedResponse;
use App\Models\Loan;
use App\Models\Customer;
use Carbon\Carbon;

class BotSectoralLoansController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $asOfDate = $request->get('as_of_date', now()->format('Y-m-d'));

        // Debug: Log the current data
        \Log::info('=== BOT SECTORAL LOANS REPORT DEBUG ===');
        
        // Get all sectors from the loans table
        $sectors = Loan::distinct()
            ->whereNotNull('sector')
            ->where('sector', '!=', '')
            ->pluck('sector')
            ->toArray();
        
        // If no sectors found, use default sectors
        if (empty($sectors)) {
            $sectors = [
                'Agriculture','Fishing','Forest','Hunting','Financial Intermediaries',
                'Mining and Quarrying','Manufacturing','Building and Construction',
                'Real Estate','Leasing','Transport and Communication','Trade',
                'Tourism','Hotels and Restaurants','Warehousing and Storage',
                'Electricity','Gas','Water','Education','Health',
                'Other Services','Personal (Private)'
            ];
        }
        
        \Log::info('Sectors found in database:', [
            'sectors' => $sectors,
            'count' => count($sectors)
        ]);

        // Get all loans with their customers
        $allLoans = Loan::with(['customer'])
            ->whereNotNull('sector')
            ->where('sector', '!=', '')
            ->get();
            
        // Get total cash collateral balance
        $totalCashCollateral = \App\Models\CashCollateral::sum('amount');
        
        \Log::info('Cash Collateral found:', [
            'total_amount' => $totalCashCollateral
        ]);
        
        \Log::info('Loans found:', [
            'count' => $allLoans->count(),
            'loans' => $allLoans->map(function($loan) {
                return [
                    'id' => $loan->id,
                    'sector' => $loan->sector,
                    'status' => $loan->status,
                    'amount_total' => $loan->amount_total,
                    'amount' => $loan->amount,
                    'customer_name' => $loan->customer->name ?? 'No customer'
                ];
            })->toArray()
        ]);

        // Initialize sector data
        $sectorData = [];
        foreach ($sectors as $sector) {
            $sectorData[$sector] = [
                'sno' => 0,
                'sector' => $sector,
                'borrowers' => 0,
                'total_outstanding' => 0,
                'current_amount' => 0,
                'past_due' => [
                    'ESM' => 0,
                    'Substandard' => 0,
                    'Doubtful' => 0,
                    'Loss' => 0,
                ],
                'written_off' => 0,
                'cash_collateral' => 0,
                'loans' => []
            ];
        }

        // Process each loan
        foreach ($allLoans as $loan) {
            $sector = $loan->sector;
            $amount = $loan->amount_total ?? $loan->amount ?? 0;
            $status = $loan->status;
            
            if (!isset($sectorData[$sector])) {
                // Create new sector if not in our list
                $sectorData[$sector] = [
                    'sno' => 0,
                    'sector' => $sector,
                    'borrowers' => 0,
                    'total_outstanding' => 0,
                    'current_amount' => 0,
                    'past_due' => [
                        'ESM' => 0,
                        'Substandard' => 0,
                        'Doubtful' => 0,
                        'Loss' => 0,
                    ],
                    'written_off' => 0,
                    'loans' => []
                ];
            }
            
            // Add loan to sector
            $sectorData[$sector]['loans'][] = [
                'id' => $loan->id,
                'amount' => $amount,
                'status' => $status,
                'customer_id' => $loan->customer_id
            ];
            
            // Categorize by status
            switch (strtolower($status)) {
                case 'active':
                    $sectorData[$sector]['current_amount'] += $amount;
                    break;
                case 'defaulted':
                case 'overdue':
                    $sectorData[$sector]['past_due']['ESM'] += $amount;
                    break;
                case 'substandard':
                    $sectorData[$sector]['past_due']['Substandard'] += $amount;
                    break;
                case 'doubtful':
                    $sectorData[$sector]['past_due']['Doubtful'] += $amount;
                    break;
                case 'loss':
                    $sectorData[$sector]['past_due']['Loss'] += $amount;
                    break;
                case 'written_off':
                    $sectorData[$sector]['written_off'] += $amount;
                    break;
                default:
                    // For other statuses, treat as current
                    $sectorData[$sector]['current_amount'] += $amount;
                    break;
            }
            
            // Add to total outstanding
            $sectorData[$sector]['total_outstanding'] += $amount;
        }
        
        // Calculate unique borrowers per sector and add cash collateral to first sector
        $firstSector = true;
        foreach ($sectorData as $sector => &$data) {
            $uniqueCustomers = collect($data['loans'])->pluck('customer_id')->unique();
            $data['borrowers'] = $uniqueCustomers->count();
            $data['sno'] = 0; // Will be set in the loop below
            
            // Add cash collateral to the first sector that has loans
            if ($firstSector && $data['total_outstanding'] > 0) {
                $data['cash_collateral'] = $totalCashCollateral;
                $firstSector = false;
            }
        }
        unset($data); // VERY IMPORTANT: break the reference to avoid duplication
        
        \Log::info('Sector Data processed:', [
            'sectors_count' => count($sectorData),
            'sample_data' => array_slice($sectorData, 0, 3, true)
        ]);

        // Convert to rows array and add serial numbers
        $rows = [];
        $sno = 1;
        foreach ($sectorData as $sector => $data) {
            $data['sno'] = $sno++;
            $rows[] = $data;
        }
        
        \Log::info('Final Report Rows:', [
            'rows_count' => count($rows),
            'sample_rows' => array_slice($rows, 0, 3)
        ]);

        return view('reports.bot.sectoral-loans', compact('user', 'asOfDate', 'rows', 'sectorData'));
    }

    public function export(Request $request): StreamedResponse
    {
        $asOfDate = $request->get('as_of_date', now()->format('Y-m-d'));
        $filename = 'BOT_Sectoral_Loans_' . $asOfDate . '.xlsx';
        
        // Get the same data as the index method
        $sectors = Loan::distinct()
            ->whereNotNull('sector')
            ->where('sector', '!=', '')
            ->pluck('sector')
            ->toArray();
        
        if (empty($sectors)) {
            $sectors = [
                'Agriculture','Fishing','Forest','Hunting','Financial Intermediaries',
                'Mining and Quarrying','Manufacturing','Building and Construction',
                'Real Estate','Leasing','Transport and Communication','Trade',
                'Tourism','Hotels and Restaurants','Warehousing and Storage',
                'Electricity','Gas','Water','Education','Health',
                'Other Services','Personal (Private)'
            ];
        }
        
        $allLoans = Loan::with(['customer'])
            ->whereNotNull('sector')
            ->where('sector', '!=', '')
            ->get();
            
        $totalCashCollateral = \App\Models\CashCollateral::sum('amount');
        
        // Initialize sector data
        $sectorData = [];
        foreach ($sectors as $sector) {
            $sectorData[$sector] = [
                'sno' => 0,
                'sector' => $sector,
                'borrowers' => 0,
                'total_outstanding' => 0,
                'current_amount' => 0,
                'past_due' => [
                    'ESM' => 0,
                    'Substandard' => 0,
                    'Doubtful' => 0,
                    'Loss' => 0,
                ],
                'written_off' => 0,
                'cash_collateral' => 0,
                'loans' => []
            ];
        }
        
        // Process each loan
        foreach ($allLoans as $loan) {
            $sector = $loan->sector;
            $amount = $loan->amount_total ?? $loan->amount ?? 0;
            $status = $loan->status;
            
            if (!isset($sectorData[$sector])) {
                $sectorData[$sector] = [
                    'sno' => 0,
                    'sector' => $sector,
                    'borrowers' => 0,
                    'total_outstanding' => 0,
                    'current_amount' => 0,
                    'past_due' => [
                        'ESM' => 0,
                        'Substandard' => 0,
                        'Doubtful' => 0,
                        'Loss' => 0,
                    ],
                    'written_off' => 0,
                    'cash_collateral' => 0,
                    'loans' => []
                ];
            }
            
            $sectorData[$sector]['loans'][] = [
                'id' => $loan->id,
                'amount' => $amount,
                'status' => $status,
                'customer_id' => $loan->customer_id
            ];
            
            // Categorize by status
            switch (strtolower($status)) {
                case 'active':
                    $sectorData[$sector]['current_amount'] += $amount;
                    break;
                case 'defaulted':
                case 'overdue':
                    $sectorData[$sector]['past_due']['ESM'] += $amount;
                    break;
                case 'substandard':
                    $sectorData[$sector]['past_due']['Substandard'] += $amount;
                    break;
                case 'doubtful':
                    $sectorData[$sector]['past_due']['Doubtful'] += $amount;
                    break;
                case 'loss':
                    $sectorData[$sector]['past_due']['Loss'] += $amount;
                    break;
                case 'written_off':
                    $sectorData[$sector]['written_off'] += $amount;
                    break;
                default:
                    $sectorData[$sector]['current_amount'] += $amount;
                    break;
            }
            
            $sectorData[$sector]['total_outstanding'] += $amount;
        }
        
        // Calculate unique borrowers per sector and add cash collateral to first sector
        $firstSector = true;
        foreach ($sectorData as $sector => &$data) {
            $uniqueCustomers = collect($data['loans'])->pluck('customer_id')->unique();
            $data['borrowers'] = $uniqueCustomers->count();
            $data['sno'] = 0;
            
            if ($firstSector && $data['total_outstanding'] > 0) {
                $data['cash_collateral'] = $totalCashCollateral;
                $firstSector = false;
            }
        }
        unset($data); // VERY IMPORTANT: break the reference to avoid duplication
        
        // Convert to rows array and add serial numbers
        $rows = [];
        $sno = 1;
        foreach ($sectorData as $sector => $data) {
            $data['sno'] = $sno++;
            $rows[] = $data;
        }
        
        // Calculate totals
        $totalBorrowers = collect($rows)->sum('borrowers');
        $totalOutstanding = collect($rows)->sum('total_outstanding');
        $totalCurrent = collect($rows)->sum('current_amount');
        $totalESM = collect($rows)->sum('past_due.ESM');
        $totalSubstandard = collect($rows)->sum('past_due.Substandard');
        $totalDoubtful = collect($rows)->sum('past_due.Doubtful');
        $totalLoss = collect($rows)->sum('past_due.Loss');
        $totalWrittenOff = collect($rows)->sum('written_off');
        $totalCashCollateral = collect($rows)->sum('cash_collateral');
        
        // Calculate provision amounts
        $provisionESM = $totalESM * 0.00;
        $provisionSubstandard = $totalSubstandard * 0.10;
        $provisionDoubtful = $totalDoubtful * 0.50;
        $provisionLoss = $totalLoss * 1.00;
        $totalProvision = $provisionESM + $provisionSubstandard + $provisionDoubtful + $provisionLoss;
        $netProvision = max(0, $totalProvision - $totalCashCollateral);
        $totalNetAmount = $totalOutstanding - $netProvision;
        $nonPerformingRatio = $totalOutstanding > 0 ? (($totalESM + $totalSubstandard + $totalDoubtful + $totalLoss) / $totalOutstanding) * 100 : 0;
        
        return response()->streamDownload(function () use ($rows, $totalBorrowers, $totalOutstanding, $totalCurrent, $totalESM, $totalSubstandard, $totalDoubtful, $totalLoss, $totalWrittenOff, $totalCashCollateral, $totalProvision, $netProvision, $totalNetAmount, $nonPerformingRatio, $asOfDate) {
            $this->generateExcelContent($rows, $totalBorrowers, $totalOutstanding, $totalCurrent, $totalESM, $totalSubstandard, $totalDoubtful, $totalLoss, $totalWrittenOff, $totalCashCollateral, $totalProvision, $netProvision, $totalNetAmount, $nonPerformingRatio, $asOfDate);
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
        ]);
    }
    
    private function generateExcelContent($rows, $totalBorrowers, $totalOutstanding, $totalCurrent, $totalESM, $totalSubstandard, $totalDoubtful, $totalLoss, $totalWrittenOff, $totalCashCollateral, $totalProvision, $netProvision, $totalNetAmount, $nonPerformingRatio, $asOfDate)
    {
        // Create CSV content (Excel can open CSV files)
        $output = fopen('php://output', 'w');
        
        // Header
        fputcsv($output, ['BOT SECTORAL CLASSIFICATION OF MICROFINANCE LOANS']);
        fputcsv($output, ['']);
        fputcsv($output, ['BOT FORM MSP2-03 to be submitted Quarterly (Amount in TZS)']);
        fputcsv($output, ['AS AT: ' . \Carbon\Carbon::parse($asOfDate)->format('d/m/Y')]);
        fputcsv($output, ['']);
        
        // Table headers
        fputcsv($output, ['Sno', 'Sector', 'Number of Borrowers', 'Total Outstanding', 'Current Amount', 'ESM', 'Substandard', 'Doubtful', 'Loss', 'Amount Written-off']);
        fputcsv($output, ['']);
        
        // Data rows
        foreach ($rows as $row) {
            fputcsv($output, [
                $row['sno'],
                $row['sector'],
                $row['borrowers'],
                number_format($row['total_outstanding'], 2),
                number_format($row['current_amount'], 2),
                number_format($row['past_due']['ESM'], 2),
                number_format($row['past_due']['Substandard'], 2),
                number_format($row['past_due']['Doubtful'], 2),
                number_format($row['past_due']['Loss'], 2),
                number_format($row['written_off'], 2)
            ]);
        }
        
        // Totals row
        fputcsv($output, ['']);
        fputcsv($output, ['Total', '', $totalBorrowers, number_format($totalOutstanding, 2), number_format($totalCurrent, 2), number_format($totalESM, 2), number_format($totalSubstandard, 2), number_format($totalDoubtful, 2), number_format($totalLoss, 2), number_format($totalWrittenOff, 2)]);
        
        // Summary section
        fputcsv($output, ['']);
        fputcsv($output, ['PROVISION RATE']);
        fputcsv($output, ['Classification', 'Provision Rate', 'Amount']);
        fputcsv($output, ['ESM', '0%', number_format($totalESM, 2)]);
        fputcsv($output, ['Substandard', '10%', number_format($totalSubstandard, 2)]);
        fputcsv($output, ['Doubtful', '50%', number_format($totalDoubtful, 2)]);
        fputcsv($output, ['Loss', '100%', number_format($totalLoss, 2)]);
        fputcsv($output, ['Total Past Due', '', number_format($totalESM + $totalSubstandard + $totalDoubtful + $totalLoss, 2)]);
        
        fputcsv($output, ['']);
        fputcsv($output, ['SUMMARY']);
        fputcsv($output, ['Item', 'Amount']);
        fputcsv($output, ['Provision Amount', number_format($totalProvision, 2)]);
        fputcsv($output, ['Cash Collateral/Insurance', number_format($totalCashCollateral, 2)]);
        fputcsv($output, ['Guarantees/Compulsory Saving', '0.00']);
        fputcsv($output, ['Net Provision Amount', number_format($netProvision, 2)]);
        fputcsv($output, ['TOTAL Net Amount', number_format($totalNetAmount, 2)]);
        fputcsv($output, ['Ratio of Non-Performing Loans to Gross Loans', number_format($nonPerformingRatio, 2) . '%']);
        
        fclose($output);
    }
} 