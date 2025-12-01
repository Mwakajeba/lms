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
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->onDelete('cascade');
            $table->string('plan_name');
            $table->text('plan_description')->nullable();
            $table->decimal('amount', 10, 2);
            $table->string('currency', 3)->default('TZS');
            $table->enum('billing_cycle', ['monthly', 'quarterly', 'half-yearly', 'yearly'])->default('monthly');
            $table->date('start_date');
            $table->date('end_date');
            $table->enum('status', ['active', 'inactive', 'expired', 'cancelled', 'pending'])->default('pending');
            $table->enum('payment_status', ['paid', 'unpaid', 'pending', 'failed'])->default('pending');
            $table->string('payment_method')->nullable();
            $table->string('transaction_id')->nullable();
            $table->text('payment_notes')->nullable();
            $table->timestamp('payment_date')->nullable();
            $table->timestamp('last_reminder_sent')->nullable();
            $table->integer('reminder_count')->default(0);
            $table->boolean('auto_renew')->default(false);
            $table->json('features')->nullable(); // Store plan features as JSON
            $table->timestamps();

            $table->index(['company_id', 'status']);
            $table->index(['end_date', 'status']);
            $table->index('payment_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subscriptions');
    }
};