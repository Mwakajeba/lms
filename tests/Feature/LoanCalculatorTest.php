<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Company;
use App\Models\Branch;
use App\Models\LoanProduct;
use App\Models\Fee;
use App\Models\Penalty;
use App\Services\LoanCalculatorService;
use Illuminate\Foundation\Testing\RefreshDatabase;

class LoanCalculatorTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $company;
    protected $branch;
    protected $loanProduct;
    protected $calculatorService;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create test data
        $this->company = Company::factory()->create();
        $this->branch = Branch::factory()->create(['company_id' => $this->company->id]);
        $this->user = User::factory()->create([
            'company_id' => $this->company->id,
            'branch_id' => $this->branch->id
        ]);
        
        // Create loan product
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
        
        $this->calculatorService = new LoanCalculatorService();
    }

    /** @test */
    public function it_can_calculate_flat_rate_loan()
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
        $this->assertEquals(100000, $result['totals']['principal']);
        $this->assertEquals(18000, $result['totals']['total_interest']); // 100000 * 0.15 * 12
        $this->assertEquals(118000, $result['totals']['total_amount']);
        $this->assertEquals(9833.33, $result['totals']['monthly_payment']); // 118000 / 12
    }

    /** @test */
    public function it_can_calculate_reducing_balance_equal_installment()
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
        $this->assertEquals(100000, $result['totals']['principal']);
        $this->assertGreaterThan(0, $result['totals']['total_interest']);
        $this->assertLessThan(18000, $result['totals']['total_interest']); // Should be less than flat rate
    }

    /** @test */
    public function it_can_calculate_reducing_balance_equal_principal()
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
        $this->assertEquals(100000, $result['totals']['principal']);
        $this->assertArrayHasKey('schedule', $result['interest_calculation']);
        $this->assertCount(12, $result['interest_calculation']['schedule']);
    }

    /** @test */
    public function it_validates_required_parameters()
    {
        $params = [
            'product_id' => $this->loanProduct->id,
            'amount' => 100000,
            // Missing period, interest_rate, start_date
        ];

        $result = $this->calculatorService->calculateLoan($params);

        $this->assertFalse($result['success']);
        $this->assertStringContainsString('Missing required parameter', $result['error']);
    }

    /** @test */
    public function it_validates_product_limits()
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
    public function it_calculates_fees_correctly()
    {
        // Create a fee
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
        $this->assertCount(1, $result['fees']);
        $this->assertEquals(2000, $result['fees'][0]['amount']); // 2% of 100000
    }

    /** @test */
    public function it_generates_repayment_schedule()
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
        $this->assertCount(12, $result['schedule']);
        
        // Check first installment
        $firstInstallment = $result['schedule'][0];
        $this->assertEquals(1, $firstInstallment['installment_number']);
        $this->assertArrayHasKey('due_date', $firstInstallment);
        $this->assertArrayHasKey('principal', $firstInstallment);
        $this->assertArrayHasKey('interest', $firstInstallment);
        $this->assertArrayHasKey('total_amount', $firstInstallment);
    }

    /** @test */
    public function it_can_compare_multiple_scenarios()
    {
        $scenarios = [
            [
                'product_id' => $this->loanProduct->id,
                'amount' => 100000,
                'period' => 12,
                'interest_rate' => 15.0,
                'start_date' => now()->format('Y-m-d'),
                'name' => 'Scenario 1'
            ],
            [
                'product_id' => $this->loanProduct->id,
                'amount' => 100000,
                'period' => 24,
                'interest_rate' => 15.0,
                'start_date' => now()->format('Y-m-d'),
                'name' => 'Scenario 2'
            ]
        ];

        $result = $this->calculatorService->compareLoans($scenarios);

        $this->assertTrue($result['success']);
        $this->assertCount(2, $result['comparisons']);
        $this->assertArrayHasKey('summary', $result);
    }

    /** @test */
    public function it_returns_available_products()
    {
        $products = $this->calculatorService->getAvailableProducts();

        $this->assertIsArray($products);
        $this->assertCount(1, $products);
        $this->assertEquals($this->loanProduct->id, $products[0]['id']);
        $this->assertEquals($this->loanProduct->name, $products[0]['name']);
    }

    /** @test */
    public function it_handles_inactive_products()
    {
        $this->loanProduct->update(['is_active' => false]);

        $products = $this->calculatorService->getAvailableProducts();

        $this->assertCount(0, $products);
    }

    /** @test */
    public function it_calculates_different_interest_cycles()
    {
        $this->loanProduct->update(['interest_cycle' => 'weekly']);

        $params = [
            'product_id' => $this->loanProduct->id,
            'amount' => 100000,
            'period' => 12,
            'interest_rate' => 15.0,
            'start_date' => now()->format('Y-m-d')
        ];

        $result = $this->calculatorService->calculateLoan($params);

        $this->assertTrue($result['success']);
        $this->assertCount(12, $result['schedule']);
    }

    /** @test */
    public function it_handles_grace_period()
    {
        $this->loanProduct->update(['grace_period' => 30]);

        $params = [
            'product_id' => $this->loanProduct->id,
            'amount' => 100000,
            'period' => 12,
            'interest_rate' => 15.0,
            'start_date' => now()->format('Y-m-d')
        ];

        $result = $this->calculatorService->calculateLoan($params);

        $this->assertTrue($result['success']);
        
        // Check that grace period is reflected in schedule
        foreach ($result['schedule'] as $installment) {
            $this->assertArrayHasKey('end_grace_date', $installment);
        }
    }

    /** @test */
    public function it_calculates_percentage_fees()
    {
        $fee = Fee::factory()->create([
            'company_id' => $this->company->id,
            'fee_type' => 'percentage',
            'amount' => 5.0, // 5%
            'deduction_criteria' => 'charge_fee_on_release_date',
            'include_in_schedule' => false,
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
        $this->assertEquals(5000, $result['fees'][0]['amount']); // 5% of 100000
    }

    /** @test */
    public function it_calculates_fixed_fees()
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
        $this->assertEquals(1000, $result['fees'][0]['amount']);
        
        // Check that fee is applied to first installment only
        $this->assertEquals(1000, $result['schedule'][0]['fee_amount']);
        for ($i = 1; $i < count($result['schedule']); $i++) {
            $this->assertEquals(0, $result['schedule'][$i]['fee_amount']);
        }
    }
}
