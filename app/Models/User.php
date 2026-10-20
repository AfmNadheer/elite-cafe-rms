<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

/**
 * A staff account with a role (FR-01 to FR-05).
 */
class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /** Role names used across the system. */
    public const ROLES = [
        'admin' => 'Admin / Owner',
        'cashier' => 'Cashier',
        'waiter' => 'Waiter',
        'kitchen' => 'Kitchen Staff',
        'storekeeper' => 'Storekeeper',
    ];

    protected $fillable = [
        'name',
        'username',
        'email',
        'password',
        'role',
        'is_active',
    ];

    /** Never sent to the browser. */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',     // bcrypt hashing (NFR-04)
            'is_active' => 'boolean',
            'locked_until' => 'datetime',
        ];
    }

    /** Example: $user->hasRole('admin', 'cashier') */
    public function hasRole(string ...$roles): bool
    {
        return in_array($this->role, $roles, true);
    }

    /** Readable role name, e.g. "Kitchen Staff". */
    public function roleName(): string
    {
        return self::ROLES[$this->role] ?? $this->role;
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function bills(): HasMany
    {
        return $this->hasMany(Bill::class);
    }

    public function expenses(): HasMany
    {
        return $this->hasMany(Expense::class);
    }
}
