<?php

namespace Database\Seeders;

use App\Models\Room;
use App\Models\RoomType;
use Illuminate\Database\Seeder;

/**
 * Elite Cafe rooms 101–115.
 * Run once: php artisan db:seed --class=RoomSeeder
 */
class RoomSeeder extends Seeder
{
    public function run(): void
    {
        $types = [
            ['name' => 'Single Room', 'price_per_night' => 6000, 'capacity' => 1, 'description' => 'One single bed, attached bathroom, air conditioning, free Wi-Fi.', 'rooms' => range(101, 105)],
            ['name' => 'Double Room', 'price_per_night' => 7500, 'capacity' => 2, 'description' => 'One double bed, attached bathroom, air conditioning, free Wi-Fi.', 'rooms' => range(106, 110)],
            ['name' => 'Super Deluxe', 'price_per_night' => 15000, 'capacity' => 2, 'description' => 'King-size bed, sitting area, hot water, TV, mini fridge, free Wi-Fi.', 'rooms' => range(111, 113)],
            ['name' => 'Family Room', 'price_per_night' => 20000, 'capacity' => 4, 'description' => 'Two double beds for families, hot water, TV, free Wi-Fi.', 'rooms' => range(114, 115)],
        ];

        foreach ($types as $data) {
            $type = RoomType::updateOrCreate(
                ['name' => $data['name']],
                collect($data)->except('rooms')->all()
            );

            foreach ($data['rooms'] as $no) {
                Room::firstOrCreate(['room_no' => (string) $no], ['room_type_id' => $type->id, 'status' => 'available']);
            }
        }
    }
}
