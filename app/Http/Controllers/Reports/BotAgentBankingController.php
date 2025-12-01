<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\StreamedResponse;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Carbon\Carbon;

class BotAgentBankingController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $asOfDate = $request->get('as_of_date', now()->format('Y-m-d'));

        $banksTz = [
            'CRDB BANK PLC',
            'NMB BANK PLC',
            'NATIONAL BANK OF COMMERCE (NBC) LIMITED',
            'ABSA BANK TANZANIA LIMITED',
            'STANDARD CHARTERED BANK TANZANIA LIMITED',
            'STANBIC BANK TANZANIA LIMITED',
            'EXIM BANK (TANZANIA) LIMITED',
            'DIAMOND TRUST BANK TANZANIA LIMITED (DTB)',
            'I&M BANK (T) LIMITED',
            'KCB BANK TANZANIA LIMITED',
            'EQUITY BANK TANZANIA LIMITED',
            'AZANIA BANK LIMITED',
            'TPB BANK PLC',
            'TIB CORPORATE BANK LIMITED',
            'UNITED BANK FOR AFRICA (UBA) TANZANIA LIMITED',
            'BANK OF BARODA (TANZANIA) LIMITED',
            'BANK OF INDIA (TANZANIA) LIMITED',
            'PEOPLE\'S BANK OF ZANZIBAR (PBZ) PLC',
            'DCB COMMERCIAL BANK PLC',
            'MKOMBOZI COMMERCIAL BANK PLC',
            'AMANA BANK LIMITED',
            'DIB BANK TANZANIA PLC',
            'ACCESS BANK TANZANIA LIMITED',
            'CITIBANK TANZANIA LIMITED'
        ];

        $mfsp = [
            'NATIONAL MICROFINANCE BANK (T) LTD.',
            'MWANGA RURAL COMMUNITY BANK',
            'KILIMANJARO COOPERATIVE BANK LIMITED',
            'MUFINDI COMMUNITY BANK LIMITED',
            'KAGERA COOPERATIVE BANK LIMITED',
            'MWANZA COOPERATIVE BANK LIMITED',
            'ARUSHA COOPERATIVE BANK LIMITED',
            'DODOMA COOPERATIVE BANK LIMITED',
            'TANGA COOPERATIVE BANK LIMITED',
            'MOROGORO COOPERATIVE BANK LIMITED',
            'IRINGA COOPERATIVE BANK LIMITED',
            'SONGEA COOPERATIVE BANK LIMITED',
            'MTWARA COOPERATIVE BANK LIMITED',
            'KIGOMA COOPERATIVE BANK LIMITED',
            'TABORA COOPERATIVE BANK LIMITED',
            'RUKWA COOPERATIVE BANK LIMITED',
            'RUVUMA COOPERATIVE BANK LIMITED',
            'MANYARA COOPERATIVE BANK LIMITED',
            'NJOMBE COOPERATIVE BANK LIMITED',
            'GEITA COOPERATIVE BANK LIMITED',
            'SIMIYU COOPERATIVE BANK LIMITED',
            'KATAVI COOPERATIVE BANK LIMITED',
            'SINGIDA COOPERATIVE BANK LIMITED',
            'PWANI COOPERATIVE BANK LIMITED',
            'DAR ES SALAAM COOPERATIVE BANK LIMITED'
        ];

        $mnos = [
            'MPESA TANZANIA LIMITED',
            'AIRTEL MONEY TANZANIA LIMITED',
            'TIGO PESA TANZANIA LIMITED',
            'HALOPESA TANZANIA LIMITED',
            'TPESA TANZANIA LIMITED',
            'EMOLA TANZANIA LIMITED',
            'T-PESA TANZANIA LIMITED'
        ];

        // Get company information for the report header
        $company = $user->company;
        
        return view('reports.bot.agent-banking', compact('user', 'asOfDate', 'banksTz', 'mfsp', 'mnos', 'company'));
    }

    public function export(Request $request): StreamedResponse
    {
        $user = Auth::user();
        $company = $user->company;
        $asOfDate = $request->get('as_of_date', now()->format('Y-m-d'));
        
        // Get the same data as the index method
        $banksTz = [
            'CRDB BANK PLC',
            'NMB BANK PLC',
            'NATIONAL BANK OF COMMERCE (NBC) LIMITED',
            'ABSA BANK TANZANIA LIMITED',
            'STANDARD CHARTERED BANK TANZANIA LIMITED',
            'STANBIC BANK TANZANIA LIMITED',
            'EXIM BANK (TANZANIA) LIMITED',
            'DIAMOND TRUST BANK TANZANIA LIMITED (DTB)',
            'I&M BANK (T) LIMITED',
            'KCB BANK TANZANIA LIMITED',
            'EQUITY BANK TANZANIA LIMITED',
            'AZANIA BANK LIMITED',
            'TPB BANK PLC',
            'TIB CORPORATE BANK LIMITED',
            'UNITED BANK FOR AFRICA (UBA) TANZANIA LIMITED',
            'BANK OF BARODA (TANZANIA) LIMITED',
            'BANK OF INDIA (TANZANIA) LIMITED',
            'PEOPLE\'S BANK OF ZANZIBAR (PBZ) PLC',
            'DCB COMMERCIAL BANK PLC',
            'MKOMBOZI COMMERCIAL BANK PLC',
            'AMANA BANK LIMITED',
            'DIB BANK TANZANIA PLC',
            'ACCESS BANK TANZANIA LIMITED',
            'CITIBANK TANZANIA LIMITED'
        ];

        $mfsp = [
            'NATIONAL MICROFINANCE BANK (T) LTD.',
            'MWANGA RURAL COMMUNITY BANK',
            'KILIMANJARO COOPERATIVE BANK LIMITED',
            'MUFINDI COMMUNITY BANK LIMITED',
            'KAGERA COOPERATIVE BANK LIMITED',
            'MWANZA COOPERATIVE BANK LIMITED',
            'ARUSHA COOPERATIVE BANK LIMITED',
            'DODOMA COOPERATIVE BANK LIMITED',
            'TANGA COOPERATIVE BANK LIMITED',
            'MOROGORO COOPERATIVE BANK LIMITED',
            'IRINGA COOPERATIVE BANK LIMITED',
            'SONGEA COOPERATIVE BANK LIMITED',
            'MTWARA COOPERATIVE BANK LIMITED',
            'KIGOMA COOPERATIVE BANK LIMITED',
            'TABORA COOPERATIVE BANK LIMITED',
            'RUKWA COOPERATIVE BANK LIMITED',
            'RUVUMA COOPERATIVE BANK LIMITED',
            'MANYARA COOPERATIVE BANK LIMITED',
            'NJOMBE COOPERATIVE BANK LIMITED',
            'GEITA COOPERATIVE BANK LIMITED',
            'SIMIYU COOPERATIVE BANK LIMITED',
            'KATAVI COOPERATIVE BANK LIMITED',
            'SINGIDA COOPERATIVE BANK LIMITED',
            'PWANI COOPERATIVE BANK LIMITED',
            'DAR ES SALAAM COOPERATIVE BANK LIMITED'
        ];

        $mnos = [
            'MPESA TANZANIA LIMITED',
            'AIRTEL MONEY TANZANIA LIMITED',
            'TIGO PESA TANZANIA LIMITED',
            'HALOPESA TANZANIA LIMITED',
            'TPESA TANZANIA LIMITED',
            'EMOLA TANZANIA LIMITED',
            'T-PESA TANZANIA LIMITED'
        ];

        return $this->generateExcel($company, $asOfDate, $banksTz, $mfsp, $mnos);
    }

    private function generateExcel($company, $asOfDate, $banksTz, $mfsp, $mnos)
    {
        $spreadsheet = new Spreadsheet();
        
        // Set document properties
        $spreadsheet->getProperties()
            ->setCreator($company->name ?? 'SmartFinance')
            ->setLastModifiedBy($company->name ?? 'SmartFinance')
            ->setTitle('BOT Agent Banking Balances Report')
            ->setSubject('Agent Banking Balances as of ' . Carbon::parse($asOfDate)->format('F d, Y'))
            ->setDescription('BOT Agent Banking Balances Report generated on ' . now()->format('F d, Y \a\t g:i A'));

        // Create worksheet
        $worksheet = $spreadsheet->getActiveSheet();
        $worksheet->setTitle('Agent Banking Balances');

        // Set headers with company information
        $row = 1;
        $worksheet->setCellValue('A' . $row, 'BOT AGENT BANKING BALANCES REPORT');
        $worksheet->getStyle('A' . $row)->getFont()->setBold(true)->setSize(14);
        $worksheet->mergeCells('A' . $row . ':D' . $row);
        $worksheet->getStyle('A' . $row)->getAlignment()->setHorizontal('center');
        $row++;

        $worksheet->setCellValue('A' . $row, 'NAME OF INSTITUTION:');
        $worksheet->setCellValue('B' . $row, $company->name ?? 'Company Name Not Set');
        $worksheet->getStyle('A' . $row)->getFont()->setBold(true);
        $row++;

        $worksheet->setCellValue('A' . $row, 'MSP CODE:');
        $worksheet->setCellValue('B' . $row, $company->msp_code ?? 'MSP Code Not Set');
        $worksheet->getStyle('A' . $row)->getFont()->setBold(true);
        $row++;

        $worksheet->setCellValue('A' . $row, 'AS AT DATE:');
        $worksheet->setCellValue('B' . $row, Carbon::parse($asOfDate)->format('d/m/Y'));
        $worksheet->getStyle('A' . $row)->getFont()->setBold(true);
        $row++;

        $worksheet->setCellValue('A' . $row, 'BOT FORM MSP2-08: To be submitted Quarterly');
        $worksheet->getStyle('A' . $row)->getFont()->setBold(true);
        $worksheet->mergeCells('A' . $row . ':D' . $row);
        $worksheet->getStyle('A' . $row)->getAlignment()->setHorizontal('center');
        $row++;

        $worksheet->setCellValue('A' . $row, '(Amount in TZS)');
        $worksheet->mergeCells('A' . $row . ':D' . $row);
        $worksheet->getStyle('A' . $row)->getAlignment()->setHorizontal('center');
        $row++;

        // Set table headers
        $worksheet->setCellValue('A' . $row, 'Sno');
        $worksheet->setCellValue('B' . $row, 'Name of Bank or Financial Institution');
        $worksheet->setCellValue('C' . $row, 'Balance');
        $worksheet->setCellValue('D' . $row, 'Validation');
        $worksheet->getStyle('A' . $row . ':D' . $row)->getFont()->setBold(true);
        $worksheet->getStyle('A' . $row . ':D' . $row)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setRGB('6C757D');
        $worksheet->getStyle('A' . $row . ':D' . $row)->getFont()->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_WHITE));
        $row++;

        // Banks in Tanzania Section
        $worksheet->setCellValue('A' . $row, '1');
        $worksheet->setCellValue('B' . $row, 'BANKS IN TANZANIA');
        $worksheet->setCellValue('C' . $row, '-');
        $worksheet->getStyle('A' . $row . ':D' . $row)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setRGB('17A2B8');
        $worksheet->getStyle('B' . $row)->getFont()->setBold(true);
        $row++;

        $sno = 2;
        foreach ($banksTz as $bank) {
            $worksheet->setCellValue('A' . $row, $sno);
            $worksheet->setCellValue('B' . $row, $bank);
            $worksheet->setCellValue('C' . $row, '0.00');
            $worksheet->setCellValue('D' . $row, '0');
            $row++;
            $sno++;
        }

        // MFSP Section
        $worksheet->setCellValue('A' . $row, $sno);
        $worksheet->setCellValue('B' . $row, 'MICROFINANCE INSTITUTIONS & COMMUNITY BANKS');
        $worksheet->setCellValue('C' . $row, '-');
        $worksheet->getStyle('A' . $row . ':D' . $row)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setRGB('FFC107');
        $worksheet->getStyle('B' . $row)->getFont()->setBold(true);
        $row++;

        $sno++;
        foreach ($mfsp as $mfspItem) {
            $worksheet->setCellValue('A' . $row, $sno);
            $worksheet->setCellValue('B' . $row, $mfspItem);
            $worksheet->setCellValue('C' . $row, '0.00');
            $worksheet->setCellValue('D' . $row, '0');
            $row++;
            $sno++;
        }

        // MNOs Section
        $worksheet->setCellValue('A' . $row, $sno);
        $worksheet->setCellValue('B' . $row, 'MOBILE NETWORK OPERATORS (MNOS)');
        $worksheet->setCellValue('C' . $row, '-');
        $worksheet->getStyle('A' . $row . ':D' . $row)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setRGB('28A745');
        $worksheet->getStyle('B' . $row)->getFont()->setBold(true);
        $row++;

        $sno++;
        foreach ($mnos as $mno) {
            $worksheet->setCellValue('A' . $row, $sno);
            $worksheet->setCellValue('B' . $row, $mno);
            $worksheet->setCellValue('C' . $row, '0.00');
            $worksheet->setCellValue('D' . $row, '0');
            $row++;
            $sno++;
        }

        // Add summary
        $row++;
        $worksheet->setCellValue('A' . $row, 'Generated on: ' . now()->format('F d, Y \a\t g:i A'));
        $worksheet->mergeCells('A' . $row . ':D' . $row);
        $worksheet->getStyle('A' . $row)->getFont()->setItalic(true);
        $worksheet->getStyle('A' . $row)->getAlignment()->setHorizontal('center');

        // Auto-size columns
        foreach (range('A', 'D') as $col) {
            $worksheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Create Excel file
        $writer = new Xlsx($spreadsheet);
        $filename = 'BOT_Agent_Banking_' . $asOfDate . '.xlsx';
        
        return response()->streamDownload(function() use ($writer) {
            $writer->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }
} 