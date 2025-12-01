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
        Schema::table('account_class', function (Blueprint $table) {
            $table->integer('range_from')->nullable()->after('name');
            $table->integer('range_to')->nullable()->after('range_from');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('account_class', function (Blueprint $table) {
            $table->dropColumn(['range_from', 'range_to']);
        });
    }
};
