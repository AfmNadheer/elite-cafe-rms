<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Public website: editable settings, contact form messages and room photos.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('site_settings', function (Blueprint $table) {
            $table->string('key', 50)->primary();
            $table->text('value')->nullable();
            $table->timestamps();
        });

        Schema::create('contact_messages', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->string('email', 100);
            $table->string('phone', 20)->nullable();
            $table->text('message');
            $table->boolean('is_read')->default(false);
            $table->string('ip_address', 45)->nullable();
            $table->timestamps();
        });

        Schema::table('room_types', function (Blueprint $table) {
            $table->string('photo')->nullable()->after('description');
        });
    }

    public function down(): void
    {
        Schema::table('room_types', fn (Blueprint $table) => $table->dropColumn('photo'));
        Schema::dropIfExists('contact_messages');
        Schema::dropIfExists('site_settings');
    }
};
