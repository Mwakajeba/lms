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
        Schema::create('bank_reconciliations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bank_account_id')->constrained('bank_accounts')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('branch_id')->constrained('branches')->onDelete('cascade');
            
            // Reconciliation period
            $table->date('reconciliation_date');
            $table->date('start_date');
            $table->date('end_date');
            
            // Bank statement details
            $table->decimal('bank_statement_balance', 15, 2)->default(0);
            $table->decimal('bank_statement_charges', 15, 2)->default(0);
            $table->decimal('bank_statement_interest', 15, 2)->default(0);
            
            // Book balance details
            $table->decimal('book_balance', 15, 2)->default(0);
            $table->decimal('book_charges', 15, 2)->default(0);
            $table->decimal('book_interest', 15, 2)->default(0);
            
            // Reconciliation results
            $table->decimal('adjusted_bank_balance', 15, 2)->default(0);
            $table->decimal('adjusted_book_balance', 15, 2)->default(0);
            $table->decimal('difference', 15, 2)->default(0);
            
            // Status and notes
            $table->enum('status', ['draft', 'in_progress', 'completed', 'cancelled'])->default('draft');
            $table->text('notes')->nullable();
            $table->text('bank_statement_notes')->nullable();
            
            // Timestamps
            $table->timestamps();
            
            // Indexes
            $table->index(['bank_account_id', 'reconciliation_date']);
            $table->index(['status', 'reconciliation_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bank_reconciliations');
    }
};
