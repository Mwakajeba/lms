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
        Schema::create('penalties', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('penalty_income_account_id')->constrained('chart_accounts')->onDelete('cascade');
            $table->foreignId('penalty_receivables_account_id')->constrained('chart_accounts')->onDelete('cascade');
            $table->enum('penalty_type', ['fixed', 'percentage'])->default('fixed');
            $table->enum('charge_frequency', ['daily', 'one_time'])->default('one_time');
            $table->decimal('amount', 15, 2)->default(0);
            $table->enum('deduction_type', [
                'over_due_principal_amount',
                'over_due_interest_amount',
                'over_due_principal_and_interest',
                'total_principal_amount_released'
            ])->default('over_due_principal_amount');
            $table->text('description')->nullable();
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->foreignId('branch_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('penalties');
    }
};
