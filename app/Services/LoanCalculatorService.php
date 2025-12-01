<?php

namespace App\Services;

use App\Models\LoanProduct;
use App\Models\Fee;
use App\Models\Penalty;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class LoanCalculatorService
{
    /**
     * Calculate loan details based on parameters
     */
    public function calculateLoan(array $params): array
    {
        try {
            // Validate inputs
            $this->validateInputs($params);
            
            // Get loan product
            $product = $this->getLoanProduct($params['product_id']);
            
            // Calculate interest
            $interestCalculation = $this->calculateInterest($params, $product);
            
            // Calculate fees
            $fees = $this->calculateFees($params, $product);
            
            // Generate repayment schedule
            $schedule = $this->generateSchedule($params, $product, $interestCalculation, $fees);
            
            // Calculate totals
            $totals = $this->calculateTotals($params, $interestCalculation, $fees, $schedule);
            
            // Calculate penalties (if applicable)
            $penalties = $this->calculatePenalties($params, $product);
            
            return [
                'success' => true,
                'product' => $this->formatProduct($product),
                'interest_calculation' => $interestCalculation,
                'fees' => $fees,
                'penalties' => $penalties,
                'schedule' => $schedule,
                'totals' => $totals,
                'summary' => $this->generateSummary($params, $totals)
            ];
            
        } catch (\Exception $e) {
            Log::error('Loan Calculator Error: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Compare multiple loan scenarios
     */
    public function compareLoans(array $scenarios): array
    {
        $results = [];
        
        foreach ($scenarios as $index => $scenario) {
            $result = $this->calculateLoan($scenario);
            $results[] = [
                'scenario' => $index + 1,
                'name' => $scenario['name'] ?? "Scenario " . ($index + 1),
                'result' => $result
            ];
        }
        
        return [
            'success' => true,
            'comparisons' => $results,
            'summary' => $this->generateComparisonSummary($results)
        ];
    }
    
    /**
     * Get available loan products for calculator
     */
    public function getAvailableProducts(): array
    {
        $products = LoanProduct::where('is_active', true)->get();
            
        return $products->map(function ($product) {
            return [
                'id' => $product->id,
                'name' => $product->name,
                'product_type' => $product->product_type,
                'min_interest_rate' => $product->minimum_interest_rate,
                'max_interest_rate' => $product->maximum_interest_rate,
                'default_interest_rate' => $product->minimum_interest_rate,
                'min_principal' => $product->minimum_principal,
                'max_principal' => $product->maximum_principal,
                'min_period' => $product->minimum_period,
                'max_period' => $product->maximum_period,
                'interest_method' => $product->interest_method,
                'interest_cycle' => $product->interest_cycle,
                'grace_period' => $product->grace_period ?? 0,
                'has_cash_collateral' => $product->has_cash_collateral,
                'cash_collateral_value' => $product->cash_collateral_value,
                'fees_count' => $product->getFeesAttribute()->count(),
                'penalties_count' => $product->penalties()->count()
            ];
        })->toArray();
    }
    
    /**
     * Validate input parameters
     */
    private function validateInputs(array $params): void
    {
        $required = ['product_id', 'amount', 'period', 'interest_rate', 'start_date'];
        
        foreach ($required as $field) {
            if (!isset($params[$field]) || empty($params[$field])) {
                throw new \InvalidArgumentException("Missing required parameter: {$field}");
            }
        }
        
        if ($params['amount'] <= 0) {
            throw new \InvalidArgumentException("Loan amount must be greater than 0");
        }
        
        if ($params['period'] <= 0) {
            throw new \InvalidArgumentException("Loan period must be greater than 0");
        }
        
        if ($params['interest_rate'] < 0) {
            throw new \InvalidArgumentException("Interest rate cannot be negative");
        }
    }
    
    /**
     * Get loan product with validation
     */
    private function getLoanProduct(int $productId): LoanProduct
    {
        $product = LoanProduct::find($productId);
        
        if (!$product) {
            throw new \InvalidArgumentException("Loan product not found");
        }
        
        if (!$product->is_active) {
            throw new \InvalidArgumentException("Loan product is not active");
        }
        
        return $product;
    }
    
    /**
     * Calculate interest based on method
     */
    private function calculateInterest(array $params, LoanProduct $product): array
    {
        $principal = $params['amount'];
        $rate = $params['interest_rate'];
        $period = $params['period'];
        $method = $product->interest_method;
        
        // Validate against product limits
        $this->validateProductLimits($params, $product);
        
        switch ($method) {
            case 'flat_rate':
                return $this->calculateFlatRate($principal, $rate, $period);
                
            case 'reducing_balance_with_equal_installment':
                return $this->calculateReducingBalanceEqualInstallment($principal, $rate, $period);
                
            case 'reducing_balance_with_equal_principal':
                return $this->calculateReducingBalanceEqualPrincipal($principal, $rate, $period);
                
            default:
                throw new \InvalidArgumentException("Unsupported interest method: {$method}");
        }
    }
    
    /**
     * Calculate flat rate interest
     */
    private function calculateFlatRate(float $principal, float $rate, int $period): array
    {
        $ratePerPeriod = $rate / 100;
        $totalInterest = $principal * $ratePerPeriod * $period;
        $monthlyInterest = $totalInterest / $period;
        $monthlyPrincipal = $principal / $period;
        
        return [
            'method' => 'flat_rate',
            'total_interest' => round($totalInterest, 2),
            'monthly_interest' => round($monthlyInterest, 2),
            'monthly_principal' => round($monthlyPrincipal, 2),
            'monthly_payment' => round($monthlyPrincipal + $monthlyInterest, 2),
            'rate_per_period' => $ratePerPeriod
        ];
    }
    
    /**
     * Calculate reducing balance with equal installments
     */
    private function calculateReducingBalanceEqualInstallment(float $principal, float $rate, int $period): array
    {
        $ratePerPeriod = $rate / 100;
        
        // PMT formula: PMT = P * [r(1+r)^n] / [(1+r)^n - 1]
        $monthlyPayment = $principal * ($ratePerPeriod * pow(1 + $ratePerPeriod, $period)) / (pow(1 + $ratePerPeriod, $period) - 1);
        $totalPayment = $monthlyPayment * $period;
        $totalInterest = $totalPayment - $principal;
        
        // Generate schedule for equal installments
        $schedule = [];
        $remainingBalance = $principal;
        $startDate = Carbon::now(); // This will be overridden by the actual start date from params
        
        for ($i = 1; $i <= $period; $i++) {
            $interest = $remainingBalance * $ratePerPeriod;
            $principalPayment = $monthlyPayment - $interest;
            
            // Round components
            $interest = round($interest, 2);
            $principalPayment = round($principalPayment, 2);

            // On final installment, adjust principal to clear remaining balance to zero
            if ($i === $period) {
                $principalPayment = round($remainingBalance, 2);
                // Recompute interest to keep total payment aligned
                $interest = round($monthlyPayment - $principalPayment, 2);
            }

            $newRemaining = round($remainingBalance - $principalPayment, 2);
            if (abs($newRemaining) < 0.05) { // clamp tiny rounding drift
                $newRemaining = 0.0;
            }

            $schedule[] = [
                'installment_number' => $i,
                'due_date' => $startDate->copy()->addMonths($i)->format('Y-m-d'),
                'principal' => $principalPayment,
                'interest' => $interest,
                'fee_amount' => 0, // Will be calculated separately
                'total_amount' => round($principalPayment + $interest, 2),
                'remaining_balance' => max(0, $newRemaining)
            ];
            
            $remainingBalance = $newRemaining;
        }
        
        return [
            'method' => 'reducing_balance_with_equal_installment',
            'total_interest' => round($totalInterest, 2),
            'monthly_payment' => round($monthlyPayment, 2),
            'total_payment' => round($totalPayment, 2),
            'rate_per_period' => $ratePerPeriod,
            'schedule' => $schedule
        ];
    }
    
    /**
     * Calculate reducing balance with equal principal
     */
    private function calculateReducingBalanceEqualPrincipal(float $principal, float $rate, int $period): array
    {
        $ratePerPeriod = $rate / 100;
        $monthlyPrincipal = $principal / $period;
        $totalInterest = 0;
        $schedule = [];
        $remainingBalance = $principal;
        
        $startDate = Carbon::now(); // This will be overridden by the actual start date from params
        
        for ($i = 1; $i <= $period; $i++) {
            $interest = $remainingBalance * $ratePerPeriod;
            $principalForRow = $monthlyPrincipal;

            // On final installment, adjust principal to remaining
            if ($i === $period) {
                $principalForRow = round($remainingBalance, 2);
            }

            $interest = round($interest, 2);
            $principalForRow = round($principalForRow, 2);
            $totalPayment = $principalForRow + $interest;

            $newRemaining = round($remainingBalance - $principalForRow, 2);
            if (abs($newRemaining) < 0.05) {
                $newRemaining = 0.0;
            }

            $schedule[] = [
                'installment_number' => $i,
                'due_date' => $startDate->copy()->addMonths($i)->format('Y-m-d'),
                'principal' => $principalForRow,
                'interest' => $interest,
                'fee_amount' => 0, // Will be calculated separately
                'total_amount' => round($totalPayment, 2),
                'remaining_balance' => max(0, $newRemaining)
            ];
            
            $remainingBalance = $newRemaining;
            $totalInterest += $interest;
        }
        
        return [
            'method' => 'reducing_balance_with_equal_principal',
            'total_interest' => round($totalInterest, 2),
            'monthly_principal' => round($monthlyPrincipal, 2),
            'schedule' => $schedule,
            'rate_per_period' => $ratePerPeriod
        ];
    }
    
    /**
     * Calculate fees
     */
    private function calculateFees(array $params, LoanProduct $product): array
    {
        $fees = [];
        $productFees = $product->getFeesAttribute();
        $principal = $params['amount'];
        $period = $params['period'];
        
        foreach ($productFees as $fee) {
            if ($fee->status !== 'active') continue;
            
            $feeAmount = $this->calculateFeeAmount($fee, $principal);
            $feeApplication = $this->determineFeeApplication($fee, $period, $feeAmount);
            
            $fees[] = [
                'fee_id' => $fee->id,
                'name' => $fee->name,
                'type' => $fee->fee_type,
                'amount' => $feeAmount,
                'application' => $feeApplication,
                'criteria' => $fee->deduction_criteria,
                'include_in_schedule' => $fee->include_in_schedule
            ];
        }
        
        return $fees;
    }
    
    /**
     * Calculate fee amount
     */
    private function calculateFeeAmount(Fee $fee, float $principal): float
    {
        if ($fee->fee_type === 'percentage') {
            return round(($principal * $fee->amount) / 100, 2);
        }
        return round($fee->amount, 2);
    }
    
    /**
     * Determine how fee is applied
     */
    private function determineFeeApplication(Fee $fee, int $period, float $computedFeeAmount): array
    {
        $criteria = $fee->deduction_criteria;
        // Use computed monetary amount regardless of type (percentage already applied)
        $totalFee = $computedFeeAmount;
        
        switch ($criteria) {
            case 'distribute_fee_evenly_to_all_repayments':
                return [
                    'type' => 'distributed',
                    'per_installment' => round($totalFee / $period, 2),
                    'total' => $totalFee
                ];
                
            case 'charge_same_fee_to_all_repayments':
                return [
                    'type' => 'per_installment',
                    'per_installment' => $totalFee,
                    'total' => $totalFee * $period
                ];
                
            case 'charge_fee_on_first_repayment':
                return [
                    'type' => 'first_only',
                    'per_installment' => $totalFee,
                    'total' => $totalFee
                ];
                
            case 'charge_fee_on_last_repayment':
                return [
                    'type' => 'last_only',
                    'per_installment' => $totalFee,
                    'total' => $totalFee
                ];
                
            case 'charge_fee_on_release_date':
                return [
                    'type' => 'release_date',
                    'per_installment' => 0,
                    'total' => $totalFee
                ];
                
            default:
                return [
                    'type' => 'not_included',
                    'per_installment' => 0,
                    'total' => 0
                ];
        }
    }
    
    /**
     * Calculate penalties
     */
    private function calculatePenalties(array $params, LoanProduct $product): array
    {
        $penalties = [];
        $productPenalties = $product->penalties();
        
        foreach ($productPenalties as $penalty) {
            if ($penalty->status !== 'active') continue;
            
            $penalties[] = [
                'penalty_id' => $penalty->id,
                'name' => $penalty->name,
                'type' => $penalty->penalty_type,
                'amount' => $penalty->amount,
                'deduction_type' => $penalty->deduction_type,
                'charge_frequency' => $penalty->charge_frequency,
                'description' => $penalty->description
            ];
        }
        
        return $penalties;
    }
    
    /**
     * Generate repayment schedule
     */
    private function generateSchedule(array $params, LoanProduct $product, array $interestCalculation, array $fees): array
    {
        $schedule = [];
        $startDate = Carbon::parse($params['start_date']);
        $period = $params['period'];
        $gracePeriod = $product->grace_period ?? 0;
        $method = $product->interest_method;
        $selectedCycle = $params['interest_cycle'] ?? $product->interest_cycle;
        
        // Handle different interest methods
        if ($method === 'reducing_balance_with_equal_principal' || $method === 'reducing_balance_with_equal_installment') {
            $baseSchedule = $interestCalculation['schedule'] ?? [];
            
            // Add fees to the schedule
            $scheduleWithFees = [];
            foreach ($baseSchedule as $index => $installment) {
                $installmentFees = $this->calculateInstallmentFees($index, $period, $fees, $params['amount']);
                
                $scheduleWithFees[] = [
                    'installment_number' => $installment['installment_number'],
                    'due_date' => $installment['due_date'],
                    'principal' => $installment['principal'],
                    'interest' => $installment['interest'],
                    'fee_amount' => round($installmentFees, 2),
                    'total_amount' => round($installment['total_amount'] + $installmentFees, 2),
                    'remaining_balance' => $installment['remaining_balance']
                ];
            }
            
            return $scheduleWithFees;
        }
        
        $remainingBalance = $params['amount'];
        
        for ($i = 0; $i < $period; $i++) {
            $dueDate = $this->calculateDueDate($startDate, $i, $selectedCycle);
            $endDate = $dueDate->copy()->addDays(5);
            $endGraceDate = $dueDate->copy()->addDays($gracePeriod);
            
            // Calculate installment amounts
            $principal = $interestCalculation['monthly_principal'] ?? ($params['amount'] / $period);
            $interest = $interestCalculation['monthly_interest'] ?? ($interestCalculation['monthly_payment'] - $principal);

            // Round
            $principal = round($principal, 2);
            $interest = round($interest, 2);

            // On final installment, adjust principal to clear remaining
            if ($i === $period - 1) {
                $principal = round($remainingBalance, 2);
            }
            
            // Calculate fees for this installment
            $installmentFees = $this->calculateInstallmentFees($i, $period, $fees, $params['amount']);
            
            // Update remaining balance
            $newRemaining = round($remainingBalance - $principal, 2);
            if (abs($newRemaining) < 0.05) {
                $newRemaining = 0.0;
            }
            
            $schedule[] = [
                'installment_number' => $i + 1,
                'due_date' => $dueDate->format('Y-m-d'),
                'end_date' => $endDate->format('Y-m-d'),
                'end_grace_date' => $endGraceDate->format('Y-m-d'),
                'principal' => $principal,
                'interest' => $interest,
                'fee_amount' => round($installmentFees, 2),
                'penalty_amount' => 0, // Calculated when overdue
                'total_amount' => round($principal + $interest + $installmentFees, 2),
                'remaining_balance' => max(0, $newRemaining)
            ];

            $remainingBalance = $newRemaining;
        }
        
        return $schedule;
    }
    
    /**
     * Calculate fees for specific installment
     */
    private function calculateInstallmentFees(int $installmentIndex, int $totalPeriods, array $fees, float $principal): float
    {
        $totalFees = 0;
        
        foreach ($fees as $fee) {
            if (!$fee['include_in_schedule']) continue;
            
            $application = $fee['application'];
            $feeAmount = 0;
            
            switch ($application['type']) {
                case 'distributed':
                    $feeAmount = $application['per_installment'];
                    break;
                    
                case 'per_installment':
                    $feeAmount = $application['per_installment'];
                    break;
                    
                case 'first_only':
                    $feeAmount = $installmentIndex === 0 ? $application['per_installment'] : 0;
                    break;
                    
                case 'last_only':
                    $feeAmount = $installmentIndex === ($totalPeriods - 1) ? $application['per_installment'] : 0;
                    break;
                    
                case 'release_date':
                case 'not_included':
                default:
                    $feeAmount = 0;
                    break;
            }
            
            $totalFees += $feeAmount;
        }
        
        return $totalFees;
    }
    
    /**
     * Calculate due date for installment
     */
    private function calculateDueDate(Carbon $startDate, int $installmentIndex, string $cycle): Carbon
    {
        switch ($cycle) {
            case 'daily':
                return $startDate->copy()->addDays($installmentIndex);
            case 'weekly':
                return $startDate->copy()->addWeeks($installmentIndex);
            case 'monthly':
                return $startDate->copy()->addMonths($installmentIndex);
            case 'quarterly':
                return $startDate->copy()->addMonths($installmentIndex * 3);
            case 'semi_annually':
                return $startDate->copy()->addMonths($installmentIndex * 6);
            case 'annually':
                return $startDate->copy()->addYears($installmentIndex);
            default:
                return $startDate->copy()->addMonths($installmentIndex);
        }
    }
    
    /**
     * Calculate totals
     */
    private function calculateTotals(array $params, array $interestCalculation, array $fees, array $schedule): array
    {
        $principal = $params['amount'];
        $totalInterest = $interestCalculation['total_interest'];
        
        // Calculate total fees
        $totalFees = 0;
        foreach ($fees as $fee) {
            $totalFees += $fee['application']['total'];
        }
        
        // Calculate from schedule
        $totalScheduleAmount = array_sum(array_column($schedule, 'total_amount'));
        $totalScheduleFees = array_sum(array_column($schedule, 'fee_amount'));
        
        return [
            'principal' => round($principal, 2),
            'total_interest' => round($totalInterest, 2),
            'total_fees' => round($totalFees, 2),
            'total_amount' => round($principal + $totalInterest + $totalFees, 2),
            'monthly_payment' => round($totalScheduleAmount / count($schedule), 2),
            'schedule_total' => round($totalScheduleAmount, 2),
            'schedule_fees' => round($totalScheduleFees, 2)
        ];
    }
    
    /**
     * Generate summary
     */
    private function generateSummary(array $params, array $totals): array
    {
        return [
            'loan_amount' => $totals['principal'],
            'interest_rate' => $params['interest_rate'],
            'period' => $params['period'],
            'monthly_payment' => $totals['monthly_payment'],
            'total_interest' => $totals['total_interest'],
            'total_fees' => $totals['total_fees'],
            'total_amount' => $totals['total_amount'],
            'interest_percentage' => round(($totals['total_interest'] / $totals['principal']) * 100, 2)
        ];
    }
    
    /**
     * Generate comparison summary
     */
    private function generateComparisonSummary(array $results): array
    {
        $summaries = [];
        
        foreach ($results as $result) {
            if ($result['result']['success']) {
                $summaries[] = [
                    'name' => $result['name'],
                    'monthly_payment' => $result['result']['totals']['monthly_payment'],
                    'total_amount' => $result['result']['totals']['total_amount'],
                    'total_interest' => $result['result']['totals']['total_interest']
                ];
            }
        }
        
        return $summaries;
    }
    
    /**
     * Format product for response
     */
    private function formatProduct(LoanProduct $product): array
    {
        return [
            'id' => $product->id,
            'name' => $product->name,
            'product_type' => $product->product_type,
            'interest_method' => $product->interest_method,
            'interest_cycle' => $product->interest_cycle,
            'grace_period' => $product->grace_period ?? 0
        ];
    }
    
    /**
     * Validate against product limits
     */
    private function validateProductLimits(array $params, LoanProduct $product): void
    {
        if ($params['amount'] < $product->minimum_principal) {
            throw new \InvalidArgumentException("Amount is below minimum: {$product->minimum_principal}");
        }
        
        if ($params['amount'] > $product->maximum_principal) {
            throw new \InvalidArgumentException("Amount exceeds maximum: {$product->maximum_principal}");
        }
        
        if ($params['period'] < $product->minimum_period) {
            throw new \InvalidArgumentException("Period is below minimum: {$product->minimum_period}");
        }
        
        if ($params['period'] > $product->maximum_period) {
            throw new \InvalidArgumentException("Period exceeds maximum: {$product->maximum_period}");
        }
        
        if ($params['interest_rate'] < $product->minimum_interest_rate) {
            throw new \InvalidArgumentException("Interest rate is below minimum: {$product->minimum_interest_rate}%");
        }
        
        if ($params['interest_rate'] > $product->maximum_interest_rate) {
            throw new \InvalidArgumentException("Interest rate exceeds maximum: {$product->maximum_interest_rate}%");
        }
    }
}
