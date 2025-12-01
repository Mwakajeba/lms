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
        Schema::create('bank_reconciliation_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bank_reconciliation_id')->constrained('bank_reconciliations')->onDelete('cascade');
            $table->foreignId('gl_transaction_id')->nullable()->constrained('gl_transactions')->onDelete('set null');
            
            // Transaction details
            $table->string('transaction_type')->nullable(); // 'bank_statement', 'book_entry', 'adjustment'
            $table->string('reference')->nullable();
            $table->text('description');
            $table->date('transaction_date');
            $table->decimal('amount', 15, 2);
            $table->enum('nature', ['debit', 'credit']);
            
            // Reconciliation status
            $table->boolean('is_reconciled')->default(false);
            $table->boolean('is_bank_statement_item')->default(false);
            $table->boolean('is_book_entry')->default(false);
            
            // Matching details
            $table->foreignId('matched_with_item_id')->nullable()->constrained('bank_reconciliation_items')->onDelete('set null');
            $table->timestamp('reconciled_at')->nullable();
            $table->foreignId('reconciled_by')->nullable()->constrained('users')->onDelete('set null');
            
            // Notes
            $table->text('notes')->nullable();
            
            // Timestamps
            $table->timestamps();
            
            // Indexes
            $table->index(['bank_reconciliation_id', 'transaction_date'], 'bank_recon_items_recon_date_idx');
            $table->index(['is_reconciled', 'transaction_type'], 'bank_recon_items_status_type_idx');
            $table->index(['matched_with_item_id'], 'bank_recon_items_matched_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bank_reconciliation_items');
    }
};
