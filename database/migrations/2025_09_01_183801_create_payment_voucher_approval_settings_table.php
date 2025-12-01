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
        Schema::create('payment_voucher_approval_settings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id');
            $table->integer('approval_levels')->default(2);
            $table->decimal('auto_approval_limit', 15, 2)->default(100000);
            $table->decimal('approval_threshold_1', 15, 2)->default(500000);
            $table->decimal('approval_threshold_2', 15, 2)->nullable();
            $table->decimal('approval_threshold_3', 15, 2)->nullable();
            $table->decimal('approval_threshold_4', 15, 2)->nullable();
            $table->decimal('approval_threshold_5', 15, 2)->nullable();
            $table->integer('escalation_time')->default(24); // hours
            $table->boolean('require_approval_for_all')->default(false);
            
            // Level 1 approval settings
            $table->string('level1_approval_type')->default('role'); // role or user
            $table->json('level1_approvers')->nullable(); // array of role names or user IDs
            
            // Level 2 approval settings
            $table->string('level2_approval_type')->nullable();
            $table->json('level2_approvers')->nullable();
            
            // Level 3 approval settings
            $table->string('level3_approval_type')->nullable();
            $table->json('level3_approvers')->nullable();
            
            // Level 4 approval settings
            $table->string('level4_approval_type')->nullable();
            $table->json('level4_approvers')->nullable();
            
            // Level 5 approval settings
            $table->string('level5_approval_type')->nullable();
            $table->json('level5_approvers')->nullable();
            
            $table->timestamps();
            
            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_voucher_approval_settings');
    }
};
