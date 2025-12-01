<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Services\LoanCalculatorService;
use App\Models\LoanProduct;
use App\Models\Company;
use App\Models\Branch;
use App\Models\Fee;
use App\Models\Penalty;
use Illuminate\Foundation\Testing\RefreshDatabase;

class LoanCalculatorServiceTest extends TestCase
{
    use RefreshDatabase;

    protected $calculatorService;
    protected $company;
    protected $branch;
    protected $loanProduct;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->calculatorService = new LoanCalculatorService();
        
        $this->company = Company::factory()->create();
        $this->branch = Branch::factory()->create(['company_id' => $this->company->id]);
        
        $this->loanProduct = LoanProduct::factory()->create([
            'company_id' => $this->company->id,
            'minimum_interest_rate' => 10.0,
            'maximum_interest_rate' => 25.0,
            'minimum_principal' => 10000,
            'maximum_principal' => 1000000,
            'minimum_period' => 1,
            'maximum_period' => 60,
            'interest_method' => 'flat_rate',
            'interest_cycle' => 'monthly',
            'is_active' => true
        ]);
    }

    /** @test */
    public function it_calculates_flat_rate_interest_correctly()
    {
        $params = [
            'product_id' => $this->loanProduct->id,
            'amount' => 100000,
            'period' => 12,
            'interest_rate' => 15.0,
            'start_date' => now()->format('Y-m-d')
        ];

        $result = $this->calculatorService->calculateLoan($params);

        $this->assertTrue($result['success']);
        
        // Flat rate calculation: Principal * Rate * Period
        $expectedInterest = 100000 * 0.15 * 12; // 18000
        $this->assertEquals($expectedInterest, $result['totals']['total_interest']);
        
        // Monthly payment: (Principal + Interest) / Period
        $expectedMonthlyPayment = (100000 + $expectedInterest) / 12;
        $this->assertEquals(round($expectedMonthlyPayment, 2), $result['totals']['monthly_payment']);
    }

    /** @test */
    public function it_calculates_reducing_balance_equal_installment_correctly()
    {
        $this->loanProduct->update(['interest_method' => 'reducing_balance_with_equal_installment']);
        
        $params = [
            'product_id' => $this->loanProduct->id,
            'amount' => 100000,
            'period' => 12,
            'interest_rate' => 15.0,
            'start_date' => now()->format('Y-m-d')
        ];

        $result = $this->calculatorService->calculateLoan($params);

        $this->assertTrue($result['success']);
        
        // For reducing balance, total interest should be less than flat rate
        $flatRateInterest = 100000 * 0.15 * 12; // 18000
        $this->assertLessThan($flatRateInterest, $result['totals']['total_interest']);
        
        // Monthly payment should be consistent
        $this->assertArrayHasKey('monthly_payment', $result['interest_calculation']);
    }

    /** @test */
    public function it_calculates_reducing_balance_equal_principal_correctly()
    {
        $this->loanProduct->update(['interest_method' => 'reducing_balance_with_equal_principal']);
        
        $params = [
            'product_id' => $this->loanProduct->id,
            'amount' => 100000,
            'period' => 12,
            'interest_rate' => 15.0,
            'start_date' => now()->format('Y-m-d')
        ];

        $result = $this->calculatorService->calculateLoan($params);

        $this->assertTrue($result['success']);
        
        // Should have schedule with decreasing interest
        $schedule = $result['interest_calculation']['schedule'];
        $this->assertCount(12, $schedule);
        
        // First installment should have highest interest
        $firstInterest = $schedule[0]['interest'];
        $lastInterest = $schedule[11]['interest'];
        $this->assertGreaterThan($lastInterest, $firstInterest);
    }

    /** @test */
    public function it_validates_loan_amount_limits()
    {
        $params = [
            'product_id' => $this->loanProduct->id,
            'amount' => 5000, // Below minimum
            'period' => 12,
            'interest_rate' => 15.0,
            'start_date' => now()->format('Y-m-d')
        ];

        $result = $this->calculatorService->calculateLoan($params);

        $this->assertFalse($result['success']);
        $this->assertStringContainsString('Amount is below minimum', $result['error']);
    }

    /** @test */
    public function it_validates_interest_rate_limits()
    {
        $params = [
            'product_id' => $this->loanProduct->id,
            'amount' => 100000,
            'period' => 12,
            'interest_rate' => 5.0, // Below minimum
            'start_date' => now()->format('Y-m-d')
        ];

        $result = $this->calculatorService->calculateLoan($params);

        $this->assertFalse($result['success']);
        $this->assertStringContainsString('Interest rate is below minimum', $result['error']);
    }

    /** @test */
    public function it_validates_period_limits()
    {
        $params = [
            'product_id' => $this->loanProduct->id,
            'amount' => 100000,
            'period' => 100, // Above maximum
            'interest_rate' => 15.0,
            'start_date' => now()->format('Y-m-d')
        ];

        $result = $this->calculatorService->calculateLoan($params);

        $this->assertFalse($result['success']);
        $this->assertStringContainsString('Period exceeds maximum', $result['error']);
    }

    /** @test */
    public function it_calculates_percentage_fees_correctly()
    {
        $fee = Fee::factory()->create([
            'company_id' => $this->company->id,
            'fee_type' => 'percentage',
            'amount' => 2.0, // 2%
            'deduction_criteria' => 'distribute_fee_evenly_to_all_repayments',
            'include_in_schedule' => true,
            'status' => 'active'
        ]);

        $this->loanProduct->update(['fees_ids' => [$fee->id]]);

        $params = [
            'product_id' => $this->loanProduct->id,
            'amount' => 100000,
            'period' => 12,
            'interest_rate' => 15.0,
            'start_date' => now()->format('Y-m-d')
        ];

        $result = $this->calculatorService->calculateLoan($params);

        $this->assertTrue($result['success']);
        
        // Fee should be 2% of 100000 = 2000
        $this->assertEquals(2000, $result['fees'][0]['amount']);
        
        // Fee should be distributed evenly across installments
        $expectedFeePerInstallment = 2000 / 12;
        $this->assertEquals(round($expectedFeePerInstallment, 2), $result['schedule'][0]['fee_amount']);
    }

    /** @test */
    public function it_calculates_fixed_fees_correctly()
    {
        $fee = Fee::factory()->create([
            'company_id' => $this->company->id,
            'fee_type' => 'fixed',
            'amount' => 1000,
            'deduction_criteria' => 'charge_same_fee_to_all_repayments',
            'include_in_schedule' => true,
            'status' => 'active'
        ]);

        $this->loanProduct->update(['fees_ids' => [$fee->id]]);

        $params = [
            'product_id' => $this->loanProduct->id,
            'amount' => 100000,
            'period' => 12,
            'interest_rate' => 15.0,
            'start_date' => now()->format('Y-m-d')
        ];

        $result = $this->calculatorService->calculateLoan($params);

        $this->assertTrue($result['success']);
        
        // Fixed fee should be 1000
        $this->assertEquals(1000, $result['fees'][0]['amount']);
        
        // Fee should be charged on every installment
        foreach ($result['schedule'] as $installment) {
            $this->assertEquals(1000, $installment['fee_amount']);
        }
    }

    /** @test */
    public function it_handles_fee_on_first_repayment()
    {
        $fee = Fee::factory()->create([
            'company_id' => $this->company->id,
            'fee_type' => 'fixed',
            'amount' => 1000,
            'deduction_criteria' => 'charge_fee_on_first_repayment',
            'include_in_schedule' => true,
            'status' => 'active'
        ]);

        $this->loanProduct->update(['fees_ids' => [$fee->id]]);

        $params = [
            'product_id' => $this->loanProduct->id,
            'amount' => 100000,
            'period' => 12,
            'interest_rate' => 15.0,
            'start_date' => now()->format('Y-m-d')
        ];

        $result = $this->calculatorService->calculateLoan($params);

        $this->assertTrue($result['success']);
        
        // Fee should only be on first installment
        $this->assertEquals(1000, $result['schedule'][0]['fee_amount']);
        for ($i = 1; $i < count($result['schedule']); $i++) {
            $this->assertEquals(0, $result['schedule'][$i]['fee_amount']);
        }
    }

    /** @test */
    public function it_handles_fee_on_last_repayment()
    {
        $fee = Fee::factory()->create([
            'company_id' => $this->company->id,
            'fee_type' => 'fixed',
            'amount' => 1000,
            'deduction_criteria' => 'charge_fee_on_last_repayment',
            'include_in_schedule' => true,
            'status' => 'active'
        ]);

        $this->loanProduct->update(['fees_ids' => [$fee->id]]);

        $params = [
            'product_id' => $this->loanProduct->id,
            'amount' => 100000,
            'period' => 12,
            'interest_rate' => 15.0,
            'start_date' => now()->format('Y-m-d')
        ];

        $result = $this->calculatorService->calculateLoan($params);

        $this->assertTrue($result['success']);
        
        // Fee should only be on last installment
        $lastIndex = count($result['schedule']) - 1;
        $this->assertEquals(1000, $result['schedule'][$lastIndex]['fee_amount']);
        
        for ($i = 0; $i < $lastIndex; $i++) {
            $this->assertEquals(0, $result['schedule'][$i]['fee_amount']);
        }
    }

    /** @test */
    public function it_handles_multiple_fees()
    {
        $fee1 = Fee::factory()->create([
            'company_id' => $this->company->id,
            'fee_type' => 'percentage',
            'amount' => 1.0, // 1%
            'deduction_criteria' => 'distribute_fee_evenly_to_all_repayments',
            'include_in_schedule' => true,
            'status' => 'active'
        ]);

        $fee2 = Fee::factory()->create([
            'company_id' => $this->company->id,
            'fee_type' => 'fixed',
            'amount' => 500,
            'deduction_criteria' => 'charge_fee_on_first_repayment',
            'include_in_schedule' => true,
            'status' => 'active'
        ]);

        $this->loanProduct->update(['fees_ids' => [$fee1->id, $fee2->id]]);

        $params = [
            'product_id' => $this->loanProduct->id,
            'amount' => 100000,
            'period' => 12,
            'interest_rate' => 15.0,
            'start_date' => now()->format('Y-m-d')
        ];

        $result = $this->calculatorService->calculateLoan($params);

        $this->assertTrue($result['success']);
        $this->assertCount(2, $result['fees']);
        
        // First installment should have both fees
        $firstInstallmentFee = $result['schedule'][0]['fee_amount'];
        $expectedFee = (100000 * 0.01 / 12) + 500; // Percentage fee distributed + fixed fee
        $this->assertEquals(round($expectedFee, 2), $firstInstallmentFee);
    }

    /** @test */
    public function it_generates_correct_schedule_dates()
    {
        $startDate = now()->format('Y-m-d');
        
        $params = [
            'product_id' => $this->loanProduct->id,
            'amount' => 100000,
            'period' => 3,
            'interest_rate' => 15.0,
            'start_date' => $startDate
        ];

        $result = $this->calculatorService->calculateLoan($params);

        $this->assertTrue($result['success']);
        $this->assertCount(3, $result['schedule']);
        
        // Check that dates are properly formatted
        foreach ($result['schedule'] as $installment) {
            $this->assertArrayHasKey('due_date', $installment);
            $this->assertArrayHasKey('end_date', $installment);
            $this->assertArrayHasKey('end_grace_date', $installment);
        }
    }

    /** @test */
    public function it_calculates_totals_correctly()
    {
        $params = [
            'product_id' => $this->loanProduct->id,
            'amount' => 100000,
            'period' => 12,
            'interest_rate' => 15.0,
            'start_date' => now()->format('Y-m-d')
        ];

        $result = $this->calculatorService->calculateLoan($params);

        $this->assertTrue($result['success']);
        
        $totals = $result['totals'];
        
        // Principal should match input
        $this->assertEquals(100000, $totals['principal']);
        
        // Total amount should be principal + interest + fees
        $expectedTotal = $totals['principal'] + $totals['total_interest'] + $totals['total_fees'];
        $this->assertEquals($expectedTotal, $totals['total_amount']);
        
        // Monthly payment should be total amount divided by period
        $expectedMonthlyPayment = $totals['total_amount'] / 12;
        $this->assertEquals(round($expectedMonthlyPayment, 2), $totals['monthly_payment']);
    }
}
