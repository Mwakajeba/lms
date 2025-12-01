<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('repayments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained('customers')->onDelete('cascade');
            $table->foreignId('loan_id')->constrained('loans')->onDelete('cascade');
            $table->foreignId('loan_schedule_id')->constrained('loan_schedules')->onDelete('cascade');
            $table->foreignId('bank_account_id')->constrained('chart_accounts')->onDelete('cascade');
            $table->decimal('principal',12,2);
            $table->decimal('interest',12,2);
            $table->decimal('penalt_amount',15,2);
            $table->decimal('fee_amount',12,2);
            $table->date('payment_date');
            $table->decimal('cash_deposit',12,2)->nullable();
            $table->date('due_date')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrationss.
     */
    public function down(): void
    {
        Schema::dropIfExists('repayments');
    }
};
