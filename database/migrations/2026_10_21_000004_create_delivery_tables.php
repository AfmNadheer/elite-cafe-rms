<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Online delivery orders: customer accounts, delivery details and the delivery fee on bills.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->string('email', 100)->unique();
            $table->string('phone', 20);
            $table->string('password');
            $table->text('address')->nullable();          // remembered for the next order
            $table->rememberToken();
            $table->timestamps();
        });

        // An online order has no waiter and can be a delivery
        Schema::table('orders', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->nullable()->change();
            $table->enum('order_type', ['dine_in', 'takeaway', 'delivery'])->default('dine_in')->change();
            $table->foreignId('customer_id')->nullable()->after('user_id')->constrained()->nullOnDelete();
        });

        Schema::create('deliveries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->unique()->constrained()->cascadeOnDelete();
            $table->foreignId('customer_id')->constrained()->restrictOnDelete();
            $table->text('address');
            $table->string('phone', 20);
            $table->string('notes', 255)->nullable();
            $table->decimal('delivery_fee', 10, 2)->default(0);
            $table->enum('payment_method', ['cod', 'online']);                     // cash on delivery or PayHere
            $table->enum('payment_status', ['unpaid', 'paid'])->default('unpaid');
            $table->foreignId('online_payment_id')->nullable()->constrained()->nullOnDelete();
            $table->enum('status', ['placed', 'out_for_delivery', 'delivered', 'cancelled'])->default('placed');
            $table->string('rider_name', 100)->nullable();
            $table->string('rider_phone', 20)->nullable();
            $table->dateTime('dispatched_at')->nullable();
            $table->dateTime('delivered_at')->nullable();
            $table->string('cancel_reason', 255)->nullable();
            $table->timestamps();
        });

        Schema::table('bills', function (Blueprint $table) {
            $table->decimal('delivery_fee', 10, 2)->default(0)->after('service_charge');
        });
    }

    public function down(): void
    {
        Schema::table('bills', fn (Blueprint $table) => $table->dropColumn('delivery_fee'));
        Schema::dropIfExists('deliveries');
        Schema::table('orders', function (Blueprint $table) {
            $table->dropConstrainedForeignId('customer_id');
        });
        Schema::dropIfExists('customers');
    }
};
