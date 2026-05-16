<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('purchase_items', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('purchase_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('ingredient_id')->constrained()->cascadeOnDelete();
            $table->decimal('quantity', 12, 2);
            $table->decimal('price', 14, 2);
            $table->decimal('subtotal', 14, 2);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchase_items');
    }
};
