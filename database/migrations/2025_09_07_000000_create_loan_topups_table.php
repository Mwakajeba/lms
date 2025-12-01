<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('loan_topups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('old_loan_id')->constrained('loans');
            $table->foreignId('new_loan_id')->nullable()->constrained('loans');
            $table->decimal('old_balance', 15, 2);
            $table->decimal('topup_amount', 15, 2);
            $table->enum('topup_type', ['restructure', 'additional']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::dropIfExists('loan_topups');
    }
};
