<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MenuItem extends Model
{
    use HasFactory;

    protected $table = 'menu_items';

    protected $fillable = [
        'category_id',
        'item_code',
        'name',
        'description',
        'price',
        'dietary_type',
        'spice_level',
        'image_path',
        'is_available',
        'prep_time_minutes',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'spice_level' => 'integer',
            'is_available' => 'boolean',
            'prep_time_minutes' => 'integer',
        ];
    }

    // -------------------------------------------------------------------------
    // Relationships
    // -------------------------------------------------------------------------

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class, 'menu_item_id');
    }

    // -------------------------------------------------------------------------
    // Scopes
    // -------------------------------------------------------------------------

    public function scopeAvailable(Builder $query): Builder
    {
        return $query->where('is_available', true);
    }

    public function scopeVeg(Builder $query): Builder
    {
        return $query->where('dietary_type', 'veg');
    }

    public function scopeNonVeg(Builder $query): Builder
    {
        return $query->where('dietary_type', 'non_veg');
    }

    public function scopeByCategory(Builder $query, int $categoryId): Builder
    {
        return $query->where('category_id', $categoryId);
    }

    // -------------------------------------------------------------------------
    // Helpers & Accessors
    // -------------------------------------------------------------------------

    /**
     * Map database dietary_type to kebab-case food_type for UI filters.
     */
    public function getFoodTypeAttribute(): string
    {
        return str_replace('_', '-', $this->dietary_type ?? 'veg');
    }

    public function isVeg(): bool
    {
        return $this->dietary_type === 'veg';
    }

    public function isNonVeg(): bool
    {
        return in_array($this->dietary_type, ['non_veg', 'egg'], true);
    }

    public function formattedPrice(): string
    {
        return '₹' . number_format($this->price, 2);
    }
}
