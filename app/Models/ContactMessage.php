<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * A message sent from the Contact form on the website.
 */
class ContactMessage extends Model
{
    protected $fillable = ['name', 'email', 'phone', 'message', 'is_read', 'ip_address'];

    protected function casts(): array
    {
        return ['is_read' => 'boolean'];
    }
}
