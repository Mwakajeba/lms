<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('loan_products', function (Blueprint $table) {
            $table->string('top_up_type')->nullable()->change();
        });
    }

    public function down()
    {
        Schema::table('loan_products', function (Blueprint $table) {
            $table->string('top_up_type')->nullable(false)->change();
        });
    }
};
