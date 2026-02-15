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
        Schema::table('receipts', function (Blueprint $table) {
            $table->softDeletes();
            $table->boolean('deleted_approved')->default(false)->after('deleted_at');
            $table->foreignId('deleted_approved_by')->nullable()->after('deleted_approved')->constrained('users')->onDelete('set null');
            $table->timestamp('deleted_approved_at')->nullable()->after('deleted_approved_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('receipts', function (Blueprint $table) {
            $table->dropForeign(['deleted_approved_by']);
            $table->dropColumn(['deleted_at', 'deleted_approved', 'deleted_approved_by', 'deleted_approved_at']);
        });
    }
};
