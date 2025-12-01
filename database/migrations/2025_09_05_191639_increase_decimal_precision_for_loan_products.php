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
        Schema::table('loan_products', function (Blueprint $table) {
            // Increase decimal precision for interest rates to allow more than 14 decimal places
            $table->decimal('minimum_interest_rate', 20, 15)->change();
            $table->decimal('maximum_interest_rate', 20, 15)->change();
            
            // Increase decimal precision for principal amounts to allow more than 14 decimal places
            $table->decimal('minimum_principal', 25, 15)->change();
            $table->decimal('maximum_principal', 25, 15)->change();
            
            // Increase decimal precision for top up and collateral values
            $table->decimal('top_up_type_value', 25, 15)->change();
            //$table->decimal('cash_collateral_value', 25, 15)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('loan_products', function (Blueprint $table) {
            // Revert to original precision
            $table->decimal('minimum_interest_rate', 8, 2)->change();
            $table->decimal('maximum_interest_rate', 8, 2)->change();
            $table->decimal('minimum_principal', 15, 2)->change();
            $table->decimal('maximum_principal', 15, 2)->change();
            $table->decimal('top_up_type_value', 15, 2)->change();
            $table->decimal('cash_collateral_value', 15, 2)->change();
        });
    }
};
