<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Dining tables and their live status (FR-10, FR-11).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('restaurant_tables', function (Blueprint $table) {
            $table->id();
            $table->string('table_no', 10)->unique();          // e.g. T01
            $table->string('section', 50)->nullable();         // e.g. Indoor, Outdoor
            $table->unsignedTinyInteger('capacity')->default(4);
            $table->enum('status', ['free', 'reserved', 'occupied'])->default('free');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('restaurant_tables');
    }
};
