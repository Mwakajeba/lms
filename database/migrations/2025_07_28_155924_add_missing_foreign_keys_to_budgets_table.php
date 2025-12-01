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
        Schema::table('budgets', function (Blueprint $table) {
            // Add missing description column if it doesn't exist
            if (!Schema::hasColumn('budgets', 'description')) {
                $table->text('description')->nullable()->after('year');
            }
            
            // Add foreign key constraints if they don't exist
            if (!Schema::hasColumn('budgets', 'branch_id_foreign')) {
                $table->foreign('branch_id')->references('id')->on('branches')->onDelete('cascade');
            }
            
            if (!Schema::hasColumn('budgets', 'company_id_foreign')) {
                $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('budgets', function (Blueprint $table) {
            // Drop foreign key constraints
            $table->dropForeign(['branch_id']);
            $table->dropForeign(['company_id']);
            
            // Drop description column
            $table->dropColumn('description');
        });
    }
};
