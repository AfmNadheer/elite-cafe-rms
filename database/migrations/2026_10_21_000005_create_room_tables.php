<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Rooms: room types, rooms, bookings and room payments.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('room_types', function (Blueprint $table) {
            $table->id();
            $table->string('name', 50)->unique();                 // Single Room, Double Room ...
            $table->decimal('price_per_night', 10, 2);
            $table->unsignedTinyInteger('capacity');               // max guests
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::create('rooms', function (Blueprint $table) {
            $table->id();
            $table->string('room_no', 10)->unique();               // 101 ... 115
            $table->foreignId('room_type_id')->constrained()->restrictOnDelete();
            $table->enum('status', ['available', 'maintenance'])->default('available');   // occupancy comes from bookings
            $table->string('notes', 255)->nullable();
            $table->timestamps();
        });

        Schema::create('room_bookings', function (Blueprint $table) {
            $table->id();
            $table->string('booking_no', 20)->unique();            // RB261021-0001
            $table->foreignId('room_id')->constrained()->restrictOnDelete();
            $table->foreignId('customer_id')->nullable()->constrained()->nullOnDelete();   // online bookings (Step 5)
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();       // staff who made it
            $table->string('guest_name', 100);
            $table->string('guest_phone', 20);
            $table->string('guest_email', 100)->nullable();
            $table->string('guest_nic', 20)->nullable();            // NIC / passport, taken at check-in
            $table->unsignedTinyInteger('adults')->default(1);
            $table->unsignedTinyInteger('children')->default(0);
            $table->date('check_in');
            $table->date('check_out');
            $table->unsignedSmallInteger('nights');
            $table->decimal('price_per_night', 10, 2);             // copied at booking time
            $table->decimal('room_total', 10, 2);
            $table->decimal('extra_charges', 10, 2)->default(0);   // room service, laundry ...
            $table->string('extra_notes', 255)->nullable();
            $table->decimal('discount', 10, 2)->default(0);
            $table->decimal('total', 10, 2);
            $table->decimal('amount_paid', 10, 2)->default(0);
            $table->enum('status', ['booked', 'checked_in', 'checked_out', 'cancelled', 'no_show'])->default('booked');
            $table->enum('source', ['walk_in', 'phone', 'online'])->default('walk_in');
            $table->dateTime('checked_in_at')->nullable();
            $table->dateTime('checked_out_at')->nullable();
            $table->string('notes', 255)->nullable();
            $table->timestamps();
            $table->index(['room_id', 'check_in', 'check_out']);
        });

        Schema::create('room_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('room_booking_id')->constrained()->cascadeOnDelete();
            $table->enum('method', ['cash', 'card', 'online']);
            $table->decimal('amount', 10, 2);
            $table->string('card_type', 20)->nullable();
            $table->char('card_last4', 4)->nullable();
            $table->string('approval_code', 30)->nullable();
            $table->string('note', 100)->nullable();              // e.g. Advance, Final payment
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->dateTime('paid_at');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('room_payments');
        Schema::dropIfExists('room_bookings');
        Schema::dropIfExists('rooms');
        Schema::dropIfExists('room_types');
    }
};
