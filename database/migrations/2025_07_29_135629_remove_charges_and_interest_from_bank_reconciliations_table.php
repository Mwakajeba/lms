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
        Schema::table('bank_reconciliations', function (Blueprint $table) {
            $table->dropColumn([
                'bank_statement_charges',
                'bank_statement_interest',
                'book_charges',
                'book_interest'
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bank_reconciliations', function (Blueprint $table) {
            $table->decimal('bank_statement_charges', 15, 2)->default(0)->after('bank_statement_balance');
            $table->decimal('bank_statement_interest', 15, 2)->default(0)->after('bank_statement_charges');
            $table->decimal('book_charges', 15, 2)->default(0)->after('book_balance');
            $table->decimal('book_interest', 15, 2)->default(0)->after('book_charges');
        });
    }
};
