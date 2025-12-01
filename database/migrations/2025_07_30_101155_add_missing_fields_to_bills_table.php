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
        Schema::table('bills', function (Blueprint $table) {
            $table->string('reference')->unique()->after('id');
            $table->decimal('total_amount', 20, 2)->default(0)->after('credit_account');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade')->after('total_amount');
            $table->foreignId('branch_id')->constrained('branches')->onDelete('cascade')->after('user_id');
            $table->foreignId('company_id')->constrained('companies')->onDelete('cascade')->after('branch_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bills', function (Blueprint $table) {
            $table->dropForeign(['user_id', 'branch_id', 'company_id']);
            $table->dropColumn(['reference', 'total_amount', 'user_id', 'branch_id', 'company_id']);
        });
    }
};
