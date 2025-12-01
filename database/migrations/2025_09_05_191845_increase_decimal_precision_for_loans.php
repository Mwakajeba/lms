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
        Schema::table('loans', function (Blueprint $table) {
            // Increase decimal precision for loan amounts to allow more than 14 decimal places
            $table->decimal('amount', 25, 15)->change();
            $table->decimal('interest', 20, 15)->change(); // Interest rate percentage
            $table->decimal('interest_amount', 25, 15)->change();
            $table->decimal('amount_total', 25, 15)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('loans', function (Blueprint $table) {
            // Revert to original precision
            $table->decimal('amount', 15, 2)->change();
            $table->decimal('interest', 8, 2)->change();
            $table->decimal('interest_amount', 15, 2)->change();
            $table->decimal('amount_total', 15, 2)->change();
        });
    }
};
