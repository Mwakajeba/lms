<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('loan_products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('product_type');
            $table->decimal('minimum_interest_rate', 8, 2);
            $table->decimal('maximum_interest_rate', 8, 2);
            $table->string('interest_cycle');
            $table->string('interest_method');
            $table->decimal('minimum_principal', 15, 2);
            $table->decimal('maximum_principal', 15, 2);
            $table->integer('minimum_period');
            $table->integer('maximum_period');
            $table->string('top_up_type')->nullable(); // e.g., fixed, percentage
            $table->decimal('top_up_type_value', 15, 2)->default(0);
            $table->boolean('has_cash_collateral')->default(false);
            $table->string('cash_collateral_type')->nullable();  //  eg.cash deposit 
            $table->string('cash_collateral_value_type')->nullable(); // fixed or percentage
            $table->decimal('cash_collateral_value', 15, 2)->nullable();
            $table->boolean('has_approval_levels')->default(false);
            $table->string('approval_levels')->nullable(); // Store approval levels configuration
            $table->foreignId('principal_receivable_account_id')->constrained('chart_accounts');
            $table->foreignId('interest_receivable_account_id')->constrained('chart_accounts');
            $table->foreignId('interest_revenue_account_id')->constrained('chart_accounts');
            $table->foreignId('fees_id')->nullable()->constrained('fees')->onDelete('set null');
            $table->foreignId('penalty_id')->nullable()->constrained('penalties')->onDelete('set null');
            $table->string('repayment_order')->nullable(); // Stores comma-separated repayment order
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('loan_products');
    }
};
