<?php

namespace App\Http\Controllers;

use App\Services\LoanCalculatorService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Validator;

class LoanCalculatorController extends Controller
{
    protected $calculatorService;
    
    public function __construct(LoanCalculatorService $calculatorService)
    {
        $this->calculatorService = $calculatorService;
    }
    
    /**
     * Show the loan calculator interface
     */
    public function index(Request $request): View
    {
        $products = $this->calculatorService->getAvailableProducts();
        
        // Check if this is a modal request
        if ($request->has('modal') && $request->modal) {
            return view('loan-calculator.modal', compact('products'));
        }
        
        return view('loan-calculator.index', compact('products'));
    }
    
    /**
     * Calculate loan details
     */
    public function calculate(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'product_id' => 'required|integer|exists:loan_products,id',
            'amount' => 'required|numeric|min:1',
            'period' => 'required|integer|min:1',
            'interest_rate' => 'required|numeric|min:0',
            'start_date' => 'required|date|after_or_equal:today'
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }
        
        $result = $this->calculatorService->calculateLoan($request->all());
        
        return response()->json($result);
    }
    
    /**
     * Compare multiple loan scenarios
     */
    public function compare(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'scenarios' => 'required|array|min:2|max:5',
            'scenarios.*.product_id' => 'required|integer|exists:loan_products,id',
            'scenarios.*.amount' => 'required|numeric|min:1',
            'scenarios.*.period' => 'required|integer|min:1',
            'scenarios.*.interest_rate' => 'required|numeric|min:0',
            'scenarios.*.start_date' => 'required|date|after_or_equal:today',
            'scenarios.*.name' => 'nullable|string|max:255'
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }
        
        $result = $this->calculatorService->compareLoans($request->scenarios);
        
        return response()->json($result);
    }
    
    /**
     * Get available loan products
     */
    public function products(): JsonResponse
    {
        $products = $this->calculatorService->getAvailableProducts();
        
        return response()->json([
            'success' => true,
            'products' => $products
        ]);
    }
    
    /**
     * Get product details for calculator
     */
    public function productDetails(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'product_id' => 'required|integer|exists:loan_products,id'
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }
        
        $product = \App\Models\LoanProduct::find($request->product_id);
            
        if (!$product || !$product->is_active) {
            return response()->json([
                'success' => false,
                'error' => 'Product not found or inactive'
            ], 404);
        }
        
        $details = [
            'id' => $product->id,
            'name' => $product->name,
            'product_type' => $product->product_type,
            'min_interest_rate' => $product->minimum_interest_rate,
            'max_interest_rate' => $product->maximum_interest_rate,
            'min_principal' => $product->minimum_principal,
            'max_principal' => $product->maximum_principal,
            'min_period' => $product->minimum_period,
            'max_period' => $product->maximum_period,
            'interest_method' => $product->interest_method,
            'interest_cycle' => $product->interest_cycle,
            'grace_period' => $product->grace_period ?? 0,
            'has_cash_collateral' => $product->has_cash_collateral,
            'cash_collateral_value' => $product->cash_collateral_value,
            'fees' => $product->getFeesAttribute()->map(function ($fee) {
                return [
                    'id' => $fee->id,
                    'name' => $fee->name,
                    'type' => $fee->fee_type,
                    'amount' => $fee->amount,
                    'criteria' => $fee->deduction_criteria,
                    'include_in_schedule' => $fee->include_in_schedule
                ];
            }),
            'penalties' => $product->penalties()->map(function ($penalty) {
                return [
                    'id' => $penalty->id,
                    'name' => $penalty->name,
                    'type' => $penalty->penalty_type,
                    'amount' => $penalty->amount,
                    'deduction_type' => $penalty->deduction_type,
                    'charge_frequency' => $penalty->charge_frequency
                ];
            })
        ];
        
        return response()->json([
            'success' => true,
            'product' => $details
        ]);
    }
    
    /**
     * Export calculation as PDF
     */
    public function exportPdf(Request $request): \Illuminate\Http\Response
    {
        $validator = Validator::make($request->all(), [
            'product_id' => 'required|integer|exists:loan_products,id',
            'amount' => 'required|numeric|min:1',
            'period' => 'required|integer|min:1',
            'interest_rate' => 'required|numeric|min:0',
            'start_date' => 'required|date|after_or_equal:today'
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }
        
        $result = $this->calculatorService->calculateLoan($request->all());
        
        if (!$result['success']) {
            return response()->json($result, 400);
        }
        
        // Generate PDF using DomPDF
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('loan-calculator.pdf', [
            'calculation' => $result,
            'generated_at' => now()
        ]);
        
        $filename = 'loan_calculation_' . now()->format('Y_m_d_H_i_s') . '.pdf';
        
        return $pdf->download($filename);
    }
    
    /**
     * Export calculation as Excel
     */
    public function exportExcel(Request $request): \Symfony\Component\HttpFoundation\BinaryFileResponse
    {
        $validator = Validator::make($request->all(), [
            'product_id' => 'required|integer|exists:loan_products,id',
            'amount' => 'required|numeric|min:1',
            'period' => 'required|integer|min:1',
            'interest_rate' => 'required|numeric|min:0',
            'start_date' => 'required|date|after_or_equal:today'
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }
        
        $result = $this->calculatorService->calculateLoan($request->all());
        
        if (!$result['success']) {
            return response()->json($result, 400);
        }
        
        // Generate Excel using Maatwebsite\Excel
        return \Maatwebsite\Excel\Facades\Excel::download(
            new \App\Exports\LoanCalculationExport($result),
            'loan_calculation_' . now()->format('Y_m_d_H_i_s') . '.xlsx'
        );
    }
    
    /**
     * Get calculation history for user
     */
    public function history(): JsonResponse
    {
        // This would typically store calculations in a database
        // For now, return empty array
        return response()->json([
            'success' => true,
            'calculations' => []
        ]);
    }
    
    /**
     * Save calculation for later reference
     */
    public function save(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'product_id' => 'required|integer|exists:loan_products,id',
            'amount' => 'required|numeric|min:1',
            'period' => 'required|integer|min:1',
            'interest_rate' => 'required|numeric|min:0',
            'start_date' => 'required|date|after_or_equal:today'
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }
        
        // Calculate the loan
        $result = $this->calculatorService->calculateLoan($request->all());
        
        if (!$result['success']) {
            return response()->json($result, 400);
        }
        
        // Here you would save to database
        // For now, just return success
        return response()->json([
            'success' => true,
            'message' => 'Calculation saved successfully',
            'calculation_id' => 'temp_' . time()
        ]);
    }
}
