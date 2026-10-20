<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Online card payments through the PayHere gateway.
 * One row per payment attempt. The same table will later serve
 * delivery orders and room bookings (payable_type / payable_id).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('online_payments', function (Blueprint $table) {
            $table->id();
            $table->string('order_ref', 40)->unique();            // order_id sent to PayHere, e.g. EC-O12-K3F9Q
            $table->string('payable_type', 50);                     // Order (later: DeliveryOrder, RoomBooking)
            $table->unsignedBigInteger('payable_id');
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();   // staff who started it
            $table->decimal('amount', 10, 2);
            $table->char('currency', 3)->default('LKR');
            $table->enum('status', ['pending', 'success', 'failed', 'cancelled', 'chargedback'])->default('pending');
            $table->json('data')->nullable();                       // bill details to save after payment
            $table->string('customer_name', 100)->nullable();
            $table->string('customer_phone', 20)->nullable();
            $table->string('customer_email', 100)->nullable();
            $table->string('gateway_payment_id', 30)->nullable();   // PayHere payment_id
            $table->string('method', 20)->nullable();               // VISA, MASTER, AMEX ...
            $table->string('card_no', 20)->nullable();              // masked by PayHere, e.g. ************1292
            $table->string('status_message')->nullable();
            $table->dateTime('paid_at')->nullable();
            $table->timestamps();
            $table->index(['payable_type', 'payable_id']);
        });

        // PayHere payment ids are longer than a card machine approval code
        Schema::table('payments', function (Blueprint $table) {
            $table->string('approval_code', 30)->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('online_payments');
    }
};
