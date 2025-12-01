<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('loan_approvals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('loan_id')->constrained('loans')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('role_name'); // The role that approved this level
            $table->integer('approval_level'); // 1, 2, 3, etc.
            $table->enum('action', ['checked', 'approved', 'authorized', 'rejected', 'defaulted', 'active'])->default('checked');
            $table->text('comments')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();

            // Ensure one approval per level per loan
            $table->unique(['loan_id', 'approval_level']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('loan_approvals');
    }
};