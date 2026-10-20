<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;

/**
 * An online customer who orders food for delivery.
 * Logs in with the separate "customer" guard, never into the staff system.
 */
class Customer extends Authenticatable
{
    protected $fillable = ['name', 'email', 'phone', 'password', 'address'];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return ['password' => 'hashed'];
    }

    public function deliveries(): HasMany
    {
        return $this->hasMany(Delivery::class);
    }
}
