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
        Schema::create('groups', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('loan_officer')->constrained('users')->onDelete('cascade');
            $table->foreignId('branch_id')->constrained('branches')->onDelete('cascade');
            $table->integer('minimum_members')->nullable();
            $table->integer('maximum_members')->nullable();
            $table->foreignId('group_leader')->nullable()->constrained('customers')->onDelete('cascade');
            $table->enum('meeting_day', ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday','every_week','every_day','every_month'])->nullable();
            $table->time('meeting_time')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('groups');
    }
};
