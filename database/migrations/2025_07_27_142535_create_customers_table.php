<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->string('customerNo')->unique();
            $table->string('name');
            $table->text('description')->nullable(); // Added description field
            $table->string('work')->nullable();
            $table->string('workAddress')->nullable();
            $table->string('phone1');
            $table->string('phone2')->nullable();
            $table->string('category')->nullable();

            // Foreign key to users table (registrar)
            $table->foreignId('registrar')->constrained('users')->onDelete('cascade');

            $table->string('idType')->nullable();
            $table->string('idNumber')->nullable();
            $table->date('dob');
            $table->foreignId('region_id')->nullable()->constrained('regions')->onDelete('set null');
            $table->foreignId('district_id')->nullable()->constrained('districts')->onDelete('set null');

            // Foreign key to branches
            $table->foreignId('branch_id')->constrained('branches')->onDelete('cascade');

            // Foreign key to companies
            $table->foreignId('company_id')->constrained('companies')->onDelete('cascade');

            $table->enum('sex', ['M', 'F']);
            $table->string('password');
            $table->date('dateRegistered');
            $table->string('relation')->nullable();
            $table->string('photo')->nullable();
            $table->string('document')->nullable();
            $table->boolean('has_cash_collateral')->default(false); // Changed to boolean with default

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
