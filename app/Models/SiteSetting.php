<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;

/**
 * Website settings stored as key → value (Admin edits them in Website Settings).
 */
class SiteSetting extends Model
{
    protected $primaryKey = 'key';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = ['key', 'value'];

    public const DAYS = ['mon' => 'Monday', 'tue' => 'Tuesday', 'wed' => 'Wednesday', 'thu' => 'Thursday', 'fri' => 'Friday', 'sat' => 'Saturday', 'sun' => 'Sunday'];

    /** All settings: defaults from config/site.php, replaced by saved values. */
    public static function values(): array
    {
        return Cache::rememberForever('site_settings', function () {
            $saved = static::pluck('value', 'key')->all();
            $values = array_merge(config('site.defaults'), $saved);
            $values['hours'] = is_string($values['hours']) ? (json_decode($values['hours'], true) ?: config('site.defaults.hours')) : $values['hours'];

            return $values;
        });
    }

    public static function put(array $values): void
    {
        foreach ($values as $key => $value) {
            static::updateOrCreate(['key' => $key], ['value' => is_array($value) ? json_encode($value) : $value]);
        }
        Cache::forget('site_settings');
    }

    /** Open and close moments for one day; a close time at or before the open time is after midnight. */
    private static function window(Carbon $day, array $h): array
    {
        $open = $day->copy()->setTimeFromTimeString($h['open']);
        $close = $day->copy()->setTimeFromTimeString($h['close']);
        if ($close->lte($open)) {
            $close->addDay();
        }

        return [$open, $close];
    }

    /**
     * "Open now" line for the website, e.g. ['open' => true, 'text' => 'Open now · closes 2:00 AM'].
     * Works with hours that go past midnight (8:00 AM – 2:00 AM).
     */
    public static function openStatus(?Carbon $now = null): array
    {
        $now ??= now();
        $hours = static::values()['hours'];
        $keys = array_keys(self::DAYS);
        $dayHours = fn (Carbon $d) => $hours[$keys[$d->dayOfWeekIso - 1]] ?? null;

        // Still inside last night's opening (e.g. 1:00 AM after an 8 AM – 2 AM day)
        $y = $dayHours($now->copy()->subDay());
        if ($y && ! ($y['closed'] ?? false)) {
            [, $close] = self::window($now->copy()->subDay()->startOfDay(), $y);
            if ($now->lt($close)) {
                return ['open' => true, 'text' => 'Open now · closes '.$close->format('g:i A')];
            }
        }

        $t = $dayHours($now);
        if ($t && ! ($t['closed'] ?? false)) {
            [$open, $close] = self::window($now->copy()->startOfDay(), $t);
            if ($now->between($open, $close)) {
                return ['open' => true, 'text' => 'Open now · closes '.$close->format('g:i A')];
            }
            if ($now->lt($open)) {
                return ['open' => false, 'text' => 'Closed now · opens '.$open->format('g:i A').' today'];
            }
        }

        for ($i = 1; $i <= 7; $i++) {
            $day = $now->copy()->addDays($i);
            $next = $dayHours($day);
            if ($next && ! ($next['closed'] ?? false)) {
                $when = $i === 1 ? 'tomorrow' : $day->format('l');

                return ['open' => false, 'text' => 'Closed now · opens '.Carbon::parse($next['open'])->format('g:i A').' '.$when];
            }
        }

        return ['open' => false, 'text' => 'Closed'];
    }

    /** "8:00 AM – 2:00 AM" when every day has the same hours, otherwise null. */
    public static function sameHoursEveryDay(): ?string
    {
        $hours = array_values(static::values()['hours']);
        $first = $hours[0] ?? null;

        foreach ($hours as $h) {
            if (($h['closed'] ?? false) || $h['open'] !== $first['open'] || $h['close'] !== $first['close']) {
                return null;
            }
        }

        return self::range($first);
    }

    public static function range(array $h): string
    {
        return ($h['closed'] ?? false) ? 'Closed'
            : Carbon::parse($h['open'])->format('g:i A').' – '.Carbon::parse($h['close'])->format('g:i A');
    }
}
