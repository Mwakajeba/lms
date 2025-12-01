<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('customer_file_types', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained()->onDelete('cascade');
            $table->foreignId('filetype_id')->constrained()->onDelete('cascade');
            $table->string('document_path')->nullable(); // e.g. for storing uploaded file path
            $table->timestamps();

            $table->unique(['customer_id', 'filetype_id']); // prevent duplicates
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customer_file_types');
    }
};

