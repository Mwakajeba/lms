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
        // Only add columns that don't exist
        if (!Schema::hasColumn('users', 'user_id')) {
            Schema::table('users', function (Blueprint $table) {
                $table->uuid('user_id')->nullable()->after('id');
            });
        }
        
        if (!Schema::hasColumn('users', 'company_id')) {
        Schema::table('users', function (Blueprint $table) {
                $table->foreignId('company_id')->nullable()->constrained()->onDelete('cascade')->after('user_id');
            });
        }
        
        if (!Schema::hasColumn('users', 'status')) {
            Schema::table('users', function (Blueprint $table) {
                $table->enum('status', ['active', 'inactive', 'suspended'])->default('active')->after('is_active');
            });
        }

        // Update existing records with UUIDs and company_id
        $users = \DB::table('users')->whereNull('user_id')->get();
        foreach ($users as $user) {
            $branch = \DB::table('branches')->where('id', $user->branch_id)->first();
            $companyId = $branch ? $branch->company_id : null;
            
            \DB::table('users')
                ->where('id', $user->id)
                ->update([
                    'user_id' => \Illuminate\Support\Str::uuid(),
                    'company_id' => $companyId
                ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['company_id']);
            $table->dropColumn(['user_id', 'company_id', 'status']);
        });
    }
};
