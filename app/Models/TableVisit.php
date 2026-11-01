<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOne;

class TableVisit extends Model
{
    use HasFactory;

    protected $table = 'table_visits';

    protected $fillable = [
        'table_id',
        'waiter_id',
        'visit_code',
        'guest_name',
        'guest_count',
        'status',
        'opened_at',
        'closed_at',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'guest_count' => 'integer',
            'opened_at' => 'datetime',
            'closed_at' => 'datetime',
        ];
    }

    // -------------------------------------------------------------------------
    // Relationships
    // -------------------------------------------------------------------------

    public function table(): BelongsTo
    {
        return $this->belongsTo(RestaurantTable::class, 'table_id');
    }

    public function waiter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'waiter_id');
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class, 'table_visit_id')->orderBy('round_number');
    }

    public function orderItems(): HasManyThrough
    {
        return $this->hasManyThrough(OrderItem::class, Order::class, 'table_visit_id', 'order_id');
    }

    public function bill(): HasOne
    {
        return $this->hasOne(Bill::class, 'table_visit_id');
    }

    // -------------------------------------------------------------------------
    // Scopes
    // -------------------------------------------------------------------------

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }

    public function scopeActiveOrBilled(Builder $query): Builder
    {
        return $query->whereIn('status', ['active', 'billed']);
    }

    public function scopeForTable(Builder $query, int $tableId): Builder
    {
        return $query->where('table_id', $tableId);
    }

    public function scopeForWaiter(Builder $query, int $waiterId): Builder
    {
        return $query->where('waiter_id', $waiterId);
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function isBilled(): bool
    {
        return $this->status === 'billed';
    }

    public function hasBill(): bool
    {
        return $this->bill !== null;
    }

    public function isClosed(): bool
    {
        return in_array($this->status, ['completed', 'cancelled']);
    }

    public function canAcceptOrders(): bool
    {
        return $this->status === 'active';
    }

    public function getNextRoundNumber(): int
    {
        return (int) ($this->orders()->max('round_number') ?? 0) + 1;
    }

    public function calculateSubtotal(): float
    {
        return (float) $this->orderItems()
            ->where('order_items.status', '!=', 'cancelled')
            ->sum('subtotal');
    }
}
