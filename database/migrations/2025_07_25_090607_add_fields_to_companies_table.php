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
        if (!Schema::hasColumn('companies', 'license_number')) {
            Schema::table('companies', function (Blueprint $table) {
                $table->string('license_number')->nullable()->after('address');
            });
        }
        
        if (!Schema::hasColumn('companies', 'registration_date')) {
        Schema::table('companies', function (Blueprint $table) {
                $table->date('registration_date')->nullable()->after('license_number');
            });
        }
        
        if (!Schema::hasColumn('companies', 'status')) {
            Schema::table('companies', function (Blueprint $table) {
                $table->enum('status', ['active', 'inactive', 'suspended'])->default('active')->after('registration_date');
            });
        }

        // Update existing records with UUIDs if company_id is null
        $companies = \DB::table('companies')->whereNull('company_id')->get();
        foreach ($companies as $company) {
            \DB::table('companies')
                ->where('id', $company->id)
                ->update(['company_id' => \Illuminate\Support\Str::uuid()]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->dropColumn(['company_id', 'license_number', 'registration_date', 'status']);
        });
    }
};
