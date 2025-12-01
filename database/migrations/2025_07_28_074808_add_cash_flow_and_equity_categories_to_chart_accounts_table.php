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
        Schema::table('chart_accounts', function (Blueprint $table) {
            $table->foreignId('cash_flow_category_id')->nullable()->constrained('cash_flow_categories')->onDelete('set null');
            $table->foreignId('equity_category_id')->nullable()->constrained('equity_categories')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('chart_accounts', function (Blueprint $table) {
            $table->dropForeign(['cash_flow_category_id']);
            $table->dropForeign(['equity_category_id']);
            $table->dropColumn(['cash_flow_category_id', 'equity_category_id']);
        });
    }
};
