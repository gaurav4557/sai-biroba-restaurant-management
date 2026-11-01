<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    use HasFactory;

    protected $table = 'orders';

    protected $fillable = [
        'table_visit_id',
        'table_id',
        'waiter_id',
        'order_number',
        'round_number',
        'status',
        'order_notes',
        'idempotency_token',
        'submitted_at',
        'cancelled_at',
        'cancelled_by',
        'cancellation_reason',
    ];

    protected function casts(): array
    {
        return [
            'round_number' => 'integer',
            'submitted_at' => 'datetime',
            'cancelled_at' => 'datetime',
        ];
    }

    // -------------------------------------------------------------------------
    // Relationships
    // -------------------------------------------------------------------------

    public function table(): BelongsTo
    {
        return $this->belongsTo(RestaurantTable::class, 'table_id');
    }

    public function visit(): BelongsTo
    {
        return $this->belongsTo(TableVisit::class, 'table_visit_id');
    }

    public function waiter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'waiter_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class, 'order_id');
    }

    public function cancelledBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cancelled_by');
    }

    // -------------------------------------------------------------------------
    // Scopes
    // -------------------------------------------------------------------------

    public function scopeSubmitted(Builder $query): Builder
    {
        return $query->where('status', 'submitted');
    }

    public function scopeActiveKitchen(Builder $query): Builder
    {
        return $query->whereIn('status', ['submitted', 'preparing']);
    }

    public function scopeForVisit(Builder $query, int $visitId): Builder
    {
        return $query->where('table_visit_id', $visitId)->orderBy('round_number');
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    public function calculateTotal(): float
    {
        return (float) $this->items->where('status', '!=', 'cancelled')->sum('subtotal');
    }

    public function isSubmitted(): bool
    {
        return $this->status === 'submitted';
    }

    public function isCancelled(): bool
    {
        return $this->status === 'cancelled';
    }

    public function formattedTotal(): string
    {
        return '₹' . number_format($this->calculateTotal(), 2);
    }
}
