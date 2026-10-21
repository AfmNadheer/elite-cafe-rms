<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * New headline and 8:00 AM – 2:00 AM hours.
 * Saved values are replaced only if they are still the old defaults, so the Admin's own edits are kept.
 */
return new class extends Migration
{
    public function up(): void
    {
        $old = [
            'hero_title' => 'Sri Lankan food, cooked to order. A room when you want to stay.',
            'hero_text' => 'Eat in, order to your door, or book a room for the night. Everything is cooked fresh in our kitchen.',
            'tagline' => 'Restaurant · Delivery · Rooms',
        ];

        foreach ($old as $key => $value) {
            DB::table('site_settings')->where('key', $key)->where('value', $value)->delete();   // falls back to the new default
        }

        // Old default hours were 08:00–22:00 every day
        $hours = DB::table('site_settings')->where('key', 'hours')->value('value');
        if ($hours && ! str_contains($hours, '"close":"02:00"') && substr_count($hours, '"close":"22:00"') === 7) {
            DB::table('site_settings')->where('key', 'hours')->delete();
        }

        Cache::forget('site_settings');
    }

    public function down(): void
    {
        Cache::forget('site_settings');
    }
};
