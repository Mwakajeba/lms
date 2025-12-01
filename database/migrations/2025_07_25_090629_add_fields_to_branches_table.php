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
        if (!Schema::hasColumn('branches', 'branch_id')) {
            Schema::table('branches', function (Blueprint $table) {
                $table->uuid('branch_id')->nullable()->after('id');
            });
        }
        
        if (!Schema::hasColumn('branches', 'branch_name')) {
            Schema::table('branches', function (Blueprint $table) {
                $table->string('branch_name')->nullable()->after('name');
            });
        }
        
        if (!Schema::hasColumn('branches', 'location')) {
            Schema::table('branches', function (Blueprint $table) {
                $table->string('location')->nullable()->after('address');
            });
        }
        
        if (!Schema::hasColumn('branches', 'manager_name')) {
        Schema::table('branches', function (Blueprint $table) {
                $table->string('manager_name')->nullable()->after('location');
            });
        }
        
        if (!Schema::hasColumn('branches', 'status')) {
            Schema::table('branches', function (Blueprint $table) {
                $table->enum('status', ['active', 'inactive'])->default('active')->after('manager_name');
            });
        }

        // Update existing records with UUIDs and branch_name
        $branches = \DB::table('branches')->whereNull('branch_id')->get();
        foreach ($branches as $branch) {
            \DB::table('branches')
                ->where('id', $branch->id)
                ->update([
                    'branch_id' => \Illuminate\Support\Str::uuid(),
                    'branch_name' => $branch->name
                ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('branches', function (Blueprint $table) {
            $table->dropColumn(['branch_id', 'branch_name', 'location', 'manager_name', 'status']);
        });
    }
};
