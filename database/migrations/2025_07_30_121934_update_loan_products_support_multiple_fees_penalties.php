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
            // Drop the single fee and penalty foreign key columns
            $table->dropForeign(['fees_id']);
            $table->dropForeign(['penalty_id']);
            $table->dropColumn(['fees_id', 'penalty_id']);

            // Add JSON columns for multiple fees and penalties
            $table->json('fees_ids')->nullable()->after('interest_revenue_account_id');
            $table->json('penalty_ids')->nullable()->after('fees_ids');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('loan_products', function (Blueprint $table) {
            // Remove JSON columns
            $table->dropColumn(['fees_ids', 'penalty_ids']);

            // Add back the single foreign key columns
            $table->foreignId('fees_id')->nullable()->constrained('fees')->onDelete('set null');
            $table->foreignId('penalty_id')->nullable()->constrained('penalties')->onDelete('set null');
        });
    }
};
