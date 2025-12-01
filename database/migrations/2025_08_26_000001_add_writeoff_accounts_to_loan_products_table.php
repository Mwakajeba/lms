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
        Schema::table('loan_products', function (Blueprint $table) {
            $table->foreignId('direct_writeoff_account_id')->nullable()->constrained('chart_accounts')->after('interest_revenue_account_id');
            $table->foreignId('provision_writeoff_account_id')->nullable()->constrained('chart_accounts')->after('direct_writeoff_account_id');
            $table->foreignId('income_provision_account_id')->nullable()->constrained('chart_accounts')->after('provision_writeoff_account_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('loan_products', function (Blueprint $table) {
            $table->dropForeign(['direct_writeoff_account_id']);
            $table->dropColumn('direct_writeoff_account_id');
            $table->dropForeign(['provision_writeoff_account_id']);
            $table->dropColumn('provision_writeoff_account_id');
            $table->dropForeign(['income_provision_account_id']);
            $table->dropColumn('income_provision_account_id');
        });
    }
};
