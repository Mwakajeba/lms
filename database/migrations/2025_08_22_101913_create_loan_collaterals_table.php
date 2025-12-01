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
        Schema::create('loan_collaterals', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('loan_id');
            $table->string('type'); // property, vehicle, equipment, cash, jewelry, electronics, etc.
            $table->string('title'); // Title/name of the collateral
            $table->text('description');
            $table->decimal('estimated_value', 15, 2);
            $table->decimal('appraised_value', 15, 2)->nullable();
            $table->string('status')->default('active'); // active, sold, released, foreclosed, damaged
            $table->string('condition')->nullable(); // excellent, good, fair, poor
            $table->string('location')->nullable(); // Where the collateral is located
            $table->date('valuation_date')->nullable();
            $table->string('valuator_name')->nullable();
            $table->text('notes')->nullable();
            $table->string('serial_number')->nullable(); // For vehicles, equipment, etc.
            $table->string('registration_number')->nullable(); // For vehicles
            $table->json('images')->nullable(); // Store image paths as JSON array
            $table->json('documents')->nullable(); // Store document paths as JSON array
            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamp('status_changed_at')->nullable();
            $table->string('status_changed_by')->nullable();
            $table->text('status_change_reason')->nullable();
            $table->timestamps();

            $table->foreign('loan_id')->references('id')->on('loans')->onDelete('cascade');
            $table->foreign('created_by')->references('id')->on('users');
            $table->foreign('updated_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('loan_collaterals');
    }
};
