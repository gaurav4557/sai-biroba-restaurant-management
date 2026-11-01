<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOne;

class RestaurantTable extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'restaurant_tables';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'table_number',
        'seating_capacity',
        'floor_section',
        'status',
        'is_active',
    ];

    /**
     * The attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'seating_capacity' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    // -------------------------------------------------------------------------
    // Relationships
    // -------------------------------------------------------------------------

    /**
     * All waiter assignments for this table (historical & current).
     */
    public function assignments(): HasMany
    {
        return $this->hasMany(TableWaiterAssignment::class, 'table_id');
    }

    /**
     * The currently active waiter assignment for this table.
     */
    public function activeAssignment(): HasOne
    {
        return $this->hasOne(TableWaiterAssignment::class, 'table_id')->where('status', 'active');
    }

    /**
     * All customer dining visits/sessions for this table (historical & active).
     */
    public function visits(): HasMany
    {
        return $this->hasMany(TableVisit::class, 'table_id');
    }

    /**
     * The currently active or billed dining session for this table.
     */
    public function activeVisit(): HasOne
    {
        return $this->hasOne(TableVisit::class, 'table_id')->whereIn('status', ['active', 'billed']);
    }

    /**
     * All food orders placed for this table.
     */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class, 'table_id');
    }

    /**
     * All bills generated for dining sessions at this table.
     */
    public function bills(): HasManyThrough
    {
        return $this->hasManyThrough(Bill::class, TableVisit::class, 'table_id', 'table_visit_id');
    }

    // -------------------------------------------------------------------------
    // Query Scopes
    // -------------------------------------------------------------------------

    /**
     * Scope a query to only include active tables.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to only include available tables ready for seating.
     */
    public function scopeAvailable(Builder $query): Builder
    {
        return $query->where('is_active', true)->where('status', 'available');
    }

    /**
     * Scope a query to only include currently occupied tables.
     */
    public function scopeOccupied(Builder $query): Builder
    {
        return $query->where('status', 'occupied');
    }

    /**
     * Scope a query to only include tables awaiting bill settlement.
     */
    public function scopeBilled(Builder $query): Builder
    {
        return $query->where('status', 'billed');
    }

    /**
     * Scope a query to tables eligible for waiter assignment (available or occupied, active).
     */
    public function scopeAssignable(Builder $query): Builder
    {
        return $query->where('is_active', true)->whereNotIn('status', ['billed', 'out_of_service']);
    }

    /**
     * Scope a query to tables in a specific room/section.
     */
    public function scopeByRoom(Builder $query, string $room): Builder
    {
        return $query->where('floor_section', $room);
    }

    /**
     * Scope a query to Room 1 tables (T1–T10).
     */
    public function scopeRoom1(Builder $query): Builder
    {
        return $query->where('floor_section', 'Room 1');
    }

    /**
     * Scope a query to Room 2 tables (T11–T20).
     */
    public function scopeRoom2(Builder $query): Builder
    {
        return $query->where('floor_section', 'Room 2');
    }

    // -------------------------------------------------------------------------
    // Helper Methods
    // -------------------------------------------------------------------------

    /**
     * Check if the table is ready for guest seating.
     */
    public function isAvailable(): bool
    {
        return $this->is_active && $this->status === 'available';
    }

    /**
     * Check if the table is currently occupied by dining guests.
     */
    public function isOccupied(): bool
    {
        return $this->status === 'occupied';
    }

    /**
     * Check if a bill has been generated and awaiting payment.
     */
    public function isBilled(): bool
    {
        return $this->status === 'billed';
    }

    /**
     * Check if the table is taken out of service or inactive.
     */
    public function isOutOfService(): bool
    {
        return !$this->is_active || $this->status === 'out_of_service';
    }

    /**
     * Check if the table is eligible for a waiter assignment.
     * Tables that are billed or out of service cannot be assigned.
     */
    public function isAssignable(): bool
    {
        return $this->is_active && !in_array($this->status, ['billed', 'out_of_service']);
    }

    /**
     * Check if the table currently has an active assigned waiter.
     */
    public function hasActiveWaiter(): bool
    {
        return $this->activeAssignment !== null;
    }
}
