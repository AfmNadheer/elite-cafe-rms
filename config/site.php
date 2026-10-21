<?php

/*
 * Default text for the public website.
 * The Admin changes all of these in Setup → Website Settings (saved in the site_settings table).
 * Contact details are empty on purpose: fill in the real ones in Website Settings.
 */
return [
    'defaults' => [
        'name' => 'Elite Cafe',
        'tagline' => 'Restaurant, delivery & rooms',
        'hero_title' => 'Eat well. Stay late. Sleep easy.',
        'hero_text' => 'Freshly cooked Sri Lankan and international food from 8 AM to 2 AM. Dine in, order to your door, or book a room for the night.',
        'about_title' => 'About Elite Cafe',
        'about_text' => "Elite Cafe (Pvt) Ltd is a family restaurant serving Sri Lankan and international dishes for dine-in, takeaway and delivery.\n\nNext to the restaurant we have guest rooms for travellers and families, from single rooms to family rooms.",
        'address' => '',
        'phone' => '',
        'whatsapp' => '',          // e.g. 94771234567 (country code, no +)
        'email' => '',
        'map_embed' => '',         // Google Maps → Share → Embed a map → copy the src link
        'facebook' => '',
        'instagram' => '',
        'tiktok' => '',
        'hero_image' => '',
        'about_image' => '',
        // Opening hours per day (24-hour time). A closing time earlier than the opening time means after midnight (02:00 = 2 AM next day).
        'hours' => [
            'mon' => ['open' => '08:00', 'close' => '02:00', 'closed' => false],
            'tue' => ['open' => '08:00', 'close' => '02:00', 'closed' => false],
            'wed' => ['open' => '08:00', 'close' => '02:00', 'closed' => false],
            'thu' => ['open' => '08:00', 'close' => '02:00', 'closed' => false],
            'fri' => ['open' => '08:00', 'close' => '02:00', 'closed' => false],
            'sat' => ['open' => '08:00', 'close' => '02:00', 'closed' => false],
            'sun' => ['open' => '08:00', 'close' => '02:00', 'closed' => false],
        ],
    ],
];
