<?php

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;
use App\Models\Budget;
use App\Models\Branch;
use App\Models\ChartAccount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

class BudgetController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $query = Budget::with(['user', 'branch', 'company', 'budgetLines']);
        
        // Filter by company scope
        if ($user->company_id) {
            $query->byCompany($user->company_id);
        }
        
        // Filter by branch scope
        if ($user->branch_id) {
            $query->byBranch($user->branch_id);
        }
        
        // Apply search filters
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('year', 'like', "%{$search}%");
            });
        }
        
        if ($request->filled('year')) {
            $query->byYear($request->year);
        }
        
        $budgets = $query->orderBy('created_at', 'desc')->paginate(15);
        
        
        return view('budgets.index', compact('budgets'));
    }
    
    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $this->authorize('create', Budget::class);
        
        $user = Auth::user();
        $accounts = ChartAccount::whereHas('accountClassGroup', function ($query) {
            $query->where('company_id', Auth::user()->company_id);
        })->get();
        
        return view('budgets.create', compact('accounts'));
    }
    
    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->authorize('create', Budget::class);
        
        $request->validate([
            'name' => 'required|string|max:255',
            'year' => 'required|integer|min:2020|max:2030',
            'description' => 'nullable|string|max:1000',
            'budget_lines' => 'required|array|min:1',
            'budget_lines.*.account_id' => 'required|exists:chart_accounts,id',
            'budget_lines.*.amount' => 'required|numeric|min:0',
            'budget_lines.*.category' => 'required|in:Revenue,Expense,Capital Expenditure',
        ]);
        
        try {
            DB::beginTransaction();
            
            $budget = Budget::create([
                'name' => $request->name,
                'year' => $request->year,
                'description' => $request->description,
                'user_id' => Auth::id(),
                'branch_id' => Auth::user()->branch_id,
                'company_id' => Auth::user()->company_id,
            ]);
            
            // Create budget lines
            foreach ($request->budget_lines as $line) {
                $budget->budgetLines()->create([
                    'account_id' => $line['account_id'],
                    'amount' => $line['amount'],
                    'category' => $line['category'],
                ]);
            }
            
            DB::commit();
            
            return redirect()->route('accounting.budgets.index')
                ->with('success', 'Budget created successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Failed to create budget: ' . $e->getMessage());
        }
    }
    
    /**
     * Display the specified resource.
     */
public function show(Budget $budget)
    {
        // Ensure user can only access budgets from their branch
        if ($budget->branch_id !== Auth::user()->branch_id) {
            abort(403, 'You can only access budgets from your own branch.');
        }
        
        $budget->load(['user', 'branch', 'company', 'budgetLines.account']);
        
        return view('budgets.show', compact('budget'));
    }
    
    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Budget $budget)
    {
        $this->authorize('update', $budget);
        
        // Ensure user can only edit budgets from their branch
        if ($budget->branch_id !== Auth::user()->branch_id) {
            abort(403, 'You can only edit budgets from your own branch.');
        }
        
        $accounts = ChartAccount::whereHas('accountClassGroup', function ($query) {
            $query->where('company_id', Auth::user()->company_id);
        })->get();
        
        $budget->load('budgetLines');
        
        return view('budgets.edit', compact('budget', 'accounts'));
    }
    
    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Budget $budget)
    {
        $this->authorize('update', $budget);
        
        // Ensure user can only update budgets from their branch
        if ($budget->branch_id !== Auth::user()->branch_id) {
            abort(403, 'You can only update budgets from your own branch.');
        }
        $request->validate([
            'name' => 'required|string|max:255',
            'year' => 'required|integer|min:2020|max:2030',
            'description' => 'nullable|string|max:1000',
            'budget_lines' => 'required|array|min:1',
            'budget_lines.*.account_id' => 'required|exists:chart_accounts,id',
            'budget_lines.*.amount' => 'required|numeric|min:0',
            'budget_lines.*.category' => 'required|in:Revenue,Expense,Capital Expenditure',
        ]);
        
        try {
            DB::beginTransaction();
            
            $budget->update([
                'name' => $request->name,
                'year' => $request->year,
                'description' => $request->description,
                'branch_id' => Auth::user()->branch_id,
            ]);
            
            // Delete existing budget lines
            $budget->budgetLines()->delete();
            
            // Create new budget lines
            foreach ($request->budget_lines as $line) {
                $budget->budgetLines()->create([
                    'account_id' => $line['account_id'],
                    'amount' => $line['amount'],
                    'category' => $line['category'],
                ]);
            }
            
            DB::commit();
            
            return redirect()->route('accounting.budgets.index')
                ->with('success', 'Budget updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Failed to update budget: ' . $e->getMessage());
        }
    }
    
    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Budget $budget)
    {
        $this->authorize('delete', $budget);
        
        // Ensure user can only delete budgets from their branch
        
        try {
            $budget->delete();
            return redirect()->route('accounting.budgets.index')
                ->with('success', 'Budget deleted successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to delete budget: ' . $e->getMessage());
        }
    }

    /**
     * Show the form for importing budgets.
     */
    public function import()
    {
        $user = Auth::user();
        $accounts = ChartAccount::whereHas('accountClassGroup', function ($query) {
            $query->where('company_id', Auth::user()->company_id);
        })->get();

        return view('budgets.import', compact('accounts'));
    }

    /**
     * Store imported budgets.
     */
    public function storeImport(Request $request)
    {
        $request->validate([
            'import_file' => 'required|file|mimes:xlsx,xls,csv|max:2048',
            'year' => 'required|integer|min:2020|max:2030',
            'budget_name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
        ]);

        try {
            DB::beginTransaction();

            // Add debugging
            \Log::info('Import started', [
                'file_name' => $request->file('import_file')->getClientOriginalName(),
                'file_size' => $request->file('import_file')->getSize(),
                'budget_name' => $request->budget_name,
                'year' => $request->year
            ]);

            $file = $request->file('import_file');
            
            \Log::info('File details', [
                'original_name' => $file->getClientOriginalName(),
                'mime_type' => $file->getMimeType(),
                'size' => $file->getSize(),
                'path' => $file->getPathname(),
                'exists' => file_exists($file->getPathname())
            ]);
            
            $spreadsheet = IOFactory::load($file->getPathname());
            $worksheet = $spreadsheet->getActiveSheet();
            $rows = $worksheet->toArray();

            \Log::info('File loaded', ['total_rows' => count($rows)]);

            // Remove header row
            array_shift($rows);

            // Validate and process rows
            $budgetLines = [];
            $errors = [];
            $rowNumber = 2; // Start from row 2 (after header)

            foreach ($rows as $row) {
                if (empty(array_filter($row))) {
                    continue; // Skip empty rows
                }

                // Skip sample data rows
                if (isset($row[0]) && strpos(strtolower(trim($row[0])), 'sample data') !== false) {
                    continue;
                }

                // Validate required columns (now 4 columns: Account Code, Account Name, Amount, Category)
                if (count($row) < 4) {
                    $errors[] = "Row {$rowNumber}: Insufficient columns. Expected: Account Code, Account Name, Amount, Category";
                    $rowNumber++;
                    continue;
                }

                $accountCode = trim($row[0]);
                $accountName = trim($row[1]);
                $amount = trim($row[2]);
                $category = trim($row[3]);
                
                // Remove BOM and other invisible characters from category
                $category = preg_replace('/[\x00-\x1F\x7F]/u', '', $category);
                $category = trim($category);
                
                // Additional cleaning for common issues
                $category = str_replace(['﻿', ' ', '　'], '', $category); // Remove BOM and other invisible spaces
                $category = trim($category);
                
                \Log::info('Processing row', [
                    'row_number' => $rowNumber,
                    'account_code' => $accountCode,
                    'account_name' => $accountName,
                    'amount' => $amount,
                    'category' => $category,
                    'category_length' => strlen($category),
                    'category_bytes' => bin2hex($category)
                ]);

                // Skip rows with empty amounts or categories
                if (empty($amount) || empty($category)) {
                    $rowNumber++;
                    continue;
                }

                // Validate account code exists
                $account = ChartAccount::where('account_code', $accountCode)
                    ->whereHas('accountClassGroup', function ($query) {
                        $query->where('company_id', Auth::user()->company_id);
                    })->first();

                if (!$account) {
                    $errors[] = "Row {$rowNumber}: Account code '{$accountCode}' not found";
                    $rowNumber++;
                    continue;
                }

                // Validate amount (same as store method)
                if (!is_numeric($amount) || $amount < 0) {
                    $errors[] = "Row {$rowNumber}: Invalid amount '{$amount}'. Amount must be numeric and greater than or equal to 0.";
                    $rowNumber++;
                    continue;
                }

                // Validate category (same as store method)
                $validCategories = ['Revenue', 'Expense', 'Capital Expenditure'];
                if (!in_array($category, $validCategories)) {
                    $errors[] = "Row {$rowNumber}: Invalid category '{$category}'. Must be one of: " . implode(', ', $validCategories);
                    $rowNumber++;
                    continue;
                }

                $budgetLines[] = [
                    'account_id' => $account->id,
                    'amount' => $amount,
                    'category' => $category,
                ];

                $rowNumber++;
            }

            \Log::info('Processing completed', [
                'errors_count' => count($errors),
                'budget_lines_count' => count($budgetLines),
                'errors' => $errors
            ]);

            if (!empty($errors)) {
                return back()->withInput()->with('errors', $errors);
            }

            if (empty($budgetLines)) {
                return back()->withInput()->with('error', 'No valid budget lines found in the file.');
            }

            // Create budget using same logic as store method
            $budget = Budget::create([
                'name' => $request->budget_name, // Using budget_name from form
                'year' => $request->year,
                'description' => $request->description,
                'user_id' => Auth::id(),
                'branch_id' => Auth::user()->branch_id,
                'company_id' => Auth::user()->company_id,
            ]);

            // Create budget lines using same logic as store method
            foreach ($budgetLines as $line) {
                $budget->budgetLines()->create([
                    'account_id' => $line['account_id'],
                    'amount' => $line['amount'],
                    'category' => $line['category'],
                ]);
            }

            DB::commit();

            \Log::info('Import successful', [
                'budget_id' => $budget->id,
                'budget_name' => $budget->name,
                'lines_count' => count($budgetLines)
            ]);

            return redirect()->route('accounting.budgets.index')
                ->with('success', "Budget '{$budget->name}' imported successfully with " . count($budgetLines) . " budget lines.");

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Import failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return back()->withInput()->with('error', 'Failed to import budget: ' . $e->getMessage());
        }
    }

    /**
     * Download budget import template.
     */
    public function downloadTemplate()
    {
        $user = Auth::user();
        
        // Get all chart accounts for the user's company
        $accounts = ChartAccount::whereHas('accountClassGroup', function ($query) use ($user) {
            $query->where('company_id', $user->company_id);
        })->with(['accountClassGroup', 'accountClassGroup.accountClass'])
          ->orderBy('account_code')
          ->get();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Set headers
        $sheet->setCellValue('A1', 'Account Code');
        $sheet->setCellValue('B1', 'Account Name');
        $sheet->setCellValue('C1', 'Amount');
        $sheet->setCellValue('D1', 'Category');

        // Add all accounts from chart_accounts table
        $row = 2;
        foreach ($accounts as $account) {
            $sheet->setCellValue('A' . $row, $account->account_code);
            $sheet->setCellValue('B' . $row, $account->account_name);
            $sheet->setCellValue('C' . $row, ''); // Empty amount for user to fill
            $sheet->setCellValue('D' . $row, ''); // Empty category for user to fill
            $row++;
        }

        // Add sample data at the end
        $sampleRow = $row + 1;
        $sheet->setCellValue('A' . $sampleRow, 'SAMPLE DATA - DELETE THESE ROWS');
        $sheet->setCellValue('B' . $sampleRow, '');
        $sheet->setCellValue('C' . $sampleRow, '1000000');
        $sheet->setCellValue('D' . $sampleRow, 'Revenue');

        $sampleRow++;
        $sheet->setCellValue('A' . $sampleRow, 'SAMPLE DATA - DELETE THESE ROWS');
        $sheet->setCellValue('B' . $sampleRow, '');
        $sheet->setCellValue('C' . $sampleRow, '500000');
        $sheet->setCellValue('D' . $sampleRow, 'Expense');

        $sampleRow++;
        $sheet->setCellValue('A' . $sampleRow, 'SAMPLE DATA - DELETE THESE ROWS');
        $sheet->setCellValue('B' . $sampleRow, '');
        $sheet->setCellValue('C' . $sampleRow, '250000');
        $sheet->setCellValue('D' . $sampleRow, 'Capital Expenditure');

        // Style headers
        $sheet->getStyle('A1:D1')->getFont()->setBold(true);
        $sheet->getStyle('A1:D1')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID);
        $sheet->getStyle('A1:D1')->getFill()->getStartColor()->setRGB('E9ECEF');

        // Style sample data rows
        $sheet->getStyle('A' . ($row + 1) . ':D' . $sampleRow)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID);
        $sheet->getStyle('A' . ($row + 1) . ':D' . $sampleRow)->getFill()->getStartColor()->setRGB('FFF3CD');
        $sheet->getStyle('A' . ($row + 1) . ':D' . $sampleRow)->getFont()->setItalic(true);

        // Auto-size columns
        foreach (range('A', 'D') as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }

        // Create the file
        $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xlsx');
        $filename = 'budget_import_template.xlsx';
        
        $tempFile = tempnam(sys_get_temp_dir(), 'budget_template');
        $writer->save($tempFile);

        return response()->download($tempFile, $filename)->deleteFileAfterSend();
    }

    /**
     * Export a specific budget to Excel.
     */
    public function exportExcel(Budget $budget)
    {
        $this->authorize('view budget details', $budget);
        
        // Ensure user can only export budgets from their branch
        if ($budget->branch_id !== Auth::user()->branch_id) {
            abort(403, 'You can only export budgets from your own branch.');
        }

        $budget->load(['user', 'branch', 'company', 'budgetLines.account']);

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Set title
        $sheet->setCellValue('A1', 'BUDGET EXPORT');
        $sheet->mergeCells('A1:D1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal('center');

        // Budget Information
        $sheet->setCellValue('A3', 'Budget Information');
        $sheet->getStyle('A3')->getFont()->setBold(true)->setSize(14);

        $sheet->setCellValue('A4', 'Budget Name:');
        $sheet->setCellValue('B4', $budget->name);
        $sheet->setCellValue('A5', 'Year:');
        $sheet->setCellValue('B5', $budget->year);
        $sheet->setCellValue('A6', 'Description:');
        $sheet->setCellValue('B6', $budget->description ?? 'N/A');
        $sheet->setCellValue('A7', 'Created By:');
        $sheet->setCellValue('B7', $budget->user->name ?? 'N/A');
        $sheet->setCellValue('A8', 'Branch:');
        $sheet->setCellValue('B8', $budget->branch->name ?? 'N/A');
        $sheet->setCellValue('A9', 'Created Date:');
        $sheet->setCellValue('B9', $budget->created_at->format('d M Y, H:i'));

        // Budget Lines
        $sheet->setCellValue('A11', 'Budget Lines');
        $sheet->getStyle('A11')->getFont()->setBold(true)->setSize(14);

        // Headers
        $sheet->setCellValue('A12', 'Account Code');
        $sheet->setCellValue('B12', 'Account Name');
        $sheet->setCellValue('C12', 'Amount');
        $sheet->setCellValue('D12', 'Category');

        // Style headers
        $sheet->getStyle('A12:D12')->getFont()->setBold(true);
        $sheet->getStyle('A12:D12')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID);
        $sheet->getStyle('A12:D12')->getFill()->getStartColor()->setRGB('E9ECEF');

        // Add budget lines
        $row = 13;
        $totalAmount = 0;
        foreach ($budget->budgetLines as $line) {
            $sheet->setCellValue('A' . $row, $line->account->account_code);
            $sheet->setCellValue('B' . $row, $line->account->account_name);
            $sheet->setCellValue('C' . $row, $line->amount);
            $sheet->setCellValue('D' . $row, $line->category);
            $totalAmount += $line->amount;
            $row++;
        }

        // Add total row
        $sheet->setCellValue('A' . $row, 'TOTAL');
        $sheet->setCellValue('B' . $row, '');
        $sheet->setCellValue('C' . $row, $totalAmount);
        $sheet->setCellValue('D' . $row, '');

        // Style total row
        $sheet->getStyle('A' . $row . ':D' . $row)->getFont()->setBold(true);
        $sheet->getStyle('A' . $row . ':D' . $row)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID);
        $sheet->getStyle('A' . $row . ':D' . $row)->getFill()->getStartColor()->setRGB('D1ECF1');

        // Auto-size columns
        foreach (range('A', 'D') as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }

        // Create the file
        $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xlsx');
        $filename = 'budget_' . $budget->name . '_' . $budget->year . '.xlsx';
        
        $tempFile = tempnam(sys_get_temp_dir(), 'budget_export');
        $writer->save($tempFile);

        return response()->download($tempFile, $filename)->deleteFileAfterSend();
    }

    /**
     * Export a specific budget to PDF.
     */
    public function exportPdf(Budget $budget)
    {
        $this->authorize('view budget details', $budget);
        
        // Ensure user can only export budgets from their branch
        if ($budget->branch_id !== Auth::user()->branch_id) {
            abort(403, 'You can only export budgets from your own branch.');
        }

        $budget->load(['user', 'branch', 'company', 'budgetLines.account']);

        $pdf = \PDF::loadView('budgets.export-pdf', compact('budget'));
        
        $filename = 'budget_' . $budget->name . '_' . $budget->year . '.pdf';
        
        return $pdf->download($filename);
    }
} 