<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Card machine details for card payments.
 * Only the last 4 digits are kept: the full card number is never stored.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->string('card_type', 20)->nullable()->after('method');      // visa, mastercard, amex, other
            $table->char('card_last4', 4)->nullable()->after('card_type');
            $table->string('approval_code', 12)->nullable()->after('card_last4');  // from the card machine slip
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn(['card_type', 'card_last4', 'approval_code']);
        });
    }
};
