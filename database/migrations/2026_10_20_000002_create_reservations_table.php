<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Table bookings made for customers (FR-12).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reservations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('table_id')->constrained('restaurant_tables')->restrictOnDelete();
            $table->string('customer_name', 100);
            $table->string('phone', 20);
            $table->dateTime('reserved_at');
            $table->unsignedTinyInteger('party_size');
            $table->enum('status', ['booked', 'seated', 'cancelled', 'no_show'])->default('booked');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reservations');
    }
};
