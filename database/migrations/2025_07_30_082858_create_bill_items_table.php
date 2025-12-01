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
        Schema::create('bill_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('bill_id'); // Foreign key to bills
            $table->unsignedBigInteger('debit_account'); 
            $table->decimal('amount', 20, 2); // Amount
            $table->timestamps();

            // Foreign key constraint
            $table->foreign('bill_id')->references('id')->on('bills')->onDelete('cascade');

            // Optional: Add foreign key for debit_account if you have a chart of accounts table
            $table->foreign('debit_account')->references('id')->on('chart_accounts')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bill_items');
    }
};
