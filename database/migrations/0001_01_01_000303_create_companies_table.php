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
        Schema::create('companies', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('address')->nullable();
            $table->string('logo')->nullable();
            $table->string('bg_color')->nullable();
            $table->string('txt_color')->nullable();
            $table->timestamps();
        });

           if (!Schema::hasColumn('companies', 'company_id')) {
            Schema::table('companies', function (Blueprint $table) {
                $table->uuid('company_id')->nullable()->after('id');
            });
        }

        // Now you can safely query and update it
        $companies = DB::table('companies')->whereNull('company_id')->get();
        foreach ($companies as $company) {
            DB::table('companies')
                ->where('id', $company->id)
                ->update(['company_id' => Str::uuid()]);
        }

        // Other fields
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
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('companies');
    }
};
