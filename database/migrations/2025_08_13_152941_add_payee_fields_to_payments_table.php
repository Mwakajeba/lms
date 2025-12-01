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
        Schema::table('payments', function (Blueprint $table) {
            $table->string('payee_type')->nullable()->after('bank_account_id'); // 'customer', 'supplier', or 'other'
            $table->unsignedBigInteger('payee_id')->nullable()->after('payee_type'); // if customer/supplier, store customer_id/supplier_id
            $table->string('payee_name')->nullable()->after('payee_id'); // for manual entry if 'other'
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn(['payee_type', 'payee_id', 'payee_name']);
        });
    }
};
