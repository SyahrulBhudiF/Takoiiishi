<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('distribution_items', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('distribution_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('ingredient_id')->constrained()->cascadeOnDelete();
            $table->decimal('quantity', 12, 2);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('distribution_items');
    }
};
