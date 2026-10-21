<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * One line in the audit log (NFR-06).
 */
class AuditLog extends Model
{
    /** Labels shown on the audit log page. */
    public const ACTIONS = [
        'login' => 'Login',
        'logout' => 'Logout',
        'login_failed' => 'Failed login',
        'account_locked' => 'Account locked',
        'password_changed' => 'Password changed',
        'user_created' => 'User created',
        'user_changed' => 'User changed',
        'price_changed' => 'Price changed',
        'menu_item_deleted' => 'Menu item deleted',
        'item_cancelled' => 'Item cancelled',
        'discount' => 'Discount given',
        'stock_adjusted' => 'Stock adjusted',
        'purchase' => 'Purchase recorded',
        'expense_deleted' => 'Expense deleted',
        'backup' => 'Database backup',
    ];

    protected $fillable = [
        'user_id',
        'action',
        'description',
        'subject_type',
        'subject_id',
        'ip_address',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Save one audit entry. The logged-in user is used unless another user is given.
     */
    public static function record(string $action, string $description, ?Model $subject = null, ?int $userId = null): void
    {
        static::create([
            'user_id' => $userId ?? auth()->id(),
            'action' => $action,
            'description' => mb_substr($description, 0, 255),
            'subject_type' => $subject ? class_basename($subject) : null,
            'subject_id' => $subject?->getKey(),
            'ip_address' => request()?->ip(),
        ]);
    }

    public function actionName(): string
    {
        return self::ACTIONS[$this->action] ?? ucfirst(str_replace('_', ' ', $this->action));
    }
}
