<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RoomType extends Model
{
    protected $fillable = ['name', 'price_per_night', 'capacity', 'description'];

    protected function casts(): array
    {
        return ['price_per_night' => 'decimal:2'];
    }

    public function rooms(): HasMany
    {
        return $this->hasMany(Room::class);
    }
}
