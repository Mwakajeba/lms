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
        Schema::create('gl_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('chart_account_id')->constrained('chart_accounts')->onDelete('cascade');
            $table->foreignId('customer_id')->nullable()->constrained('customers')->onDelete('set null');
            $table->decimal('amount', 20, 2);
            $table->enum('nature', ['debit', 'credit']);
            $table->unsignedBigInteger('transaction_id');
            $table->string('transaction_type', 100);
            $table->timestamp('date')->useCurrent(); // This is fillable
            $table->text('description')->nullable();
            $table->foreignId('branch_id')->constrained('branches')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->timestamps(); // created_at, updated_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('gl_transactions');
    }
};
