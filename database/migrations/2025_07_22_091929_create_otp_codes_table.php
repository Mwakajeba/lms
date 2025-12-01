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
            Schema::create('otp_codes', function (Blueprint $table) {
            $table->id();
            $table->string('phone'); // or use foreignId if linked to users
            $table->string('code'); // OTP code
            $table->timestamp('expires_at')->nullable();
            $table->boolean('is_used')->default(false); // Optional: to track usage
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('otp_codes');
    }
};
