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
        Schema::create('budget_lines', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('budget_id');     // FK to budgets
            $table->unsignedBigInteger('account_id');    // FK to chart of accounts
            $table->decimal('amount', 20, 2);            // Budgeted amount
            $table->text('description')->nullable();     // Optional: line-specific notes
            $table->timestamps();

            $table->foreign('budget_id')->references('id')->on('budgets')->onDelete('cascade');
            $table->foreign('account_id')->references('id')->on('chart_accounts')->onDelete('restrict');

            $table->unique(['budget_id', 'account_id'], 'unique_budget_account');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('budget_lines');
    }
};
