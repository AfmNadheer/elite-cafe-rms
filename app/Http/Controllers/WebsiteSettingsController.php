<?php

namespace App\Http\Controllers;

use App\Models\SiteSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

/**
 * Admin edits the public website: text, contact details, opening hours and photos.
 */
class WebsiteSettingsController extends Controller
{
    private const TEXT_FIELDS = ['name', 'tagline', 'hero_title', 'hero_text', 'about_title', 'about_text', 'address', 'phone', 'whatsapp', 'email', 'map_embed', 'facebook', 'instagram', 'tiktok'];

    public function edit(): View
    {
        return view('website-settings.edit', ['s' => SiteSetting::values(), 'days' => SiteSetting::DAYS]);
    }

    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:60'],
            'tagline' => ['nullable', 'string', 'max:100'],
            'hero_title' => ['required', 'string', 'max:120'],
            'hero_text' => ['nullable', 'string', 'max:300'],
            'about_title' => ['required', 'string', 'max:80'],
            'about_text' => ['nullable', 'string', 'max:3000'],
            'address' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'regex:/^[0-9 +\-]{9,20}$/'],
            'whatsapp' => ['nullable', 'regex:/^[0-9]{9,15}$/'],
            'email' => ['nullable', 'email', 'max:100'],
            'map_embed' => ['nullable', 'url', 'starts_with:https://www.google.com/maps/embed'],
            'facebook' => ['nullable', 'url', 'max:255'],
            'instagram' => ['nullable', 'url', 'max:255'],
            'tiktok' => ['nullable', 'url', 'max:255'],
            'hours' => ['required', 'array'],
            'hours.*.open' => ['required', 'date_format:H:i'],
            'hours.*.close' => ['required', 'date_format:H:i', 'different:hours.*.open'],   // earlier than open = after midnight
            'hero_image' => ['nullable', 'image', 'max:4096'],
            'about_image' => ['nullable', 'image', 'max:4096'],
        ], [
            'whatsapp.regex' => 'WhatsApp number: digits only with the country code, e.g. 94771234567.',
            'map_embed.starts_with' => 'Paste the link from Google Maps → Share → Embed a map (it starts with https://www.google.com/maps/embed).',
            'hours.*.close.different' => 'Opening and closing time cannot be the same.',
        ]);

        $values = collect($data)->only(self::TEXT_FIELDS)->map(fn ($v) => $v ?? '')->all();

        $hours = [];
        foreach (array_keys(SiteSetting::DAYS) as $day) {
            $hours[$day] = [
                'open' => $data['hours'][$day]['open'] ?? '08:00',
                'close' => $data['hours'][$day]['close'] ?? '22:00',
                'closed' => $request->boolean("hours.$day.closed"),
            ];
        }
        $values['hours'] = $hours;

        // Photos: upload a new one, or remove the old one
        $current = SiteSetting::values();
        foreach (['hero_image', 'about_image'] as $field) {
            if ($request->hasFile($field)) {
                $this->deletePhoto($current[$field] ?? '');
                $values[$field] = $request->file($field)->store('site', 'public');
            } elseif ($request->boolean("remove_$field")) {
                $this->deletePhoto($current[$field] ?? '');
                $values[$field] = '';
            }
        }

        SiteSetting::put($values);

        return back()->with('success', 'Website updated.');
    }

    private function deletePhoto(?string $path): void
    {
        if ($path) {
            Storage::disk('public')->delete($path);
        }
    }
}
