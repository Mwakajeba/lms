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
        Schema::create('suppliers', function (Blueprint $table) {
            $table->id();

            // Basic info
            $table->string('name');
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->text('address')->nullable();
            $table->string('region')->nullable();

            // Business & legal
            $table->string('company_registration_name')->nullable();
            $table->string('tin_number')->nullable();
            $table->string('vat_number')->nullable();

            // Banking info
            $table->string('bank_name')->nullable();
            $table->string('bank_account_number')->nullable();
            $table->string('account_name')->nullable();

            // Supply details
            $table->text('products_or_services')->nullable();
            $table->enum('status', ['active', 'inactive', 'blacklisted'])->default('active');

            // Company and branch relationships
            $table->foreignId('company_id')->constrained()->onDelete('cascade');
            $table->foreignId('branch_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('set null');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('suppliers');
    }
};
