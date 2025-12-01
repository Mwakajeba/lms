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
        Schema::create('bills', function (Blueprint $table) {
            $table->id(); // id (primary key)
            $table->date('date'); // bill date
            $table->date('due_date')->nullable(); // due date (nullable if not always provided)
            $table->unsignedBigInteger('supplier_id'); // foreign key to suppliers table
            $table->text('note')->nullable(); // note (optional)
            $table->unsignedBigInteger('credit_account'); // reference to chart of accounts or similar
            $table->decimal('paid', 20, 2)->default(0); // paid amount
            $table->timestamps(); // created_at and updated_at

            // Optional: Add foreign key constraint
            $table->foreign('supplier_id')->references('id')->on('suppliers')->onDelete('cascade');
            // You can also add a foreign key for credit_account if applicable
            $table->foreign('credit_account')->references('id')->on('chart_accounts')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bills');
    }
};
