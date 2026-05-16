<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('distributions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->date('distribution_date');
            $table->foreignUuid('from_outlet_id')->constrained('outlets')->cascadeOnDelete();
            $table->foreignUuid('to_outlet_id')->constrained('outlets')->cascadeOnDelete();
            $table->foreignUuid('created_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('distributions');
    }
};
