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
        Schema::table('repayments', function (Blueprint $table) {
            $table->text('deletion_reason')->nullable()->after('deleted_approved_at');
        });

        Schema::table('receipts', function (Blueprint $table) {
            $table->text('deletion_reason')->nullable()->after('deleted_approved_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('repayments', function (Blueprint $table) {
            $table->dropColumn('deletion_reason');
        });

        Schema::table('receipts', function (Blueprint $table) {
            $table->dropColumn('deletion_reason');
        });
    }
};
