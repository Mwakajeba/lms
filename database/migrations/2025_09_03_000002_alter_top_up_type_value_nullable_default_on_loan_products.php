<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('loan_products', function (Blueprint $table) {
            // Make column nullable with default 0
            $table->decimal('top_up_type_value', 15, 2)->nullable()->default(0)->change();
        });
    }

    public function down(): void
    {
        Schema::table('loan_products', function (Blueprint $table) {
            // Revert to not nullable with default 0 (original state)
            $table->decimal('top_up_type_value', 15, 2)->default(0)->change();
        });
    }
}; 