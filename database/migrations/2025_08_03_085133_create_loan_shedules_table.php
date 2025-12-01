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
        Schema::create('loan_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('loan_id')->constrained('loans')->onDelete('cascade');
            $table->date('due_date')->nullable();
            $table->date('end_grace_date')->nullable();
            $table->date('end_date')->nullable();
            $table->date('end_pernalty_date')->nullable();
            $table->foreignId('customer_id')->constrained('customers')->onDelete('cascade');
            $table->decimal('principal', 10, 2);
            $table->decimal('interest', 10, 2);
            $table->decimal('fee_amount', 10, 2);
            $table->decimal('penalty_amount', 10, 2)->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('loan_shedules');
    }
};
