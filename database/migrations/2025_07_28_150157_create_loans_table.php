<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('loans', function (Blueprint $table) {
            $table->id();

            $table->foreignId('customer_id')
                ->constrained('customers') // Assuming customers table exists
                ->onDelete('cascade');

            $table->foreignId('group_id')
                ->nullable()
                ->constrained('groups') // Assuming groups table exists
                ->onDelete('set null'); // Assuming groups table exists

            $table->foreignId('product_id')
                ->nullable()
                ->constrained('loan_products') // Assuming loan_products table exists
                ->onDelete('set null'); // Assuming products table exists

            $table->foreignId('loan_officer_id')
                ->nullable()
                ->constrained('users') // Assuming loan_products table exists
                ->onDelete('set null'); // Assuming products table exists

            $table->decimal('amount', 15, 2)->default(0);
            $table->decimal('interest', 8, 2)->default(0); // Interest rate percentage
            $table->decimal('interest_amount', 15, 2)->default(0);
            $table->decimal('amount_total', 15, 2)->default(0);

            $table->integer('period')->default(0); // e.g., number of months

            $table->foreignId('bank_account_id')
                ->nullable()
                ->constrained('bank_accounts') // assuming `bank_accounts` table exists
                ->onDelete('set null');

            $table->foreignId('branch_id')
                ->nullable()
                ->constrained('branches') // assuming `bank_accounts` table exists
                ->onDelete('cascade');

            $table->date('date_applied')->nullable();
            $table->date('disbursed_on')->nullable();
            $table->date('first_repayment_date')->nullable();
            $table->date('last_repayment_date')->nullable();

            $table->string('interest_cycle');

            $table->string('status')->nullable();
            $table->string('sector')->nullable();
            $table->string('loanNo')->unique();

            $table->foreignId('top_up_id')
                ->nullable()
                ->constrained('loans') // Self-referencing (top-up loan)
                ->onDelete('set null');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('loans');
    }
};
