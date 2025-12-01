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
            Schema::create('budgets', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Budget Name
            $table->year('year'); // Budget Year
            $table->unsignedBigInteger('user_id'); // Drop existing column
            $table->unsignedBigInteger('branch_id'); // Branch ID
            $table->unsignedBigInteger('company_id'); // Company ID
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('budgets');
    }
};
