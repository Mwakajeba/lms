<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCashCollateralsTable extends Migration
{
    public function up()
    {
        Schema::create('cash_collaterals', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('customer_id');
            $table->unsignedBigInteger('type_id');
            $table->unsignedBigInteger('branch_id');
            $table->unsignedBigInteger('company_id');

            $table->decimal('amount', 15, 2)->default(0.00);

            $table->timestamps();

            // Foreign keys
            $table->foreign('customer_id')->references('id')->on('customers')->onDelete('cascade');
            $table->foreign('type_id')->references('id')->on('cash_collateral_types')->onDelete('cascade');
            $table->foreign('branch_id')->references('id')->on('branches')->onDelete('cascade');
            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('cash_collaterals');
    }
}
